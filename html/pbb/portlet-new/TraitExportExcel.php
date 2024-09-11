<?php

/**
 * ExportExcel
 */
trait ExportExcel
{
    protected $excelProperties = array(
        'creator'        => 'Alfa System',
        'lastModifiedBy' => 'Alfa System',
        'title'          => 'Data Pembayaran',
        'subject'        => 'Data Pembayaran',
        'description'    => 'Data Pembayaran Wajib Pajak',
        'keywords'       => 'Alfa System, Data Pembayaran, Data Pembayaran Wajib Pajak',
        'NAMA_DINAS'     => 'BADAN PELAYANAN PAJAK DAERAH',
        'HEADER_TEXT'    => 'INFORMASI DATA PEMBAYARAN',
        'filename'       => 'data_pembayaran'
    );

    public function getExcel($PHPExcel)
    {
        $data = $this->getData();
        $lastIndex = ($data['numRows'] - 1);

        if (empty($data['rows'])) {
            return false;
        }

        $PHPExcel->getProperties()
            ->setCreator($this->excelProperties['creator'])
            ->setLastModifiedBy($this->excelProperties['lastModifiedBy'])
            ->setTitle($this->excelProperties['title'])
            ->setSubject($this->excelProperties['subject'])
            ->setDescription($this->excelProperties['description'])
            ->setKeywords($this->excelProperties['keywords']);
        
        $bold = array('font' => array('bold' => true));
        $center = array( 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        ));

        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', 'PEMERINTAH ' . $this->appConfig['C_KABKOT'] . ' ' . $this->appConfig['NAMA_KOTA'])
            ->setCellValue('A3', $this->excelProperties['NAMA_DINAS'])
            ->setCellValue('A5', $this->excelProperties['HEADER_TEXT'])
            ->setCellValue('A6', 'Nomor Objek Pajak')
            ->setCellValue('E6', 'Tahun Ketetapan')
            ->setCellValue('A11', 'Nama Wajib Pajak')
            ->setCellValue('A12', 'Alamat Wajib Pajak')
            ->setCellValue('A10', 'Alamat Objek Pajak')
            ->setCellValue('A9', 'Kecamatan Objek Pajak')
            ->setCellValue('E9', 'Kelurahan Objek Pajak')
            ->setCellValue('A7', 'Luas Bumi')
            ->setCellValue('E7', 'NJOP Bumi')
            ->setCellValue('A8', 'Luas Bangunan')
            ->setCellValue('E8', 'NJOP Bangunan')
            ->setCellValue('A13', 'Tanggal Printout');

        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B6', ': ' . $this->formatNop($data['rows'][$lastIndex]['NOP']))
            ->setCellValue('F6', ': ' . $data['rows'][$lastIndex]['SPPT_TAHUN_PAJAK'])
            ->setCellValue('B11', ': ' . $data['rows'][$lastIndex]['WP_NAMA'])
            ->setCellValue('B12', ': ' . $data['rows'][$lastIndex]['WP_ALAMAT'])
            ->setCellValue('B10', ': ' . $data['rows'][$lastIndex]['OP_ALAMAT'])
            ->setCellValue('B9', ': ' . $data['rows'][$lastIndex]['OP_KECAMATAN'])
            ->setCellValue('F9', ': ' . $data['rows'][$lastIndex]['OP_KELURAHAN'])
            ->setCellValue('B7', ': ' . $data['rows'][$lastIndex]['OP_LUAS_BUMI'] . ' M2')
            ->setCellValue('F7', ': ' . $this->formatRupiah($data['rows'][$lastIndex]['NJOP_BUMI_M2']) . ' / M2')
            ->setCellValue('B8', ': ' . $data['rows'][$lastIndex]['OP_LUAS_BANGUNAN'] . ' M2')
            ->setCellValue('F8', ': ' . $this->formatRupiah($data['rows'][$lastIndex]['NJOP_BANGUNAN_M2']). ' / M2')
            ->setCellValue('B13', ': ' . $this->formatHumanDate(date('Y-m-d')));
        
        $PHPExcel->getActiveSheet()->getStyle('A2:A5')->applyFromArray($center);
        $PHPExcel->getActiveSheet()->getStyle('A2:A5')->applyFromArray($bold);
        $PHPExcel->getActiveSheet()->mergeCells('A2:G2')
            ->mergeCells('A3:G3')
            ->mergeCells('A5:G5')
            ->mergeCells('B6:D6')
            ->mergeCells('F6:G6')
            ->mergeCells('B7:D7')
            ->mergeCells('F7:G7')
            ->mergeCells('B8:D8')
            ->mergeCells('F8:G8')
            ->mergeCells('B9:D9')
            ->mergeCells('F9:G9')
            ->mergeCells('B10:G10')
            ->mergeCells('B11:G11')
            ->mergeCells('B12:G12')
            ->mergeCells('B13:G13')
            ->mergeCells('A14:G14');
        
