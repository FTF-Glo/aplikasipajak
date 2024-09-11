<?php

/**
 * Class Grafik Pencapaian Pajak
 */
class GrafikPencapaian extends Pajak
{

	public function get_target_pajak($returnQuery = false, $tahun = '2022')
	{
		$query = "
			SELECT
				SUM( A.patda_total_bayar ) AS TOTAL_PENDAPATAN,
				C.CPM_JUMLAH AS TARGET,
				B.id_sw AS ID_JENIS_PAJAK,
				B.jenis_sw AS JENIS_PAJAK,
				YEAR ( A.payment_paid ) AS TAHUN 
			FROM
				SIMPATDA_GW A
				INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id
				INNER JOIN PATDA_TARGET_PAJAK C ON B.id_sw = C.CPM_JENIS_PAJAK 
				AND C.CPM_TAHUN = $tahun
				AND C.CPM_AKTIF = '1' 
			WHERE
				YEAR ( A.payment_paid ) = $tahun
				AND A.payment_flag = '1' 
			GROUP BY
				A.simpatda_type;
		";

		$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

		if ($returnQuery) {
			return $result;
		}

		$arr_res = array();
		while ($row = mysqli_fetch_assoc($result)) {
			$arr_res['jenis_pajak'][] = $row['JENIS_PAJAK'];
			$arr_res['pendapatan'][] = $row['TOTAL_PENDAPATAN'];
			$arr_res['target'][] = $row['TARGET'];
			$persen = ($row['TOTAL_PENDAPATAN'] <= 0 || (float)$row['TARGET'] == 0) ? 0 : (float)$row['TOTAL_PENDAPATAN'] / (float)$row['TARGET'] * 100;
			$persen = (float)number_format($persen, 1);
			$arr_res['persentase'][] = $persen;

			$arr_res['grafik'][] = array(
				'name' => $row['JENIS_PAJAK'],
				'data' => array($persen),
				'tooltip' => array(
					'valueSuffix' => ' % [Rp.' . number_format($row['TOTAL_PENDAPATAN'], 2) . ']'
				)
			);
		}
		if (count($arr_res) == 0) {
			$jenispajak = ["Air Bawah Tanah", "Hiburan", "Hotel", "Parkir", "Penerangan Jalan", "Reklame", "Restoran", "Sarang Burung Walet"];
			$grafik = [];
			foreach ($jenispajak as $v) {
				$obj = (object)[];
				$obj->name = $v;
				$obj->data = [1];
				$obj->tooltip = (object)[];
				$obj->tooltip->valueSuffix = '';
				array_push($grafik, $obj);
			}
			$obj = (object)[];
			$obj->grafik = $grafik;
			$arr_res = $obj;
		}
		return json_encode($arr_res);
	}

	public function get_target_pajak_tunggakan($returnQuery = false, $tahun = '2022')
	{
		$query = "
			SELECT
				SUM( A.simpatda_dibayar + A.simpatda_denda ) AS TOTAL_PENDAPATAN,
				C.CPM_JUMLAH AS TARGET,
				B.id_sw AS ID_JENIS_PAJAK,
				B.jenis_sw AS JENIS_PAJAK,
				YEAR ( A.payment_paid ) AS TAHUN 
			FROM
				SIMPATDA_GW A
				INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id
				INNER JOIN PATDA_TARGET_PAJAK C ON B.id_sw = C.CPM_JENIS_PAJAK 
				AND C.CPM_TAHUN = $tahun
				AND C.CPM_AKTIF = '1' 
			WHERE
				A.simpatda_tahun_pajak = $tahun
				AND A.payment_flag != '1' 
			GROUP BY
				A.simpatda_type;
		";

		$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

		if ($returnQuery) {
			return $result;
		}

		$arr_res = array();
		while ($row = mysqli_fetch_assoc($result)) {
			$arr_res['jenis_pajak'][] = $row['JENIS_PAJAK'];
			$arr_res['pendapatan'][] = $row['TOTAL_PENDAPATAN'];
			$arr_res['target'][] = $row['TARGET'];
			$persen = ($row['TOTAL_PENDAPATAN'] <= 0 || (float)$row['TARGET'] == 0) ? 0 : (float)$row['TOTAL_PENDAPATAN'] / (float)$row['TARGET'] * 100;
			$persen = (float)number_format($persen, 1);
			$arr_res['persentase'][] = $persen;

			$arr_res['grafik'][] = array(
				'name' => $row['JENIS_PAJAK'],
				'data' => array($persen),
				'tooltip' => array(
					'valueSuffix' => ' % [Rp.' . number_format($row['TOTAL_PENDAPATAN'], 2) . ']'
				)
			);
		}
		if (count($arr_res) == 0) {
			$jenispajak = ["Air Bawah Tanah", "Hiburan", "Hotel", "Parkir", "Penerangan Jalan", "Reklame (NonReg)", "Restoran", "Sarang Burung Walet"];
			$grafik = [];
			foreach ($jenispajak as $v) {
				$obj = (object)[];
				$obj->name = $v;
				$obj->data = [1];
				$obj->tooltip = (object)[];
				$obj->tooltip->valueSuffix = '';
				array_push($grafik, $obj);
			}
			$obj = (object)[];
			$obj->grafik = $grafik;
			$arr_res = $obj;
		}
		return json_encode($arr_res);
	}


