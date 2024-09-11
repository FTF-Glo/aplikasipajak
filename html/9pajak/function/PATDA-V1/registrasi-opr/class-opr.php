<?php

class OperatorPajak extends Pajak {

    protected $CPM_USER;
    protected $CPM_NAMA;
    protected $CPM_PWD;
    protected $CPM_ROLE;
    protected $CPM_ROLE_PREV;
    
    private $CTR_U_PWD = "";
    private $CTR_U_ADMIN = 0;
    private $CTR_U_STYLE = "default";
    private $CTR_RM_ID = "";
    private $CPM_STATUS = 1;
    private $CAPTCHA;
    private $CTR_U_BLOCKED = 0;

    function __construct() {
        parent::__construct();

        $this->CTR_U_PWD = md5("123");
        $OPR = isset($_POST['OPR']) ? $_POST['OPR'] : array();

        foreach ($OPR as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }
        
        $this->set_list_role();
    }
    
    public $arr_role;
	private function set_list_role(){
		$query = "SELECT * FROM CENTRAL_ROLE_MODULE WHERE CTR_RM_DESC = 'opr'";
		$res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

		while($d = mysqli_fetch_assoc($res)){
			$this->arr_role[$d['CTR_RM_ID']] = $d['CTR_RM_NAME'];
		}
	}	

