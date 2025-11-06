<?php

// =============================================================================
// FILE: app/Services/ShortCodeGenerator.php
// Purpose: Generate unique short codes with collision handling
// =============================================================================

namespace App\Services;

use App\Models\Link;
use Illuminate\Support\Str;

class ShortCodeGenerator
{
    /**
     * Characters allowed in short codes
     * Using base62: a-z, A-Z, 0-9 (62 characters)
     */
    private const CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    /**
     * Default length of generated short codes
     */
    private const DEFAULT_LENGTH = 6;
    
    /**
     * Maximum attempts to generate a unique code before giving up
     */
    private const MAX_ATTEMPTS = 10;
    
    /**
     * Reserved words that cannot be used as short codes
     */
    private const RESERVED_WORDS = [
        'admin', 'api', 'dashboard', 'login', 'register', 'logout',
        'profile', 'settings', 'home', 'about', 'contact', 'help',
        'terms', 'privacy', 'docs', 'blog', 'app', 'www', 'mail',
        'static', 'assets', 'storage', 'public', 'qr', 'analytics',
    ];

    /**
     * Generate a random unique short code
     * 
     * Algorithm: Random selection from base62 character set
     * Collision handling: Check database and retry up to MAX_ATTEMPTS
     * 
     * @param int $length Length of the code (default 6)
     * @return string Unique short code
     * @throws \RuntimeException If unable to generate unique code
     */
    public function generate(int $length = self::DEFAULT_LENGTH): string
    {
        $attempts = 0;
        
        do {
            $code = $this->generateRandomCode($length);
            $attempts++;
            
            // Check if code is unique in database
            if (!$this->codeExists($code)) {
                return $code;
            }
            
        } while ($attempts < self::MAX_ATTEMPTS);
        
        // If we couldn't generate a unique code, increase length and try once more
        return $this->generate($length + 1);
    }

    /**
     * Validate a custom alias
     * 
     * @param string $alias Custom alias to validate
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validateCustomAlias(string $alias): array
    {
        // Check length
        if (strlen($alias) < 3 || strlen($alias) > 50) {
            return [
                'valid' => false,
                'error' => 'Alias must be between 3 and 50 characters'
            ];
        }
        
        // Check format: alphanumeric and hyphens only
        if (!preg_match('/^[a-zA-Z0-9-]+$/', $alias)) {
            return [
                'valid' => false,
                'error' => 'Alias can only contain letters, numbers, and hyphens'
            ];
        }
        
        // Check for reserved words
        if (in_array(strtolower($alias), self::RESERVED_WORDS)) {
            return [
                'valid' => false,
                'error' => 'This alias is reserved and cannot be used'
            ];
        }
        
        // Check if already exists in database
        if ($this->codeExists($alias)) {
            return [
                'valid' => false,
                'error' => 'This alias is already taken'
            ];
        }
        
        return ['valid' => true, 'error' => null];
    }

    /**
     * Generate a random code of specified length
     * 
     * @param int $length
     * @return string
     */
    private function generateRandomCode(int $length): string
    {
        $code = '';
        $maxIndex = strlen(self::CHARACTERS) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $code .= self::CHARACTERS[random_int(0, $maxIndex)];
        }
        
        return $code;
    }

    /**
     * Check if a code already exists in database
     * 
     * @param string $code
     * @return bool
     */
    private function codeExists(string $code): bool
    {
        return Link::where('short_code', $code)
                ->orWhere('custom_alias', $code)
                ->exists();
    }

    /**
     * Get list of reserved words (for frontend validation)
     * 
     * @return array
     */
    public static function getReservedWords(): array
    {
        return self::RESERVED_WORDS;
    }
}