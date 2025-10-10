<?php

class CSRFHelper {
    public static function generateToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyToken(?string $token): bool {
        if (isset($_SESSION['csrf_token']) && $token && hash_equals($_SESSION['csrf_token'], $token)) {
            //unset($_SESSION['csrf_token']); 
            return true;
        }
        return false;
    }

    public static function getTokenInput(): string {
        $token = self::generateToken();
        return "<input type='hidden' name='csrf_token' id='csrf_token' value='{$token}'>";
    }
}