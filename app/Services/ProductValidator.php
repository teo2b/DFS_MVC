<?php

declare(strict_types=1);

namespace App\Services;

final class ProductValidator
{
    private const ALLOWED_FIELDS = [
        'name',
        'category_id',
        'description',
        'price',
        'promotional_price',
        'stock',
        'image_path',
        'published_on',
    ];

    /**
     * @param array<string, mixed> $input
     * @return array{data: array<string, mixed>|null, errors: array<string, string>}
     */
    public function validate(array $input): array
    {
        $errors = [];
        foreach (array_keys($input) as $field) {
            if (!in_array($field, self::ALLOWED_FIELDS, true)) {
                $errors[$field] = 'Ce champ n’est pas accepté.';
            }
        }

        $name = $input['name'] ?? null;
        if (!is_string($name) || trim($name) === '' || strlen(trim($name)) > 200) {
            $errors['name'] = 'Le nom est obligatoire et doit contenir au maximum 200 caractères.';
        }

        $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if ($categoryId === false) {
            $errors['category_id'] = 'Sélectionnez une catégorie valide.';
        }

        $price = $this->parsePrice($input['price'] ?? null);
        if ($price === null) {
            $errors['price'] = 'Saisissez un prix positif ou nul avec au maximum deux décimales.';
        }

        $promotionalPriceInput = $input['promotional_price'] ?? null;
        $promotionalPrice = null;
        if ($promotionalPriceInput !== null && $promotionalPriceInput !== '') {
            $promotionalPrice = $this->parsePrice($promotionalPriceInput);
            if ($promotionalPrice === null) {
                $errors['promotional_price'] = 'Saisissez un prix promotionnel valide.';
            } elseif ($price !== null && (float) $promotionalPrice > (float) $price) {
                $errors['promotional_price'] = 'Le prix promotionnel ne peut pas dépasser le prix normal.';
            }
        }

        $stock = filter_var($input['stock'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0],
        ]);
        if ($stock === false) {
            $errors['stock'] = 'Le stock doit être un nombre entier positif ou nul.';
        }

        $description = $input['description'] ?? '';
        if (!is_string($description) || strlen($description) > 5000) {
            $errors['description'] = 'La description ne doit pas dépasser 5 000 caractères.';
        }

        $imagePath = $input['image_path'] ?? null;
        if (
            $imagePath !== null
            && (
                !is_string($imagePath)
                || strlen($imagePath) > 255
                || preg_match('~^/uploads/products/[a-f0-9]{32}\.(jpg|png|webp)$~D', $imagePath) !== 1
            )
        ) {
            $errors['image_path'] = 'Le chemin de l’image est invalide.';
        }

        $publishedOn = $input['published_on'] ?? date('Y-m-d');
        if (!is_string($publishedOn) || !$this->isValidDate($publishedOn)) {
            $errors['published_on'] = 'Saisissez une date valide.';
        }

        if ($errors !== []) {
            return ['data' => null, 'errors' => $errors];
        }

        return [
            'data' => [
                'name' => trim($name),
                'category_id' => $categoryId,
                'description' => trim($description) === '' ? null : trim($description),
                'price' => $price,
                'promotional_price' => $promotionalPrice,
                'stock' => $stock,
                'image_path' => $imagePath,
                'published_on' => $publishedOn,
            ],
            'errors' => [],
        ];
    }

    private function parsePrice(mixed $value): ?string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return null;
        }

        $price = (string) $value;
        if (preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $price) !== 1) {
            return null;
        }

        return number_format((float) $price, 2, '.', '');
    }

    private function isValidDate(string $value): bool
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $value, $parts) !== 1) {
            return false;
        }

        return checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1]);
    }

}
