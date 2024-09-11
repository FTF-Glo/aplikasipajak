<?php

function appName()
{
    return APP_NAME;
}
function base_url($url = '')
{
    $url = ltrim(trim($url), '/');
    return BASE_URL . $url;
}
function now()
{
    return date('Y-m-d H:i:s');
}
function toDateTime($time)
{
    return date('Y-m-d H:i:s', $time);
}
function redirect($url)
{
    $url = ltrim(trim($url), '/');
    header('location:' . BASE_URL . $url);
    exit;
}
function redirectToPage($page)
{
    header('location:' . BASE_URL . '?page=' . $page);
    exit;
}
function getCurrentPage()
{
    return (isset($_GET['page']) && $_GET['page']) ? $_GET['page'] : false;
}
function getCurrentAction()
{
    return (isset($_GET['action']) && $_GET['action']) ? $_GET['action'] : false;
}
function toCurrency($number)
{
    return number_format($number, 0, ',', '.');
}
function inputPost($name)
{
    return (isset($_POST[$name]) && $_POST[$name]) ? $_POST[$name] : null;
}
function inputGet($name)
{
    return (isset($_GET[$name]) && $_GET[$name]) ? $_GET[$name] : null;
}
function inputSession($name)
{
    return (isset($_SESSION[$name]) && $_SESSION[$name]) ? $_SESSION[$name] : null;
}
function getFullUrl()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}
function getQueryParams()
{
    $queryParams = array();
    $fullUrl = explode('?', getFullUrl(), 2);
    if (isset($fullUrl[1])) {
        parse_str($fullUrl[1], $queryParams);
    }

    return $queryParams;
}
function makePaginationUrl($targetPagination)
{
    $queryParams = getQueryParams();
    $queryParams['p'] = $targetPagination;

    return base_url('?' . http_build_query($queryParams));
}
function makeSearchUrl($search)
{
    $queryParams = getQueryParams();
    $queryParams['s'] = $search;

    return base_url('?' . http_build_query($queryParams));
}
function makeOrderByUrl($by)
{
    $direction = 'asc';
    $queryParams = getQueryParams();

    if (
        (isset($queryParams['ob']) &&
            $queryParams['ob'] &&
            $queryParams['ob'] == $by) &&
        (isset($queryParams['o']) &&
            $queryParams['o'] &&
            $queryParams['o'] == 'asc')
    ) {
        $direction = 'desc';
    }

    $queryParams['o'] = $direction;
    $queryParams['ob'] = $by;

    return base_url('?' . http_build_query($queryParams));
}
function getOrderCaret($by)
{
    $caret = '';
    $queryParams = getQueryParams();
    if (
        (isset($queryParams['ob']) &&
            $queryParams['ob'] &&
            $queryParams['ob'] == $by)
    ) {
        if (
            isset($queryParams['o']) &&
            $queryParams['o'] &&
            $queryParams['o'] == 'asc'
        ) {
            $caret = '&#9650;';
        } else {
            $caret = '&#9660;';
        }
    }

    return $caret;
}
