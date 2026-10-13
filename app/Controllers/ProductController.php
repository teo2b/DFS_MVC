<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ProductImage;
use App\Core\View;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Services\ProductValidator;
use RuntimeException;

final class ProductController
{
    public function index(): void
    {
        $search = $_GET['q'] ?? '';
        $categoryId = filter_var($_GET['category_id'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        $cartNotice = $_SESSION['cart_notice'] ?? null;
        $cartError = $_SESSION['cart_error'] ?? null;
        unset($_SESSION['cart_notice'], $_SESSION['cart_error']);

        View::render('products/index', [
            'title' => 'Catalogue des produits',
            'products' => (new ProductModel())->all(
                is_string($search) ? trim($search) : null,
                $categoryId === false ? null : $categoryId
            ),
            'categories' => (new CategoryModel())->all(),
            'search' => is_string($search) ? $search : '',
            'selectedCategory' => $categoryId === false ? '' : (string) $categoryId,
            'userLogin' => $_SESSION['user_login'] ?? null,
            'csrfToken' => $this->csrfToken(),
            'notice' => isset($_GET['saved']) ? 'Le produit a été enregistré.' : $cartNotice,
            'error' => is_string($cartError) ? $cartError : null,
        ]);
    }

    public function create(): void
    {
        if (!$this->requireLogin()) {
            return;
        }

        $this->renderForm('Nouveau produit', $this->emptyProduct(), [], 'create');
    }

    public function store(): void
    {
        if (!$this->requireLogin()) {
            return;
        }
        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm('Nouveau produit', $this->formProduct(), ['csrf_token' => 'La page a expiré. Rechargez-la.'], 'create');
            return;
        }

        $product = $this->formProduct();
        $validation = (new ProductValidator())->validate($product);
        if ($validation['data'] === null) {
            http_response_code(400);
            $this->renderForm('Nouveau produit', $product, $validation['errors'], 'create');
            return;
        }

        $model = new ProductModel();
        if (!$model->categoryExists($validation['data']['category_id'])) {
            http_response_code(400);
            $this->renderForm('Nouveau produit', $product, ['category_id' => 'Cette catégorie n’existe pas.'], 'create');
            return;
        }

        $imagePath = null;
        try {
            $imagePath = ProductImage::save($_FILES['image'] ?? []);
        } catch (RuntimeException $error) {
            http_response_code(400);
            $this->renderForm('Nouveau produit', $product, ['image' => $error->getMessage()], 'create');
            return;
        }

        $validation['data']['image_path'] = $imagePath;
        try {
            $model->create($validation['data']);
        } catch (\Throwable $error) {
            ProductImage::remove($imagePath);
            throw $error;
        }

        header('Location: /products?saved=1');
    }

    public function edit(string $id): void
    {
        if (!$this->requireLogin()) {
            return;
        }

        $productId = $this->parseId($id);
        $product = $productId === null ? null : (new ProductModel())->find($productId);
        if ($product === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Produit introuvable']);
            return;
        }

        $this->renderForm('Modifier un produit', $product, [], 'edit', $productId);
    }

    public function update(string $id): void
    {
        if (!$this->requireLogin()) {
            return;
        }

        $productId = $this->parseId($id);
        $model = new ProductModel();
        $existing = $productId === null ? null : $model->find($productId);
        if ($productId === null || $existing === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Produit introuvable']);
            return;
        }

        $product = array_merge([
            'name' => $existing['name'],
            'category_id' => $existing['category_id'],
            'description' => $existing['description'],
            'price' => $existing['price'],
            'promotional_price' => $existing['promotional_price'],
            'stock' => $existing['stock'],
            'image_path' => $existing['image_path'],
            'published_on' => $existing['published_on'],
        ], $this->formProduct());
        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm('Modifier un produit', $product, ['csrf_token' => 'La page a expiré. Rechargez-la.'], 'edit', $productId);
            return;
        }

        $validation = (new ProductValidator())->validate($product);
        if ($validation['data'] === null) {
            http_response_code(400);
            $this->renderForm('Modifier un produit', $product, $validation['errors'], 'edit', $productId);
            return;
        }
        if (!$model->categoryExists($validation['data']['category_id'])) {
            http_response_code(400);
            $this->renderForm('Modifier un produit', $product, ['category_id' => 'Cette catégorie n’existe pas.'], 'edit', $productId);
            return;
        }

        $oldImage = $existing['image_path'];
        $newImage = null;
        try {
            $newImage = ProductImage::save($_FILES['image'] ?? []);
        } catch (RuntimeException $error) {
            http_response_code(400);
            $this->renderForm('Modifier un produit', $product, ['image' => $error->getMessage()], 'edit', $productId);
            return;
        }

        $validation['data']['image_path'] = $newImage ?? $oldImage;
        try {
            $model->update($productId, $validation['data']);
        } catch (\Throwable $error) {
            ProductImage::remove($newImage);
            throw $error;
        }
        if ($newImage !== null) {
            ProductImage::remove($oldImage);
        }

        header('Location: /products?saved=1');
    }

    public function delete(string $id): void
    {
        if (!$this->requireLogin()) {
            return;
        }
        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Accès refusé']);
            return;
        }

        $productId = $this->parseId($id);
        $model = new ProductModel();
        $product = $productId === null ? null : $model->find($productId);
        if ($product === null || $productId === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Produit introuvable']);
            return;
        }

        $model->delete($productId);
        ProductImage::remove($product['image_path']);
        header('Location: /products?saved=1');
    }

    /**
     * @param array<string, mixed> $product
     * @param array<string, string> $errors
     */
    private function renderForm(string $title, array $product, array $errors, string $mode, ?int $id = null): void
    {
        View::render('products/form', [
            'title' => $title,
            'product' => $product,
            'categories' => (new CategoryModel())->all(),
            'errors' => $errors,
            'csrfToken' => $this->csrfToken(),
            'mode' => $mode,
            'productId' => $id,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyProduct(): array
    {
        return [
            'name' => '',
            'category_id' => '',
            'description' => '',
            'price' => '',
            'promotional_price' => '',
            'stock' => '0',
            'image_path' => null,
            'published_on' => date('Y-m-d'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formProduct(): array
    {
        $fields = ['name', 'category_id', 'description', 'price', 'promotional_price', 'stock', 'published_on'];
        $product = [];
        foreach ($fields as $field) {
            $product[$field] = $_POST[$field] ?? '';
        }

        return $product;
    }

    private function requireLogin(): bool
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return false;
        }

        return true;
    }

    private function hasValidCsrfToken(): bool
    {
        $submittedToken = $_POST['csrf_token'] ?? null;

        return is_string($submittedToken)
            && hash_equals($this->csrfToken(), $submittedToken);
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
}
