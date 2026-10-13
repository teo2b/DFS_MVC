<section class="auth-card" aria-labelledby="register-title">
    <p class="eyebrow">Espace utilisateur</p>
    <h1 id="register-title">Créer un utilisateur</h1>
    <p class="intro">Créez votre accès. Votre numéro de compte sera attribué automatiquement.</p>

    <?php if ($error !== null): ?>
        <p class="form-error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form action="/register" method="post">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <label for="login">Pseudo</label>
        <input
            id="login"
            name="login"
            type="text"
            maxlength="90"
            autocomplete="username"
            value="<?= e($login) ?>"
            required
            autofocus
        >

        <label for="email">Adresse courriel</label>
        <input
            id="email"
            name="email"
            type="email"
            maxlength="255"
            autocomplete="email"
            value="<?= e($email) ?>"
            required
        >

        <label for="password">Mot de passe</label>
        <input
            id="password"
            name="password"
            type="password"
            minlength="8"
            maxlength="72"
            autocomplete="new-password"
            aria-describedby="password-hint"
            required
        >
        <p class="field-hint" id="password-hint">8 à 72 caractères, avec une minuscule, une majuscule, un chiffre et un caractère spécial.</p>

        <label for="password_confirmation">Confirmer le mot de passe</label>
        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            minlength="8"
            maxlength="72"
            autocomplete="new-password"
            required
        >

        <button type="submit">Créer mon utilisateur</button>
    </form>

    <p class="auth-footer">Déjà inscrit ? <a href="/login">Se connecter</a></p>
</section>