        $startRow = 15;
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $startRow, 'NAMA WP')
            ->setCellValue('B' . $startRow, 'TAHUN PAJAK')
            ->setCellValue('C' . $startRow, 'PBB')
            ->setCellValue('D' . $startRow, 'DENDA(*)')
            ->setCellValue('E' . $startRow, 'JATUH TEMPO')
            ->setCellValue('F' . $startRow, 'KURANG BAYAR')
            ->setCellValue('G' . $startRow, 'STATUS BAYAR');

        $PHPExcel->getActiveSheet()->getStyle('A' . $startRow . ':G' . $startRow)->applyFromArray($center);
        $PHPExcel->getActiveSheet()->getStyle('A' . $startRow . ':G' . $startRow)->applyFromArray($bold);
        $PHPExcel->getActiveSheet()->getStyle('A' . $startRow . ':G' . ($startRow + $data['numRows']))->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );

        $PHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $PHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $PHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $PHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $PHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $PHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $PHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

        $contentRow = $startRow + 1; // 16
        foreach ($data['rows'] as $row) {
            $PHPExcel->getActiveSheet()->setCellValue('A' . $contentRow, $row['WP_NAMA']);
            $PHPExcel->getActiveSheet()->setCellValue('B' . $contentRow, $row['SPPT_TAHUN_PAJAK']);
            $PHPExcel->getActiveSheet()->setCellValue('C' . $contentRow, $row['SPPT_PBB_HARUS_DIBAYAR']);
            $PHPExcel->getActiveSheet()->setCellValue('D' . $contentRow, $row['PBB_DENDA'] + 0);
            $PHPExcel->getActiveSheet()->setCellValue('E' . $contentRow, $this->formatDate($row['SPPT_TANGGAL_JATUH_TEMPO']));
            $PHPExcel->getActiveSheet()->setCellValue('F' . $contentRow, $row['TAGIHAN_PLUS_DENDA'] + 0);
            $PHPExcel->getActiveSheet()->setCellValue('G' . $contentRow, !$row['IS_LUNAS'] ? Portlet::BELUM_LUNAS_TEXT : 'LUNAS: ' . $row['PAYMENT_PAID']);
            $contentRow++;
        }

        $PHPExcel->getActiveSheet()->getStyle('B' . $startRow . ':B' . ($contentRow - 1))->applyFromArray($center);
        $PHPExcel->getActiveSheet()->getStyle('E' . $startRow . ':E' . ($contentRow - 1))->applyFromArray($center);
        $PHPExcel->getActiveSheet()->getStyle('G' . $startRow . ':G' . ($contentRow - 1))->applyFromArray($center);

        $summaryRow = $contentRow + 1;
        $PHPExcel->getActiveSheet()->mergeCells('A' . $summaryRow . ':F' . $summaryRow);
        $PHPExcel->getActiveSheet()->mergeCells('A' . ($summaryRow + 1) . ':F' . ($summaryRow + 1));
        $PHPExcel->getActiveSheet()->mergeCells('A' . ($summaryRow + 2) . ':F' . ($summaryRow + 2));
        $PHPExcel->getActiveSheet()->mergeCells('A' . ($summaryRow + 3) . ':F' . ($summaryRow + 3));
        $PHPExcel->getActiveSheet()->setCellValue('A' . $summaryRow, 'TOTAL PBB YANG BELUM DIBAYAR');
        $PHPExcel->getActiveSheet()->setCellValue('A' . ($summaryRow + 1), 'TOTAL DENDA (SESUAI TANGGAL PRINTOUT)');
        $PHPExcel->getActiveSheet()->setCellValue('A' . ($summaryRow + 2), 'JUMLAH YANG HARUS DIBAYAR');
        $PHPExcel->getActiveSheet()->setCellValue('A' . ($summaryRow + 3), '*Perhitungan Denda : 2% setiap bulan, maksimal 24 bulan.');
        $PHPExcel->getActiveSheet()->setCellValue('G' . $summaryRow, $data['total']['tagihan'] + 0);
        $PHPExcel->getActiveSheet()->setCellValue('G' . ($summaryRow + 1), $data['total']['denda'] + 0);
        $PHPExcel->getActiveSheet()->setCellValue('G' . ($summaryRow + 2), $data['total']['tagihan_plus_denda'] + 0);
        $PHPExcel->getActiveSheet()->setCellValue('A' . ($summaryRow + 5), 'Petugas');
        $PHPExcel->getActiveSheet()->setCellValue('B' . ($summaryRow + 5), ': ..................................................');
        $PHPExcel->getActiveSheet()->setCellValue('A' . ($summaryRow + 6), 'Keperluan');
        $PHPExcel->getActiveSheet()->setCellValue('B' . ($summaryRow + 6), ': ..................................................');

        $PHPExcel->getActiveSheet()->setCellValue('G' . ($summaryRow + 5), $this->appConfig['NAMA_KOTA_PENGESAHAN'] . ', ' . $this->formatHumanDate(date('Y-m-d')));
        $PHPExcel->getActiveSheet()->setCellValue('G' . ($summaryRow + 9), $this->appConfig['KABID_NAMA']);
        $PHPExcel->getActiveSheet()->setCellValue('G' . ($summaryRow + 10), $this->appConfig['KABID_NIP']);
        $PHPExcel->getActiveSheet()->getStyle('G' . ($summaryRow + 5) . ':G' . ($summaryRow + 10))->applyFromArray($center);
        $PHPExcel->getActiveSheet()->getStyle('A' . ($summaryRow) . ':G' . ($summaryRow + 2))->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );

        $PHPExcel->getActiveSheet()->setTitle('Data Pembayaran');

        return $PHPExcel;
    }
}
