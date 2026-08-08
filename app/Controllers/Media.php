<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;

class Media extends BaseController
{
    public function serve(...$pathArr)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Reconstruct the path from the wildcard segments
        $path = implode('/', $pathArr);
        
        $r2 = new \App\Libraries\CloudflareStorage();
        if ($r2->isConfigured() && $r2->fileExists('uploads/' . $path)) {
            // Anti-Download: Shorten URL lifespan to 10 minutes so shared links die quickly
            $signedUrl = $r2->getSignedUrl('uploads/' . $path, '+10 minutes');
            if ($signedUrl) {
                return redirect()->to($signedUrl);
            }
        }

        // FALLBACK: Local File Streaming
        $filePath = WRITEPATH . 'uploads/' . $path;

        if (!is_file($filePath)) {
            throw new PageNotFoundException('Media not found');
        }

        $mimeType = mime_content_type($filePath);
        $fileSize = filesize($filePath);
        
        // Setup streaming for videos
        $this->response->setContentType($mimeType);
        $this->response->setHeader('Content-Length', (string)$fileSize);
        $this->response->setHeader('Accept-Ranges', 'bytes');
        
        // Handle basic Range requests for video scrubbing
        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = $_SERVER['HTTP_RANGE'];
            $range = str_replace('bytes=', '', $range);
            $rangeArr = explode('-', $range);
            $start = intval($rangeArr[0]);
            $end = ($rangeArr[1] === '') ? $fileSize - 1 : intval($rangeArr[1]);
            
            $length = $end - $start + 1;
            
            $this->response->setStatusCode(206);
            $this->response->setHeader('Content-Range', "bytes $start-$end/$fileSize");
            $this->response->setHeader('Content-Length', (string)$length);
            
            $this->response->sendHeaders();
            
            // Read and stream the requested chunk in 8KB pieces
            $fp = fopen($filePath, 'rb');
            fseek($fp, $start);
            
            $bytesLeft = $length;
            $bufferSize = 8192; // 8KB chunks
            
            while ($bytesLeft > 0 && !feof($fp)) {
                $readSize = min($bufferSize, $bytesLeft);
                echo fread($fp, $readSize);
                ob_flush();
                flush();
                $bytesLeft -= $readSize;
            }
            fclose($fp);
            exit; // End execution to prevent CI4 from loading anything else into memory
        } else {
            // Serve the whole file efficiently via stream
            $this->response->sendHeaders();
            
            $fp = fopen($filePath, 'rb');
            while (!feof($fp)) {
                echo fread($fp, 8192);
                ob_flush();
                flush();
            }
            fclose($fp);
            exit;
        }
    }
}
