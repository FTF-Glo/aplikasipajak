<?php

/**
 * Class Grafik Pencapaian Pajak
 */
class GrafikPencapaian extends Pajak
{

	public function get_target_pajak($returnQuery = false, $tahun = '2021')
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
				AND C.CPM_TAHUN = YEAR ( CURRENT_DATE ) 
				AND C.CPM_AKTIF = '1' 
			WHERE
				YEAR ( A.payment_paid ) = '$tahun'
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
			$arr_res['persentase'][] = (float) $row['TOTAL_PENDAPATAN'] / (float) $row['TARGET'] * 100;

			$arr_res['grafik'][] = array(
				'name' => $row['JENIS_PAJAK'],
				'data' => array((float) $row['TOTAL_PENDAPATAN'] / (float) $row['TARGET'] * 100),
				'tooltip' => array(
					'valueSuffix' => ' % [Rp.' . number_format($row['TOTAL_PENDAPATAN'], 2) . ']'
				)
			);
		}
		return json_encode($arr_res);
	}

	public function get_target_pajak_tunggakan($returnQuery = false, $tahun = '2021')
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
				AND C.CPM_TAHUN = YEAR ( CURRENT_DATE ) 
				AND C.CPM_AKTIF = '1' 
			WHERE
				A.simpatda_tahun_pajak = '$tahun'
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
			$arr_res['persentase'][] = (float) $row['TOTAL_PENDAPATAN'] / (float) $row['TARGET'] * 100;

			$arr_res['grafik'][] = array(
				'name' => $row['JENIS_PAJAK'],
				'data' => array((float) $row['TOTAL_PENDAPATAN'] / (float) $row['TARGET'] * 100),
				'tooltip' => array(
					'valueSuffix' => ' % [Rp.' . number_format($row['TOTAL_PENDAPATAN'], 2) . ']'
				)
			);
		}
		return json_encode($arr_res);
	}


	public function get_target_pajak_perbandingan($returnQuery = false, $tahun = '2021')
	{
		$query = "
			SELECT
				SUM( A.simpatda_dibayar + A.simpatda_denda ) AS TOTAL_PENDAPATAN,
				YEAR ( A.payment_paid ) AS TAHUN 
			FROM
				SIMPATDA_GW A
				INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id
				INNER JOIN PATDA_TARGET_PAJAK C ON B.id_sw = C.CPM_JENIS_PAJAK 
				AND C.CPM_TAHUN = YEAR ( CURRENT_DATE ) 
				AND C.CPM_AKTIF = '1' 
			WHERE
				A.simpatda_tahun_pajak = '$tahun'
				AND A.payment_flag != '1' 
		";

		$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

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
				AND C.CPM_TAHUN = YEAR ( CURRENT_DATE ) 
				AND C.CPM_AKTIF = '1' 
			WHERE
				YEAR ( A.payment_paid ) = '$tahun'
				AND A.payment_flag = '1' 
		";

		$result = mysqli_query($this->Conn, $query) or die(mysqli_error($this->Conn));

		while ($row = mysqli_fetch_assoc($result)) {
			$pencapaian = $row['TOTAL_PENDAPATAN'];
		}

		$total = $tunggakan + $pencapaian;
		$tunggakan_persen = ($tunggakan>0) ? $tunggakan / $total * 100 : 0;
		$pencapaian_persen = ($pencapaian>0) ? $pencapaian / $total * 100 : 0;
		$arr_res = array();
		$arr_res['grafik'][] = array(
			'name' => 'Persentase',
			'colorByPoint' => 'true',
			'data' => [
				array('name' => 'Total Tunggakan', 'y' => $tunggakan_persen, 'custom' => 'Rp.' . number_format($tunggakan, 2)),
				array('name' => 'Total Pencapaian', 'y' => $pencapaian_persen, 'custom' => 'Rp.' . number_format($pencapaian, 2)),
			]
		);




		return json_encode($arr_res);
	}

	public function get_bulan_lalu()
	{
		$query = "
			SELECT
				SUM(A.patda_total_bayar) AS TOTAL_PENDAPATAN,
				C.CPM_JUMLAH AS TARGET,
				B.id_sw AS ID_JENIS_PAJAK,
				B.jenis_sw AS JENIS_PAJAK,
				YEAR(A.payment_paid) AS TAHUN,
				MONTH(A.payment_paid) AS BULAN
			FROM
				SIMPATDA_GW A 
				INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id
				INNER JOIN PATDA_TARGET_PAJAK C ON B.id_sw = C.CPM_JENIS_PAJAK AND C.CPM_TAHUN = YEAR(CURRENT_DATE) AND C.CPM_AKTIF = '1'
			WHERE
				YEAR ( A.payment_paid ) = YEAR ( CURRENT_DATE ) 
				AND A.payment_flag = '1' AND MONTH(A.payment_paid) = MONTH(CURRENT_DATE)
			GROUP BY A.simpatda_type, MONTH(A.payment_paid)
			UNION
			SELECT
				SUM(A.patda_total_bayar) AS TOTAL_PENDAPATAN,
				C.CPM_JUMLAH AS TARGET,
				B.id_sw AS ID_JENIS_PAJAK,
				B.jenis_sw AS JENIS_PAJAK,
				YEAR(A.payment_paid) AS TAHUN,
				MONTH(A.payment_paid) AS BULAN
			FROM
				SIMPATDA_GW A 
				INNER JOIN SIMPATDA_TYPE B ON A.simpatda_type = B.id
				INNER JOIN PATDA_TARGET_PAJAK C ON B.id_sw = C.CPM_JENIS_PAJAK AND C.CPM_TAHUN = YEAR(CURRENT_DATE) AND C.CPM_AKTIF = '1'
			WHERE
				YEAR ( A.payment_paid ) = YEAR ( CURRENT_DATE ) 
				AND A.payment_flag = '1' AND MONTH(A.payment_paid) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
			GROUP BY A.simpatda_type, MONTH(A.payment_paid);
		";

		$result = mysqli_query($this->Conn, $query);

		$arr_res = array();
		while ($row = mysqli_fetch_assoc($result)) {
			$arr_res[$row['BULAN']][$row['ID_JENIS_PAJAK']] = $row['TOTAL_PENDAPATAN'];
		}

		return $arr_res;
	}

	public function download_excel()
	{

		$objPHPExcel = new PHPExcel();
		$arr_bulan = $this->arr_bulan;
		$tanggalSekarang = date('d') . ' ' . $arr_bulan[date('n')] . ' ' . date('Y');
		$startRow = 5;
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('Total');

		$no = 1;
		$total = array(
			'target' => 0,
			'bulan_ini' => 0,
			'bulan_lalu' => 0,
			'sekarang' => 0
		);

		// HEADER
		$objPHPExcel->getActiveSheet()->setCellValue("A1", 'REKAPITULASI REALISASI DAN TARGET SEMUA OBJEK PAJAK (' . $tanggalSekarang . ')')
			->setCellValue('A3', 'NO')
			->setCellValue('B3', 'JENIS PAJAK')
			->setCellValue('C3', 'TARGET')
			->setCellValue('D3', 'REALISASI')
			->setCellValue('D4', 'BULAN LALU')
			->setCellValue('E4', 'BULAN INI')
			->setCellValue('F4', 'S/D BULAN INI')
			->setCellValue('G3', '%')
			->setCellValue('H3', 'LEBIH/KURANG')
			->setCellValue('I3', 'KET'); // HEADER END

		// QUERY
		$perbulan = $this->get_bulan_lalu();
		$result = $this->get_target_pajak(true);
		while ($row = mysqli_fetch_assoc($result)) {
			$bulan_ini = (isset($perbulan[date('n')][(int)$row['ID_JENIS_PAJAK']]) ? $perbulan[date('n')][(int)$row['ID_JENIS_PAJAK']] : 0);
			$bulan_lalu = (isset($perbulan[date('n', strtotime('-1 month'))][(int)$row['ID_JENIS_PAJAK']]) ? $perbulan[date('n', strtotime('-1 month'))][(int)$row['ID_JENIS_PAJAK']] : 0);


			$objPHPExcel->getActiveSheet()->setCellValue("A{$startRow}", $no)
				->setCellValue("B{$startRow}", $row['JENIS_PAJAK'])
				->setCellValue("C{$startRow}", 'Rp.' . number_format($row['TARGET'], 2))
				->setCellValue("D{$startRow}", 'Rp.' . number_format($bulan_lalu, 2))
				->setCellValue("E{$startRow}", 'Rp.' . number_format($bulan_ini, 2))
				->setCellValue("F{$startRow}", 'Rp.' . number_format($row['TOTAL_PENDAPATAN'], 2))
				->setCellValue("G{$startRow}", ((float) $row['TOTAL_PENDAPATAN'] / (float) $row['TARGET'] * 100) . '%');

			$lebihKurang = $row['TARGET'] - $row['TOTAL_PENDAPATAN'];
			if ($lebihKurang > 0) { // kurang dari target (angkanya poisitif)
				$objPHPExcel->getActiveSheet()->setCellValue("I{$startRow}", '(kurang)');
			} elseif ($lebihKurang < 0) {
				$lebihKurang *= -1;
				$objPHPExcel->getActiveSheet()->setCellValue("I{$startRow}", '(lebih)');
			} else {
				$objPHPExcel->getActiveSheet()->setCellValue("I{$startRow}", '(pas)');
			}
			$objPHPExcel->getActiveSheet()->setCellValue("H{$startRow}", 'Rp.' . number_format($lebihKurang, 2));
			$no++;
			$startRow++;

			$total['target'] += $row['TARGET'];
			$total['bulan_ini'] += $bulan_ini;
			$total['bulan_lalu'] += $bulan_lalu;
			$total['sekarang'] += $row['TOTAL_PENDAPATAN'];
		}

		$objPHPExcel->getActiveSheet()->setCellValue("A{$startRow}", 'TOTAL')
			->setCellValue("C{$startRow}", 'Rp.' . number_format($total['target'], 2))
			->setCellValue("D{$startRow}", 'Rp.' . number_format($total['bulan_lalu'], 2))
			->setCellValue("E{$startRow}", 'Rp.' . number_format($total['bulan_ini'], 2))
			->setCellValue("F{$startRow}", 'Rp.' . number_format($total['sekarang'], 2));
		// END QUERY

		// FOOTER
		$footerStartRow = $startRow + 2;
		$objPHPExcel->getActiveSheet()->setCellValue("A{$footerStartRow}", 'Dibuat Tanggal: ' . $tanggalSekarang);
		$objPHPExcel->getActiveSheet()->setCellValue("A" . ($footerStartRow + 3), 'Mengetahui')
			->setCellValue("A" . ($footerStartRow + 4), 'Kepala BPPRD Lampung Selatan')
			->setCellValue("A" . ($footerStartRow + 5), 'Kabupaten Lampung Selatan')
			->setCellValue("A" . ($footerStartRow + 10), 'Drs. BURHANUDDIN, MM')
			->setCellValue("A" . ($footerStartRow + 11), '19630310 198411 1 002');
		// END FOOTER

		// MERGER
		$objPHPExcel->getActiveSheet()->mergeCells("A1:I1")
			->mergeCells("D3:F3")
			->mergeCells("A3:A4")
			->mergeCells("B3:B4")
			->mergeCells("C3:C4")
			->mergeCells("G3:G4")
			->mergeCells("H3:H4")
			->mergeCells("I3:I4")
			->mergeCells("A" . ($footerStartRow) . ":B" . ($footerStartRow))
			->mergeCells("A" . ($footerStartRow + 3) . ":B" . ($footerStartRow + 3))
			->mergeCells("A" . ($footerStartRow + 4) . ":B" . ($footerStartRow + 4))
			->mergeCells("A" . ($footerStartRow + 5) . ":B" . ($footerStartRow + 5))
			->mergeCells("A" . ($footerStartRow + 10) . ":B" . ($footerStartRow + 10))
			->mergeCells("A" . ($footerStartRow + 11) . ":B" . ($footerStartRow + 11))
			->mergeCells("A{$startRow}:B{$startRow}");
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
		$objPHPExcel->getActiveSheet()->getStyle("H5:H{$startRow}")->applyFromArray(array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		));
		$objPHPExcel->getActiveSheet()->getStyle("C{$startRow}:F{$startRow}")->applyFromArray(array(
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
		$objPHPExcel->getActiveSheet()->getStyle("G5:G{$startRow}")->applyFromArray(array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		)); // persenan
		$objPHPExcel->getActiveSheet()->getStyle("A1:I4")->applyFromArray(array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true
			)
		)); // judul + header
		$objPHPExcel->getActiveSheet()->getStyle("A3:I{$startRow}")->applyFromArray(
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

		// SET WITDH
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(3.29);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(49.29);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(24.29);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(24.29);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(24.29);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(24.29);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(7);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(24.29);
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10.29);
		// END SET WIDTH

		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');

		header('Content-Disposition: attachment;filename="pencapaian' . date('yymdhmi') . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
}
