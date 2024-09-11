<?php

class KonfigurasiTarget extends Pajak {

	protected $table = 'PATDA_TARGET_PAJAK';

    function __construct() {

        parent::__construct();
        $TARGET = isset($_POST['TARGET']) ? $_POST['TARGET'] : array();
        foreach ($TARGET as $a => $b) {
            $this->$a = mysqli_escape_string($this->Conn, trim($b));
        }
        $this->_a = "aPatda";
        $this->set_jenis_pajak();

        if(isset($this->AKTIF)){
            $this->AKTIF = $this->AKTIF == 'on';
        }
    }

    public function data_table() {
        try {
			$where = " WHERE 1=1 ";
			$where.= (isset($_REQUEST['jenis']) && $_REQUEST['jenis'] != "") ? " AND B.CPM_JENIS like \"{$_REQUEST['jenis']}%\" " : "";
			$where.= (isset($_REQUEST['tahun']) && $_REQUEST['tahun'] != "") ? " AND A.CPM_TAHUN like \"{$_REQUEST['tahun']}%\" " : "";
			
            $query = "SELECT A.*, B.CPM_JENIS FROM {$this->table} A INNER JOIN PATDA_JENIS_PAJAK B 
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

            $query = "SELECT * FROM {$this->table} A INNER JOIN PATDA_JENIS_PAJAK B 
            ON A.CPM_JENIS_PAJAK = B.CPM_NO AND B.CPM_TIPE < 24 {$where}";
// var_dump($query);die;
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

    public function grid_table() {
        $DIR = "PATDA-V1";
        $modul = "konfigurasi/target";
        $html = "<link href=\"inc/{$DIR}/jtable/themes/jtable.min.css\" rel=\"stylesheet\" type=\"text/css\" />
                <script src=\"inc/{$DIR}/jtable/jquery.jtable.min.js\" type=\"text/javascript\"></script>
				<script language=\"javascript\" src=\"inc/js/jquery.number.js\"></script>
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
                                updateAction: 'function/{$DIR}/{$modul}/svc-konfigurasi-target.php?function=update_target',
                            },
                            fields: {
                                NO : {title: 'No',width: '3%',edit :false, sorting:false},
                                CPM_JENIS_PAJAK: {key: true,list: false,edit :false}, 
                                CPM_TAHUN_BERLAKU: {key: true,list: false,edit :false}, 
                                CPM_JENIS: {title:'Jenis Pajak',width: '10%',edit :false}, 
                                CPM_TAHUN: {title: 'Tahun Berlaku',width: '10%',edit :false},
                                CPM_JUMLAH: {
                                	title: 'Jumlah',
                                	width: '10%',
                                	edit :true,
                                	display:function(data) {
                                		return $.number(data.record.CPM_JUMLAH);	
                            		}
                            	},
                                CPM_AKTIF: {
                                	width: '10%',
                                	edit:true,
                            		title: 'Aktif',
				                    type: 'checkbox',
				                    values: { '0': 'Tidak', '1': 'Ya' },
				                    defaultValue: 'true'},
                                customDelete: {
                                    title: '',
                                    width: '0.3%',
                                    edit: false,
                                    display: function(data) {
                                        var \$but = $('<button title=\"delete\" class=\"jtable-command-button jtable-delete-command-button\" ></button>');
                                       \$but.click(function(){
                                           var \$dfd = $.Deferred();
                                           if(data.record.configType == 'global')
                                           {
                                               alert('Global Type Configuration are not allowed for deletion.')
                                               return false;
                                           }
                                           if (confirm('Anda yakin ingin menghapus data ini ?')) {
                                                $.ajax({
                                                    url: 'function/{$DIR}/{$modul}/svc-konfigurasi-target.php?function=delete_target',
                                                    type: 'POST',
                                                    dataType: 'json',
                                                    data: data.record,
                                                    success: function (data) {
                                                        \$dfd.resolve(data);
                                                        $('#konfig-{$this->_i}').jtable('load') ;

                                                    },
                                                    error: function () {
                                                        \$dfd.reject();
                                                    }
                                                });
                                            }
                                        });
                                        return \$but;
                                    },
                                }
                            }
                        });
                        $('#cari-{$this->_i}').click(function (e) {
                            e.preventDefault();
                            $('#konfig-{$this->_i}').jtable('load', {
                                jenis : $('#jenis-{$this->_i}').val(),
                                tahun : $('#tahun-{$this->_i}').val()
                            });
                        });
                        $('#cari-{$this->_i}').click();
                    });
                </script>";
        echo $html;
    }

    public function filtering($id) {
		$opt_jenis_pajak = "<option value=\"\">Semua</option>";
        foreach ($this->arr_pajak as $y) {
			$opt_jenis_pajak .= "<option value='{$y}'>{$y}</option>";
        }

        $thn = date("Y");
		$opt_tahun = "<option value=\"\">Semua</option>";
		for ($t = $thn; $t > ($thn - 9); $t--) {
			$opt_tahun .= "<option value=\"{$t}\">{$t}</option>";
		}

        $html = "<div class=\"filtering\">
                    <form>
                        Jenis Pajak : <select id=\"jenis-{$id}\" name=\"jenis-{$id}\">{$opt_jenis_pajak}</select>
                        Tahun Pajak : <select id=\"tahun-{$id}\" name=\"tahun-{$id}\">{$opt_tahun}</select>
                        <button type=\"submit\" id=\"cari-{$id}\">Cari</button>
                    </form>
                </div> ";
        return $html;
    }

    public function save_target()
    {
    	if($this->is_exists()){
    		echo json_encode(array(
				'type' => 'warning',
				'message' => 'Gagal menyimpan data, target pajak sudah ada.'
			));
			exit;
    	}

    	$jumlah = str_replace(',', '', $this->JUMLAH);

    	$query = "INSERT INTO {$this->table} SET";
		$query .= " CPM_TAHUN = '{$this->TAHUN_BERLAKU}',";
		$query .= " CPM_JENIS_PAJAK = '{$this->JENIS_PAJAK}',";
		$query .= " CPM_AKTIF = '{$this->AKTIF}',";
		$query .= " CPM_JUMLAH = '{$jumlah}'";

		if ($result = mysqli_query($this->Conn, $query)) {
			echo json_encode(array(
				'type' => 'success',
				'message' => 'Target pajak berhasil disimpan.'
			));
			exit;
		}
		echo json_encode(array(
				'type' => 'warning',
				'message' => 'Terjadi kesalahan dalam menyimpan data, silahkan coba beberapa saat lagi.'
			));
		exit;
    }

    public function is_exists()
    {
    	$query = "SELECT * FROM {$this->table} A WHERE 1=1";
		$query .= " AND A.CPM_TAHUN = '{$this->TAHUN_BERLAKU}'";
		$query .= " AND A.CPM_JENIS_PAJAK = '{$this->JENIS_PAJAK}'";

		if ($result = mysqli_query($this->Conn, $query)) {
			return mysqli_num_rows($result);
		}
		die('Error!');
    }

    public function update_target()
    {
    	$CPM_AKTIF = '0';
    	try {
            foreach ($_REQUEST as $a => $b) {
                $$a = mysqli_escape_string($this->Conn, $b);
            }
            $query = sprintf("UPDATE {$this->table} SET 
                    CPM_JUMLAH ='%s',
                    CPM_AKTIF ='%s'
                    WHERE CPM_JENIS_PAJAK = '%s' AND CPM_TAHUN = '%s'", 
                    $CPM_JUMLAH, 
                    $CPM_AKTIF, 
                    $CPM_JENIS_PAJAK,
                    $CPM_TAHUN_BERLAKU);
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

    public function delete_target()
    {
        try {
            foreach ($_REQUEST as $a => $b) {
                $$a = mysqli_escape_string($this->Conn, $b);
            }
            $query = sprintf("DELETE FROM {$this->table} 
                   WHERE CPM_JENIS_PAJAK = '%s' AND CPM_TAHUN = '%s'", 
                    $CPM_JENIS_PAJAK,
                    $CPM_TAHUN_BERLAKU);
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

}