<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\ProjectModel;
use App\Models\SequenceModel;
use App\Models\ShotModel;

class Briefing extends BaseController
{
    protected $projectModel;
    protected $sequenceModel;
    protected $shotModel;

    public function __construct()
    {
        $this->projectModel  = new ProjectModel();
        $this->sequenceModel = new SequenceModel();
        $this->shotModel     = new ShotModel();
    }

    /**
     * Client Shot Briefing & Feedback Matrix
     */
    public function index($projectId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userRole = session()->get('userRole');
        $clientId = session()->get('clientId');

        $project = $this->projectModel->find($projectId);
        if (!$project) {
            return redirect()->back()->with('error', 'Project not found.');
        }

        // Access control: Ensure client can only view their own projects (admins can view all)
        if ($userRole === 'client' && $project->client_id != $clientId) {
            return redirect()->to('/client/dashboard')->with('error', 'Unauthorized access to this project.');
        }

        $sequences = $this->sequenceModel->where('project_id', $projectId)->orderBy('name', 'ASC')->findAll();
        $shots = $this->shotModel->where('project_id', $projectId)->orderBy('shot_number', 'ASC')->findAll();

        // Process reference images JSON
        foreach ($shots as $shot) {
            $refs = !empty($shot->reference_images) ? json_decode($shot->reference_images, true) : [];
            $shot->references = is_array($refs) ? $refs : [];
        }

        $data = [
            'pageTitle' => $project->name . ' - Shot Briefing & Reference Matrix',
            'project'   => $project,
            'sequences' => $sequences,
            'shots'     => $shots,
            'userRole'  => $userRole,
        ];

        return view('client/briefing/index', $data);
    }

