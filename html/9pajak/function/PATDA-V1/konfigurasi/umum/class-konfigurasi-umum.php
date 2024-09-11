<?php

class KonfigurasiUmum extends Pajak {

    function __construct() {

        parent::__construct();
        $PAJAK = isset($_POST['PAJAK']) ? $_POST['PAJAK'] : array();
        foreach ($PAJAK as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }
        $this->_a = "aPatda";
    }

    public function data_table() {
        try {
            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM CENTRAL_APP_CONFIG WHERE CTR_AC_AID='{$this->_a}' AND (CTR_AC_DESC IS NOT NULL AND CTR_AC_DESC !='') ";
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];
            
            $query = "SELECT CTR_AC_AID,CTR_AC_KEY,CTR_AC_DESC, CTR_AC_VALUE FROM CENTRAL_APP_CONFIG
			WHERE CTR_AC_AID='{$this->_a}' AND (CTR_AC_DESC IS NOT NULL AND CTR_AC_DESC !='') ORDER BY CTR_AC_DESC
			LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
				
            $result = mysqli_query($this->Conn, $query);
            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
                $row['KEY'] = $row['CTR_AC_KEY'];
                $row['DESC'] = $row['CTR_AC_DESC'];
                $row['VALUE'] = $row['CTR_AC_VALUE'];
                $rows[] = $row;
            }
            $jTableResult = array();
            $jTableResult['Result'] = "OK";
            $jTableResult['TotalRecordCount'] = $recordCount;
            $jTableResult['Records'] = $rows;
            print $this->Json->encode($jTableResult);

            mysqli_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }
	
	public function data_table_pejabat() {
        try {
			
			$query = "SELECT * FROM PATDA_PEJABAT 
			ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
			
            $result = mysqli_query($this->Conn, $query);
            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no)); 
                $rows[] = $row;
            }
            $jumlah = mysqli_num_rows($result);
            $jTableResult = array();
            $jTableResult['Result'] = "OK";

            $query = "SELECT * FROM PATDA_PEJABAT";
            $result = mysqli_query($this->Conn, $query);

            $jTableResult['TotalRecordCount'] = mysqli_num_rows($result);
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
            $query = "SELECT CTR_AC_AID,CTR_AC_KEY,CTR_AC_DESC,CTR_AC_VALUE FROM CENTRAL_APP_CONFIG
                    WHERE CTR_AC_AID='{$this->_a}'
                    AND CTR_AC_KEY in ('BAG_VERIFIKASI_NIP','BAG_VERIFIKASI_NAMA')
                    LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
            $result = mysqli_query($this->Conn, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
                $row['KEY'] = $row['CTR_AC_KEY'];
                $row['DESC'] = $row['CTR_AC_DESC'];
                $row['VALUE'] = $row['CTR_AC_VALUE'];
                $rows[] = $row;
            }
            $jTableResult = array();
            $jTableResult['q'] = $query;
            $jTableResult['Result'] = "OK";
            $jTableResult['TotalRecordCount'] = 2;
            $jTableResult['Records'] = $rows;
            print $this->Json->encode($jTableResult);

            mysqli_close($this->Conn);
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
            $result = mysqli_query($this->Conn, $query);

            $jTableResult = array();
            $jTableResult['Result'] = "OK";
            print $this->Json->encode($jTableResult);

            mysqli_close($this->Conn);
        } catch (Exception $ex) {
            #Return error message
            $jTableResult = array();
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
            print $this->Json->encode($jTableResult);
        }
    }
	
	public function UpdateDataPejabat($nip,$nama,$jabatan,$pangkat, $key) {
        try {
            $query = "update PATDA_PEJABAT set CPM_NIP='" . $nip . "',CPM_NAMA='" . $nama . "',CPM_JABATAN='" . $jabatan . "',CPM_PANGKAT='" . $pangkat . "' 
                  where CPM_KEY = '" . $key . "'";
            $result = mysqli_query($this->Conn, $query);

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

    public function grid_table($modul = "konfigurasi/umum", $file = "svc-list-data.php") {
        $DIR = "PATDA-V1";
        
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                
                <div id=\"konfig-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">

                    $(document).ready(function() {
                        $('#konfig-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: {$this->pageSize},
                            selecting:true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/{$file}?action=list',
                                updateAction: 'function/{$DIR}/konfigurasi/umum/svc-konfigurasi-umum.php',
                            },
                            fields: {
								NO : {title: 'No',width: '3%',edit :false},
                                KEY: {key: true,list: false}, 
                                DESC: {title:'Konfigurasi',width: '40%',edit :false},
                                VALUE: {title:'Isi',width: '40%'}
                            }
                        });
                        $('#konfig-{$this->_i}').jtable('load');
                    });
                </script>";
        echo $html;
    }
	
	
	
	public function grid_table_pejabat($modul = "konfigurasi/umum", $file = "svc-list-data-pejabat.php") {
        $DIR = "PATDA-V1";
        // echo "view/{$DIR}/{$modul}/{$file}?action=list";exit;
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                
                <div id=\"konfig-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">

                    $(document).ready(function() {
                        $('#konfig-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            sorting: true,
                            defaultSorting: 'CPM_NAMA DESC',
                            pageSize: {$this->pageSize},
                            selecting:true,
                            
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/{$file}?action=list',
                                updateAction: 'function/{$DIR}/konfigurasi/umum/svc-konfigurasi-umum-pejabat.php',
                            },
                            fields: {
                                NO : {title: 'No',width: '1%',edit :false},
                                CPM_ORDER : {list :false, edit:false},
                                CPM_KEY: {list :false,key: true,edit :false}, 
                                CPM_JABATAN: {title:'Jabatan',width: '10%'}, 
                                CPM_NAMA: {title: 'Nama',width: '10%'},
                                CPM_PANGKAT: {title: 'Pangkat',width: '10%'},
                                CPM_NIP: {title: 'NIP',width: '10%'}
                            }
                        });
                        $('#konfig-{$this->_i}').jtable('load');
                    });
                </script>";
        echo $html;
    }

    public function grid_table_ver($modul = "konfigurasi/verifikasi" ) {
        return $this->grid_table($modul, "svc-list-data.php");
    }

}

?>
