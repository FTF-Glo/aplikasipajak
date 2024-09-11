<?php

require_once 'classCetakDHKP.php';

class CetakTandaTerimaSppt extends CetakDHKP
{
    public $perPageList = 55;
    public $columnLength = array(
        'nomor'     => 4,
        'nop'       => 10,
        'nama_wp'   => 31,
        'alamat_op' => 19,
        'alamat_wp' => 20,
        'rt'        => 3,
        'rw'        => 3,
        'kls_bgn'   => 3,
        'kls_tnh'   => 3,
        'l_tnh'     => 9,
        'l_bgn'     => 9,
        'tagihan'   => 19,
        'paraf'     => 9,
        'foot_desc' => 104,
        'foot_val'  => 29,
        'buku'      => 6,
        'jumlah_op' => 20,
        'lt'        => 19,
        'lb'        => 22,
        'total'     => 21
    );

    public function __construct($DBLink, $kecamatan, $thn, $kelurahan, $stsPenetapan, $appConfig, $qBuku, $namaKecamatan, $namaKelurahan, $buku, $nop)
    {
        parent::__construct($DBLink, $kecamatan, $thn, $kelurahan, $stsPenetapan, $appConfig, $qBuku, $namaKecamatan, $namaKelurahan, $buku);

        $this->emptyRow = $this->setTableRow(array('nomor' => array(),'nop' => array(), 'nama_wp' => array(), 'alamat_wp' => array(), 'rt' => array(), 'rw' => array(), 'kls_bgn' => array(), 'kls_tnh' => array(),'l_tnh' => array(), 'l_bgn' => array(), 'tagihan' => array(), 'paraf' => array()));
        $this->headerRow = implode($this->getBR(), [
            $this->setTableRow(array(
                'nomor' => array(
                    'text' => 'NO',
                    'center' => true
                ),
                'nop' => array(
                    'text' => 'NOP',
                    'center' => true
                ),
                'nama_wp' => array(
                    'text' => 'NAMA WAJIB PAJAK',
                ),
                'alamat_wp' => array(
                    'text' => 'ALAMAT WAJIB PAJAK',
                ),
                'rt' => array(
                    'text' => 'RT',
                    'pad' => 'left'
                ),
                'rw' => array(
                    'text' => 'RW',
                    'pad' => 'left'
                ),
                'kls_bgn' => array(
                    'text' => 'KLS',
                    'center' => true
                ),
                'kls_tnh' => array(
                    'text' => 'KLS',
                    'center' => true
                ),
                'l_tnh' => array(
                    'text' => 'L.TNH',
                    'center' => true
                ),
                'l_bgn' => array(
                    'text' => 'L.BGN',
                    'center' => true
                ),
                'tagihan' => array(
                    'text' => 'PAJAK TERHUTANG',
                    'center' => true
                ),
                'paraf' => array(
                    'text' => 'PARAF',
                    'center' => true
                )
                )),
            $this->setTableRow(array(
                'nomor' => array(),'nop' => array(), 'nama_wp' => array(), 'alamat_wp' => array(), 'rt' => array(), 'rw' => array(), 
                'kls_bgn' => array(
                    'text' => 'BGN',
                    'center' => true
                ), 'kls_tnh' => array(
                    'text' => 'TNH',
                    'center' => true
                ), 'l_tnh' => array(), 'l_bgn' => array(), 'tagihan' => array(), 'paraf' => array()
            ))
        ]);
    }

