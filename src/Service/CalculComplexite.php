<?php

namespace App\Service;

class CalculComplexite
{
    public function calculateComplexity(string $password): int
    {
        $score = 0;

        // Length greater than 8
        if (strlen($password) >= 8) {
            $score += 2;
        }

        // At least one uppercase letter
        if (preg_match('/[A-Z]/', $password)) {
            $score += 3;
        }

        // At least one lowercase letter
        if (preg_match('/[a-z]/', $password)) {
            $score += 3;
        }

        // At least one special character
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += 4;
        }

        return $score;
    }
}
