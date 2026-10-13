<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProductModel;
use RuntimeException;

final class CartService
{
    private ProductModel $products;

    public function __construct()
    {
        $this->products = new ProductModel();
    }

    /**
     * @return list<array{product: array<string, mixed>, quantity: int, unit_price_cents: int, line_total_cents: int}>
     */
    public function items(): array
    {
        $items = [];
        $_SESSION['cart'] = $_SESSION['cart'] ?? [];

        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $product = $this->products->find((int) $productId);
            if ($product === null || (int) $product['stock'] === 0) {
                unset($_SESSION['cart'][$productId]);
                $_SESSION['cart_notice'] = 'Un produit indisponible a été retiré du panier.';
                continue;
            }

            if ($quantity > (int) $product['stock']) {
                $quantity = (int) $product['stock'];
                $_SESSION['cart'][$productId] = $quantity;
                $_SESSION['cart_notice'] = 'La quantité a été ajustée au stock disponible.';
            }

            $price = $this->unitPriceCents($product);
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price_cents' => $price,
                'line_total_cents' => $price * $quantity,
            ];
        }

        return $items;
    }

    public function add(int $productId, int $quantity): void
    {
        $product = $this->products->find($productId);
        if ($product === null) {
            throw new RuntimeException('Ce produit n’existe plus.');
        }

        $cart = $_SESSION['cart'] ?? [];
        $newQuantity = ($cart[$productId] ?? 0) + $quantity;
        if ($quantity < 1 || $newQuantity > (int) $product['stock']) {
            throw new RuntimeException(sprintf(
                'Stock insuffisant : il reste %d exemplaire(s) de ce produit.',
                (int) $product['stock']
            ));
        }

        $_SESSION['cart'][$productId] = $newQuantity;
    }

    public function update(int $productId, int $quantity): void
    {
        $product = $this->products->find($productId);
        if ($product === null) {
            unset($_SESSION['cart'][(string) $productId]);
            throw new RuntimeException('Ce produit n’existe plus et a été retiré du panier.');
        }
        if ($quantity < 0 || $quantity > (int) $product['stock']) {
            throw new RuntimeException(sprintf(
                'Stock insuffisant : il reste %d exemplaire(s) de ce produit.',
                (int) $product['stock']
            ));
        }

        if ($quantity === 0) {
            $this->remove($productId);
            return;
        }

        $_SESSION['cart'][(string) $productId] = $quantity;
    }

    public function remove(int $productId): void
    {
        unset($_SESSION['cart'][(string) $productId]);
    }

    public function clear(): void
    {
        $_SESSION['cart'] = [];
    }

    /**
     * @param list<array{product: array<string, mixed>, quantity: int, unit_price_cents: int, line_total_cents: int}> $items
     * @return array{subtotal_cents: int, vat_cents: int, shipping_cents: int, total_cents: int, item_count: int}
     */
    public function totals(array $items): array
    {
        $subtotal = array_sum(array_column($items, 'line_total_cents'));
        $shipping = $subtotal > 0 ? 500 : 0;
        $vat = (int) round(($subtotal + $shipping) * 0.20);

        return [
            'subtotal_cents' => $subtotal,
            'vat_cents' => $vat,
            'shipping_cents' => $shipping,
            'total_cents' => $subtotal + $shipping + $vat,
            'item_count' => array_sum(array_column($items, 'quantity')),
        ];
    }

    private function unitPriceCents(array $product): int
    {
        $price = $this->decimalToCents((string) $product['price']);
        if ($product['promotional_price'] === null) {
            return $price;
        }

        return min($price, $this->decimalToCents((string) $product['promotional_price']));
    }

    private function decimalToCents(string $amount): int
    {
        return (int) round((float) $amount * 100);
    }
}
