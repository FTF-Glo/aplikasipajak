<?php

// ini_set('memory_limit', '2048M');
// if (session_id() == '') {
//   session_start();
// }
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set("log_errors", 1);
// ini_set("error_log", "/tmp/patda-base-v2-error.log");
class Cetak extends Pajak
{
  private $tanggallapor;
  private $masapajak;

  function download_bentang_panjang_abt1()
  {
    $this->download_pajak_xls_pat_tahunan1();
  }
  function download_bentang_panjang_hot()
  {
    $this->download_bentang_panjang_ter1();
  }
  function download_bentang_panjang_res11()
  {
    // $this->download_bentang_panjang_teres();
    $this->download_pajak_xls_bentang_panjang_S1();
  }
  function where3_cetak_bentang_tahun()
  {
    $periode = '';
    $periode_bulan = '';
    $where = "(";
    $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

    if ($this->_mod == "pel") { #pelaporan
      if ($this->_s == 0) { #semua data
        $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ver") { #verifikasi
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "per") { #persetujuan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ply") { #pelayanan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    }
    $where .= ") ";
    //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
    $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
    $where .= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
    $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

    // if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
    $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"" . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) . "\" " : "   ";
    // }

    //$where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
    if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
      $where .= "  AND YEAR(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"))<= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\")";
    }
    return $where;
  }
  function where_cetak_bentang_tahun()
  {
    $periode = '';
    $periode_bulan = '';
    $where = "(";
    $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan

    if ($this->_mod == "pel") { #pelaporan
      if ($this->_s == 0) { #semua data
        $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ver") { #verifikasi
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "per") { #persetujuan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ply") { #pelayanan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    }
    $where .= ") ";
    //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
    $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
    $where .= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
    $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

    // if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
    $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"" . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) . "\" " : "   ";
    // }

    //$where.= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
    if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
      $where .= "  AND YEAR(STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\"))<= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\")";
    }
    return $where;
  }
  //Atb
  public function download_pajak_xls_atb_su()
  {

    // echo "string";
    // exit();
    $periode = '';
    $periode_bulan = '';
    $where = "(";
    $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
    $where2 = '';
    if ($this->_mod == "pel") { #pelaporan
      if ($this->_s == 0) { #semua data
        $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ver") { #verifikasi
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "per") { #persetujuan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ply") { #pelayanan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    }
    $where .= ") ";
    //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
    $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
    // $where.= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
    $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

    $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : " ";

    $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
    if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
      $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
      $periode = 'BULAN ' . $this->arr_bulan[date('n', strtotime($_REQUEST['CPM_TGL_LAPOR1']))];
      $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
    }

    $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
    $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';
    $z = 0;
    // var_dump($JENIS_PAJAK);
    // die;
    $query_wp = "SELECT wp.*,pr.CPM_NAMA_OP FROM patda_wp wp 
      INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' {$where2} 
      WHERE wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' ORDER BY wp.CPM_KECAMATAN_WP ASC";
    // $query_wp = "SELECT wp.*,pr.CPM_NAMA_OP FROM patda_wp wp 
    // INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD 
    // WHERE  wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' ORDER BY wp.CPM_KECAMATAN_WP ASC";
    // var_dump($query_wp);
    // die;
    #query select list data

    $query2 = "SELECT
          SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
          YEAR(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) AS TAHUN_LAPOR,
          YEAR(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_YEAR,
          MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
          pr.CPM_NPWPD,
          pr.CPM_NAMA_WP,
          UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
          pr.CPM_ALAMAT_WP,
          pr.CPM_ALAMAT_OP,
          pr.CPM_KECAMATAN_WP,
          pr.CPM_KECAMATAN_OP,
          atr.CPM_ATR_VOLUME as CPM_VOLUME
        FROM
          PATDA_{$JENIS_PAJAK}_DOC pj
          LEFT JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
          LEFT JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
          LEFT JOIN PATDA_{$JENIS_PAJAK}_DOC_ATR atr
      ON pj.CPM_ID = atr.CPM_ATR_{$JENIS_PAJAK}_ID AND pj.`CPM_MASA_PAJAK`=atr.`CPM_ATR_BULAN`
              WHERE {$where}
              GROUP BY CPM_BULAN,CPM_NPWPD,CPM_YEAR
              ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";
    //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1'
    $data = array();

    $res = mysqli_query($this->Conn, $query2);

    while ($row = mysqli_fetch_assoc($res)) {

      $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
      $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
      $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
      $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
      switch (TRUE) {
        case ($row['CPM_YEAR'] != $row['TAHUN_LAPOR']):
          $data[$row['CPM_NPWPD']]['tahun'][$row['CPM_YEAR']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_PAJAK' => $row['CPM_TOTAL_PAJAK']);

          break;
        case ($row['CPM_YEAR'] == $row['TAHUN_LAPOR']):
          $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);

          break;
      }
      //$data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);
    }



    $where3 = $this->where3_cetak_bentang_tahun();
    $where4 = $this->where3_cetak_bentang();

    $data2 = array();


    $query4 = "SELECT
                  -- SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
                  pj.CPM_TOTAL_PAJAK as CPM_TOTAL_PAJAK,
                  YEAR(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) AS TAHUN_LAPOR,
                  YEAR(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_YEAR,
                  MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                  pr.CPM_NPWPD,
                  pr.CPM_NAMA_WP,
                  UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
                  pr.CPM_ALAMAT_WP,
                  pr.CPM_ALAMAT_OP,
                  pr.CPM_KECAMATAN_WP,
                  pr.CPM_KECAMATAN_OP,
                  atr.CPM_ATR_VOLUME as CPM_VOLUME,
                  atr.CPM_ATR_{$JENIS_PAJAK}_ID as CPM_ID_ATR
                FROM
                  PATDA_{$JENIS_PAJAK}_DOC pj
                LEFT JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
                LEFT JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                JOIN PATDA_{$JENIS_PAJAK}_DOC_ATR atr ON pj.CPM_ID = atr.CPM_ATR_{$JENIS_PAJAK}_ID AND pj.`CPM_MASA_PAJAK`=atr.`CPM_ATR_BULAN`
                WHERE {$where}
                -- GROUP BY CPM_VOLUME,CPM_BULAN,CPM_NPWPD
                ORDER BY CPM_YEAR, pr.CPM_KECAMATAN_OP, pr.CPM_NAMA_OP";
    // var_dump($query4);
    // die;
    $data3 = array();
    $res3 = mysqli_query($this->Conn, $query4);
    // $jumlah_data;
    $npwp_x = '';
    $tahun_x = '';
    while ($row = mysqli_fetch_assoc($res3)) {
      
      if($npwp_x != $row['CPM_NPWPD']){
        $npwp_x = $row['CPM_NPWPD'];
        $CPM_YEAR = [];
        $CPM_YEAR[] = array(
          'CPM_YEAR' => $row['CPM_YEAR'],
          'CPM_VOLUME' => $row['CPM_VOLUME'],
          'CPM_TAHUN_TOTAL' => $row['CPM_TOTAL_PAJAK'],
        );

      }else{
        $npwp_x = $row['CPM_NPWPD'];
        $CPM_YEAR[] = array(
          'CPM_YEAR' => $row['CPM_YEAR'],
          'CPM_VOLUME' => $row['CPM_VOLUME'],
          'CPM_TAHUN_TOTAL' => $row['CPM_TOTAL_PAJAK'],
        );
      }

      $data3[$row['CPM_NPWPD']] = array(
        'CPM_ID_ATR' => $row['CPM_ID_ATR'],
      );
      $data3[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data3[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data3[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data3[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
      $data3[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
      $data3[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
      $data3[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      $data3[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      //$data2[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['T_PAJAK'];
      $data3[]['tahun'][] = $CPM_YEAR;
    }

    // echo '<pre>';
    // print_r(json_encode($data3));
    // die;

    $data_wp = array();
    $res_wp = mysqli_query($this->Conn, $query_wp);

    while ($row = mysqli_fetch_assoc($res_wp)) {
      $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
    }

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set properties
    $objPHPExcel->getProperties()->setCreator("vpost")
      ->setLastModifiedBy("vpost")
      ->setTitle("9 PAJAK ONLINE")
      ->setSubject("-")
      ->setDescription("bphtb")
      ->setKeywords("9 PAJAK ONLINE");

    switch (TRUE) {
      case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
        $tahun_pajak_label = date('Y');
        break;
      case ($_REQUEST['CPM_TAHUN_PAJAK'] != "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
        $tahun_pajak_label = $_REQUEST['CPM_TAHUN_PAJAK'];
        break;
      case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] != ""):

        $tahun_pajak_label =  date("Y", strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        break;
      default:
        $tahun_pajak_label = date('Y');
        break;
    }

    // Add some data

    $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "Triwulan IV " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "Triwulan IV " . (date('Y') - 1);

    $pajakk = '';
    $SPTPD = '';
    if ($JENIS_PAJAK === 'MINERAL') {
      $pajakk = 'MINERBA';
      $SPTPD = 'SPTPD';
    } elseif ($JENIS_PAJAK === 'AIRBAWAHTANAH') {
      $pajakk = 'PAJAK AIR TANAH';
      $SPTPD = 'SKPD';
    }


    $objPHPExcel->setActiveSheetIndex(0)
      ->setCellValue('A1', 'PEMERINTAH KABUPATEN LAMPUNG SELATAN')
      ->setCellValue('A2', 'REKAPITULASI ' . $SPTPD . '  ' . $pajakk)

      ->setCellValue('A3', 'BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH')
      ->setCellValue('A4', 'MASA PAJAK TRIWULAN I s/d TRIWULAN IV ' . $tahun_pajak_label . '')
      ->setCellValue('A5', 'BIDANG PENGEMBANGAN DAN PENETAPAN')

      ->setCellValue('A7', 'NO.')
      ->setCellValue('B7', 'NAMA OBJEK PAJAK.')
      ->setCellValue('D7', 'PAJAK ' . $JENIS_PAJAK . ' TAHUN ')
      ->setCellValue('C8', $tahun_pajak_label)
      ->setCellValue('C7', 'TAPBOX.')
      ->setCellValue('I9', 'TRIWULAN I')
      ->setCellValue('J9', 'TRIWULAN II')
      ->setCellValue('K9', 'TRIWULAN III')
      ->setCellValue('L9', 'TRIWULAN IV')
      ->setCellValue('M7', 'VOLUME AIR M3')
      // ->setCellValue('V7', 'ID I')
      // ->setCellValue('W7', 'ID II')
      // ->setCellValue('X7', 'BULAN')
      ->setCellValue('V7', 'JUMLAH VOLUME AIR. M3');

    switch (true) {
      case ($JENIS_PAJAK == 'AIRBAWAHTANAH'):
        $objPHPExcel->setActiveSheetIndex(0)
          ->setCellValue('R9', 'TRIWULAN I')
          ->setCellValue('S9', 'TRIWULAN II')
          ->setCellValue('T9', 'TRIWULAN III')
          ->setCellValue('U9', 'TRIWULAN IV')
          ->setCellValue('W7', 'JUMLAH.');
        for ($i = 0; $i < 6; $i++) {
          $bar = 8;
          $column = PHPExcel_Cell::columnIndexFromString('Q') - $i; // hitung kolom baru
          $column_letter = PHPExcel_Cell::stringFromColumnIndex($column); // konversi angka kolom ke huruf
          $cell = $column_letter . $bar; // gabungkan huruf kolom dan nomor baris untuk membentuk string sel
          //  echo $cell;

          $year = $tahun_pajak_label - $i;

          $objPHPExcel->setActiveSheetIndex($z)
            ->setCellValue($cell, $year);
        }

        break;
    }

    if ($JENIS_PAJAK == 'AIRBAWAHTANAH') {
      for ($i = 0; $i < 6; $i++) {
        $bar = 8;
        $column = PHPExcel_Cell::columnIndexFromString('H') - $i; // hitung kolom baru
        $column_letter = PHPExcel_Cell::stringFromColumnIndex($column); // konversi angka kolom ke huruf
        $cell = $column_letter . $bar; // gabungkan huruf kolom dan nomor baris untuk membentuk string sel
        //  echo $cell;

        $year = $tahun_pajak_label - $i;

        $objPHPExcel->setActiveSheetIndex($z)
          ->setCellValue($cell, $year);
      }
      // die;
    }
    // var_dump($JENIS_PAJAK);
    // die;

    // judul dok

    $objPHPExcel->getActiveSheet()->mergeCells("A1:M1");
    $objPHPExcel->getActiveSheet()->mergeCells("A2:M2");
    $objPHPExcel->getActiveSheet()->mergeCells("A3:M3");
    $objPHPExcel->getActiveSheet()->mergeCells("A4:M4");
    $objPHPExcel->getActiveSheet()->mergeCells("A5:M5");
    //$objPHPExcel->getActiveSheet()->mergeCells("A6:M6");
    $objPHPExcel->getActiveSheet()->mergeCells("I8:L8");
    $objPHPExcel->getActiveSheet()->mergeCells("R8:U8");
    $objPHPExcel->getActiveSheet()->mergeCells("M7:U7");

    $objPHPExcel->getActiveSheet()->mergeCells("A7:A9");
    $objPHPExcel->getActiveSheet()->mergeCells("B7:B9");
    $objPHPExcel->getActiveSheet()->mergeCells("C7:C9");
    $objPHPExcel->getActiveSheet()->mergeCells("D7:L7");


    // Miscellaneous glyphs, UTF-8
    $objPHPExcel->setActiveSheetIndex(0);

    $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
    $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
    $tab = $jns[$this->_s];
    $jml = 0;

    $row = 10;
    $sumRows = mysqli_num_rows($res);
    $total_pajak = 0;

    foreach ($data_wp as $npwpd => $rowDataWP) {
      $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
    }

    //Menghitung jumlah orang setiap kecamatan
    $jumlah_orang_kecamatan = array();

    foreach ($data_wp as $npwpd => $rawDataWP) {
      $kecamatan = $rawDataWP['CPM_KECAMATAN_WP'];

      if (isset($jumlah_orang_kecamatan[$kecamatan])) {
        $jumlah_orang_kecamatan[$kecamatan]++;
      } else {
        $jumlah_orang_kecamatan[$kecamatan] = 1;
      }
    }

    $jumlah_data = count($data_wp);

    foreach ($data_wp as $npwpd => $rowDataWP) {



      $rowData = $data[$rowDataWP['CPM_NPWPD']];
      $rowData2 = $data2[$rowDataWP['CPM_NPWPD']];
      $rowData3 = $data3[$rowDataWP['CPM_NPWPD']];


      $queryNext = "SELECT *
              FROM patda_airbawahtanah_doc AS a
              JOIN patda_airbawahtanah_doc_atr AS b ON a.CPM_ID = b.CPM_ATR_AIRBAWAHTANAH_ID
              
              WHERE b.CPM_ATR_AIRBAWAHTANAH_ID = '" . $rowData3['CPM_ID_ATR'] . "'";

      $nilaiR = [];
      $resNext = mysqli_query($this->Conn, $queryNext);
      
      while ($row4 = mysqli_fetch_assoc($resNext)) {
        $nilaiR[] = $row4['CPM_ATR_VOLUME'];
      }
      
      $nilaiR = implode(', ', $nilaiR);



      if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
        $nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':B' . $row);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");

        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $tahun1);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tahun2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $tahun3);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $tahun4);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $tahun5);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $triwulan_satu);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $triwulan_dua);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $triwulan_tiga);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $triwulan_empat);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $airtahun1);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $airtahun2);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $airtahun3);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $airtahun4);
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $airtahun5);
        $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $air1);
        $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $air2);
        $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $air3);
        $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $air4);
        $objPHPExcel->getActiveSheet()->setCellValue('V' . $row, $jumair);

        $objPHPExcel->getActiveSheet()->setCellValue('W' . $row, $total_pajak);

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':W' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':W' . $row)->getFill()->getStartColor()->setRGB('ffc000');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':W' . $row)->applyFromArray(
          array(
            'font' => array(
              'bold' => true
            ),
          )
        );

        if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
          // var_dump($row);die;
          $space = $row + 1;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':W' . $space);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->getFill()->getStartColor()->setRGB('ffffff');
          $row++;
        }


        $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
        $row++;
      }

      if ($rowDataWP['CPM_KECAMATAN_WP']) {

        if ($rowDataWP['CPM_KECAMATAN_WP'] != $s_kecamatan) {
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':W' . $row);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':W' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':W' . $row)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':W' . $row)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );

          $s_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
          $row++;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 2, 2);
          $no = 0;
        }
      }

      $query2 = "select CPM_ID, CPM_NPWPD,UPPER(CPM_NAMA_OP) as CPM_NAMA_OP from PATDA_RESTORAN_PROFIL where CPM_NPWPD='" . $rowData['CPM_NP WPD'] . "' order by CPM_TGL_UPDATE asc";
      $resR = mysqli_query($this->Conn, $query2);
      $row_cek = mysqli_fetch_array($resR);
      // echo "string";
      $history = strtoupper($row_cek['CPM_NAMA_OP']);
      //}

      $nama_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
      $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
      $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no + 1));
      $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_OP'], PHPExcel_Cell_DataType::TYPE_STRING);
      $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, '');

      // $objPHPExcel->getActiveSheet()->setCellValue('D'.$row, '');
      $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0);

      $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, ($rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK']));
      $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK']);
      $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK']);
      $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);

      // $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData3['tahun'][$year]['CPM_VOLUME'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData3['tahun'][$year]['CPM_VOLUME'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData3['tahun'][$year + 1]['CPM_VOLUME'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData3['tahun'][$year + 2]['CPM_VOLUME'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData3['tahun'][$year + 3]['CPM_VOLUME'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData3['tahun'][$year + 4]['CPM_VOLUME'] + 0);





      $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $rowData['bulan'][1]['CPM_VOLUME'] + $rowData['bulan'][2]['CPM_VOLUME'] + $rowData['bulan'][3]['CPM_VOLUME']);
      $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $rowData['bulan'][4]['CPM_VOLUME'] + $rowData['bulan'][5]['CPM_VOLUME'] + $rowData['bulan'][6]['CPM_VOLUME']);
      $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $rowData['bulan'][7]['CPM_VOLUME'] + $rowData['bulan'][8]['CPM_VOLUME'] + $rowData['bulan'][9]['CPM_VOLUME']);
      $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $rowData['bulan'][10]['CPM_VOLUME'] + $rowData['bulan'][11]['CPM_VOLUME'] + $rowData['bulan'][12]['CPM_VOLUME']);

      $objPHPExcel->getActiveSheet()->setCellValue('V' . $row, $rowData3['tahun'][$year + 2]['CPM_VOLUME'] + $rowData3['tahun'][$year + 3]['CPM_VOLUME'] + $rowData3['tahun'][$year + 4]['CPM_VOLUME'] +  $rowData['bulan'][1]['CPM_VOLUME'] + $rowData['bulan'][2]['CPM_VOLUME'] + $rowData['bulan'][3]['CPM_VOLUME'] + $rowData['bulan'][4]['CPM_VOLUME'] + $rowData['bulan'][5]['CPM_VOLUME'] + $rowData['bulan'][6]['CPM_VOLUME'] + $rowData['bulan'][7]['CPM_VOLUME'] + $rowData['bulan'][8]['CPM_VOLUME'] + $rowData['bulan'][9]['CPM_VOLUME'] + $rowData['bulan'][10]['CPM_VOLUME'] + $rowData['bulan'][11]['CPM_VOLUME'] + $rowData['bulan'][12]['CPM_VOLUME']);


      $objPHPExcel->getActiveSheet()->setCellValue('W' . $row, $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] +  $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);
      // echo "apapappa";
      // die;
      if ($nama_kecamatan != $nama_kecamatans) {
        $total_pajak = 0;
        $tahun1 = 0;
        $tahun2 = 0;
        $tahun3 = 0;
        $tahun4 = 0;
        $tahun5 = 0;
        $triwulan_satu = 0;
        $triwulan_dua = 0;
        $triwulan_tiga = 0;
        $triwulan_empat = 0;
        $totalair = 0;
        $airtahun1 = 0;
        $airtahun2 = 0;
        $airtahun3 = 0;
        $airtahun4 = 0;
        $airtahun5 = 0;
        $airtriwulan1  = 0;
        $airtriwulan2 = 0;
        $airtriwulan3  = 0;
        $airtriwulan4 = 0;
        $totaldesbelum = 0;
      }
      $tahun1 += $rowData3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0;
      $tahun2 += $rowData3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0;
      $tahun3 += $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0;
      $tahun4 += $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0;
      $tahun5 += $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0;

      $airtahun1 += $rowData3['tahun'][$year]['CPM_VOLUME'] + 0;
      $airtahun2 += $rowData3['tahun'][$year + 1]['CPM_VOLUME'] + 0;
      $airtahun3 += $rowData3['tahun'][$year + 2]['CPM_VOLUME'] + 0;
      $airtahun4 += $rowData3['tahun'][$year + 3]['CPM_VOLUME'] + 0;
      $airtahun5 += $rowData3['tahun'][$year + 4]['CPM_VOLUME'] + 0;
      $totalair += $rowData3['tahun'][$year + 2]['CPM_VOLUME'] + $rowData3['tahun'][$year + 3]['CPM_VOLUME'] + $rowData3['tahun'][$year + 4]['CPM_VOLUME'] + $rowData['bulan'][1]['CPM_VOLUME'] + $rowData['bulan'][2]['CPM_VOLUME'] + $rowData['bulan'][3]['CPM_VOLUME'] + $rowData['bulan'][4]['CPM_VOLUME'] + $rowData['bulan'][5]['CPM_VOLUME'] + $rowData['bulan'][6]['CPM_VOLUME'] + $rowData['bulan'][7]['CPM_VOLUME'] + $rowData['bulan'][8]['CPM_VOLUME'] + $rowData['bulan'][9]['CPM_VOLUME'] + $rowData['bulan'][10]['CPM_VOLUME'] + $rowData['bulan'][11]['CPM_VOLUME'] + $rowData['bulan'][12]['CPM_VOLUME'];
      $airtriwulan1 += $rowData['bulan'][1]['CPM_VOLUME'] + $rowData['bulan'][2]['CPM_VOLUME'] + $rowData['bulan'][3]['CPM_VOLUME'] + 0;
      $airtriwulan2 += $rowData['bulan'][4]['CPM_VOLUME'] + $rowData['bulan'][5]['CPM_VOLUME'] + $rowData['bulan'][6]['CPM_VOLUME'] + 0;
      $airtriwulan3 += $rowData['bulan'][7]['CPM_VOLUME'] + $rowData['bulan'][8]['CPM_VOLUME'] + $rowData['bulan'][9]['CPM_VOLUME'] + 0;
      $airtriwulan4 += $rowData['bulan'][10]['CPM_VOLUME'] + $rowData['bulan'][11]['CPM_VOLUME'] + $rowData['bulan'][12]['CPM_VOLUME'] + 0;



      $total_pajak += $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
      $triwulan_satu += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
      $triwulan_dua += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
      $triwulan_tiga += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
      $triwulan_empat += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      //$desbelum += $rowData2['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

      //untuk total
      $total_total_pajak += $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
      $totalairtotal += $rowData3['tahun'][$year + 2]['CPM_VOLUME'] + $rowData3['tahun'][$year + 3]['CPM_VOLUME'] + $rowData3['tahun'][$year + 4]['CPM_VOLUME'] + $rowData['bulan'][1]['CPM_VOLUME'] + $rowData['bulan'][2]['CPM_VOLUME'] + $rowData['bulan'][3]['CPM_VOLUME'] + $rowData['bulan'][4]['CPM_VOLUME'] + $rowData['bulan'][5]['CPM_VOLUME'] + $rowData['bulan'][6]['CPM_VOLUME'] + $rowData['bulan'][7]['CPM_VOLUME'] + $rowData['bulan'][8]['CPM_VOLUME'] + $rowData['bulan'][9]['CPM_VOLUME'] + $rowData['bulan'][10]['CPM_VOLUME'] + $rowData['bulan'][11]['CPM_VOLUME'] + $rowData['bulan'][12]['CPM_VOLUME'];

      $totalairtriwulan1 += $rowData['bulan'][1]['CPM_VOLUME'] + $rowData['bulan'][2]['CPM_VOLUME'] + $rowData['bulan'][3]['CPM_VOLUME'] + 0;
      $totalairtriwulan2 += $rowData['bulan'][4]['CPM_VOLUME'] + $rowData['bulan'][5]['CPM_VOLUME'] + $rowData['bulan'][6]['CPM_VOLUME'] + 0;
      $totalairtriwulan3 += $rowData['bulan'][7]['CPM_VOLUME'] + $rowData['bulan'][8]['CPM_VOLUME'] + $rowData['bulan'][9]['CPM_VOLUME'] + 0;
      $totalairtriwulan4 += $rowData['bulan'][10]['CPM_VOLUME'] + $rowData['bulan'][11]['CPM_VOLUME'] + $rowData['bulan'][12]['CPM_VOLUME'] + 0;
      // $totaldesbelum += $rowData2['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      $totalairtahun1 += $rowData3['tahun'][$year]['CPM_VOLUME'] + 0;
      $totalairtahun2 += $rowData3['tahun'][$year + 1]['CPM_VOLUME'] + 0;
      $totalairtahun3 += $rowData3['tahun'][$year + 2]['CPM_VOLUME'] + 0;
      $totalairtahun4 += $rowData3['tahun'][$year + 3]['CPM_VOLUME'] + 0;
      $totalairtahun5 += $rowData3['tahun'][$year + 4]['CPM_VOLUME'] + 0;
      

      $total_triwulan_satu += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
      $total_triwulan_dua += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
      $total_triwulan_tiga += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
      $total_triwulan_empat += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      // $totaldesbelum += $rowData2['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      $total_tahun1 += $rowData3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0;
      $total_tahun2 += $rowData3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0;
      $total_tahun3 += $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0;
      $total_tahun4 += $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0;
      $total_tahun5 += $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0;

      
      //var_dump($total_pajak);die;

      $jml++;
      $row++;
      $no++;
      // var_dump($jumlah_data, $row);die;
      if ($jumlah_data == $jml) {
        // $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':E'.$row);
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $tahun1);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tahun2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $tahun3);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $tahun4);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $tahun5);

        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $triwulan_satu);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $triwulan_dua);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $triwulan_tiga);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $triwulan_empat);

        $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $airtahun1, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $airtahun2, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $airtahun3, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $airtahun4, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $airtahun5, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);

        $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $airtriwulan1, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $airtriwulan2, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $airtriwulan3, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $airtriwulan4, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);

        $objPHPExcel->getActiveSheet()->setCellValue('V' . $row, $totalair, PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
        $objPHPExcel->getActiveSheet()->setCellValue('W' . $row, $total_pajak);

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':W' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':W' . $row)->getFill()->getStartColor()->setRGB('ffc000');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':W' . $row)->applyFromArray(
          array(
            'font' => array(
              'bold' => true
            ),
          )
        );


        if ($jumlah_data == $jml) {
          // var_dump($row);die;
          $space = $row + 1;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          // $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':E'.$row);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "Jumlah Pajak ");

          $objPHPExcel->getActiveSheet()->setCellValue('D' . $space, $total_tahun1);
          $objPHPExcel->getActiveSheet()->setCellValue('E' . $space, $total_tahun2);
          $objPHPExcel->getActiveSheet()->setCellValue('F' . $space, $total_tahun3);
          $objPHPExcel->getActiveSheet()->setCellValue('G' . $space, $total_tahun4);
          $sada = $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $total_tahun5);

          // echo"<pre>";
          // print_r($sada);die;

          $objPHPExcel->getActiveSheet()->setCellValue('I' . $space, $total_triwulan_satu);
          $objPHPExcel->getActiveSheet()->setCellValue('J' . $space, $total_triwulan_dua);
          $objPHPExcel->getActiveSheet()->setCellValue('K' . $space, $total_triwulan_tiga);
          $objPHPExcel->getActiveSheet()->setCellValue('L' . $space, $total_triwulan_empat);


          $objPHPExcel->getActiveSheet()->setCellValue('M' . $space, $totalairtahun1);
          $objPHPExcel->getActiveSheet()->setCellValue('N' . $space, $totalairtahun1);
          $objPHPExcel->getActiveSheet()->setCellValue('O' . $space, $totalairtahun1);
          $objPHPExcel->getActiveSheet()->setCellValue('P' . $space, $totalairtahun1);
          $objPHPExcel->getActiveSheet()->setCellValue('Q' . $space, $totalairtahun1);

          $objPHPExcel->getActiveSheet()->setCellValue('R' . $space, $totalairtriwulan2);
          $objPHPExcel->getActiveSheet()->setCellValue('S' . $space, $totalairtriwulan2);
          $objPHPExcel->getActiveSheet()->setCellValue('T' . $space, $totalairtriwulan2);
          $objPHPExcel->getActiveSheet()->setCellValue('U' . $space, $totalairtriwulan2);

          $objPHPExcel->getActiveSheet()->setCellValue('V' . $space, $totalairtotal);
          $objPHPExcel->getActiveSheet()->setCellValue('W' . $space, $total_total_pajak);

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );
        }


        if ($jumlah_data == $jml) {
          //var_dump($row);die;
          $space = $row + 3;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':D' . $space);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "KETERANGAN JUMLAH WP PERKECAMATAN");


          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->getFill()->getStartColor()->setRGB('ffff00');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );
        }

        // var_dump($jumlah_data);
        // die;
        $space = $space + 1;
        $no_keterangan = 0;
        $total_wp = 0;
        // $query_keterangan = "select CPM_KECAMATAN_WP, count(CPM_KECAMATAN_WP) as TOTAL from patda_wp where CPM_JENIS_PAJAK like '%{$this->_idp}%'  GROUP BY CPM_KECAMATAN_WP  ORDER BY CPM_KECAMATAN_WP ASC";

        // $res_keterangan = mysqli_query($this->Conn, $query_keterangan);
        // while ($row_keterangan = mysqli_fetch_array($res_keterangan)) {
        //   $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
        //   $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, $row_keterangan['CPM_KECAMATAN_WP']);
        //   $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $row_keterangan['TOTAL']);
        //   $totalwp += $row_keterangan['TOTAL'] + 0;

        //   $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':W' . $space)->getFill()->getStartColor()->setRGB('ffff00');
        //   $space++;
        //   $no_keterangan++;


        // Menampilkan hasil jumlah orang dalam setiap kecamatan
        foreach ($jumlah_orang_kecamatan as $kecamatan => $jumlah) {
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
          $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, 'JUMLAH WP KECAMATAN ' . ($kecamatan == '' ? "LAIN " : $kecamatan));
          $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $jumlah);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffff00');
          $space++;
          $no_keterangan++;
          $total_wp += $jumlah;
        }
        // CODINGAN UNTUK MENAMPILKAN TOTAL WP DI CETAK BENTANG PANJANG
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($space), "TOTAL");
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($space), $total_wp);
      }
    }

    // echo $nilaiR;
    // die;

    /** style **/
    // judul dok + judul tabel
    $objPHPExcel->getActiveSheet()->getStyle('A1:M5')->applyFromArray(
      array(
        'font' => array(
          'bold' => true
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A7:W9')->applyFromArray(
      array(
        'font' => array(
          'bold' => true
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A5:W7')->getAlignment()->setWrapText(true);

    // border
    $objPHPExcel->getActiveSheet()->getStyle('A7:W' . $row)->applyFromArray(
      array(
        'borders' => array(
          'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
          )
        )
      )
    );


    // fill tabel header
    $objPHPExcel->getActiveSheet()->getStyle('A7:W9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A7:W9')->getFill()->getStartColor()->setRGB('E4E4E4');

    // format angka col I & K
    $objPHPExcel->getActiveSheet()->getStyle('E10:L' . $row)->getNumberFormat()->setFormatCode('#,##0');
    $objPHPExcel->getActiveSheet()->getStyle('W10:W' . $row)->getNumberFormat()->setFormatCode('#,##0');
    //$objPHPExcel->getActiveSheet()->setCellValueExplicit('M10:V'. $row['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC, 2);
    // $objPHPExcel->getActiveSheet()->getStyle('M10:V' . $row)->getNumberFormat()->setFormatCode('#,##0.000');

    // // fill tabel footer
    // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->getStartColor()->setRGB('E4E4E4');



    // Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak ' . $tab);


    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
    for ($x = "A"; $x <= "H"; $x++) {
      if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
      else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
    }
    ob_clean();
    // Redirect output to a client’s web browser (Excel5)

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="rekap-tahunan-' . strtolower($JENIS_PAJAK) . '-' . $_REQUEST['CPM_TAHUN_PAJAK'] . '.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
    // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
    $objWriter->save('php://output');
    mysqli_close($this->Conn);
  }
  //mineral
  private function download_pajak_xls_pat_tahunan1()
  {

    // echo "string";exit();
    $periode = '';
    $periode_bulan = '';
    $where = "(";
    $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
    $where2 = '';
    if ($this->_mod == "pel") { #pelaporan
      if ($this->_s == 0) { #semua data
        $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ver") { #verifikasi
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "per") { #persetujuan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ply") { #pelayanan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    }
    $where .= ") ";
    //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
    $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
    // $where.= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
    $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

    $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : " ";

    $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
    if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
      $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                  STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
      $periode = 'BULAN ' . $this->arr_bulan[date('n', strtotime($_REQUEST['CPM_TGL_LAPOR1']))];
      $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
    }

    $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
    $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';
    $z = 0;

    $query_wp = "SELECT wp.*,pr.CPM_NAMA_OP FROM patda_wp wp 
    INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' {$where2} 
    WHERE wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' ORDER BY wp.CPM_KECAMATAN_WP ASC";

    #query select list data

    $query2 = "SELECT
            SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
            YEAR(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) AS TAHUN_LAPOR,
            YEAR(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_YEAR,
            MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
            pr.CPM_NPWPD,
            pr.CPM_NAMA_WP,
            UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
            pr.CPM_ALAMAT_WP,
            pr.CPM_ALAMAT_OP,
            pr.CPM_KECAMATAN_WP,
            pr.CPM_KECAMATAN_OP
          FROM
            PATDA_{$JENIS_PAJAK}_DOC pj
            LEFT JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
            LEFT JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
            WHERE {$where}
            GROUP BY CPM_BULAN,CPM_NPWPD,CPM_YEAR
            ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";

    //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1'
    $data = array();
    $res = mysqli_query($this->Conn, $query2);
    while ($row = mysqli_fetch_assoc($res)) {

      $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
      $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
      $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
      $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
      switch (TRUE) {
        case ($row['CPM_YEAR'] != $row['TAHUN_LAPOR']):
          $data[$row['CPM_NPWPD']]['tahunk'][$row['CPM_YEAR']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_PAJAK' => $row['CPM_TOTAL_PAJAK']);

          break;
        case ($row['CPM_YEAR'] == $row['TAHUN_LAPOR']):
          $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);

          break;
      }
      //$data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);
    }



    $where3 = $this->where3_cetak_bentang_tahun();
    $where4 = $this->where3_cetak_bentang();

    $data2 = array();


    $query4 = "SELECT
                    SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
                    YEAR(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) AS TAHUN_LAPOR,
                    YEAR(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_YEAR,
                    MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                    pr.CPM_NPWPD,
                    pr.CPM_NAMA_WP,
                    UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
                    pr.CPM_ALAMAT_WP,
                    pr.CPM_ALAMAT_OP,
                    pr.CPM_KECAMATAN_WP,
                    pr.CPM_KECAMATAN_OP
                  FROM
                    PATDA_{$JENIS_PAJAK}_DOC pj
                    LEFT JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
                    LEFT JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                    WHERE {$where}
                    GROUP BY CPM_YEAR,CPM_NPWPD
                    ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";


    // die(var_dump($query4));
    $data3 = array();
    $res3 = mysqli_query($this->Conn, $query4);
    // $jumlah_data;
    while ($row = mysqli_fetch_assoc($res3)) {
      $data3[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data3[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data3[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data3[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
      $data3[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
      $data3[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
      $data3[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      $data3[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      //$data2[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['T_PAJAK'];
      $data3[$row['CPM_NPWPD']]['tahun'][$row['CPM_YEAR']] = array(
        'CPM_VOLUME' => $row['CPM_VOLUME'],
        'CPM_TAHUN_TOTAL' => $row['CPM_TOTAL_PAJAK'],
      );
    }

    $data_wp = array();
    $res_wp = mysqli_query($this->Conn, $query_wp);

    while ($row = mysqli_fetch_assoc($res_wp)) {
      $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
    }



    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set properties
    $objPHPExcel->getProperties()->setCreator("vpost")
      ->setLastModifiedBy("vpost")
      ->setTitle("9 PAJAK ONLINE")
      ->setSubject("-")
      ->setDescription("bphtb")
      ->setKeywords("9 PAJAK ONLINE");

    switch (TRUE) {
      case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
        $tahun_pajak_label = date('Y');
        break;
      case ($_REQUEST['CPM_TAHUN_PAJAK'] != "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
        $tahun_pajak_label = $_REQUEST['CPM_TAHUN_PAJAK'];
        break;
      case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] != ""):

        $tahun_pajak_label =  date("Y", strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        break;
      default:
        $tahun_pajak_label = date('Y');
        break;
    }

    // Add some data
    $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "Triwulan IV " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "Triwulan IV " . (date('Y') - 1);

    $pajakk = '';
    $SPTPD = '';
    if ($JENIS_PAJAK === 'MINERAL') {
      $pajakk = 'MINERBA';
      $SPTPD = 'SPTPD';
    } elseif ($JENIS_PAJAK === 'AIRBAWAHTANAH') {
      $pajakk = 'PAJAK AIR TANAH';
      $SPTPD = 'SKPD';
    }


    $objPHPExcel->setActiveSheetIndex(0)
      ->setCellValue('A1', 'PEMERINTAH KABUPATEN LAMPUNG SELATAN')
      ->setCellValue('A2', 'REKAPITULASI ' . $SPTPD . '  ' . $pajakk)

      ->setCellValue('A3', 'BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH')
      ->setCellValue('A4', 'MASA PAJAK TRIWULAN I s/d TRIWULAN IV ' . $tahun_pajak_label . '')
      ->setCellValue('A5', 'BIDANG PENGEMBANGAN DAN PENETAPAN')

      ->setCellValue('A7', 'NO.')
      ->setCellValue('B7', 'NAMA OBJEK PAJAK.')
      ->setCellValue('D7', 'PAJAK ' . $JENIS_PAJAK . ' TAHUN ')
      ->setCellValue('C8', $tahun_pajak_label)
      ->setCellValue('C7', 'TAPBOX.')
      ->setCellValue('I9', 'TRIWULAN I')
      ->setCellValue('J9', 'TRIWULAN II')
      ->setCellValue('K9', 'TRIWULAN III')
      ->setCellValue('L9', 'TRIWULAN IV')
      ->setCellValue('M7', 'JUMLAH.');

    if ($JENIS_PAJAK == 'AIRBAWAHTANAH' || $JENIS_PAJAK === 'MINERAL') {
      for ($i = 0; $i < 6; $i++) {
        $bar = 8;
        $column = PHPExcel_Cell::columnIndexFromString('H') - $i; // hitung kolom baru
        $column_letter = PHPExcel_Cell::stringFromColumnIndex($column); // konversi angka kolom ke huruf
        $cell = $column_letter . $bar; // gabungkan huruf kolom dan nomor baris untuk membentuk string sel
        //  echo $cell;

        $year = $tahun_pajak_label - $i;

        $objPHPExcel->setActiveSheetIndex($z)
          ->setCellValue($cell, $year);
      }
    }
    $objPHPExcel->getActiveSheet()->mergeCells("A1:M1");
    $objPHPExcel->getActiveSheet()->mergeCells("A2:M2");
    $objPHPExcel->getActiveSheet()->mergeCells("A3:M3");
    $objPHPExcel->getActiveSheet()->mergeCells("A4:M4");
    $objPHPExcel->getActiveSheet()->mergeCells("A5:M5");
    //$objPHPExcel->getActiveSheet()->mergeCells("A6:M6");
    $objPHPExcel->getActiveSheet()->mergeCells("I8:M8");

    $objPHPExcel->getActiveSheet()->mergeCells("A7:A9");
    $objPHPExcel->getActiveSheet()->mergeCells("B7:B9");
    $objPHPExcel->getActiveSheet()->mergeCells("C7:C9");
    $objPHPExcel->getActiveSheet()->mergeCells("D7:L7");


    // Miscellaneous glyphs, UTF-8
    $objPHPExcel->setActiveSheetIndex(0);

    $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
    $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
    $tab = $jns[$this->_s];
    $jml = 0;

    $row = 10;
    $sumRows = mysqli_num_rows($res);
    $total_pajak = 0;

    foreach ($data_wp as $npwpd => $rowDataWP) {
      $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
    }
    //Menghitung jumlah orang setiap kecamatan
    $jumlah_orang_kecamatan = array();

    foreach ($data_wp as $npwpd => $rawDataWP) {
      $kecamatan = $rawDataWP['CPM_KECAMATAN_WP'];

      if (isset($jumlah_orang_kecamatan[$kecamatan])) {
        $jumlah_orang_kecamatan[$kecamatan]++;
      } else {
        $jumlah_orang_kecamatan[$kecamatan] = 1;
      }
    }

    $jumlah_data = count($data_wp);

    foreach ($data_wp as $npwpd => $rowDataWP) {
      $rowData = $data[$rowDataWP['CPM_NPWPD']];
      $rowData2 = $data2[$rowDataWP['CPM_NPWPD']];
      $rowData3 = $data3[$rowDataWP['CPM_NPWPD']];



      //var_dump($rowDataWP['CPM_KECAMATAN_WP'], $cek_kecamatan);die;
      if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
        $nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);
        // var_dump($rowDataWP['CPM_KECAMATAN_WP'], $cek_kecamatan);die;

        // $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':E'.$row);
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':B' . $row);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");

        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $tahun1);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tahun2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $tahun3);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $tahun4);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $tahun5);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $triwulan_satu);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $triwulan_dua);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $triwulan_tiga);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $triwulan_empat);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $total_pajak);

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':M' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':M' . $row)->getFill()->getStartColor()->setRGB('ffc000');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':M' . $row)->applyFromArray(
          array(
            'font' => array(
              'bold' => true
            ),
          )
        );

        if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
          // var_dump($row);die;
          $space = $row + 1;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':M' . $space);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':M' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':M' . $space)->getFill()->getStartColor()->setRGB('ffffff');
          $row++;
        }


        $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
        $row++;
      }







      if ($rowDataWP['CPM_KECAMATAN_WP']) {

        if ($rowDataWP['CPM_KECAMATAN_WP'] != $s_kecamatan) {
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':M' . $row);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':M' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':M' . $row)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':M' . $row)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );

          $s_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
          $row++;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 2, 2);
          $no = 0;
        }
      }
      $query2 = "select CPM_ID, CPM_NPWPD,UPPER(CPM_NAMA_OP) as CPM_NAMA_OP from PATDA_RESTORAN_PROFIL where CPM_NPWPD='" . $rowData['CPM_NPWPD'] . "' order by CPM_TGL_UPDATE asc";



      $resR = mysqli_query($this->Conn, $query2);
      $row_cek = mysqli_fetch_array($resR);
      // echo "string";
      $history = strtoupper($row_cek['CPM_NAMA_OP']);
      //}

      $nama_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
      $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
      $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no + 1));
      $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_WP'], PHPExcel_Cell_DataType::TYPE_STRING);
      $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, '');

      // $objPHPExcel->getActiveSheet()->setCellValue('D'.$row, '');
      $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0);

      $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK']);
      $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK']);
      $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK']);
      $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);
      $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] +  $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);
      // echo "apapappa";
      // die;
      if ($nama_kecamatan != $nama_kecamatans) {
        $total_pajak = 0;
        $tahun1 = 0;
        $tahun2 = 0;
        $tahun3 = 0;
        $tahun4 = 0;
        $tahun5 = 0;
        $triwulan_satu = 0;
        $triwulan_dua = 0;
        $triwulan_tiga = 0;
        $triwulan_empat = 0;

        $totaldesbelum = 0;
      }
      $tahun1 += $rowData3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0;
      $tahun2 += $rowData3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0;
      $tahun3 += $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0;
      $tahun4 += $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0;
      $tahun5 += $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0;

      $total_pajak += $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
      $triwulan_satu += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
      $triwulan_dua += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
      $triwulan_tiga += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
      $triwulan_empat += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      //$desbelum += $rowData2['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

      //untuk total
      $total_total_pajak += $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];

      $total_triwulan_satu += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
      $total_triwulan_dua += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
      $total_triwulan_tiga += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
      $total_triwulan_empat += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      // $totaldesbelum += $rowData2['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      $total_tahun1 += $rowData3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0;
      $total_tahun2 += $rowData3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0;
      $total_tahun3 += $rowData3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0;
      $total_tahun4 += $rowData3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0;
      $total_tahun5 += $rowData3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0;
      //var_dump($total_pajak);die;

      $jml++;
      $row++;
      $no++;
      // var_dump($jumlah_data, $row);die;
      if ($jumlah_data == $jml) {
        // $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':E'.$row);
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $tahun1);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tahun2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $tahun3);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $tahun4);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $tahun5);

        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $triwulan_satu);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $triwulan_dua);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $triwulan_tiga);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $triwulan_empat);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $total_pajak);

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':M' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':M' . $row)->getFill()->getStartColor()->setRGB('ffc000');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':M' . $row)->applyFromArray(
          array(
            'font' => array(
              'bold' => true
            ),
          )
        );


        if ($jumlah_data == $jml) {
          // var_dump($row);die;
          $space = $row + 1;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          // $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':E'.$row);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "Jumlah Pajak ");

          $objPHPExcel->getActiveSheet()->setCellValue('D' . $space, $total_tahun1);
          $objPHPExcel->getActiveSheet()->setCellValue('E' . $space, $total_tahun2);
          $objPHPExcel->getActiveSheet()->setCellValue('F' . $space, $total_tahun3);
          $objPHPExcel->getActiveSheet()->setCellValue('G' . $space, $total_tahun4);
          $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $total_tahun5);

          $objPHPExcel->getActiveSheet()->setCellValue('I' . $space, $total_triwulan_satu);
          $objPHPExcel->getActiveSheet()->setCellValue('J' . $space, $total_triwulan_dua);
          $objPHPExcel->getActiveSheet()->setCellValue('K' . $space, $total_triwulan_tiga);
          $objPHPExcel->getActiveSheet()->setCellValue('L' . $space, $total_triwulan_empat);
          $objPHPExcel->getActiveSheet()->setCellValue('M' . $space, $total_total_pajak);

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':M' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':M' . $space)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':M' . $space)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );
        }


        if ($jumlah_data == $jml) {
          //var_dump($row);die;
          $space = $row + 3;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':D' . $space);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "KETERANGAN JUMLAH WP PERKECAMATAN");


          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':M' . $space)->getFill()->getStartColor()->setRGB('ffff00');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':M' . $space)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );
        }

        // var_dump($space);die;
        $space = $space + 1;
        $no_keterangan = 0;
        $total_wp = 0;

        // Menampilkan hasil jumlah orang dalam setiap kecamatan
        foreach ($jumlah_orang_kecamatan as $kecamatan => $jumlah) {
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
          $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, 'JUMLAH WP KECAMATAN ' . ($kecamatan == '' ? "LAIN " : $kecamatan));
          $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $jumlah);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffff00');
          $space++;
          $no_keterangan++;
          $total_wp += $jumlah;
        }
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':B' . $space);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, 'Jumlah :');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $total_wp);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':C' . $space)->applyFromArray(
          array(
            'font' => array(
              'bold' => true
            ),
          )
        );
      }
    }



    /** style **/
    // judul dok + judul tabel
    $objPHPExcel->getActiveSheet()->getStyle('A1:M4')->applyFromArray(
      array(
        'font' => array(
          'bold' => true
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A7:M9')->applyFromArray(
      array(
        'font' => array(
          'bold' => true
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A5:M7')->getAlignment()->setWrapText(true);

    // border
    $objPHPExcel->getActiveSheet()->getStyle('A7:M' . $row)->applyFromArray(
      array(
        'borders' => array(
          'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
          )
        )
      )
    );


    // fill tabel header
    $objPHPExcel->getActiveSheet()->getStyle('A7:M9')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A7:M9')->getFill()->getStartColor()->setRGB('E4E4E4');

    // format angka col I & K
    $objPHPExcel->getActiveSheet()->getStyle('E10:M' . $row)->getNumberFormat()->setFormatCode('#,##0');

    // // fill tabel footer
    // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->getStartColor()->setRGB('E4E4E4');



    // Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak ' . $tab);


    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
    for ($x = "A"; $x <= "H"; $x++) {
      if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
      else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
    }
    ob_clean();
    // Redirect output to a client’s web browser (Excel5)

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="rekap-tahunan-' . strtolower($JENIS_PAJAK) . '-' . $_REQUEST['CPM_TAHUN_PAJAK'] . '.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
    // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
    $objWriter->save('php://output');
    mysqli_close($this->Conn);
  }

  //'HIBURAN' 'HOTEL'  'JALAN' 'PARKIR' 'REKLAME') {
  private function download_bentang_panjang_ter1()
  {
    // var_dump('sji');
    // die;
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);
    $periode = '';
    $periode_bulan = '';
    $where = "(";
    $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
    $where2 = '';
    if ($this->_mod == "pel") { #pelaporan
      if ($this->_s == 0) { #semua data
        $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ver") { #verifikasi
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "per") { #persetujuan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ply") { #pelayanan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    }
    $where .= ") ";
    //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
    $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
    $where .= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
    $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

    //if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
    $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : " ";
    //}

    $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
    if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
      $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                  STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
      $periode = 'BULAN ' . $this->arr_bulan[date('n', strtotime($_REQUEST['CPM_TGL_LAPOR1']))];
      $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
    }
    $thuuun = (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : "";



    $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
    $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';
    $objPHPExcel = new PHPExcel();
    $jenis_pajaks = (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? "{$_REQUEST['CPM_JENIS_PJK']}" : "";
    $jenisPajak = $this->arr_tipe_pajak;

    $z = 0;

    $query_wp = "SELECT wp.*,pr.CPM_NAMA_OP FROM patda_wp wp 
    INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD 
    WHERE  wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' ORDER BY wp.CPM_KECAMATAN_WP ASC";
    // var_dump($query_wp);
    // die;

    #query select list data AND pr.CPM_AKTIF = '1'  OR pr.CPM_AKTIF = '1' wp.CPM_STATUS = '1' &&

    $query2 = "SELECT
            SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
            YEAR(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) AS TAHUN_LAPOR,
            YEAR(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_YEAR,
            MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
            pr.CPM_NPWPD,
            pr.CPM_NAMA_WP,
            UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
            pr.CPM_ALAMAT_WP,
            pr.CPM_ALAMAT_OP,
            pr.CPM_KECAMATAN_WP,
            pr.CPM_KECAMATAN_OP
          FROM
            PATDA_{$JENIS_PAJAK}_DOC pj
            LEFT JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
            LEFT JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
            WHERE {$where}
            GROUP BY CPM_BULAN,CPM_NPWPD,CPM_YEAR
            ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";
    // echo "<PRE>";
    // var_dump($query2);
    // die;
    //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1'
    $data = array();
    $res = mysqli_query($this->Conn, $query2);
    while ($row = mysqli_fetch_assoc($res)) {

      $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
      $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
      $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
      $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
      switch (TRUE) {
        case ($row['CPM_YEAR'] != $row['TAHUN_LAPOR']):
          $data[$row['CPM_NPWPD']]['tahun'][$row['CPM_YEAR']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_PAJAK' => $row['CPM_TOTAL_PAJAK']);

          break;
        case ($row['CPM_YEAR'] == $row['TAHUN_LAPOR']):
          $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);

          break;
      }
      //  $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);
    }



    $where3 = $this->where3_cetak_bentang_tahun();
    $where4 = $this->where3_cetak_bentang();

    $data2 = array();

    $query4 = "SELECT
                    SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
                    YEAR(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) AS TAHUN_LAPOR,
                    YEAR(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_YEAR,
                    MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
                    pr.CPM_NPWPD,
                    pr.CPM_NAMA_WP,
                    UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
                    pr.CPM_ALAMAT_WP,
                    pr.CPM_ALAMAT_OP,
                    pr.CPM_KECAMATAN_WP,
                    pr.CPM_KECAMATAN_OP
                  FROM
                    PATDA_{$JENIS_PAJAK}_DOC pj
                    LEFT JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
                    LEFT JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                    WHERE {$where}
                    GROUP BY CPM_YEAR,CPM_NPWPD
                    ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";


    $data3 = array();
    $res3 = mysqli_query($this->Conn, $query4);
    // $jumlah_data;
    while ($row = mysqli_fetch_assoc($res3)) {
      $data3[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data3[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data3[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data3[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
      $data3[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
      $data3[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
      $data3[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      $data3[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      //$data2[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['T_PAJAK'];
      $data3[$row['CPM_NPWPD']]['tahun'][$row['CPM_YEAR']] = array(
        'CPM_VOLUME' => $row['CPM_VOLUME'],
        'CPM_TAHUN_TOTAL' => $row['CPM_TOTAL_PAJAK'],
      );
    }

    // // var_dump($query4);
    // die;
    $data_wp = array();
    $res_wp = mysqli_query($this->Conn, $query_wp);

    while ($row = mysqli_fetch_assoc($res_wp)) {


      $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
    }

    $objPHPExcel->getProperties()->setCreator("vpost")
      ->setLastModifiedBy("vpost")
      ->setTitle("9 PAJAK ONLINE")
      ->setSubject("-")
      ->setDescription("bphtb")
      ->setKeywords("9 PAJAK ONLINE");

    // Add some data
    // $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "DES " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "DES " . (date('Y') - 1);
    switch (TRUE) {
      case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
        $tahun_pajak_label = date('Y');
        break;
      case ($_REQUEST['CPM_TAHUN_PAJAK'] != "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
        $tahun_pajak_label = $_REQUEST['CPM_TAHUN_PAJAK'];
        break;
      case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] != ""):

        $tahun_pajak_label =  date("Y", strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        break;
      default:
        $tahun_pajak_label = date('Y');
        break;
    }


    $objPHPExcel->setActiveSheetIndex($z)
      ->setCellValue('A1', 'PEMERINTAH KABUPATEN LAMPUNG SELATAN')
      ->setCellValue('A2', 'REKAPITULASI SPTPD PAJAK ' . $JENIS_PAJAK)
      ->setCellValue('A3', 'BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH')
      ->setCellValue('A4', 'MASA PAJAK JANUARI s/d DESEMBER ' . $tahun_pajak_label . '')
      ->setCellValue('A5', 'BIDANG PENGEMBANGAN DAN PENETAPAN')
      ->setCellValue('A6', '')
      ->setCellValue('A7', 'NO.')
      ->setCellValue('B7', 'NAMA OBJEK PAJAK.')
      ->setCellValue('C7', 'NILAI SPTPD PAJAK ' . $JENIS_PAJAK)
      ->setCellValue('C8',  'TAHUN ')
      ->setCellValue('C9', 'TAPBOX.')
      ->setCellValue('I10', 'JAN')
      ->setCellValue('J10', 'FEB')
      ->setCellValue('K10', 'MAR')
      ->setCellValue('L10', 'APRIL')
      ->setCellValue('M10', 'MEI')
      ->setCellValue('N10', 'JUNI')
      ->setCellValue('O10', 'JULI')
      ->setCellValue('P10', 'AGS')
      ->setCellValue('Q10', 'SEPT')
      ->setCellValue('R10', 'OKT')
      ->setCellValue('S10', 'NOP')
      ->setCellValue('T10', 'DES')
      ->setCellValue('U7', 'JUMLAH');


    // 1 => "AIR BAWAH TANAH",
    // 2 => "HIBURAN",3 => "HOTEL",5 => "PARKIR", 6 => "PENERANGAN JALAN", 7 => "REKLAME", 8 => "RESTORAN",9 => "SARANG WALET"
    // 4 => "MINERAL BUKAN LOGAM DAN BATUAN ",
    if ($JENIS_PAJAK == 'HIBURAN' || $JENIS_PAJAK == 'HOTEL' || $JENIS_PAJAK == 'JALAN' || $JENIS_PAJAK == 'PARKIR'  || $JENIS_PAJAK == 'REKLAME') {
      for ($i = 0; $i < 6; $i++) {
        $bar = 9;
        $column = PHPExcel_Cell::columnIndexFromString('H') - $i; // hitung kolom baru
        $column_letter = PHPExcel_Cell::stringFromColumnIndex($column); // konversi angka kolom ke huruf
        $cell = $column_letter . $bar; // gabungkan huruf kolom dan nomor baris untuk membentuk string sel
        //   echo $cell;

        $year = $tahun_pajak_label - $i;
        $objPHPExcel->setActiveSheetIndex($z)
          ->setCellValue($cell, $year);
      }
    }
    // die;
    if ($JENIS_PAJAK == 'RESTORAN') {
      $objPHPExcel->setActiveSheetIndex($z)
        ->setCellValue('B7', 'NAMA WAJIB OP.');
    }

    // judul dok
    $objPHPExcel->getActiveSheet()->mergeCells("A1:R1");
    $objPHPExcel->getActiveSheet()->mergeCells("A2:R2");
    $objPHPExcel->getActiveSheet()->mergeCells("A3:R3");
    $objPHPExcel->getActiveSheet()->mergeCells("A4:R4");
    $objPHPExcel->getActiveSheet()->mergeCells("A5:R5");
    $objPHPExcel->getActiveSheet()->mergeCells("A7:A10");
    $objPHPExcel->getActiveSheet()->mergeCells("B7:B10");
    $objPHPExcel->getActiveSheet()->mergeCells("C9:C10");
    $objPHPExcel->getActiveSheet()->mergeCells("C7:T7");
    $objPHPExcel->getActiveSheet()->mergeCells("C8:T8");
    $objPHPExcel->getActiveSheet()->mergeCells("I9:T9");
    $objPHPExcel->getActiveSheet()->mergeCells("U7:U10");


    // Miscellaneous glyphs, UTF-8
    $objPHPExcel->setActiveSheetIndex($z);

    $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
    $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
    $tab = $jns[$this->_s];
    $jml = 0;

    $row = 11;
    $sumRows = mysqli_num_rows($res);
    $total_pajak = 0;


    foreach ($data_wp as $npwpd => $rowDataWP) {
      $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
    }

    //Menghitung jumlah orang setiap kecamatan
    $jumlah_orang_kecamatan = array();

    foreach ($data_wp as $npwpd => $rawDataWP) {
      $kecamatan = $rawDataWP['CPM_KECAMATAN_WP'];

      if (isset($jumlah_orang_kecamatan[$kecamatan])) {
        $jumlah_orang_kecamatan[$kecamatan]++;
      } else {
        $jumlah_orang_kecamatan[$kecamatan] = 1;
      }
    }

    // $dataGenerator = dataGenerator($data_wp);
    $jumlah_data = count($data_wp);
    foreach ($data_wp as $npwpd => $rowDataWP) {
      $rowData = $data[$rowDataWP['CPM_NPWPD']];
      $rowData2 = $data2[$rowDataWP['CPM_NPWPD']];
      $rowDAta3 = $data3[$rowDataWP['CPM_NPWPD']];

      if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
        $nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);
        // die(var_dump($tahun1));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row,  $tahun1);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row,  $tahun2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row,  $tahun3);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row,  $tahun4);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row,  $tahun5);
        //  $objPHPExcel->getActiveSheet()->getStyle($clm . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_IDR_SIMPLE);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $jan);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $feb);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $mar);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $apr);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $mei);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $jun);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $jul);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $agu);
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $sep);
        $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $okt);
        $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $nov);
        $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $des);
        $objPHPExcel->getActiveSheet()->setCellValue('U' . $row,  $total_pajak);

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
          array(
            'font' => array(
              'bold' => true
            ),
          )
        );

        if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
          $space = $row + 1;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':U' . $space);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->getStartColor()->setRGB('ffffff');
          $row++;
        }

        $no = 0;
        $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
        $row++;
      }

      if ($rowDataWP['CPM_KECAMATAN_WP']) {

        if ($rowDataWP['CPM_KECAMATAN_WP'] != $s_kecamatan) {
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':U' . $row);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );

          $s_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
          $row++;

          $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 2, 2);
        }
      }
      $nama_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
      // echo $nama_kecamatan;
      // exit();
      $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
      $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no + 1));
      $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_WP'], PHPExcel_Cell_DataType::TYPE_STRING);

      if ($JENIS_PAJAK == 'RESTORAN') {
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_OP'], PHPExcel_Cell_DataType::TYPE_STRING);
      }


      $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, '');
      $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowDAta3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowDAta3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowDAta3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowDAta3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowDAta3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0);
      // $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0);

      $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $rowDAta3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + $rowDAta3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + $rowDAta3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);


      if ($nama_kecamatan != $nama_kecamatans) {
        //  $total_pajak = 0;
        $tahun1 = 0;
        $tahun2 = 0;
        $tahun3 = 0;
        $tahun4 = 0;
        $tahun5 = 0;
        $jan = 0;
        $feb = 0;
        $mar = 0;
        $apr = 0;
        $mei = 0;
        $jun = 0;
        $jul = 0;
        $agu = 0;
        $sep = 0;
        $okt = 0;
        $nov = 0;
        $des = 0;
      }


      $total_pajak += $rowDAta3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + $rowDAta3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + $rowDAta3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
      $tahun1 += $rowDAta3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0;
      $tahun2 += $rowDAta3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0;
      $tahun3 += $rowDAta3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0;
      $tahun4 += $rowDAta3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0;
      $tahun5 += $rowDAta3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0;
      $jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
      $feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
      $mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
      $apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
      $mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
      $jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
      $jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
      $agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
      $sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
      $okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
      $nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
      $des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

      //untuk total
      $total_total_pajak += $rowDAta3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + $rowDAta3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + $rowDAta3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
      $tahunTotal1 += $rowDAta3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0;
      $tahunTotal2 += $rowDAta3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0;
      $tahunTotal3 += $rowDAta3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0;
      $tahunTotal4 += $rowDAta3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0;
      $tahunTotal5 += $rowDAta3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0;
      $total_jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
      $total_feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
      $total_mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
      $total_apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
      $total_mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
      $total_jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
      $total_jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
      $total_agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
      $total_sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
      $total_okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
      $total_nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
      $total_des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;



      $jml++;
      $row++;
      $no++;

      if ($jumlah_data == $jml) {

        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $tahun1);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tahun2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $tahun3);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $tahun4);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $tahun5);

        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $jan);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $feb);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $mar);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $apr);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $mei);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $jun);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $jul);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $agu);
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $sep);
        $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $okt);
        $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $nov);
        $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $des);
        $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $total_pajak);


        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
          array(
            'font' => array(
              'bold' => true
            ),
          )
        );


        // var_dump($row);
        // die;
        if ($jumlah_data == $jml) {
          //var_dump($row);die;
          $space = $row + 1;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "Jumlah Pajak ");
          $objPHPExcel->getActiveSheet()->setCellValue('D' . $space, $tahunTotal1);
          $objPHPExcel->getActiveSheet()->setCellValue('E' . $space, $tahunTotal2);
          $objPHPExcel->getActiveSheet()->setCellValue('F' . $space, $tahunTotal3);
          $objPHPExcel->getActiveSheet()->setCellValue('G' . $space, $tahunTotal4);
          $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $tahunTotal5);

          $objPHPExcel->getActiveSheet()->setCellValue('I' . $space, $total_jan);
          $objPHPExcel->getActiveSheet()->setCellValue('J' . $space, $total_feb);
          $objPHPExcel->getActiveSheet()->setCellValue('K' . $space, $total_mar);
          $objPHPExcel->getActiveSheet()->setCellValue('L' . $space, $total_apr);
          $objPHPExcel->getActiveSheet()->setCellValue('M' . $space, $total_mei);
          $objPHPExcel->getActiveSheet()->setCellValue('N' . $space, $total_jun);
          $objPHPExcel->getActiveSheet()->setCellValue('O' . $space, $total_jul);
          $objPHPExcel->getActiveSheet()->setCellValue('P' . $space, $total_agu);
          $objPHPExcel->getActiveSheet()->setCellValue('Q' . $space, $total_sep);
          $objPHPExcel->getActiveSheet()->setCellValue('R' . $space, $total_okt);
          $objPHPExcel->getActiveSheet()->setCellValue('S' . $space, $total_nov);
          $objPHPExcel->getActiveSheet()->setCellValue('T' . $space, $total_des);
          $objPHPExcel->getActiveSheet()->setCellValue('U' . $space, $total_total_pajak);

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );
        }


        if ($jumlah_data == $jml) {
          //var_dump($row);die;
          $space = $row + 3;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "KETERANGAN ");
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->getStartColor()->setRGB('ffff00');
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );
        }

        //var_dump($space);die;
        $space = $space + 1;
        $no_keterangan = 0;
        $total_wp = 0;

        // Menampilkan hasil jumlah orang dalam setiap kecamatan
        foreach ($jumlah_orang_kecamatan as $kecamatan => $jumlah) {
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
          $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, 'JUMLAH WP KECAMATAN ' . ($kecamatan == '' ? "LAIN " : $kecamatan));
          $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $jumlah);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffff00');
          $space++;
          $no_keterangan++;
          $total_wp += $jumlah;
        }
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':B' . $space);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, 'Jumlah :');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $total_wp);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':C' . $space)->applyFromArray(
          array(
            'font' => array(
              'bold' => true
            ),
          )
        );
      }
      gc_collect_cycles();
    }
    // var_dump($rowData);
    // die;

    // echo "COBA AH";
    // die;

    /** style **/
    // judul dok + judul tabel
    $objPHPExcel->getActiveSheet()->getStyle('A1:V5')->applyFromArray(
      array(
        'font' => array(
          'bold' => true
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->applyFromArray(
      array(
        'font' => array(
          'bold' => true
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->getAlignment()->setWrapText(true);

    // border
    $objPHPExcel->getActiveSheet()->getStyle('A7:U' . $row)->applyFromArray(
      array(
        'borders' => array(
          'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
          )
        )
      )
    );
    // var_dump($space);
    // die(var_dump($rowData['tahun']));

    // fill tabel header
    $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->getFill()->getStartColor()->setRGB('E4E4E4');

    // format angka col I & K
    $objPHPExcel->getActiveSheet()->getStyle('E11:U' . $row)->getNumberFormat()->setFormatCode('#,##0');

    // // fill tabel footer
    // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->getStartColor()->setRGB('E4E4E4');



    // Rename sheet
    //$objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak '.$tab);

    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("P")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("R")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("S")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("T")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("U")->setWidth(15);
    for ($x = "A"; $x <= "H"; $x++) {
      if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
      else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
    }

    //  }
    // die(var_dump($_REQUEST['CPM_JENIS_PJK']));
    ob_clean();
    // Redirect output to a client’s web browser (Excel5)

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="rekap-tahunan-' . strtolower($JENIS_PAJAK) . '-' . $_REQUEST['CPM_TAHUN_PAJAK'] . '.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
    // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
    $objWriter->save('php://output');
    mysqli_close($this->Conn);
  }

  //REstoran
  private function download_pajak_xls_bentang_panjang_S1()
  {
    // ini_set('memory_limit', '256M');
    // error_reporting(E_ALL);
    // ini_set('display_errors', 1);
    // ini_set("log_errors", 1);
    $periode = '';
    $periode_bulan = '';
    $where = "(";
    $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
    $where2 = '';

    if ($this->_mod == "pel") { #pelaporan
      if ($this->_s == 0) { #semua data
        $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ver") { #verifikasi
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "per") { #persetujuan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ply") { #pelayanan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    }
    $where .= ") ";
    //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
    $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
    $where .= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
    $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

    //if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
    $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : " ";
    //}

    $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
    if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
      $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                  STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
      $periode = 'BULAN ' . $this->arr_bulan[date('n', strtotime($_REQUEST['CPM_TGL_LAPOR1']))];
      $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
    }

    $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
    $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';

    switch (TRUE) {
      case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
        $tahun_pajak_label = date('Y');
        break;
      case ($_REQUEST['CPM_TAHUN_PAJAK'] != "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
        $tahun_pajak_label = $_REQUEST['CPM_TAHUN_PAJAK'];
        break;
      case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] != ""):

        $tahun_pajak_label =  date("Y", strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        break;
      default:
        $tahun_pajak_label = date('Y');
        break;
    }

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    $jenis_pajaks = (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? "{$_REQUEST['CPM_JENIS_PJK']}" : "";
    $jenisPajak = array(1 => "Restoran", 2 => "Jasa Boga");
    $z = 0;
    foreach ($jenisPajak as $jp => $jp_id) {
      if ($jenis_pajaks != $jp && $jenis_pajaks != '') {
        continue;
      }

      if ($jp == 2) {
        $no = 0;
      }

      if ($jp == 2) {
        $total_total_pajak = 0;
        $total_jan = 0;
        $total_feb = 0;
        $total_mar = 0;
        $total_apr = 0;
        $total_mei = 0;
        $total_jun = 0;
        $total_jul = 0;
        $total_agu = 0;
        $total_sep = 0;
        $total_okt = 0;
        $total_nov = 0;
        $total_des = 0;
      }

      //if(isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != ""){
      //$where .= " AND pj.CPM_TIPE_PAJAK={$_REQUEST['CPM_JENIS_PJK']}";    
      //if($_REQUEST['CPM_JENIS_PJK']==1)
      //	$where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
      //elseif($_REQUEST['CPM_JENIS_PJK']==2)
      //	$where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
      //}

      $where3 = $this->where3_cetak_bentang();
      $where5 = $this->where3_cetak_bentang_tahun();

      if ($this->_idp == '7') {
        $q_tipe_pajak = 'pj.CPM_TYPE_PAJAK';
      } else {
        $q_tipe_pajak = 'pj.CPM_TIPE_PAJAK';
      }

      //$query_wp = "select * from patda_wp where  CPM_STATUS = '1' && CPM_JENIS_PAJAK like '%{$this->_idp}%' ORDER BY CPM_KECAMATAN_WP ASC";
      //if($this->_idp == '8'){
      $query_wp = "SELECT CPM_NAMA_OP,wp.* FROM patda_wp wp 
          INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD 
          WHERE wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' ORDER BY wp.CPM_KECAMATAN_WP ASC";
      //}AND pr.CPM_AKTIF = '1' {$where2} 
      // var_dump($query_wp);
      // die;
      //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' {$where2} 
      #query select list data
      $query2 = "SELECT
             SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
            YEAR(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) AS TAHUN_LAPOR,
            YEAR(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_YEAR,
            MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
          pr.CPM_NPWPD,
          pr.CPM_NAMA_WP,
          UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
          pr.CPM_ALAMAT_WP,
          pr.CPM_ALAMAT_OP,
          pr.CPM_KECAMATAN_WP,
          pr.CPM_KECAMATAN_OP
        FROM
          PATDA_{$JENIS_PAJAK}_DOC pj
          INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
          INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
          WHERE {$where} AND {$q_tipe_pajak} = '{$jp}'
          GROUP BY CPM_BULAN, pr.CPM_NPWPD,CPM_YEAR
          ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";

      //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1'
      // var_dump($query2);
      // die;
      $data = array();
      $res = mysqli_query($this->Conn, $query2);
      while ($row = mysqli_fetch_assoc($res)) {

        $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
        $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
        $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
        $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
        $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
        $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
        $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
        switch (TRUE) {
          case ($row['CPM_YEAR'] != $row['TAHUN_LAPOR']):
            $data[$row['CPM_NPWPD']]['tahun'][$row['CPM_YEAR']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_PAJAK' => $row['CPM_TOTAL_PAJAK']);

            break;
          case ($row['CPM_YEAR'] == $row['TAHUN_LAPOR']):
            $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);

            break;
        }
      }


      // $query3 = "SELECT
      //     SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
      //     MONTH(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) as CPM_BULAN,
      //     YEAR(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) as CPM_TAHUN,
      //     pr.CPM_NPWPD,
      //     pr.CPM_NAMA_WP,
      //     UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
      //     pr.CPM_ALAMAT_WP,
      //     pr.CPM_ALAMAT_OP,
      //     pr.CPM_KECAMATAN_OP
      //         FROM
      //   PATDA_{$JENIS_PAJAK}_DOC pj
      //   INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1' {$where2}
      //             INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
      //             WHERE {$where5} AND YEAR(STR_TO_DATE( CPM_TGL_LAPOR, '%d-%m-%Y' )) <='{$tahun_pajak_label}' AND {$q_tipe_pajak} = '{$jp}'
      //             GROUP BY CPM_TAHUN, pr.CPM_NPWPD
      //   ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";


      $data2 = array();
      // $res2 = mysqli_query($this->Conn, $query3);
      // // $jumlah_data;
      // while ($row = mysqli_fetch_assoc($res2)) {
      //   $data2[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      //   $data2[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      //   $data2[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      //   $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
      //   $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
      //   $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      //   $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      //   //$data2[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['T_PAJAK'];
      //   $data2[$row['CPM_NPWPD']]['tahun'][$row['CPM_TAHUN']] = array(

      //     'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK'],
      //   );
      // }

      $query4 = "SELECT
      SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
     YEAR(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) AS TAHUN_LAPOR,
     YEAR(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_YEAR,
   pr.CPM_NPWPD,
   pr.CPM_NAMA_WP,
   UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
   pr.CPM_ALAMAT_WP,
   pr.CPM_ALAMAT_OP,
   pr.CPM_KECAMATAN_WP,
   pr.CPM_KECAMATAN_OP
   FROM
   PATDA_{$JENIS_PAJAK}_DOC pj
   INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
   INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
   WHERE {$where} AND {$q_tipe_pajak} = '{$jp}'
   GROUP BY  pr.CPM_NPWPD,CPM_YEAR
   ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";


      $data3 = array();
      $res3 = mysqli_query($this->Conn, $query4);
      // $jumlah_data;
      while ($row = mysqli_fetch_assoc($res3)) {
        $data3[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
        $data3[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
        $data3[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
        $data3[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
        $data3[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
        $data3[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
        $data3[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
        //$data2[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['T_PAJAK'];
        $data3[$row['CPM_NPWPD']]['tahun'][$row['CPM_YEAR']] = array(

          'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK'],
        );
      }


      $data_wp = array();

      $res_wp = mysqli_query($this->Conn, $query_wp);
      // echo "<pre>";

      while ($row = mysqli_fetch_assoc($res_wp)) {
        $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
        $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
        $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
        $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
        $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
      }

      // Set properties
      $objPHPExcel->getProperties()->setCreator("vpost")
        ->setLastModifiedBy("vpost")
        ->setTitle("9 PAJAK ONLINE")
        ->setSubject("-")
        ->setDescription("bphtb")
        ->setKeywords("9 PAJAK ONLINE");

      // Add some data
      // $tahun_pajak_label = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? $_REQUEST['CPM_TAHUN_PAJAK'] : date('Y');
      // $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "DES " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "DES " . (date('Y') - 1);
      switch (TRUE) {
        case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
          $tahun_pajak_label = date('Y');
          break;
        case ($_REQUEST['CPM_TAHUN_PAJAK'] != "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
          $tahun_pajak_label = $_REQUEST['CPM_TAHUN_PAJAK'];
          break;
        case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] != ""):

          $tahun_pajak_label =  date("Y", strtotime($_REQUEST['CPM_TGL_LAPOR1']));
          break;
        default:
          $tahun_pajak_label = date('Y');
          break;
      }


      $objPHPExcel->setActiveSheetIndex($z)
        ->setCellValue('A1', 'PEMERINTAH KABUPATEN LAMPUNG SELATAN')
        ->setCellValue('A2', 'REKAPITULASI SPTPD PAJAK ' . $JENIS_PAJAK)
        ->setCellValue('A3', 'BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH')
        ->setCellValue('A4', 'MASA PAJAK JANUARI s/d DESEMBER ' . $tahun_pajak_label . '')
        ->setCellValue('A5', 'BIDANG PENGEMBANGAN DAN PENETAPAN')
        ->setCellValue('A6', '')
        ->setCellValue('A7', 'NO.')
        ->setCellValue('B7', 'NAMA OBJEK PAJAK.')
        ->setCellValue('C7', 'NILAI SPTPD PAJAK ' . $JENIS_PAJAK)
        ->setCellValue('C8',  'TAHUN ')
        //   ->setCellValue('Q7', 'JUMLAH.')
        ->setCellValue('C9', 'TAPBOX.')
        // ->setCellValue('D8', $tahun_pajak_label_sebelumnya)
        // ->setCellValue('E8', 'JAN')
        // ->setCellValue('F8', 'FEB')
        // ->setCellValue('G8', 'MAR')
        // ->setCellValue('H8', 'APRIL')
        ->setCellValue('I10', 'JAN')
        ->setCellValue('J10', 'FEB')
        ->setCellValue('K10', 'MAR')
        ->setCellValue('L10', 'APRIL')
        ->setCellValue('M10', 'MEI')
        ->setCellValue('N10', 'JUNI')
        ->setCellValue('O10', 'JULI')
        ->setCellValue('P10', 'AGS')
        ->setCellValue('Q10', 'SEPT')
        ->setCellValue('R10', 'OKT')
        ->setCellValue('S10', 'NOP')
        ->setCellValue('T10', 'DES')
        ->setCellValue('U7', 'JUMLAH');


      // 1 => "AIR BAWAH TANAH",
      // 2 => "HIBURAN",3 => "HOTEL",5 => "PARKIR", 6 => "PENERANGAN JALAN", 7 => "REKLAME", 8 => "RESTORAN",9 => "SARANG WALET"
      // 4 => "MINERAL BUKAN LOGAM DAN BATUAN ",
      if ($JENIS_PAJAK == 'RESTORAN') {
        for ($i = 0; $i < 6; $i++) {
          $bar = 9;
          $column = PHPExcel_Cell::columnIndexFromString('H') - $i; // hitung kolom baru
          $column_letter = PHPExcel_Cell::stringFromColumnIndex($column); // konversi angka kolom ke huruf
          $cell = $column_letter . $bar; // gabungkan huruf kolom dan nomor baris untuk membentuk string sel
          //   echo $cell;

          $year = $tahun_pajak_label - $i;
          $objPHPExcel->setActiveSheetIndex($z)
            ->setCellValue($cell, $year);
        }
      }
      // judul dok
      // judul dok
      $objPHPExcel->getActiveSheet()->mergeCells("A1:R1");
      $objPHPExcel->getActiveSheet()->mergeCells("A2:R2");
      $objPHPExcel->getActiveSheet()->mergeCells("A3:R3");
      $objPHPExcel->getActiveSheet()->mergeCells("A4:R4");
      $objPHPExcel->getActiveSheet()->mergeCells("A5:R5");
      $objPHPExcel->getActiveSheet()->mergeCells("A7:A10");
      $objPHPExcel->getActiveSheet()->mergeCells("B7:B10");
      $objPHPExcel->getActiveSheet()->mergeCells("C9:C10");
      $objPHPExcel->getActiveSheet()->mergeCells("C7:T7");
      $objPHPExcel->getActiveSheet()->mergeCells("C8:T8");
      $objPHPExcel->getActiveSheet()->mergeCells("I9:T9");
      $objPHPExcel->getActiveSheet()->mergeCells("U7:U10");

      // Miscellaneous glyphs, UTF-8
      $objPHPExcel->setActiveSheetIndex($z);

      $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
      $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
      $tab = $jns[$this->_s];
      $jml = 0;

      $row = 11;
      $sumRows = mysqli_num_rows($res);


      foreach ($data_wp as $npwpd => $rowDataWP) {
        $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
        // var_dump($npwpd);
      }

      //Menghitung jumlah orang setiap kecamatan
      $jumlah_orang_kecamatan = array();

      foreach ($data_wp as $npwpd => $rawDataWP) {
        $kecamatan = $rawDataWP['CPM_KECAMATAN_WP'];

        if (isset($jumlah_orang_kecamatan[$kecamatan])) {
          $jumlah_orang_kecamatan[$kecamatan]++;
        } else {
          $jumlah_orang_kecamatan[$kecamatan] = 1;
        }
      }


      //  var_dump($cek_kecamatan);
      // var_dump($jumlah_orang_kecamatan);
      // die;
      $jumlah_data = count($data_wp);



      foreach ($data_wp as $npwpd => $rowDataWP) {
        // var_dump($data) . '<br>';
        // var_dump($data[$rowDataWP['CPM_NPWPD']]);
        // die;
        $rowData = $data[$rowDataWP['CPM_NPWPD']];
        $rowDAta3 = $data3[$rowDataWP['CPM_NPWPD']];
        $rowData2 = $data2[$rowDataWP['CPM_NPWPD']];

        // var_dump($data3['P171515840404']['tahun']);
        // die;

        // var_dump($data3['P171521171705']);
        // die;
        if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
          $nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);

          // die(var_dump($tahun1));
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");
          $objPHPExcel->getActiveSheet()->setCellValue('D' . $row,  $tahun1);
          $objPHPExcel->getActiveSheet()->setCellValue('E' . $row,  $tahun2);
          $objPHPExcel->getActiveSheet()->setCellValue('F' . $row,  $tahun3);
          $objPHPExcel->getActiveSheet()->setCellValue('G' . $row,  $tahun4);
          $objPHPExcel->getActiveSheet()->setCellValue('H' . $row,  $tahun5);
          //  $objPHPExcel->getActiveSheet()->getStyle($clm . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_IDR_SIMPLE);
          $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $jan);
          $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $feb);
          $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $mar);
          $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $apr);
          $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $mei);
          $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $jun);
          $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $jul);
          $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $agu);
          $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $sep);
          $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $okt);
          $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $nov);
          $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $des);
          $objPHPExcel->getActiveSheet()->setCellValue('U' . $row,  $total_pajak);

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );

          if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {

            $space = $row + 1;
            $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':U' . $space);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->getStartColor()->setRGB('ffffff');
            $row++;
          }

          $no = 0;
          $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
          $row++;
        }

        if ($rowDataWP['CPM_KECAMATAN_WP']) {

          if ($rowDataWP['CPM_KECAMATAN_WP'] != $s_kecamatan) {
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':U' . $row);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

            $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
              array(
                'font' => array(
                  'bold' => true
                ),
              )
            );

            $s_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
            $row++;

            $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 2, 2);
          }
        }
        $nama_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
        // echo $nama_kecamatan;
        // exit();
        $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no + 1));
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_OP'], PHPExcel_Cell_DataType::TYPE_STRING);

        // if ($JENIS_PAJAK == 'RESTORAN') {
        //   $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_OP'], PHPExcel_Cell_DataType::TYPE_STRING);
        // }


        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, '');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowDAta3['tahun'][$year]['CPM_TOTAL_PAJAK'] + 0);

        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowDAta3['tahun'][$year + 1]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowDAta3['tahun'][$year + 2]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowDAta3['tahun'][$year + 3]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowDAta3['tahun'][$year + 4]['CPM_TOTAL_PAJAK'] + 0);
        // $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $rowDAta3['tahun'][$year + 1]['CPM_TOTAL_PAJAK'] + $rowDAta3['tahun'][$year + 2]['CPM_TOTAL_PAJAK'] + $rowDAta3['tahun'][$year + 3]['CPM_TOTAL_PAJAK'] + $rowDAta3['tahun'][$year + 4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);


        if ($nama_kecamatan != $nama_kecamatans) {
          $total_pajak = 0;
          $tahun1 = 0;
          $tahun2 = 0;
          $tahun3 = 0;
          $tahun4 = 0;
          $tahun5 = 0;
          $jan = 0;
          $feb = 0;
          $mar = 0;
          $apr = 0;
          $mei = 0;
          $jun = 0;
          $jul = 0;
          $agu = 0;
          $sep = 0;
          $okt = 0;
          $nov = 0;
          $des = 0;
        }


        $total_pajak +=  $rowDAta3['tahun'][$year + 2]['CPM_TOTAL_PAJAK'] + $rowDAta3['tahun'][$year + 3]['CPM_TOTAL_PAJAK'] + $rowDAta3['tahun'][$year + 4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
        $tahun1 += $rowDAta3['tahun'][$year]['CPM_TOTAL_PAJAK'] + 0;
        $tahun2 += $rowDAta3['tahun'][$year + 1]['CPM_TOTAL_PAJAK'] + 0;
        $tahun3 += $rowDAta3['tahun'][$year + 2]['CPM_TOTAL_PAJAK'] + 0;
        $tahun4 += $rowDAta3['tahun'][$year + 3]['CPM_TOTAL_PAJAK'] + 0;
        $tahun5 += $rowDAta3['tahun'][$year + 4]['CPM_TOTAL_PAJAK'] + 0;
        $jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
        $feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
        $mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
        $apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
        $mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
        $jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
        $jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
        $agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
        $sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
        $okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
        $nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
        $des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
        $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

        //untuk total
        $total_total_pajak += $rowDAta3['tahun'][$year + 2]['CPM_TOTAL_PAJAK'] + $rowDAta3['tahun'][$year + 3]['CPM_TOTAL_PAJAK'] + $rowDAta3['tahun'][$year + 4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
        $tahunTotal1 += $rowDAta3['tahun'][$year]['CPM_TOTAL_PAJAK'] + 0;
        $tahunTotal2 += $rowDAta3['tahun'][$year + 1]['CPM_TOTAL_PAJAK'] + 0;
        $tahunTotal3 += $rowDAta3['tahun'][$year + 2]['CPM_TOTAL_PAJAK'] + 0;
        $tahunTotal4 += $rowDAta3['tahun'][$year + 3]['CPM_TOTAL_PAJAK'] + 0;
        $tahunTotal5 += $rowDAta3['tahun'][$year + 4]['CPM_TOTAL_PAJAK'] + 0;
        $total_jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
        $total_feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
        $total_mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
        $total_apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
        $total_mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
        $total_jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
        $total_jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
        $total_agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
        $total_sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
        $total_okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
        $total_nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
        $total_des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;



        $jml++;
        $row++;
        $no++;

        if ($jumlah_data == $jml) {

          $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah");
          $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $tahun1);
          $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tahun2);
          $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $tahun3);
          $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $tahun4);
          $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $tahun5);
          // var_dump($rowDAta2);
          // die;
          $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $jan);
          $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $feb);
          $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $mar);
          $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $apr);
          $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $mei);
          $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $jun);
          $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $jul);
          $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $agu);
          $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $sep);
          $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $okt);
          $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $nov);
          $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $des);
          $objPHPExcel->getActiveSheet()->setCellValue('U' . $row,  $total_pajak);


          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );


          // var_dump($no);
          // die;
          if ($jumlah_data == $jml) {
            //var_dump($row);die;
            $space = $row + 1;
            $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "Jumlah Pajak ");
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $space, $tahunTotal1);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $space, $tahunTotal2);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $space, $tahunTotal3);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $space, $tahunTotal4);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $tahunTotal5);
            // echo "<pre>";
            // var_dump($data3['P171512220009']["buan"]['CPM_TOTAL_PAJAK']);
            // var_dump();
            // die;
            // if()
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $space, $total_jan);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $space, $total_feb);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $space, $total_mar);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $space, $total_apr);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $space, $total_mei);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $space, $total_jun);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $space, $total_jul);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $space, $total_agu);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $space, $total_sep);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $space, $total_okt);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . $space, $total_nov);
            $objPHPExcel->getActiveSheet()->setCellValue('T' . $space, $total_des);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . $space, $total_total_pajak);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->getStartColor()->setRGB('ffc000');

            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->applyFromArray(
              array(
                'font' => array(
                  'bold' => true
                ),
              )
            );
          }


          if ($jumlah_data == $jml) {
            //var_dump($row);die;
            $space = $row + 3;
            $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "KETERANGAN ");
            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->getStartColor()->setRGB('ffff00');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->applyFromArray(
              array(
                'font' => array(
                  'bold' => true
                ),
              )
            );
          }
          // var_dump($jml);
          // die;
          //var_dump($space);die;
          $space = $space + 1;
          $no_keterangan = 0;
          $total_wp = 0;
          // //$query_keterangan = "select CPM_KECAMATAN_WP, count(CPM_KECAMATAN_WP) as TOTAL from patda_wp where CPM_STATUS = '1' && CPM_JENIS_PAJAK like '%{$this->_idp}%' GROUP BY CPM_KECAMATAN_WP ORDER BY CPM_KECAMATAN_WP ASC";
          // //if($this->_idp == '8'){
          // $query_keterangan = "SELECT
          //               wp.CPM_KECAMATAN_WP,
          //               count( wp.CPM_KECAMATAN_WP ) AS TOTAL 
          //             FROM
          //               patda_wp wp
          //               INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' 
          //               AND pr.CPM_ID = (SELECT MAX(CPM_ID) FROM PATDA_{$JENIS_PAJAK}_PROFIL pr WHERE CPM_AKTIF = 1 && CPM_NPWPD = wp.CPM_NPWPD {$where2})  {$where2}
          //             WHERE
          //               wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' 
          //             GROUP BY
          //               CPM_KECAMATAN_WP 
          //             ORDER BY
          //               CPM_KECAMATAN_WP ASC";
          //}
          // var_dump($query_keterangan);
          // die;

          // Menampilkan hasil jumlah orang dalam setiap kecamatan
          foreach ($jumlah_orang_kecamatan as $kecamatan => $jumlah) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, 'JUMLAH WP KECAMATAN ' . ($kecamatan == '' ? "LAIN " : $kecamatan));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $jumlah);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffff00');
            $space++;
            $no_keterangan++;
            $total_wp += $jumlah;
          }

          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':B' . $space);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, 'Jumlah :');
          $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $total_wp);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':C' . $space)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );
        }
      }

      // var_dump($no);
      // die;



      /** style **/
      // judul dok + judul tabel
      $objPHPExcel->getActiveSheet()->getStyle('A1:V5')->applyFromArray(
        array(
          'font' => array(
            'bold' => true
          ),
          'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
          )
        )
      );

      $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->applyFromArray(
        array(
          'font' => array(
            'bold' => true
          ),
          'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
          )
        )
      );

      $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->getAlignment()->setWrapText(true);

      // border
      $objPHPExcel->getActiveSheet()->getStyle('A7:U' . $row)->applyFromArray(
        array(
          'borders' => array(
            'allborders' => array(
              'style' => PHPExcel_Style_Border::BORDER_THIN,
            )
          )
        )
      );
      // var_dump($space);
      // die(var_dump($rowDAta3['tahun']));

      // fill tabel header
      $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
      $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->getFill()->getStartColor()->setRGB('E4E4E4');

      // format angka col I & K
      $objPHPExcel->getActiveSheet()->getStyle('E11:U' . $row)->getNumberFormat()->setFormatCode('#,##0');

      // // fill tabel footer
      // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
      // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->getStartColor()->setRGB('E4E4E4');



      // Rename sheet
      //$objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak '.$tab);

      $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
      $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("P")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("R")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("S")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("T")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("U")->setWidth(15);
      for ($x = "A"; $x <= "H"; $x++) {
        if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
        else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
      }
      if ($_REQUEST['CPM_JENIS_PJK'] == 1) {
        $objPHPExcel->getActiveSheet()->setTitle("Restoran");
        $objPHPExcel->createSheet();
      } elseif ($_REQUEST['CPM_JENIS_PJK'] == 2) {
        $objPHPExcel->getActiveSheet()->setTitle("Jasa Boga");
        $objPHPExcel->createSheet();
      } else {
        $objPHPExcel->getActiveSheet()->setTitle("$jp_id");
        $objPHPExcel->createSheet();
        $z++;
      }
    }
    // die(var_dump($_REQUEST['CPM_JENIS_PJK']));
    ob_clean();
    // Redirect output to a client’s web browser (Excel5)

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="rekap-tahunan-' . strtolower($JENIS_PAJAK) . '-' . $_REQUEST['CPM_TAHUN_PAJAK'] . '.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
    // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
    $objWriter->save('php://output');
    mysqli_close($this->Conn);
  }
  private function  download_bentang_panjang_teres()
  {

    $periode = '';
    $periode_bulan = '';
    $where = "(";
    $where .= ($this->_s == 4) ? " 1=1 " : " tr.CPM_TRAN_FLAG = '0' "; #jika status ditolak, maka flag tidak ditentukan
    $where2 = '';

    if ($this->_mod == "pel") { #pelaporan
      if ($this->_s == 0) { #semua data
        $where = "  ((tr.CPM_TRAN_FLAG = '0' AND tr.CPM_TRAN_STATUS in (1,2,3,4,5)) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ver") { #verifikasi
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "per") { #persetujuan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    } elseif ($this->_mod == "ply") { #pelayanan
      if ($this->_s == 0) { #semua data
        $where .= " AND tr.CPM_TRAN_STATUS in (1,2,3,4,5) OR (tr.CPM_TRAN_FLAG in ('0','1') AND tr.CPM_TRAN_STATUS='4') ";
      } elseif ($this->_s == 2) { #tab proses
        $where .= " AND tr.CPM_TRAN_STATUS in (2,3) ";
      } else {
        $where .= " AND tr.CPM_TRAN_STATUS = '{$this->_s}' ";
      }
    }
    $where .= ") ";
    //$where.= ($this->_mod == "pel") ? " AND pr.CPM_NPWPD like '{$_SESSION['npwpd']}%' " : "";
    $where .= (isset($_REQUEST['CPM_NPWPD']) && trim($_REQUEST['CPM_NPWPD']) != "") ? " AND CPM_NPWPD like \"{$_REQUEST['CPM_NPWPD']}%\" " : "";
    $where .= (isset($_REQUEST['CPM_REKENING']) && $_REQUEST['CPM_REKENING'] != "") ? " AND CPM_REKENING like \"{$_REQUEST['CPM_REKENING']}%\" " : "";
    $where .= (isset($_REQUEST['CPM_NAMA_WP']) && $_REQUEST['CPM_NAMA_WP'] != "") ? " AND CPM_NAMA_WP like \"%{$_REQUEST['CPM_NAMA_WP']}%\" " : "";

    //if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
    $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : " AND CPM_TAHUN_PAJAK = \"" . date('Y') . "\" ";
    //}

    $where .= (isset($_REQUEST['CPM_MASA_PAJAK']) && $_REQUEST['CPM_MASA_PAJAK'] != "") ? " AND MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) = \"" . str_pad($_REQUEST['CPM_MASA_PAJAK'], 2, "0", STR_PAD_LEFT) . "\" " : "";
    if (isset($_REQUEST['CPM_TGL_LAPOR1']) && $_REQUEST['CPM_TGL_LAPOR1'] != "") {
      $where .= " AND (STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\")>= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR1']}\",\" 00:00:00\") and 
                STR_TO_DATE(CPM_TGL_LAPOR,\"%d-%m-%Y\") <= CONCAT(\"{$_REQUEST['CPM_TGL_LAPOR2']}\",\" 23:59:59\") ) ";
      $periode = 'BULAN ' . $this->arr_bulan[date('n', strtotime($_REQUEST['CPM_TGL_LAPOR1']))];
      $periode_bulan = date('Y-m', strtotime($_REQUEST['CPM_TGL_LAPOR1']));
    }

    $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);


    $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';



    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    $jenis_pajaks = (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? "{$_REQUEST['CPM_JENIS_PJK']}" : "";
    $jenisPajak =  array(1 => "Restoran", 2 => "Jasa Boga");
    $z = 0;
    foreach ($jenisPajak as $jp => $jp_id) {
      if ($jenis_pajaks != $jp && $jenis_pajaks != '') {
        continue;
      }

      if ($jp == 2) {
        $no = 0;
      }

      if ($jp == 2) {
        $total_total_pajak = 0;
        $total_jan = 0;
        $total_feb = 0;
        $total_mar = 0;
        $total_apr = 0;
        $total_mei = 0;
        $total_jun = 0;
        $total_jul = 0;
        $total_agu = 0;
        $total_sep = 0;
        $total_okt = 0;
        $total_nov = 0;
        $total_des = 0;
      }

      //if(isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != ""){
      //$where .= " AND pj.CPM_TIPE_PAJAK={$_REQUEST['CPM_JENIS_PJK']}";    
      //if($_REQUEST['CPM_JENIS_PJK']==1)
      //	$where2 .= " AND pr.CPM_REKENING!='4.1.01.07.07'";    
      //elseif($_REQUEST['CPM_JENIS_PJK']==2)
      //	$where2 .= " AND pr.CPM_REKENING='4.1.01.07.07'";    
      //}

      $where3 = $this->where3_cetak_bentang();

      // var_dump($where3);
      // die;
      if ($this->_idp == '7') {
        $q_tipe_pajak = 'pj.CPM_TYPE_PAJAK';
      } else {
        $q_tipe_pajak = 'pj.CPM_TIPE_PAJAK';
      }

      //$query_wp = "select * from patda_wp where  CPM_STATUS = '1' && CPM_JENIS_PAJAK like '%{$this->_idp}%' ORDER BY CPM_KECAMATAN_WP ASC";
      //if($this->_idp == '8'){
      $query_wp = "SELECT wp.*,pr.CPM_NAMA_OP FROM patda_wp wp 
        INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' {$where2} 
        WHERE wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' ORDER BY wp.CPM_KECAMATAN_WP ASC";
      //}

      //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' {$where2} 
      #query select list data
      $query2 = "SELECT
        SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
        MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
        pr.CPM_NPWPD,
        pr.CPM_NAMA_WP,
        UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
        pr.CPM_ALAMAT_WP,
        pr.CPM_ALAMAT_OP,
                    pr.CPM_KECAMATAN_WP,
        pr.CPM_KECAMATAN_OP
      FROM
        PATDA_{$JENIS_PAJAK}_DOC pj
        INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  {$where2}
        INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
        WHERE {$where} AND {$q_tipe_pajak} = '{$jp}'
        GROUP BY CPM_BULAN, pr.CPM_NPWPD
        ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";


      //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1'
      // var_dump($query_wp);
      // exit();
      $data = array();
      $res = mysqli_query($this->Conn, $query2);
      while ($row = mysqli_fetch_assoc($res)) {

        $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
        $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
        $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
        $data[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
        $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
        $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
        $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
        $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
        $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);
        // var_dump($query2);
        // break;
      }
      // echo $data[$row['CPM_NPWPD']]['CPM_NPWPD'];
      //exit();
      $query3 = "SELECT
        SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
        MONTH(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
        pr.CPM_NPWPD,
        pr.CPM_NAMA_WP,
        UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
        pr.CPM_ALAMAT_WP,
        pr.CPM_ALAMAT_OP,
        pr.CPM_KECAMATAN_OP
            FROM
      PATDA_{$JENIS_PAJAK}_DOC pj
      INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1' {$where2}
                INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                WHERE {$where3} AND MONTH(STR_TO_DATE( pj.CPM_MASA_PAJAK1, '%d/%m/%Y' )) = 12 AND {$q_tipe_pajak} = '{$jp}'
                GROUP BY CPM_BULAN, pr.CPM_NPWPD
      ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";
      // var_dump($query3);
      // die;

      $data2 = array();
      $res2 = mysqli_query($this->Conn, $query3);
      // $jumlah_data;
      while ($row = mysqli_fetch_assoc($res2)) {
        $data2[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
        $data2[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
        $data2[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
        $data2[$row['CPM_NPWPD']]['CPM_PERUNTUKAN'] = $row['CPM_PERUNTUKAN'];
        $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
        $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
        $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
        $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
        //$data2[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['T_PAJAK'];
        $data2[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array(
          'CPM_VOLUME' => $row['CPM_VOLUME'],
          'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK'],
        );
      }



      $data_wp = array();

      $res_wp = mysqli_query($this->Conn, $query_wp);
      // echo "<pre>";

      while ($row = mysqli_fetch_assoc($res_wp)) {
        $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
        $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
        $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
        $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
      }

      // Set properties
      $objPHPExcel->getProperties()->setCreator("vpost")
        ->setLastModifiedBy("vpost")
        ->setTitle("9 PAJAK ONLINE")
        ->setSubject("-")
        ->setDescription("bphtb")
        ->setKeywords("9 PAJAK ONLINE");

      // Add some data
      $tahun_pajak_label = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? $_REQUEST['CPM_TAHUN_PAJAK'] : date('Y');
      $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "DES " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "DES " . (date('Y') - 1);

      $objPHPExcel->setActiveSheetIndex($z)
        ->setCellValue('A1', 'PEMERINTAH KABUPATEN LAMPUNG SELATAN')
        ->setCellValue('A2', 'REKAPITULASI SPTPD PAJAK ' . $JENIS_PAJAK)
        ->setCellValue('A3', 'BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH')
        ->setCellValue('A4', 'MASA PAJAK JANUARI s/d DESEMBER ' . $tahun_pajak_label . '')
        ->setCellValue('A6', 'BIDANG PENGEMBANGAN DAN PENETAPAN')
        ->setCellValue('A7', 'NO.')
        ->setCellValue('B7', 'NAMA WAJIB PAJAK.')
        ->setCellValue('C7', 'NILAI SPTPD PAJAK ' . $JENIS_PAJAK . ' TAHUN ' . $tahun_pajak_label . ' ')
        ->setCellValue('Q8', 'JUMLAH.')
        ->setCellValue('C8', 'TAPBOX.')
        ->setCellValue('D8', $tahun_pajak_label_sebelumnya)
        ->setCellValue('E8', 'JAN')
        ->setCellValue('F8', 'FEB')
        ->setCellValue('G8', 'MAR')
        ->setCellValue('H8', 'APRIL')
        ->setCellValue('I8', 'MEI')
        ->setCellValue('J8', 'JUNI')
        ->setCellValue('K8', 'JULI')
        ->setCellValue('L8', 'AGS')
        ->setCellValue('M8', 'SEPT')
        ->setCellValue('N8', 'OKT')
        ->setCellValue('O8', 'NOP')
        ->setCellValue('P8', 'DES');
      if ($JENIS_PAJAK == 'RESTORAN') {
        $objPHPExcel->setActiveSheetIndex($z)
          ->setCellValue('B7', 'NAMA WAJIB OP.');
      }

      // judul dok
      $objPHPExcel->getActiveSheet()->mergeCells("A1:R1");
      $objPHPExcel->getActiveSheet()->mergeCells("A2:R2");
      $objPHPExcel->getActiveSheet()->mergeCells("A3:R3");
      $objPHPExcel->getActiveSheet()->mergeCells("A4:R4");
      $objPHPExcel->getActiveSheet()->mergeCells("A6:R6");
      $objPHPExcel->getActiveSheet()->mergeCells("A7:A8");
      $objPHPExcel->getActiveSheet()->mergeCells("B7:B8");
      $objPHPExcel->getActiveSheet()->mergeCells("C7:Q7");


      // Miscellaneous glyphs, UTF-8
      $objPHPExcel->setActiveSheetIndex($z);

      $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
      $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
      $tab = $jns[$this->_s];
      $jml = 0;

      $row = 9;
      $sumRows = mysqli_num_rows($res);
      $total_pajak = 0;


      foreach ($data_wp as $npwpd => $rowDataWP) {
        $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
        // var_dump($cek_kecamatan);
        // //break;
        // die;
      }

      $jumlah_data = count($data_wp);
      // print_r($data) . '<br><br>';
      // die;


      foreach ($data_wp as $npwpd => $rowDataWP) {
        //print_r($data) . '<br>';
        // print_r($data[$rowDataWP['CPM_NPWPD']]);
        // die;
        $rowData = $data[$rowDataWP['CPM_NPWPD']];
        $rowData2 = $data2[$rowDataWP['CPM_NPWPD']];

        // print_r($rowData['bulan'][10]['CPM_TOTAL_PAJAK']) . '<br>';
        // print_r($rowDataWP['CPM_KECAMATAN_WP']) . '<br>';
        // print_r($cek_kecamatan);
        // die;


        if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
          $nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);

          $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':D' . $row);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");
          //  $objPHPExcel->getActiveSheet()->getStyle($clm . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_IDR_SIMPLE);
          $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $jan);
          $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $feb);
          $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $mar);
          $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $apr);
          $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $mei);
          $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $jun);
          $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $jul);
          $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $agu);
          $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $sep);
          $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $okt);
          $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $nov);
          $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $des);
          $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $total_pajak);

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );

          if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
            $space = $row + 1;
            $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':Q' . $space);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffffff');
            $row++;
          }

          $no = 0;
          $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
          $row++;
        }


        if ($rowDataWP['CPM_KECAMATAN_WP']) {

          if ($rowDataWP['CPM_KECAMATAN_WP'] != $s_kecamatan) {
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':Q' . $row);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->getStartColor()->setRGB('ffc000');

            $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->applyFromArray(
              array(
                'font' => array(
                  'bold' => true
                ),
              )
            );

            $s_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
            $row++;

            $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 2, 2);
          }
        }
        // var_dump($rowData['bulan']);
        // die;

        $nama_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
        // echo $nama_kecamatan;
        // exit();
        $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no + 1));
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_WP'], PHPExcel_Cell_DataType::TYPE_STRING);
        if ($JENIS_PAJAK == 'RESTORAN') {
          $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_OP'], PHPExcel_Cell_DataType::TYPE_STRING);
        }
        // var_dump($JENIS_PAJAK == 'RESTORAN');
        // die;
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, '');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);


        if ($nama_kecamatan != $nama_kecamatans) {
          $total_pajak = 0;
          $jan = 0;
          $feb = 0;
          $mar = 0;
          $apr = 0;
          $mei = 0;
          $jun = 0;
          $jul = 0;
          $agu = 0;
          $sep = 0;
          $okt = 0;
          $nov = 0;
          $des = 0;
        }


        $total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
        $jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
        $feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
        $mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
        $apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
        $mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
        $jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
        $jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
        $agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
        $sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
        $okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
        $nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
        $des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
        $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

        //untuk total
        $total_total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
        $total_jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
        $total_feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
        $total_mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
        $total_apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
        $total_mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
        $total_jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
        $total_jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
        $total_agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
        $total_sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
        $total_okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
        $total_nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
        $total_des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;

        //var_dump($total_pajak);die;

        $jml++;
        $row++;
        $no++;
        //var_dump($jumlah_data, $row);die;
        if ($jumlah_data == $jml) {
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':D' . $row);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");

          $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $jan);
          $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $feb);
          $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $mar);
          $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $apr);
          $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $mei);
          $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $jun);
          $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $jul);
          $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $agu);
          $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $sep);
          $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $okt);
          $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $nov);
          $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $des);
          $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $total_pajak);

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':Q' . $row)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );


          if ($jumlah_data == $jml) {
            //var_dump($row);die;
            $space = $row + 1;
            $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':D' . $space);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "Jumlah Pajak ");

            $objPHPExcel->getActiveSheet()->setCellValue('E' . $space, $total_jan);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $space, $total_feb);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $space, $total_mar);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $total_apr);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $space, $total_mei);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $space, $total_jun);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $space, $total_jul);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $space, $total_agu);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $space, $total_sep);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $space, $total_okt);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $space, $total_nov);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $space, $total_des);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $space, $total_total_pajak);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffc000');

            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->applyFromArray(
              array(
                'font' => array(
                  'bold' => true
                ),
              )
            );
          }


          if ($jumlah_data == $jml) {
            //var_dump($row);die;
            $space = $row + 3;
            $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "KETERANGAN ");
            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffff00');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->applyFromArray(
              array(
                'font' => array(
                  'bold' => true
                ),
              )
            );
          }

          //var_dump($space);die;
          $space = $space + 1;
          $no_keterangan = 0;
          $total_wp = 0;
          //$query_keterangan = "select CPM_KECAMATAN_WP, count(CPM_KECAMATAN_WP) as TOTAL from patda_wp where CPM_STATUS = '1' && CPM_JENIS_PAJAK like '%{$this->_idp}%' GROUP BY CPM_KECAMATAN_WP ORDER BY CPM_KECAMATAN_WP ASC";
          //if($this->_idp == '8'){
          $query_keterangan = "SELECT
                      wp.CPM_KECAMATAN_WP,
                      count( wp.CPM_KECAMATAN_WP ) AS TOTAL 
                    FROM
                      patda_wp wp
                      INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1' 
                      AND pr.CPM_ID = (SELECT MAX(CPM_ID) FROM PATDA_{$JENIS_PAJAK}_PROFIL pr WHERE CPM_AKTIF = 1 && CPM_NPWPD = wp.CPM_NPWPD {$where2})  {$where2}
                    WHERE
                      wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' 
                    GROUP BY
                      CPM_KECAMATAN_WP 
                    ORDER BY
                      CPM_KECAMATAN_WP ASC";
          //}
          //var_dump($query_keterangan);die;

          $res_keterangan = mysqli_query($this->Conn, $query_keterangan);
          while ($row_keterangan = mysqli_fetch_array($res_keterangan)) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, 'JUMLAH WP KECAMATAN ' . $row_keterangan['CPM_KECAMATAN_WP']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $row_keterangan['TOTAL']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffff00');
            $space++;
            $no_keterangan++;
            $total_wp += $row_keterangan['TOTAL'];
          }
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':B' . $space);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, 'Jumlah :');
          $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $total_wp);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':C' . $space)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );
        }
      }




      /** style **/
      // judul dok + judul tabel
      $objPHPExcel->getActiveSheet()->getStyle('A1:Q4')->applyFromArray(
        array(
          'font' => array(
            'bold' => true
          ),
          'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
          )
        )
      );

      $objPHPExcel->getActiveSheet()->getStyle('A7:Q8')->applyFromArray(
        array(
          'font' => array(
            'bold' => true
          ),
          'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
          )
        )
      );

      $objPHPExcel->getActiveSheet()->getStyle('A5:Q7')->getAlignment()->setWrapText(true);

      // border
      $objPHPExcel->getActiveSheet()->getStyle('A7:Q' . $row)->applyFromArray(
        array(
          'borders' => array(
            'allborders' => array(
              'style' => PHPExcel_Style_Border::BORDER_THIN
            )
          )
        )
      );


      // fill tabel header
      $objPHPExcel->getActiveSheet()->getStyle('A7:Q8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
      $objPHPExcel->getActiveSheet()->getStyle('A7:Q8')->getFill()->getStartColor()->setRGB('E4E4E4');

      // format angka col I & K
      $objPHPExcel->getActiveSheet()->getStyle('E8:Q' . $row)->getNumberFormat()->setFormatCode('#,##0');

      // // fill tabel footer
      // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
      // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->getStartColor()->setRGB('E4E4E4');



      // Rename sheet
      //$objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak '.$tab);

      $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("P")->setWidth(15);
      $objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(15);
      for ($x = "A"; $x <= "H"; $x++) {
        if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
        else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
      }

      if ($_REQUEST['CPM_JENIS_PJK'] == 1) {
        $objPHPExcel->getActiveSheet()->setTitle("Reguler");
        $objPHPExcel->createSheet();
      } elseif ($_REQUEST['CPM_JENIS_PJK'] == 2) {
        $objPHPExcel->getActiveSheet()->setTitle("Non Reguler");
        $objPHPExcel->createSheet();
      } else {
        $objPHPExcel->getActiveSheet()->setTitle("$jp_id");
        $objPHPExcel->createSheet();
        $z++;
      }
    }
    ob_clean();
    // Redirect output to a client’s web browser (Excel5)

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="rekap-tahunan-' . strtolower($JENIS_PAJAK) . '-' . $_REQUEST['CPM_TAHUN_PAJAK'] . '.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
    // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
    $objWriter->save('php://output');
    mysqli_close($this->Conn);
  }

  //JAlan
  public function download_bentang_panjang_jalan()
  {


    $JENIS_PAJAK = strtoupper($this->arr_idpajak[$this->_idp]);
    $JENIS_LAPOR = ($this->_idp == 1 || $this->_idp == 7) ? '(OFFICIAL)' : '(SELF ASSESMEN)';

    switch (TRUE) {
      case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
        $tahun_pajak_label = date('Y');
        break;
      case ($_REQUEST['CPM_TAHUN_PAJAK'] != "" && $_REQUEST['CPM_TGL_LAPOR1'] == ""):
        $tahun_pajak_label = $_REQUEST['CPM_TAHUN_PAJAK'];
        break;
      case ($_REQUEST['CPM_TAHUN_PAJAK'] == "" && $_REQUEST['CPM_TGL_LAPOR1'] != ""):

        $tahun_pajak_label =  date("Y", strtotime($_REQUEST['CPM_TGL_LAPOR1']));
        break;
      default:
        $tahun_pajak_label = date('Y');
        break;
    }

    $where = $this->where_cetak_bentang_tahun();

    $objPHPExcel = new PHPExcel();

    $jenis_pajaks = (isset($_REQUEST['CPM_JENIS_PJK']) && $_REQUEST['CPM_JENIS_PJK'] != "") ? "{$_REQUEST['CPM_JENIS_PJK']}" : "";
    //$jenisPajak = array(1 => "Restoran", 2 => "Jasa Boga");
    $z = 0;
    $total_total_pajak = 0;
    $total_tahun1 = 0;
    $total_tahun2 = 0;
    $total_tahun3 = 0;
    $total_tahun4 = 0;
    $total_tahun5 = 0;
    $total_jan = 0;
    $total_feb = 0;
    $total_mar = 0;
    $total_apr = 0;
    $total_mei = 0;
    $total_jun = 0;
    $total_jul = 0;
    $total_agu = 0;
    $total_sep = 0;
    $total_okt = 0;
    $total_nov = 0;
    $total_des = 0;
    $where5 = $this->where3_cetak_bentang_tahun();

    if ($this->_idp == '7') {
      $q_tipe_pajak = 'pj.CPM_TYPE_PAJAK';
    } else {
      $q_tipe_pajak = 'pj.CPM_TIPE_PAJAK';
    }


    $query_wp = "SELECT CPM_NAMA_OP,wp.* FROM patda_wp wp 
          INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON wp.CPM_NPWPD = pr.CPM_NPWPD AND pr.CPM_AKTIF = '1'
          WHERE wp.CPM_STATUS = '1' && wp.CPM_JENIS_PAJAK LIKE '%{$this->_idp}%' AND wp.`CPM_KECAMATAN_WP` !='' ORDER BY wp.CPM_KECAMATAN_WP ASC";

    $query2 = "SELECT
          SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
          YEAR(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) as CPM_YEAR,
          MONTH(STR_TO_DATE(CPM_TGL_LAPOR,'%d-%m-%Y')) as CPM_BULAN,
          pr.CPM_NPWPD,
          pr.CPM_NAMA_WP,
          UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
          pr.CPM_ALAMAT_WP,
          pr.CPM_ALAMAT_OP,
          pr.CPM_KECAMATAN_WP,
          pr.CPM_KECAMATAN_OP
        FROM
          PATDA_{$JENIS_PAJAK}_DOC pj
          INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL 
          INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
          WHERE {$where} 
          GROUP BY CPM_BULAN, pr.CPM_NPWPD
          ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";

    //INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1'

    $data = array();
    $res = mysqli_query($this->Conn, $query2);
    while ($row = mysqli_fetch_assoc($res)) {

      $data[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
      $data[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
      $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      $data[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
      $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);
    }
    // var_dump($data);
    // break;
    $query3 = "SELECT
          SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
          MONTH(STR_TO_DATE(CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_BULAN,
          YEAR(STR_TO_DATE(pj.CPM_TGL_LAPOR,'%d-%m-%Y')) as CPM_TAHUN,
          pr.CPM_NPWPD,
          pr.CPM_NAMA_WP,
          UPPER(pr.CPM_NAMA_OP) AS CPM_NAMA_OP,
          pr.CPM_ALAMAT_WP,
          pr.CPM_ALAMAT_OP,
          pr.CPM_KECAMATAN_OP
              FROM
        PATDA_{$JENIS_PAJAK}_DOC pj
        INNER JOIN PATDA_{$JENIS_PAJAK}_PROFIL pr ON pr.CPM_ID = pj.CPM_ID_PROFIL  AND pr.CPM_AKTIF = '1'
                  INNER JOIN PATDA_{$JENIS_PAJAK}_DOC_TRANMAIN tr ON pj.CPM_ID = tr.CPM_TRAN_{$JENIS_PAJAK}_ID 
                  WHERE {$where5} AND YEAR(STR_TO_DATE( CPM_TGL_LAPOR, '%d-%m-%Y' )) <='{$tahun_pajak_label}' 
                  GROUP BY CPM_TAHUN, pr.CPM_NPWPD
        ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";

    $data2 = array();
    $res2 = mysqli_query($this->Conn, $query3);
    // $jumlah_data;
    while ($row = mysqli_fetch_assoc($res2)) {
      $data2[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data2[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data2[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_WP'] = $row['CPM_ALAMAT_WP'];
      $data2[$row['CPM_NPWPD']]['CPM_ALAMAT_OP'] = $row['CPM_ALAMAT_OP'];
      $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      $data2[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      //$data2[$row['CPM_NPWPD']]['CPM_TIPE_PAJAK'] = $row['T_PAJAK'];
      $data2[$row['CPM_NPWPD']]['tahun'][$row['CPM_TAHUN']] = array(

        'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK'],
      );
    }



    $data_wp = array();


    $res_wp = mysqli_query($this->Conn, $query_wp);
    // echo "<pre>";

    while ($row = mysqli_fetch_assoc($res_wp)) {
      $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      // $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_OP'] = $row['CPM_KECAMATAN_OP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
    }

    // Set properties
    $objPHPExcel->getProperties()->setCreator("vpost")
      ->setLastModifiedBy("vpost")
      ->setTitle("9 PAJAK ONLINE")
      ->setSubject("-")
      ->setDescription("bphtb")
      ->setKeywords("9 PAJAK ONLINE");

    // Add some data
    // $tahun_pajak_label = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? $_REQUEST['CPM_TAHUN_PAJAK'] : date('Y');
    // $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "DES " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "DES " . (date('Y') - 1);


    $objPHPExcel->setActiveSheetIndex($z)
      ->setCellValue('A1', 'PEMERINTAH KABUPATEN LAMPUNG SELATAN')
      ->setCellValue('A2', 'REKAPITULASI SPTPD PAJAK ' . $JENIS_PAJAK)
      ->setCellValue('A3', 'BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH')
      ->setCellValue('A4', 'MASA PAJAK JANUARI s/d DESEMBER ' . $tahun_pajak_label . '')
      ->setCellValue('A5', 'BIDANG PENGEMBANGAN DAN PENETAPAN')
      ->setCellValue('A6', '')
      ->setCellValue('A7', 'NO.')
      ->setCellValue('B7', 'NAMA OBJEK PAJAK.')
      ->setCellValue('C7', 'NILAI SPTPD PAJAK ' . $JENIS_PAJAK)
      ->setCellValue('C8',  'TAHUN ')
      ->setCellValue('C9', 'TAPBOX.')
      ->setCellValue('I10', 'JAN')
      ->setCellValue('J10', 'FEB')
      ->setCellValue('K10', 'MAR')
      ->setCellValue('L10', 'APRIL')
      ->setCellValue('M10', 'MEI')
      ->setCellValue('N10', 'JUNI')
      ->setCellValue('O10', 'JULI')
      ->setCellValue('P10', 'AGS')
      ->setCellValue('Q10', 'SEPT')
      ->setCellValue('R10', 'OKT')
      ->setCellValue('S10', 'NOP')
      ->setCellValue('T10', 'DES')
      ->setCellValue('U7', 'JUMLAH');


    // 1 => "AIR BAWAH TANAH",
    // 2 => "HIBURAN",3 => "HOTEL",5 => "PARKIR", 6 => "PENERANGAN JALAN", 7 => "REKLAME", 8 => "RESTORAN",9 => "SARANG WALET"
    // 4 => "MINERAL BUKAN LOGAM DAN BATUAN ",
    if ($JENIS_PAJAK == 'JALAN') {
      for ($i = 0; $i < 6; $i++) {
        $bar = 9;
        $column = PHPExcel_Cell::columnIndexFromString('H') - $i; // hitung kolom baru
        $column_letter = PHPExcel_Cell::stringFromColumnIndex($column); // konversi angka kolom ke huruf
        $cell = $column_letter . $bar; // gabungkan huruf kolom dan nomor baris untuk membentuk string sel
        //   echo $cell;

        $year = $tahun_pajak_label - $i;
        $objPHPExcel->setActiveSheetIndex($z)
          ->setCellValue($cell, $year);
      }
    }
    // judul dok
    $objPHPExcel->getActiveSheet()->mergeCells("A1:R1");
    $objPHPExcel->getActiveSheet()->mergeCells("A2:R2");
    $objPHPExcel->getActiveSheet()->mergeCells("A3:R3");
    $objPHPExcel->getActiveSheet()->mergeCells("A4:R4");
    $objPHPExcel->getActiveSheet()->mergeCells("A5:R5");
    $objPHPExcel->getActiveSheet()->mergeCells("A7:A10");
    $objPHPExcel->getActiveSheet()->mergeCells("B7:B10");
    $objPHPExcel->getActiveSheet()->mergeCells("C9:C10");
    $objPHPExcel->getActiveSheet()->mergeCells("C7:T7");
    $objPHPExcel->getActiveSheet()->mergeCells("C8:T8");
    $objPHPExcel->getActiveSheet()->mergeCells("I9:T9");
    $objPHPExcel->getActiveSheet()->mergeCells("U7:U10");

    // Miscellaneous glyphs, UTF-8
    $objPHPExcel->setActiveSheetIndex($z);

    $jns = array(1 => 'Draft', 'Proses', 'Ditolak', 'Disetujui', 'Semua');
    $triwulan = array(1 => 'Triwulan I', 4 => 'Triwulan II', 7 => 'Triwulan III', 10 => 'Triwulan IV');
    $tab = $jns[$this->_s];
    $jml = 0;

    $row = 11;
    $sumRows = mysqli_num_rows($res);
    $total_pajak = 0;
    $jan1 = 0;
    $feb1 = 0;
    $mar1 = 0;
    $apr1 = 0;
    $mei1 = 0;
    $jun1 = 0;
    $jul1 = 0;
    $agu1 = 0;
    $sep1 = 0;
    $okt1 = 0;
    $nov1 = 0;
    $des1 = 0;

    foreach ($data_wp as $npwpd => $rowDataWP) {
      $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
    }

    //Menghitung jumlah orang setiap kecamatan
    $jumlah_orang_kecamatan = array();

    foreach ($data_wp as $npwpd => $rawDataWP) {
      $kecamatan = $rawDataWP['CPM_KECAMATAN_WP'];

      if (isset($jumlah_orang_kecamatan[$kecamatan])) {
        $jumlah_orang_kecamatan[$kecamatan]++;
      } else {
        $jumlah_orang_kecamatan[$kecamatan] = 1;
      }
    }


    $jumlah_data = count($data_wp);

    foreach ($data_wp as $npwpd => $rowDataWP) {

      $rowData = $data[$rowDataWP['CPM_NPWPD']];
      $rowDAta3 = $data2[$rowDataWP['CPM_NPWPD']];

      if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
        $nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);
        $jan1 += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
        $feb1 += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
        $mar1 += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
        $apr1 += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
        $mei1 += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
        $jun1 += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
        $jul1 += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
        $agu1 += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
        $sep1 += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
        $okt1 += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
        $nov1 += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
        $des1 += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;

        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah ");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row,  $tahun1);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row,  $tahun2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row,  $tahun3);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row,  $tahun4);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row,  $tahun5);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $jan1);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $feb1);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $mar1);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $apr1);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $mei1);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $jun1);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $jul1);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $agu1);
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $sep1);
        $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $okt1);
        $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $nov1);
        $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $des1);
        $objPHPExcel->getActiveSheet()->setCellValue('U' . $row,  $total_pajak);

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
          array(
            'font' => array(
              'bold' => true
            ),
          )
        );

        if ($rowDataWP['CPM_KECAMATAN_WP'] != $cek_kecamatan) {
          $space = $row + 1;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':U' . $space);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->getStartColor()->setRGB('ffffff');
          $row++;
        }

        $no = 0;
        $cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
        $row++;
      }

      if ($rowDataWP['CPM_KECAMATAN_WP']) {

        if ($rowDataWP['CPM_KECAMATAN_WP'] != $s_kecamatan) {
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':U' . $row);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "KECAMATAN " . $rowDataWP['CPM_KECAMATAN_WP']);

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );

          $s_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
          $row++;

          $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 2, 2);
        }
      }
      $nama_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];

      $rowData['CPM_NPWPD'] = Pajak::formatNPWPD($rowData['CPM_NPWPD']);
      $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($no + 1));
      $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_WP'], PHPExcel_Cell_DataType::TYPE_STRING);

      if ($JENIS_PAJAK == 'RESTORAN') {
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row,  $rowDataWP['CPM_NAMA_OP'], PHPExcel_Cell_DataType::TYPE_STRING);
      }


      $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, '');
      $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowDAta3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowDAta3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowDAta3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowDAta3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowDAta3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0);
      // $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0);
      $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, 'aku tak tahu' . $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);


      if ($nama_kecamatan != $nama_kecamatans) {
        $total_pajak = 0;
        $tahun1 = 0;
        $tahun2 = 0;
        $tahun3 = 0;
        $tahun4 = 0;
        $tahun5 = 0;
        $jan = 0;
        $feb = 0;
        $mar = 0;
        $apr = 0;
        $mei = 0;
        $jun = 0;
        $jul = 0;
        $agu = 0;
        $sep = 0;
        $okt = 0;
        $nov = 0;
        $des = 0;
      }


      $total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
      $tahun1 += $rowDAta3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0;
      $tahun2 += $rowDAta3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0;
      $tahun3 += $rowDAta3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0;
      $tahun4 += $rowDAta3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0;
      $tahun5 += $rowDAta3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0;
      $jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
      $feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
      $mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
      $apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
      $mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
      $jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
      $jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
      $agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
      $sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
      $okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
      $nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
      $des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

      //untuk total
      $total_total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
      $tahunTotal1 += $rowDAta3['tahun'][$year]['CPM_TAHUN_TOTAL'] + 0;
      $tahunTotal2 += $rowDAta3['tahun'][$year + 1]['CPM_TAHUN_TOTAL'] + 0;
      $tahunTotal3 += $rowDAta3['tahun'][$year + 2]['CPM_TAHUN_TOTAL'] + 0;
      $tahunTotal4 += $rowDAta3['tahun'][$year + 3]['CPM_TAHUN_TOTAL'] + 0;
      $tahunTotal5 += $rowDAta3['tahun'][$year + 4]['CPM_TAHUN_TOTAL'] + 0;
      $total_jan += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + 0;
      $total_feb += $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + 0;
      $total_mar += $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
      $total_apr += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + 0;
      $total_mei += $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + 0;
      $total_jun += $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
      $total_jul += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + 0;
      $total_agu += $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + 0;
      $total_sep += $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
      $total_okt += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + 0;
      $total_nov += $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + 0;
      $total_des += $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;



      $jml++;
      $row++;
      $no++;

      if ($jumlah_data == $jml) {

        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, "Jumlah");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $tahun1);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tahun2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $tahun3);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $tahun4);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $tahun5);

        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $jan);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $feb);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $mar);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $apr);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $mei);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $jun);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $jul);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $agu);
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $sep);
        $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $okt);
        $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $nov);
        $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $des);
        $objPHPExcel->getActiveSheet()->setCellValue('U' . $row,  $total_pajak);


        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->getFill()->getStartColor()->setRGB('ffc000');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':U' . $row)->applyFromArray(
          array(
            'font' => array(
              'bold' => true
            ),
          )
        );


        // var_dump($row);
        // die;
        if ($jumlah_data == $jml) {
          //var_dump($row);die;
          $space = $row + 1;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "Jumlah Pajak ");
          $objPHPExcel->getActiveSheet()->setCellValue('D' . $space, $tahunTotal1);
          $objPHPExcel->getActiveSheet()->setCellValue('E' . $space, $tahunTotal2);
          $objPHPExcel->getActiveSheet()->setCellValue('F' . $space, $tahunTotal3);
          $objPHPExcel->getActiveSheet()->setCellValue('G' . $space, $tahunTotal4);
          $objPHPExcel->getActiveSheet()->setCellValue('H' . $space, $tahunTotal5);

          $objPHPExcel->getActiveSheet()->setCellValue('I' . $space, $total_jan);
          $objPHPExcel->getActiveSheet()->setCellValue('J' . $space, $total_feb);
          $objPHPExcel->getActiveSheet()->setCellValue('K' . $space, $total_mar);
          $objPHPExcel->getActiveSheet()->setCellValue('L' . $space, $total_apr);
          $objPHPExcel->getActiveSheet()->setCellValue('M' . $space, $total_mei);
          $objPHPExcel->getActiveSheet()->setCellValue('N' . $space, $total_jun);
          $objPHPExcel->getActiveSheet()->setCellValue('O' . $space, $total_jul);
          $objPHPExcel->getActiveSheet()->setCellValue('P' . $space, $total_agu);
          $objPHPExcel->getActiveSheet()->setCellValue('Q' . $space, $total_sep);
          $objPHPExcel->getActiveSheet()->setCellValue('R' . $space, $total_okt);
          $objPHPExcel->getActiveSheet()->setCellValue('S' . $space, $total_nov);
          $objPHPExcel->getActiveSheet()->setCellValue('T' . $space, $total_des);
          $objPHPExcel->getActiveSheet()->setCellValue('U' . $space, $total_total_pajak);

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->getStartColor()->setRGB('ffc000');

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );
        }


        if ($jumlah_data == $jml) {
          //var_dump($row);die;
          $space = $row + 3;
          $objPHPExcel->getActiveSheet()->insertNewRowBefore($space, 1);
          $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':C' . $space);
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, "KETERANGAN ");
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->getFill()->getStartColor()->setRGB('ffff00');
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':U' . $space)->applyFromArray(
            array(
              'font' => array(
                'bold' => true
              ),
            )
          );
        }

        //var_dump($space);die;
        $space = $space + 1;
        $no_keterangan = 0;
        $total_wp = 0;

        // Menampilkan hasil jumlah orang dalam setiap kecamatan
        foreach ($jumlah_orang_kecamatan as $kecamatan => $jumlah) {
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
          $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, 'JUMLAH WP KECAMATAN ' . ($kecamatan == '' ? "LAIN " : $kecamatan));
          $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $jumlah);
          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':Q' . $space)->getFill()->getStartColor()->setRGB('ffff00');
          $space++;
          $no_keterangan++;
          $total_wp += $jumlah;
        }
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $space . ':B' . $space);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, 'Jumlah :');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $total_wp);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':C' . $space)->applyFromArray(
          array(
            'font' => array(
              'bold' => true
            ),
          )
        );
      }
    }




    /** style **/
    // judul dok + judul tabel
    $objPHPExcel->getActiveSheet()->getStyle('A1:V5')->applyFromArray(
      array(
        'font' => array(
          'bold' => true
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->applyFromArray(
      array(
        'font' => array(
          'bold' => true
        ),
        'alignment' => array(
          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
      )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->getAlignment()->setWrapText(true);

    // border
    $objPHPExcel->getActiveSheet()->getStyle('A7:U' . $row)->applyFromArray(
      array(
        'borders' => array(
          'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
          )
        )
      )
    );
    // var_dump($space);
    // die(var_dump($rowDAta3['tahun']));

    // fill tabel header
    $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A7:U10')->getFill()->getStartColor()->setRGB('E4E4E4');

    // format angka col I & K
    $objPHPExcel->getActiveSheet()->getStyle('E11:U' . $row)->getNumberFormat()->setFormatCode('#,##0');

    // // fill tabel footer
    // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    // $objPHPExcel->getActiveSheet()->getStyle("A{$row}:S{$row}")->getFill()->getStartColor()->setRGB('E4E4E4');



    // Rename sheet
    //$objPHPExcel->getActiveSheet()->setTitle('Daftar Pajak '.$tab);

    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("P")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("R")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("S")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("T")->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension("U")->setWidth(15);
    for ($x = "A"; $x <= "H"; $x++) {
      if ($x == 'A') $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setWidth(5);
      else $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
    }


    // die(var_dump($_REQUEST['CPM_JENIS_PJK']));
    ob_clean();
    // Redirect output to a client’s web browser (Excel5)

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="rekap-tahunan-' . strtolower($JENIS_PAJAK) . '-' . $_REQUEST['CPM_TAHUN_PAJAK'] . '.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Output XLS
    // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // Output Browser (HTML)
    $objWriter->save('php://output');
    mysqli_close($this->Conn);
  }
}
