<?php foreach ($articles as $article): ?>
    <h2><a href="<?= public_path('/articles/' . $article->getId()) ?>"><?= $article->getName() ?></a></h2>
    <p><?= $article->getText() ?></p>
    <hr>
<?php endforeach; ?>