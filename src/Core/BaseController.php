<?php
namespace App\Core;

abstract class BaseController {
    protected function redirect($url) {
        header("Location: /$url");
        exit;
    }

    protected function flash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }

    protected function isRole($requiredRole) {
        $roles = ['user' => 1, 'moderator' => 2, 'admin' => 3, 'superadmin' => 4];
        $userRole = $_SESSION['user']['role'] ?? 'guest';
        return isset($roles[$userRole]) && $roles[$userRole] >= $roles[$requiredRole];
    }
}
