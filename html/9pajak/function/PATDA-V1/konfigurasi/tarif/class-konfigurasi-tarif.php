<?php

class KonfigurasiTarif extends Pajak {
    protected $kdrek;

    function __construct() {

        parent::__construct();
        $PAJAK = isset($_POST['PAJAK']) ? $_POST['PAJAK'] : array();
        foreach ($PAJAK as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }
        $REK = isset($_POST['REK']) ? $_POST['REK'] : array();

        foreach ($REK as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }
        $this->_a = "aPatda";
    }

    public function data_table() {
        try {
			$where = " WHERE kdrek not like '%4.1.1.04%' ";
			$where.= (isset($_REQUEST['jenis']) && $_REQUEST['jenis'] != "") ? " AND nmheader3 like \"{$_REQUEST['jenis']}%\" " : "";
			$where.= (isset($_REQUEST['kdrek']) && $_REQUEST['kdrek'] != "") ? " AND kdrek like \"{$_REQUEST['kdrek']}%\" " : "";            
            
            $query = "SELECT kdrek,nmrek,nmheader3,tarif1,tarif2,kdrek as CPM_ID FROM PATDA_REK_PERMEN13 {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
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

            $query = "SELECT * FROM PATDA_REK_PERMEN13 {$where}";
            $result = mysqli_query($this->Conn, $query);

            $jTableResult['TotalRecordCount'] = mysqli_num_rows($result);
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
    
    public function data_table_reklame() {
        try {
			$where = " WHERE 1=1";
			$where.= (isset($_REQUEST['kdrek']) && $_REQUEST['kdrek'] != "") ? " AND A.kdrek like \"{$_REQUEST['kdrek']}%\" " : "";
			$where.= (isset($_REQUEST['nmrek']) && $_REQUEST['nmrek'] != "") ? " AND B.nmrek like \"%{$_REQUEST['nmrek']}%\" " : "";
            
            $query = "SELECT A.id, A.kdrek, A.type_masa, B.nmrek, A.harga_dasar FROM PATDA_REK_PERMEN_REKLAME AS A 
				INNER JOIN PATDA_REK_PERMEN13 AS B ON A.kdrek=B.kdrek {$where} 
				ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
				
            $result = mysqli_query($this->Conn, $query);
            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            
            $list_type_masa = $this->get_type_masa();
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
                if(isset($list_type_masa[$row['type_masa']])) {
                    $row['type_masa'] = $list_type_masa[$row['type_masa']];
                }
                else{
                    $row['type_masa'] = null;
                }
                $rows[] = $row;
            }
            $jumlah = mysqli_num_rows($result);
            $jTableResult = array();
            $jTableResult['Result'] = "OK";

            $query = "SELECT * FROM PATDA_REK_PERMEN_REKLAME AS A 
				INNER JOIN PATDA_REK_PERMEN13 AS B ON A.kdrek=B.kdrek {$where}";
            $result = mysqli_query($this->Conn, $query);

            $jTableResult['TotalRecordCount'] = mysqli_num_rows($result);
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

    public function update() {
        try {
            foreach ($_REQUEST as $a => $b) {
                $$a = mysqli_escape_string($this->Conn, $b);
            }
            $CPM_TGL_UPDATE = date("d-m-Y");
            $query = sprintf("UPDATE PATDA_REK_PERMEN13 SET 
                    nmrek ='%s',
                    tarif1 ='%s',
                    tarif2 ='%s'
                    WHERE kdrek = '%s'", $nmrek, $tarif1, $tarif2, $CPM_ID);
            $result = mysqli_query($this->Conn, $query);

            $jTableResult = array();
            $jTableResult['Q'] = $query;
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
    
    public function delete() {
        try {
            foreach ($_REQUEST as $a => $b) {
                $$a = mysqli_escape_string($this->Conn, $b);
            }
            $CPM_TGL_UPDATE = date("d-m-Y");
            $query = sprintf("DELETE FROM PATDA_REK_PERMEN13
                    WHERE kdrek = '%s'",$CPM_ID); 
            $result = mysqli_query($this->Conn, $query);

            $jTableResult = array();
            $jTableResult['Q'] = $query;
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

    public function save_tarif() {
        $query = "SELECT * FROM PATDA_REK_PERMEN13 WHERE kdrek = '{$this->kdrek}'";
        $res = mysqli_query($this->Conn, $query);
        if (mysqli_num_rows($res) == 0) {
            
            #insert tarif baru
            $query = sprintf("INSERT INTO PATDA_REK_PERMEN13 
                    (kdrek, nmheader3, nmrek, tarif1, tarif2)
                    VALUES ( '%s','%s','%s','%s','%s')", $this->kdrek, $this->nama,$this->nmrek, $this->tarif1, $this->tarif2
            );
            $res = mysqli_query($this->Conn, $query);
            if ($res == true) {
                $msg = "sukses";
            } else {
                $res = false;
                $msg = "Gagal disimpan, kode rekening sudah terdaftar sebelumnya!";
                $this->Message->setMessage($msg);
                $_SESSION['_error'] = $msg;
            }
        }
        // $res = $this->kdrek;
        return $res;
    }
    
    public function update_reklame() {
        try {
            foreach ($_REQUEST as $a => $b) {
                $$a = mysqli_escape_string($this->Conn, $b);
            }
            $CPM_TGL_UPDATE = date("d-m-Y");
            $query = sprintf("UPDATE PATDA_REK_PERMEN_REKLAME SET 
                    harga_dasar ='%s'
                    WHERE id = '%s'", $harga_dasar, $id);
            $result = mysqli_query($this->Conn, $query);

            $jTableResult = array();
            $jTableResult['Q'] = $query;
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

    public function filtering($id) {
		$opt_jenis_pajak = "<option value=\"\">Semua</option>";
        foreach ($this->arr_pajak as $y) {
			if($y!='Reklame')
				$opt_jenis_pajak .= "<option value='{$y}'>{$y}</option>";
        }
        $html = "<div class=\"filtering\">
                    <form>
                        Jenis Pajak : <select id=\"jenis-{$id}\" name=\"jenis-{$id}\">{$opt_jenis_pajak}</select>
                        Kode Rekening : <input type=\"text\" name=\"kdrek-{$id}\" id=\"kdrek-{$id}\" > 
                        <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                    </form>
                </div> ";
        return $html;
    }
    
	public function filtering_reklame($id) {
		
		$html = "<div class=\"filtering\">
                    <form>
                        Kode Rekening : <input type=\"text\" name=\"kdrek-{$id}\" id=\"kdrek-{$id}\" > 
                        Nama Rekening : <input type=\"text\" name=\"nmrek-{$id}\" id=\"nmrek-{$id}\" > 
                        <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                    </form>
                </div> ";
        return $html;
    }
    
    public function grid_table() {
        $DIR = "PATDA-V1";
        $modul = "konfigurasi/tarif";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                {$this->filtering($this->_i)}
                <div id=\"konfig-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">

                    $(document).ready(function() {
                        $('#konfig-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: {$this->pageSize},
                            sorting: true,
                            defaultSorting: 'nmheader3 ASC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list',
                                updateAction: 'function/{$DIR}/{$modul}/svc-konfigurasi-tarif.php',
                                deleteAction: 'function/{$DIR}/{$modul}/svc-konfigurasi-tarif-del.php',
                            },
                            fields: {
                                NO : {title: 'No',width: '3%',edit :false},
                                CPM_ID: {key: true,list: false,edit :false}, 
                                nmheader3: {title:'Jenis Pajak',width: '10%',edit :false}, 
                                kdrek: {title: 'Kode Rekening',width: '10%',edit :false},
                                nmrek: {title: 'Nama Rekening',width: '10%'},
                                tarif1: {title: 'Tarif Pajak (%)',width: '10%'},
                                tarif2: {title: 'Harga (Rp)',width: '10%'}
                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#konfig-{$this->_i}').jtable('load', {
                                jenis : $('#jenis-{$this->_i}').val(),
                                kdrek : $('#kdrek-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                    });
                </script>";
        echo $html;
    }

    public function grid_table_reklame() {
        $DIR = "PATDA-V1";
        $modul = "konfigurasi/tarif";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
                {$this->filtering_reklame($this->_i)}
                <div id=\"konfig-{$this->_i}\" style=\"width:100%;\"></div>
                <script type=\"text/javascript\">
                    $(document).ready(function() {
                        $('#konfig-{$this->_i}').jtable({
                            title: '',
                            columnResizable : false,
                            columnSelectable : false,
                            paging: true,
                            pageSize: {$this->pageSize},
                            sorting: true,
                            defaultSorting: 'kdrek ASC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data-reklame.php?action=list',
                                updateAction: 'function/{$DIR}/{$modul}/svc-konfigurasi-tarif-reklame.php',
                            },
                            fields: {
                                NO : {title: 'No',width: '3%',edit :false},
                                id: {key: true,list: false,edit :false}, 
                                kdrek: {title: 'Kode Rekening',width: '6%',edit :false},
                                nmrek: {title: 'Nama Rekening',width: '30%',edit :false},
                                type_masa: {title: 'Tipe Masa',width: '10%',edit :false},
                                harga_dasar: {title: 'Harga (Rp)',width: '10%'}
                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#konfig-{$this->_i}').jtable('load', {
                                kdrek : $('#kdrek-{$this->_i}').val(),
                                nmrek : $('#nmrek-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                    });
                </script>";
        echo $html;
    }
}

?>
