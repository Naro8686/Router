<?php
/**
 * @param string $path
 * @return string
 */
function assets($path)
{
    $isHttps = !empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']);
    $protocol = $isHttps ? 'https://' : 'http://';
    list(, $p) = explode('/', $_SERVER['PHP_SELF']);
    return $protocol . $_SERVER['SERVER_NAME'] . "/" . $p . $path;
}

/**
 * @param $path
 * @return string
 */
function public_path($path = '')
{
    return assets($path);
}