	public function get_target_pajak_perbandingan($returnQuery = false, $tahun = '2022')
	{
		$query = "
			SELECT
				SUM( A.simpatda_dibayar + A.simpatda_denda ) AS TOTAL_PENDAPATAN,
				YEAR ( A.payment_paid ) AS TAHUN 
			FROM
				SIMPATDA_GW A
				INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id
				INNER JOIN PATDA_TARGET_PAJAK C ON B.id_sw = C.CPM_JENIS_PAJAK 
				AND C.CPM_TAHUN = $tahun
				AND C.CPM_AKTIF = '1' 
			WHERE
				A.simpatda_tahun_pajak = $tahun
				AND A.payment_flag != '1' 
		";

		$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

		$tunggakan = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			$tunggakan = $row['TOTAL_PENDAPATAN'];
		}


		//pencapaian
		$query = "
			SELECT
				SUM( A.patda_total_bayar ) AS TOTAL_PENDAPATAN,
				YEAR ( A.payment_paid ) AS TAHUN 
			FROM
				SIMPATDA_GW A
				INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id
				INNER JOIN PATDA_TARGET_PAJAK C ON B.id_sw = C.CPM_JENIS_PAJAK 
				AND C.CPM_TAHUN = $tahun
				AND C.CPM_AKTIF = '1' 
			WHERE
				YEAR ( A.payment_paid ) = $tahun
				AND A.payment_flag = '1' 
		";

		$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