    public function getData()
    {
        $res = $this->getQuery();

        $page = 2;
        $number = 1;
        $counter = 1;
        $totalTagihanPerPage = 0;
        $totalTagihanUntilPage = 0;

        $lastPageData = array(
            1 => array(
                'nop'   => 0,
                'lt'    => 0,
                'lb'    => 0,
                'total' => 0
            ), 2 => array(
                'nop'   => 0,
                'lt'    => 0,
                'lb'    => 0,
                'total' => 0
            ), 3 => array(
                'nop'   => 0,
                'lt'    => 0,
                'lb'    => 0,
                'total' => 0
            ), 4 => array(
                'nop'   => 0,
                'lt'    => 0,
                'lb'    => 0,
                'total' => 0
            ), 5 => array(
                'nop'   => 0,
                'lt'    => 0,
                'lb'    => 0,
                'total' => 0
            ),
        );

        $emptyRow = $this->emptyRow;

        $text = array();

        $text[] = $this->setCover();
        while ($row = mysqli_fetch_assoc($res)) {
            $nop            = ($row["NOP"] != "") ? $row["NOP"] : "";
            $nama           = ($row["WP_NAMA"] != "") ? $row["WP_NAMA"] : "";
            $alamat_wp      = ($row["WP_ALAMAT"] != "") ? $row["WP_ALAMAT"] : "";
            $alamat_op      = ($row["OP_ALAMAT"] != "") ? $row["OP_ALAMAT"] : "";
            $tagihan        = ($row["SPPT_PBB_HARUS_DIBAYAR"] != "") ? $row["SPPT_PBB_HARUS_DIBAYAR"] : 0;
            $luas_tanah     = ($row["OP_LUAS_BUMI"] != "") ? $row["OP_LUAS_BUMI"] : 0;
            $luas_bangunan  = ($row["OP_LUAS_BANGUNAN"] != "") ? $row["OP_LUAS_BANGUNAN"] : 0;
            $rt             = ($row["WP_RT"] != "") ? $row["WP_RT"] : "";
            $rw             = ($row["WP_RW"] != "") ? $row["WP_RW"] : "";
            $kls_tnh        = ($row["OP_KELAS_BUMI"] != "") ? $row["OP_KELAS_BUMI"] : "";
            $kls_bgn        = ($row["OP_KELAS_BANGUNAN"] != "") ? $row["OP_KELAS_BANGUNAN"] : "";
            $l_tnh          = ($row["OP_LUAS_BUMI"] != "") ? $row["OP_LUAS_BUMI"] : 0;
            $l_bgn          = ($row["OP_LUAS_BANGUNAN"] != "") ? $row["OP_LUAS_BANGUNAN"] : 0;

            if ($counter == 1) {
                $text[] = $this->setHeader($page);
            }

            $text[] = $this->setTableRow(array(
                'nomor' => array(
                    'text' => $number,
                    'pad' => 'left'
                ),
                'nop' => array(
                    'text' => $this->formatNOP($nop),
                    'center' => true
                ),
                'nama_wp' => array(
                    'text' => $this->strLimit(trim($nama), $this->columnLength['nama_wp'], '*', true),
                ),
                'alamat_wp' => array(
                    'text' => $this->strLimit(trim($alamat_wp), $this->columnLength['alamat_wp'], '*', true),
                ),
                'rt' => array(
                    'text' => $rt,
                    'pad' => 'left'
                ),
                'rw' => array(
                    'text' => $rw,
                    'pad' => 'left'
                ),
                'kls_bgn' => array(
                    'text' => $kls_bgn,
                    'center' => true
                ),
                'kls_tnh' => array(
                    'text' => $kls_tnh,
                    'center' => true
                ),
                'l_tnh' => array(
                    'text' => $this->toCurrency($l_tnh),
                    'pad' => 'left'
                ),
                'l_bgn' => array(
                    'text' => $this->toCurrency($l_bgn),
                    'pad' => 'left'
                ),
                'tagihan' => array(
                    'text' => $this->toCurrency($tagihan),
                    'pad' => 'left'
                ),
                'paraf' => array()
            ));


            $totalTagihanPerPage += $tagihan;
            $totalTagihanUntilPage += $tagihan;

            $lastPageData[$this->getBuku($tagihan)]['nop']      += 1;
            $lastPageData[$this->getBuku($tagihan)]['lt']       += $luas_tanah;
            $lastPageData[$this->getBuku($tagihan)]['lb']       += $luas_bangunan;
            $lastPageData[$this->getBuku($tagihan)]['total']    += $tagihan;

            if ($counter == $this->perPageList || $number == $this->rowCount) {
                if ($counter < $this->perPageList) {
                    $repeat = $this->perPageList - $counter;
                    for ($i = 0; $i < $repeat; $i++) {
                        $text[] = $emptyRow;
                    }
                }

                $text[] = $this->setFooter($this->toCurrency($totalTagihanPerPage), $this->toCurrency($totalTagihanUntilPage));
                // $text[] = '';

                if ($number == $this->rowCount) {
                    $text[] = $this->setLastCover($lastPageData);
                }


                $counter = 0;
                $totalTagihanPerPage = 0;
                $page++;
            }

            $number++;
            $counter++;
        }

        return implode($this->getBR(), $text);
    }

