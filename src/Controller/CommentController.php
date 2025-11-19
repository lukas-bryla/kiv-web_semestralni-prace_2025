<?php
namespace App\Controller;

use App\Core\BaseController;
use App\Model\CommentModel;

class CommentController extends BaseController {
    public function store($twig, $eventId) {
        if (!$this->isRole('user')) {
            $this->flash('danger', 'Pro komentování musíš být přihlášen.');
            $this->redirect('login');
        }

        $content = trim($_POST['content'] ?? '');

        if ($content === '') {
            $this->flash('danger', 'Komentář nesmí být prázdný.');
            $this->redirect('event/' . (int)$eventId);
        }

        $model = new CommentModel();
        if ($model->create((int)$eventId, (int)$_SESSION['user']['id'], $content)) {
            $this->flash('success', 'Komentář byl odeslán ke schválení moderátorovi.');
        } else {
            $this->flash('danger', 'Komentář se nepodařilo uložit, zkus to prosím znovu.');
        }

        $this->redirect('event/' . (int)$eventId);
    }
}
