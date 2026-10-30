<?php use Holy\{Csrf, View}; /** @var array $errors @var array $old @var string $tab */
// $old holds the posted values. Only the form that was posted gets them back.
$loginOld = $tab === 'login' ? $old : [];
$registerOld = $tab === 'register' ? $old : [];
?>
<section class="mx-auto grid max-w-[1100px] gap-6 px-4 pb-20 pt-8 md:px-6 md:pt-14 lg:grid-cols-2">

  <form method="post" action="<?= url('login.php') ?>" class="flex flex-col gap-6 self-start border border-line p-6 md:p-10" novalidate>
    <?= Csrf::field() ?>

    <div class="flex flex-col gap-2">
      <h1 class="font-logo text-[36px] leading-none md:text-[44px]">Welcome back</h1>
      <p class="text-muted">Log in to check your orders and check out faster.</p>
    </div>

    <?php if ($tab === 'login'): View::partial('form_errors', ['errors' => $errors]); endif; ?>

    <div class="field">
      <label for="le">Email</label>
      <input id="le" name="email" type="email" class="input" autocomplete="email" required value="<?= e($loginOld['email'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="lp">Password</label>
      <input id="lp" name="password" type="password" class="input" autocomplete="current-password" required>
    </div>

    <button class="btn w-full" type="submit">Log in</button>
  </form>

  <form method="post" action="<?= url('register.php') ?>" class="flex flex-col gap-6 bg-soft p-6 md:p-10" novalidate>
    <?= Csrf::field() ?>

    <h2 class="font-logo text-[36px] leading-none md:text-[44px]">New here?</h2>
    <p class="text-muted">Save your details, check your order status and check out faster.</p>

    <?php if ($tab === 'register'): View::partial('form_errors', ['errors' => $errors]); endif; ?>

    <div class="grid gap-5 md:grid-cols-2">
      <div class="field">
        <label for="rf">First name</label>
        <input id="rf" name="first_name" class="input" autocomplete="given-name" required value="<?= e($registerOld['first_name'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="rl">Last name</label>
        <input id="rl" name="last_name" class="input" autocomplete="family-name" required value="<?= e($registerOld['last_name'] ?? '') ?>">
      </div>
    </div>

    <div class="field">
      <label for="re">Email</label>
      <input id="re" name="email" type="email" class="input" autocomplete="email" required value="<?= e($registerOld['email'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="rp">Password</label>
      <input id="rp" name="password" type="password" class="input" autocomplete="new-password" required minlength="8">
      <span class="text-[11px] text-muted">At least 8 characters</span>
    </div>

    <label class="flex min-h-[44px] items-center gap-2.5">
      <input type="checkbox" name="marketing" value="1"> Send me drops and news by email
    </label>

    <button class="btn w-full" type="submit">Create account</button>
  </form>
</section>