    public function setFooter($totalTagihanPerPage, $totalTagihanUntilPage)
    {
        $text = array();
        $text[] = $this->repeatText('=', $this->totalChars);
        $text[] = $this->setTableRow(array('foot_desc' => array('text' => '    Total Halaman Ini'), 'foot_val' => array('text' => $totalTagihanPerPage . $this->repeatText(' ', ($this->columnLength['paraf'] + 1)), 'pad' => 'left')));
        $text[] = $this->setTableRow(array('foot_desc' => array('text' => '    Total Sampai Dengan Halaman Ini'), 'foot_val' => array('text' => $totalTagihanUntilPage . $this->repeatText(' ', ($this->columnLength['paraf'] + 1)), 'pad' => 'left')));
        $text[] = $this->repeatText('=', $this->totalChars);

        return implode($this->getBR(), $text);
    }

    public function setLastCover(array $data)
    {
        $totalNop = 0;
        $totalLt = 0;
        $totalLb = 0;
        $totalPajak = 0;

        $text = array();
        $text[] = $this->rightText('Halaman ' . $this->totalPages . ' dari ' . $this->totalPages);
        $text[] = $this->centerText('PEMERINTAH KABUPATEN ' . strtoupper($this->appConfig['KANWIL']));
        $text[] = $this->centerText('BADAN PENDAPATAN DAERAH');
        $text[] = $this->getBR(3);
        $text[] = $this->padRight($this->padLeft($this->padRight('PROPINSI', 20), $this->totalChars / 2) . ' : ' . $this->kodePropinsi . ' - ' . $this->namaPropinsi, ($this->totalChars / 2));
        $text[] = $this->padRight($this->padLeft($this->padRight('KABUPATEN', 20), $this->totalChars / 2) . ' : ' . $this->kodeKota . ' - ' . $this->namaKota, ($this->totalChars / 2));
        $text[] = $this->padRight($this->padLeft($this->padRight('KECAMATAN', 20), $this->totalChars / 2) . ' : ' . $this->kodeKecamatan . ' - ' . $this->namaKecamatan, ($this->totalChars / 2));
        $text[] = $this->padRight($this->padLeft($this->padRight('KELURAHAN/PEKON', 20), $this->totalChars / 2) . ' : ' . $this->kodeKelurahan . ' - ' . $this->namaKelurahan, ($this->totalChars / 2));
        $text[] = $this->getBR(2);
        $text[] = $this->centerText('PAJAK BUMI DAN BANGUNAN');
        $text[] = $this->centerText('TAHUN : ' . $this->thn);
        $text[] = $this->getBR(2);
        $text[] = $this->centerText($this->padRight('DAFTAR INI TERDIRI ATAS', 29) . ' : ' . $this->totalPages . ' HALAMAN');
        $text[] = $this->centerText($this->padRight('JUMLAH STTS SEBANYAK', 29) . ' : ' . $this->rowCount . ' LEMBAR');
        $text[] = $this->centerText($this->padRight('JUMLAH SPPT SEBANYAK', 29) . ' : ' . $this->rowCount . ' LEMBAR');
        $text[] = $this->getBR(2);
        $text[] = $this->centerText($this->repeatText('=', 94));
        $text[] = $this->centerText($this->setTableRow(array(
            'buku'      => array('text' => 'Buku', 'center' => true),
            'jumlah_op' => array('text' => 'Jumlah Objek ', 'pad' => 'left'),
            'lt'        => array('text' => 'Luas Tanah ', 'pad' => 'left'),
            'lb'        => array('text' => 'Luas Bangunan ', 'pad' => 'left'),
            'total'     => array('text' => 'Pokok Ketetapan ', 'pad' => 'left'),
        )));
        $text[] = $this->centerText($this->repeatText('=', 94));

        foreach ($data as $buku => $attr) {
            $totalNop += $attr['nop'];
            $totalLt += $attr['lt'];
            $totalLb += $attr['lb'];
            $totalPajak += $attr['total'];

            $text[] = $this->centerText($this->setTableRow(array(
                'buku'      => array('text' => $buku . ' ', 'pad' => 'left'),
                'jumlah_op' => array('text' => $this->toCurrency($attr['nop']) . ' ', 'pad' => 'left'),
                'lt'        => array('text' => $this->toCurrency($attr['lt']) . ' ', 'pad' => 'left'),
                'lb'        => array('text' => $this->toCurrency($attr['lb']) . ' ', 'pad' => 'left'),
                'total'     => array('text' => $this->toCurrency($attr['total']) . ' ', 'pad' => 'left'),
            )));
        }
        $text[] = $this->centerText($this->repeatText('=', 94));
        $text[] = $this->centerText($this->setTableRow(array(
            'buku'      => array('text' => 'Jumlah', 'center' => true),
            'jumlah_op' => array('text' => $this->toCurrency($totalNop) . ' ', 'pad' => 'left'),
            'lt'        => array('text' => $this->toCurrency($totalLt) . ' ', 'pad' => 'left'),
            'lb'        => array('text' => $this->toCurrency($totalLb) . ' ', 'pad' => 'left'),
            'total'     => array('text' => $this->toCurrency($totalPajak) . ' ', 'pad' => 'left'),
        )));
        $text[] = $this->centerText($this->repeatText('=', 94));
        $text[] = $this->centerText(strtoupper(SayInIndonesian($totalPajak)));
        $text[] = $this->getBR(2);
        $text[] = $this->centerText($this->centerText(strtoupper($this->repeatText('.', 9) . ', ' . $this->tglTerbit . $this->thn), 50) . $this->centerText(strtoupper($this->repeatText('.', 9) . ', ' . $this->tglTerbit . $this->thn), 50));
        $text[] = $this->centerText($this->centerText(strtoupper('CAMAT ' . $this->namaKecamatan), 50) . $this->centerText(strtoupper('LURAH / KEPALA PEKON ' . $this->namaKelurahan), 50));
        $text[] = $this->getBR(4);
        $text[] = $this->centerText($this->centerText($this->repeatText('.', 25), 50) . $this->centerText($this->repeatText('.', 25), 50));
        $text[] = $this->getBR(3);
        $text[] = 'Perhatian :';
        $text[] = '- Halaman pertama dan terakhir ditanda tangani, halaman lainnya diparaf';
        $text[] = '- Pajak terhutang harus lunas selambat-lambatnya 6 (enam) bulan sejak diterima SPPT';
        $text[] = '- Text yang melebihi batas akan dipotong dan ditandai dengan bintang (*) diakhir text';

        return $this->formatToOnePage($text);
        // return implode($this->getBR(), $text);
    }

