
<?php

function isPageExists($path)
{
    $realpath = PAGE_PATH . $path . '.php';
    $inclpath = PAGES . $path . '.php';
    return file_exists($realpath) ? $inclpath : false;
}

function isActionExists($path)
{
    $realpath = ACTION_PATH . $path . '.php';
    $inclpath = ACTIONS . $path . '.php';
    return file_exists($realpath) ? $inclpath : false;
}


if (inputGet('page') || inputGet('action')) {

    $page = inputGet('page') ? isPageExists($_GET['page']) : false;
    $action = inputGet('action') ? isActionExists($_GET['action']) : false;

    if ($page || $action) {

        if (
            ($page && in_array($_GET['page'], config::get('auth_pages')) && !$isUserLoggedIn) ||
            ($action && in_array($_GET['action'], config::get('auth_actions')) && !$isUserLoggedIn)
        ) {
            redirectToPage('login');
        }

        if (
            ($page && in_array($_GET['page'], config::get('guest_pages')) && $isUserLoggedIn) ||
            ($action && in_array($_GET['action'], config::get('guest_actions')) && $isUserLoggedIn)
        ) {
            redirectToPage('home');
        }

        if ($action) {
            include_once $action;
        }

        if ($page) {
            include_once $page;
        }
    } else {
        echo '404 - Not Found';
    }
} else {
    redirectToPage(config::get('default_page'));
}
