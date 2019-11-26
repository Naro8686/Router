<?php

namespace App\Views;

class View
{
    private $templatesPath;
    private $header = '/layout/header.php';
    private $footer = '/layout/footer.php';
    private $title = 'Мой блог';

    public function __construct(string $templatesPath)
    {
        $this->templatesPath = $templatesPath;
    }

    public function setCode(int $code)
    {
        $this->code = $code;
    }

    /**
     * @param string $templateName
     * @param array $vars
     */
    public function renderHtml(string $templateName, array $vars = [], int $code = 200)
    {
        http_response_code($code);
        extract($vars);
        ob_start();
        include_once "./App/helpers.php";
        include_once $this->templatesPath . $this->header;
        include_once $this->templatesPath . '/' . $templateName;
        include_once $this->templatesPath . $this->footer;
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }
}