    public function setCover()
    {
        $text = array();
        $text[] = $this->rightText('Halaman 1 dari ' . $this->totalPages);
        $text[] = $this->getBR(3);
        $text[] = $this->centerText('PEMERINTAH KABUPATEN ' . strtoupper($this->appConfig['KANWIL']));
        $text[] = $this->getBR();
        $text[] = $this->centerText('BADAN PENDAPATAN DAERAH');
        $text[] = $this->getBR(4);
        $text[] = $this->getBigCoverText();
        $text[] = $this->getBR(4);
        $text[] = $this->centerText('( PAJAK BUMI DAN BANGUNAN )');
        $text[] = $this->centerText('BUKU ' . $this->buku);
        $text[] = $this->getBR();
        $text[] = $this->centerText('T A H U N : ' . $this->thn);
        $text[] = $this->getBR();
        $text[] = $this->centerText('TANGGAL TERBIT : ' . $this->tglTerbit .  $this->thn);
        $text[] = $this->getBR(4);
        $text[] = $this->padRight($this->padLeft($this->padRight('PROPINSI', 20), $this->totalChars / 2) . ' : ' . $this->kodePropinsi . ' - ' . $this->namaPropinsi, ($this->totalChars / 2));
        $text[] = $this->padRight($this->padLeft($this->padRight('KABUPATEN', 20), $this->totalChars / 2) . ' : ' . $this->kodeKota . ' - ' . $this->namaKota, ($this->totalChars / 2));
        $text[] = $this->padRight($this->padLeft($this->padRight('KECAMATAN', 20), $this->totalChars / 2) . ' : ' . $this->kodeKecamatan . ' - ' . $this->namaKecamatan, ($this->totalChars / 2));
        $text[] = $this->padRight($this->padLeft($this->padRight('KELURAHAN/PEKON', 20), $this->totalChars / 2) . ' : ' . $this->kodeKelurahan . ' - ' . $this->namaKelurahan, ($this->totalChars / 2));

        return $this->formatToOnePage($text);
        // return implode($this->getBR(), $text);
    }

