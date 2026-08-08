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
            
            // Read the requested chunk
            $fp = fopen($filePath, 'rb');
            fseek($fp, $start);
            $buffer = fread($fp, $length);
            fclose($fp);
            
            $this->response->setBody($buffer);
        } else {
            // Serve the whole file
            $this->response->setBody(file_get_contents($filePath));
        }

        return $this->response;
    }
}
