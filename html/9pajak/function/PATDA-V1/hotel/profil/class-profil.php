<?php

class ProfilPajak extends Pajak {

    private $CPM_GOL_HOTEL;
    private $CPM_DEVICE_ID;
    private $CPM_TGL_UPDATE;
    private $CPM_AKTIF;
    private $CPM_APPROVE;

    function __construct() {
        parent::__construct();
		
		$PROFIL = isset($_POST['PROFIL']) ? $_POST['PROFIL'] : array();
        foreach ($PROFIL as $a => $b) {
            $this->$a = mysql_escape_string(trim($b));
        }
        $this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
    }

    public function update() {
        #update profil

        $query = sprintf("UPDATE PATDA_HOTEL_PROFIL SET
                    CPM_NPWPD = '%s',
                    CPM_NAMA_WP = '%s',
                    CPM_ALAMAT_WP = '%s',
                    CPM_NAMA_OP = '%s',
                    CPM_ALAMAT_OP = '%s',
					CPM_KELURAHAN_OP = '%s',
					CPM_KECAMATAN_OP = '%s',
					CPM_KELURAHAN_WP = '%s',
					CPM_KECAMATAN_WP = '%s',					
                    CPM_GOL_HOTEL = '%s',
                    CPM_AUTHOR = '%s',
                    CPM_NOP = '%s'
                    WHERE CPM_ID = '{$this->CPM_ID}'
                    ", $this->CPM_NPWPD, $this->CPM_NAMA_WP, $this->CPM_ALAMAT_WP, $this->CPM_NAMA_OP, $this->CPM_ALAMAT_OP, $this->CPM_KELURAHAN_OP, $this->CPM_KECAMATAN_OP, $this->CPM_KELURAHAN_WP, $this->CPM_KECAMATAN_WP, $this->CPM_GOL_HOTEL, $this->CPM_AUTHOR, $this->CPM_NOP);
        
        if($res = mysql_query($query, $this->Conn)){
			$_SESSION['_success'] = 'Profil berhasil diupdate';
		}else{
			$_SESSION['_error'] = 'Profil gagal diupdate';
		}
    }

    public function save() {
        $query = "SELECT * FROM PATDA_HOTEL_PROFIL WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' ORDER BY CPM_TGL_UPDATE DESC LIMIT 0,1";
        $res = mysql_query($query, $this->Conn);
        if (mysql_num_rows($res) == 0) {
            
        } else {
            $data = mysql_fetch_assoc($res);
            $aktif = $data['CPM_AKTIF'];
            $approve = $data['CPM_APPROVE'];

            #jika profil belum di approve maka hanya bisa update saja
            if ($aktif == '1' && $approve == '0') {
                $this->update();
                return true;
            }
        }

        $this->CPM_ID = c_uuid();
        $this->CPM_AKTIF = 1;
        $this->CPM_APPROVE = 0;

        #update aktif menjadi nol
        $query = "UPDATE PATDA_HOTEL_PROFIL SET CPM_AKTIF ='0' WHERE CPM_NPWPD = '{$this->CPM_NPWPD}'";
        $res = mysql_query($query, $this->Conn);

        #insert profil baru
        $query = sprintf("INSERT INTO PATDA_HOTEL_PROFIL 
                    (CPM_ID, CPM_NPWPD, CPM_NAMA_WP,
                    CPM_ALAMAT_WP, CPM_NAMA_OP, CPM_ALAMAT_OP,
					CPM_KELURAHAN_OP,CPM_KECAMATAN_OP,
					CPM_KELURAHAN_WP,CPM_KECAMATAN_WP,
                    CPM_GOL_HOTEL, CPM_AKTIF, CPM_DEVICE_ID, 
                    CPM_APPROVE, CPM_AUTHOR, CPM_NOP)
                    VALUES ( '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s',
                             '%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s')", $this->CPM_ID, $this->CPM_NPWPD, $this->CPM_NAMA_WP, $this->CPM_ALAMAT_WP, $this->CPM_NAMA_OP, $this->CPM_ALAMAT_OP, $this->CPM_KELURAHAN_OP, $this->CPM_KECAMATAN_OP, $this->CPM_KELURAHAN_WP, $this->CPM_KECAMATAN_WP, $this->CPM_GOL_HOTEL, $this->CPM_AKTIF, $this->CPM_DEVICE_ID, $this->CPM_APPROVE, $this->CPM_AUTHOR, $this->CPM_NOP
        );

        if($res = mysql_query($query, $this->Conn)){
			$_SESSION['_success'] = 'Perubahan Data Berhasil disimpan';
		}else{
			$_SESSION['_error'] = 'Profil gagal disimpan';
		}
    }

    public function update_tapbox() {
        $query = "UPDATE PATDA_HOTEL_PROFIL SET CPM_DEVICE_ID ='{$this->CPM_DEVICE_ID}' WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_ID='{$this->CPM_ID}'";
        mysql_query($query, $this->Conn);
    }

    public function get_last_profil($user) {
        #query untuk mengambil data relasi user dan data profil
        $query = "SELECT 
				P.*, WP.*, 
				KEL.CPM_KELURAHAN AS CPM_NAMA_KELURAHAN_OP , 
				KEC.CPM_KECAMATAN AS CPM_NAMA_KECAMATAN_OP
			FROM PATDA_WP WP 
				LEFT JOIN PATDA_HOTEL_PROFIL P ON P.CPM_NPWPD = WP.CPM_NPWPD AND P.CPM_AKTIF='1' 
				LEFT JOIN PATDA_MST_KELURAHAN AS KEL ON P.CPM_KELURAHAN_OP = KEL.CPM_KEL_ID 
				LEFT JOIN PATDA_MST_KECAMATAN AS KEC ON P.CPM_KECAMATAN_OP = KEC.CPM_KEC_ID
			WHERE WP.CPM_USER = '{$user}' ";
		
		$result = mysql_query($query, $this->Conn);
		$data = $this->get_field_array($result);
		
        $arr_rekening = $this->getRekening();
        $data = array_merge($data, $arr_rekening); 
        
        foreach ($data as $a => $b) {
            if (!is_array($b)) {
                $data[$a] = htmlspecialchars($b);
            }
        }
        return $data;
    }

    public function getRekening() {
        return parent::getRekening("4.1.1.1");
    }

    public function get_profil_byid($id) {
        
        $query = "
			SELECT PR.*, 
			KEC.CPM_KECAMATAN AS CPM_NAMA_KECAMATAN_OP, 
			KEL.CPM_KELURAHAN AS CPM_NAMA_KELURAHAN_OP
			FROM PATDA_HOTEL_PROFIL PR 
			LEFT JOIN PATDA_MST_KECAMATAN KEC ON PR.CPM_KECAMATAN_OP = KEC.CPM_KEC_ID
			LEFT JOIN PATDA_MST_KELURAHAN KEL ON PR.CPM_KELURAHAN_OP = KEL.CPM_KEL_ID
			WHERE PR.CPM_ID = '{$id}' ";
        $result = mysql_query($query, $this->Conn);
		
		$data = mysql_fetch_assoc($result);
		$data['CPM_DEVICE_ID'] = base64_encode($data['CPM_DEVICE_ID']);
        foreach ($data as $a => $b) {
            if (!is_array($b)) {
                $data[$a] = htmlspecialchars($b);
            }
        }
        return $data;
    }

    public function rollback() {
        $query = "DELETE FROM PATDA_HOTEL_PROFIL WHERE CPM_ID='{$this->CPM_ID}'";
        mysql_query($query, $this->Conn);

        $query = "SELECT * FROM PATDA_HOTEL_PROFIL WHERE CPM_NPWPD='{$this->CPM_NPWPD}' ORDER BY CPM_TGL_UPDATE DESC LIMIT 0,1";
        $result = mysql_query($query, $this->Conn);
        $data = mysql_fetch_assoc($result);

        $query = "UPDATE PATDA_HOTEL_PROFIL SET CPM_AKTIF='1' WHERE CPM_ID='{$data['CPM_ID']}'";
        $result = mysql_query($query, $this->Conn);
    }

}

?>
