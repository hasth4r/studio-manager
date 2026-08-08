<?php

namespace App\Libraries;

use App\Models\SettingsModel;

class FolderManager
{
    /**
     * Get the base production path from settings.
     */
    public static function getBasePath()
    {
        $settingsModel = new SettingsModel();
        $basePath = $settingsModel->getSetting('production_drive_path', 'F:\\STUDIO_PRODUCTION\\PROJECTS');
        // Ensure standard directory separator and trim trailing slash
        return rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $basePath), DIRECTORY_SEPARATOR);
    }

    /**
     * Helper to recursively create a directory.
     */
    private static function makeDir($path)
    {
        if (!is_dir($path)) {
            // Suppress warnings in case of permission issues, but we can log them if needed
            @mkdir($path, 0777, true);
        }
    }

    /**
     * Create the base folder structure for a new Project.
     * 
     * [Project Code]
     *  ├── admin
     *  ├── assets
     *  │   ├── char
     *  │   ├── prop
     *  │   └── env
     *  ├── shots
     *  ├── renders
     *  ├── deliverables
     *  └── references
     */
    public static function createProjectFolders($projectCode)
    {
        if (empty($projectCode)) return false;

        $base = self::getBasePath() . DIRECTORY_SEPARATOR . $projectCode;

        $directories = [
            $base,
            $base . DIRECTORY_SEPARATOR . 'admin',
            $base . DIRECTORY_SEPARATOR . 'assets',
            $base . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'char',
            $base . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'prop',
            $base . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'env',
            $base . DIRECTORY_SEPARATOR . 'shots',
            $base . DIRECTORY_SEPARATOR . 'renders',
            $base . DIRECTORY_SEPARATOR . 'deliverables',
            $base . DIRECTORY_SEPARATOR . 'references',
        ];

        foreach ($directories as $dir) {
            self::makeDir($dir);
        }

        return true;
    }

    /**
     * Create folder for a Sequence.
     * 
     * [Project Code] / shots / [Sequence Code]
     */
    public static function createSequenceFolders($projectCode, $sequenceCode)
    {
        if (empty($projectCode) || empty($sequenceCode)) return false;

        $path = self::getBasePath() . DIRECTORY_SEPARATOR . $projectCode . DIRECTORY_SEPARATOR . 'shots' . DIRECTORY_SEPARATOR . $sequenceCode;
        self::makeDir($path);
        
        return true;
    }

    /**
     * Create folder for a Shot and its standard subfolders.
     * 
     * [Project Code] / shots / [Sequence Code] / [Shot Code]
     *  ├── plates
     *  ├── tracking
     *  ├── anim
     *  ├── fx
     *  ├── light
     *  └── comp
     */
    public static function createShotFolders($projectCode, $sequenceCode, $shotCode)
    {
        if (empty($projectCode) || empty($sequenceCode) || empty($shotCode)) return false;

        $base = self::getBasePath() . DIRECTORY_SEPARATOR . $projectCode . DIRECTORY_SEPARATOR . 'shots' . DIRECTORY_SEPARATOR . $sequenceCode . DIRECTORY_SEPARATOR . $shotCode;

        $directories = [
            $base,
            $base . DIRECTORY_SEPARATOR . 'plates',
            $base . DIRECTORY_SEPARATOR . 'tracking',
            $base . DIRECTORY_SEPARATOR . 'anim',
            $base . DIRECTORY_SEPARATOR . 'fx',
            $base . DIRECTORY_SEPARATOR . 'light',
            $base . DIRECTORY_SEPARATOR . 'comp',
        ];

        foreach ($directories as $dir) {
            self::makeDir($dir);
        }
        
        return true;
    }

    /**
     * Create folder for an Asset.
     * 
     * [Project Code] / assets / [Category] / [Asset Name]
     */
    public static function createAssetFolders($projectCode, $category, $assetName)
    {
        if (empty($projectCode) || empty($assetName)) return false;

        // Default category to prop if missing or invalid
        $validCategories = ['char', 'prop', 'env'];
        if (!in_array($category, $validCategories)) {
            $category = 'prop';
        }

        // Sanitize asset name for folder creation (replace spaces with underscores)
        $safeAssetName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $assetName);

        $path = self::getBasePath() . DIRECTORY_SEPARATOR . $projectCode . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $safeAssetName;
        
        self::makeDir($path);
        
        return true;
    }

    /**
     * Create folder for a Task, inside its parent Shot or Asset.
     * Contains: input, projectfile, output
     */
    public static function createTaskFolders($projectId, $taskTypeId, $shotId = null, $assetId = null)
    {
        $db = \Config\Database::connect();
        
        $project = $db->table('projects')->where('id', $projectId)->get()->getRow();
        $taskType = $db->table('task_types')->where('id', $taskTypeId)->get()->getRow();
        
        if (!$project || !$taskType) return false;

        // Map database task names to standard short folder names
        $taskFolderMap = [
            'Animation'       => 'anim',
            'Lighting'        => 'light',
            'Compositing'     => 'comp',
            'FX'              => 'fx',
            'Layout'          => 'layout',
            'Simulation'      => 'sim',
            'Motion graphics' => 'mograph',
            'Modeling'        => 'geo',
            'Texturing'       => 'tex',
            'Rigging'         => 'rig'
        ];

        // Use mapped name if it exists, otherwise sanitize the original name
        $folderName = isset($taskFolderMap[$taskType->name]) 
            ? $taskFolderMap[$taskType->name] 
            : preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($taskType->name));

        $basePath = self::getBasePath() . DIRECTORY_SEPARATOR . $project->project_code;
        $parentPath = '';

        if (!empty($shotId)) {
            $shot = $db->table('shots')->where('id', $shotId)->get()->getRow();
            if ($shot && $shot->sequence_id) {
                $seq = $db->table('sequences')->where('id', $shot->sequence_id)->get()->getRow();
                if ($seq) {
                    $parentPath = 'shots' . DIRECTORY_SEPARATOR . $seq->name . DIRECTORY_SEPARATOR . $shot->shot_number;
                }
            }
        } elseif (!empty($assetId)) {
            $asset = $db->table('assets')->where('id', $assetId)->get()->getRow();
            if ($asset) {
                $safeAssetName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $asset->name);
                $validCategories = ['char', 'prop', 'env'];
                $category = in_array($asset->type, $validCategories) ? $asset->type : 'prop';
                $parentPath = 'assets' . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $safeAssetName;
            }
        }

        if (empty($parentPath)) return false;

        $taskRoot = $basePath . DIRECTORY_SEPARATOR . $parentPath . DIRECTORY_SEPARATOR . $folderName;

        self::makeDir($taskRoot);
        self::makeDir($taskRoot . DIRECTORY_SEPARATOR . 'input');
        self::makeDir($taskRoot . DIRECTORY_SEPARATOR . 'projectfile');
        self::makeDir($taskRoot . DIRECTORY_SEPARATOR . 'output');

        return true;
    }

    /**
     * Sync/Generate all folders for a project by iterating through its entities.
     * This is useful if the folder was deleted locally or created before the automation existed.
     */
    public static function syncProjectFolders($projectId)
    {
        $db = \Config\Database::connect();
        
        $project = $db->table('projects')->where('id', $projectId)->get()->getRow();
        if (!$project) return false;

        // 1. Project Base Folders
        self::createProjectFolders($project->project_code);

        // 2. Sequences
        $sequences = $db->table('sequences')->where('project_id', $projectId)->get()->getResult();
        foreach ($sequences as $seq) {
            self::createSequenceFolders($project->project_code, $seq->name);
            
            // 3. Shots within Sequence
            $shots = $db->table('shots')->where('sequence_id', $seq->id)->get()->getResult();
            foreach ($shots as $shot) {
                self::createShotFolders($project->project_code, $seq->name, $shot->shot_number);
            }
        }

        // 4. Assets
        $assets = $db->table('assets')->where('project_id', $projectId)->get()->getResult();
        foreach ($assets as $asset) {
            self::createAssetFolders($project->project_code, $asset->type, $asset->name);
        }

        // 5. Tasks
        $tasks = $db->table('tasks')->where('project_id', $projectId)->get()->getResult();
        foreach ($tasks as $task) {
            self::createTaskFolders($projectId, $task->task_type_id, $task->shot_id, $task->asset_id);
        }

        return true;
    }
}
