<?php
namespace App\Controller;

use App\Core\BaseController;

class HomeController extends BaseController {
    public function index($twig) {
        echo $twig->render('home/index.twig');
    }
}
