<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'pengurangan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/json.php");
echo "<script src=\"inc/js/jquery-1.3.2.min.js\"></script>\n";
echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"inc/js/jquery.formatCurrency-1.4.0.min.js\" type=\"text/javascript\"></script>\n";

echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"function/BPHTB/notaris/func-detail-notaris.css\">";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/bphtb/css/table/table.css\">";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/bphtb/css/button/buttons.css\">";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/bphtb/css/button/buttons-core.css\">";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/datepicker/datepickercontrol.css\">";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script src=\"inc/datepicker/datepickercontrol.js\"></script>\n";


//error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
//ini_set("display_errors", 1); 
$json = new Services_JSON();

function getConfigValue($key)
{
  global $DBLink;
  $id = $_REQUEST['a'];
  $qry = "select * from central_app_config where CTR_AC_AID = 'aBPHTB' and CTR_AC_KEY = '$key'";
  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    echo $qry . "<br>";
    echo mysqli_error($DBLink);
  }
  while ($row = mysqli_fetch_assoc($res)) {
    return $row['CTR_AC_VALUE'];
  }
}

function mysql2json($mysql_result, $name)
{
  $json = "{\n'$name': [\n";
  $field_names = array();
  $fields = mysqli_num_fields($mysql_result);
  for ($x = 0; $x < $fields; $x++) {
    $field_name = mysqli_fetch_field($mysql_result);
    if ($field_name) {
      $field_names[$x] = $field_name->name;
    }
  }
  $rows = mysqli_num_rows($mysql_result);
  for ($x = 0; $x < $rows; $x++) {
    $row = mysqli_fetch_array($mysql_result);
    $json .= "{\n";
    for ($y = 0; $y < count($field_names); $y++) {
      $json .= "'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
      if ($y == count($field_names) - 1) {
        $json .= "\n";
      } else {
        $json .= ",\n";
      }
    }
    if ($x == $rows - 1) {
      $json .= "\n}\n";
    } else {
      $json .= "\n},\n";
    }
  }
  $json .= "]\n}";
  return ($json);
}

function getDataReject($idssb, $idt)
{
  global $DBLink, $json;
  $qry = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_Id = '{$idssb}' AND A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID";
  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    //echo $qry . "<br>";
    echo mysqli_error($DBLink);
  }
  //echo $qry;
  //print_r($data);
  /* while ($row = mysqli_fetch_assoc($res)) {
      return $row['CTR_AC_VALUE'];
      } */
  $d = $json->decode(mysql2json($res, "data"));
  return $d;
}
function getBPHTBPayment($lb, $nb, $lt, $nt, $h, $p, $jh, $NPOPTKP, $phw, $aphbt, $denda)
{
  //$a = $_REQUEST['a'];
  /*$NPOPTKP =  $this->getConfigValue($a,'NPOPTKP_STANDAR');
		
		$typeR = $jh;
		
		if (($typeR==4) || ($typeR==6)){
			$NPOPTKP =  $this->getConfigValue($a,'NPOPTKP_WARIS');
		} else {
			
		}*/

  /*if($this->getNOKTP($noktp,$nop,$tgl)) {	
			$NPOPTKP = 0;
		}*/
  $configAPHB = getConfigValue('CONFIG_APHB');
  $hitungaphb = getConfigValue('HITUNG_APHB');
  $configPengenaan = getConfigValue('CONFIG_PENGENAAN');
  $a = strval($lb) * strval($nb) + strval($lt) * strval($nt);
  $b = strval($h);
  $npop = 0;
  if ($jh == '15') {
    $npop = $b;
  } else {
    if ($b <= $a) $npop = $a;
    else $npop = $b;
  }
  $npkp = $npop - strval($NPOPTKP);
  $jmlByr = ($npop - strval($NPOPTKP)) * 0.05;
  $hbphtb = ($npop - strval($NPOPTKP)) * 0.05;
  $aphb = 0;
  $hbphtb_pengenaan = 0;
  $hbphtb_aphb = 0;
  if (($jh == 4) || ($jh == 5) || ($jh == 31)) {
    if ($configPengenaan == '1') {
      $hbphtb_pengenaan = ($hbphtb * $phw * 0.01);
      $jmlByr = $hbphtb - ($hbphtb_pengenaan);
    } else {
      $hbphtb_pengenaan = 0;
      $jmlByr = $hbphtb;
    }
  } else if ($jh == 7) {
    if ($configAPHB == '1') {
      $p = explode("/", $aphbt);
      $aphb = $p[0] / $p[1];
      $hbphtb_pengenaan = 0;
      if ($hitungaphb == '1') {
        $hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05 * $aphb;
      } else if ($hitungaphb == '2') {
        $hbphtb_aphb = (($npop - strval($NPOPTKP)) * 0.05) - (($npop - strval($NPOPTKP)) * 0.05 * $aphb);
      } else if ($hitungaphb == '3') {
        $hbphtb_aphb = ($npop * $aphb) - (strval($NPOPTKP) * 0.05);
      } else if ($hitungaphb == '0') {
        $hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05;
      }
    } else {
      $hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05;
    }
    $jmlByr = $hbphtb_aphb;
  }
  $total_temp = $jmlByr;
  $tp = strval($p);
  if ($tp != 0) $jmlByr = $jmlByr - ($jmlByr * ($tp * 0.01));

  if ($denda > 0) {
    $jmlByr = $jmlByr + $denda;
  } else {
    $jmlByr = $jmlByr;
  }
  if ($jmlByr < 0) $jmlByr = 0;
  return $jmlByr;
}
function getBPHTBPaymentKB($npopkb, $p, $jh, $NPOPTKP)
{

  // $a = strval($lb) * strval($nb) + strval($lt) * strval($nt);
  // $b = strval($h);
  // $npop = 0;
  // if ($b <= $a)
  // $npop = $a;
  // else
  // $npop = $b;
  $npop = $npopkb;
  $jmlByr = ($npop - strval($NPOPTKP)) * 0.05;
  $tp = strval($p);
  if ($tp != 0)
    $jmlByr = $jmlByr - ($jmlByr * ($tp * 0.01));

  if ($jmlByr < 0)
    $jmlByr = 0;

  return $jmlByr;
}
function getsudahdibayar($idssb)
{
  global $DBLink;
  $qry = "select * from cppmod_ssb_doc where CPM_SSB_ID ='" . $idssb . "'";
  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    echo $qry . "<br>";
    echo mysqli_error($DBLink);
  }
  $before = "";
  while ($rw = mysqli_fetch_assoc($res)) {
    $NPOPTKP = strval($rw['CPM_OP_NPOPTKP']);
    $a = strval($rw['CPM_OP_LUAS_BANGUN']) * strval($rw['CPM_OP_NJOP_BANGUN']) + strval($rw['CPM_OP_LUAS_TANAH']) * strval($rw['CPM_OP_NJOP_TANAH']);
    $b = strval($rw['CPM_OP_HARGA']);
    $npop = 0;
    if ($b <= $a)
      $npop = $a;
    else
      $npop = $b;

    $jmlByr = ($npop - strval($NPOPTKP)) * 0.05;
    $tp = strval($rw['CPM_PAYMENT_TIPE_PENGURANGAN']);
    if ($tp != 0)
      $jmlByr = $jmlByr - ($jmlByr * ($tp * 0.01));

    $ccc = getBPHTBPayment($rw['CPM_OP_LUAS_BANGUN'], $rw['CPM_OP_NJOP_BANGUN'], $rw['CPM_OP_LUAS_TANAH'], $rw['CPM_OP_NJOP_TANAH'], $rw['CPM_OP_HARGA'], $rw['CPM_PAYMENT_TIPE_PENGURANGAN'], $rw['CPM_OP_JENIS_HAK'], $rw['CPM_OP_NPOPTKP'], $rw['CPM_PENGENAAN'], $rw['CPM_APHB'], $rw['CPM_DENDA']);
  }
  $before = $ccc;
  return $before;
}

