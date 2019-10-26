<?php


namespace App\Controllers;

use App\Models\Articles\Article;
use App\Views\View;

class ArticlesController
{
    /** @var View */
    private $view;


    public function __construct()
    {
        $this->view = new View(__DIR__ . '/../../templates/');
    }

    public function view(int $articleId)
    {
        $article = Article::getById($articleId);
        if ($article === null) {
            $this->view->renderHtml('errors/404.php', [], 404);
            return;
        }
        $this->view->renderHtml('articles/view.php', [
            'article' => $article
        ]);
    }

    public function edit(int $articleId): void
    {
        $article = Article::getById($articleId);
        if ($article === null) {
            $this->view->renderHtml('errors/404.php', [], 404);
            return;
        }

        $article->setName('Новое название статьи');
        $article->setText('Новый текст статьи');

        $article->save();
    }
}