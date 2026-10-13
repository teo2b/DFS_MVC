<section class="cart-page">
    <div class="page-heading">
        <div>
            <p class="eyebrow">Votre sélection</p>
            <h1>Mon panier</h1>
            <p class="intro">Les prix et la disponibilité sont revérifiés depuis le catalogue.</p>
        </div>
        <a class="button-link secondary-button" href="/products">Continuer mes achats</a>
    </div>

    <?php if ($notice !== null): ?>
        <p class="success-message" role="status"><?= e($notice) ?></p>
    <?php endif; ?>
    <?php if ($error !== null): ?>
        <p class="form-error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($items === []): ?>
        <div class="empty-state">
            <h2>Votre panier est vide</h2>
            <p>Parcourez le catalogue pour ajouter des produits.</p>
            <a class="button-link" href="/products">Voir les produits</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($items as $item): ?>
                    <?php $product = $item['product']; ?>
                    <article class="cart-item">
                        <?php if ($product['image_path'] !== null): ?>
                            <img
                                class="cart-item-image"
                                src="<?= e($product['image_path']) ?>"
                                alt=""
                            >
                        <?php else: ?>
                            <div class="cart-item-image product-placeholder" aria-hidden="true">Image à ajouter</div>
                        <?php endif; ?>
                        <div class="cart-item-details">
                            <span class="category-badge"><?= e($product['category_name']) ?></span>
                            <h2><?= e($product['name']) ?></h2>
                            <p class="stock-count">
                                <?= number_format($item['unit_price_cents'] / 100, 2, ',', ' ') ?> € HT l’unité
                                · Stock disponible : <?= (int) $product['stock'] ?>
                            </p>
                        </div>
                        <div class="cart-item-controls">
                            <form action="/cart/<?= (int) $product['product_id'] ?>/update" method="post" class="quantity-form">
                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                <label class="visually-hidden" for="cart-quantity-<?= (int) $product['product_id'] ?>">Quantité de <?= e($product['name']) ?></label>
                                <input
                                    id="cart-quantity-<?= (int) $product['product_id'] ?>"
                                    name="quantity"
                                    type="number"
                                    min="0"
                                    max="<?= (int) $product['stock'] ?>"
                                    value="<?= $item['quantity'] ?>"
                                    required
                                >
                                <button class="secondary-button" type="submit">Mettre à jour</button>
                            </form>
                            <form action="/cart/<?= (int) $product['product_id'] ?>/remove" method="post">
                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                <button class="danger-button" type="submit">Retirer</button>
                            </form>
                        </div>
                        <strong class="cart-line-total"><?= number_format($item['line_total_cents'] / 100, 2, ',', ' ') ?> € HT</strong>
                    </article>
                <?php endforeach; ?>
                <form action="/cart/clear" method="post" class="clear-cart-form">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <button class="danger-button" type="submit">Vider le panier</button>
                </form>
            </div>

            <aside class="cart-summary" aria-labelledby="summary-title">
                <h2 id="summary-title">Récapitulatif</h2>
                <dl>
                    <div>
                        <dt>Articles (<?= $totals['item_count'] ?>)</dt>
                        <dd><?= number_format($totals['subtotal_cents'] / 100, 2, ',', ' ') ?> € HT</dd>
                    </div>
                    <div>
                        <dt>Livraison</dt>
                        <dd><?= number_format($totals['shipping_cents'] / 100, 2, ',', ' ') ?> € HT</dd>
                    </div>
                    <div>
                        <dt>TVA (20 %)</dt>
                        <dd><?= number_format($totals['vat_cents'] / 100, 2, ',', ' ') ?> €</dd>
                    </div>
                    <div class="cart-total">
                        <dt>Total TTC</dt>
                        <dd><?= number_format($totals['total_cents'] / 100, 2, ',', ' ') ?> €</dd>
                    </div>
                </dl>
                <p class="field-hint">La livraison est forfaitaire à 5,00 € HT. Les montants sont recalculés sur le serveur.</p>
            </aside>
        </div>
    <?php endif; ?>
</section>
