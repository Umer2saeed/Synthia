<?php

namespace App\Services;

class SpamFilterService
{
    /*
    | Keywords that trigger auto-hold.
    | In D6 (Settings) these will be configurable from the admin UI.
    | For now they live here as a constant.
    */
    private array $keywords = [
        'buy cheap',
        'click here',
        'free money',
        'make money fast',
        'work from home',
        'earn $',
        'casino',
        'viagra',
        'bitcoin investment',
        'whatsapp me',
        'call me on',
        'http://',  // bare HTTP links are often spam
    ];

    public function isSpam(string $content): bool
    {
        $lower = strtolower($content);

        foreach ($this->keywords as $keyword) {
            if (str_contains($lower, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }
}
