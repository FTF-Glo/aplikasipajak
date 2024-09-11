<?php

// $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'SIMPATDA-V2-PLK', '', dirname(__FILE__))) . '/';

class dbMonitoring {

    private $dbType = 0;
    private $dbConn = NULL;
    private $result = NULL;
    private $fields = array();
    private $dbQuery = "";
    private $dbTitles = array();
    private $rowPerpage = 10;
    private $page = 1;
    private $status = 1;
    private $nextPage = 0;
    private $prevPage = 0;
    private $dbTable = "";
    private $dbWhere = "";
    private $export = false;
    public $dbHost = "";
    public $dbPort = "";
    public $dbName = "";
    public $dbUser = "";
    public $dbPassword = "";

    public function __construct($host, $port, $user, $password, $dbname) {
        $this->dbHost = $host;
        $this->dbPort = $port;
        $this->dbName = $dbname;
        $this->dbUser = $user;
        $this->dbPassword = $password;
    }

	public function noPaging(){
		$this->export = true;
	}
    // menentukan koneksi ke database Postgres
    public function setConnectToPostgres() {
        $this->dbType = 1;
    }

    // menentukan koneksi ke database Mysql
    public function setConnectToMysql() {
        $this->dbType = 0;
    }

    // koneksi Mysql
    public function mysql(&$msg) {
        if ($this->dbConn = mysql_connect($this->dbHost . ":" . $this->dbPort, $this->dbUser, $this->dbPassword)) {
            if (mysql_select_db($this->dbName, $this->dbConn))
                return true;
        }
        $msg = mysql_error();
        return false;
    }

    // koneksi postgres
    public function postgres(&$msg) {
        $host = $this->dbHost;
        $port = $this->dbPort;
        $dbname = $this->dbName;
        $user = $this->dbUser;
        $pass = $this->dbPassword;

        if ($this->dbConn = pg_connect("host={$host} port={$port} dbname={$dbname} user={$user} password={$pass}")) {
            return true;
        }
        echo "error : host={$host} port={$port} dbname={$dbname} user={$user} password={$pass}" . pg_last_error($this->dbConn);
        $msg = pg_last_error($this->dbConn);
        return false;
        //pg_close($conn);
    }

    // set table
    public function setTable($table) {
        if ($table)
            $this->dbTable = " FROM " . $table;
    }

    public function setTableUpdate($table) {
        if ($table)
            $this->dbTable = " " . $table;
    }

    public function setWhere($where) {
        if ($where)
            $this->dbWhere = " WHERE " . $where;
    }

    // set query data
    public function query($query) {
        $this->dbQuery = $query;
    }

    private function getBulan($bln) {
        $strBln = "";
        switch ($bln) {
            case "01":
                $strBln = "Jan";
                break;
            case "02":
                $strBln = "Feb";
                break;
            case "03":
                $strBln = "Mar";
                break;
            case "04":
                $strBln = "Apr";
                break;
            case "05":
                $strBln = "Mei";
                break;
            case "06":
                $strBln = "Jun";
                break;
            case "07":
                $strBln = "Jul";
                break;
            case "08":
                $strBln = "Agust";
                break;
            case "09":
                $strBln = "Sep";
                break;
            case "10":
                $strBln = "Okt";
                break;
            case "11":
                $strBln = "Nov";
                break;
            case "12":
                $strBln = "Des";
                break;
        }
        return $strBln;
    }

