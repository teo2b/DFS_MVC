<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\UserModel;

final class AuthController
{
    public function showLogin(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            return;
        }

        View::render('auth/login', [
            'title' => 'Connexion',
            'csrfToken' => $this->csrfToken(),
            'error' => null,
            'email' => '',
        ]);
    }

    public function showRegister(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            return;
        }

        $this->renderRegister(null, '', '');
    }

    public function register(): void
    {
        $submittedToken = $_POST['csrf_token'] ?? null;
        if (!is_string($submittedToken) || !hash_equals($this->csrfToken(), $submittedToken)) {
            http_response_code(403);
            $this->renderRegister('La page a expiré. Rechargez-la et réessayez.', '', '');
            return;
        }

        $login = $_POST['login'] ?? null;
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;
        $passwordConfirmation = $_POST['password_confirmation'] ?? null;

        if (
            !is_string($login)
            || !is_string($email)
            || !is_string($password)
            || !is_string($passwordConfirmation)
        ) {
            http_response_code(400);
            $this->renderRegister('Veuillez remplir correctement tous les champs.', '', '');
            return;
        }

        $login = trim($login);
        $email = trim($email);

        if ($login === '' || strlen($login) > 90) {
            http_response_code(400);
            $this->renderRegister('Le pseudo est obligatoire et ne doit pas dépasser 90 caractères.', $login, $email);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
            http_response_code(400);
            $this->renderRegister('Veuillez saisir une adresse courriel valide.', $login, $email);
            return;
        }

        if (
            strlen($password) < 8
            || strlen($password) > 72
            || preg_match('/[a-z]/', $password) !== 1
            || preg_match('/[A-Z]/', $password) !== 1
            || preg_match('/[0-9]/', $password) !== 1
            || preg_match('/[^a-zA-Z0-9]/', $password) !== 1
        ) {
            http_response_code(400);
            $this->renderRegister(
                'Le mot de passe doit contenir 8 à 72 caractères, une minuscule, une majuscule, un chiffre et un caractère spécial.',
                $login,
                $email
            );
            return;
        }

        if ($password !== $passwordConfirmation) {
            http_response_code(400);
            $this->renderRegister('Les mots de passe ne correspondent pas.', $login, $email);
            return;
        }

        $model = new UserModel();
        if ($model->existsByLoginOrEmail($login, $email)) {
            http_response_code(409);
            $this->renderRegister(
                'Ce pseudo ou cette adresse courriel est déjà utilisé.',
                $login,
                $email
            );
            return;
        }

        $userId = $model->create(
            $login,
            password_hash($password, PASSWORD_DEFAULT),
            $email
        );

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_login'] = $login;

        header('Location: /');
    }

    public function login(): void
    {
        $submittedToken = $_POST['csrf_token'] ?? null;
        if (!is_string($submittedToken) || !hash_equals($this->csrfToken(), $submittedToken)) {
            http_response_code(403);
            $this->renderLogin('La page a expiré. Rechargez-la et réessayez.', '');
            return;
        }

        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;
        if (!is_string($email) || !is_string($password) || trim($email) === '' || $password === '') {
            http_response_code(400);
            $this->renderLogin('Veuillez saisir votre adresse courriel et votre mot de passe.', '');
            return;
        }

        $email = trim($email);
        $user = (new UserModel())->findByEmail($email);

        if ($user === null || !password_verify($password, $user['user_password'])) {
            http_response_code(401);
            $this->renderLogin('Adresse courriel ou mot de passe incorrect.', $email);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['user_id'];
        $_SESSION['user_login'] = $user['user_login'];

        header('Location: /');
    }

    public function logout(): void
    {
        $submittedToken = $_POST['csrf_token'] ?? null;
        if (!is_string($submittedToken) || !hash_equals($this->csrfToken(), $submittedToken)) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Accès refusé']);
            return;
        }

        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: /login');
    }

    private function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    private function renderLogin(string $error, string $email): void
    {
        View::render('auth/login', [
            'title' => 'Connexion',
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'email' => $email,
        ]);
    }

    private function renderRegister(?string $error, string $login, string $email): void
    {
        View::render('auth/register', [
            'title' => 'Créer un utilisateur',
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'login' => $login,
            'email' => $email,
        ]);
    }
}