    public function getBigCoverText()
    {
        $text = array();
        $text[] = $this->centerText('  _____      _      _   _   ____       _          _____   _____   ____    ___   __  __      _     ');
        $text[] = $this->centerText(' |_   _|    / \    | \ | | |  _ \     / \        |_   _| | ____| |  _ \  |_ _| |  \/  |    / \    ');
        $text[] = $this->centerText('   | |     / _ \   |  \| | | | | |   / _ \         | |   |  _|   | |_) |  | |  | |\/| |   / _ \   ');
        $text[] = $this->centerText('   | |    / ___ \  | |\  | | |_| |  / ___ \        | |   | |___  |  _ <   | |  | |  | |  / ___ \  ');
        $text[] = $this->centerText('   |_|   /_/   \_\ |_| \_| |____/  /_/   \_\       |_|   |_____| |_| \_\ |___| |_|  |_| /_/   \_\ ');
        $text[] = $this->centerText('                                                                                                  ');
        $text[] = $this->centerText('    ____    _____   _   _  __   __     _      __  __   ____       _      ___      _      _   _    ');
        $text[] = $this->centerText('   |  _ \  | ____| | \ | | \ \ / /    / \    |  \/  | |  _ \     / \    |_ _|    / \    | \ | |   ');
        $text[] = $this->centerText('   | |_) | |  _|   |  \| |  \ V /    / _ \   | |\/| | | |_) |   / _ \    | |    / _ \   |  \| |   ');
        $text[] = $this->centerText('   |  __/  | |___  | |\  |   | |    / ___ \  | |  | | |  __/   / ___ \   | |   / ___ \  | |\  |   ');
        $text[] = $this->centerText('   |_|     |_____| |_| \_|   |_|   /_/   \_\ |_|  |_| |_|     /_/   \_\ |___| /_/   \_\ |_| \_|   ');
        $text[] = $this->centerText('                                                                                                  ');
        $text[] = $this->centerText('        ____    ____    ____    _____     ____    ____    ____            ____    ____            ');
        $text[] = $this->centerText('       / ___|  |  _ \  |  _ \  |_   _|   |  _ \  | __ )  | __ )          |  _ \  |___ \           ');
        $text[] = $this->centerText('       \___ \  | |_) | | |_) |   | |     | |_) | |  _ \  |  _ \   _____  | |_) |   __) |          ');
        $text[] = $this->centerText('        ___) | |  __/  |  __/    | |     |  __/  | |_) | | |_) | |_____| |  __/   / __/           ');
        $text[] = $this->centerText('       |____/  |_|     |_|       |_|     |_|     |____/  |____/          |_|     |_____|          ');

        return implode($this->getBR(), $text);
    }

    public function setHeader($page)
    {

        $text = array();
        $text[] = $this->rightText('Halaman ' . $page . ' dari ' . $this->totalPages);
        // $text[] = $this->getBR();
        // $text[] = $this->centerText('TANDA TERIMA PENYAMPAIAN SPPT PBB-P2 TAHUN ' . $this->thn);
        // $text[] = $this->centerText('TAHUN : ' . $this->thn);
        // $text[] = $this->getBR(2);
        $text[] = $this->padRight(($this->padRight('PROPINSI', 9) . ' : ' . $this->kodePropinsi . ' - ' . $this->namaPropinsi), ($this->totalChars / 2)) . $this->padRight('KECAMATAN', 16) . ' : ' . $this->kodeKecamatan . ' - ' . $this->namaKecamatan;
        $text[] = $this->padRight(($this->padRight('KABUPATEN', 9) . ' : ' . $this->kodeKota . ' - ' . $this->namaKota), ($this->totalChars / 2)) . $this->padRight('KELURAHAN/PEKON', 16) . ' : ' . $this->kodeKelurahan . ' - ' . $this->namaKelurahan;
        // $text[] = $this->getBR();
        $text[] = $this->repeatText('=', $this->totalChars);
        $text[] = $this->headerRow;
        $text[] = $this->repeatText('=', $this->totalChars);

        return implode($this->getBR(), $text);
    }
}