    //get Type
    public function QueryType() {
        $query = $this->dbQuery . $this->dbTable . $this->dbWhere;
        $type = '';
        if ($this->mysql($errMsg)) {
            $res = mysql_query($query);
            if ($res) {
                if ($hasil = mysql_fetch_array($res)) {
                    $type = $hasil['jenis'];
                }
            }
        }

        mysql_close($this->dbConn);
        //header('Content-type: text/xml');
        return $type;
    }

    
    public function Grafik($type, $thn) {
        $query = $this->dbQuery . $this->dbTable . $this->dbWhere;

        if ($type == '') {
            $pajak = "Semua";
        } else {
            $pajak = $type;
        }
        $xAxisName = "Tahun Pajak {$thn}";
        if ($thn == "All") {
            $xAxisName = "Per Tahun";
        }

        $strXML = "<graph caption='Grafik Laporan Pendapatan' subCaption='Jenis {$pajak} pajak {$type}' xAxisName='{$xAxisName}' yAxisName='Pendapatan' decimalPrecision='0' formatNumberScale='0'>";
        if ($this->mysql($errMsg)) {
            $res = mysql_query($query);
            if ($res) {
                while ($hasil = mysql_fetch_array($res)) {
                    if ($thn == 'All') {
                        $strXML .= "<set name='" . $hasil['simpatda_tahun_pajak'] . "' value='" . $hasil['simpatda_dibayar'] . "' />";
                    } else {
                        $strXML .= "<set name='" . $this->getBulan($hasil['simpatda_bulan_pajak']) . "' value='" . $hasil['simpatda_dibayar'] . "' />";
                    }
                }
            }
        }
        $strXML .= "</graph>";
        mysql_close($this->dbConn);
        //header('Content-type: text/xml');
        return $strXML;
    }

    public function KetranganGrafik() {
        $query = $this->dbQuery . $this->dbTable . $this->dbWhere;
        $str = 'kosong';
        if ($this->mysql($errMsg)) {
            $res = mysql_query($query);
            if ($res) {
                if ($hasil = mysql_fetch_array($res)) {
                    if ($hasil['bayar'] != '') {
                        $str = "Keterangan <br/><br/> Pendapatan tertinggi = " . number_format($hasil['bayar'], 2, ",", ".");
                        //. "<br/>Pada Bulan = ".$this->getBulan($hasil['bulan']);  
                    }
                }
            }
        }

        mysql_close($this->dbConn);

        return $str;
    }

    // get count all data
    public function getCountData() {
        $query = "Select Count(*) as TOTDATA " . $this->dbTable . $this->dbWhere;
        #echo $query;
        if ($this->dbType == 1) {
            if ($this->postgres($errMsg)) {
                $res = pg_query($this->dbConn, $query);
                if ($res) {
                    //$row = pg_fetch_assoc($res);
                    $row = pg_fetch_row($res);
                    return $row[0];
                } else {
                    pg_close($this->dbConn);
                    return 0;
                }
            } else {
                pg_close($this->dbConn);
                return 0;
            }
        }
        if ($this->dbType == 0) {
            if ($this->mysql($errMsg)) {
                $res = mysql_query($query, $this->dbConn);
                if ($res) {
                    $row = mysql_fetch_row($res);
                    return $row[0];
                } else {
                    mysql_close($this->dbConn);
                    return 0;
                }
            } else {
                mysql_close($this->dbConn);
                return 0;
            }
        }
    }

    //melakukan update payment_flag
    public function pembayaran($sts) {
        if ($sts == '0') {
            $query = "Update " . $this->dbTable . " set payment_flag = '1', payment_paid = CURRENT_TIMESTAMP(2) " . $this->dbWhere;
        } else {
            $query = "Update " . $this->dbTable . " set payment_flag = '3', payment_paid = NULL " . $this->dbWhere;
        }
        //echo $query;exit;
        if ($this->dbType == 1) {
            if ($this->postgres($errMsg)) {
                $res = pg_query($this->dbConn, $query);
                if ($res) {
                    $row = pg_fetch_row($res);
                    return '1';
                } else {
                    pg_close($this->dbConn);
                    return '0';
                }
            } else {
                pg_close($this->dbConn);
                return '0';
            }
        }

        if ($this->dbType == 0) {
            if ($this->mysql($errMsg)) {
                $res = mysql_query($query, $this->dbConn);
                if ($res) {
                    $row = mysql_fetch_row($res);
                    return '1';
                } else {
                    mysql_close($this->dbConn);
                    return '0';
                }
            } else {
                mysql_close($this->dbConn);
                return '0';
            }
        }
    }

