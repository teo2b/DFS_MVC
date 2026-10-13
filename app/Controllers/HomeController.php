<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\HomeModel;

final class HomeController
{
    public function index(): void
    {
        $userLogin = $_SESSION['user_login'] ?? null;

        if (is_string($userLogin)) {
            View::render('home/index', [
                'title' => 'Accueil',
                'message' => sprintf('Bienvenue, %s !', $userLogin),
                'csrfToken' => $_SESSION['csrf_token'],
            ]);
            return;
        }

        $model = new HomeModel();

        View::render('home/index', [
            'title' => 'Accueil',
            'message' => $model->getWelcomeMessage(),
            'csrfToken' => null,
        ]);
    }
}
