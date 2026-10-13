<section class="auth-card product-form-card" aria-labelledby="product-form-title">
    <p class="eyebrow">Catalogue</p>
    <h1 id="product-form-title"><?= e($title) ?></h1>
    <p class="intro">Les champs marqués sont requis. Les images JPEG, PNG et WebP jusqu’à 5 Mo sont acceptées.</p>

    <?php if ($errors !== []): ?>
        <div class="form-error" role="alert">
            <p>Vérifiez les champs suivants :</p>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php
    $action = $mode === 'create' ? '/products' : '/products/' . $productId . '/update';
    ?>
    <form action="<?= e($action) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <label for="name">Nom du produit</label>
        <input id="name" name="name" type="text" maxlength="200" value="<?= e((string) ($product['name'] ?? '')) ?>" required>

        <label for="category_id">Catégorie</label>
        <select id="category_id" name="category_id" required>
            <option value="">Choisir une catégorie</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['category_id'] ?>" <?= (string) ($product['category_id'] ?? '') === (string) $category['category_id'] ? 'selected' : '' ?>>
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" maxlength="5000"><?= e((string) ($product['description'] ?? '')) ?></textarea>

        <div class="form-row">
            <div>
                <label for="price">Prix HT (€)</label>
                <input id="price" name="price" type="number" min="0" max="99999999.99" step="0.01" value="<?= e((string) ($product['price'] ?? '')) ?>" required>
            </div>
            <div>
                <label for="promotional_price">Prix promotionnel HT (€)</label>
                <input id="promotional_price" name="promotional_price" type="number" min="0" max="99999999.99" step="0.01" value="<?= e((string) ($product['promotional_price'] ?? '')) ?>">
            </div>
        </div>

        <div class="form-row">
            <div>
                <label for="stock">Stock</label>
                <input id="stock" name="stock" type="number" min="0" step="1" value="<?= e((string) ($product['stock'] ?? '0')) ?>" required>
            </div>
            <div>
                <label for="published_on">Date de mise en ligne</label>
                <input id="published_on" name="published_on" type="date" value="<?= e(substr((string) ($product['published_on'] ?? date('Y-m-d')), 0, 10)) ?>" required>
            </div>
        </div>

        <label for="image">Image du produit</label>
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
        <?php if (!empty($product['image_path'])): ?>
            <img class="current-product-image" src="<?= e($product['image_path']) ?>" alt="Image actuelle du produit">
            <p class="field-hint">L’image actuelle est conservée si aucun nouveau fichier n’est choisi.</p>
        <?php endif; ?>
        <?php if (isset($errors['image'])): ?>
            <p class="form-error" role="alert"><?= e($errors['image']) ?></p>
        <?php endif; ?>

        <div class="form-actions">
            <button type="submit"><?= $mode === 'create' ? 'Créer le produit' : 'Enregistrer les modifications' ?></button>
            <a class="button-link secondary-button" href="/products">Annuler</a>
        </div>
    </form>
</section>