    public function getSumTagihan() {
        $query = "Select COALESCE(SUM(simpatda_dibayar),0) as totaltagihan " . $this->dbTable . $this->dbWhere;
        #echo $query;    
//		return $query;
        if ($this->dbType == 1) {
            if ($this->postgres($errMsg)) {
                $res = pg_query($this->dbConn, $query);
                if ($res) {
                    $row = pg_fetch_row($res);
                    return $row[0];
                } else {
                    pg_close($this->dbConn);
                    return 0;
                }
            } else {
                pg_close($this->dbConn);
                return 0;
            }
        }
        if ($this->dbType == 0) {
            if ($this->mysql($errMsg)) {
                $res = mysql_query($query, $this->dbConn);
                if ($res) {
                    $row = mysql_fetch_row($res);
                    return $row[0];
                } else {
                    mysql_close($this->dbConn);
                    return 0;
                }
            } else {
                mysql_close($this->dbConn);
                return 0;
            }
        }
    }

    // set row Perpage
    public function setRowPerpage($rp) {
        $this->rowPerpage = $rp;
    }

    // set page
    public function setPage($page) {
        $this->page = $page;
        $this->prevPage = ($page - 1) > 0 ? ($page - 1) : 1;
        $this->nextPage = $page + 1;
    }

    public function setStatus($sts) {
        $this->status = $sts;
    }

    // membuat paging
    private function paging_query() {
        $html = "";
        $p = $this->page - 1;
        if ($this->page < 1) {
            $this->page = 0;
        }
        $p_num = $this->rowPerpage * $p;
        //echo $p_num;
        $html = " LIMIT " . $p_num . "," . $this->rowPerpage . "";
        return $html;
    }

    private function paging() {
        $tot = $this->getCountData();

        $nrpage_amount = $tot / $this->rowPerpage;
        $page_amount = ceil($nrpage_amount);
        $page_amount = $page_amount - 1;
        $page = $this->page;
        if ($page < "1") {
            $page = "0";
        }

        $p_num = ($this->rowPerpage * ($page - 1)) + 1;
        $lp = (($this->page) * $this->rowPerpage);

        $page_next = $this->page + 1;
        $page_prev = $this->page - 1;
        $html = "";
        $txtpp = "";
        $txtnp = "";
        $span = "";
        if ($page_amount != "0") {

            if ($page != "0") {
                $prev = $page - 1;
                if ($p_num > 1)
                    $txtpp = "<span id=\"prev-page\" name=\"prev-page\" onclick=\"setPage('" . $page_prev . "','" . $this->status . "')\"></span>";
            }
            if ($lp < $tot) {
                $next = $page + 1;
                //if ($lp < $tot)
                $txtnp = "<span id=\"next-page\" name=\"next-page\" onclick=\"setPage('" . $page_next . "','" . $this->status . "')\"></span>";
            } else {
                $lp = $tot;
            }
            $html .= "<tr><td colspan=\"{$span}\" align=\"right\">{$txtpp}&nbsp;";
            $html .= $p_num . " - " . $lp . " dari " . $tot;
            $html .= "&nbsp;{$txtnp}</td></tr>";
        }

        return $html;
    }

    private $monitorType = 1;

    public function setMonitorType($sts) {
        $this->monitorType = $sts;
    }

