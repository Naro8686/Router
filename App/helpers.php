<?php
function assets(string $path): string
{
    $isHttps = !empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']);
    $protocol = $isHttps ? 'https://' : 'http://';
    list(, $p) = explode('/', $_SERVER['PHP_SELF']);
    return $protocol . $_SERVER['SERVER_NAME'] . "/" . $p . $path;
}

function public_path(string $path = ''): string
{
    return assets($path);
}