<?php
namespace App\Controller;

use App\Core\BaseController;
use App\Model\UserModel;
use App\Model\EventModel;

class AdminController extends BaseController {
    public function index($twig) {
        if (!$this->isRole('admin')) {
            $this->flash('danger', 'Nemáš oprávnění vstoupit do administrace.');
            $this->redirect('');
        }

        $userModel = new UserModel();
        $eventModel = new EventModel();

        $data = [
            'users' => $userModel->getAll(),
            'pendingEvents' => $eventModel->getAll(0),
        ];

        echo $twig->render('admin/index.twig', $data);
    }

    public function approve($twig, $id) {
        if (!$this->isRole('admin')) {
            $this->redirect('');
        }

        $eventModel = new EventModel();
        if ($eventModel->approve($id)) {
            $this->flash('success', 'Akce byla schválena.');
        } else {
            $this->flash('danger', 'Chyba při schvalování akce.');
        }
        $this->redirect('admin');
    }

    public function reject($twig, $id) {
        if (!$this->isRole('admin')) {
            $this->redirect('');
        }

        $eventModel = new EventModel();
        if ($eventModel->delete($id)) {
            $this->flash('success', 'Akce byla odmítnuta a smazána.');
        } else {
            $this->flash('danger', 'Chyba při mazání akce.');
        }
        $this->redirect('admin');
    }

    public function role($twig, $id) {
        if (!$this->isRole('admin')) {
            $this->flash('danger', 'Nemáš oprávnění měnit role.');
            $this->redirect('admin');
        }

        $currentUser = $_SESSION['user'] ?? null;
        $currentRole = $currentUser['role'] ?? 'guest';

        $newRole = $_POST['new_role'] ?? '';
        $allowedRoles = ['user', 'moderator', 'admin'];

        if (!in_array($newRole, $allowedRoles, true)) {
            $this->flash('danger', 'Neplatná role.');
            $this->redirect('admin');
        }

        $userModel = new UserModel();
        $targetUser = $userModel->findById($id);

        if ((int)$id === 1) {
            $this->flash('danger', 'Roli superadmina nelze měnit.');
            $this->redirect('admin');
        }

        if ($currentRole === 'admin') {
            if ($targetUser && $targetUser['role'] === 'admin') {
                $this->flash('danger', 'Roli administrátora může spravovat pouze superadmin.');
                $this->redirect('admin');
            }
            if ($newRole === 'admin') {
                $this->flash('danger', 'Na roli administrátora může povyšovat pouze superadmin.');
                $this->redirect('admin');
            }
        }

        if ($userModel->updateRole($id, $newRole)) {
            $this->flash('success', 'Role uživatele byla změněna.');
        } else {
            $this->flash('danger', 'Nelze změnit roli tohoto uživatele.');
        }

        $this->redirect('admin');
    }

    public function delete($twig, $id) {
        if (!$this->isRole('admin')) {
            $this->flash('danger', 'Nemáš oprávnění mazat uživatele.');
            $this->redirect('admin');
        }

        $userModel = new UserModel();
        $currentUser = $_SESSION['user'] ?? null;
        $currentRole = $currentUser['role'] ?? 'guest';
        $targetUser = $userModel->findById($id);

        if ((int)$id === 1) {
            $this->flash('danger', 'Superadmina nelze smazat.');
            $this->redirect('admin');
        }

        if ($currentRole === 'admin' && $targetUser && $targetUser['role'] === 'admin') {
            $this->flash('danger', 'Administrátory může mazat pouze superadmin.');
            $this->redirect('admin');
        }

        if ($userModel->delete($id)) {
            $this->flash('success', 'Uživatel byl smazán.');
        } else {
            $this->flash('danger', 'Nelze smazat tohoto uživatele.');
        }

        $this->redirect('admin');
    }
}
