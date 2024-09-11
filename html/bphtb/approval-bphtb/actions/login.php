<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = inputPost('username');
    $password = inputPost('password');

    if (!$username || !$password) {
        set_flash('Username atau Password tidak boleh kosong', 'danger');
        redirectToPage('login');
    }

    $user = $auth->login($username, $password);

    if (empty($user)) {
        set_flash('Username atau Password anda salah', 'danger');
        redirectToPage('login');
    }

    redirectToPage('home');
}
