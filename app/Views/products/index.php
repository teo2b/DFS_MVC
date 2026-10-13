<section class="catalog">
    <div class="page-heading">
        <div>
            <p class="eyebrow">Catalogue</p>
            <h1>Produits</h1>
            <p class="intro">Retrouvez et gérez les produits du catalogue.</p>
        </div>
        <?php if (is_string($userLogin)): ?>
            <a class="button-link" href="/products/create">Ajouter un produit</a>
        <?php else: ?>
            <a class="button-link" href="/login">Se connecter pour gérer les produits</a>
        <?php endif; ?>
    </div>

    <?php if ($notice !== null): ?>
        <p class="success-message" role="status"><?= e($notice) ?></p>
    <?php endif; ?>
    <?php if ($error !== null): ?>
        <p class="form-error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form class="filters" action="/products" method="get">
        <label for="search">Rechercher un produit</label>
        <input id="search" name="q" type="search" value="<?= e($search) ?>" placeholder="Nom ou description">
        <label for="category-filter">Catégorie</label>
        <select id="category-filter" name="category_id">
            <option value="">Toutes les catégories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['category_id'] ?>" <?= $selectedCategory === (string) $category['category_id'] ? 'selected' : '' ?>>
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filtrer</button>
    </form>

    <?php if ($products === []): ?>
        <p class="empty-state">Aucun produit à afficher pour le moment.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <?php if ($product['image_path'] !== null): ?>
                        <img
                            class="product-image"
                            src="<?= e($product['image_path']) ?>"
                            alt="<?= e($product['name']) ?>"
                        >
                    <?php else: ?>
                        <div class="product-image product-placeholder" aria-hidden="true">Image à ajouter</div>
                    <?php endif; ?>
                    <div class="product-card-content">
                        <span class="category-badge"><?= e($product['category_name']) ?></span>
                        <h2><?= e($product['name']) ?></h2>
                        <?php if ($product['description'] !== null): ?>
                            <p class="product-description"><?= e($product['description']) ?></p>
                        <?php endif; ?>
                        <p class="product-price">
                            <?php if ($product['promotional_price'] !== null): ?>
                                <del><?= number_format((float) $product['price'], 2, ',', ' ') ?> €</del>
                                <strong><?= number_format((float) $product['promotional_price'], 2, ',', ' ') ?> €</strong>
                            <?php else: ?>
                                <strong><?= number_format((float) $product['price'], 2, ',', ' ') ?> €</strong>
                            <?php endif; ?>
                        </p>
                        <p class="stock-count">Stock : <?= (int) $product['stock'] ?></p>
                        <form class="add-to-cart-form" action="/cart/add/<?= (int) $product['product_id'] ?>" method="post">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <label class="visually-hidden" for="quantity-<?= (int) $product['product_id'] ?>">Quantité de <?= e($product['name']) ?></label>
                            <input
                                id="quantity-<?= (int) $product['product_id'] ?>"
                                name="quantity"
                                type="number"
                                min="1"
                                max="<?= (int) $product['stock'] ?>"
                                value="1"
                                aria-label="Quantité"
                                <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>
                            >
                            <button type="submit" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>
                                <?= (int) $product['stock'] < 1 ? 'Indisponible' : 'Ajouter au panier' ?>
                            </button>
                        </form>
                        <?php if (is_string($userLogin)): ?>
                            <div class="product-actions">
                                <a class="button-link secondary-button" href="/products/<?= (int) $product['product_id'] ?>/edit">Modifier</a>
                                <form action="/products/<?= (int) $product['product_id'] ?>/delete" method="post" onsubmit="return confirm('Supprimer ce produit ?');">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                    <button class="danger-button" type="submit">Supprimer</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
