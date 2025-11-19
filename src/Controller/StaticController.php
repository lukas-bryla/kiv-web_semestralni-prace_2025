<?php
namespace App\Controller;

use App\Core\BaseController;

class StaticController extends BaseController {
    public function terms($twig) {
        echo $twig->render('static/terms.twig');
    }

    public function privacy($twig) {
        echo $twig->render('static/privacy.twig');
    }
}
