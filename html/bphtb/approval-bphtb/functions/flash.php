<?php

function set_flash($msg, $type = '')
{
    $_SESSION['flash_msg'] = $msg;
    $_SESSION['flash_type'] = $type;
}

function flash()
{
    $flashMsg = inputSession('flash_msg');
    $flashType = inputSession('flash_type');

    if ($flashMsg || $flashType) {


        if (!$flashMsg) {
            return false;
        }

        $alertClass = $flashType ? 'alert-' . $flashType : '';
        echo '<div class="alert ' . $alertClass . '" role="alert">';
        echo '<button class="close" data-dismiss="alert" type="button" aria-label="Close">';
        echo '<span aria-hidden="true">&times;</span>';
        echo '</button>';
        echo $flashMsg;
        echo '</div>';
    }

    unset($_SESSION['flash_msg']);
    unset($_SESSION['flash_type']);
}
