<?php

class ObjekPajak extends Pajak
{

    private $CPM_REKENING;
    private $CPM_TGL_UPDATE;
    private $CPM_AKTIF;
    private $CPM_APPROVE;

    function __construct()
    {
        parent::__construct();
        $PROFIL = isset($_POST['PROFIL']) ? $_POST['PROFIL'] : array();

        foreach ($PROFIL as $a => $b) {
            $this->$a = is_array($b) ? $b : mysqli_real_escape_string($this->Conn, trim($b));
        }
        $this->CPM_NPWPD = preg_replace("/[^A-Za-z0-9 ]/", '', $this->CPM_NPWPD);
    }

    public function update()
    {
        $PERUNTUKAN_AIR = $_POST['PAJAK']['CPM_PERUNTUKAN'];
        #update profil
        $query = sprintf(
            "UPDATE PATDA_AIRBAWAHTANAH_PROFIL SET
                    CPM_NPWPD = '%s',
                    CPM_NAMA_WP = '%s',
                    CPM_ALAMAT_WP = '%s',
                    CPM_KELURAHAN_WP = '%s',
                    CPM_KECAMATAN_WP = '%s',
                    CPM_NAMA_OP = '%s',
                    CPM_ALAMAT_OP = '%s',
					CPM_KELURAHAN_OP = '%s',
					CPM_KECAMATAN_OP = '%s',
                    CPM_AUTHOR = '%s',
                    CPM_NOP = '%s',
                    CPM_REKENING = '%s',
                    CPM_TELEPON_WP = '%s',
                    CPM_TELEPON_OP = '%s',
                    CPM_PERUNTUKAN_AIR = '%s'
                    WHERE CPM_ID = '{$this->CPM_ID}'
                    ",
            $this->CPM_NPWPD,
            $this->CPM_NAMA_WP,
            $this->CPM_ALAMAT_WP,
            $this->CPM_KELURAHAN_WP,
            $this->CPM_KECAMATAN_WP,
            $this->CPM_NAMA_OP,
            $this->CPM_ALAMAT_OP,
            $this->CPM_KELURAHAN_OP,
            $this->CPM_KECAMATAN_OP,
            $this->CPM_AUTHOR,
            $this->CPM_NOP,
            $this->CPM_REKENING,
            $this->CPM_TELEPON_WP,
            $this->CPM_TELEPON_OP,
            $PERUNTUKAN_AIR
        );
        //         echo "<pre>";
        // var_dump($query);
        // die;


        if ($res = mysqli_query($this->Conn, $query)) {
            $_SESSION['_success'] = 'Profil berhasil diupdate';
        } else {
            $_SESSION['_error'] = 'Profil gagal diupdate';
        }
    }

    public function delete()
    {
        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "ERROR";
        $jTableResult['Message'] = "NPWPD tidak dapat dihapus karena telah melakukan pelaporan!";

        if (isset($_POST['CPM_ID'])) {

            $query = "select count(*) as total FROM PATDA_AIRBAWAHTANAH_PROFIL A INNER JOIN 
			PATDA_AIRBAWAHTANAH_DOC B ON A.CPM_ID = B.CPM_ID_PROFIL
			WHERE A.CPM_ID = '{$_POST['CPM_ID']}'";

            $res = mysqli_query($this->Conn, $query);
            $res_data = mysqli_fetch_assoc($res);
            if ($res_data['total'] == 0) {
                $query = "DELETE FROM PATDA_AIRBAWAHTANAH_PROFIL WHERE CPM_ID = '{$_POST['CPM_ID']}'";
                $res = mysqli_query($this->Conn, $query);
                if ($res) {
                    $jTableResult['Result'] = "OK";
                }
            }
        }
        echo json_encode($jTableResult);
    }

