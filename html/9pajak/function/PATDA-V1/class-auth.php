<?php

class Auth {

    private $Conn;
    private $Data;
    private $id_pajak;
    private $arr_pajak = array(1 => "Air Bawah Tanah", 2 => "Hiburan", 3 => "Hotel", 4 => "Mineral Non Logam dan Batuan", 5 => "Parkir",
        6 => "Penerangan Jalan", 7 => "Reklame", 8 => "Restoran", 9 => "Sarang Walet");

    public function __construct() {
        global $DBLink, $data, $id_pajak;

        $this->id_pajak = $id_pajak;
        $this->Conn = $DBLink;
        $this->Data = $data;
    }
    
    public function check_auth_wp() {
        //$query = "SELECT * FROM PATDA_WP WHERE CPM_USER = '{$this->Data->uname}'";
        $query = "SELECT * FROM PATDA_WP WHERE CPM_USER = '{$this->Data->uid}'";
        
        $result = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($result);
        $jenis_pajak = explode(";", $data['CPM_JENIS_PAJAK']);
        
        $html = "<ul>";
        foreach($jenis_pajak as $x){
            $html .= "<li>{$this->arr_pajak[$x]}</li>";
        }
        $html .= "</ul>";
        
        if (in_array($this->id_pajak, $jenis_pajak)) {            
        } else {
            echo "<script>";
            
            echo "$(document).ready(function(){";
            echo "  $(\"<div title='PERHATIAN'>Anda tidak memiliki hak akses !<br/> Pajak yang bisa anda laporkan adalah :<br/> {$html}</div>\").dialog({";
            echo "      modal: true,";
            echo "      closeOnEscape: false,";
            echo "      draggable: false,";
            echo "      resizable: false,";
            echo "      buttons: {";
            echo "          'Ok': function() {";
            echo "              window.location.href = 'http://'+ window.location.host+'/main.php';";
            echo "          }";
            echo "      }";
            echo "  });";
            echo "  $(\".ui-dialog-titlebar-close\").hide();";
            echo "});";
            echo "</script>";
        }
    }

}

?>
