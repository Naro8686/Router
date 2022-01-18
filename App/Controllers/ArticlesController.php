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

    /**
     * @param int $articleId
     * @return void
     */
    public function view($articleId)
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

    /**
     * @param int $articleId
     * @return void
     */
    public function edit($articleId)
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