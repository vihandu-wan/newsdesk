<footer class="site-footer">
    <div class="footer-inner">
        <div>
            <strong>NSBM NewsDesk</strong><br>
            Campus news, events and announcements from NSBM Green University.
        </div>
        <div class="footer-links">
            <a href="<?= url('index.php') ?>">Home</a>
            <a href="<?= url('announcements/index.php') ?>">Announcements</a>
            <a href="<?= url('search.php') ?>">Search</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= url('submissions/create.php') ?>">Submit news</a>
            <?php else: ?>
                <a href="<?= url('auth/register.php') ?>">Join NewsDesk</a>
            <?php endif; ?>
        </div>
        <div class="footer-copy">&copy; <?= date('Y') ?> NSBM NewsDesk – WAD Batch 26.1</div>
    </div>
</footer>
<script src="<?= url('assets/main.js') ?>"></script>
</body>
</html>
