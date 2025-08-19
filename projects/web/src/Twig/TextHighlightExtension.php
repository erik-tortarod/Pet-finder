<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TextHighlightExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('highlight', [$this, 'highlightText'], ['is_safe' => ['html']]),
        ];
    }

    public function highlightText(?string $text, ?string $searchTerm): string
    {
        // Handle null text
        if ($text === null) {
            return '';
        }

        // Handle null or empty search term
        if (empty($searchTerm)) {
            return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }

        // Escape HTML entities in the search term to prevent XSS
        $searchTerm = htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8');

        // Use case-insensitive search and highlight
        $pattern = '/(' . preg_quote($searchTerm, '/') . ')/i';
        $replacement = '<mark class="">$1</mark>';

        return preg_replace($pattern, $replacement, htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
    }
}
