<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
class dbMonitoring
{
	private $dbType = 0;
	private $dbConn = NULL;
	private $result = NULL;
	private $fields = array();
	private $dbQuery = "";
	private $dbQuerySummary = " SELECT SUM(sppt_pbb_harus_dibayar) ";
	private $dbTitles = array();
	private $rowPerpage = 10;
	private $page = 1;
	private $status = 1;
	private $nextPage = 0;
	private $prevPage = 0;
	private $dbTableLog = "";
	private $dbWhere = "";
	private $export = false;

	public $dbHost = "";
	public $dbPort = "";
	public $dbName = "";
	public $dbUser = "";
	public $dbPassword = "";

	public function __construct($host, $port, $user, $password, $dbname)
	{
		$this->dbHost = $host;
		$this->dbPort = $port;
		$this->dbName = $dbname;
		$this->dbUser = $user;
		$this->dbPassword = $password;
	}

	// menentukan koneksi ke database Postgres
	public function setConnectToPostgres()
	{
		$this->dbType = 0;
	}

	// menentukan koneksi ke database Mysql
	public function setConnectToMysql()
	{
		$this->dbType = 0;
	}

	// koneksi Mysql
	public function mysql(&$msg)
	{
		if ($this->dbConn = mysqli_connect($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName, $this->dbPort)) {
			return true;
			//if (mysql_select_db($this->dbName, $this->dbConn)) return true;
		}
		$msg = mysqli_error($DBLink);
		return false;
	}

