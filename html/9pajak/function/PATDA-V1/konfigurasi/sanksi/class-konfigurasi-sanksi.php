<?php

class KonfigurasiSanksi extends Pajak {

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
			$where = " WHERE 1=1 ";
			$where.= (isset($_REQUEST['jenis']) && $_REQUEST['jenis'] != "") ? " AND B.CPM_JENIS like \"{$_REQUEST['jenis']}%\" " : "";
			
            $query = "SELECT A.*, B.CPM_JENIS FROM PATDA_DENDA_TERLAMBAT_LAPOR A INNER JOIN PATDA_JENIS_PAJAK B 
            ON A.CPM_JENIS_PAJAK = B.CPM_NO AND B.CPM_TIPE < 24 {$where} ORDER BY {$_GET["jtSorting"]} LIMIT {$_GET["jtStartIndex"]},{$_GET["jtPageSize"]}";
            
            $result = mysqli_query($this->Conn, $query);
            $rows = array();
            $no = ($_GET["jtStartIndex"] / $_GET["jtPageSize"]) * $_GET["jtPageSize"];
            while ($row = mysqli_fetch_assoc($result)) {
                $row = array_merge($row, array("NO" => ++$no));
                $row['CPM_TAHUN_BERLAKU'] = $row['CPM_TAHUN'];
                $rows[] = $row;
            }
            $jumlah = mysqli_num_rows($result);
            $jTableResult = array();
            $jTableResult['Result'] = "OK";

            $query = "SELECT * FROM PATDA_DENDA_TERLAMBAT_LAPOR A INNER JOIN PATDA_JENIS_PAJAK B 
            ON A.CPM_JENIS_PAJAK = B.CPM_NO AND B.CPM_TIPE < 24 {$where}";
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
            $query = sprintf("UPDATE PATDA_DENDA_TERLAMBAT_LAPOR SET 
                    CPM_PERSENTASE ='%s'
                    WHERE CPM_JENIS_PAJAK = '%s' AND CPM_TAHUN = '%s'", $CPM_PERSENTASE, $CPM_JENIS_PAJAK, $CPM_TAHUN_BERLAKU);
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
			$opt_jenis_pajak .= "<option value='{$y}'>{$y}</option>";
        }
        $html = "<div class=\"filtering\">
                    <form>
                        Jenis Pajak : <select id=\"jenis-{$id}\" name=\"jenis-{$id}\">{$opt_jenis_pajak}</select>
                        <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                    </form>
                </div> ";
        return $html;
    }
    
    public function grid_table() {
        $DIR = "PATDA-V1";
        $modul = "konfigurasi/sanksi";
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
                            defaultSorting: 'CPM_JENIS ASC',
                            selecting: true,
                            actions: {
                                listAction: 'view/{$DIR}/{$modul}/svc-list-data.php?action=list',
                                updateAction: 'function/{$DIR}/{$modul}/svc-konfigurasi-sanksi.php',
                            },
                            fields: {
                                NO : {title: 'No',width: '3%',edit :false},
                                CPM_JENIS_PAJAK: {key: true,list: false,edit :false}, 
                                CPM_TAHUN_BERLAKU: {key: true,list: false,edit :false}, 
                                CPM_JENIS: {title:'Jenis Pajak',width: '10%',edit :false}, 
                                CPM_PERSENTASE: {title: 'Persentase Sanksi',width: '10%'},
                                CPM_TAHUN: {title: 'Tahun Berlaku',width: '10%',edit :false},
                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#konfig-{$this->_i}').jtable('load', {
                                jenis : $('#jenis-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                    });
                </script>";
        echo $html;
    }

}

?>
