<?php
namespace App\Controller;

use App\Model\EventModel;

header('Content-Type: application/json');

class ApiController {
    private $model;

    public function __construct() {
        $this->model = new EventModel();
    }

    public function events() {
        $events = $this->model->getAll(1);
        echo json_encode(['data' => $events], JSON_UNESCAPED_UNICODE);
    }

    public function store() {
        if (empty($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $this->model->create($input);
        http_response_code(201);
        echo json_encode(['status' => 'created']);
    }
}