	// koneksi postgres
	public function postgres(&$msg)
	{
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
	public function setTable($table)
	{
		if ($table) $this->dbTable = " FROM " . $table;
	}

	public function setTableUpdate($table)
	{
		if ($table) $this->dbTable = " " . $table;
	}

	public function setWhere($where)
	{
		if ($where) $this->dbWhere = " WHERE " . $where;
	}
	// set query data
	public function query($query)
	{
		$this->dbQuery = $query;
	}

	public function getBank()
	{
		$query = "SELECT * FROM CDCCORE_BANK";

		if ($this->mysql($errMsg)) {
			$res = mysqli_query($this->dbConn, $query);
			if ($res) {
				while ($row = mysqli_fetch_assoc($res)) {
					$data[] = $row;
				}
				return $data;
			} else {
				mysqli_close($this->dbConn);
				return 0;
			}
		} else {
			mysqli_close($this->dbConn);
			return 0;
		}
	}


	// get count all data
	public function getCountData()
	{
		$query = "Select Count(*) as TOTDATA " . $this->dbTable . $this->dbWhere;
		//		echo $query;
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
				$res = mysqli_query($this->dbConn, $query);
				if ($res) {
					$row = mysql_fetch_row($res);
					return $row[0];
				} else {
					mysqli_close($this->dbConn);
					return 0;
				}
			} else {
				mysqli_close($this->dbConn);
				return 0;
			}
		}
	}

	public function getNominal($nop, $tahun)
	{
		$query = "SELECT PBB_TOTAL_BAYAR AS NOMINAL FROM PBB_SPPT WHERE SPPT_TAHUN_PAJAK = '$tahun' AND NOP = '$nop' ";
		if ($this->mysql($errMsg)) {
			$res = mysqli_query($this->dbConn, $query);
			if ($res) {
				$row = mysql_fetch_row($res);
				return $row[0];
			} else {
				mysqli_close($this->dbConn);
				return 0;
			}
		} else {
			mysqli_close($this->dbConn);
			return 0;
		}
	}

	public function getKdKecUser($uid)
	{
		$query = "SELECT * FROM TBL_REG_USER_PBB A 
					LEFT JOIN cppmod_tax_kecamatan B ON A.kecamatan=B.CPC_TKC_ID 
					LEFT JOIN cppmod_tax_kelurahan C ON A.kelurahan=C.CPC_TKL_ID 
				  WHERE ctr_u_id = '" . $uid . "' ";
		// echo $query;
		if ($this->mysql($errMsg)) {
			$res = mysqli_query($this->dbConn, $query);
			if ($res) {
				$row = mysqli_fetch_assoc($res);
				return $row;
			} else {
				mysqli_close($this->dbConn);
				return 0;
			}
		} else {
			mysqli_close($this->dbConn);
			return 0;
		}
	}

	public function insLogPembayaran($uname, $nop, $tahun, $nominal, $type)
	{
		global  $DBLink;
		$query = "INSERT INTO cppmod_pbb_update_log VALUES(UUID(),'$uname','$nop','$tahun','$nominal','$type',now()) ";
		// echo $query; exit;
		if ($this->mysql($errMsg)) {
			$res = mysqli_query($DBLink, $query);
			if ($res) {
				$row = mysql_fetch_row($res);
				return '1';
			} else {
				mysqli_close($this->dbConn);
				return '0';
			}
		} else {
			mysqli_close($this->dbConn);
			return '0';
		}
	}

	//melakukan update payment_flag
	public function pembayaran($sts, $nop, $tahun, $uname)
	{
		if ($sts == '0') {
			$query = "Update " . $this->dbTable . " set payment_flag = '1', payment_paid = CURRENT_TIMESTAMP(2), PBB_TOTAL_BAYAR = SPPT_PBB_HARUS_DIBAYAR " . $this->dbWhere;
			$statusPembayaran = 1;
		} else {
			$query = "Update " . $this->dbTable . " set payment_flag = '3', payment_paid = '', PBB_TOTAL_BAYAR = '0' " . $this->dbWhere;
			$statusPembayaran = 3;
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
				$res = mysqli_query($this->dbConn, $query);
				if ($res) {
					$row 		= mysql_fetch_row($res);
					$nominal 	= $this->getNominal($nop, $tahun);
					$bOK 		= $this->insLogPembayaran($uname, $nop, $tahun, $nominal, $statusPembayaran);
					if ($bOK) {
						return '1';
					} else
						return '0';
				} else {
					mysqli_close($this->dbConn);
					return '0';
				}
			} else {
				mysqli_close($this->dbConn);
				return '0';
			}
		}
	}
	public function pembayaran2($sts, $nop, $tahun, $uname, $tgl)
	{
		if ($sts == '0') {
			$query = "Update " . $this->dbTable . " set payment_flag = '1', payment_paid = '{$tgl}', PBB_TOTAL_BAYAR = SPPT_PBB_HARUS_DIBAYAR " . $this->dbWhere;
			$statusPembayaran = 1;
		} else {
			$query = "Update " . $this->dbTable . " set payment_flag = '3', payment_paid = '', PBB_TOTAL_BAYAR = '0' " . $this->dbWhere;
			$statusPembayaran = 3;
		}
		//print_r($query);
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
				$res = mysqli_query($this->dbConn, $query);
				if ($res) {
					$row 		= mysql_fetch_row($res);
					$nominal 	= $this->getNominal($nop, $tahun);
					$bOK 		= $this->insLogPembayaran($uname, $nop, $tahun, $nominal, $statusPembayaran);
					if ($bOK) {
						return '1';
					} else
						return '0';
				} else {
					mysqli_close($this->dbConn);
					return '0';
				}
			} else {
				mysqli_close($this->dbConn);
				return '0';
			}
		}
	}
	public function getSumTagihan()
	{
		$query = "Select COALESCE(SUM(PBB_TOTAL_BAYAR),0) as totaltagihan " . $this->dbTable . $this->dbWhere;
		// echo $query;    
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
				$res = mysqli_query($this->dbConn, $query);
				if ($res) {
					$row = mysql_fetch_row($res);
					return $row[0];
				} else {
					mysqli_close($this->dbConn);
					return 0;
				}
			} else {
				mysqli_close($this->dbConn);
				return 0;
			}
		}
	}

	// set row Perpage
	public function setRowPerpage($rp)
	{
		$this->rowPerpage = $rp;
	}

	// set page
	public function setPage($page)
	{
		$this->page = $page;
		$this->prevPage = ($page - 1) > 0 ? ($page - 1) : 1;
		$this->nextPage = $page + 1;
	}

	public function setStatus($sts)
	{
		$this->status = $sts;
	}

	// membuat paging
	private function paging_query()
	{
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


	private function paging()
	{
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

	// query data
	public function query_result($query)
	{
		$result = array();

		if ($this->export) $query = $query . $this->dbTable . $this->dbWhere . " ORDER BY sppt_tahun_pajak DESC ";
		else $query = $query . $this->dbTable . $this->dbWhere . " ORDER BY sppt_tahun_pajak DESC " . $this->paging_query();
		//echo "---------- <br>".$query."<br>";exit;
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
				$this->result = mysqli_query($this->dbConn, $query);
				if ($this->result) {
					$result["result"] = "true";
					$result["data"] = $this->result;
				} else {
					$result["result"] = "false";
					$result["data"] = mysqli_error($this->dbConn);
				}
				mysqli_close($this->dbConn);
				return $result;
			} else {
				$result["result"] = "true";
				$result["data"] = $errMsg;
				mysqli_close($this->dbConn);
				return $result;
			}
		}
	}

	// query data
	public function query_result_summary()
	{
		$result = array();

		if ($this->status == 1)
			$query = "Select COALESCE(SUM(A.SPPT_PBB_HARUS_DIBAYAR),0) as pokok, COALESCE(SUM(A.PBB_DENDA),0) as denda,  COALESCE(SUM(A.SPPT_PBB_HARUS_DIBAYAR+(COALESCE(A.PBB_DENDA, 0))),0) as total " . $this->dbTable . $this->dbWhere;
		else $query = "Select COALESCE(SUM(A.SPPT_PBB_HARUS_DIBAYAR),0) as pokok, COALESCE(SUM(B.PBB_DENDA),0) as denda,  COALESCE(SUM(A.SPPT_PBB_HARUS_DIBAYAR+(COALESCE(B.PBB_DENDA, 0))),0) as total " . $this->dbTable . $this->dbWhere;

		if ($this->dbType == 0) {
			if ($this->mysql($errMsg)) {
				$res = mysqli_query($this->dbConn, $query);
				if ($res) {
					$row = mysql_fetch_row($res);
					return $row;
				} else {
					mysqli_close($this->dbConn);
					return array();
				}
			} else {
				mysqli_close($this->dbConn);
				return array();
			}
		}
	}

	// mengambil jenis-jenis field data dari database postgres
	private function getFieldFromPostgre()
	{
		$i = pg_num_fields($this->result);
		for ($j = 0; $j < $i; $j++) {
			$this->fields[$j]["name"] = pg_field_name($this->result, $j);
		}
	}


	private function getFieldFromMysql()
	{
		$i = mysqli_num_fields($this->result);
		for ($j = 0; $j < $i; $j++) {
			$this->fields[$j]["name"] = mysql_field_name($this->result, $j);
		}
	}
	// menentukan titel header dengan cara memasukan nilai dengan
	// cara sebagai menggunakan JSON;
	public function setTitleHeader($jsonTitle)
	{
		if ($jsonTitle) {
			$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
			$this->dbTitles = $json->decode($jsonTitle);
		}
	}

	// membuat titel header dengan mengambil nilai-nilai field yang ada
	private function createTitleHeader()
	{
		$c = count($this->fields);
		$txtHeader = "";
		for ($i = 0; $i < $c; $i++) {
			$txtHeader .= "<th>" . $this->fields[$i]["name"] . "</th>";
		}
		if ($this->dbTitles) {
			for ($i = 0; $i < count($this->dbTitles->data); $i++) {
				$txtHeader = str_replace(
					"<th>" . $this->dbTitles->data[$i]->field,
					"<th width=\"" . $this->dbTitles->data[$i]->length . "\">" . $this->dbTitles->data[$i]->title,
					$txtHeader
				);
			}
		}
		return $txtHeader;
	}

	// mengambil data dari database postgree
	private function getDataFromPostgres()
	{
		return  pg_fetch_assoc($this->result);
	}

	private function getDataFromMysql()
	{
		return  mysqli_fetch_assoc($this->result);
	}

	// membuat tampilan body tabel
	private function bodyConstruct()
	{
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
					if ($format == "number") $row[$this->fields[$i]["name"]] = number_format($row[$this->fields[$i]["name"]], 0, ',', '.');
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
									$opt[0] = $this->dbTitles->data[$x]->optional[0];
									$opt[1] = $this->dbTitles->data[$x]->optional[1];
								}
							}
						}
					}
					if ($format == "number") $row[$this->fields[$i]["name"]] = number_format($row[$this->fields[$i]["name"]], 0, ',', '.');
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

	// membuat tampilan summary
	private function bodySummary()
	{
		$html = "";
		$total = $this->query_result_summary();
		$c = count($this->fields);
		$summaryCol = 0;
		$html .= "<tr>";
		for ($i = 0; $i < $c; $i++) {

			if ($this->fields[$i]["name"] == 'sppt_pbb_harus_dibayar') {
				$html .= "<td align=\"center\" colspan=\"" . $i . "\"><b>TOTAL KESELURUHAN</b></td>";
				$html .= "<td align=\"right\">&nbsp;<b>" . number_format($total[0], 0, ',', '.') . "</b></td>";
				$summaryCol = $i;
			} else if ($this->fields[$i]["name"] == 'pbb_denda') {
				$html .= "<td align=\"right\">&nbsp;<b>" . number_format($total[1], 0, ',', '.') . "</b></td>";
			} else if ($this->fields[$i]["name"] == 'pbb_total_bayar') {
				$html .= "<td align=\"right\">&nbsp;<b>" . number_format($total[2], 0, ',', '.') . "</b></td>";
			} else if ($summaryCol == 0 && $this->fields[$i]["name"] != 'sppt_pbb_harus_dibayar') {
			} else if ($summaryCol != 0 && $this->fields[$i]["name"] != 'sppt_pbb_harus_dibayar') {
				$html .= "<td align=\"right\">&nbsp;</td>";
			}
		}

		$html .= "</tr>";

		return $html;
	}


	// membuat header
	private function headerConstruct()
	{
		//if ($this->dbType == 1) {
		$this->getFieldFromPostgre();
		//}
		return $this->createTitleHeader();
	}

	// membuat header
	private function headerConstructMysql()
	{
		//if ($this->dbType == 1) {
		$this->getFieldFromMysql();
		//}
		return $this->createTitleHeader();
	}

	// menampilkan html
	public function showHTML()
	{
		$this->export = false;
		$this->query_result($this->dbQuery);
		$html = "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\"><table class=\"table table-bordered table-striped\" width=\"2200px\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">";
		$html .= $this->headerConstructMysql();
		$html .= $this->bodyConstruct();
		$html .= $this->bodySummary();
		$paging = $this->paging();
		$html .= "</table></div><div id=\"paging-tbl\" class=\"paging-tbl-class\">{$paging}</div>";

		echo $html;
	}

	//menampilkan HTML search
	public function showHTMLsearch($uid)
	{
		$this->export = false;
		$this->query_result($this->dbQuery);
		$html = "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\"><table width=\"2200px\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">";
		$html .= "<script type=\"text/javascript\" src=\"inc/PBB/jquery-1.3.2.min.js\"></script>";
		$html .= "<script type=\"text/javascript\" src=\"view/PBB/updatePBB/pembayaran.js\"></script>";
		$html .= $this->headerConstructMysql();
		$html .= $this->bodyConstructSearch($uid);
		$paging = $this->paging();
		$html .= "</table></div><div id=\"paging-tbl\" class=\"paging-tbl-class\">{$paging}</div>";

		echo $html;
	}


	//rangka tabel hasil pencarian
	private function bodyConstructSearch($uid)
	{
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
					if ($format == "number") $row[$this->fields[$i]["name"]] = number_format($row[$this->fields[$i]["name"]], 0, ',', '.');
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
										$opt[1] = "<input type=\"button\" name=\"btn-bayar\" id=\"btn-bayar\" value=\"Batal\" onclick=\"bayar('" . $row['nop'] . "'," . $row['sppt_tahun_pajak'] . ",'1','" . $uid . "')\">";
									else {
										$opt[$row[$this->fields[$x]["name"]]] = "<input type=\"button\" name=\"btn-bayar\" id=\"btn-bayar\" value=\"Bayar\" onclick=\"bayar_tgl('" . $row['nop'] . "'," . $row['sppt_tahun_pajak'] . ",'1','" . $uid . "')\">";
									}
								}
							}
						}
					}
					if ($format == "number") $row[$this->fields[$i]["name"]] = number_format($row[$this->fields[$i]["name"]], 0, ',', '.');
					if ($format == "optional") {
						$row[$this->fields[$i]["name"]] = $opt[$row[$this->fields[$i]["name"]]];
					}
					$html .= "<td align=\"{$align}\">&nbsp;" . $row[$this->fields[$i]["name"]] . "</td>";
				}
				$html .= "</tr>";
			}
?>
			<script type="text/javascript">
				function bayar_tgl(nop, thnpjk, uid) {
					var tgl = document.getElementById('tgl-bayar').value;
					tgl_pembayaran(nop, thnpjk, '0', uid, tgl + ' 00:00:00');
				}
			</script>
<?php
		}

		return $html;
	}

	//export data ke excel
	public function exportToXls()
	{
		$file = date("Ymdhi") . ".xls";
		$this->export = true;
		$this->query_result($this->dbQuery);
		$html = "<table width=\"100%\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\">";

		$html .= $this->headerConstructMysql();
		$html .= $this->bodyConstruct();
		//$html .= $this->headerConstruct();
		//$html .= $this->bodyConstruct();
		$html .= "</table>";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$file");
		echo $html;
	}
}
?>