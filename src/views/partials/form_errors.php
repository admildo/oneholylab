<?php /** @var array $errors messages to show above a form; nothing is printed when it is empty */ ?>
<?php if ($errors): ?>
  <div class="border-l-4 border-black bg-soft p-4" role="alert">
    <?php foreach ($errors as $error): ?>
      <p><?= e($error) ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