    /**
     * AJAX: Live Auto-Save for Shot Description / Brief / Client Notes
     */
    public function saveBriefAjax()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'error' => 'Authentication required'])->setStatusCode(401);
        }

        $shotId = (int)$this->request->getPost('shot_id');
        $description = $this->request->getPost('description');
        $clientNotes = $this->request->getPost('client_notes');

        if (!$shotId) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid shot ID'])->setStatusCode(400);
        }

        $shot = $this->shotModel->find($shotId);
        if (!$shot) {
            return $this->response->setJSON(['success' => false, 'error' => 'Shot not found'])->setStatusCode(404);
        }

        // Verify project ownership if client
        if (session()->get('userRole') === 'client') {
            $project = $this->projectModel->find($shot->project_id);
            if (!$project || $project->client_id != session()->get('clientId')) {
                return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized'])->setStatusCode(403);
            }
        }

        $updateData = [];
        if ($description !== null) {
            $updateData['description'] = trim($description);
        }
        if ($clientNotes !== null) {
            $updateData['client_notes'] = trim($clientNotes);
        }

        if (!empty($updateData)) {
            $this->shotModel->update($shotId, $updateData);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Briefing auto-saved!',
            'shot_id' => $shotId
        ]);
    }

    /**
     * AJAX: Upload Reference Image or Document for a specific Shot
     */
    public function uploadReferenceAjax()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'error' => 'Authentication required'])->setStatusCode(401);
        }

        $shotId = (int)$this->request->getPost('shot_id');
        if (!$shotId) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid shot ID'])->setStatusCode(400);
        }

        $shot = $this->shotModel->find($shotId);
        if (!$shot) {
            return $this->response->setJSON(['success' => false, 'error' => 'Shot not found'])->setStatusCode(404);
        }

        // Verify project ownership if client
        if (session()->get('userRole') === 'client') {
            $project = $this->projectModel->find($shot->project_id);
            if (!$project || $project->client_id != session()->get('clientId')) {
                return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized'])->setStatusCode(403);
            }
        }

        $file = $this->request->getFile('reference_file');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid file upload'])->setStatusCode(400);
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'mp4', 'mov'];
        $ext = strtolower($file->getClientExtension());
        if (!in_array($ext, $allowedExtensions)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Unsupported file type. Allowed: JPG, PNG, WebP, GIF, PDF, MP4, MOV.'])->setStatusCode(400);
        }

        $targetDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'references';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        $originalName = $file->getClientName();
        $isImage      = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);

        // For images: compress and convert to WebP; for other files: keep original
        if ($isImage) {
            $safeName = 'ref_' . $shot->project_id . '_' . $shot->id . '_' . uniqid() . '.webp';
            $destPath = $targetDir . DIRECTORY_SEPARATOR . $safeName;

            // Move original first, then compress in place
            $tmpName = 'tmp_' . uniqid() . '.' . $ext;
            $file->move($targetDir, $tmpName);
            $tmpPath = $targetDir . DIRECTORY_SEPARATOR . $tmpName;

            // Compress with GD → WebP (max 1920px wide, 85% quality)
            $compressed = $this->compressImageToWebP($tmpPath, $destPath, 1920, 85);
            @unlink($tmpPath);

            if (!$compressed) {
                // GD failed — fall back to original
                $safeName = 'ref_' . $shot->project_id . '_' . $shot->id . '_' . uniqid() . '.' . $ext;
                rename($tmpPath, $targetDir . DIRECTORY_SEPARATOR . $safeName);
                $destPath = $targetDir . DIRECTORY_SEPARATOR . $safeName;
            }

            $ext      = 'webp';
            $isImage  = true;
        } else {
            $safeName = 'ref_' . $shot->project_id . '_' . $shot->id . '_' . uniqid() . '.' . $ext;
            $file->move($targetDir, $safeName);
            $destPath = $targetDir . DIRECTORY_SEPARATOR . $safeName;
        }

        $relPath = 'uploads/references/' . $safeName;

        // Sync to Cloudflare R2
        try {
            $r2 = new \App\Libraries\CloudflareStorage();
            if ($r2->isConfigured()) {
                $r2->uploadFile($destPath, $relPath);
            }
        } catch (\Throwable $e) {
            log_message('error', 'R2 Reference upload warning: ' . $e->getMessage());
        }

        // Persist to DB
        $existing = !empty($shot->reference_images) ? json_decode($shot->reference_images, true) : [];
        if (!is_array($existing)) $existing = [];

        $refItem = [
            'path'        => $relPath,
            'url'         => base_url($relPath),
            'name'        => $originalName,
            'ext'         => $ext,
            'is_image'    => $isImage,
            'uploaded_by' => session()->get('userName') ?? 'Client',
            'uploaded_at' => date('Y-m-d H:i:s'),
        ];

        $existing[] = $refItem;
        $this->shotModel->update($shotId, ['reference_images' => json_encode($existing)]);

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Reference attached successfully!',
            'shot_id'    => $shotId,
            'reference'  => $refItem,
            'references' => $existing
        ]);
    }

    /**
     * Compress any image to WebP using GD.
     * Returns true on success, false on failure.
     */
    private function compressImageToWebP(string $srcPath, string $destPath, int $maxWidth = 1920, int $quality = 85): bool
    {
        if (!function_exists('imagecreatefromjpeg')) return false;

        $info = @getimagesize($srcPath);
        if (!$info) return false;

        [$origW, $origH, $type] = $info;

        $src = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($srcPath),
            IMAGETYPE_PNG  => @imagecreatefrompng($srcPath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($srcPath),
            IMAGETYPE_GIF  => @imagecreatefromgif($srcPath),
            default        => false,
        };

        if (!$src) return false;

        // Scale down if wider than maxWidth
        if ($origW > $maxWidth) {
            $scale   = $maxWidth / $origW;
            $newW    = (int)($origW * $scale);
            $newH    = (int)($origH * $scale);
            $resized = imagecreatetruecolor($newW, $newH);

            // Preserve transparency for PNG/WebP
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($src);
            $src = $resized;
        }

        $result = imagewebp($src, $destPath, $quality);
        imagedestroy($src);

        return $result && file_exists($destPath);
    }

    /**
     * AJAX: Delete a Reference Attachment
     */
    public function deleteReferenceAjax()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'error' => 'Authentication required'])->setStatusCode(401);
        }

        $shotId = (int)$this->request->getPost('shot_id');
        $refPath = $this->request->getPost('ref_path');

        if (!$shotId || empty($refPath)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid parameters'])->setStatusCode(400);
        }

        $shot = $this->shotModel->find($shotId);
        if (!$shot) {
            return $this->response->setJSON(['success' => false, 'error' => 'Shot not found'])->setStatusCode(404);
        }

        // Verify project ownership if client
        if (session()->get('userRole') === 'client') {
            $project = $this->projectModel->find($shot->project_id);
            if (!$project || $project->client_id != session()->get('clientId')) {
                return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized'])->setStatusCode(403);
            }
        }

        $existing = !empty($shot->reference_images) ? json_decode($shot->reference_images, true) : [];
        if (!is_array($existing)) $existing = [];

        $updated = [];
        foreach ($existing as $item) {
            if ($item['path'] === $refPath) {
                // Delete local file if exists
                $local = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $item['path']);
                @unlink($local);
            } else {
                $updated[] = $item;
            }
        }

        $this->shotModel->update($shotId, ['reference_images' => json_encode($updated)]);

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Reference removed',
            'shot_id'    => $shotId,
            'references' => $updated
        ]);
    }
}