    public function save()
    {
        $PERUNTUKAN_AIR = $_POST['PAJAK']['CPM_PERUNTUKAN'];

        // var_dump($PERUNTUKAN_AIR);exit;

        $query = "SELECT * FROM PATDA_AIRBAWAHTANAH_PROFIL 
        WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_NOP='{$this->CPM_NOP}' ORDER BY CPM_TGL_UPDATE DESC LIMIT 0,1";
        $res = mysqli_query($this->Conn, $query);
        if (mysqli_num_rows($res) == 0) {
        } else {
            $data = mysqli_fetch_assoc($res);
            $aktif = $data['CPM_AKTIF'];
            $approve = $data['CPM_APPROVE'];
            #jika profil belum di approve maka hanya bisa update saja
            if ($aktif == '1' && $approve == '0') {
                $this->update();
                return true;
            }
        }


        /* $query = "SELECT * FROM PATDA_AIRBAWAHTANAH_PROFIL 
        WHERE 
        CPM_NPWPD = '{$this->CPM_NPWPD}' AND 
        CPM_NOP='{$this->CPM_NOP}' AND
        CPM_NAMA_OP='{$this->CPM_NAMA_OP}' AND
        CPM_ALAMAT_OP='{$this->CPM_ALAMAT_OP}' AND
        CPM_TELEPON_OP='{$this->CPM_TELEPON_OP}' AND
        CPM_KELURAHAN_OP='{$this->CPM_KELURAHAN_OP}' AND
        CPM_KECAMATAN_OP='{$this->CPM_KECAMATAN_OP}' AND
        CPM_REKENING='{$this->CPM_REKENING}'
        
        ORDER BY CPM_TGL_UPDATE DESC LIMIT 0,1";
        $res = mysql_query($query, $this->Conn);
        if ($pr = mysql_fetch_assoc($res)) {
			$_SESSION['_success'] = 'NOP '.$this->CPM_NOP.' berhasil perbaharui';
			return true;
		} */

        $this->CPM_ID = c_uuid();
        $this->CPM_AKTIF = 1;
        $this->CPM_APPROVE = 0;

        #update aktif menjadi nol
        $query = "UPDATE PATDA_AIRBAWAHTANAH_PROFIL SET CPM_AKTIF ='0' WHERE CPM_NPWPD = '{$this->CPM_NPWPD}' AND CPM_NOP='{$this->CPM_NOP}'";
        $res = mysqli_query($this->Conn, $query);

        if ($this->CPM_NOP == '') {
            // get last nop
            $res_nop = mysqli_query($this->Conn, "SELECT CPM_NOP from PATDA_AIRBAWAHTANAH_PROFIL WHERE LENGTH(CPM_NOP)=12 order by CPM_NOP desc limit 1");
            if ($last_nop = mysqli_fetch_object($res_nop)) {
                $last_no = substr($last_nop->CPM_NOP, -7, 7);
                $last_no = (int) $last_no;
                $new_nomor = $last_no + 1;
            } else {
                $new_nomor = 1;
            }
            $kdrek = substr('0' . substr($this->CPM_REKENING, 7, 2), -2, 2);
            // jika NP kosong, generate baru dgn format rek + th + + 000000n
            $this->CPM_NOP = $kdrek . date('.y') . substr('000000' . $new_nomor, -7, 7);
        }
        // var_dump($this);exit;
        // echo $last_nop->CPM_NOP."<>";
        // echo $last_no;exit();
        #insert profil baru
        $query = sprintf(
            "INSERT INTO PATDA_AIRBAWAHTANAH_PROFIL 
                    (CPM_ID, CPM_NPWPD, CPM_NAMA_WP,
                    CPM_ALAMAT_WP,CPM_KELURAHAN_WP,CPM_KECAMATAN_WP,
                    CPM_NAMA_OP, CPM_ALAMAT_OP,
					CPM_KELURAHAN_OP,CPM_KECAMATAN_OP,
					CPM_AKTIF, CPM_REKENING,
                    CPM_APPROVE, CPM_AUTHOR, CPM_NOP, 
                    CPM_TELEPON_WP, CPM_TELEPON_OP, longitude, 
                     latitude, CPM_PERUNTUKAN_AIR)
                    VALUES ( '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s',
                             '%s','%s',
                             '%s','%s',
                             '%s','%s','%s',
                             '%s','%s','%s',
                             '%s','%s')",
            $this->CPM_ID,
            $this->CPM_NPWPD,
            $this->CPM_NAMA_WP,
            $this->CPM_ALAMAT_WP,
            $this->CPM_KELURAHAN_WP,
            $this->CPM_KECAMATAN_WP,
            $this->CPM_NAMA_OP,
            $this->CPM_ALAMAT_OP,
            $this->CPM_KELURAHAN_OP,
            $this->CPM_KECAMATAN_OP,
            $this->CPM_AKTIF,
            $this->CPM_REKENING,
            $this->CPM_APPROVE,
            $this->CPM_AUTHOR,
            $this->CPM_NOP,
            $this->CPM_TELEPON_WP,
            $this->CPM_TELEPON_OP,
            $this->longitude,
            // $this->tapping,
            $this->latitude,
            $PERUNTUKAN_AIR
        );
        // echo "<pre>";
        // var_dump($query);
        // die;

        if ($res = mysqli_query($this->Conn, $query)) {
            $_SESSION['_success'] = 'Profil berhasil disimpan';
        } else {
            $_SESSION['_error'] = 'Profil gagal disimpan';
        }
    }

