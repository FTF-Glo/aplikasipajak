<?php

class WajibPajak extends Pajak
{

    protected $CPM_USER;
    private $CTR_U_PWD = "";
    private $CPM_PWD = "";
    private $CTR_U_ADMIN = 0;
    private $CTR_U_STYLE = "default";
    private $CTR_RM_ID = "rmPatdaWp";
    private $CPM_STATUS = 1;
    private $CAPTCHA;
    private $CTR_U_BLOCKED = 0;
    private $NPASSWORD;
    private $CNPASSWORD;

    function __construct()
    {
        parent::__construct();

        $this->CTR_U_PWD = md5("123");
        $WP = isset($_POST['WP']) ? $_POST['WP'] : array();

        if (isset($_REQUEST['CPM_NPWPD'])) {
            $_REQUEST['CPM_NPWPD'] = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['CPM_NPWPD']);
        }

        foreach ($WP as $a => $b) {
            $this->$a = is_array($b) ? $b : mysqli_escape_string($this->Conn, trim($b));
        }
    }

    public function update()
    {
		//die(var_dump($this));
        #update wp
        $PWD = "";
        if ($this->NPASSWORD != "") {
            if ($this->NPASSWORD == $this->CNPASSWORD) {
                $this->CTR_U_PWD = md5($this->NPASSWORD);
                if ($this->update_core_user()) {
                    $this->CPM_PWD = base64_encode($this->NPASSWORD);
                    $PWD = "CPM_PWD = '{$this->CPM_PWD}',";
                    $msg = "Password berhasil di perbaharui";
                    $this->Message->setMessage($msg, true);
                    $_SESSION['_success'] = $msg;
                }
            } else {
                $_SESSION['_error'] = "Password Gagal diperbaharui, pastikan password baru dan password konfirmasi sesuai!";
            }
        }
        $this->CPM_JENIS_PAJAK = implode(";", $this->CPM_JENIS_PAJAK);

        // $kode_kecamatan = $this->get_kode_kecamatan($this->CPM_KECAMATAN_WP);
        // $kode_kelurahan = $this->get_kode_kelurahan($this->CPM_KELURAHAN_WP);
        $this->CPM_NAMA_KECAMATAN_WP = $this->get_nama_kecamatan($this->CPM_KECAMATAN_WP);
        $this->CPM_NAMA_KELURAHAN_WP = $this->get_nama_kelurahan($this->CPM_KELURAHAN_WP);

        if (isset($_FILES['FILE_TANDABUKTI']) && $_FILES['FILE_TANDABUKTI']['size'] > 0) {
            $dir = "../../../image/tandabukti/";
            $ext  = strtolower(substr($_FILES['FILE_TANDABUKTI']['name'], strrpos($_FILES['FILE_TANDABUKTI']['name'], '.') + 1));
            $file_name =  "{$this->CPM_NPWPD}_{$this->CPM_JENIS_TANDABUKTI}.{$ext}";
            if (move_uploaded_file($_FILES['FILE_TANDABUKTI']['tmp_name'], "{$dir}{$file_name}")) {
                if ($this->CPM_FILE_TANDABUKTI != '' && $this->CPM_FILE_TANDABUKTI != $filename) unlink($dir . $this->CPM_FILE_TANDABUKTI);
                $this->CPM_FILE_TANDABUKTI = $file_name;
            }
        }

        if (isset($_FILES['FILE_KK']) && $_FILES['FILE_KK']['size'] > 0) {
            $dir = "../../../image/tandabukti/";
            $ext  = strtolower(substr($_FILES['FILE_KK']['name'], strrpos($_FILES['FILE_KK']['name'], '.') + 1));
            $file_name =  "{$this->CPM_NPWPD}_KK.{$ext}";
            if (move_uploaded_file($_FILES['FILE_KK']['tmp_name'], "{$dir}{$file_name}")) {
                if ($this->CPM_FILE_KK != '' && $this->CPM_FILE_KK != $filename) unlink($dir . $this->CPM_FILE_KK);
                $this->CPM_FILE_KK = $file_name;
            }
        }

        if (isset($_FILES['FILE_NPWP']) && $_FILES['FILE_NPWP']['size'] > 0) {
            $dir = "../../../image/tandabukti/";
            $ext  = strtolower(substr($_FILES['FILE_NPWP']['name'], strrpos($_FILES['FILE_NPWP']['name'], '.') + 1));
            $file_name =  "{$this->CPM_NPWPD}_NPWP.{$ext}";
            if (move_uploaded_file($_FILES['FILE_NPWP']['tmp_name'], "{$dir}{$file_name}")) {
                if ($this->CPM_FILE_NPWP != '' && $this->CPM_FILE_NPWP != $filename) unlink($dir . $this->CPM_FILE_NPWP);
                $this->CPM_FILE_NPWP = $file_name;
            }
        }

        $query = sprintf(
            "UPDATE PATDA_WP SET
                    CPM_NPWPD = '%s',
                    CPM_JENIS_PAJAK = '%s',
                    {$PWD}
                    CPM_NAMA_WP = '%s', 
					CPM_ALAMAT_WP = '%s', 
					CPM_TELEPON_WP = '%s', 
                    CPM_KECAMATAN_WP = '%s', 
                    CPM_KELURAHAN_WP = '%s', 
                    CPM_LUAR_DAERAH = '%s', 
					CPM_JENIS_WP = '%s', 
                    CPM_AUTHOR = '%s', 
                    CPM_SURAT_IZIN = '%s', 
                    CPM_NO_SURAT_IZIN = '%s', 
                    CPM_TGL_SURAT_IZIN = '%s', 
                    CPM_JENIS_KEWARGANEGARAAN = '%s', 
                    CPM_JENIS_TANDABUKTI = '%s', 
                    CPM_NO_TANDABUKTI = '%s', 
                    CPM_TGL_TANDABUKTI = '%s', 
                    CPM_NO_KK = '%s', 
                    CPM_TGL_KK = '%s', 
                    CPM_NO_NPWP = '%s', 
                    CPM_JENIS_PEKERJAAN = '%s', 
                    CPM_JABATAN = '%s', 
                    CPM_NAMA_USAHA = '%s', 
                    CPM_ALAMAT_USAHA = '%s',
                    CPM_RTRW_WP = '%s', 
                    CPM_KOTA_WP = '%s', 
                    CPM_KODEPOS_WP = '%s',
                    CPM_FILE_TANDABUKTI = '%s',
                    CPM_FILE_KK = '%s',
                    CPM_FILE_NPWP = '%s'
                    WHERE CPM_USER = '{$this->CPM_USER}'",
            $this->CPM_NPWPD,
            $this->CPM_JENIS_PAJAK,
            $this->CPM_NAMA_WP,
            $this->CPM_ALAMAT_WP,
            $this->CPM_TELEPON_WP,
            ($this->CPM_LUAR_DAERAH == '0')? $this->CPM_NAMA_KECAMATAN_WP: $this->CPM_KECAMATAN_WP1,
            ($this->CPM_LUAR_DAERAH == '0')? $this->CPM_NAMA_KELURAHAN_WP : $this->CPM_KELURAHAN_WP1,
            $this->CPM_LUAR_DAERAH,
            $this->CPM_JENIS_WP,
            $this->CPM_AUTHOR,
            $this->CPM_SURAT_IZIN,
            $this->CPM_NO_SURAT_IZIN,
            $this->CPM_TGL_SURAT_IZIN,
            $this->CPM_JENIS_KEWARGANEGARAAN,
            $this->CPM_JENIS_TANDABUKTI,
            $this->CPM_NO_TANDABUKTI,
            $this->CPM_TGL_TANDABUKTI,
            $this->CPM_NO_KK,
            $this->CPM_TGL_KK,
            $this->CPM_NO_NPWP,
            $this->CPM_JENIS_PEKERJAAN,
            $this->CPM_JABATAN,
            $this->CPM_NAMA_USAHA,
            $this->CPM_ALAMAT_USAHA,
            $this->CPM_RTRW_WP,
            $this->CPM_KOTA_WP,
            $this->CPM_KODEPOS_WP,
            $this->CPM_FILE_TANDABUKTI,
            $this->CPM_FILE_KK,
            $this->CPM_FILE_NPWP
        );
         //echo '<pre>',$query;
         //print_r($this);
         //exit;
        $save = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
        if ($save) {
            $_SESSION['_success'] = 'WP berhasil disimpan';
        } else {
            $_SESSION['_error'] = 'WP gagal disimpan';
        }
        mysqli_close($this->Conn);
        return $save;
    }

    public function save()
    {
        // echo "<pre>";print_r($this);exit;
        $query = "SELECT * FROM PATDA_WP WHERE CPM_USER = '{$this->CPM_USER}'";
        $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
		
        if (mysqli_num_rows($res) == 0) {

            $this->CPM_PWD = base64_encode($this->NPASSWORD);
            $this->CPM_TGL_JOIN = date("d-m-Y");
            $this->CPM_JENIS_PAJAK = implode(";", $this->CPM_JENIS_PAJAK);
            $this->CPM_NAMA_KECAMATAN_WP = $this->get_nama_kecamatan($this->CPM_KECAMATAN_WP);
            $this->CPM_NAMA_KELURAHAN_WP = $this->get_nama_kelurahan($this->CPM_KELURAHAN_WP);


            if ($this->CPM_LUAR_DAERAH == 1) {
                $this->CPM_NAMA_KECAMATAN_WP = $this->CPM_KECAMATAN_WP1;
                $this->CPM_NAMA_KELURAHAN_WP = $this->CPM_KELURAHAN_WP1;
            }

            $npwpd = '';
            $nomor = 1;
            $type = $this->CPM_JENIS_WP == 1 ? 'P1' : 'P2';
            $kode_akhir = $this->get_config_value($this->_a, "KODE_AREA");


            $kec = substr($this->CPM_KECAMATAN_WP, -2, 2);
            $kel = substr($this->CPM_KELURAHAN_WP, -2, 2);
            $query = "SELECT mid(CPM_NPWPD,3,7) as nomor 
                    FROM `PATDA_WP` 
                    where mid(CPM_NPWPD,1,2)='{$type}' 
                    order by mid(CPM_NPWPD,3,7) desc 
                    limit 1";
            $res = mysqli_query($this->Conn, $query);
			
            if ($data = mysqli_fetch_object($res)) {
                $no = (int)$data->nomor;
                $no++;
            } else {
                $no = 1;
            }

            $npwpd = $type;
            $npwpd .= str_pad($no, 7, 0, STR_PAD_LEFT);
            $npwpd .= str_pad($kec, 2, 0, STR_PAD_LEFT);
            $npwpd .= str_pad($kel, 2, 0, STR_PAD_LEFT);
            $this->CPM_NPWPD = $npwpd;
            $this->CPM_USER = $npwpd;

            if (isset($_FILES['FILE_TANDABUKTI']) && $_FILES['FILE_TANDABUKTI']['size'] > 0) {
                $dir = "../../../image/tandabukti/";
                $ext  = strtolower(substr($_FILES['FILE_TANDABUKTI']['name'], strrpos($_FILES['FILE_TANDABUKTI']['name'], '.') + 1));
                $file_name =  "{$this->CPM_NPWPD}_{$this->CPM_JENIS_TANDABUKTI}.{$ext}";
                if (move_uploaded_file($_FILES['FILE_TANDABUKTI']['tmp_name'], "{$dir}{$file_name}")) {
                    $this->CPM_FILE_TANDABUKTI = $file_name;
                }
            }
            if (isset($_FILES['FILE_KK']) && $_FILES['FILE_KK']['size'] > 0) {
                $dir = "../../../image/tandabukti/";
                $ext  = strtolower(substr($_FILES['FILE_KK']['name'], strrpos($_FILES['FILE_KK']['name'], '.') + 1));
                $file_name =  "{$this->CPM_NPWPD}_KK.{$ext}";
                if (move_uploaded_file($_FILES['FILE_KK']['tmp_name'], "{$dir}{$file_name}")) {
                    $this->CPM_FILE_KK = $file_name;
                }
            }
            if (isset($_FILES['FILE_NPWP']) && $_FILES['FILE_NPWP']['size'] > 0) {
                $dir = "../../../image/tandabukti/";
                $ext  = strtolower(substr($_FILES['FILE_NPWP']['name'], strrpos($_FILES['FILE_NPWP']['name'], '.') + 1));
                $file_name =  "{$this->CPM_NPWPD}_NPWP.{$ext}";
                if (move_uploaded_file($_FILES['FILE_NPWP']['tmp_name'], "{$dir}{$file_name}")) {
                    $this->CPM_FILE_NPWP = $file_name;
                }
            }

            #insert wp baru
            $query = sprintf(
                "INSERT INTO PATDA_WP (
				CPM_USER, CPM_PWD, CPM_NPWPD, CPM_JENIS_PAJAK, CPM_AUTHOR, CPM_STATUS, 
				CPM_TGL_JOIN, CPM_NAMA_WP, CPM_ALAMAT_WP, CPM_TELEPON_WP,  
                CPM_KECAMATAN_WP, CPM_KELURAHAN_WP, CPM_LUAR_DAERAH, CPM_JENIS_WP, CPM_GENERATED_BY, CPM_GENERATED_DATETIME, 
                CPM_SURAT_IZIN, CPM_NO_SURAT_IZIN, CPM_TGL_SURAT_IZIN, 
                CPM_JENIS_KEWARGANEGARAAN, CPM_JENIS_TANDABUKTI, CPM_NO_TANDABUKTI, CPM_TGL_TANDABUKTI, CPM_NO_KK, CPM_TGL_KK, CPM_NO_NPWP,
                CPM_JENIS_PEKERJAAN, CPM_NAMA_USAHA, CPM_ALAMAT_USAHA, 
                CPM_RTRW_WP, CPM_KOTA_WP, CPM_KODEPOS_WP, CPM_FILE_TANDABUKTI, CPM_FILE_KK, CPM_FILE_NPWP) VALUES 
				( '%s','%s','%s','%s','%s','%s',
				'%s','%s','%s','%s',
				'%s','%s','%s','%s','%s','%s',
                '%s','%s','%s','%s',
                '%s','%s','%s','%s','%s','%s',
                '%s','%s','%s',
                '%s','%s','%s','%s','%s','%s')",
                $this->CPM_USER,
                $this->CPM_PWD,
                $this->CPM_NPWPD,
                $this->CPM_JENIS_PAJAK,
                $this->CPM_AUTHOR,
                $this->CPM_STATUS,
                $this->CPM_TGL_JOIN,
                $this->CPM_NAMA_WP,
                $this->CPM_ALAMAT_WP,
                $this->CPM_TELEPON_WP,
                $this->CPM_NAMA_KECAMATAN_WP,
                $this->CPM_NAMA_KELURAHAN_WP,
                $this->CPM_LUAR_DAERAH,
                $this->CPM_JENIS_WP,
                'sistem',
                date('Y-m-d H:i:s'),
                $this->CPM_SURAT_IZIN,
                $this->CPM_NO_SURAT_IZIN,
                $this->CPM_TGL_SURAT_IZIN,
                $this->CPM_JENIS_KEWARGANEGARAAN,
                $this->CPM_JENIS_TANDABUKTI,
                $this->CPM_NO_TANDABUKTI,
                $this->CPM_TGL_TANDABUKTI,
                $this->CPM_NO_KK,
                $this->CPM_TGL_KK,
                $this->CPM_NO_NPWP,
                $this->CPM_JENIS_PEKERJAAN,
                $this->CPM_NAMA_USAHA,
                $this->CPM_ALAMAT_USAHA,
                $this->CPM_RTRW_WP,
                $this->CPM_KOTA_WP,
                $this->CPM_KODEPOS_WP,
                $this->CPM_FILE_TANDABUKTI,
                $this->CPM_FILE_KK,
                $this->CPM_FILE_NPWP
            );

            $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

            if ($res == true) {
                $this->CTR_U_PWD = md5($this->NPASSWORD);
                $res = $this->save_core_user();

                if ($res) {
                    $_SESSION['_success'] = 'WP berhasil disimpan';
                } else {
                    $_SESSION['_error'] = 'WP gagal disimpan';
                }
            } else {
                $res = false;
                $msg = "Gagal disimpan, User Name sudah terdaftar sebelumnya!";
                $this->Message->setMessage($msg);
                $_SESSION['_error'] = $msg;
            }
        } else {
            $res = false;
            $msg = "Gagal disimpan, User Name sudah terdaftar sebelumnya!";
            $this->Message->setMessage($msg);
            $_SESSION['_error'] = $msg;
        }
        mysqli_close($this->Conn);
        return $res;
    }

    public function daftar()
    {
        $this->CPM_STATUS = 0;
        $this->CTR_U_BLOCKED = 1;
        $this->CPM_AUTHOR = $this->CPM_USER;
        $this->_a = "aPatda";
        if ($this->save() == true) {
            $this->Message->setMessage("WP baru berhasil di daftarkan, anda belum bisa login aplikasi, silakan tunggu konfirmasi balik dari administrator!", 1);
        }
        $this->redirect("../../../registrasi_wp");
    }

    private function update_core_user()
    {
        // cek central user
        if ($cek = mysqli_fetch_assoc(mysqli_query($this->Conn, sprintf("SELECT * from CENTRAL_USER WHERE CTR_U_ID='%s'", $this->CPM_USER)))) {
            // ada data, update
            $query = sprintf("UPDATE CENTRAL_USER SET CTR_U_PWD = '%s' WHERE CTR_U_ID = '{$this->CPM_USER}'", md5($this->NPASSWORD));
        } else {
            // tidak ada data, insert
            return $this->save_core_user();
        }

        return mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
    }

    // private function update_core_user() {
    //     $query = sprintf("UPDATE CENTRAL_USER SET CTR_U_PWD = '%s' WHERE CTR_U_ID = '{$this->CPM_USER}'", md5($this->NPASSWORD));
    //     return mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
    // }

    private function save_core_user()
    {
        $this->CTR_U_STYLE = $this->get_config_value($this->_a, "STYLE_APP");
		
        $query = sprintf("INSERT INTO CENTRAL_USER 
            (CTR_U_ID, CTR_U_UID, CTR_U_PWD, CTR_U_ADMIN, CTR_U_STYLE, CTR_U_BLOCKED,CTR_U_MULT_LOGIN) VALUES
            ('%s','%s','%s','%s','%s','%s','%s')", $this->CPM_USER, $this->CPM_USER, $this->CTR_U_PWD, $this->CTR_U_ADMIN, $this->CTR_U_STYLE, $this->CTR_U_BLOCKED,"0");
        $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
		//$this->_a tambahan untuk register
        if ($res == true) {
            $query = sprintf("INSERT INTO CENTRAL_USER_TO_APP 
            (CTR_USER_ID, CTR_APP_ID, CTR_RM_ID) VALUES 
            ('%s','%s','%s')", $this->CPM_USER, $this->_a, $this->CTR_RM_ID);
            $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
        }
		
        return $res;
    }

    public function delete()
    {
        $query = "DELETE FROM PATDA_WP WHERE CPM_USER = '{$this->CPM_USER}'";
        $res = mysqli_query($this->Conn, $query);
        if ($res == true) {
            $res = $this->delete_core_user();

            if ($res) {
                $_SESSION['_success'] = 'WP berhasil dihapus';
            } else {
                $_SESSION['_error'] = 'WP gagal dihapus';
            }
        } else {
            $_SESSION['_error'] = 'WP gagal dihapus';
        }
    }

    private function delete_core_user()
    {
        $query = "DELETE FROM CENTRAL_USER WHERE CTR_U_ID = '{$this->CPM_USER}'";
        $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

        if ($res) {
            $query = "DELETE FROM CENTRAL_USER_TO_APP WHERE CTR_USER_ID= '{$this->CPM_USER}'";
            $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
        }

        return $res;
    }

    public function aktivasi()
    {
        $status = 1;
        $blok = 0;
        $query = "UPDATE PATDA_WP SET
                    CPM_STATUS = '{$status}'
                    WHERE CPM_USER = '{$this->CPM_USER}'";

        $res = mysqli_query($this->Conn, $query);
        if (mysqli_num_rows($res) == 0) {
            $query = "UPDATE CENTRAL_USER SET CTR_U_BLOCKED = '{$blok}' WHERE CTR_U_ID = '{$this->CPM_USER}'";
            $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));
            if ($res) {
                $_SESSION['_success'] = 'WP berhasil diaktifkan';
            } else {
                $_SESSION['_error'] = 'WP gagal diaktifkan';
            }
        }
    }

    public function blok()
    {
        $status = 2;
        $blok = 1;
        $query = "UPDATE PATDA_WP SET
                    CPM_STATUS = '{$status}'
                    WHERE CPM_USER = '{$this->CPM_USER}'";

        $res = mysqli_query($this->Conn, $query);
        if (mysqli_num_rows($res) == 0) {
            $query = "UPDATE CENTRAL_USER SET CTR_U_BLOCKED = '{$blok}' WHERE CTR_U_ID = '{$this->CPM_USER}'";
            $res = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

            if ($res) {
                $_SESSION['_success'] = 'WP berhasil diblok';
            } else {
                $_SESSION['_error'] = 'WP gagal diblok';
            }
        }
    }

    public function captcha()
    {
        echo ($this->CAPTCHA == $_SESSION['securimage_code_value']) ? 1 : 0;
    }

    public function getDataWP()
    {
        #inisialisasi data kosong
        $data = array(
            "CPM_USER" => "",
            "CPM_NPWPD" => "",
            "CPM_NAMA_WP" => "",
            "CPM_ALAMAT_WP" => "",
            "CPM_TELEPON_WP" => "",
            "CPM_KECAMATAN_WP" => "",
            "CPM_KELURAHAN_WP" => "",
            "CPM_JENIS_PAJAK" => "",
            "CPM_JENIS_WP" => "",
            "CPM_SURAT_IZIN" => "",
            "CPM_NO_SURAT_IZIN" => "",
            "CPM_TGL_SURAT_IZIN" => "",
            "CPM_JENIS_KEWARGANEGARAAN" => "",
            "CPM_JENIS_TANDABUKTI" => "",
            "CPM_NO_TANDABUKTI" => "",
            "CPM_TGL_TANDABUKTI" => "",
            "CPM_NO_KK" => "",
            "CPM_TGL_KK" => "",
            "CPM_JENIS_PEKERJAAN" => "",
            "CPM_NAMA_USAHA" => "",
            "CPM_ALAMAT_USAHA" => "",
            "CPM_RTRW_WP" => "",
            "CPM_KOTA_WP" => "",
            "CPM_KODEPOS_WP" => ""
        );

        #query untuk mengambil data wp
        $query = "SELECT * FROM PATDA_WP WHERE CPM_USER = '{$this->_id}'";
        $result = mysqli_query($this->Conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $dataWajib = mysqli_fetch_assoc($result);
            $data = array_merge($data, $dataWajib);
        }
        return $data;
    }

    public function filtering($id)
    {
        $opt_jenispajak = '<option value="">All</option>';
        foreach ($this->arr_pajak as $a => $b) {
            $opt_jenispajak .= "<option value='{$a}'>{$b}</option>";
        }

        $opt_asalwp = '<option value="">All</option><option value="1">Daerah Kab. Lampung Tengah</option><option value="2">Luar Daerah Kab. Lampung Tengah</option>';

        $html = "<div class=\"filtering\">
                    <form>
                        User Name : <input type=\"text\" name=\"CPM_USER-{$id}\" id=\"CPM_USER-{$id}\" >  
                        NPWPD : <input type=\"text\" name=\"CPM_NPWPD-{$id}\" id=\"CPM_NPWPD-{$id}\" >  
                        Jenis Pajak : <select name=\"CPM_JENIS_PAJAK-{$id}\" style=\"width:120px\" id=\"CPM_JENIS_PAJAK-{$id}\">{$opt_jenispajak}</select>    
                        Asal WP : <select name=\"CPM_ASAL_WP-{$id}\" style=\"width:120px\" id=\"CPM_ASAL_WP-{$id}\">{$opt_asalwp}</select>    
                        <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                        <button type=\"button\" id=\"cetak-{$id}\" class=\"download-xls\" data-id=\"{$id}\">Eksport ke Excel</button>
                    </form>
                </div> ";
        return $html;
    }

    public function grid_table()
    {
        $DIR = "PATDA-V1";
        $modul = "registrasi-wp";
        $idTable = uniqid();
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                {$this->filtering($this->_i)}
                <div id=\"{$idTable}-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">
					function printCard (data,pjk) {
                        var windowObjectReference = window.open(\"function/{$DIR}/registrasi-wp/printCard.php?npwp=\"+data+\"&a=" . $_REQUEST['a'] . "&pjk=\"+pjk);
                    }
                    $(document).ready(function() {
                        $('#{$idTable}-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: {$this->pageSize},
                            sorting: true,
                            defaultSorting: 'CPM_GENERATED_DATETIME DESC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',                            
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_GENERATED_DATETIME: {list: false}, 
                                CPM_USER: {title: 'User Name',key: true}, 
                                CPM_NPWPD: {title: 'NPWPD',width: '10%',
                                    display:function(data){
                                        return data.record.CPM_NPWPD_FORMAT;
                                    }
                                },
                                CPM_NAMA_WP: {title: 'Nama Lengkap'}, 
                                CPM_ALAMAT_WP: {title: 'Alamat'}, 
                                CPM_KELURAHAN_WP: {title: 'Kelurahan'}, 
                                CPM_KECAMATAN_WP: {title: 'Kecamatan'}, 
                                CPM_AUTHOR: {title: 'Author',width: '10%'},
                                CPM_JENIS_PAJAK: {title: 'Jenis Pajak',width: '10%'},
								ACTION: {title: 'Action',width: '12%',
                                    display : function (data) {
                                        return '<input type=\"button\" value=\"Kartu Data PDF\" onClick=\"printCard(\''+data.record.CPM_NPWPD+'\',\''+data.record.CPM_JENIS_PAJAK_C+'\');\">';
                                    }
                                }
                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#{$idTable}-{$this->_i}').jtable('load', {
                                CPM_NPWPD : $('#CPM_NPWPD-{$this->_i}').val(),
                                CPM_USER : $('#CPM_USER-{$this->_i}').val(),
                                CPM_JENIS_PAJAK : $('#CPM_JENIS_PAJAK-{$this->_i}').val(),
                                CPM_ASAL_WP : $('#CPM_ASAL_WP-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                        
                    });
                </script>";
        echo $html;
    }

    public function grid_data()
    {
        try {
            $where = "CPM_STATUS = '{$this->_s}' ";

            $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
            $where .= (isset($_REQUEST['CPM_USER']) && $_REQUEST['CPM_USER'] != "") ? " AND CPM_USER like \"{$_REQUEST['CPM_USER']}%\" " : "";
            $where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK like \"%{$_REQUEST['CPM_JENIS_PAJAK']}%\" " : "";
            if (isset($_REQUEST['CPM_ASAL_WP']) && $_REQUEST['CPM_ASAL_WP'] != '') {
                if ($_REQUEST['CPM_ASAL_WP'] == 1) {
                    $where .= " AND CPM_LUAR_DAERAH='0' ";
                } elseif ($_REQUEST['CPM_ASAL_WP'] == 2) {
                    $where .= " AND CPM_LUAR_DAERAH='1' ";
                }
            }

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM PATDA_WP WHERE {$where}";
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT * FROM PATDA_WP WHERE {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->Conn, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            $this->arr_pajak[';'] = ",<br/>";
            #print_r($this->arr_pajak);exit;
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
                $base64 = "a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&id={$row['CPM_USER']}&s={$row['CPM_STATUS']}&i={$this->_i}";
                $url = "main.php?param=" . base64_encode($base64);

                $row['CPM_USER'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$row['CPM_USER']}</a>";
                $row['CPM_NPWPD_FORMAT'] = Pajak::formatNPWPD($row['CPM_NPWPD']);
                $row['CPM_JENIS_PAJAK_C'] = $row['CPM_JENIS_PAJAK'];
                $row['CPM_JENIS_PAJAK'] = '(' . str_replace(array(1, 2, 3, 4, 5, 6, 7, 8, 9, ";"), $this->arr_pajak, $row['CPM_JENIS_PAJAK']) . ')';
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

    private function get_core_user($user)
    {
        $query = "SELECT * FROM CENTRAL_USER WHERE CTR_U_UID = '{$user}'";
        $result = mysqli_query($this->Conn, $query);
        return mysqli_fetch_assoc($result);
    }

    public function print_bukti_registrasi()
    {
        $this->_id = $this->CPM_USER;
        $DATA = $this->getDataWP();

        $DATA['CPM_PWD'] = base64_decode($DATA['CPM_PWD']);

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = $config['ALAMAT_KOTA'];
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];

        $ARR_JENIS_PAJAK = explode(";", $DATA['CPM_JENIS_PAJAK']);

        $radio_jns_pajak[1] = in_array(1, $ARR_JENIS_PAJAK) ? "[v]" : "[_]";
        $radio_jns_pajak[2] = in_array(2, $ARR_JENIS_PAJAK) ? "[v]" : "[_]";
        $radio_jns_pajak[3] = in_array(3, $ARR_JENIS_PAJAK) ? "[v]" : "[_]";
        $radio_jns_pajak[4] = in_array(4, $ARR_JENIS_PAJAK) ? "[v]" : "[_]";
        $radio_jns_pajak[5] = in_array(5, $ARR_JENIS_PAJAK) ? "[v]" : "[_]";
        $radio_jns_pajak[6] = in_array(6, $ARR_JENIS_PAJAK) ? "[v]" : "[_]";
        $radio_jns_pajak[7] = in_array(7, $ARR_JENIS_PAJAK) ? "[v]" : "[_]";
        $radio_jns_pajak[8] = in_array(8, $ARR_JENIS_PAJAK) ? "[v]" : "[_]";
        $radio_jns_pajak[9] = in_array(9, $ARR_JENIS_PAJAK) ? "[v]" : "[_]";

        $url = $config['PATDA_URL_PUBLIK'];
        $Kewarganegaraan_1 = ($DATA['CPM_JENIS_KEWARGANEGARAAN'] == 1) ? "v" : "_";
        $Kewarganegaraan_2 = ($DATA['CPM_JENIS_KEWARGANEGARAAN'] == 2) ? "v" : "_";

        $TBD_1 = ($DATA['CPM_JENIS_TANDABUKTI'] == 1) ? "v" : "_";
        $TBD_2 = ($DATA['CPM_JENIS_TANDABUKTI'] == 2) ? "v" : "_";
        $TBD_3 = ($DATA['CPM_JENIS_TANDABUKTI'] == 3) ? "v" : "_";

        $PU_1 = ($DATA['CPM_JENIS_PEKERJAAN'] == 1) ? "v" : "_";
        $PU_2 = ($DATA['CPM_JENIS_PEKERJAAN'] == 2) ? "v" : "_";
        $PU_3 = ($DATA['CPM_JENIS_PEKERJAAN'] == 3) ? "v" : "_";
        $PU_4 = ($DATA['CPM_JENIS_PEKERJAAN'] == 4) ? "v" : "_";
        $PU_5 = ($DATA['CPM_JENIS_PEKERJAAN'] == 5) ? "v" : "_";

        if ($DATA['CPM_JENIS_WP'] == 1) {
            $html = "<table border=\"1\" cellpadding=\"3\">
                    <tr>
                        <td colspan=\"3\" align=\"center\" width=\"100%\">
                            <table border=\"0\" cellpadding=\"10\">
                                <tr>
                                    <td rowspan=\"2\" align=\"center\" width=\"20%\"></td>
                                    <td align=\"center\" width=\"60%\">
                                        <font size=\"+4\"><b>BADAN PENGELOLA KEUANGAN<br/>DAN ASET DAERAH</b>
                                        </font>
                                    </td>
                                    <!--KOSONG-->
                                    <td rowspan=\"2\" align=\"center\" width=\"20%\">
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"center\">
                                        <font class=\"normal\">{$JALAN}<br/>{$KOTA} - {$PROVINSI} {$KODE_POS}</font>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" align=\"center\">
                                        <font class=\"normal\"><b>SURAT PENDAFTARAN OBJEK PAJAK DAERAH<br/>WAJIB PAJAK PRIBADI</font></b>
                                    </td>
                                </tr>
                                <tr align=\"right\">
                                    <td colspan=\"2\" align=\"left\" width=\"65%\">
                                        &nbsp;
                                    </td>
                                    <td colspan=\"1\" align=\"left\" width=\"100%\">
                                        <font class=\"normal\"><b>Kepada Yth.<br>
                                        ..................................................................<br>
                                        ..................................................................<br>
                                        di ..............................................................</b></font>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"3\" align=\"center\">
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"3\" align=\"left\">
                            PERHATIAN :<br/>
                            &nbsp;&nbsp;1. Harap diisi dalam rangkap dua (2) ditulis dengan hurut CETAK.<br/>
                            &nbsp;&nbsp;2. Beri tanda v pada kotak [_] yang tersedia untuk jawaban yang diberikan.<br/>
                            &nbsp;&nbsp;3. Setelah Formulir Pendaftaran ini diisi dan ditanda tangani, harap diserahkan kembali pada Badan Pengelola Keuangan dan Aset Daerag Kabupaten Banyuasin &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Komp. Perkantoran Pemerintahan Kabupaten Banyuasin langsung atau dikirim melalui pos.
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"3\" align=\"center\">
                            <b>DIISI OLEH SELURUH WAJIB PAJAK PRIBADI</b>
                        </td>
                    </tr>
                    <tr>            
                        <td colspan=\"3\">
                            <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
                                <!--<tr><td colspan=\"3\" ALIGN=\"center\"><font size=\"+2\">BUKTI REGISTRASI USER<br /></font></td></tr>-->
                                <tr>
                                    <td width=\"200\">1. Nama Lengkap</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> " . $DATA['CPM_NAMA_WP'] . "</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">2. Kewarganegaraan</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> [{$Kewarganegaraan_1}] WNI [{$Kewarganegaraan_2}] WNA</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"340\">3. Alamat (Photo copy Surat Keterangan Domisili dilampirkan)</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-Jalan/No</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_ALAMAT_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-RT/RW/RK</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_RTRW_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-Kelurahan</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_KELURAHAN_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-Kecamatan</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_KECAMATAN_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-Kabupaten/Kotamadya</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_KOTA_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-Nomor Telepon</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_TELEPON_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-Kode Pos</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_KODEPOS_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">4. Tanda Bukti Diri</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> [{$TBD_1}] KTP [{$TBD_2}] SIM [{$TBD_3}] PASPOR</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">5. No. dan Tgl. Tanda Bukti Diri</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_DATA_TANDABUKTI']}</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"340\">&nbsp;&nbsp;&nbsp;(Photo Copy dilampirkan)</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">6. No. dan Tgl. Kartu Keluraga</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_DATA_KK']}</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"340\">&nbsp;&nbsp;&nbsp;(Photo Copy dilampirkan)</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">7. Pekerjaan/Usaha</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> [{$PU_1}] Pegawai Negeri [{$PU_2}] Pegawai Swasta [{$PU_3}] ABRI &nbsp;[{$PU_4}] Pemilik Usaha [{$PU_5}] Lainnya</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">8. Nama Instansi tempat pekerjaan <br/>&nbsp;&nbsp;&nbsp;&nbsp;atau Usaha</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_NAMA_USAHA']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">9. Alamat</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_ALAMAT_USAHA']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">10. NPWPD</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\">" . Pajak::formatNPWPD($DATA['CPM_NPWPD']) . "</td>
                                </tr>
                                <tr>
                                    <td>11. Jenis Pajak</td><td>:</td>
                                    <td><table>";
            foreach ($this->arr_pajak as $a => $b) {
                $html .= "<tr><td>{$radio_jns_pajak[$a]} {$b}</td></tr>";
            }
            $html .= "</table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"left\" colspan=\"2\" width=\"440\">&nbsp;</td>
                                    <td align=\"left\" colspan=\"1\" width=\"300\">
                                        ..................................................20..........
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"left\" colspan=\"1\" width=\"440\">&nbsp;</td>
                                    <td align=\"left\" colspan=\"1\" width=\"100\">Nama Jelas</td>
                                    <td align=\"left\" colspan=\"1\" width=\"200\"> : </td>
                                </tr>
                                <tr>
                                    <td align=\"left\" colspan=\"1\" width=\"440\">&nbsp;</td>
                                    <td align=\"left\" colspan=\"1\" width=\"100\">Tanda Tangan</td>
                                    <td align=\"left\" colspan=\"1\" width=\"200\"> : </td>
                                </tr>
                                <tr>
                                    <td align=\"left\" colspan=\"3\" width=\"440\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" colspan=\"2\" width=\"380\">DIISI OLEH PETUGAS PENERIMA</td>
                                    <td align=\"left\" colspan=\"1\" width=\"300\">DIISI OLEH PETUGAS PECATATAN DATA</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" colspan=\"1\" width=\"200\">Diterima tanggal</td>
                                    <td align=\"left\" colspan=\"1\" width=\"180\"> : </td>
                                    <td align=\"left\" colspan=\"1\" width=\"300\">NPWPD yang diberikan</td>
                                </tr>
                                <tr>
                                    <td align=\"left\" colspan=\"1\" width=\"200\">Alamat Jelas/NIP</td>
                                    <td align=\"left\" colspan=\"1\" width=\"180\"> : </td>
                                    <td align=\"left\" colspan=\"1\" width=\"300\"><b>" . Pajak::formatNPWPD($DATA['CPM_NPWPD']) . "</b></td>
                                </tr>
                                <tr>
                                    <td align=\"left\" colspan=\"1\" width=\"200\">Tanda Tangan</td>
                                    <td align=\"left\" colspan=\"1\" width=\"180\"> : </td>
                                    <td align=\"left\" colspan=\"1\" width=\"300\">&nbsp;</td>
                                </tr>
                            </table>					
                        </td>
                    </tr> 
                    <tr>
                        <td colspan=\"3\" align=\"center\">
                            ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                        </td>
                    </tr><tr>            
                        <td colspan=\"3\">
                            <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
                                <tr>
                                    <td colspan=\"3\" width=\"80%\" align=\"right\">No. Formulir : ..................................&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"80%\" align=\"left\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"100%\" align=\"center\"><b>TANDA TANGAN</b></td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"80%\" align=\"left\"><br/>Nama &nbsp; : ..............................................................................</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"80%\" align=\"left\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"80%\" align=\"left\">Alamat : ..............................................................................</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"80%\" align=\"left\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"100%\" align=\"center\">...................................Tahun...................................</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"100%\" align=\"center\">Yang Menerima</td>
                                </tr>
                            </table>                    
                        </td>
                    </tr>                   
                </table>";
            // echo $html;exit();
        } else if ($DATA['CPM_JENIS_WP'] == 2) {
            $html = "<table border=\"1\" cellpadding=\"3\">
                    <tr>
                        <td colspan=\"3\" align=\"center\" width=\"100%\">
                            <table border=\"0\" cellpadding=\"10\">
                                <tr>
                                    <td rowspan=\"2\" align=\"center\" width=\"20%\"></td>
                                    <td align=\"center\" width=\"60%\">
                                        <font size=\"+4\"><b>BADAN PENGELOLA KEUANGAN<br/>DAN ASET DAERAH</b>
                                        </font>
                                    </td>
                                    <!--KOSONG-->
                                    <td rowspan=\"2\" align=\"center\" width=\"20%\">
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"center\">
                                        <font class=\"normal\">{$JALAN}<br/>{$KOTA} - {$PROVINSI} {$KODE_POS}</font>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" align=\"center\">
                                        <font class=\"normal\"><b>SURAT PENDAFTARAN OBJEK PAJAK DAERAH<br/>WAJIB PAJAK BADAN</font></b>
                                    </td>
                                </tr>
                                <tr align=\"right\">
                                    <td colspan=\"2\" align=\"left\" width=\"65%\">
                                        &nbsp;
                                    </td>
                                    <td colspan=\"1\" align=\"left\" width=\"100%\">
                                        <font class=\"normal\"><b>Kepada Yth.<br>
                                        ..................................................................<br>
                                        ..................................................................<br>
                                        di ..............................................................</b></font>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"3\" align=\"center\">
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"3\" align=\"left\">
                            PERHATIAN :<br/>
                            &nbsp;&nbsp;1. Harap diisi dalam rangkap dua (2) ditulis dengan hurut CETAK.<br/>
                            &nbsp;&nbsp;2. Beri tanda v pada kotak [_] yang tersedia untuk jawaban yang diberikan.<br/>
                            &nbsp;&nbsp;3. Setelah Formulir Pendaftaran ini diisi dan ditanda tangani, harap diserahkan kembali pada Badan Pengelola Keuangan dan Aset Daerag Kabupaten Banyuasin &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Komp. Perkantoran Pemerintahan Kabupaten Banyuasin langsung atau dikirim melalui pos.
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"3\" align=\"center\">
                            <b>DIISI OLEH SELURUH WAJIB PAJAK BADAN</b>
                        </td>
                    </tr>
                    <tr>            
                        <td colspan=\"3\">
                            <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
                                <!--<tr><td colspan=\"3\" ALIGN=\"center\"><font size=\"+2\">BUKTI REGISTRASI USER<br /></font></td></tr>-->
                                <tr>
                                    <td width=\"200\">1. Nama Badan / Merk Usaha</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> " . $DATA['CPM_NAMA_WP'] . "</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"340\">2. Alamat (Photo copy Surat Keterangan Domisili dilampirkan)</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-Jalan/No</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_ALAMAT_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-RT/RW/RK</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_RTRW_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-Kelurahan</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_KELURAHAN_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-Kecamatan</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_KECAMATAN_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-Kabupaten/Kotamadya</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_KOTA_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-Nomor Telepon</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_TELEPON_WP']}</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">&nbsp;&nbsp;-Kode Pos</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"> {$DATA['CPM_KODEPOS_WP']}</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"340\">3. Surat Izin yang dimiliki (Photo copy Surat Izin harap dilampirkan)</td>
                                </tr>
                                <tr>
                                    <td width=\"220\">&nbsp;&nbsp;-Surat Izin Tempat Usaha</td>
                                    <td width=\"220\">No. {$DATA['CPM_NO_SURAT_IZIN']}</td>
                                    <td width=\"220\">Tgl. {$DATA['CPM_TGL_SURAT_IZIN']}</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"80%\" align=\"left\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width=\"200\">4. Bidang Usaha</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"><table>";
            foreach ($this->arr_pajak as $a => $b) {
                $html .= "<tr><td>{$radio_jns_pajak[$a]} {$b}</td></tr>";
            }
            $html .= "</table>
                                    </td>
                                </tr>
                                <tr>
                                    <td width=\"200\">5. NPWPD</td>
                                    <td width=\"20\">:</td>
                                    <td width=\"220\"><b>" . Pajak::formatNPWPD($DATA['CPM_NPWPD']) . "</b></td>
                                </tr>
                            </table>                    
                        </td>
                    </tr> 
                    <tr>
                        <td colspan=\"3\" align=\"center\">
                            ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                        </td>
                    </tr><tr>            
                        <td colspan=\"3\">
                            <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
                                <tr>
                                    <td colspan=\"3\" width=\"80%\" align=\"left\">KETERANGAN PEMILIK ATAU PENGELOLA</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"80%\" align=\"left\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"80%\" align=\"left\"><br/>Nama Pemilik/Pengelola &nbsp; : ..............................................................................</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"80%\" align=\"left\">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan=\"3\" width=\"80%\" align=\"left\">Jabatan : ..............................................................................</td>
                                </tr>
                            </table>                    
                        </td>
                    </tr>                   
                </table>";
            // echo $html;exit();
        }

        require_once("../../../inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('-');
        $pdf->SetSubject('-');
        $pdf->SetKeywords('-');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(2, 4, 2);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Image('../../../view/Registrasi/configure/logo/' . $LOGO_CETAK_PDF, 11, 11, 19, '', '', '', '', false, 300, '', false);
        $pdf->SetAlpha(0.3);

        $pdf->Output('disposisi.pdf', 'I');
    }

    function kel_nama_to_id($kel)
    {
        $query = "SELECT CPM_KEL_ID FROM PATDA_MST_KELURAHAN WHERE CPM_KELURAHAN = '{$kel}' ";
        $res = mysqli_query($this->Conn, $query);

        $list = "";
        if ($row = mysqli_fetch_object($res)) {
            $list = $row->CPM_KEL_ID;
        }

        return $list;
    }

    function print_form_daftar()
    {
        if ($this->CPM_JENIS_WP == 1) {
            $this->form_daftar_pribadi();
        } elseif ($this->CPM_JENIS_WP == 2) {
            $this->form_daftar_badan();
        }
        exit;
    }

    private function form_daftar_pribadi()
    {
        $this->_id = $this->CPM_USER;
        $DATA = $this->getDataWP();

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $NAMA_BADAN = $config['NAMA_BADAN_PENGELOLA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = ucwords(strtolower($config['ALAMAT_KOTA']));
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];

        $KEPALA_NAMA = $config['KEPALA_DINAS_NAMA'];
        $KEPALA_NIP = $config['KEPALA_DINAS_NIP'];
        $VERIFIKASI_NAMA = $config['BAG_VERIFIKASI_NAMA'];
        $VERIFIKASI_NIP = $config['BAG_VERIFIKASI_NIP'];
        $PENDATAAN_NAMA = $config['KABID_PENDATAAN_NAMA'];
        $PENDATAAN_NIP = $config['KABID_PENDATAAN_NIP'];


        // potong bagian NPWPD: P.a.xxxxxxx.yy.xx
        $wp_kode = substr($DATA['CPM_NPWPD'], 0, 1);
        $wp_jenis = substr($DATA['CPM_NPWPD'], 1, 1);
        $wp_nourut = substr($DATA['CPM_NPWPD'], 2, 7); // 
        $wp_kec = substr($DATA['CPM_NPWPD'], 9, 2);
        $wp_kel = substr($DATA['CPM_NPWPD'], 10, 2);

        // ceklis
        $cek_wni = $DATA['CPM_JENIS_KEWARGANEGARAAN'] == 'WNI' ? '<span style="font-family:zapfdingbats">3</span>' : '<span style="font-family:zapfdingbats;">q</span>';
        $cek_wna = $DATA['CPM_JENIS_KEWARGANEGARAAN'] == 'WNA' ? '<span style="font-family:zapfdingbats">3</span>' : '<span style="font-family:zapfdingbats;">q</span>';

        $cek_ktp = $DATA['CPM_JENIS_TANDABUKTI'] == 'KTP' ? '<span style="font-family:zapfdingbats">3</span>' : '<span style="font-family:zapfdingbats;">q</span>';
        $cek_sim = $DATA['CPM_JENIS_TANDABUKTI'] == 'SIM' ? '<span style="font-family:zapfdingbats">3</span>' : '<span style="font-family:zapfdingbats;">q</span>';
        $cek_paspor = $DATA['CPM_JENIS_TANDABUKTI'] == 'PASPOR' ? '<span style="font-family:zapfdingbats">3</span>' : '<span style="font-family:zapfdingbats;">q</span>';

        $data_tandabukti = array();
        if ($DATA['CPM_NO_TANDABUKTI'] != '') $data_tandabukti[] = 'No: ' . $DATA['CPM_NO_TANDABUKTI'];
        if ($DATA['CPM_TGL_TANDABUKTI'] != '') $data_tandabukti[] = 'Tanggal: ' . $DATA['CPM_TGL_TANDABUKTI'];

        $data_kk = array();
        if ($DATA['CPM_NO_KK'] != '') $data_kk[] = 'No: ' . $DATA['CPM_NO_KK'];
        if ($DATA['CPM_TGL_KK'] != '') $data_kk[] = 'Tanggal: ' . $DATA['CPM_TGL_KK'];

        $url = $config['PATDA_URL_PUBLIK'];

        $html = "<table border=\"0\" cellpadding=\"7\">
                    <tr>
                        <td align=\"center\" width=\"15%\"></td>
                        <td align=\"center\" width=\"60%\">
                            <font size=\"+1\"> <b>PEMERINTAH " . strtoupper($KOTA) . "<br/>
                            {$NAMA_BADAN}</b></font><br>
                            <font class=\"normal\">{$JALAN}<br/>
                            {$KOTA} - {$PROVINSI} {$KODE_POS}</font>
                        </td>
                        <td align=\"center\" width=\"25%\">
                            <br><br><b>Nomor Formulir</b><br>
                            <font size=\"+2\">{$wp_nourut}</font>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"3\" align=\"center\" style=\"border-top:1px solid #000\">
                            <font size=\"+1\"><b>FORMULIR PENDAFTARAN<br>																																		
                            WAJIB PAJAK / RETRIBUSI PRIBADI</b></font>
                        </td>
                    </tr>
                    <tr>
                        <td width=\"15%\"></td>
                        <td width=\"40%\"></td>
                        <td width=\"45%\"><font size=\"-1\">Yth.
                        <br>{$KEPALA_NAMA}
                        <br>KEPALA {$NAMA_BADAN}
                        <br>Di KALIANDA</font>           
                        </td>
                    </tr>
                    <tr>            
                        <td colspan=\"3\">
                        <table>
                        <tr><td colspan=\"2\"><b>PERHATIAN: </b></td></tr>
                        <tr><td width=\"15\">1.</td><td width=\"670\">Harap diisi dalam rangkap dua dan ditulis dengan huruf CETAK</td></tr>
                        <tr><td>2.</td><td>Berita tanda <span style=\"font-family:zapfdingbats;\">3</span> pada kotak yang tersedia untuk jawaban yang diberikan</td></tr>
                        <tr><td>3.</td><td>Setelah Formulir Pendaftaran ini diisi dan ditandatangani, harap diserahkan kembali kepada pemerintah kabupaten lampung selatan , langsung atau dikirim melalui pos paling lambat tanggal </td></tr>
                        </table><br><br>

                        <b>DIISI OLEH WAJIB PAJAK/RETRIBUSI PRIBADI</b><br>
                            <table>
                                <tr>
                                    <td width=\"20\">1.</td>
                                    <td width=\"200\">Nama Lengkap</td>
                                    <td width=\"10\">:</td>
                                    <td width=\"450\">{$DATA['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr>
                                    <td>2.</td>
                                    <td>Kewarganegawaan</td>
                                    <td colspan=\"2\">:
                                        {$cek_wni} WNI &nbsp; &nbsp;
                                        {$cek_wna} WNA
                                    </td>
                                </tr>
                                <tr>
                                    <td>3.</td>
                                    <td>Alamat Tempat Tinggal</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;Jalan / Nomor</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_ALAMAT_WP']}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;RT / RW</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_RTRW_WP']}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;Kelurahan</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_KELURAHAN_WP']}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;Kecamatan</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_KECAMATAN_WP']}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;Kabupaten / Kotamadya</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_KOTA_WP']}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;Nomor Telepon</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_TELEPON_WP']}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;Kode Pos</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_KODEPOS_WP']}</td>
                                </tr>
                                <tr>
                                    <td>4.</td>
                                    <td>Tanda Bukti Diri</td>
                                    <td>:</td>
                                    <td>{$cek_ktp} KTP &nbsp; &nbsp; {$cek_sim} SIM &nbsp; &nbsp; {$cek_paspor} PASPOR</td>
                                </tr>
                                <tr>
                                    <td>5.</td>
                                    <td>No dan Tanggal Tanda Bukti Diri<br><i>(Fotocopy dilampirkan)</i></td>
                                    <td>:</td>
                                    <td>" . implode(' &nbsp; ', $data_tandabukti) . "</td>
                                </tr>
                                <tr>
                                    <td>6.</td>
                                    <td>No dan Tanggal Kartu Keluraga<br><i>(Fotocopy dilampirkan)</i></td>
                                    <td>:</td>
                                    <td>" . implode(' &nbsp; ', $data_kk) . "</td>
                                </tr>
                                <tr>
                                    <td>7.</td>
                                    <td>Pekerjaan / Usaha</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_JENIS_PEKERJAAN']}</td>
                                </tr>
                                <tr>
                                    <td>8.</td>
                                    <td>Nama Instansi Tempat Pekerjaan atau Usaha</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_NAMA_USAHA']}</td>
                                </tr>
                                <tr>
                                    <td>9.</td>
                                    <td>Alamat</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_ALAMAT_USAHA']}</td>
                                </tr>
                            </table>	
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"3\">
                            <table padding=\"3\">
                            <tr>
                                <td width=\"15%\"></td>
                                <td width=\"15%\"></td>
                                <td width=\"30%\"></td>
                                <td width=\"40%\"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Kaliandra, " . date('j') . ' ' . $this->arr_bulan[date('n')] . ' ' . date('Y') . "<br>
                                Nama Jelas : {$DATA['CPM_NAMA_WP']}<br>
                                Tanda Tangan :<br></td>
                            </tr>
                            <tr>
                                <td colspan=\"2\" align=\"center\" width=\"50%\">
                                    <b>DIISI OLEH PETUGAS PENERIMA</b><br>
                                    Diterima tanggal " . date('j') . ' ' . $this->arr_bulan[date('n')] . ' ' . date('Y') . "<br><br><br><br>

                                    Diterima oleh :<br>
                                    KASUBBID PERHITUNGAN DAN PENETAPAN<br><br><br><br><br>

                                    {$VERIFIKASI_NAMA}<br>
                                    NIP. {$VERIFIKASI_NIP}
                                </td>
                                <td colspan=\"2\" align=\"center\" width=\"50%\">
                                    <b>DIISI OLEH PETUGAS PENCATAT DATA</b><br>
                                    NPWPD yang diberikan :<br>
                                    <b>{$wp_kode} . {$wp_jenis} . {$wp_nourut} . {$wp_kec} . {$wp_kel}</b><br>
                                    NPWRD yang diberikan :<br><br>

                                    Petugas Pencatat Data :<br>
                                    KASUBBID. PENDAFTARAN DAN PENGEMBANGAN<br><br><br><br><br>

                                    {$PENDATAAN_NAMA}<br>
                                    NIP. {$PENDATAAN_NIP}
                                </td>
                            </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"3\" align=\"center\"><font size=\"-1\">--------------------------------------------------------------------------------- <i>Gunting disini</i> ---------------------------------------------------------------------------------</font></td>
                    </tr>
                    <tr>
                        <td colspan=\"2\"></td>
                        <td>No Formulir : {$wp_nourut}</td>
                    </tr>
                    <tr>
                        <td colspan=\"3\" align=\"center\"><b>TANDA TERIMA</b></td>
                    </tr>
                    <tr>
                        <td colspan=\"3\"><table>
                            <tr>
                                <td width=\"15%\"><b>Nama</b></td>
                                <td width=\"85%\">: {$DATA['CPM_NAMA_WP']}</td>
                            </tr>
                            <tr>
                                <td><b>Alamat</b></td>
                                <td>: {$DATA['CPM_ALAMAT_WP']} Kel. {$DATA['CPM_KELURAHAN_WP']} Kec. {$DATA['CPM_KECAMATAN_WP']} Kab. {$DATA['CPM_KOTA_WP']}</td>
                            </tr>
                        </table></td>
                    </tr>
                    <tr>
                        <td colspan=\"2\"></td>
                        <td align=\"center\">KALIANDRA, " . date('j') . ' ' . $this->arr_bulan[date('n')] . ' ' . date('Y') . "
                        <br>Yang Menerima,
                        <br><br><br>(" . strtoupper($DATA['CPM_NAMA_WP']) . ")</td>
                    </tr>
                </table>";
        // echo $html; exit;
        require_once("../../../inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('-');
        $pdf->SetSubject('-');
        $pdf->SetKeywords('-');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(6, 4, 6);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
        $pdf->SetAutoPageBreak(true, 0);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Image('../../../view/Registrasi/configure/logo/' . $LOGO_CETAK_PDF, 7, 5, 14, '', '', '', '', false, 300, '', false);

        $pdf->Output('formulir-pendaftaran-1b.pdf', 'I');
    }

    private function form_daftar_badan()
    {
        $this->_id = $this->CPM_USER;
        $DATA = $this->getDataWP();

        $config = $this->get_config_value($this->_a);
        $LOGO_CETAK_PDF = $config['LOGO_CETAK_PDF'];
        $JENIS_PEMERINTAHAN = $config['PEMERINTAHAN_JENIS'];
        $NAMA_PEMERINTAHAN = $config['PEMERINTAHAN_NAMA'];
        $NAMA_BADAN = $config['NAMA_BADAN_PENGELOLA'];
        $JALAN = $config['ALAMAT_JALAN'];
        $KOTA = ucwords(strtolower($config['ALAMAT_KOTA']));
        $PROVINSI = $config['ALAMAT_PROVINSI'];
        $KODE_POS = $config['ALAMAT_KODE_POS'];

        $KEPALA_NAMA = $config['KEPALA_DINAS_NAMA'];
        $KEPALA_NIP = $config['KEPALA_DINAS_NIP'];
        $VERIFIKASI_NAMA = $config['BAG_VERIFIKASI_NAMA'];
        $VERIFIKASI_NIP = $config['BAG_VERIFIKASI_NIP'];
        $PENDATAAN_NAMA = $config['KABID_PENDATAAN_NAMA'];
        $PENDATAAN_NIP = $config['KABID_PENDATAAN_NIP'];


        // potong bagian NPWPD: P.a.xxxxxxx.yy.xx
        $wp_kode = substr($DATA['CPM_NPWPD'], 0, 1);
        $wp_jenis = substr($DATA['CPM_NPWPD'], 1, 1);
        $wp_nourut = substr($DATA['CPM_NPWPD'], 2, 7); // 
        $wp_kec = substr($DATA['CPM_NPWPD'], 9, 2);
        $wp_kel = substr($DATA['CPM_NPWPD'], 10, 2);

        $data_usaha = array();
        foreach (explode(';', $DATA['CPM_JENIS_PAJAK']) as $i => $pjk) {
            $data_usaha[] = ($i + 1) . '. ' . $this->arr_pajak[$pjk];
        }

        $data_suratijin = array();
        if ($DATA['CPM_SURAT_IZIN'] != '') $data_suratijin[] = $DATA['CPM_SURAT_IZIN'];
        if ($DATA['CPM_NO_SURAT_IZIN'] != '') $data_suratijin[] = 'No: ' . $DATA['CPM_NO_SURAT_IZIN'];
        if ($DATA['CPM_TGL_SURAT_IZIN'] != '') $data_suratijin[] = 'Tanggal: ' . $DATA['CPM_TGL_SURAT_IZIN'];


        $html = "<table border=\"0\" cellpadding=\"7\">
                    <tr>
                        <td align=\"center\" width=\"15%\"></td>
                        <td align=\"center\" width=\"60%\">
                            <font size=\"+1\"> <b>PEMERINTAH " . strtoupper($KOTA) . "<br/>
                            {$NAMA_BADAN}</b></font><br>
                            <font class=\"normal\">{$JALAN}<br/>
                            {$KOTA} - {$PROVINSI} {$KODE_POS}</font>
                        </td>
                        <td align=\"center\" width=\"25%\">
                            <br><br><b>Nomor Formulir</b><br>
                            <font size=\"+2\">{$wp_nourut}</font>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"3\" align=\"center\" style=\"border-top:1px solid #000\">
                            <font size=\"+1\"><b>FORMULIR PENDAFTARAN<br>																																		
                            WAJIB PAJAK / RETRIBUSI BADAN</b></font>
                        </td>
                    </tr>
                    <tr>
                        <td width=\"15%\"></td>
                        <td width=\"40%\"></td>
                        <td width=\"45%\"><font size=\"-1\">Yth.
                        <br>{$KEPALA_NAMA}
                        <br>KEPALA {$NAMA_BADAN}
                        <br>Di KALIANDA</font>           
                        </td>
                    </tr>
                    <tr>            
                        <td colspan=\"3\">
                        <table>
                        <tr><td colspan=\"2\"><b>PERHATIAN: </b></td></tr>
                        <tr><td width=\"15\">1.</td><td width=\"670\">Harap diisi dalam rangkap dua dan ditulis dengan huruf CETAK</td></tr>
                        <tr><td>2.</td><td>Berita tanda <span style=\"font-family:zapfdingbats;\">3</span> pada kotak yang tersedia untuk jawaban yang diberikan</td></tr>
                        <tr><td>3.</td><td>Setelah Formulir Pendaftaran ini diisi dan ditandatangani, harap diserahkan kembali kepada pemerintah kabupaten lampung selatan , langsung atau dikirim melalui pos paling lambat tanggal </td></tr>
                        </table><br><br>

                        <b>DIISI OLEH WAJIB PAJAK/RETRIBUSI BADAN</b><br>
                            <table>
                                <tr>
                                    <td width=\"20\">1.</td>
                                    <td width=\"200\">Nama Lengkap</td>
                                    <td width=\"10\">:</td>
                                    <td width=\"450\">{$DATA['CPM_NAMA_WP']}</td>
                                </tr>
                                <tr>
                                    <td>2.</td>
                                    <td>Alamat Tempat Tinggal</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;Jalan / Nomor</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_ALAMAT_WP']}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;RT / RW</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_RTRW_WP']}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;Kelurahan</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_KELURAHAN_WP']}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;Kecamatan</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_KECAMATAN_WP']}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;Kabupaten / Kotamadya</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_KOTA_WP']}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;Nomor Telepon</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_TELEPON_WP']}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>- &nbsp;Kode Pos</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_KODEPOS_WP']}</td>
                                </tr>
                                <tr>
                                    <td>3.</td>
                                    <td>Surat Ijin yang Dimiliki<br><i>(Fotocopy dilampirkan)</i></td>
                                    <td>:</td>
                                    <td>" . implode(' &nbsp; ', $data_suratijin) . "</td>
                                </tr>
                                <tr>
                                    <td>4.</td>
                                    <td>Bidang Usaha</td>
                                    <td>:</td>
                                    <td>" . implode('<br>', $data_usaha) . "</td>
                                </tr>
                                <!--<tr>
                                    <td>5.</td>
                                    <td>No dan Tanggal Tanda Bukti Diri<br><i>(Fotocopy dilampirkan)</i></td>
                                    <td>:</td>
                                    <td>" . implode(' &nbsp; ', $data_tandabukti) . "</td>
                                </tr>
                                <tr>
                                    <td>6.</td>
                                    <td>No dan Tanggal Kartu Keluraga<br><i>(Fotocopy dilampirkan)</i></td>
                                    <td>:</td>
                                    <td>" . implode(' &nbsp; ', $data_kk) . "</td>
                                </tr>
                                <tr>
                                    <td>7.</td>
                                    <td>Pekerjaan / Usaha</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_JENIS_PEKERJAAN']}</td>
                                </tr>
                                <tr>
                                    <td>8.</td>
                                    <td>Nama Instansi Tempat Pekerjaan atau Usaha</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_NAMA_USAHA']}</td>
                                </tr>
                                <tr>
                                    <td>9.</td>
                                    <td>Alamat</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_ALAMAT_USAHA']}</td>
                                </tr>-->
                                <tr>
                                    <td>5.</td>
                                    <td>Jabatan</td>
                                    <td>:</td>
                                    <td>{$DATA['CPM_JABATAN']}</td>
                                </tr>
                            </table>	
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"3\">
                            <table padding=\"3\">
                            <tr>
                                <td width=\"15%\"></td>
                                <td width=\"15%\"></td>
                                <td width=\"30%\"></td>
                                <td width=\"40%\"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Kaliandra, " . date('j') . ' ' . $this->arr_bulan[date('n')] . ' ' . date('Y') . "<br><br>
                                Nama Jelas : {$DATA['CPM_NAMA_WP']}<br>
                                Tanda Tangan :<br></td>
                            </tr>
                            <tr>
                                <td colspan=\"2\" align=\"center\" width=\"50%\">
                                    <b>DIISI OLEH PETUGAS PENERIMA</b><br>
                                    Diterima tanggal " . date('j') . ' ' . $this->arr_bulan[date('n')] . ' ' . date('Y') . "<br><br><br><br>

                                    Diterima oleh :<br>
                                    KASUBBID PERHITUNGAN DAN PENETAPAN<br><br><br><br><br>

                                    {$VERIFIKASI_NAMA}<br>
                                    NIP. {$VERIFIKASI_NIP}
                                </td>
                                <td colspan=\"2\" align=\"center\" width=\"50%\">
                                    <b>DIISI OLEH PETUGAS PENCATAT DATA</b><br>
                                    NPWPD yang diberikan :<br>
                                    <b>{$wp_kode} . {$wp_jenis} . {$wp_nourut} . {$wp_kec} . {$wp_kel}</b><br>
                                    NPWRD yang diberikan :<br><br>

                                    Petugas Pencatat Data :<br>
                                    KASUBBID. PENDAFTARAN DAN PENGEMBANGAN<br><br><br><br><br>

                                    {$PENDATAAN_NAMA}<br>
                                    NIP. {$PENDATAAN_NIP}
                                </td>
                            </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=\"3\" align=\"center\"><font size=\"-1\">--------------------------------------------------------------------------------- <i>Gunting disini</i> ---------------------------------------------------------------------------------</font></td>
                    </tr>
                    <tr>
                        <td colspan=\"2\"></td>
                        <td>No Formulir : {$wp_nourut}</td>
                    </tr>
                    <tr>
                        <td colspan=\"3\" align=\"center\"><b>TANDA TERIMA</b></td>
                    </tr>
                    <tr>
                        <td colspan=\"3\"><table>
                            <tr>
                                <td width=\"15%\"><b>Nama</b></td>
                                <td width=\"85%\">: {$DATA['CPM_NAMA_WP']}</td>
                            </tr>
                            <tr>
                                <td><b>Alamat</b></td>
                                <td>: {$DATA['CPM_ALAMAT_WP']} Kel. {$DATA['CPM_KELURAHAN_WP']} Kec. {$DATA['CPM_KECAMATAN_WP']} Kab. {$DATA['CPM_KOTA_WP']}</td>
                            </tr>
                        </table></td>
                    </tr>
                    <tr>
                        <td colspan=\"2\"></td>
                        <td align=\"center\">KALIANDRA, " . date('j') . ' ' . $this->arr_bulan[date('n')] . ' ' . date('Y') . "
                        <br>Yang Menerima,
                        <br><br><br><br>(" . strtoupper($DATA['CPM_NAMA_WP']) . ")</td>
                    </tr>
                </table>";
        // echo $html; exit;
        require_once("../../../inc/payment/tcpdf/tcpdf.php");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('vpost');
        $pdf->SetTitle('-');
        $pdf->SetSubject('-');
        $pdf->SetKeywords('-');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(6, 5, 6);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
        $pdf->SetAutoPageBreak(true, 0);

        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Image('../../../view/Registrasi/configure/logo/' . $LOGO_CETAK_PDF, 7, 5, 16, '', '', '', '', false, 300, '', false);

        $pdf->Output('formulir-pendaftaran-1a.pdf', 'I');
    }

    function download_excel()
    {
        // echo '<pre>';
        // print_r($_POST);
        // print_r($_GET);
        // print_r($this);
        // exit;

        $z = 0;
        $where = '';
        switch ($this->_s) {
            case '1':
                $where = "CPM_STATUS = '1'";
                break;
            case '2':
                $where = "CPM_STATUS = '0'";
                break;
            case '3':
                $where = "CPM_STATUS = '2'";
                break;
        }

        $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_USER']) && $_REQUEST['CPM_USER'] != "") ? " AND CPM_USER like \"{$_REQUEST['CPM_USER']}%\" " : "";
        $where .= (isset($_REQUEST['CPM_JENIS_PAJAK']) && $_REQUEST['CPM_JENIS_PAJAK'] != "") ? " AND CPM_JENIS_PAJAK like \"%{$_REQUEST['CPM_JENIS_PAJAK']}%\" " : "";
        if (isset($_REQUEST['CPM_ASAL_WP']) && $_REQUEST['CPM_ASAL_WP'] != '') {
            if ($_REQUEST['CPM_ASAL_WP'] == 1) {
                $where .= " AND CPM_LUAR_DAERAH='0' ";
            } elseif ($_REQUEST['CPM_ASAL_WP'] == 2) {
                $where .= " AND CPM_LUAR_DAERAH='1' ";
            }
        }

        #count utk pagging
        $query = "SELECT COUNT(*) AS RecordCount FROM PATDA_WP WHERE {$where}";
        $result = mysqli_query($this->Conn, $query);
        $row = mysqli_fetch_assoc($result);
        $recordCount = $row['RecordCount'];

        #query select list data
        $query = "SELECT * FROM PATDA_WP WHERE {$where}";
        //-- ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
        $result = mysqli_query($this->Conn, $query);

        // echo $query;exit;

        $objPHPExcel = new PHPExcel();
        // Set properties
        $objPHPExcel->getProperties()->setCreator("vpost")
            ->setLastModifiedBy("vpos")
            ->setTitle("-")
            ->setSubject("-")
            ->setDescription("patda")
            ->setKeywords("-");

        // Add some data
        $objPHPExcel->setActiveSheetIndex($z)
            ->setCellValue('A1', 'No.')
            ->setCellValue('B1', 'Username')
            ->setCellValue('C1', 'NPWPD')
            ->setCellValue('D1', 'Nama Lengkap')
            ->setCellValue('E1', 'Alamat')
            ->setCellValue('F1', 'Kelurahan')
            ->setCellValue('G1', 'Kecamatan')
            ->setCellValue('H1', 'Jenis Pajak')
            ->setCellValue('I1', 'Tgl Terdaftar');

        // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex($z);

        $row = 2;
        $sumRows = mysqli_num_rows($res);

        while ($rowData = mysqli_fetch_assoc($result)) {
            $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
            $rowData['CPM_JENIS_PAJAK'] = str_replace(array(1, 2, 3, 4, 5, 6, 7, 8, 9), $this->arr_pajak, $rowData['CPM_JENIS_PAJAK']);
            $rowData['CPM_JENIS_PAJAK'] = str_replace(';', ', ', $rowData['CPM_JENIS_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $rowData['CPM_USER'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $rowData['CPM_NPWPD'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['CPM_NAMA_WP']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_ALAMAT_WP']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_KECAMATAN_WP']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_KELURAHAN_WP']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_JENIS_PAJAK']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, date('d-m-Y', strtotime($rowData['CPM_GENERATED_DATETIME'])));
            $row++;
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Daftar Wajib Pajak');

        //----set style cell
        //style header
        $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:C' . ($row - 1))->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:I' . ($row - 1))->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFill()->getStartColor()->setRGB('E4E4E4');

        for ($x = "A"; $x <= "I"; $x++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
        }

        // $kecamatan = $rows['CPM_KECAMATAN'];
        // $objPHPExcel->getActiveSheet()->setTitle("$kecamatan");
        // $objPHPExcel->createSheet();

        $z++;

        ob_clean();
        // Redirect output to a client�s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Data-Wajib-Pajak-' . date('YmdHi') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
        $objWriter->save('php://output');
    }
}
