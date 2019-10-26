<?php

namespace App\Controllers;

use App\Models\Articles\Article;
use App\Services\Db;
use App\Views\View;

class MainController
{
    /** @var View */
    private $view;


    public function __construct()
    {
        $this->view = new View(__DIR__ . '/../../templates/');
    }

    public function main()
    {
        $articles = Article::findAll();
        $this->view->renderHtml('pages/main.php', ['articles' => $articles]);
    }
}