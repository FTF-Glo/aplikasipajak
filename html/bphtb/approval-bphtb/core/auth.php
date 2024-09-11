<?php

class auth
{
    protected $conn;

    public function __construct()
    {
        $dbInstance = db::getInstance();
        $this->conn = $dbInstance->connection('default');
    }

    public function isLoggedIn()
    {
        $key = isset($_SESSION['key']) ? $_SESSION['key'] : false;
        if ($key) {
            $session = $this->conn
                ->where('expired_date', now(), '>')
                ->where('session', $key)
                ->getOne('sessions');

            if ($key && !$session) {
                set_flash('Sesi login anda telah kedaluwarsa, silakan login kembali', 'primary');
                $this->unsetLoginKey();
                redirectToPage('login');
            }

            $key = $session;
        }

        return $key;
    }

    public function login($username, $password)
    {
        if ($this->isLoggedIn()) {
            return true;
        }


        $swConnection = $this->conn->connection('sw_ssb');
        $user = $swConnection
            ->where(config::get('user_username_col_name'), $username)
            ->where(config::get('user_password_col_name'), md5(trim($password)))
            ->getOne(config::get('table_user'));
        
        if (!empty($user)) {

            $key = md5(uniqid(rand()));
            $sessionData = array(
                'session' => $key,
                'expired_date' => toDateTime(time() + config::get('session_expired_interval')),
                'uid' => $user[config::get('user_id_col_name')]
            );

            $id = $this->conn->insert('sessions', $sessionData);

            if (!$id) {
                echo 'insert to sessions failed: ' . $this->conn->getLastError();
                die;
            }

            $_SESSION['key'] = $key;
        }

        return $user;
    }

    public function logout()
    {
        $key = $this->isLoggedIn();

        if ($key) {
            $this->conn->where('session', $key['session']);
            $this->conn->update('sessions', array(
                'expired_date' => now()
            ));
        }

        $this->unsetLoginKey();
    }

    protected function unsetLoginKey()
    {
        unset($_SESSION['key']);
    }
}

$auth = new auth();