    // query data
    public function query_result($query) {
        $result = array();
        if ($this->monitorType == 1) {
            if ($this->export)
                $query = $query . $this->dbTable . $this->dbWhere . " ORDER BY saved_date asc, simpatda_tahun_pajak asc, simpatda_bulan_pajak asc ";
            else {
                $query = $query . $this->dbTable . $this->dbWhere . " ORDER BY saved_date asc, simpatda_tahun_pajak asc, simpatda_bulan_pajak asc " . $this->paging_query();
            }
        } else {
            $query = $query . $this->dbTable . $this->dbWhere . " ORDER BY CPM_NPWPD desc ";
        }
        # echo "---------- <br>".$query."<br>";
        if ($this->dbType == 1) {
            if ($this->postgres($errMsg)) {
                $this->result = pg_query($this->dbConn, $query);
                if ($this->result) {
                    $result["result"] = "true";
                    $result["data"] = $this->result;
                } else {
                    $result["result"] = "false";
                    $result["data"] = pg_last_error($this->dbConn);
                }
                pg_close($this->dbConn);
                return $result;
            } else {
                $result["result"] = "false";
                $result["data"] = $errMsg;
                pg_close($this->dbConn);
                return $result;
            }
        }

        if ($this->dbType == 0) {
            if ($this->mysql($errMsg)) {
                #echo $query;
                $this->result = mysql_query($query, $this->dbConn);
                if ($this->result) {
                    $result["result"] = "true";
                    $result["data"] = $this->result;
                } else {
                    $result["result"] = "false";
                    $result["data"] = mysql_error($this->dbConn);
                }
                mysql_close($this->dbConn);
                return $result;
            } else {
                $result["result"] = "true";
                $result["data"] = $errMsg;
                mysql_close($this->dbConn);
                return $result;
            }
        }
    }

    // mengambil jenis-jenis field data dari database postgres
    private function getFieldFromPostgre() {
        $i = pg_num_fields($this->result);
        for ($j = 0; $j < $i; $j++) {
            $this->fields[$j]["name"] = pg_field_name($this->result, $j);
        }
    }

    private function getFieldFromMysql() {
        $i = mysql_num_fields($this->result);
        for ($j = 0; $j < $i; $j++) {
            $this->fields[$j]["name"] = mysql_field_name($this->result, $j);
        }
    }

    // menentukan titel header dengan cara memasukan nilai dengan
    // cara sebagai menggunakan JSON;
    public function setTitleHeader($jsonTitle) {
        if ($jsonTitle) {
            $json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
            $this->dbTitles = $json->decode($jsonTitle);
        }
    }

    // membuat titel header dengan mengambil nilai-nilai field yang ada
    private function createTitleHeader() {
        $c = count($this->fields);
        $txtHeader = "";
        for ($i = 0; $i < $c; $i++) {
            $txtHeader .= "<th>" . $this->fields[$i]["name"] . "</th>";
        }
        if ($this->dbTitles) {

            for ($i = 0; $i < count($this->dbTitles->data); $i++) {
                $txtHeader = str_replace("<th>" . $this->dbTitles->data[$i]->field, "<th width=\"" . $this->dbTitles->data[$i]->length . "\">" . $this->dbTitles->data[$i]->title, $txtHeader);
            }
            $txtHeader = ($txtHeader != "") ? "<th width='20px;'>No.</th>" . $txtHeader : "";
        }

        return $txtHeader;
    }

    // mengambil data dari database postgree
    private function getDataFromPostgres() {
        return pg_fetch_assoc($this->result);
    }

    private function getDataFromMysql() {
        return mysql_fetch_assoc($this->result);
    }

