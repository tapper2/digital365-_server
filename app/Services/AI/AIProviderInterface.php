<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    /**
     * Generate 4 marketing hero images for the landing page.
     * Returns array of 4 absolute URLs to the stored images.
     *
     * @param array{
     *     title: string,
     *     text: string,
     *     url: string,
     *     settings: array,
     *     logo_path: string|null,
     *     image_paths: array,
     *     landing_page_id: int
     * } $input
     * @return array<string> Array of 4 absolute image URLs
     */
    public function generateMarketingImages(array $input): array;

    /**
     * Apply a natural-language edit instruction to existing HTML.
     * (Used for legacy HTML-based editing if needed.)
     */
    public function applyEdit(string $currentHtml, string $instruction): string;
}
