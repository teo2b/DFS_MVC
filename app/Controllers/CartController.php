<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Services\CartService;
use RuntimeException;

final class CartController
{
    public function index(): void
    {
        $cart = new CartService();
        $items = $cart->items();

        View::render('cart/index', [
            'title' => 'Mon panier',
            'items' => $items,
            'totals' => $cart->totals($items),
            'csrfToken' => $this->csrfToken(),
            'notice' => $this->consumeFlash('cart_notice'),
            'error' => $this->consumeFlash('cart_error'),
        ]);
    }

    public function add(string $id): void
    {
        if (!$this->verifyCsrfToken()) {
            return;
        }

        $productId = $this->parseId($id);
        $quantity = filter_var($_POST['quantity'] ?? '1', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if ($productId === null || $quantity === false) {
            $this->flashError('Produit ou quantité invalide.');
            header('Location: /products');
            return;
        }

        try {
            (new CartService())->add($productId, $quantity);
        } catch (RuntimeException $error) {
            $this->flashError($error->getMessage());
            header('Location: /products');
            return;
        }

        $_SESSION['cart_notice'] = 'Le produit a été ajouté au panier.';
        header('Location: /cart');
    }

    public function update(string $id): void
    {
        if (!$this->verifyCsrfToken()) {
            return;
        }

        $productId = $this->parseId($id);
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0],
        ]);
        if ($productId === null || $quantity === false) {
            $this->flashError('Produit ou quantité invalide.');
            header('Location: /cart');
            return;
        }

        try {
            (new CartService())->update($productId, $quantity);
        } catch (RuntimeException $error) {
            $this->flashError($error->getMessage());
            header('Location: /cart');
            return;
        }

        $_SESSION['cart_notice'] = $quantity === 0
            ? 'Le produit a été retiré du panier.'
            : 'La quantité a été mise à jour.';
        header('Location: /cart');
    }

    public function remove(string $id): void
    {
        if (!$this->verifyCsrfToken()) {
            return;
        }

        $productId = $this->parseId($id);
        if ($productId === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Produit introuvable']);
            return;
        }

        (new CartService())->remove($productId);
        $_SESSION['cart_notice'] = 'Le produit a été retiré du panier.';
        header('Location: /cart');
    }

    public function clear(): void
    {
        if (!$this->verifyCsrfToken()) {
            return;
        }

        (new CartService())->clear();
        $_SESSION['cart_notice'] = 'Le panier a été vidé.';
        header('Location: /cart');
    }

    private function verifyCsrfToken(): bool
    {
        $token = $_POST['csrf_token'] ?? null;
        if (is_string($token) && hash_equals($this->csrfToken(), $token)) {
            return true;
        }

        http_response_code(403);
        View::render('errors/403', ['title' => 'Accès refusé']);
        return false;
    }

    private function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    private function parseId(string $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : $id;
    }

    private function flashError(string $message): void
    {
        $_SESSION['cart_error'] = $message;
    }

    private function consumeFlash(string $key): ?string
    {
        $message = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);

        return is_string($message) ? $message : null;
    }
}