    // membuat tampilan body tabel
    private function bodyConstruct() {
        $html = "";
        $c = count($this->fields);
        //print_r ($this->getDataFromPostgres());exit;
        if ($this->dbType == 1) {
            while ($row = $this->getDataFromPostgres()) {
                $html .= "<tr>";
                for ($i = 0; $i < $c; $i++) {
                    $width = "";
                    $opt = array();
                    if ($this->dbTitles) {
                        for ($x = 0; $x < count($this->dbTitles->data); $x++) {
                            if ($this->dbTitles->data[$x]->field == $this->fields[$i]["name"]) {
                                $align = @isset($this->dbTitles->data[$x]->align) ? $this->dbTitles->data[$x]->align : "";
                                $format = @isset($this->dbTitles->data[$x]->format) ? $this->dbTitles->data[$x]->format : "";
                                if ($format == "optional") {
                                    $opt[0] = $this->dbTitles->data[$x]->optional[0];
                                    $opt[1] = $this->dbTitles->data[$x]->optional[1];
                                }
                            }
                        }
                    }
                    if ($format == "number")
                        $row[$this->fields[$i]["name"]] = number_format($row[$this->fields[$i]["name"]], 0, ',', '.');
                    if ($format == "optional") {
                        $row[$this->fields[$i]["name"]] = $opt[$row[$this->fields[$i]["name"]]];
                    }
                    $html .= "<td align=\"{$align}\">&nbsp;" . $row[$this->fields[$i]["name"]] . "</td>";
                }
                $html .= "</tr>";
            }
        }
        if ($this->dbType == 0) {

            $no = $this->rowPerpage * ($this->page - 1);
            while ($row = $this->getDataFromMysql()) {
                $html .= "<tr>";
                $html .= "<td align=\"right\">" . ( ++$no) . "</td>";
                for ($i = 0; $i < $c; $i++) {
                    $width = "";
                    $opt = array();
                    $format = "";
                    $align = "left";
                    if ($this->dbTitles) {
                        for ($x = 0; $x < count($this->dbTitles->data); $x++) {
                            if ($this->dbTitles->data[$x]->field == $this->fields[$i]["name"]) {
                                $align = @isset($this->dbTitles->data[$x]->align) ? $this->dbTitles->data[$x]->align : "";
                                $format = @isset($this->dbTitles->data[$x]->format) ? $this->dbTitles->data[$x]->format : "";
                                if ($format == "optional") {
                                    $opt[0] = $this->dbTitles->data[$x]->optional[0];
                                    $opt[1] = $this->dbTitles->data[$x]->optional[1];
                                }
                            }
                        }
                    }
                    if ($format == "number")
                        $row[$this->fields[$i]["name"]] = number_format($row[$this->fields[$i]["name"]], 0, ',', '.');
                    if ($format == "optional")
                        $row[$this->fields[$i]["name"]] = $opt[$row[$this->fields[$i]["name"]]];
                    $html .= "<td align=\"{$align}\">&nbsp;" . $row[$this->fields[$i]["name"]] . "</td>";
                }
                $html .= "</tr>";
            }
        }

        return $html;
    }

    // membuat header
    private function headerConstruct() {
        //if ($this->dbType == 1) {
        $this->getFieldFromPostgre();
        //}
        return $this->createTitleHeader();
    }

    // membuat header
    private function headerConstructMysql() {
        //if ($this->dbType == 1) {
        $this->getFieldFromMysql();
        //}
        return $this->createTitleHeader();
    }

    // menampilkan html
    public function showHTML() {
        $this->export = false;
        $this->query_result($this->dbQuery);
        $html = "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\"><table width=\"1275px\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">";
        $html .= $this->headerConstructMysql();
        $html .= $this->bodyConstruct();
        $paging = $this->paging();
        $html .= "</table></div><div id=\"paging-tbl\" class=\"paging-tbl-class\">{$paging}</div>";

        echo $html;
    }

    //menampilkan HTML search
    public function showHTMLsearch() {
        $this->export = false;
        $this->query_result($this->dbQuery);
        $html = "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\"><table width=\"2200px\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">";
        $html .= "<script type=\"text/javascript\" src=\"inc/PBB/jquery-1.3.2.min.js\"></script>";
        $html .= "<script type=\"text/javascript\" src=\"view/PBB/updatePBB/pembayaran.js\"></script>";
        $html .= $this->headerConstructMysql();
        $html .= $this->bodyConstructSearch();
        $paging = $this->paging();
        $html .= "</table></div><div id=\"paging-tbl\" class=\"paging-tbl-class\">{$paging}</div>";

        echo $html;
    }