    public function get_last_profil($npwpd, $nop)
    {
        #query untuk mengambil data relasi user dan data profil
        $query = "SELECT 
				P.*, WP.*,B.CPM_PERUNTUKAN, 
				KEL.CPM_KELURAHAN AS CPM_NAMA_KELURAHAN_OP , 
				KEC.CPM_KECAMATAN AS CPM_NAMA_KECAMATAN_OP
			FROM PATDA_WP WP 
				LEFT JOIN PATDA_AIRBAWAHTANAH_PROFIL P ON P.CPM_NPWPD = WP.CPM_NPWPD AND P.CPM_AKTIF='1' AND P.CPM_NOP='{$nop}'
				LEFT JOIN PATDA_AIRBAWAHTANAH_DOC B ON P.CPM_ID = B.CPM_ID_PROFIL
                LEFT JOIN PATDA_MST_KELURAHAN AS KEL ON P.CPM_KELURAHAN_OP = KEL.CPM_KEL_ID 
				LEFT JOIN PATDA_MST_KECAMATAN AS KEC ON P.CPM_KECAMATAN_OP = KEC.CPM_KEC_ID
			WHERE WP.CPM_NPWPD = '{$npwpd}'";

        $result = mysqli_query($this->Conn, $query);
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

    public function getRekening($kdrek = '')
    {
        return parent::getRekening("4.1.01.12");
    }

    public function get_profil_byid($id)
    {

        $query = "
			SELECT PR.*, 
			KEC.CPM_KECAMATAN AS CPM_NAMA_KECAMATAN_OP, 
			KEL.CPM_KELURAHAN AS CPM_NAMA_KELURAHAN_OP
			FROM PATDA_AIRBAWAHTANAH_PROFIL PR 
			LEFT JOIN PATDA_MST_KECAMATAN KEC ON PR.CPM_KECAMATAN_OP = KEC.CPM_KEC_ID
			LEFT JOIN PATDA_MST_KELURAHAN KEL ON PR.CPM_KELURAHAN_OP = KEL.CPM_KEL_ID
			WHERE PR.CPM_ID = '{$id}' ";

        $result = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($result);

        foreach ($data as $a => $b) {
            if (!is_array($b)) {
                $data[$a] = htmlspecialchars($b);
            }
        }
        return $data;
    }

    public function grid_table($npwpd)
    {
        $DIR = "PATDA-V1";
        $modul = "airbawahtanah/op";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
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
                            defaultSorting: 'CPM_NAMA_OP DESC',
                            selecting: true,
                            actions: {
                                listAction: 'function/{$DIR}/{$modul}/svc-list-data.php?action=list&s={$this->_s}&a={$this->_a}&m={$this->_m}&mod={$this->_mod}&f={$this->_f}&i={$this->_i}',
                                deleteAction: 'function/{$DIR}/{$modul}/svc-op.php?param={$_GET['param']}&function=delete&npwpd={$npwpd}',
                            },
                            fields: {
                                NO : {title: 'No',width: '3%'},
                                CPM_ID: {key: true,list: false}, 
                                CPM_NOP: {title: 'NOP',width: '10%'},
                                CPM_NAMA_OP: {title: 'Nama OP',width: '10%'},
                                CPM_ALAMAT_OP: {title: 'Alamat OP',width: '10%'},
                                CPM_REKENING: {title: 'Jenis Pajak',width: '10%'},
                                longitude : {title: 'Longtitude',width: '10%'},
                                latitude  : {title: 'Latitude',width: '10%'},
                                tapping  : {title: 'Tappingbox',width: '10%'},

                            },
                            recordsLoaded: function (event, data) {
                                for (var i in data.records) {
                                    if (data.records[i].READ == '0') {
                                        $('#laporanPajak-{$this->_i}').find('.jtable tbody tr:eq('+i+') td').css({'background-color':'#a0a0a0','border':'1px #CCC solid'});
                                    }
                                }
                            }
                        });
                        
