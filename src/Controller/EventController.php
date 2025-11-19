<?php
namespace App\Controller;

use App\Core\BaseController;
use App\Model\EventModel;
use App\Model\CommentModel;
use App\Model\RegistrationModel;
use App\Services\UploadService;
use DateTime;

class EventController extends BaseController {
    public function index($twig) {
        $model = new EventModel();

        $query = trim($_GET['q'] ?? '');
        $tagIds = array_filter(array_map('intval', $_GET['tags'] ?? []));
        $onlyUpcoming = isset($_GET['upcoming']) && $_GET['upcoming'] === '1';

        $filters = [
            'query' => $query,
            'tag_ids' => $tagIds,
            'only_upcoming' => $onlyUpcoming,
        ];

        $events = $model->getAll(1, $filters);
        $allTags = $model->getAllTags();

        $registeredEventIds = [];
        if (!empty($_SESSION['user']['id'])) {
            $registrationModel = new RegistrationModel();
            $userEvents = $registrationModel->getEventsForUser((int)$_SESSION['user']['id']);
            foreach ($userEvents as $e) {
                if (isset($e['id'])) {
                    $registeredEventIds[] = (int)$e['id'];
                }
            }
        }

        echo $twig->render('event/index.twig', [
            'events' => $events,
            'filters' => $filters,
            'allTags' => $allTags,
            'registeredEventIds' => $registeredEventIds,
        ]);
    }

    public function create($twig) {
        if (!$this->isRole('user')) {
            $this->redirect('');
        }

        $model = new EventModel();
        $allTags = $model->getAllTags();

        echo $twig->render('event/create.twig', [
            'allTags' => $allTags,
        ]);
    }

    public function store($twig) {
        if (!$this->isRole('user')) {
            $this->redirect('');
        }

        if (
            empty($_POST['title']) ||
            empty($_POST['description']) ||
            empty($_POST['date']) ||
            empty($_POST['capacity']) ||
            empty($_POST['location'])
        ) {
            $this->flash('danger', 'Vyplň všechny povinné položky');
            $this->redirect('event/create');
        }

        $date = DateTime::createFromFormat('Y-m-d\TH:i', $_POST['date']);
        if (!$date) {
            $this->flash('danger', 'Neplatný formát data');
            $this->redirect('event/create');
        }
        $dateString = $date->format('Y-m-d H:i:s');

        $tagIds = array_filter(array_map('intval', $_POST['tags'] ?? []));

        $image = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = UploadService::upload($_FILES['image']);
            if (!$image) {
                $this->flash('danger', 'Nepovedlo se nahrát obrázek (špatný typ nebo velikost)');
                $this->redirect('event/create');
            }
        }

        $data = [
            'title'       => trim($_POST['title']),
            'description' => $_POST['description'],
            'date'        => $dateString,
            'capacity'    => (int)$_POST['capacity'],
            'location'    => trim($_POST['location']),
        ];

        $model = new EventModel();
        if ($model->create($data, $image, $tagIds)) {
            $this->flash('success', 'Akce byla úspěšně odeslána ke schválení!');
            $this->redirect('events');
        } else {
            $this->flash('danger', 'Chyba při ukládání akce');
            $this->redirect('event/create');
        }
    }

    public function show($twig, $id) {
        $model = new EventModel();
        $event = $model->find($id);
        if (!$event) {
            http_response_code(404);
            echo $twig->render('errors/404.twig');
            return;
        }

        $commentModel = new CommentModel();
        $comments = $commentModel->getApprovedForEvent((int)$id);

        $registrationModel = new RegistrationModel();
        $isRegistered = false;
        if (!empty($_SESSION['user']['id'])) {
            $isRegistered = $registrationModel->isRegistered((int)$id, (int)$_SESSION['user']['id']);
        }

        $registrations = [];
        if ($this->isRole('moderator')) {
            $registrations = $registrationModel->getUsersForEvent((int)$id);
        }

        echo $twig->render('event/show.twig', [
            'event' => $event,
            'comments' => $comments,
            'isRegistered' => $isRegistered,
            'registrations' => $registrations,
        ]);
    }

    public function myEvents($twig) {
        if (empty($_SESSION['user'])) {
            $this->redirect('login');
        }

        $registrationModel = new RegistrationModel();
        $events = $registrationModel->getEventsForUser((int)$_SESSION['user']['id']);

        echo $twig->render('event/my-events.twig', [
            'events' => $events,
        ]);
    }
}
