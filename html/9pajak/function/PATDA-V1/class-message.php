<?php

class Message {

    public function setMessage($msg, $type = 0) {
        $type = ($type==1)? "succ" : "err";
        $_SESSION[$type][] = $msg;
    }
    
    public function clearMessage(){
        if(isset($_SESSION['succ'])) unset($_SESSION['succ']);
        if(isset($_SESSION['err'])) unset($_SESSION['err']);
    }

    public function show() {
        if (isset($_SESSION['succ']) && is_array($_SESSION['succ'])) {
            $msg = "";
            foreach ($_SESSION['succ'] as $m) {
                $msg = "- {$m}\n";
            }
            echo "<div class=\"message succ\">{$msg}</div>";
        }
        if (isset($_SESSION['err']) && is_array($_SESSION['err'])) {
            $msg = "";
            foreach ($_SESSION['err'] as $m) {
                $msg = "- {$m}\n";
            }
            echo "<div class=\"message err\">{$msg}</div>";
        }
        $this->clearMessage();        
    }

}

?>