function inputKodeBayar()
{

  $params = "a=" . $_REQUEST['a'] . "&m=modPenguranganBPHTB";
  $par1 = $params . "&f=f5153-newpengurangan&validasikb=1";
  $html  = "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/bootstrap/css/bootstrap.css\">";
  $html .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/bootstrap/css/bootstrap-theme.css\">";
  $html .= "<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css\">";
  $html .= "<script language=\"javascript\" src=\"inc/bootstrap/js/bootstrap.min.js\" type=\"text/javascript\"></script>\n";
  $html .= "<script language=\"javascript\">
				function checkPaymentCode() { 
					var jml = $(\"#kodebayar\").val();

					$.ajax({
						type: \"post\",
						data: \"kodebayar=\" + jml,
						url: \"./function/BPHTB/pengurangan/svc-cek-kodebayar.php\",
						success: function(res) {
				           /// alert(res);
							if (res==\"Nan\"){
								alert(\"Data tidak ditemukan\");
							}else if(res==0){
								alert(\"Kode Pembayaran tersebut belum melakukan Pembayaran\");
							}else{
                var data = JSON.parse(res);
                var idssid=data[0]['id_switching'];
                var kodebayar=data[0]['kodebayar'];

                console.log();
								// console.log(res);
									 window.location = './main.php?param=" . base64_encode($par1) . "'+encodeBase64(\"&idssb=\"+idssid+\"&kodebayar=\"+kodebayar);
								
							}
							//    alert(res);

							
						}
					});
				}
				</script>";
  $html .= "<div class=\"container-fluid\">
				<div class=\"row\">
					<div class=\"col-lg-0 col-lefted\"></div>
					<div class=\"col-sm-4\">
						<div class=\"panel panel-primary\">
							<div style=\"height: 30px;\" class=\"panel-heading\">
								
								<p align=\"center\">Masukkan Kode Bayar</p>
							</div>
							<div class=\"panel-body\">
								  <div class=\"form-group\">
                                                <label class=\"col-md-4 control-label\">Kode Bayar</label>
                                                <div class=\"col-md-7\">
                                                    <div class=\"input-group\">
                                                        <div class=\"input-icon\">
                                                            <input class=\"form-control\" type=\"text\" id=\"kodebayar\" name=\"kodebayar\" /> </div>
                                                        <span class=\"input-group-btn\">
                                                            <button id=\"kirim\" class=\"btn btn-success\" type=\"button\" onclick=\"checkPaymentCode();\">
                                                                <i class=\"fa fa-send\"></i> Kirim</button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
							</div>
						</div>
					</div>
				</div>
			  </div>";


  return $html;
}

function jenishak($js)
{
  global $DBLink;

  $texthtml = "<select name=\"right-land-build\" id=\"right-land-build\" onchange=\"checkTransLast();hidepasar();cekAPHB();\">";
  $qry = "select * from cppmod_ssb_jenis_hak ORDER BY CPM_KD_JENIS_HAK asc";
  //echo $qry;exit;
  $res = mysqli_query($DBLink, $qry);

  while ($data = mysqli_fetch_assoc($res)) {
    if ($js == $data['CPM_KD_JENIS_HAK']) {
      $selected = "selected";
    } else {
      $selected = "";
    }
    $texthtml .= "<option value=\"" . $data['CPM_KD_JENIS_HAK'] . "\" " . $selected . " >" . str_pad($data['CPM_KD_JENIS_HAK'], 2, "0", STR_PAD_LEFT) . " " . $data['CPM_JENIS_HAK'] . "</option>";
  }
  $texthtml .= "			      </select>";
  return $texthtml;
}

function aphb($aphb)
{
  global $DBLink;

  $texthtml = " Hamparan <select name=\"pengurangan-aphb\" id=\"pengurangan-aphb\" onchange=\"checkTransLast();\">
				    <option value=\"\">Pilih</option>
				    ";
  $qry = "select * from cppmod_ssb_aphb ORDER BY CPM_APHB_KODE asc";
  //echo $qry;exit;
  $res = mysqli_query($DBLink, $qry);
  if (($aphb != $data['CPM_APHB']) || ($aphb == "")) {
    $selected = "";
  } else {
    $selected = "selected";
  }
  while ($data = mysqli_fetch_assoc($res)) {

    $texthtml .= "<option value=\"" . $data['CPM_APHB'] . "\" " . $selected . " >" . str_pad($data['CPM_APHB_KODE'], 2, "0", STR_PAD_LEFT) . ":" . $data['CPM_APHB'] . "</option>";
  }
  $texthtml .= "			      </select>";
  return $texthtml;
}

function getBPHTB_BAYAR($idssb)
{

  $dbName = getConfigValue('BPHTBDBNAME');
  $dbHost = getConfigValue('BPHTBHOSTPORT');
  $dbPwd = getConfigValue('BPHTBPASSWORD');
  $dbTable = getConfigValue('BPHTBTABLE');
  $dbUser = getConfigValue('BPHTBUSERNAME');
  //$dbLimit = getConfigValue('TENGGAT_WAKTU');

  SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
  //payment_flag, mysqli_real_escape_string($payment_flag),
  $query2 = "select bphtb_dibayar from {$dbTable} where id_switching = '" . $idssb . "'";
  //echo $query2;exit;
  $r = mysqli_query($DBLinkLookUp, $query2);
  if ($r === false) {
    $Ok = false;
    setDOCReject($nop);
  }
  if (mysqli_num_rows($r)) {
    $row = mysqli_fetch_assoc($r);
    $bphtb_bayar = $row['bphtb_dibayar'];
  }
  return $bphtb_bayar;
}

function formSSB($val = array())
{
  $hitungAPHB = getConfigValue('HITUNG_APHB');
  $value = array();
  $errMsg = array();
  $j = count($val);
  $err = "";
  for ($i = 0; $i < $j; $i++) {
    if ((substr($val[$i], 0, 5) == 'Error') || ($val[$i] == "")) {
      $errMsg[$i] = $val[$i];
      $value[$i] = "";
    } else {
      $errMsg[$i] = "";
      $value[$i] = $val[$i];
    }
  }
  $APHB = $data->CPM_APHB;
  $pengenaan = getConfigValue('PENGENAAN_HIBAH_WARIS');
  $tAPHB = 0;
  if (($typeR == 33) || ($typeR == 7)) {
    $tAPHB = $data->CPM_APHB;
  }

  $idssb = @isset($_REQUEST["idssb"]) ? $_REQUEST["idssb"] : "";
  $idt = @isset($_REQUEST["idtid"]) ? $_REQUEST["idtid"] : "";
  $kodebayar = @isset($_REQUEST["kodebayar"]) ? $_REQUEST["kodebayar"] : "";
  $validasikb = @isset($_REQUEST["validasikb"]) ? $_REQUEST["validasikb"] : "";
  $data = "";
  $sel1 = "";
  $sel2 = "";
  $sel3 = "";
  $rej = "";
  $txtReject = "";
  $nourutKB = "";
  $nourutKB = getUrutKB();
  $disable_aphb = "$('#pengurangan-aphb').attr(\"disabled\", \"disabled\");";
  // var_dump($kodebayar);
  // die;
  $tanggalkb = date("d/m/Y");
  if (getConfigValue('DENDA') == '1') {
    $c_denda = "$(\"#denda-value\").val(0);
				$(\"#denda-percent\").val(0);
				$(\"#denda-percent\").focus(function() {
					if($(\"#denda-percent\").val()==0){
						$(\"#denda-percent\").val(\"\");
					}
				  
				});
				$(\"#denda-value\").blur(function() {
						if($(\"#denda-value\").val()==0){
						$(\"#denda-value\").val(0);
					}
					  
					});
					
				$(\"#denda-percent\").blur(function() {
						if($(\"#denda-percent\").val()==0){
						$(\"#denda-percent\").val(0);
					}
					  
					});
					";
    $kena_denda = "<tr>
					<td>Denda &nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"denda-percent\" id=\"denda-percent\" onKeyPress=\"return numbersonly(this, event);checkTransaction();\" onkeyup=\"checkTransaction()\" title=\"Denda\" size=\"2\"  /> %</td>
					<td id=\"tdenda\" align=\"right\"><input type=\"text\" name=\"denda-value\" id=\"denda-value\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"checkTransaction()\" title=\"Denda\" readonly=\"readonly\"/></td>
				  </tr>";
    $kena_denda2 = "";
  } else {
    $c_denda = "$(\"#denda-value\").val(0);
					$(\"#denda-percent\").val(0);";
    $kena_denda = "";
    $kena_denda2 = "<input type=\"hidden\" name=\"denda-percent\" id=\"denda-percent\" />
					  <input type=\"hidden\" name=\"denda-value\" id=\"denda-value\"/>";
  }
  $btnSave = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\" class=\"button-success pure-button\" />";
  $btnSaveFinal = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan dan Finalkan\" class=\"button-success pure-button\" />";
  if ($idssb) {
    $dat = getDataReject($idssb, $idt);
    $btnSave = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan sebagai versi baru\" class=\"button-success pure-button\" />";
    $btnSaveFinal = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan versi baru dan finalkan\" class=\"button-success pure-button\" />";
    $data = $dat->data[0];
    $APHB = $data->CPM_APHB;
    $typeR = $data->CPM_OP_JENIS_HAK;

    if ($validasikb != 1) {
      $nourutKB = $data->CPM_NO_KURANG_BAYAR;
      $tanggalkb = $data->CPM_PAYMENT_TIPE_SURAT_TANGGAL;
      $bphtb_before = $data->CPM_KURANG_BAYAR_SEBELUM;
    } else {
      $nourutKB = getUrutKB();
      $tanggalkb = date("d/m/Y");
      $bphtb_before = getBPHTB_BAYAR($idssb);
    }

    // var_dump($data);
    // die;

    $sel = $data->CPM_PAYMENT_TIPE_SURAT;
    if ($typeR == 7 || $typeR == 33) {
      $disable_aphb = "";
    } else {
      $disable_aphb = "$('#pengurangan-aphb').attr(\"disabled\", \"disabled\");";
    }


    $rej = "1";
    if ($sel == '1')
      $sel1 = "selected=\"selected\"";
    if ($sel == '2')
      $sel2 = "selected=\"selected\"";
    if ($sel == '3')
      $sel3 = "selected=\"selected\"";
    //$txtReject = "Alasan penolakan : ".$data->CPM_TRAN_INFO;
    if ($data->CPM_TRAN_STATUS == '4') {
      if ($data->CPM_TRAN_SSB_NEW_VERSION)
        $msgNewVer = "<br><i>Dokumen ini telah dibuat versi barunya yaitu : " . $data->CPM_TRAN_SSB_NEW_VERSION . "</i>";
      $txtReject = "\n<br><br><div id=\"info-reject\" class=\"msg-info\"> <strong>Ditolak karena : </strong><br/>" . str_replace("\n", "<br>", $data->CPM_TRAN_INFO) . $msgNewVer . "</div>\n";
    }
    $nop = $data->CPM_OP_NOMOR ? $data->CPM_OP_NOMOR : getConfigValue('PREFIX');
    //print_r($data);
    $npopkp = number_format(strval($data->CPM_OP_HARGA) - strval($data->CPM_OP_NPOPTKP), 0, '.', ',');
    $harusnya = number_format(((strval($data->CPM_OP_NPOP) - strval($data->CPM_OP_NPOPTKP)) * 0.05), 0, '.', ',');

    if (getConfigValue("1", 'DENDA') == '1') {
      $c_denda = "$(\"#denda-value\").val(0);
				$(\"#denda-percent\").val(0);
				$(\"#denda-percent\").focus(function() {
					if($(\"#denda-percent\").val()==0){
						$(\"#denda-percent\").val(\"\");
					}
				  
				});
				$(\"#denda-value\").blur(function() {
						if($(\"#denda-value\").val()==0){
						$(\"#denda-value\").val(0);
					}
					  
					});
					
				$(\"#denda-percent\").blur(function() {
						if($(\"#denda-percent\").val()==0){
						$(\"#denda-percent\").val(0);
					}
					  
					});
					";
      $kena_denda = "<tr>
					<td>Denda &nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"denda-percent\" id=\"denda-percent\" value=\"" . $data->DENDA . "\" onKeyPress=\"return numbersonly(this, event);checkTransaction();\" onkeyup=\"checkTransaction()\" title=\"Denda\" size=\"2\"  /> %</td>
					<td id=\"tdenda\" align=\"right\"><input type=\"text\" name=\"denda-value\" id=\"denda-value\" value=\"" . $data->CPM_PERSEN_DENDA . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"checkTransaction()\" title=\"Denda\" readonly=\"readonly\"/></td>
				  </tr>";
      $kena_denda2 = "";
    } else {
      $c_denda = "$(\"#denda-value\").val(0);
					$(\"#denda-percent\").val(0);";
      $kena_denda = "";
      $kena_denda2 = "<input type=\"hidden\" name=\"denda-percent\" id=\"denda-percent\" />
					  <input type=\"hidden\" name=\"denda-value\" id=\"denda-value\"/>";
    }
  }
  $configAPHB = getConfigValue('CONFIG_APHB');
  $configPengenaan = getConfigValue('CONFIG_PENGENAAN');

  ($configAPHB == "1") ? $display_aphb = "" : $display_aphb = "style=\"display:none\"";
  ($configPengenaan == "1") ? $display_pengenaan = "" : $display_pengenaan = "style=\"display:none\"";
  $hargatertinggi = strval($data->CPM_OP_LUAS_BANGUN) * strval($data->CPM_OP_NJOP_BANGUN) + strval($data->CPM_OP_LUAS_TANAH) * strval($data->CPM_OP_NJOP_TANAH);
  // echo $hargatertinggi;
  // exit;
  $html = "
	<script src=\"function/BPHTB/pengurangan/func-kurang-bayar.js?ver=0222\"></script>\n
<script language=\"javascript\">
var edit = false;
var hitungaphb = " . $hitungAPHB . ";
var configaphb = " . $configAPHB . ";
var configpengenaan = " . $configPengenaan . ";
$(function(){
        $(\"#name2\").mask(\"" . str_pad(getConfigValue('PREFIX_NOP') . "?", 19, "9", STR_PAD_RIGHT) . "\");
	$(\"#noktp\").focus(function() {
	  $(\"#noktp\").val(\"" . getConfigValue('PREFIX') . "\");
	});
	" . $c_denda . "
	" . $disable_aphb . "
	$(\"#noktp\").keyup(function() {
		var input = $(this),
		text = input.val().replace(/[^./0-9-_\s]/g, \"\");
		if(/_|\s/.test(text)) {
			text = text.replace(/_|\s/g, \"\");
			// logic to notify user of replacement
		}
		input.val(text);
	});
	/*$(\"#certificate-number\").keyup(function() {
		var input = $(this),
		text = input.val().replace(/[^./0-9-_\s]/g, \"\");
		if(/_|\s/.test(text)) {
			text = text.replace(/_|\s/g, \"\");
			// logic to notify user of replacement
		}
		input.val(text);
	});*/
});
function setForm(d){
		$(\"#name\").val(d.CPM_WP_NAMA);
		$(\"#address\").val(d.CPM_WP_ALAMAT);
		$(\"#rt\").val(d.CPM_WP_RT);
		$(\"#rw\").val(d.CPM_WP_RW);
		//$(\"#WP_PROPINSI\").val(PROV);
		$(\"#kabupaten\").val(\"CIANJUR\");
		$(\"#kecamatan\").val(d.CPM_WP_KECAMATAN);
		$(\"#kelurahan\").val(d.CPM_WP_KELURAHAN);
	}

function checkDukcapil(){
		var appID	= '" . $_REQUEST['a'] . "';	
		var noKTP 	= $('#noktp').val();
		$('#pengurangan-aphb').attr(\"disabled\", \"disabled\");
		$('#loaderCek').show();
		$.ajax({
			type: 'POST',
			data: '&noKTP='+noKTP+'&appID='+appID,
			url: './function/BPHTB/pengurangan/svcCheckDukcapil.php',
			success: function(res){  
				d=jQuery.parseJSON(res);
				if(d.res==1){
					$('#loaderCek').hide();
					$(\"<div>\"+d.msg+\"</div>\").dialog({
						modal: true,
						buttons: {
							Ya: function() {
								$(this).dialog( \"close\" );
								setForm(d.dat);
							},
							Tidak: function() {
								$(this).dialog( \"close\" );
							}
						}
					});
				} else if(d.res==0){
					$('#loaderCek').hide();
					$(\"<div>\"+d.msg+\"</div>\").dialog({
						modal: true,
						buttons: {
							OK: function() {
								$(this).dialog( \"close\" );
							}
						}
					});
				}
			}	
		});
	}
</script>
<style scoped>

        .button-success,
        .button-error,
        .button-warning,
        .button-secondary {
            color: white;
            border-radius: 4px;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
        }

        .button-success {
            background: rgb(28, 184, 65); /* this is a green */
        }

        .button-error {
            background: rgb(202, 60, 60); /* this is a maroon */
        }

        .button-warning {
            background: rgb(223, 117, 20); /* this is an orange */
        }

        .button-secondary {
            background: rgb(66, 184, 221); /* this is a light blue */
        }

    </style>
<div id=\"main-content\">
  <form name=\"form-notaris\" id=\"form-notaris\" method=\"post\" action=\"\" onsubmit=\"return checkform();\">
		  <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
			<tr>
			  <td colspan=\"2\" align=\"center\" style=\"border-radius: 10px 10px 0px 0px;\"><strong><font size=\"+2\">Form Surat Setoran Pajak Daerah <br>Bea Perolehan Hak Atas Tanah dan Bangunan <br>(SSPD-BPHTB)</font></strong>" . $txtReject . "
			</tr>
			
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>A. </b></font></div></td>
			  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"2%\"><div align=\"right\">1.</div></td>
				  <td width=\"18%\">NOP PBB</td>
				  <td width=\"30%\"><input type=\"text\" name=\"name2\" id=\"name2\" value=\"" . $nop . "\" onBlur=\"checkNOP(this);autoNOP(this);\" onKeyPress=\"return nextFocus(this, event)\" size=\"25\" title=\"NOP PBB\" \"/></td>
				  <td>Nama WP Lama : </td>
				  <td><input type=\"text\" name=\"nama-wp-lama\" id=\"nama-wp-lama\" value=\"" . $data->CPM_WP_NAMA_LAMA . "\" size=\"35\" maxlength=\"30\" title=\"Nama WP Lama\"/>
				  </td>			  
				  </tr>
				<tr>
				  <td valign=\"top\"><div align=\"right\">2.</div></td>
				  <td valign=\"top\">Lokasi Objek Pajak</td>
				  <td><textarea  name=\"address2\" id=\"address2\" cols=\"35\" rows=\"4\" title=\"Lain-lain\">" . str_replace("<br />", "\n", $data->CPM_OP_LETAK) . "</textarea>
				  <td valign=\"top\">Nama WP Sesuai Sertifikat : </td>
				  <td valign=\"top\"><input type=\"text\" name=\"nama-wp-cert\" id=\"nama-wp-cert\" value=\"" . $data->CPM_WP_NAMA_CERT . "\" size=\"35\" maxlength=\"30\" title=\"Nama WP Sesuai Sertifikat\"/></td>				  
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td>Kelurahan/Desa</td>
				  <td><input type=\"text\" name=\"kelurahan2\" id=\"kelurahan2\" value=\"" . $data->CPM_OP_KELURAHAN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kelurahan Objek Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">4.</div></td>
				  <td>RT/RW</td>
				  <td><input type=\"text\" name=\"rt2\" id=\"rt2\" maxlength=\"3\" size=\"3\" value=\"" . $data->CPM_OP_RT . "\" onKeyPress=\"return nextFocus(this, event)\" title=\"RT Objek Pajak\"/>
					/
					<input type=\"text\" name=\"rw2\" id=\"rw2\" maxlength=\"3\" size=\"3\" value=\"" . $data->CPM_OP_RW . "\" onKeyPress=\"return nextFocus(this, event)\" title=\"RW Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
				  <td>Kecamatan</td>
				  <td><input type=\"text\" name=\"kecamatan2\" id=\"kecamatan2\" value=\"" . $data->CPM_OP_KECAMATAN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kecamatan Objek Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td><input type=\"text\" name=\"kabupaten2\" id=\"kabupaten2\" value=\"" . $data->CPM_OP_KABUPATEN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kabupaten/Kota Objek Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
				  <td>Kode Pos</td>
				  <td><input type=\"text\" name=\"zip-code2\" id=\"zip-code2\" value=\"" . $data->CPM_OP_KODEPOS . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"5\" maxlength=\"5\" title=\"Kode Pos Objek Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
			  </table>
			  
			  </td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>B. </b></font></div></td>
			  <td width=\"97%\"><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"2%\"><div align=\"right\">1.</div></td>
				  <td width=\"18%\">Nama Wajib Pajak</td>
				  <td width=\"40%\"><input type=\"text\" name=\"name\" id=\"name\" value=\"" . $data->CPM_WP_NAMA . "\" onkeypress=\"return nextFocus(this,event)\" size=\"60\" maxlength=\"60\" title=\"Nama Wajib Pajak\"/></td>
				   <td colspan=\"2\"><input type=\"hidden\" id=\"idssb-lama\" name =\"idssb-lama\" value=\"" . $idssb . "\"></td>
				</tr>
				<tr>
				  <td><div align=\"right\">2.</div></td>
				  <td>NPWP</td>
				  <td><input type=\"text\" name=\"npwp\" id=\"npwp\" value=\"" . $data->CPM_WP_NPWP . "\" onkeypress=\"return nextFocus(this,event)\" size=\"15\" maxlength=\"15\" title=\"NPWP\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td>Nomor KTP</td>
				  <td><input type=\"text\" name=\"noktp\" id=\"noktp\" value=\"" . $data->CPM_WP_NOKTP . "\" onkeypress=\"return nextFocus(this,event)\" size=\"16\" maxlength=\"24\" title=\"No KTP wajib di isi\" onblur=\"checkTransLast();checkTransaksi();\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td valign=\"top\"><div align=\"right\">4.</div></td>
				  <td valign=\"top\">Alamat Wajib Pajak</td>
				  <td><textarea  name=\"address\" id=\"address\" cols=\"35\" rows=\"4\" title=\"Alamat\">" . str_replace("<br />", "\n", $data->CPM_WP_ALAMAT) . "</textarea>
				  <td></td>
				  <td></td>
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
				  <td>Kelurahan/Desa</td>
				  <td><input type=\"text\" name=\"kelurahan\" id=\"kelurahan\" value=\"" . $data->CPM_WP_KELURAHAN . "\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\" title=\"Kelurahan/Desa Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>RT/RW</td>
				  <td><input type=\"text\" name=\"rt\" id=\"rt\" maxlength=\"3\" size=\"3\"  value=\"" . $data->CPM_WP_RT . "\" onkeypress=\"return nextFocus(this,event)\" title=\"RT Wajib Pajak\"/>/<input type=\"text\" name=\"rw\" id=\"rw\" maxlength=\"3\" size=\"3\"  value=\"" . $data->CPM_WP_RW . "\" onkeypress=\"return nextFocus(this,event)\" title=\"RW Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
				  <td>Kecamatan</td>
				  <td><input type=\"text\" name=\"kecamatan\" id=\"kecamatan\"  value=\"" . $data->CPM_WP_KECAMATAN . "\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\" title=\"Kecamatan Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">8.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td><input type=\"text\" name=\"kabupaten\" id=\"kabupaten\"  value=\"" . $data->CPM_WP_KABUPATEN . "\" onkeypress=\"return nextFocus(this,event)\"  size=\"20\" maxlength=\"20\" title=\"Kabupaten/Kota Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">9.</div></td>
				  <td>Kode Pos</td>
				  <td><input type=\"text\" name=\"zip-code\" id=\"zip-code\"  value=\"" . $data->CPM_WP_KODEPOS . "\" onKeyPress=\"return numbersonly(this, event)\"  size=\"5\" maxlength=\"5\" title=\"Kode Pos Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
			  </table>
			  <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
			  
				<tr>
				  <td width=\"14\"><div align=\"right\">15.</div></td>
				  <td colspan=\"2\">Jenis perolehan hak atas tanah atau bangunan</td>
			    </tr>
               <tr>
				  <td><div align=\"right\"></div></td>
				  <td colspan=\"2\">" . jenishak($typeR) . "</td>
			    <tr>
				<tr id=\"aphb\" " . $display_aphb . ">
				  <td><div align=\"right\"></div></td>
				  <td colspan=\"2\">" . aphb($APHB) . "</td>
			    <tr>
					
				<tr>
				  <td><div align=\"right\">16.</div></td>
				  <td>Nomor Sertifikat</td>
				  <td><input type=\"text\" name=\"certificate-number\" id=\"certificate-number\" value=\"" . $data->CPM_OP_NMR_SERTIFIKAT . "\" size=\"30\" maxlength=\"30\" title=\"Nomor Sertifikat Tanah\"/></td>
				</tr>
			  </table>
			  <table width=\"900\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\" style=\"border: 1px solid black;border-collapse: collapse;\" class=\"pure-table\"><thead>
		  <tr>
			<td colspan=\"5\"><strong>Penghitungan NJOP PBB:</strong></th>
			</tr>
		  <tr>
			<th width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Objek pajak</th>
			<th width=\"176\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Diisi luas tanah atau bangunan yang haknya diperoleh</th>
			<th width=\"197\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Diisi
    berdasarkan SPPT PBB terakhir sebelum terjadinya peralihan hak 
			  <input type=\"text\" name=\"right-year\" id=\"right-year\" maxlength=\"4\" size=\"4\" value=\"" . $data->CPM_OP_THN_PEROLEH . "\" onKeyPress=\"return numbersonly(this, event)\" title=\"Tahun Pajak\"/></th>
			<th colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP PBB /m²</th>
			</tr>
			</thead>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">7. Luas Tanah (Bumi)</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">9. NJOP Tanah (Bumi) /m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (7x9)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"land-area\" id=\"land-area\" value=\"" . number_format(strval($data->CPM_OP_LUAS_TANAH), 0, '', '')  . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addSN();addET();checkTransaction();\" title=\"Luas Tanah\"/>
			  m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"land-njop\" id=\"land-njop\" value=\"" . $data->CPM_OP_NJOP_TANAH . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addSN();addET();checkTransaction();\" title=\"NJOP Tanah\"/></td>
			<td width=\"28\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">11.</td>
			<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\"  id=\"t1\">" . number_format(strval($data->CPM_OP_LUAS_TANAH) * strval($data->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
		  </tr>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">8. Luas Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">10. NJOP Bangunan / m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (8x10)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"building-area\" id=\"building-area\" value=\"" . number_format(strval($data->CPM_OP_LUAS_BANGUN), 0, '', '') . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" title=\"Luas Bangunan\"/>
		m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"building-njop\" id=\"building-njop\" value=\"" . $data->CPM_OP_NJOP_BANGUN . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" title=\"NJOP Bangunan\"/></td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">12.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t2\">" . number_format(strval($data->CPM_OP_LUAS_BANGUN) * strval($data->CPM_OP_NJOP_BANGUN), 0, '.', ',') . "</td>
		  </tr>
		  <tr>
			<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP PBB </td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">13.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\">" . number_format(strval($data->CPM_OP_LUAS_BANGUN) * strval($data->CPM_OP_NJOP_BANGUN) + strval($data->CPM_OP_LUAS_TANAH) * strval($data->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
		  </tr>
		   <tr>
			<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">Harga Transaksi</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">14.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\">
			<input type=\"text\" name=\"trans-value\" id=\"trans-value\" value=\"" . $data->CPM_OP_HARGA . "\" onKeyPress=\"return numbersonly(this, event)\"    onkeyup=\"checkTransaction()\" title=\"Harga Transaksi\"/></td>
		  </tr>
			  </table>
			  </td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>C. </b></font></div></td>
			  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"443\"><strong>AKUMULASI NILAI PEROLEHAN HAK SEBELUMNYA</strong></td>
				  <td width=\"188\" id=\"akumulasi\">" . number_format(strval($data->CPM_SSB_AKUMULASI), 0, '.', ',') . "</td></td>
				</tr>
			  </table></td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>D. </b></font></div></td>
			  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				  <tr>
					<td width=\"443\"><strong>Penghitungan BPHTB</strong></td>
					<td width=\"188\"><em><strong>Dalam rupiah</strong></em></td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak (NPOP)</td>
					<td id=\"xNJOP\" align=\"right\"><input type=\"text\" name=\"tNPOP\" id=\"tNPOP\"  value=\"" . $data->CPM_OP_HARGA . "\"onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"checkTransaction();\" title=\"Nilai Perolehan Objek Pajak (NPOP)\"/></td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
					<td id=\"xNPOPTKP\" align=\"right\"><input type=\"text\" name=\"tNPOPTKP\" id=\"tNPOPTKP\"  value=\"" . $data->CPM_OP_NPOPTKP . "\" onKeyPress=\"return numbersonly(this, event)\"  \" title=\"Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)\"/></td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
					<td id=\"tNPOPKP\" align=\"right\">" . $npopkp . "</td>
				  </tr>
				  <tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan yang terhutang</td>
					<td id=\"tBPHTBTS\" align=\"right\">" . number_format(($data->CPM_OP_HARGA - strval($data->CPM_OP_NPOPTKP)) * 0.05, 0, '.', ',') . "</td>
				  </tr>
				  <tr " . $display_aphb . ">
					<td>APHB &nbsp;&nbsp;</td>
					<td id=\"tAPHB\" align=\"right\">" . number_format(($data->CPM_OP_HARGA - strval($data->CPM_OP_NPOPTKP)) * 0.05 * $tAPHB, 0, '.', ',') . "</td>
				  </tr>
				  <tr " . $display_pengenaan . ">
					<td>Pengenaan Karena Waris/Hibah Wasiat/Pemberian Hak Pengelola &nbsp;&nbsp;<input style=\"border:none\" type=\"text\" id=\"Phibahwaris\" size=\"1\" name=\"Phibahwaris\" value=\"" . $pengenaan . "\" readonly=\"readonly\"/>%</td>
					<td id=\"tPengenaan\" align=\"right\">" . number_format(($data->CPM_OP_AKUMULASI - strval($data->CPM_OP_NPOPTKP)) * 0.05 * $dat->CPM_PENGENAAN * 0.01, 0, '.', ',') . "</td>
				  </tr>
				  " . $kena_denda . "" . $kena_denda2 . "
				  <tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan yang harusnya dibayar</td>
					<td id=\"harusbayar\" align=\"right\">" . number_format(((strval($data->CPM_BPHTB_BAYAR) - strval($data->CPM_OP_NPOPTKP)) * 0.05), 0, '.', ',') . "</td>
				  </tr>
				  <tr>
					<td>Nilai Pajak Pengurangan yang diberikan dalam Persen</td>
					<td id=\"xBPHTB_BAYAR_PERSEN\" align=\"right\"><input type=\"text\" name=\"tBPHTB_BAYAR_PERSEN\" id=\"tBPHTB_BAYAR_PERSEN\" max=\"100\" value=\"0\" onKeyPress=\"return numbersonly1(this, event);\" onkeyup=\"hitungpersen();\" title=\"BPHTB sebelumnya\" /></td>
				  </tr>
          <tr>
					<td>Nilai Pajak Pengurangan yang diberikan dalam Angka</td>
					<td id=\"xBPHTB_BAYAR\" align=\"right\"><input type=\"text\" name=\"tBPHTB_BAYAR\" id=\"tBPHTB_BAYAR\"  value=\"0\" onKeyPress=\"return numbersonly(this, event);\" onkeyup=\"checkTransaction();\" title=\"BPHTB sebelumnya\" /></td>
				  </tr>
				  <tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan Pengurangan</td>
					<td id=\"xBPHTBT\" align=\"right\"><input type=\"text\" name=\"tBPHTBTU\" id=\"tBPHTBTU\" value=\"" . $data->CPM_OP_BPHTB_TU . "\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"\" title=\"Bea Perolehan atas Hak Tanah dan Bangunan yang terutang\" readonly=\"readonly\"/></td>
				  </tr>
			  </table></td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>E. </b></font></div></td>
			  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td colspan=\"3\"><strong>Jumlah Setoran Berdasarkan : </strong><em>(tekan kotak yang sesuai)</em></td>
				  </tr>
				<tr>
				  <td align=\"center\" valign=\"top\"></td>
				  <td align=\"right\" valign=\"top\"></td>
				  <td valign=\"top\"><select name=\"jsb-choose\" id=\"jsb-choose\" " . $r2 . ">
				 <option " . $sel1 . " >SSPD Pengurangan</option> 
					<option value=\"" . $sel2 . "\" >SKPD Kurang Bayar</option>
					<option value=\"" . $sel3 . "\" >SKPD Kurang Bayar Tambahan</option>
				  </select></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Nomor : 
					<input type=\"text\" name=\"jsb-choose-number\" id=\"jsb-choose-number\" size=\"30\" maxlength=\"30\" value=\"" . $nourutKB . "\" title=\"Nomor Surat Pengurangan\" readonly=\"readonly\"/></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Tanggal : 
					<input type=\"text\" name=\"jsb-choose-date\" id=\"jsb-choose-date\" datepicker=\"true\" datepicker_format=\"DD/MM/YYYY\" readonly=\"readonly\" size=\"10\" maxlength=\"10\" value=\"" . $tanggalkb . "\" title=\"Tanggal Surat Pengurangan\"/></td>
				</tr>
				
			  </table></td>
			</tr>
			<tr>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" id=\"jmlBayar\">Jumlah yang dibayarkan : </td>
			</tr>
			<tr><input type=\"hidden\" id=\"jsb-total-before\" name=\"jsb-total-before\"><input type=\"hidden\" id=\"hd-npoptkp\" name=\"hd-npoptkp\">
			<input type=\"hidden\" id=\"reject-data\" name =\"reject-data\" value=\"" . $rej . "\">
			<input type=\"hidden\" id=\"ver-doc\" value=\"" . $data->CPM_TRAN_SSB_VERSION . "\" name=\"ver-doc\">
			<input type=\"hidden\" id=\"trsid\" value=\"" . $data->CPM_TRAN_ID . "\" name=\"trsid\">
			<input type=\"hidden\"  id=\"znt\" value=\"" . $data->CPM_OP_ZNT . "\" name=\"znt\">
			<input type=\"hidden\"  id=\"bphtbtu\" value=\"" . $data->CPM_OP_BPHTB_TU . "\" name=\"bphtbtu\">
			<td colspan=\"2\" align=\"center\" valign=\"middle\" >" . $btnSave . "
			  &nbsp;&nbsp;&nbsp;
			  " . $btnSaveFinal . "</td>
			</tr>
			<tr>
			  <td colspan=\"2\" align=\"center\" valign=\"middle\" style=\"border-radius: 0px 0px 10px 10px;\">&nbsp;</td>
			</tr>
		  </table>
		</form></div>";
  return $html;
}

function validation($str, &$err)
{
  $OK = true;
  $j = count($str);
  $err = "";
  return $OK;
}

function setDOCReject($nop)
{
  global $data, $DBLink;
  $dbLimit = getConfigValue('TENGGAT_WAKTU');
  $qry = "select B.CPM_TRAN_SSB_ID from cppmod_ssb_doc A, cppmod_ssb_tranmain B where B.CPM_TRAN_SSB_ID = A.CPM_SSB_ID AND A.CPM_OP_NOMOR = '{$nop}' and (B.CPM_TRAN_FLAG <> '5' or B.CPM_TRAN_FLAG <> '4') and DATE_ADD(A.CPM_SSB_CREATED,INTERVAL {$dbLimit} day) <= CURDATE()";

  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    $Ok = false;
  }
  if (mysqli_num_rows($res)) {
    $num_rows = mysqli_num_rows($res);
    while ($row = mysqli_fetch_assoc($res)) {
      //print_r($row["CPM_TRAN_SSB_ID"]);
      $dqry = "DELETE FROM cppmod_ssb_tranmain WHERE CPM_TRAN_SSB_ID='" . $row["CPM_TRAN_SSB_ID"] . "'";
      mysqli_query($DBLink, $dqry);
      $dqry = "DELETE FROM cppmod_ssb_doc WHERE CPM_SSB_ID='" . $row["CPM_TRAN_SSB_ID"] . "'";
      mysqli_query($DBLink, $dqry);
    }
  }
}

function getNOPBPHTB($nop)
{
  global $data, $DBLink;
  $Ok = false;
  $dbName = getConfigValue('BPHTBDBNAME');
  $dbHost = getConfigValue('BPHTBHOSTPORT');
  $dbPwd = getConfigValue('BPHTBPASSWORD');
  $dbTable = getConfigValue('BPHTBTABLE');
  $dbUser = getConfigValue('BPHTBUSERNAME');
  $dbLimit = getConfigValue('TENGGAT_WAKTU');

  SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
  //payment_flag, mysqli_real_escape_string($payment_flag),
  $query2 = "select * from {$dbTable} where op_nomor ='{$nop}' and DATE_ADD(saved_date,INTERVAL {$dbLimit} day) > CURDATE() and payment_flag = 0";
  //echo $query2;exit;
  $r = mysqli_query($DBLinkLookUp, $query2);
  if ($r === false) {
    $Ok = false;
    setDOCReject($nop);
  }
  if (mysqli_num_rows($r)) {
    while ($row = mysqli_fetch_assoc($r)) {
      //print_r("<br> 1r.".mysqli_num_rows ($r).$query2);	
      $Ok = true;
    }
  }

  $qry = "select max(DATE(B.CPM_TRAN_DATE)) AS DT, A.CPM_SSB_ID from cppmod_ssb_doc A, cppmod_ssb_tranmain B where B.CPM_TRAN_SSB_ID = A.CPM_SSB_ID AND A.CPM_OP_NOMOR = '{$nop}' and (B.CPM_TRAN_FLAG <> '5' or B.CPM_TRAN_FLAG <> '4') and DATE_ADD(A.CPM_SSB_CREATED,INTERVAL {$dbLimit} day) > CURDATE() GROUP BY A.CPM_SSB_ID ORDER BY B.CPM_TRAN_DATE DESC LIMIT 1 ";
  //echo $qry;exit;
  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    $Ok = false;
  }

  if (mysqli_num_rows($res)) {
    $num_rows = mysqli_num_rows($res);
    while ($row = mysqli_fetch_assoc($res)) {
      if (!$row["DT"]) {
        $Ok = false;
      } else {
        $Ok = true;
        $dt = $row["CPM_SSB_ID"];
        //jika sudah terbayar maka data dengan NOP yang sama bisa dilakukan transaksi kembali
        $query2 = "select * from {$dbTable} where id_switching = '{$dt}' and payment_flag = 1";
        //echo $query2;exit;
        $rx = mysqli_query($DBLinkLookUp, $query2);
        if (mysqli_num_rows($rx)) {
          $Ok = false;
        }
      }
    }
  }
  return $Ok;
}

function getNOKTP($noktp)
{
  global $DBLink;

  $N1 = getConfigValue('NPOPTKP_STANDAR');
  $N2 = getConfigValue('NPOPTKP_WARIS');
  $day = getConfigValue("BATAS_HARI_NPOPTKP");
  $dbLimit = getConfigValue('TENGGAT_WAKTU');

  $dbName = getConfigValue('BPHTBDBNAME');
  $dbHost = getConfigValue('BPHTBHOSTPORT');
  $dbPwd = getConfigValue('BPHTBPASSWORD');
  $dbTable = getConfigValue('BPHTBTABLE');
  $dbUser = getConfigValue('BPHTBUSERNAME');
  // Connect to lookup database
  SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
  //payment_flag, mysqli_real_escape_string($payment_flag),

  $qry = "select sum(A.CPM_SSB_AKUMULASI) AS mx, A.CPM_OP_NOMOR as OP_NOMOR from cppmod_ssb_doc A LEFT JOIN cppmod_ssb_tranmain AS B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
where A.CPM_WP_NOKTP = '{$noktp}' and (DATE_ADD(DATE(A.CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) 
AND B.CPM_TRAN_STATUS <> 4 AND B.CPM_TRAN_STATUS <> 1 AND B.CPM_TRAN_FLAG <> 1 AND A.CPM_OP_JENIS_HAK <> 6 AND A.CPM_OP_JENIS_HAK <> 4 GROUP BY A.CPM_OP_NOMOR";
  //print_r($qry); 
  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    return false;
  }

  if (mysqli_num_rows($res)) {
    $num_rows = mysqli_num_rows($res);
    while ($row = mysqli_fetch_assoc($res)) {
      if (($row["mx"]) && ($row["mx"] >= $N1)) {
        $op = $row["OP_NOMOR"];
        $query2 = "SELECT  op_nomor,saved_date FROM ssb WHERE op_nomor = '{$op}' and payment_flag = 0 and 
				DATE_ADD(DATE(saved_date), INTERVAL {$dbLimit} DAY) < CURDATE() ";
        //print_r ($query2);
        $r = mysqli_query($DBLinkLookUp, $query2);
        if ($r === false) {
          die("Error Insertxx: " . mysqli_error($DBLinkLookUp));
        }
        if (mysqli_num_rows($r))
          return false;
        //print_r($row["mx"]);
        return true;
      }
    }
  }

  return false;
}
function getnpoptkp($noktp)
{
  global $data, $DBLink;
  $boleh_input = true;

  $dbName = getConfigValue('BPHTBDBNAME');
  $dbHost = getConfigValue('BPHTBHOSTPORT');
  $dbPwd = getConfigValue('BPHTBPASSWORD');
  $dbTable = getConfigValue('BPHTBTABLE');
  $dbUser = getConfigValue('BPHTBUSERNAME');
  $dbLimit = getConfigValue('TENGGAT_WAKTU');


  SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);

  $cari = "select count(*) AS jumlah from {$dbTable} WHERE wp_noktp='{$noktp}'";
  $query = mysqli_query($DBLinkLookUp, $cari);
  if ($query === false) {
    echo $qry . "<br>";
    echo mysqli_error($DBLinkLookUp);
  }
  while ($row = mysqli_fetch_assoc($query)) {
    return $row['jumlah'];
  }
}

function save($final, $newVer = false)
{

  global $data, $DBLink;
  $dat = $data;
  //print_r($appDbLink);

  $data = array();
  //$data[0] = $_REQUEST['tax-services-office']? $_REQUEST['tax-services-office'] :"Error: Nama Kantor Pelayanan Pajak Pratama tidak boleh dokosongkan !";
  //$data[1] = $_REQUEST['tax-services-office-code']? $_REQUEST['tax-services-office-code'] :"Error: Kode Kantor Pelayanan Pajak Pratama tidak boleh dikosongkan !";
  $data[0] = "-";
  $data[1] = "-";
  $data[2] = @isset($_REQUEST['name']) ? $_REQUEST['name'] : "Error: Nama Wajib Pajak tidak boleh dikosongkan!";
  $data[3] = @isset($_REQUEST['npwp']) ? $_REQUEST['npwp'] : "Error: NPWP tidak boleh dikosongkan!";
  $data[4] = @isset($_REQUEST['address']) ? $_REQUEST['address'] : "Error: Alamat tidak boleh dikosongkan!";
  $data[5] = "-";
  $data[6] = @isset($_REQUEST['kelurahan']) ? $_REQUEST['kelurahan'] : "Error: Kelurahan tidak boleh dikosongkan!";
  $data[7] = @isset($_REQUEST['rt']) ? $_REQUEST['rt'] : "Error: RT tidak boleh dikosongkan!";
  $data[8] = @isset($_REQUEST['rw']) ? $_REQUEST['rw'] : "Error: RW tidak boleh dikosongkan!";
  $data[9] = @isset($_REQUEST['kecamatan']) ? $_REQUEST['kecamatan'] : "Error: Kecamatan tidak boleh dikosongkan!";
  $data[10] = @isset($_REQUEST['kabupaten']) ? $_REQUEST['kabupaten'] : "Error: Kabupaten tidak boleh dikosongkan!";
  $data[11] = @isset($_REQUEST['zip-code']) ? $_REQUEST['zip-code'] : "Error: Kode POS tidak boleh dikosongkan!";
  $data[12] = @isset($_REQUEST['name2']) ? $_REQUEST['name2'] : "Error: NOP PBB tidak boleh dikosongkan!";
  $data[13] = @isset($_REQUEST['address2']) ? $_REQUEST['address2'] : "Error: Alamat Objek Pajak tidak boleh dikosongkan!";
  $data[14] = "-";
  $data[15] = @isset($_REQUEST['kelurahan2']) ? $_REQUEST['kelurahan2'] : "Error: Kelurahan Objek Pajak tidak boleh dikosongkan!";
  $data[16] = @isset($_REQUEST['rt2']) ? $_REQUEST['rt2'] : "Error: RT Objek Pajak tidak boleh dikosongkan!";
  $data[17] = @isset($_REQUEST['rw2']) ? $_REQUEST['rw2'] : "Error: RW Objek Pajak tidak boleh dikosongkan!";
  $data[18] = @isset($_REQUEST['kecamatan2']) ? $_REQUEST['kecamatan2'] : "Error: Kecamatan Objek Pajak tidak boleh dikosongkan!";
  $data[19] = @isset($_REQUEST['kabupaten2']) ? $_REQUEST['kabupaten2'] : "Error: Kabupaten Objek Pajak tidak boleh dikosongkan!";
  $data[20] = @isset($_REQUEST['zip-code2']) ? $_REQUEST['zip-code2'] : "Error: Kode POS Objek Pajak tidak boleh dikosongkan!";
  $data[21] = @isset($_REQUEST['right-year']) ? $_REQUEST['right-year'] : "Error: Tahun SPPT PBB tidak boleh dikosongkan!";
  $data[22] = @isset($_REQUEST['land-area']) ? $_REQUEST['land-area'] : "Error: Luas Tanah tidak boleh dikosongkan!";
  $data[23] = @isset($_REQUEST['land-njop']) ? $_REQUEST['land-njop'] : "Error: NJOP Tanah tidak boleh dikosongkan!";
  $data[24] = @isset($_REQUEST['building-area']) ? $_REQUEST['building-area'] : "Error: Luas Bangunan tidak boleh dikosongkan!";
  $data[25] = @isset($_REQUEST['building-njop']) ? $_REQUEST['building-njop'] : "Error: NJOP Bangunan tidak boleh dikosongkan!";
  $data[26] = @isset($_REQUEST['right-land-build']) ? $_REQUEST['right-land-build'] : "";
  $data[27] = @isset($_REQUEST['trans-value']) ? $_REQUEST['trans-value'] : "Error: Harga transasksi tidak boleh dikosongkan!";
  $data[28] = @isset($_REQUEST['certificate-number']) ? $_REQUEST['certificate-number'] : "Error: Nomor sertifikat tidak boleh dikosongkan!";
  $data[29] = @isset($_REQUEST['tNPOPTKP']) ? $_REQUEST['tNPOPTKP'] : "NPOPTKP";
  $data[30] = @isset($_REQUEST['RadioGroup1']) ? $_REQUEST['RadioGroup1'] : "Error: Pilihan Jumlah Setoran tidak dipilih!";
  $data[31] = @isset($_REQUEST['jsb-choose']) ? $_REQUEST['jsb-choose'] : "Error: Pilihan jenis tidak dipilih!";
  $data[32] = @isset($_REQUEST['jsb-choose-number']) ? $_REQUEST['jsb-choose-number'] : "Error: Nomor surat tidak boleh dikosongkan!";
  $data[33] = @isset($_REQUEST['jsb-choose-date']) ? $_REQUEST['jsb-choose-date'] : "Error: Tanggal surat tidak boleh dikosongkan!";
  $data[34] = "-"; //$_REQUEST['pdsk-choose']? $_REQUEST['pdsk-choose']:"Error: Pengurangan tidak dipilih!";
  $data[35] = @isset($_REQUEST['jsb-etc']) ? $_REQUEST['jsb-etc'] : "Error: Keterangan lain-lain tidak boleh dikosongkan!";
  $data[36] = @isset($_REQUEST['jsb-total-before']) ? $_REQUEST['jsb-total-before'] : "Error: Akumulasi nilai perolehan hak sebelumnya tidak boleh di kosongkan!";
  $data[37] = @isset($_REQUEST['jsb-choose-role-number']) ? $_REQUEST['jsb-choose-role-number'] : "Error: No Aturan KDH tidak boleh di kosongkan!";
  $data[38] = @isset($_REQUEST['noktp']) ? $_REQUEST['noktp'] : "Error: Nomor KTP tidak boleh dikosongkan!";
  $data[39] = ""; //@isset($_REQUEST['jsb-choose-percent'])? $_REQUEST['jsb-choose-percent']:"Error: persentase tidak boleh dikosongkan!";
  $data[40] = @isset($_REQUEST['tNPOP']) ? $_REQUEST['tNPOP'] : "Error: Nilai Perolehan Objek Pajak!";
  $data[41] = @isset($_REQUEST['tBPHTBTU']) ? $_REQUEST['tBPHTBTU'] : "Error: BPHTB Terhutang!";

  $data[42] = @isset($_REQUEST['nama-wp-lama']) ? $_REQUEST['nama-wp-lama'] : "Error: Nama WP Lama tidak boleh dikosongkan!";
  $data[43] = @isset($_REQUEST['nama-wp-cert']) ? $_REQUEST['nama-wp-cert'] : "Error: Nama WP Sesuai Sertifikat tidak boleh dikosongkan!";
  $data[46] = @isset($_REQUEST['pengurangan-aphb']) ? $_REQUEST['pengurangan-aphb'] : "1";
  $idssb_lama = @isset($_REQUEST['idssb-lama']) ? $_REQUEST['idssb-lama'] : "";
  if (($data[29] == "0") || ($data[29] == 0)) {
    if ($_REQUEST['right-land-build'] == 5) {
      $data[29] = getConfigValue('NPOPTKP_WARIS');
    } else if (($_REQUEST['right-land-build'] == 30) || ($_REQUEST['right-land-build'] == 31) || ($_REQUEST['right-land-build'] == 32) || ($_REQUEST['right-land-build'] == 33)) {
      $data[29] = 0;
    } else {
      $data[29] = getConfigValue('NPOPTKP_STANDAR');
    }
  }
  if (($_REQUEST['right-land-build'] == 33) || ($_REQUEST['right-land-build'] == 7)) {
    $pAPHB = $data[46];
  } else {
    $pAPHB = "";
  }
  $pengenaan = 0;
  if (($_REQUEST['right-land-build'] == 5) || ($_REQUEST['right-land-build'] == 3)) {
    $pengenaan = getConfigValue('PENGENAAN_HIBAH_WARIS');
  }
  // $query = sprintf("UPDATE cppmod_ssb_tranmain SET CPM_TRAN_SSB_NEW_VERSION='%s'
  // WHERE CPM_TRAN_ID='%s' ", $version, $_REQUEST['trsid']);
  // var_dump($idssb_lama);
  // die;
  $typeSurat = '';
  $typeSuratNomor = '';
  $typeSuratTanggal = '';
  $typePengurangan = '';
  $typeLainnya = '';
  $trdate = date("Y-m-d H:i:s");
  $opr = $dat->uname;
  $version = "1.0";
  if ($newVer) {
    // echo "sini";
    // die;
    $version = (1 + $_REQUEST['ver-doc']) . ".0";
  }
  // echo "bukan";
  // die;
  $nokhd = "";
  $typeSurat = $data[31];
  $typeSuratNomor = $data[32];
  $typeSuratTanggal = $data[33];

  $bphtb_terutang = @isset($_REQUEST['bphtbtu']) ? $_REQUEST['bphtbtu'] : 0;
  $znt = @isset($_REQUEST['znt']) ? $_REQUEST['znt'] : 0;
  // $bphtb_sebelum = @isset($_REQUEST['tBPHTB_BAYAR']) ? $_REQUEST['tBPHTB_BAYAR'] : 0;
  $bphtb_potongan = @isset($_REQUEST['tBPHTB_BAYAR']) ? $_REQUEST['tBPHTB_BAYAR'] : 0;
  $bphtb_bayar = @isset($_REQUEST['tBPHTBTU']) ? $_REQUEST['tBPHTBTU'] : 0;
  $bphtb_persen = @isset($_REQUEST['tBPHTB_BAYAR_PERSEN']) ? $_REQUEST['tBPHTB_BAYAR_PERSEN'] : 0;

  $nourutKB = getUrutKB();
  if (validation($data, $err)) {

    $iddoc = c_uuid();
    $refnum = c_uuid();
    $tranid = c_uuid();
    $rejcs = @isset($_REQUEST['idssb']) ? $_REQUEST['idssb'] : "";

    // var_dump($rejcs . ' a pa ');
    // die;
    // please note %d in the format string, using %s would be meaningless
    // $query = "INSERT INTO cppmod_ssb_doc_pengurangan (
    // CPM_SSB_ID,CPM_KPP,
    // CPM_KPP_ID,CPM_WP_NAMA,CPM_WP_NPWP,CPM_WP_ALAMAT,CPM_WP_RT,CPM_WP_RW,CPM_WP_KELURAHAN,CPM_WP_KECAMATAN,CPM_WP_KABUPATEN,CPM_WP_KODEPOS,
    // CPM_OP_NOMOR,CPM_OP_LETAK,CPM_OP_RT,CPM_OP_RW,CPM_OP_KELURAHAN,CPM_OP_KECAMATAN,CPM_OP_KABUPATEN,CPM_OP_KODEPOS,CPM_OP_THN_PEROLEH,CPM_OP_LUAS_TANAH,
    // CPM_OP_LUAS_BANGUN,CPM_OP_NJOP_TANAH,CPM_OP_NJOP_BANGUN,CPM_OP_JENIS_HAK,CPM_OP_HARGA,CPM_OP_NMR_SERTIFIKAT,CPM_OP_NPOPTKP,CPM_PAYMENT_TIPE,
    // CPM_PAYMENT_TIPE_SURAT,CPM_PAYMENT_TIPE_SURAT_NOMOR,CPM_PAYMENT_TIPE_SURAT_TANGGAL,CPM_PAYMENT_TIPE_PENGURANGAN,CPM_PAYMENT_TIPE_OTHER,CPM_SSB_CREATED,
    // CPM_SSB_AUTHOR,CPM_SSB_VERSION,CPM_SSB_AKUMULASI,CPM_PAYMENT_TIPE_KHD_NOMOR,CPM_WP_NOKTP,CPM_OP_NPOP,CPM_KURANG_BAYAR,CPM_WP_NAMA_LAMA,CPM_WP_NAMA_CERT,CPM_OP_BPHTB_TU,CPM_NO_KURANG_BAYAR,CPM_IDSSB_KURANG_BAYAR, CPM_APHB, CPM_PENGENAAN, CPM_OP_ZNT,CPM_KURANG_BAYAR_SEBELUM,CPM_BPHTB_BAYAR) 
    // VALUES  ('" . mysqli_real_escape_string($DBLink, $iddoc) . "','','','" . mysqli_real_escape_string($DBLink, $data[2]) . "','" . mysqli_real_escape_string($DBLink, $data[3]) . "','" . mysqli_real_escape_string($DBLink, nl2br($data[4])) . "','" . mysqli_real_escape_string($DBLink, $data[7]) . "','" . mysqli_real_escape_string($DBLink, $data[8]) . "','" . mysqli_real_escape_string($DBLink, $data[6]) . "','" . mysqli_real_escape_string($DBLink, $data[9]) . "','" . mysqli_real_escape_string($DBLink, $data[10]) . "','" . mysqli_real_escape_string($DBLink, $data[11]) . "','" . mysqli_real_escape_string($DBLink, $data[12]) . "','" . mysqli_real_escape_string($DBLink, nl2br($data[13])) . "','" . mysqli_real_escape_string($DBLink, $data[16]) . "','" . mysqli_real_escape_string($DBLink, $data[17]) . "','" . mysqli_real_escape_string($DBLink, $data[15]) . "','" . mysqli_real_escape_string($DBLink, $data[18]) . "',
    // '" . mysqli_real_escape_string($DBLink, $data[19]) . "','" . mysqli_real_escape_string($DBLink, $data[20]) . "','" . mysqli_real_escape_string($DBLink, $data[21]) . "','" . mysqli_real_escape_string($DBLink, $data[22]) . "','" . mysqli_real_escape_string($DBLink, $data[24]) . "','" . mysqli_real_escape_string($DBLink, $data[23]) . "','" . mysqli_real_escape_string($DBLink, $data[25]) . "','" . mysqli_real_escape_string($DBLink, $data[26]) . "','" . mysqli_real_escape_string($DBLink, $data[27]) . "','" . mysqli_real_escape_string($DBLink, $data[28]) . "','" . mysqli_real_escape_string($DBLink, $data[29]) . "','2','" . mysqli_real_escape_string($DBLink, $typeSurat) . "','" . mysqli_real_escape_string($DBLink, $typeSuratNomor) . "','" . mysqli_real_escape_string($DBLink, $typeSuratTanggal) . "','" . mysqli_real_escape_string($DBLink, $data[39]) . "','" . mysqli_real_escape_string($DBLink, $typeLainnya) . "','" . mysqli_real_escape_string($DBLink, $trdate) . "','" . mysqli_real_escape_string($DBLink, $opr) . "','" . mysqli_real_escape_string($DBLink, $version) . "','" . mysqli_real_escape_string($DBLink, $data[36]) . "','" . mysqli_real_escape_string($DBLink, $nokhd) . "','" . mysqli_real_escape_string($DBLink, $data[38]) . "','" . mysqli_real_escape_string($DBLink, $data[40]) . "','" . mysqli_real_escape_string($DBLink, $data[41]) . "','" . mysqli_real_escape_string($DBLink, $data[42]) . "','" . mysqli_real_escape_string($DBLink, $data[43]) . "','" . mysqli_real_escape_string($DBLink, $bphtb_terutang) . "','" . $nourutKB . "','" . $idssb_lama . "','" . $pAPHB . "','" . $pengenaan . "','" . $znt . "','" . $bphtb_sebelum . "','" . $data[41] . "')";

    $query = "INSERT INTO cppmod_ssb_doc_pengurangan (
      CPM_SSB_ID,CPM_KPP,
      CPM_KPP_ID,CPM_WP_NAMA,CPM_WP_NPWP,CPM_WP_ALAMAT,CPM_WP_RT,CPM_WP_RW,CPM_WP_KELURAHAN,CPM_WP_KECAMATAN,CPM_WP_KABUPATEN,CPM_WP_KODEPOS,
      CPM_OP_NOMOR,CPM_OP_LETAK,CPM_OP_RT,CPM_OP_RW,CPM_OP_KELURAHAN,CPM_OP_KECAMATAN,CPM_OP_KABUPATEN,CPM_OP_KODEPOS,CPM_OP_THN_PEROLEH,CPM_OP_LUAS_TANAH,
      CPM_OP_LUAS_BANGUN,CPM_OP_NJOP_TANAH,CPM_OP_NJOP_BANGUN,CPM_OP_JENIS_HAK,CPM_OP_HARGA,CPM_OP_NMR_SERTIFIKAT,CPM_OP_NPOPTKP,CPM_PAYMENT_TIPE,
      CPM_PAYMENT_TIPE_SURAT,CPM_PAYMENT_TIPE_SURAT_NOMOR,CPM_PAYMENT_TIPE_SURAT_TANGGAL,CPM_PAYMENT_TIPE_PENGURANGAN,CPM_PAYMENT_TIPE_OTHER,CPM_SSB_CREATED,
      CPM_SSB_AUTHOR,CPM_SSB_VERSION,CPM_SSB_AKUMULASI,CPM_PAYMENT_TIPE_KHD_NOMOR,CPM_WP_NOKTP,CPM_OP_NPOP,CPM_KURANG_BAYAR,CPM_WP_NAMA_LAMA,CPM_WP_NAMA_CERT,CPM_OP_BPHTB_TU,CPM_NO_KURANG_BAYAR,CPM_IDSSB_KURANG_BAYAR, CPM_APHB, CPM_PENGENAAN, CPM_OP_ZNT,CPM_KURANG_BAYAR_SEBELUM,CPM_BPHTB_BAYAR
      ,sebelumpengurangan,nilaipengurangan,nilaipersen)
      SELECT CPM_SSB_ID,CPM_KPP,
      CPM_KPP_ID,CPM_WP_NAMA,CPM_WP_NPWP,CPM_WP_ALAMAT,CPM_WP_RT,CPM_WP_RW,CPM_WP_KELURAHAN,CPM_WP_KECAMATAN,CPM_WP_KABUPATEN,CPM_WP_KODEPOS,
      CPM_OP_NOMOR,CPM_OP_LETAK,CPM_OP_RT,CPM_OP_RW,CPM_OP_KELURAHAN,CPM_OP_KECAMATAN,CPM_OP_KABUPATEN,CPM_OP_KODEPOS,CPM_OP_THN_PEROLEH,CPM_OP_LUAS_TANAH,
      CPM_OP_LUAS_BANGUN,CPM_OP_NJOP_TANAH,CPM_OP_NJOP_BANGUN,CPM_OP_JENIS_HAK,CPM_OP_HARGA,CPM_OP_NMR_SERTIFIKAT,CPM_OP_NPOPTKP,CPM_PAYMENT_TIPE,
      CPM_PAYMENT_TIPE_SURAT,CPM_PAYMENT_TIPE_SURAT_NOMOR,CPM_PAYMENT_TIPE_SURAT_TANGGAL,CPM_PAYMENT_TIPE_PENGURANGAN,CPM_PAYMENT_TIPE_OTHER,CPM_SSB_CREATED,
      CPM_SSB_AUTHOR,CPM_SSB_VERSION,CPM_SSB_AKUMULASI,CPM_PAYMENT_TIPE_KHD_NOMOR,CPM_WP_NOKTP,CPM_OP_NPOP,CPM_KURANG_BAYAR,CPM_WP_NAMA_LAMA,CPM_WP_NAMA_CERT,CPM_OP_BPHTB_TU,CPM_NO_KURANG_BAYAR,CPM_IDSSB_KURANG_BAYAR, CPM_APHB, CPM_PENGENAAN, CPM_OP_ZNT,CPM_KURANG_BAYAR_SEBELUM,CPM_BPHTB_BAYAR
			,CPM_BPHTB_BAYAR as sebelumpengurangan,'$bphtb_potongan','$bphtb_persen'
      FROM cppmod_ssb_doc 
      where  CPM_SSB_ID ='$rejcs'";

    //  ('" . mysqli_real_escape_string($DBLink, $iddoc) . "','','','" . mysqli_real_escape_string($DBLink, $data[2]) . "','" . mysqli_real_escape_string($DBLink, $data[3]) . "','" . mysqli_real_escape_string($DBLink, nl2br($data[4])) . "','" . mysqli_real_escape_string($DBLink, $data[7]) . "','" . mysqli_real_escape_string($DBLink, $data[8]) . "','" . mysqli_real_escape_string($DBLink, $data[6]) . "','" . mysqli_real_escape_string($DBLink, $data[9]) . "','" . mysqli_real_escape_string($DBLink, $data[10]) . "','" . mysqli_real_escape_string($DBLink, $data[11]) . "','" . mysqli_real_escape_string($DBLink, $data[12]) . "','" . mysqli_real_escape_string($DBLink, nl2br($data[13])) . "','" . mysqli_real_escape_string($DBLink, $data[16]) . "','" . mysqli_real_escape_string($DBLink, $data[17]) . "','" . mysqli_real_escape_string($DBLink, $data[15]) . "','" . mysqli_real_escape_string($DBLink, $data[18]) . "',
    //  '" . mysqli_real_escape_string($DBLink, $data[19]) . "','" . mysqli_real_escape_string($DBLink, $data[20]) . "','" . mysqli_real_escape_string($DBLink, $data[21]) . "','" . mysqli_real_escape_string($DBLink, $data[22]) . "','" . mysqli_real_escape_string($DBLink, $data[24]) . "','" . mysqli_real_escape_string($DBLink, $data[23]) . "','" . mysqli_real_escape_string($DBLink, $data[25]) . "','" . mysqli_real_escape_string($DBLink, $data[26]) . "','" . mysqli_real_escape_string($DBLink, $data[27]) . "','" . mysqli_real_escape_string($DBLink, $data[28]) . "','" . mysqli_real_escape_string($DBLink, $data[29]) . "','2','" . mysqli_real_escape_string($DBLink, $typeSurat) . "','" . mysqli_real_escape_string($DBLink, $typeSuratNomor) . "','" . mysqli_real_escape_string($DBLink, $typeSuratTanggal) . "','" . mysqli_real_escape_string($DBLink, $data[39]) . "','" . mysqli_real_escape_string($DBLink, $typeLainnya) . "','" . mysqli_real_escape_string($DBLink, $trdate) . "','" . mysqli_real_escape_string($DBLink, $opr) . "','" . mysqli_real_escape_string($DBLink, $version) . "','" . mysqli_real_escape_string($DBLink, $data[36]) . "','" . mysqli_real_escape_string($DBLink, $nokhd) . "','" . mysqli_real_escape_string($DBLink, $data[38]) . "','" . mysqli_real_escape_string($DBLink, $data[40]) . "','" . mysqli_real_escape_string($DBLink, $data[41]) . "','" . mysqli_real_escape_string($DBLink, $data[42]) . "','" . mysqli_real_escape_string($DBLink, $data[43]) . "','" . mysqli_real_escape_string($DBLink, $bphtb_terutang) . "','" . $nourutKB . "','" . $idssb_lama . "','" . $pAPHB . "','" . $pengenaan . "','" . $znt . "','" . $bphtb_sebelum . "','" . $data[41] . "')";

    // echo "<pre>";
    // print_r('INSERT INTO cppmod_ssb_tranmain ' . $tranid . ', INSERT INTO cppmod_ssb_doc ' . mysqli_real_escape_string($DBLink, $iddoc) . ', UPDATE cppmod_ssb_tranmain ' . $_REQUEST['trsid']);
    // print_r($query);
    // exit();
    $result = mysqli_query($DBLink, $query);
    if ($result === false) {
      //handle the error here
      print_r(mysqli_error($DBLink));
    } else {
      $sql = "UPDATE cppmod_ssb_doc SET CPM_BPHTB_BAYAR='$bphtb_bayar', statuspengurangan='1' where CPM_SSB_ID ='$rejcs'";
      $result = mysqli_query($DBLink, $sql);
      if ($result === false) {
        //handle the error here
        print_r(mysqli_error($DBLink));
      }
    }
    $sts1 = '2';
    if ($newVer) {
      // $query = sprintf("UPDATE cppmod_ssb_tranmain SET CPM_TRAN_SSB_NEW_VERSION='%s'
      // WHERE CPM_TRAN_ID='%s' ", $version, $_REQUEST['trsid']);
      $query = sprintf("UPDATE cppmod_ssb_tranmain SET CPM_TRAN_FLAG='%s'
       WHERE CPM_TRAN_SSB_ID='%s' ", $sts1, $idssb_lama);
      $result = mysqli_query($DBLink, $query);
    }
    $query = "INSERT INTO cppmod_ssb_tranmain (CPM_TRAN_ID,CPM_TRAN_REFNUM,CPM_TRAN_SSB_ID,CPM_TRAN_SSB_VERSION,CPM_TRAN_STATUS,CPM_TRAN_FLAG,CPM_TRAN_DATE,CPM_TRAN_CLAIM,
		CPM_TRAN_OPR_NOTARIS) VALUES
		 ('" . $tranid . "','" . $refnum . "','" . $rejcs . "','" . $version . "','" . $final . "','0','" . $trdate . "','0','" . $opr . "')";

    $result = mysqli_query($DBLink, $query);
    if ($result === false) {
      //handle the error here
      echo mysqli_error($DBLink);
    } else {
      //print_r($data);
      // @isset($_REQUEST['idssb']) ? $_REQUEST['idssb'] : "";
      $kodebayar = @isset($_REQUEST['kodebayar']) ? $_REQUEST['kodebayar'] : "";
      $sqlDelete = "DELETE FROM gw_ssb.ssb WHERE payment_code='{$kodebayar}'";
      $result = mysqli_query($DBLink, $sqlDelete);

      // var_dump($_REQUEST['kodebayar']);
      // die;
      echo "Data Berhasil disimpan ...! ";
      $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&n=4";
      $address = $_SERVER['HTTP_HOST'] . "payment/pc/svr/central/main.php?param=" . base64_encode($params);
      echo "\n<script language=\"javascript\">\n";

      echo "	function delayer(){\n";
      echo "  hideMask();";
      echo "		window.location = \"./main.php?param=" . base64_encode($params) . "\"\n";
      echo "	}\n";
      echo "	Ext.onReady(function(){\n";
      echo "		setTimeout('delayer()', 2000);\n";
      echo "	});\n";
      echo "</script>\n";
    }
  } else {
    echo $err;
    echo formSSB($data);
  }
}
function getUrutKB()
{
  global $DBLink;
  $qry = "select MAX(CPM_NO_KURANG_BAYAR) AS CPM_NO_KURANG_BAYAR from cppmod_ssb_doc where CPM_PAYMENT_TIPE ='2'";
  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    echo $qry . "<br>";
    echo mysqli_error($DBLink);
  }
  $urut = "";
  while ($row = mysqli_fetch_assoc($res)) {
    if (is_null($row['CPM_NO_KURANG_BAYAR'])) {
      $urut = 1;
    } else {
      $urut = $row['CPM_NO_KURANG_BAYAR'] + 1;
    }
    return $urut;
  }
}
$save = $_REQUEST['btn-save'];
$rejc = @isset($_REQUEST['reject-data']) ? $_REQUEST['reject-data'] : "";


if (($save == 'Simpan') || ($save == 'Simpan sebagai versi baru')) {
  // var_dump($rejcs);
  // die;
  if ($rejc) {
    // var_dump("apa ");
    // die;
    save(1, true);
  } else {
    // var_dump("apa sini");
    // die;
    if (!getNOPBPHTB($_REQUEST['name2'])) {
      save(1);
    } else {
      echo $msg = "Maaf data anda tidak bisa ditindaklanjuti, karena sudah ditransaksikan sebelumnya. <br>Silahkan hubungi Administrator (DPPKAD)!";
      $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&n=4";
      $address = $_SERVER['HTTP_HOST'] . "payment/pc/svr/central/main.php?param=" . base64_encode($params);
      echo "\n<script language=\"javascript\">\n";
      echo "	alert('{$msg}');\n";
      echo "	function delayer(){\n";
      echo "		window.location = \"./main.php?param=" . base64_encode($params) . "\"\n";
      echo "	}\n";
      echo "	Ext.onReady(function(){\n";
      echo "		setTimeout('delayer()', 5000);\n";
      echo "	});\n";
      echo "</script>\n";
    }
  }
} else if (($save == 'Simpan dan Finalkan') || ($save == 'Simpan versi baru dan finalkan')) {

  if ($rejc) {
    if (getConfigValue('VERIFIKASI') != '1') {
      save(3, true);
    } else {
      save(2, true);
    }
  } else {
    if (!getNOPBPHTB($_REQUEST['name2'])) {
      if (getConfigValue('VERIFIKASI') != '1') {
        save(3);
      } else {
        save(2);
      }
    } else {

      echo $msg = "Maaf data anda tidak bisa ditindaklanjuti, karena sudah ditransaksikan sebelumnya. <br>Silahkan hubungi Administrator (DPPKAD)!";
      $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&n=4";
      $address = $_SERVER['HTTP_HOST'] . "payment/pc/svr/central/main.php?param=" . base64_encode($params);
      echo "\n<script language=\"javascript\">\n";
      echo "	alert('{$msg}');\n";
      echo "	function delayer(){\n";
      echo "		window.location = \"./main.php?param=" . base64_encode($params) . "\"\n";
      echo "	}\n";
      echo "	Ext.onReady(function(){\n";
      echo "		setTimeout('delayer()', 5000);\n";
      echo "	});\n";
      echo "</script>\n";
    }
  }
} else if ($idssb) {
  echo "<script language=\"javascript\">var axx = '" . base64_encode($_REQUEST['a']) . "'</script> ";
  echo formSSB();
} else {
  echo "<script language=\"javascript\">var axx = '" . base64_encode($_REQUEST['a']) . "'</script> ";
  echo inputKodeBayar();
}
