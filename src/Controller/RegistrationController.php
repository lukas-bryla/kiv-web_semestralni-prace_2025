<?php
namespace App\Controller;

use App\Core\BaseController;
use App\Model\EventModel;
use App\Model\RegistrationModel;

class RegistrationController extends BaseController {
    public function register($twig, $eventId) {
        if (!$this->isRole('user')) {
            $this->flash('danger', 'Pro registraci musíš být přihlášen.');
            $this->redirect('login');
        }

        $eventModel = new EventModel();
        $event = $eventModel->find((int)$eventId);
        if (!$event || !$event['approved']) {
            $this->flash('danger', 'Akce není dostupná pro registraci.');
            $this->redirect('events');
        }

        $registrationModel = new RegistrationModel();
        $userId = (int)$_SESSION['user']['id'];

        if ($registrationModel->isRegistered((int)$eventId, $userId)) {
            $this->flash('info', 'Na tuto akci už jsi registrován.');
            $this->redirect('event/' . (int)$eventId);
        }

        $ok = $registrationModel->register((int)$eventId, $userId, (int)$event['capacity']);
        if ($ok) {
            $this->flash('success', 'Úspěšně jsi se registroval na akci.');
        } else {
            $this->flash('danger', 'Kapacita akce je již naplněná.');
        }

        $this->redirect('event/' . (int)$eventId);
    }
}
