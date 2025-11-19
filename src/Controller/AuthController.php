<?php
namespace App\Controller;

use App\Core\BaseController;
use App\Model\UserModel;

class AuthController extends BaseController {
    public function showLogin($twig) {
        echo $twig->render('auth/login.twig');
    }

    public function showRegister($twig) {
        echo $twig->render('auth/register.twig');
    }

    public function login($twig) {
        $userModel = new UserModel();
        $user = $userModel->findByUsername($_POST['username']);

        if ($user && password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user'] = $user;
            $this->flash('success', 'Vítej zpět, ' . $user['username'] . '!');
            $this->redirect('');
        } else {
            $this->flash('danger', 'Špatné přihlašovací údaje');
            $this->redirect('login');
        }
    }

    public function register($twig) {
        $userModel = new UserModel();

        if ($_POST['password'] !== $_POST['password2']) {
            $this->flash('danger', 'Hesla se neshodují');
            $this->redirect('register');
        }

        if ($userModel->create($_POST['username'], $_POST['email'], $_POST['password'])) {
            $this->flash('success', 'Registrace úspěšná – teď se přihlaš!');
            $this->redirect('login');
        } else {
            $this->flash('danger', 'Uživatelské jméno nebo email už existuje');
            $this->redirect('register');
        }
    }

    public function logout() {
        unset($_SESSION['user']);
        $this->flash('success', 'Byl jsi odhlášen');
        $this->redirect('');
    }
}
