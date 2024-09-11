<?php

class Konfigurasi extends Pajak {
    
    function __construct() {       
        
        parent::__construct();
        $PAJAK = isset($_POST['PAJAK']) ? $_POST['PAJAK'] : array();
        foreach ($PAJAK as $a => $b) {
            $this->$a = mysql_escape_string(trim($b));
        }
        $this->_a = "aPatda";
    }

    public function data_table() {
        try {
            $query = "select count(*) as jml FROM CENTRAL_APP_CONFIG
                  where CTR_AC_AID={$this->_a} ";
            $result = mysql_query($query, $this->Conn);
            $row = mysql_fetch_assoc($result);
            $jumlah = $row['jml'];
            
            $query = "select CTR_AC_AID,CTR_AC_KEY,CTR_AC_VALUE FROM CENTRAL_APP_CONFIG
                  where CTR_AC_AID={$this->_a} LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
            $result = mysql_query($query, $this->Conn);
            $rows = array();
            $no = ($_GET["jtStartIndex"]/$_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysql_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
                $row['KEY'] = $row['CTR_AC_KEY'];
                $row['VALUE'] = $row['CTR_AC_VALUE'];
                $rows[] = $row;
            }
            $jTableResult = array();
            $jTableResult['Result'] = "OK";
            $jTableResult['TotalRecordCount'] = $jumlah;
            $jTableResult['Records'] = $rows;
            print $this->Json->encode($jTableResult);

            mysql_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }
    
    public function data_table_ver() {
        try {            
            $query = "SELECT CTR_AC_AID,CTR_AC_KEY,CTR_AC_VALUE FROM CENTRAL_APP_CONFIG
                    WHERE CTR_AC_AID='{$this->_a}'
                    AND CTR_AC_KEY in ('BAG_VERIFIKASI_NIP','BAG_VERIFIKASI_NAMA')
                    LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
            $result = mysql_query($query, $this->Conn);
                
            $rows = array();            
            $no = ($_GET["jtStartIndex"]/$_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysql_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
                $row['KEY'] = $row['CTR_AC_KEY'];
                $row['VALUE'] = $row['CTR_AC_VALUE'];
                $rows[] = $row;
            }
            $jTableResult = array();
            $jTableResult['q'] = $query;
            $jTableResult['Result'] = "OK";
            $jTableResult['TotalRecordCount']= 2;
            $jTableResult['Records'] = $rows;
            print $this->Json->encode($jTableResult);

            mysql_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }

    public function UpdateData($nip, $key) {
        try {
            $query = "update CENTRAL_APP_CONFIG set CTR_AC_VALUE='" . $nip . "' 
                  where CTR_AC_AID='{$this->_a}' AND CTR_AC_KEY = '" . $key . "'";
            $result = mysql_query($query, $this->Conn);

            $jTableResult = array();
            $jTableResult['Result'] = "OK";
            print $this->Json->encode($jTableResult);

            mysql_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }

    public function grid_table($file = "svc-list-data.php") {
        $DIR = "PATDA-V1";
        $modul = "konfigurasi";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                
                <div id=\"konfig\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">

                    $(document).ready(function() {
                        $('#konfig').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: {$this->pageSize},
                            selecting:true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/{$file}?action=list',
                                updateAction: 'function/{$DIR}/{$modul}/svc-konfigurasi.php',
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                KEY: { key: true,
                                       list:true,
                                       title:'KEY CONFIG',
                                       width: '30%',
                                       edit :false,
                                     }, 
                                VALUE: {
                                    title:'VALUE CONFIG',
                                    width: '50%'
                                } 
                            }
                        });
                        $('#konfig').jtable('load');
                    });
                </script>";
        echo $html;
    }
    
    public function grid_table_ver() {
        return $this->grid_table("svc-list-data-ver.php");
    }

}

?>
