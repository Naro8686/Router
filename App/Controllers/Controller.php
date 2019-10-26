<?php


namespace App\Controllers;

use App\Views\View;

abstract class Controller
{
    protected $view;

    protected $template = __DIR__ . '/../../templates/';

    public function __construct()
    {
        $this->view = new View($this->template);
    }

    public function view($page)
    {
        $this->view->renderHtml($page);
    }

}