    //rangka tabel hasil pencarian
    private function bodyConstructSearch() {
        $html = "";
        $c = count($this->fields);
        if ($this->dbType == 1) {
            while ($row = $this->getDataFromPostgres()) {
                $html .= "<tr>";
                for ($i = 0; $i < $c; $i++) {
                    $width = "";
                    $opt = array();
                    if ($this->dbTitles) {
                        for ($x = 0; $x < count($this->dbTitles->data); $x++) {
                            if ($this->dbTitles->data[$x]->field == $this->fields[$i]["name"]) {
                                $align = @isset($this->dbTitles->data[$x]->align) ? $this->dbTitles->data[$x]->align : "";
                                $format = @isset($this->dbTitles->data[$x]->format) ? $this->dbTitles->data[$x]->format : "";
                                if ($format == "optional") {
                                    if ($row[$this->fields[$x]["name"]] == 1)
                                        $opt[1] = "<input type=\"button\" name=\"btn-bayar\" id=\"btn-bayar\" value=\"Batal\" onclick=\"bayar('" . $row['nop'] . "'," . $row['sppt_tahun_pajak'] . ",'1')\">";
                                    else {
                                        $opt[$row[$this->fields[$x]["name"]]] = "<input type=\"button\" name=\"btn-bayar\" id=\"btn-bayar\" value=\"Bayar\" onclick=\"bayar('" . $row['nop'] . "'," . $row['sppt_tahun_pajak'] . ",'0')\">";
                                    }
                                }
                            }
                        }
                    }
                    if ($format == "number")
                        $row[$this->fields[$i]["name"]] = number_format($row[$this->fields[$i]["name"]], 0, ',', '.');
                    if ($format == "optional") {
                        $row[$this->fields[$i]["name"]] = $opt[$row[$this->fields[$i]["name"]]];
                    }
                    $html .= "<td align=\"{$align}\">&nbsp;" . $row[$this->fields[$i]["name"]] . "</td>";
                }
                $html .= "</tr>";
            }
        }
        if ($this->dbType == 0) {
            while ($row = $this->getDataFromMysql()) {
                $html .= "<tr>";
                for ($i = 0; $i < $c; $i++) {
                    $width = "";
                    $opt = array();
                    if ($this->dbTitles) {
                        for ($x = 0; $x < count($this->dbTitles->data); $x++) {
                            if ($this->dbTitles->data[$x]->field == $this->fields[$i]["name"]) {
                                $align = @isset($this->dbTitles->data[$x]->align) ? $this->dbTitles->data[$x]->align : "";
                                $format = @isset($this->dbTitles->data[$x]->format) ? $this->dbTitles->data[$x]->format : "";
                                if ($format == "optional") {
                                    if ($row[$this->fields[$x]["name"]] == 1)
                                        $opt[1] = "<input type=\"button\" name=\"btn-bayar\" id=\"btn-bayar\" value=\"Batal\" onclick=\"bayar('" . $row['nop'] . "'," . $row['sppt_tahun_pajak'] . ",'1')\">";
                                    else {
                                        $opt[$row[$this->fields[$x]["name"]]] = "<input type=\"button\" name=\"btn-bayar\" id=\"btn-bayar\" value=\"Bayar\" onclick=\"bayar('" . $row['nop'] . "'," . $row['sppt_tahun_pajak'] . ",'0')\">";
                                    }
                                }
                            }
                        }
                    }
                    if ($format == "number")
                        $row[$this->fields[$i]["name"]] = number_format($row[$this->fields[$i]["name"]], 0, ',', '.');
                    if ($format == "optional") {
                        $row[$this->fields[$i]["name"]] = $opt[$row[$this->fields[$i]["name"]]];
                    }
                    $html .= "<td align=\"{$align}\">&nbsp;" . $row[$this->fields[$i]["name"]] . "</td>";
                }
                $html .= "</tr>";
            }
        }

        return $html;
    }

    //export data ke excel
    public function exportToXls() {
        $file = date("Ymdhi") . ".xls";
        $this->export = true;
        $this->query_result($this->dbQuery);
        $html = "<table width=\"100%\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\">";
        $html .= $this->headerConstruct();
        $html .= $this->bodyConstruct();
        $html .= "</table>";
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$file");
        echo $html;
    }

}

?>
