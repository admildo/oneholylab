<?php /** @var string $status one of the order statuses, e.g. View::partial('status_pill', ['status' => 'paid']) */
// Active orders get a black dot, finished or stopped ones a grey dot.
$dot = match ($status) {
    'paid' => '#000',
    'processing', 'shipped' => '#5c5c5c',
    default => '#bdbdbd',
};
?>
<span class="pill"><span class="inline-block h-[7px] w-[7px] rounded-full" style="background:<?= $dot ?>"></span><?= ucfirst(e($status)) ?></span>