		$pencapaian = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			$pencapaian = $row['TOTAL_PENDAPATAN'];
		}

		$total = $tunggakan + $pencapaian;
		$tunggakan_persen = ($tunggakan > 0) ? $tunggakan / $total * 100 : 0;
		$pencapaian_persen = ($pencapaian > 0) ? $pencapaian / $total * 100 : 0;
		$arr_res = array();
		$arr_res['grafik'][] = array(
			'name' => 'Persentase',
			'colorByPoint' => 'true',
			'data' => [
				array('name' => 'Total Tunggakan', 'y' => ($total == 0) ? 100 : $tunggakan_persen, 'custom' => 'Rp.' . number_format($tunggakan, 2)),
				array('name' => 'Total Pencapaian', 'y' => ($total == 0) ? 0 : $pencapaian_persen, 'custom' => 'Rp.' . number_format($pencapaian, 2)),
			]
		);
		return json_encode($arr_res);
	}

	function get_nilai_table($bulan = '01', $tahun = '2022')
	{
		$query = "
				SELECT
					SUM(A.simpatda_dibayar) AS TOTAL_PENDAPATAN,
					C.CPM_JUMLAH AS TARGET,
					B.id_sw AS ID_JENIS_PAJAK,
					B.jenis_sw AS JENIS_PAJAK,
					YEAR(A.payment_paid) AS TAHUN
				FROM
					SIMPATDA_GW A 
					INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id
					INNER JOIN PATDA_TARGET_PAJAK C ON B.id_sw = C.CPM_JENIS_PAJAK AND C.CPM_TAHUN = $tahun AND C.CPM_AKTIF = '1'
				WHERE
					YEAR ( A.payment_paid ) = $tahun 
					AND A.payment_flag = '1' AND MONTH(A.payment_paid) = '$bulan'
				GROUP BY B.id_sw, MONTH(A.payment_paid);
			";

		$result = mysqli_query($this->Conn, $query);

		$arr_res = array();
		while ($row = mysqli_fetch_assoc($result)) {
			$arr_res[$row['ID_JENIS_PAJAK']] = $row['TOTAL_PENDAPATAN'];
		}
		return $arr_res;
	}

	public function get_nilai_pencapaian($returnQuery = false, $tahun = '2022')
	{
		$result_for_table = $this->get_target_pajak(true, $tahun);

		$arrBULAN = $this->arr_bulan;
		$bulanini = date('m');
		$bulanini = ($tahun == date('Y')) ? $bulanini : 12;
		// $bulanini = '06';
		$blniniint = (int)$bulanini;
		$tib = $blniniint - 2;
		$tib = ($tib <= 0) ? 1 : $tib;
		$colspan = $blniniint + 1;
		$colspan = ($colspan >= 5) ? 4 : $colspan;

		$capaibulan = [];
		for ($i = $tib; $i <= $blniniint; $i++) $capaibulan[$i] = $this->get_nilai_table(sprintf("%02d", $i), $tahun);

		$html = '<thead>
					<tr>
						<th rowspan="2">Jenis Pajak</th>
						<th rowspan="2">Target</th>
						<th colspan="' . ($colspan) . '">Realisasi ' . $tahun . '</th>
						<th rowspan="2">Persentase</th>
					</tr>
					<tr>';
		for ($i = $tib; $i <= $blniniint; $i++) $html .= '<th>' . $arrBULAN[$i] . ' ' . $tahun . '</th>';
		$html .= ($blniniint == 12) ? '<th>Total 1&nbsp;Tahun</th>' : (($blniniint <= 3) ? '<th>Total ' . $blniniint . '&nbsp;Bulan</th>' : '<th>Total Hingga Bulan ' . $arrBULAN[$blniniint] . '</th>');
		$html .= '</tr>
				</thead>
				<tbody>';
		$n = 0;
		while ($row = mysqli_fetch_assoc($result_for_table)) {
			$html .= '<tr>';
			$html .= '<td>' . $row['JENIS_PAJAK'] . '</td>';
			$html .= '<td>' . number_format($row['TARGET'], 2) . '</td>';
			$idpj = (int)$row['ID_JENIS_PAJAK'];
			for ($i = $tib; $i <= $blniniint; $i++) {
				$dibulan = $capaibulan[$i];
				$html .= '<td>' . (isset($dibulan[$idpj]) ? number_format($dibulan[$idpj]) : 0) . '</td>';
			}
			$html .= '<td>' . number_format($row['TOTAL_PENDAPATAN'], 2) . '</td>';
			$persentage = ($row['TOTAL_PENDAPATAN'] <= 0 || (float)$row['TARGET'] <= 0) ? 0 : (float)round(((float) $row['TOTAL_PENDAPATAN'] / ((float)$row['TARGET']) * 100), 2);
			$html .= '<td>' . $persentage . ' %</td>';
			$html .= '</tr>';
			$n++;
		}
		if ($n == 0) {
			$html .= '<tr><td>JENIS_PAJAK</td><td>0</td>';
			for ($i = $tib; $i <= $blniniint; $i++) $html .= '<td>0</td>';
			$html .= '<td>0</td><td>0%</td></tr>';
		}
		return $html . '</tbody>';
	}

	public function download_excel($tahun = 2022)
	{
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('Rekapitulasi ' . $tahun);

		$result_for_table = $this->get_target_pajak(true, $tahun);
		$arrBULAN = $this->arr_bulan;
		$tanggalSekarang = date('d') . ' ' . $arrBULAN[date('n')] . ' ' . date('Y');
		$startRow = 5;
		$bulanini = date('m');
		$bulanini = ($tahun == date('Y')) ? $bulanini : 12;
		$blniniint = (int)$bulanini;
		$tib = 1;
		$no = 1;

		$capaibulan = [];
		for ($i = $tib; $i <= $blniniint; $i++) $capaibulan[$i] = $this->get_nilai_table(sprintf("%02d", $i), $tahun);

		// HEADER
		$objPHPExcel->getActiveSheet()->setCellValue("A1", 'REKAPITULASI REALISASI DAN TARGET SEMUA JENIS PAJAK ' . $tahun);
		$objPHPExcel->getActiveSheet()->setCellValue('A3', 'NO')->getColumnDimension('A')->setWidth(4);
		$objPHPExcel->getActiveSheet()->setCellValue('B3', 'JENIS PAJAK')->getColumnDimension('B')->setWidth(27);
		$objPHPExcel->getActiveSheet()->setCellValue('C3', 'TARGET')->getColumnDimension('C')->setWidth(25);

		$objPHPExcel->getActiveSheet()->setCellValue('D3', 'REALISASI');

		$clm = 'D';
		for ($i = $tib; $i <= $blniniint; $i++) {
			$totaltarget = 0;
			$totalbulan{
				$i} = 0;
			$totalpencapaian = 0;
			$objPHPExcel->getActiveSheet()->setCellValue($clm . '4', $arrBULAN[$i])->getColumnDimension($clm)->setWidth(22);
			$clm++;
		}
		$objPHPExcel->getActiveSheet()->mergeCells("D3:" . $clm-- . "3");
		$objPHPExcel->getActiveSheet()->setCellValue($clm . '4', 'Total s/d ' . $arrBULAN[$blniniint])->getColumnDimension($clm)->setWidth(25);

		$clm++;
		$objPHPExcel->getActiveSheet()->setCellValue($clm . '3', '%')->getColumnDimension($clm)->setWidth(10);
		$clm++;
		$objPHPExcel->getActiveSheet()->setCellValue($clm . '3', 'LEBIH/KURANG')->getColumnDimension($clm)->setWidth(22);
		$clm++;
		$objPHPExcel->getActiveSheet()->setCellValue($clm . '3', 'KET')->getColumnDimension($clm)->setWidth(10); // HEADER END

		$colpersen = 'G';
		$colplusmin = 'H';
		$colterakhir = 'I';
		while ($row = mysqli_fetch_assoc($result_for_table)) {

			$objPHPExcel->getActiveSheet()->setCellValue("A{$startRow}", $no)
				->setCellValue("B{$startRow}", $row['JENIS_PAJAK'])
				->setCellValue("C{$startRow}", $row['TARGET']);
			$clm = 'D';
			$idpj = (int)$row['ID_JENIS_PAJAK'];
			for ($i = $tib; $i <= $blniniint; $i++) {
				$dibulan = $capaibulan[$i];
				$objPHPExcel->getActiveSheet()->setCellValue($clm . $startRow, (isset($dibulan[$idpj]) ? $dibulan[$idpj] : 0));
				$totalbulan{
					$i} += (isset($dibulan[$idpj]) ? $dibulan[$idpj] : 0);
				$clm++;
			}
			$objPHPExcel->getActiveSheet()->setCellValue($clm . $startRow, $row['TOTAL_PENDAPATAN']);
			$objPHPExcel->getActiveSheet()->getStyle('C' . $startRow . ':' . $clm . $startRow)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_IDR_SIMPLE);

			$clm++;
			$angkapersent = ($row['TOTAL_PENDAPATAN'] > 0 && $row['TARGET'] > 0) ? (float)round(((float) $row['TOTAL_PENDAPATAN'] / (float) $row['TARGET'] * 100), 2) : 0;
			$objPHPExcel->getActiveSheet()->setCellValue($clm . $startRow, $angkapersent . ' %');
			$colpersen = $clm;

			$lebihKurang = $row['TARGET'] - $row['TOTAL_PENDAPATAN'];
			$clm++;
			$objPHPExcel->getActiveSheet()->setCellValue($clm . $startRow, $lebihKurang);
			$objPHPExcel->getActiveSheet()->getStyle($clm . $startRow)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_IDR_SIMPLE);
			$colplusmin = $clm;

			$clm++;
			if ($lebihKurang > 0) { // kurang dari target (angkanya poisitif)
				$objPHPExcel->getActiveSheet()->setCellValue($clm . $startRow, '(Kurang)');
			} elseif ($lebihKurang < 0) {
				$lebihKurang *= -1;
				$objPHPExcel->getActiveSheet()->setCellValue($clm . $startRow, '(Lebih)');
			} else {
				$objPHPExcel->getActiveSheet()->setCellValue($clm . $startRow, '(Pas)');
			}
			$colterakhir = $clm;
			$no++;
			$startRow++;

			$totaltarget += $row['TARGET'];
			$totalpencapaian += $row['TOTAL_PENDAPATAN'];
		}
		// print_r($capaibulan);exit;


		$objPHPExcel->getActiveSheet()->setCellValue("A{$startRow}", 'TOTAL')
			->setCellValue("C{$startRow}", $totaltarget);
		$clm = 'D';
		for ($i = $tib; $i <= $blniniint; $i++) {
			$objPHPExcel->getActiveSheet()->setCellValue($clm . $startRow, $totalbulan{
				$i});
			$clm++;
		}
		$objPHPExcel->getActiveSheet()->setCellValue($clm . $startRow, $totalpencapaian);
		$objPHPExcel->getActiveSheet()->getStyle("C{$startRow}:" . $clm . $startRow)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_IDR_SIMPLE);

		// FOOTER
		$footerStartRow = $startRow + 2;
		$objPHPExcel->getActiveSheet()->setCellValue("A{$footerStartRow}", 'Dibuat Tanggal: ' . $tanggalSekarang);
		$objPHPExcel->getActiveSheet()->setCellValue("A" . ($footerStartRow + 3), 'Mengetahui')
			->setCellValue("A" . ($footerStartRow + 4), 'Kepala BPPRD Bandar Lampung')
			->setCellValue("A" . ($footerStartRow + 5), 'Kota Bandar Lampung')
			->setCellValue("A" . ($footerStartRow + 10), '(_________________________)')
			->setCellValue("A" . ($footerStartRow + 11), '000');
		// END FOOTER

		// MERGER
		$objPHPExcel->getActiveSheet()->mergeCells("A1:I1")
			->mergeCells("A3:A4")
			->mergeCells("B3:B4")
			->mergeCells("C3:C4")
			->mergeCells("{$colpersen}3:{$colpersen}4")
			->mergeCells("{$colplusmin}3:{$colplusmin}4")
			->mergeCells("{$colterakhir}3:{$colterakhir}4")
			->mergeCells("A" . ($footerStartRow) . ":B" . ($footerStartRow))
			->mergeCells("A" . ($footerStartRow + 3) . ":B" . ($footerStartRow + 3))
			->mergeCells("A" . ($footerStartRow + 4) . ":B" . ($footerStartRow + 4))
			->mergeCells("A" . ($footerStartRow + 5) . ":B" . ($footerStartRow + 5))
			->mergeCells("A" . ($footerStartRow + 10) . ":B" . ($footerStartRow + 10))
			->mergeCells("A" . ($footerStartRow + 11) . ":B" . ($footerStartRow + 11))
			->mergeCells("A{$startRow}:B{$startRow}");

		$objPHPExcel->getActiveSheet()->mergeCells();
		// END MERGER

		// STYLES
		$objPHPExcel->getActiveSheet()->getStyle("A{$footerStartRow}")->applyFromArray(array(
			'font' => array(
				'bold' => true
			)
		));
		$objPHPExcel->getActiveSheet()->getStyle("A5" . ":A" . ($startRow - 1))->applyFromArray(array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
		));
		$objPHPExcel->getActiveSheet()->getStyle("I5" . ":I" . ($startRow - 1))->applyFromArray(array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
		));
		$objPHPExcel->getActiveSheet()->getStyle("A" . ($footerStartRow + 3) . ":B" . ($footerStartRow + 11))->applyFromArray(array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true
			)
		));
		$objPHPExcel->getActiveSheet()->getStyle("H5:" . $colplusmin . $startRow)->applyFromArray(array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		));
		$objPHPExcel->getActiveSheet()->getStyle("C{$startRow}:" . $colterakhir . $startRow)->applyFromArray(array(
			'font' => array(
				'bold' => true
			)
		));
		$objPHPExcel->getActiveSheet()->getStyle("C5:F{$startRow}")->applyFromArray(array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		));
		$objPHPExcel->getActiveSheet()->getStyle("G5:" . $colpersen . $startRow)->applyFromArray(array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		)); // persenan
		$objPHPExcel->getActiveSheet()->getStyle("A1:{$colterakhir}4")->applyFromArray(array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true
			)
		)); // judul + header
		$objPHPExcel->getActiveSheet()->getStyle("A3:" . $colterakhir . $startRow)->applyFromArray(
			array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				)
			)
		);
		$objPHPExcel->getActiveSheet()->getStyle("A1")->applyFromArray(
			array(
				'font' => array(
					'size' => 16
				)
			)
		);
		// END STYLES

		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');

		header('Content-Disposition: attachment;filename="pencapaian_' . $tahun . '_' . date('His') . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
}
