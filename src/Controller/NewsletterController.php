<?php
namespace App\Controller;

use App\Core\BaseController;
use App\Core\Database;

class NewsletterController extends BaseController {
    public function store($twig) {
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            $this->flash('danger', 'Email je povinný');
            $this->redirect('');
        }

        if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
            $this->flash('danger', 'Neplatný formát emailu');
            $this->redirect('');
        }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT IGNORE INTO newsletter_emails (email) VALUES (?)");
        if ($stmt->execute([$email])) {
            $this->flash('success', 'Děkujeme za přihlášení k newsletteru!');
        } else {
            $this->flash('danger', 'Chyba při přihlašování - email už existuje nebo zkuste později');
        }
        $this->redirect('');
    }
}
