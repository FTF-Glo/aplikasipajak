<?php

function menu()
{
    global $isUserLoggedIn;
    $menus = config::get('menus');
    foreach ($menus as $title => $menu) {
        if ((strpos($menu, 'auth@') !== false) && !$isUserLoggedIn) {
            continue;
        }
        if ((strpos($menu, 'guest@') !== false) && $isUserLoggedIn) {
            continue;
        }

        $menu = str_replace(array('auth@', 'guest@'), '', $menu);

        echo '<li class="nav-item">';
        echo '<a href="' . base_url($menu) . '" class="nav-link">' . $title . '</a>';
        echo '</li>';
    }
}
