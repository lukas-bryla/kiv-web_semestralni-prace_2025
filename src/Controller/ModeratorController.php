<?php
namespace App\Controller;

use App\Core\BaseController;
use App\Model\EventModel;
use App\Model\CommentModel;

class ModeratorController extends BaseController {
    public function index($twig) {
        if (!$this->isRole('moderator')) {
            $this->flash('danger', 'Nemáš oprávnění vstoupit do moderátorské sekce.');
            $this->redirect('');
        }

        $eventModel = new EventModel();
        $commentModel = new CommentModel();

        $pendingEvents = $eventModel->getAll(0);
        $pendingComments = $commentModel->getPending();

        echo $twig->render('moderator/index.twig', [
            'pendingEvents' => $pendingEvents,
            'pendingComments' => $pendingComments,
        ]);
    }

    public function approveEvent($twig, $id) {
        if (!$this->isRole('moderator')) {
            $this->redirect('');
        }

        $eventModel = new EventModel();
        if ($eventModel->approve($id)) {
            $this->flash('success', 'Akce byla schválena.');
        } else {
            $this->flash('danger', 'Chyba při schvalování akce.');
        }
        $this->redirect('moderator');
    }

    public function rejectEvent($twig, $id) {
        if (!$this->isRole('moderator')) {
            $this->redirect('');
        }

        $eventModel = new EventModel();
        if ($eventModel->delete($id)) {
            $this->flash('success', 'Akce byla odmítnuta a smazána.');
        } else {
            $this->flash('danger', 'Chyba při mazání akce.');
        }
        $this->redirect('moderator');
    }

    public function approveComment($twig, $id) {
        if (!$this->isRole('moderator')) {
            $this->redirect('');
        }

        $commentModel = new CommentModel();
        if ($commentModel->setStatus((int)$id, 1)) {
            $this->flash('success', 'Komentář byl schválen.');
        } else {
            $this->flash('danger', 'Chyba při schvalování komentáře.');
        }
        $this->redirect('moderator');
    }

    public function rejectComment($twig, $id) {
        if (!$this->isRole('moderator')) {
            $this->redirect('');
        }

        $commentModel = new CommentModel();
        if ($commentModel->setStatus((int)$id, 2)) {
            $this->flash('success', 'Komentář byl skryt.');
        } else {
            $this->flash('danger', 'Chyba při skrývání komentáře.');
        }
        $this->redirect('moderator');
    }
}
