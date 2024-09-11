<?php
// if (session_id() == '') {
//   session_start();
// }
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set("log_errors", 1);
// ini_set("error_log", "/tmp/patda-base-v2-error.log");
class Cetak extends Pajak
{
  function download_bentang_panjang_abt1()
  {
    $this->download_pajak_xls_pat_tahunan1();
  }
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

    if ($_REQUEST['CPM_TAHUN_PAJAK'] != "") {
      $where .= (isset($_REQUEST['CPM_TAHUN_PAJAK']) && $_REQUEST['CPM_TAHUN_PAJAK'] != "") ? " AND CPM_TAHUN_PAJAK = \"{$_REQUEST['CPM_TAHUN_PAJAK']}\" " : " AND CPM_TAHUN_PAJAK = \"" . date('Y') . "\" ";
    }

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
                    YEAR(STR_TO_DATE(pj.CPM_MASA_PAJAK1,'%d/%m/%Y')) as CPM_YEAR,
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
            WHERE {$where}
            GROUP BY CPM_BULAN, pr.CPM_NPWPD,CPM_YEAR
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
      $data[$row['CPM_NPWPD']]['bulan'][$row['CPM_BULAN']] = array('CPM_VOLUME' => $row['CPM_VOLUME'], 'CPM_TOTAL_PAJAK' => $row['CPM_TOTAL_PAJAK']);
    }



    $where3 = $this->where3_cetak_bentang_abt();
    $where4 = $this->where3_cetak_bentang();

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
                WHERE {$where4}
                GROUP BY CPM_BULAN, pr.CPM_NPWPD
          ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP";

    // die(var_dump($query3));
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

    $query4 = "SELECT
                    SUM(pj.CPM_TOTAL_PAJAK) as CPM_TOTAL_PAJAK,
                    CPM_TAHUN_PAJAK,
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
                WHERE {$where4}
                GROUP BY pr.CPM_NPWPD,CPM_TAHUN_PAJAK
                ORDER BY pr.CPM_KECAMATAN_OP,pr.CPM_NAMA_OP,CPM_TAHUN_PAJAK";

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
      $data3[$row['CPM_NPWPD']]['tahun'][$row['CPM_TAHUN_PAJAK']] = array(
        'CPM_VOLUME' => $row['CPM_VOLUME'],
        'CPM_TAHUN_TOTAL' => $row['CPM_TOTAL_PAJAK'],
      );
    }


    $data_wp = array();
    $res_wp = mysqli_query($this->Conn, $query_wp);
    // echo "<pre>";
    //die(var_dump($query_wp));
    //  $rows = [];
    while ($row = mysqli_fetch_assoc($res_wp)) {


      $data_wp[$row['CPM_NPWPD']]['CPM_NPWPD'] = $row['CPM_NPWPD'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_WP'] = $row['CPM_NAMA_WP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_NAMA_OP'] = $row['CPM_NAMA_OP'];
      $data_wp[$row['CPM_NPWPD']]['CPM_KECAMATAN_WP'] = $row['CPM_KECAMATAN_WP'];
    }
    //var_dump($data_wp);
    //var_dump($data);

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set properties
    $objPHPExcel->getProperties()->setCreator("vpost")
      ->setLastModifiedBy("vpost")
      ->setTitle("9 PAJAK ONLINE")
      ->setSubject("-")
      ->setDescription("bphtb")
      ->setKeywords("9 PAJAK ONLINE");

    // Add some data
    // $tahun_pajak_label = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? date('Y') : "Tahun Belum di Pilih" ;
    // $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? date('Y')-1 : "Tahun Belum di Pilih";
    $tahun_pajak_label = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? $_REQUEST['CPM_TAHUN_PAJAK'] : date('Y');
    $tahun_pajak_label_sebelumnya = ($_REQUEST['CPM_TAHUN_PAJAK'] != "") ? "Triwulan IV " . ($_REQUEST['CPM_TAHUN_PAJAK'] - 1) : "Triwulan IV " . (date('Y') - 1);
    // var_dump($tahun_pajak_label_sebelumnya);exit;
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
      ->setCellValue('A1', 'PEMERINTAH KABUPATEN LAMPUNG SELATANasdasdsa')
      ->setCellValue('A2', 'REKAPITULASI ' . $SPTPD . '  ' . $pajakk)

      ->setCellValue('A3', 'BADAN PENGELOLA PAJAK DAN RETRIBUSI DAERAH')
      ->setCellValue('A4', 'MASA PAJAK TRIWULAN I s/d TRIWULAN IV ' . $tahun_pajak_label . '')
      ->setCellValue('A5', 'BIDANG PENGEMBANGAN DAN PENETAPAN')

      ->setCellValue('A7', 'NO.')
      ->setCellValue('B7', 'NAMA OBJEK PAJAK.')
      // ->setCellValue('C7', 'NAMA WAJIB PAJAK.')
      ->setCellValue('D7', 'PAJAK ' . $JENIS_PAJAK . ' TAHUN ')
      ->setCellValue('C8', $tahun_pajak_label)
      ->setCellValue('C7', 'TAPBOX.')
      //->setCellValue('D8', $tahun_pajak_label_sebelumnya)
      // ->setCellValue('E8', $tahun_pajak_label_sebelumnya)
      ->setCellValue('I9', 'TRIWULAN I')
      ->setCellValue('J9', 'TRIWULAN II')
      ->setCellValue('K9', 'TRIWULAN III')
      ->setCellValue('L9', 'TRIWULAN IV')
      ->setCellValue('M7', 'JUMLAH.');

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
      break;
    }
    // die(var_dump($data3));

    $jumlah_data = count($data_wp);
    // echo $cek_kecamatan;exit();

    foreach ($data_wp as $npwpd => $rowDataWP) {
      $rowData = $data[$rowDataWP['CPM_NPWPD']];
      $rowData2 = $data2[$rowDataWP['CPM_NPWPD']];
      $rowData3 = $data3[$rowDataWP['CPM_NPWPD']];
      // var_dump($rowData);
      // var_dump($rowData2);
      //var_dump($year + 1);
      // var_dump($rowData3['tahun'][$year + 1]);
      // die;
      //var_dump($data[$rowDataWP['CPM_NPWPD']]);

      //die;
      // var_dump($rowData['CPM_NPWPD']);die;
      //$nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);
      //$nama_kecamatan = $cek_kecamatan;

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
        //$nama_kecamatan = $this->get_nama_kecamatan($cek_kecamatan);
        //$nama_kecamatan = $cek_kecamatan;
        //echo $nama_kecamatan;exit;
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
          //$cek_kecamatan = $rowDataWP['CPM_KECAMATAN_WP'];
          $row++;


          $objPHPExcel->getActiveSheet()->insertNewRowBefore($row + 2, 2);
          //var_dump($row);die;
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
      $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK']);


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

      $total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];
      $triwulan_satu += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + 0;
      $triwulan_dua += $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + 0;
      $triwulan_tiga += $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + 0;
      $triwulan_empat += $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      //$desbelum += $rowData2['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData2['bulan'][12]['CPM_TOTAL_PAJAK'] + 0;
      $nama_kecamatans = $rowDataWP['CPM_KECAMATAN_WP'];

      //untuk total
      $total_total_pajak += $rowData['bulan'][1]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][2]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][3]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][4]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][5]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][6]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][7]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][8]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][9]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][10]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][11]['CPM_TOTAL_PAJAK'] + $rowData['bulan'][12]['CPM_TOTAL_PAJAK'];

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
        $query_keterangan = "select CPM_KECAMATAN_WP, count(CPM_KECAMATAN_WP) as TOTAL from patda_wp where CPM_JENIS_PAJAK like '%{$this->_idp}%'  GROUP BY CPM_KECAMATAN_WP  ORDER BY CPM_KECAMATAN_WP ASC";

        $res_keterangan = mysqli_query($this->Conn, $query_keterangan);
        while ($row_keterangan = mysqli_fetch_array($res_keterangan)) {
          $objPHPExcel->getActiveSheet()->setCellValue('A' . $space, $no_keterangan + 1);
          $objPHPExcel->getActiveSheet()->setCellValue('B' . $space, $row_keterangan['CPM_KECAMATAN_WP']);
          $objPHPExcel->getActiveSheet()->setCellValue('C' . $space, $row_keterangan['TOTAL']);
          $totalwp += $row_keterangan['TOTAL'] + 0;

          $objPHPExcel->getActiveSheet()->getStyle('A' . $space . ':M' . $space)->getFill()->getStartColor()->setRGB('ffff00');
          $space++;
          $no_keterangan++;

          // CODINGAN UNTUK MENAMPILKAN TOTAL WP DI CETAK BENTANG PANJANG

          if ($no_keterangan == mysqli_num_rows($res_keterangan)) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($space), "TOTAL");
            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($space), $totalwp);
          }
        }
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
}
