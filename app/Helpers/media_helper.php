<?php

if (!function_exists('media_cdn_url')) {
    /**
     * Resolves a media path to the Cloudflare R2 / Custom Domain Worker CDN URL.
     * Fallback to base_url() if custom domain is not set.
     *
     * @param string|null $path Relative path, e.g. 'uploads/MHLYA-1/War/sh0280/edit/vid_sh0280.mp4'
     * @return string Full CDN URL or local URL
     */
    function media_cdn_url(?string $path = ''): string
    {
        if (empty($path)) {
            return '';
        }

        // If it's already an absolute URL, return as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');

        // Check if local file exists in public/ or uploads/
        if (is_file(FCPATH . $cleanPath) || is_file(FCPATH . 'uploads/' . $cleanPath)) {
            return base_url($cleanPath);
        }

        // Check if a dedicated, enabled CDN domain is set
        $cdnDomain = env('r2.custom_domain', '');
        $useCdn = env('r2.enabled', false);
        if ($useCdn && !empty($cdnDomain)) {
            $cdnDomain = rtrim($cdnDomain, '/');
            return $cdnDomain . '/' . $cleanPath;
        }

        return base_url($cleanPath);
    }
}
