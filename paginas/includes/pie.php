</main>
<footer class="container py-3 border-top text-body-secondary small">AppVentas</footer>
<script src="<?= $base ?>/js/jquery.js"></script>
<script src="<?= $base ?>/js/bootstrap.bundle.min.js"></script>
<?php foreach (($scripts ?? []) as $js): ?>
<script src="<?= $base ?>/js/<?= htmlspecialchars($js) ?>"></script>
<?php endforeach; ?>
</body>
</html>
