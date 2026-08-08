<?php

namespace App\Libraries;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class CloudflareStorage
{
    protected $client;
    protected $bucket;

    public function __construct()
    {
        $config = config('CloudflareR2');
        
        $this->bucket = $config->bucket;

        // Ensure we have credentials
        if (empty($config->key) || empty($config->secret) || empty($this->bucket)) {
            log_message('error', 'Cloudflare R2 credentials are not set in .env');
            return;
        }

        // Ensure AWS SDK is installed
        if (!class_exists('\Aws\S3\S3Client')) {
            log_message('error', 'AWS SDK is not installed. Run composer install.');
            return;
        }

        $this->client = new S3Client([
            'version'     => 'latest',
            'region'      => $config->region,
            'endpoint'    => $config->endpoint,
            'credentials' => [
                'key'    => $config->key,
                'secret' => $config->secret,
            ],
            // R2 requires this to be true
            'use_path_style_endpoint' => true,
        ]);
    }

    public function isConfigured(): bool
    {
        return $this->client !== null;
    }

    /**
     * Upload a file to R2
     *
     * @param string $sourcePath The absolute path of the local file
     * @param string $destPath The path inside the R2 bucket (e.g. 'uploads/video.mp4')
     * @return bool
     */
    public function uploadFile(string $sourcePath, string $destPath): bool
    {
        if (!$this->client) return false;

        try {
            $this->client->putObject([
                'Bucket'     => $this->bucket,
                'Key'        => ltrim($destPath, '/'),
                'SourceFile' => $sourcePath,
            ]);
            return true;
        } catch (AwsException $e) {
            log_message('error', 'R2 Upload Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a file exists on R2
     *
     * @param string $path The path in the R2 bucket
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        if (!$this->client) return false;

        try {
            return $this->client->doesObjectExist($this->bucket, ltrim($path, '/'));
        } catch (\Exception $e) {
            log_message('error', 'R2 Exists Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a securely signed URL valid for a specific duration
     *
     * @param string $path The path in the R2 bucket
     * @param string $expires Expiry time (e.g. '+60 minutes')
     * @return string
     */
    public function getSignedUrl(string $path, string $expires = '+60 minutes'): string
    {
        if (!$this->client) return '';

        try {
            $cmd = $this->client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key'    => ltrim($path, '/'),
            ]);

            $request = $this->client->createPresignedRequest($cmd, $expires);
            return (string) $request->getUri();
        } catch (AwsException $e) {
            log_message('error', 'R2 Signed URL Error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Delete a file from R2
     */
    public function deleteFile(string $path): bool
    {
        if (!$this->client) return false;

        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => ltrim($path, '/'),
            ]);
            return true;
        } catch (AwsException $e) {
            log_message('error', 'R2 Delete Error: ' . $e->getMessage());
            return false;
        }
    }
}