						$('#laporanPajak-{$this->_i}').jtable('load', {
							CPM_NPWPD : '{$npwpd}',
							param : '{$_GET['param']}'
						});
                        
                    });
                </script>";
        echo $html;
    }

    public function grid_data()
    {
        try {

            $rek = $this->getRekening();

            $where = " CPM_AKTIF = 1 AND ";
            $where .= (isset($_REQUEST['CPM_NPWPD']) && $_REQUEST['CPM_NPWPD'] != "") ? sprintf(" CPM_NPWPD = '%s' ", $_REQUEST['CPM_NPWPD']) : " 1=0 ";

            #count utk pagging
            $query = "SELECT COUNT(*) AS RecordCount FROM {$this->PATDA_AIRBAWAHTANAH_PROFIL}
					WHERE {$where}";
            $result = mysqli_query($this->Conn, $query);
            $row = mysqli_fetch_assoc($result);
            $recordCount = $row['RecordCount'];

            #query select list data
            $query = "SELECT *, 
        IF(tapping = 1, 'Menggunakan Tappingbox', 'Tidak Menggunakan Tappingbox') as tapping FROM {$this->PATDA_AIRBAWAHTANAH_PROFIL} WHERE {$where}
                        ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            $result = mysqli_query($this->Conn, $query);

            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {

                $row = array_merge($row, array("NO" => ++$no));
                $row['READ'] = 0;
                // $row['tapping'] = 0;
                if ($row['tapping'] == 1) {
                    $row['tapping'] == "Menggunakan Tappingbox";
                } else {
                    $row['tapping'] == "Tidak Menggunakan Tappingbox";
                }
                $base64 = base64_decode($_REQUEST['param']) . '&npwpd=' . $row['CPM_NPWPD'] . '&nop=' . $row['CPM_NOP'];
                $url = "main.php?param=" . base64_encode($base64) . '#CPM_KECAMATAN_WP';

                $formatNOP = strlen($row['CPM_NOP']) == 11 ? Pajak::formatNOP($row['CPM_NOP']) : $row['CPM_NOP'];
                $row['CPM_NOP'] = "<a href=\"{$url}\" title=\"Klik untuk detail\">{$formatNOP}</a>";
                $row['CPM_NPWPD'] = Pajak::formatNPWPD($row['CPM_NPWPD']);

                $row['CPM_REKENING'] = isset($rek['CPM_REKENING'][$row['CPM_REKENING']]['nmrek']) ? $rek['CPM_REKENING'][$row['CPM_REKENING']]['nmrek'] : $row['CPM_REKENING'];
                $rows[] = $row;
            }

            $jTableResult = array();
            $jTableResult['q'] = $query;
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


    public function check_nop()
    {

        $query = sprintf(
            "SELECT COUNT(*) AS TOTAL FROM PATDA_AIRBAWAHTANAH_PROFIL 
			WHERE CPM_NPWPD='%s' AND CPM_NOP='%s'",
            $_REQUEST['CPM_NPWPD'],
            $_REQUEST['CPM_NOP']
        );

        $result = mysqli_query($this->Conn, $query);
        $data = mysqli_fetch_assoc($result);

        echo json_encode($data);
    }

    public function get_list_nop($npwpd)
    {
        $where = " CPM_AKTIF = 1 AND ";
        $where .= sprintf(" CPM_NPWPD = '%s' ", $npwpd);
        $query = "SELECT PR.*,KEC.CPM_KECAMATAN,KEL.CPM_KELURAHAN FROM {$this->PATDA_AIRBAWAHTANAH_PROFIL} PR
                    LEFT JOIN PATDA_MST_KECAMATAN KEC ON KEC.CPM_KEC_ID=PR.CPM_KECAMATAN_OP
                    LEFT JOIN PATDA_MST_KELURAHAN KEL ON KEL.CPM_KEL_ID=PR.CPM_KELURAHAN_OP
                    WHERE {$where}";
        $result = mysqli_query($this->Conn, $query);

        $list = array();
        while ($data = mysqli_fetch_assoc($result)) {
            $list[$data['CPM_NOP']] = $data;
        }
        return $list;
    }
}