    public function update() {
        #update wp
		
        $PWD = "";
        if ($this->NPASSWORD != "") {
            if ($this->NPASSWORD == $this->CNPASSWORD) {
                if ($this->update_core_user()) {
                    $this->CPM_PWD = base64_encode($this->NPASSWORD);
                    $PWD = "CPM_PWD = '{$this->CPM_PWD}',";
                    $msg = "Password berhasil di perbaharui";
                    $this->Message->setMessage($msg, true);
                    $_SESSION['_success'] = $msg;
                }
            } else {
				$msg = "Password Gagal diperbaharui, pastikan password baru dan password konfirmasi sesuai!";
                $this->Message->setMessage($msg);
                $_SESSION['_error'] = $msg;
                
            }
        }
		
		$query = sprintf("UPDATE PATDA_PETUGAS SET
                    CPM_NAMA = '%s',
                    CPM_ROLE = '%s',
                    CPM_NIP = '%s',
                    {$PWD}
                    CPM_AUTHOR = '%s'
                    WHERE CPM_USER = '{$this->CPM_USER}'", $this->CPM_NAMA, $this->CPM_ROLE,$this->CPM_NIP, $this->CPM_AUTHOR);
        $res = mysqli_query($this->Conn, $query);
        
		if($this->CPM_ROLE_PREV != $this->CPM_ROLE){
			$query = sprintf("UPDATE CENTRAL_USER_TO_APP SET CTR_RM_ID = '%s' WHERE CTR_USER_ID = '%s'", $this->CPM_ROLE, $this->CPM_USER);
            mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
		}
		
        return $res;
    }
	
	private function update_core_user() {
		$query = sprintf("UPDATE CENTRAL_USER SET CTR_U_PWD = '%s' WHERE CTR_U_ID = '%s'", md5($this->NPASSWORD), $this->CPM_USER);
        $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
        return $res;
    }
    
    public function save() {
    	// die(var_dump($_REQUEST));
        $query = "SELECT * FROM PATDA_PETUGAS WHERE CPM_USER = '{$this->CPM_USER}'";
        $res = mysqli_query($this->Conn, $query);
        if (mysqli_num_rows($res) == 0) {

            $this->CPM_PWD = base64_encode($this->NPASSWORD);
            $this->CPM_TGL_JOIN = date("d-m-Y");
            
            #insert wp baru
            $query = sprintf("INSERT INTO PATDA_PETUGAS 
                    (CPM_USER, CPM_PWD, CPM_NAMA, CPM_NIP, CPM_ROLE, CPM_AUTHOR, CPM_STATUS, CPM_TGL_JOIN)
                    VALUES ( '%s','%s','%s','%s','%s','%s','%s','%s')", $this->CPM_USER,$this->CPM_PWD, $this->CPM_NAMA, $this->CPM_NIP, $this->CPM_ROLE, $this->CPM_AUTHOR, $this->CPM_STATUS, $this->CPM_TGL_JOIN
            );
            $this->CTR_RM_ID = $this->CPM_ROLE;
            $res = mysqli_query($this->Conn, $query);
            if ($res == true) {
                $this->CTR_U_PWD = md5($this->NPASSWORD);
                $res = $this->save_core_user();
                
                if($res){
					$_SESSION['_success'] = 'Operator berhasil disimpan';
				}else{
					$_SESSION['_error'] = 'Operator gagal disimpan';
				}
            }else{
				$_SESSION['_error'] = 'Operator gagal disimpan';
			}
        } else {
            $res = false;
            $msg = "Gagal disimpan, User Name sudah terdaftar sebelumnya!";
            $this->Message->setMessage($msg);
            $_SESSION['_error'] = $msg;
        }
        return $res;
    }

    public function daftar() {
        $this->CPM_STATUS = 0;
        $this->CTR_U_BLOCKED = 1;
        $this->CPM_AUTHOR = $this->CPM_USER;
        $this->_a = "aPatda";
        if ($this->save() == true) {
            $this->Message->setMessage("Petugas baru berhasil di daftarkan, anda belum bisa login aplikasi, silakan tunggu konfirmasi balik dari administrator!", 1);
        }
        $this->redirect("../../../registrasi_wp");
    }

    private function save_core_user() {
		$this->CTR_U_STYLE = $this->get_config_value($this->_a, "STYLE_APP");
		
        $query = sprintf("INSERT INTO CENTRAL_USER 
            (CTR_U_ID, CTR_U_UID, CTR_U_PWD, CTR_U_ADMIN, CTR_U_STYLE, CTR_U_BLOCKED) VALUES
            ('%s','%s','%s','%s','%s','%s')", $this->CPM_USER, $this->CPM_USER, $this->CTR_U_PWD, $this->CTR_U_ADMIN, $this->CTR_U_STYLE, $this->CTR_U_BLOCKED);
        $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

        if ($res == true) {
            $query = sprintf("INSERT INTO CENTRAL_USER_TO_APP 
            (CTR_USER_ID, CTR_APP_ID, CTR_RM_ID) VALUES 
            ('%s','%s','%s')", $this->CPM_USER, $this->_a, $this->CTR_RM_ID);
            $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
        }
        return $res;
    }

    public function delete() {
        $query = "DELETE FROM PATDA_PETUGAS WHERE CPM_USER = '{$this->CPM_USER}'";
        $res = mysqli_query($this->Conn, $query);
        if ($res == true) {
            $res = $this->delete_core_user();
            if($res){
				$_SESSION['_success'] = 'Operator berhasil dihapus';
			}else{
				$_SESSION['_error'] = 'Operator gagal dihapus';
			}
        }else{
			$_SESSION['_error'] = 'Operator gagal dihapus';
		}
    }

    private function delete_core_user() {
        $query = "DELETE FROM CENTRAL_USER WHERE CTR_U_ID = '{$this->CPM_USER}'";
        mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

        $query = "DELETE FROM CENTRAL_USER_TO_APP WHERE CTR_USER_ID= '{$this->CPM_USER}'";
        return mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
    }

    public function aktivasi() {
        $status = 1;
        $blok = 0;
        $query = "UPDATE PATDA_PETUGAS SET
                    CPM_STATUS = '{$status}'
                    WHERE CPM_USER = '{$this->CPM_USER}'";

        $res = mysqli_query($this->Conn, $query);
        if (mysqli_num_rows($res) == 0) {
            $query = "UPDATE CENTRAL_USER SET CTR_U_BLOCKED = '{$blok}' WHERE CTR_U_ID = '{$this->CPM_USER}'";
            $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
            
            if($res){
				$_SESSION['_success'] = 'Operator berhasil diaktifkan';
			}else{
				$_SESSION['_error'] = 'Operator gagal diaktifkan';
			}
			return $res;
        }
    }

    public function blok() {
        $status = 2;
        $blok = 1;
        $query = "UPDATE PATDA_PETUGAS SET
                    CPM_STATUS = '{$status}'
                    WHERE CPM_USER = '{$this->CPM_USER}'";

        $res = mysqli_query($this->Conn, $query);
        if (mysqli_num_rows($res) == 0) {
            $query = "UPDATE CENTRAL_USER SET CTR_U_BLOCKED = '{$blok}' WHERE CTR_U_ID = '{$this->CPM_USER}'";
            $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
            
            if($res){
				$_SESSION['_success'] = 'Operator berhasil diaktifkan';
			}else{
				$_SESSION['_error'] = 'Operator gagal diaktifkan';
			}
			return $res;
        }
    }

    public function getDataOpr() {
        #inisialisasi data kosong
        $data = array("CPM_USER" => "", "CPM_NAMA" => "", "CPM_NIP" => "", "CPM_ROLE" => "");

        #query untuk mengambil data wp
        $query = "SELECT * FROM PATDA_PETUGAS WHERE CPM_USER = '{$this->_id}'";
        $result = mysqli_query($this->Conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $dataOpr = mysqli_fetch_assoc($result);
            $data = array_merge($data, $dataOpr);
        }
        return $data;
    }

    public function filtering($id) {
        $html = "<div class=\"filtering\">
                    <form>
                        <label>User Name :</label> <input type=\"text\" name=\"CPM_USER-{$id}\" id=\"CPM_USER-{$id}\" class=\"form-control\" style=\"height: 32px; width: 168px; display: inline-block\"  >  
                        <label style=\"margin-left: 10px\">Nama :</label> <input type=\"text\" name=\"CPM_NAMA-{$id}\" id=\"CPM_NAMA-{$id}\" class=\"form-control\" style=\"height: 32px; width: 168px; display: inline-block\" >  
                        <label style=\"margin-left: 10px\">NIP :</label> <input type=\"text\" name=\"CPM_NIP-{$id}\" id=\"CPM_NIP-{$id}\" class=\"form-control\" style=\"height: 32px; width: 168px; display: inline-block\" >  
                        <label style=\"margin-left: 10px\">Role Petugas :</label> <select name=\"CPM_ROLE-{$id}\" id=\"CPM_ROLE-{$id}\" class=\"form-control\" style=\"height: 32px; width: 168px; display: inline-block\" >";
        $html.= "<option value=''>All</option>";
        foreach ($this->arr_role as $a => $b) {
            $html.= "<option value='{$a}'>{$b}</option>";
        }
        $html.= "</select>    
                        <button type=\"submit\" id=\"cari-{$id}\" class=\"btn btn-primary lm-btn\" style=\"font-size: 0.7rem !important; margin-left: 10px\" ><i class=\"fa fa-search\"></i> Cari</button>
                    </form>
                </div> ";
        return $html;
    }

    public function grid_table() {
        $DIR = "PATDA-V1";
        $modul = "registrasi-opr";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                {$this->filtering($this->_i)}
                <div id=\"laporanPajak-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">

                    $(document).ready(function() {
                        $('#laporanPajak-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: {$this->pageSize},
                            sorting: true,
                            defaultSorting: 'CPM_ROLE ASC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_USER: {title: 'User Name',key: true}, 
                                CPM_NAMA: {title: 'Nama Petugas',key: true}, 
                                CPM_NIP: {title: 'NIP',width: '10%'},
                                CPM_AUTHOR: {title: 'Author',width: '10%'},
                                CPM_ROLE: {title: 'Role Petugas',width: '10%'}
                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#laporanPajak-{$this->_i}').jtable('load', {
                                CPM_NIP : $('#CPM_NIP-{$this->_i}').val(),
                                CPM_USER : $('#CPM_USER-{$this->_i}').val(),
                                CPM_ROLE : $('#CPM_ROLE-{$this->_i}').val(),
                                CPM_NAMA : $('#CPM_NAMA-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        
                    });
                </script>";
        echo $html;
    }

    public function grid_data() {
        try {
            $where = "CPM_STATUS = '{$this->_s}' ";
            $where.= (isset($_REQUEST['CPM_NIP']) && $_REQUEST['CPM_NIP'] != "") ? " AND CPM_NIP like \"{$_REQUEST['CPM_NIP']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_ROLE']) && $_REQUEST['CPM_ROLE'] != "") ? " AND CPM_ROLE = \"{$_REQUEST['CPM_ROLE']}\" " : "";
            $where.= (isset($_REQUEST['CPM_USER']) && $_REQUEST['CPM_USER'] != "") ? " AND CPM_USER like \"{$_REQUEST['CPM_USER']}%\" " : "";
            $where.= (isset($_REQUEST['CPM_NAMA']) && $_REQUEST['CPM_NAMA'] != "") ? " AND CPM_NAMA like \"{$_REQUEST['CPM_NAMA']}%\" " : "";
            
            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM PATDA_PETUGAS WHERE {$where}";
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT * FROM PATDA_PETUGAS WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->Conn, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"]/$_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&id={$row['CPM_USER']}&s={$row['CPM_STATUS']}&i={$this->_i}";
                $url = "main.php?param=" . base64_encode($base64);

                $row['CPM_USER'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_USER']}</a>";
                $row['CPM_ROLE'] = $this->arr_role[$row['CPM_ROLE']];
                $rows[] = $row;
            }

            $jTableResult = array();
            $jTableResult['Result'] = "OK";
            $jTableResult['q'] = $query;
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

}

?>
