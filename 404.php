<?php
require_once __DIR__ . '/includes/init.php';
http_response_code(404);
$pageTitle = 'Page not found | NSBM NewsDesk';
include __DIR__ . '/includes/header.php';
?>
<main>
    <div class="empty" style="max-width:520px;margin:40px auto">
        <h1>404</h1>
        <p>Sorry, we couldn't find that page.</p>
        <a class="btn btn-primary" href="<?= url('index.php') ?>">Back to home</a>
        <a class="btn" href="<?= url('search.php') ?>">Search news</a>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
