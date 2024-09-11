<?php
date_default_timezone_set("Asia/Jakarta");
require_once($sRootPath . "inc/payment/sayit.php");
// ALDES
class CetakDHKP
{
    public $totalChars = 136;
    public $perPageList = 22;
    public $perPageMaxLine = 66;

    public $formatHtml = false;

    public $DBLink;
    public $thn;
    public $stsPenetapan;
    public $appConfig;
    public $qBuku;
    public $buku;
    public $namaKecamatan;
    public $kecamatan;
    public $namaKelurahan;
    public $kelurahan;
    public $kodePropinsi;
    public $namaPropinsi;
    public $kodeKota;
    public $namaKota;
    public $kodeKecamatan;
    public $kodeKelurahan;
    public $nop;

    public $totalPages;
    public $rowCount;

    public $summaryakhir;

    public $columnLength = array(
        'nomor'         => 6,
        'nop'           => 10,
        'nomor_induk'   => 11,
        'nama_wp'       => 38,
        'alamat_op'     => 49,
        'alamat_wp'     => 49,
        'tagihan'       => 15,
        'foot_desc'     => 68,
        'foot_val'      => 65,
        'buku'          => 6,
        'jumlah_op'     => 20,
        'lt'            => 19,
        'lb'            => 22,
        'total'         => 21
    );

    public $tglTerbit = '01 MARET ';
    public $tempatPembayaran = 'BANK LAMPUNG';

    public $emptyRow;
    public $headerRow;

    public $fromPage = null;
    public $toPage = null;

    public $bulan = array('JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER');
	public $spacingHeaderHalaman;
    public function __construct($DBLink, $kecamatan, $thn, $kelurahan, $stsPenetapan, $appConfig, $qBuku, $namaKecamatan, $namaKelurahan, $buku, $nop)
    {
        $this->DBLink = $DBLink;
        $this->kecamatan = $kecamatan;
        $this->thn = $thn;
        $this->kelurahan = $kelurahan;
        $this->stsPenetapan = $stsPenetapan;
        $this->appConfig = $appConfig;
        $this->qBuku = $qBuku;
        $this->namaKecamatan = $namaKecamatan;
        $this->namaKelurahan = $namaKelurahan;
        $this->nop = $nop;
        $this->buku = implode(',', (array) str_split($buku));
		$this->tglTerbit = $appConfig['DHKP_TGL_TERBIT'];

        $this->kodePropinsi   = $appConfig['KODE_PROVINSI'];
        $this->namaPropinsi   = $appConfig['NAMA_PROVINSI'];
        $this->kodeKota       = str_replace($this->kodePropinsi, '', $appConfig['KODE_KOTA']);
        $this->namaKota       = $appConfig['NAMA_KOTA'];
        $this->kodeKecamatan  = str_replace($appConfig['KODE_KOTA'], '', $kecamatan);
        $this->kodeKelurahan  = str_replace($kecamatan, '', $kelurahan);

        $this->emptyRow = $this->setTableRow(array('nomor' => array(), 'nop' => array(), 'nomor_induk' => array(), 'nama_wp' => array(), 'alamat_wp' => array(), 'tagihan' => array()));
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
                'nomor_induk' => array(
                    'text' => 'NOMOR INDUK',
                    'center' => true
                ),
                'nama_wp' => array(
                    'text' => 'NAMA WAJIB PAJAK',
                ),
                'alamat_op' => array(
                    'text' => 'ALAMAT OBJEK PAJAK',
                ),
                'tagihan' => array(
                    'text' => 'PAJAK TERHUTANG',
                    'center' => true
                )
            )),
            $this->setTableRow(array(
                'nomor' => array(), 
                'nop' => array(),
                'nomor_induk' => array(),
                'nama_wp' => array(), 
                'alamat_wp' => array('text' => 'WAJIB PAJAK', 'pad' => 'left', 'pad_length' => 18), 
                'tagihan' => array()
                ))
        ]);

        //$this->tglTerbit = date('d') . ' ' . $this->bulan[(date('n') - 1)] . ' ';
		$this->spacingHeaderHalaman = $this->repeatText(' ', 5);

        $this->summaryakhir = array('jmlop'=>0,'luasbumi'=>0,'luasbgn'=>0,'ketetapan'=>0);
    }

    public function setFromPage($page = null)
    {
        $this->fromPage = (is_numeric($page) ? $page : null);
        return $this;
    }

    public function setToPage($page = null)
    {
        $this->toPage = (is_numeric($page) ? $page : null);
        return $this;
    }

    public function getFromPage()
    {
        return $this->fromPage != null && $this->fromPage >= 1 ? $this->fromPage : 1;
    }

    public function getToPage()
    {
        return $this->toPage != null && $this->toPage <= $this->totalPages ? $this->toPage : $this->totalPages;
    }

    public function formatHtml()
    {
        $this->formatHtml = true;
    }

    public function getQuery()
    {
        if ($this->kelurahan == "") {
            $filter = $this->kecamatan;
        } else {
            $filter = $this->kelurahan;
        }

        if($this->nop) {
            $nop = array();

            foreach (explode(',', trim($this->nop)) as $n) {
                $nop[] = "'". trim($n) ."'";
            }
            if(!empty($nop)){
                $filterNop = ' AND NOP IN ('. implode(',', $nop) .')';
            }else {
                $filterNop = " AND NOP LIKE '{$filter}%'";
            }
        }else {
            $filterNop = " AND NOP LIKE '{$filter}%'";
        }

        $filStsPenetapan = "";
        if ($this->stsPenetapan == "1") {
            $filStsPenetapan = "AND FLAG_SUSULAN = 1";
        } else if ($this->stsPenetapan == "0") {
            $filStsPenetapan = "AND (FLAG_SUSULAN <> 1 OR FLAG_SUSULAN IS NULL)";
        }

        if ($this->thn == $this->appConfig['tahun_tagihan']) {
            $table = 'cppmod_pbb_sppt_current';
        } else {
            $table = "cppmod_pbb_sppt_cetak_{$this->thn}";
        }
        $query = "SELECT * FROM {$table} WHERE NOP<>'' AND SPPT_PBB_HARUS_DIBAYAR>'0' AND SPPT_TAHUN_PAJAK = '{$this->thn}' {$this->qBuku} {$filStsPenetapan} {$filterNop}";

        $res = mysqli_query($this->DBLink, $query);

        if ($res === false) {
            echo mysqli_error($this->DBLink);
            exit();
        }

        $this->rowCount = mysqli_num_rows($res);
        // + 2 = cover + lastcover
        $this->totalPages = ceil($this->rowCount / $this->perPageList) + 2;

        /// add by d3Di --- cari summary seluruhnya
        $queryx = "SELECT 
                        OP_LUAS_BUMI AS lt, 
                        OP_LUAS_BANGUNAN AS lb,
                        SPPT_PBB_HARUS_DIBAYAR AS tagihan
                FROM {$table} 
                WHERE 
                    SPPT_PBB_HARUS_DIBAYAR > 0 AND SPPT_TAHUN_PAJAK = '{$this->thn}' {$this->qBuku} {$filStsPenetapan} {$filterNop}";
        // print_r($query);exit;
        $summary = mysqli_query($this->DBLink, $queryx);

        $this->summaryakhir = $summary;//mysqli_fetch_assoc($summary);
        // print_r($this->summaryakhir);exit;

        return $res;
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

        if ($this->getFromPage() == 1) {
            $text[] = $this->setCover();
        }

        /// edit by Deris 
        $rowsum = $this->summaryakhir;
        while ($rowx = mysqli_fetch_assoc($rowsum)) {
            $tagihan        = ($rowx["tagihan"] != "") ? $rowx["tagihan"] : 0;
            $luas_tanah     = ($rowx["lt"] != "") ? $rowx["lt"] : 0;
            $luas_bangunan  = ($rowx["lb"] != "") ? $rowx["lb"] : 0;

            $lastPageData[$this->getBuku($tagihan)]['nop']      += 1;
            $lastPageData[$this->getBuku($tagihan)]['lt']       += $luas_tanah;
            $lastPageData[$this->getBuku($tagihan)]['lb']       += $luas_bangunan;
            $lastPageData[$this->getBuku($tagihan)]['total']    += $tagihan;
        }
        //-----------------------------------------------------------------

        while ($row = mysqli_fetch_assoc($res)) {
            $nop            = ($row["NOP"] != "") ? $row["NOP"] : "";
            $nama           = ($row["WP_NAMA"] != "") ? $row["WP_NAMA"] : "";
            $alamat_wp      = ($row["WP_ALAMAT"] != "") ? $row["WP_ALAMAT"] : "";
            $alamat_op      = ($row["OP_ALAMAT"] != "") ? $row["OP_ALAMAT"] : "";
            $tagihan        = ($row["SPPT_PBB_HARUS_DIBAYAR"] != "") ? $row["SPPT_PBB_HARUS_DIBAYAR"] : 0;
            $luas_tanah     = ($row["OP_LUAS_BUMI"] != "") ? $row["OP_LUAS_BUMI"] : 0;
            $luas_bangunan  = ($row["OP_LUAS_BANGUNAN"] != "") ? $row["OP_LUAS_BANGUNAN"] : 0;

            if ($page >= $this->getFromPage() && $page <= $this->getToPage()) {
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
                        'pad' => 'left'
                    ),
                    'nomor_induk' => array(
                        'text' => '',
                    ),
                    'nama_wp' => array(
                        'text' => $this->strLimit($nama, $this->columnLength['nama_wp']),
                    ),
                    'alamat_op' => array(
                        'text' => $this->strLimit($alamat_op, $this->columnLength['alamat_op']),
                    ),
                    'tagihan' => array(
                        'text' => $this->toCurrency($tagihan),
                        'pad' => 'left'
                    )
                ));

                
                $text[] = $this->setTableRow(array(
                    'nomor' => array(), 
                    'nop' => array(), 
                    'nomor_induk' => array(), 
                    'nama_wp' => array(), 
                    'alamat_wp' => array('text' => $this->strLimit($alamat_wp, $this->columnLength['alamat_wp'])), 
                    'tagihan' => array()
                ));
                // $text[] = $emptyRow;

                // if(
                //     strlen($number) > $this->columnLength['nomor'] &&
                //     strlen($this->formatNOP($nop)) > $this->columnLength['nop'] &&
                //     strlen($nama) > $this->columnLength['nama_wp'] &&
                //     strlen($alamat_op) > $this->columnLength['alamat_op'] &&
                //     strlen($alamat_wp) > $this->columnLength['alamat_wp'] &&
                //     strlen($tagihan) > $this->columnLength['tagihan']
                //     ) {
                //         die('hehe');
                //     }

                $totalTagihanPerPage += $tagihan;
                $totalTagihanUntilPage += $tagihan;

                // $lastPageData[$this->getBuku($tagihan)]['nop']      += 1;
                // $lastPageData[$this->getBuku($tagihan)]['lt']       += $luas_tanah;
                // $lastPageData[$this->getBuku($tagihan)]['lb']       += $luas_bangunan;
                // $lastPageData[$this->getBuku($tagihan)]['total']    += $tagihan;
            }

            if ($counter == $this->perPageList || $number == $this->rowCount) {

                if ($page >= $this->getFromPage() && $page <= $this->getToPage()) {
                    if ($counter < $this->perPageList) {
                        $repeat = $this->perPageList - $counter;
                        for ($i = 0; $i < $repeat; $i++) {
                            // $text[] = $emptyRow;
                            $text[] = $emptyRow; // alamat wp
                            $text[] = $emptyRow; // spacing
                        }
                    }

                    $text[] = $this->setFooter($this->toCurrency($totalTagihanPerPage), $this->toCurrency($totalTagihanUntilPage));
                    $text[] = $this->getBR(4);
                }

                if ($number == $this->rowCount && $this->getToPage() == $this->totalPages) {
                    $text[] = $this->setLastCover($lastPageData);
                    $text[] = '';
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

    public function setCover()
    {
        $text = array();
		$text[] = $this->getBR();
        $text[] = $this->rightText('Halaman 1 dari ' . $this->totalPages);
        // $text[] = $this->getBR(3);
        $text[] = $this->centerText('PEMERINTAH '. strtoupper($this->appConfig['C_KABKOT']) .' ' . strtoupper($this->appConfig['KANWIL']));
        $text[] = $this->centerText('BADAN PENDAPATAN DAERAH');
        $text[] = $this->centerText('KOMPLEK PERKANTORAN PEMKAB. PESAWARAN JL. RAYA KEDONDONG BINONG DESA WAY LAYAP, GEDONG TATAAN');
        $text[] = $this->centerText('KODE POS 35371 TELP/FAX (0721) 5620580');
        $text[] = $this->getBR();
        // $text[] = $this->centerText('BADAN PENDAPATAN DAERAH');
        $text[] = $this->getBR(4);
        $text[] = $this->getDHKP();
        $text[] = $this->getBR(4);
        $text[] = $this->centerText('( DAFTAR HIMPUNAN KETETAPAN PAJAK & PEMBAYARAN )');
        $text[] = $this->centerText('( PAJAK BUMI DAN BANGUNAN )');
        $text[] = $this->centerText('BUKU ' . $this->buku);
        $text[] = $this->getBR();
        $text[] = $this->centerText('T A H U N : ' . $this->thn);
        $text[] = $this->getBR();
        $text[] = $this->centerText('TANGGAL TERBIT : ' . $this->tglTerbit);
        $text[] = $this->getBR(4);
        $text[] = $this->padRight($this->padLeft($this->padRight('PROPINSI', 20), $this->totalChars / 2) . ' : ' . $this->kodePropinsi . ' - ' . $this->namaPropinsi, ($this->totalChars / 2));
        $text[] = $this->padRight($this->padLeft($this->padRight('DATI II', 20), $this->totalChars / 2) . ' : ' . $this->kodeKota . ' - ' . $this->namaKota, ($this->totalChars / 2));
        
        if ($this->kecamatan) {
            $text[] = $this->padRight($this->padLeft($this->padRight('KECAMATAN', 20), $this->totalChars / 2) . ' : ' . $this->kodeKecamatan . ' - ' . $this->namaKecamatan, ($this->totalChars / 2));
        } else {
            $text[] = "";
        }

        if ($this->kelurahan) {
            $text[] = $this->padRight($this->padLeft($this->padRight('KELURAHAN/PEKON', 20), $this->totalChars / 2) . ' : ' . $this->kodeKelurahan . ' - ' . $this->namaKelurahan, ($this->totalChars / 2));
        } else {
            $text[] = "";
        }
        return $this->formatToOnePage($text);
        // return implode($this->getBR(), $text);
    }

    public function setLastCover(array $data)
    {
        $totalNop = 0;
        $totalLt = 0;
        $totalLb = 0;
        $totalPajak = 0;

        $text = array();
		$text[] = $this->getBR();
        $text[] = $this->rightText('Halaman ' . $this->totalPages . ' dari ' . $this->totalPages);
        // $text[] = $this->centerText('PEMERINTAH KABUPATEN ' . strtoupper($this->appConfig['KANWIL']));
        // $text[] = $this->centerText('BADAN PENDAPATAN DAERAH');
        $text[] = $this->centerText('PEMERINTAH '. strtoupper($this->appConfig['C_KABKOT']) .' ' . strtoupper($this->appConfig['KANWIL']));
        $text[] = $this->centerText('BADAN PENDAPATAN DAERAH');
        $text[] = $this->centerText('KOMPLEK PERKANTORAN PEMKAB. PESAWARAN JL. RAYA KEDONDONG BINONG DESA WAY LAYAP, GEDONG TATAAN');
        $text[] = $this->centerText('KODE POS 35371 TELP/FAX (0721) 5620580');
        $text[] = $this->getBR();
        $text[] = $this->padRight($this->padLeft($this->padRight('PROPINSI', 20), $this->totalChars / 2) . ' : ' . $this->kodePropinsi . ' - ' . $this->namaPropinsi, ($this->totalChars / 2));
        $text[] = $this->padRight($this->padLeft($this->padRight('DATI II', 20), $this->totalChars / 2) . ' : ' . $this->kodeKota . ' - ' . $this->namaKota, ($this->totalChars / 2));
        
        if ($this->kecamatan) {
            $text[] = $this->padRight($this->padLeft($this->padRight('KECAMATAN', 20), $this->totalChars / 2) . ' : ' . $this->kodeKecamatan . ' - ' . $this->namaKecamatan, ($this->totalChars / 2));
        } else {
            $text[] = "";
        }

        if ($this->kelurahan) {
            $text[] = $this->padRight($this->padLeft($this->padRight('KELURAHAN/PEKON', 20), $this->totalChars / 2) . ' : ' . $this->kodeKelurahan . ' - ' . $this->namaKelurahan, ($this->totalChars / 2));
        } else {
            $text[] = "";
        }
        $text[] = $this->getBR(2);
        $text[] = $this->centerText('DAFTAR HIMPUNAN KETETAPAN PAJAK & PEMBAYARAN BUKU ' . $this->buku);
        $text[] = $this->centerText('( DHKP )');
        $text[] = $this->centerText('PAJAK BUMI DAN BANGUNAN');
        $text[] = $this->centerText('TAHUN : ' . $this->thn);
        $text[] = $this->getBR(2);
        $text[] = $this->centerText($this->padRight('DAFTAR INI TERDIRI ATAS', 29) . ' :' . $this->padLeft($this->totalPages, 6) . ' HALAMAN');
        //$text[] = $this->centerText($this->padRight('JUMLAH STTS SEBANYAK', 29) . ' : ' . $this->rowCount . ' LEMBAR ');
        $text[] = $this->centerText($this->padRight('JUMLAH SPPT SEBANYAK', 29) . ' :' . $this->padLeft($this->rowCount, 6) . ' LEMBAR ');
        $text[] = $this->getBR(2);
        $text[] = $this->centerText($this->repeatText('=', 94));
        // RangeTtile
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

        // foreach ($data as $buku => $attr) {
        //     $totalNop += $attr['nop'];
        //     $totalLt += $attr['lt'];
        //     $totalLb += $attr['lb'];
        //     $totalPajak += $attr['total'];

        //     $text[] = $this->centerText($this->setTableRow(array(
        //         'buku'      => array('text' => $buku . ' ', 'pad' => 'left'),
        //         'jumlah_op' => array('text' => $this->toCurrency($attr['nop']) . ' ', 'pad' => 'left'),
        //         'lt'        => array('text' => $this->toCurrency($attr['lt']) . ' ', 'pad' => 'left'),
        //         'lb'        => array('text' => $this->toCurrency($attr['lb']) . ' ', 'pad' => 'left'),
        //         'total'     => array('text' => $this->toCurrency($attr['total']) . ' ', 'pad' => 'left'),
        //     )));
        // }
        $text[] = $this->centerText($this->repeatText('=', 94));

        // RangeTtile
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
        //$text[] = $this->centerText(strtoupper($this->appConfig['KANWIL']) . ', ' . $this->tglTerbit . $this->thn);
		$text[] = $this->centerText(strtoupper($this->appConfig['KANWIL']) . ', ' . $this->tglTerbit);
        $text[] = $this->centerText(strtoupper($this->appConfig['PEJABAT_SK2']));
        $text[] = $this->getBR(4);
        $text[] = $this->centerText(strtoupper($this->appConfig['NAMA_PEJABAT_SK2']));
        $text[] = $this->centerText('NIP ' . strtoupper($this->appConfig['NAMA_PEJABAT_SK2_NIP']));
        $text[] = $this->getBR(3);
        $text[] = 'Perhatian :';
        $text[] = '- Halaman pertama dan terakhir ditanda tangani, halaman lainnya diparaf';
        $text[] = '- Pajak terhutang harus lunas selambat-lambatnya 6 (enam) bulan sejak diterima SPPT';

        return $this->formatToOnePage($text);
        // return implode($this->getBR(), $text);
    }

    public function setTableRow(array $data)
    {
        $rowText = array();
        foreach ($data as $key => $attr) {
            if (!isset($this->columnLength[$key])) {
                continue;
            }

            $length = $this->columnLength[$key];
            $text = isset($attr['text']) ? $attr['text'] : '';
            $center = isset($attr['center']) ? $attr['center'] : false;
            $pad = isset($attr['pad']) ? $attr['pad'] : false;
            $pad_length = isset($attr['pad_length']) ? $attr['pad_length'] : $length;

            if ($center) {
                $text = $this->centerText($text, $length);
            } elseif ($pad && $pad == 'left') {
                $text = $this->padLeft($text, $pad_length);
            }

            $text = $this->padRight($text, $length);

            $rowText[] = $text;
        }

        return '|' . implode('|', $rowText) . '|';
    }

    public function setHeader($page)
    {

        $headerKelurahan = "";
        $headerKecamatan = "";
        
        if ($this->kelurahan) {
            $headerKelurahan = $this->padRight('KELURAHAN/PEKON', 16) . ' : ' . $this->kodeKelurahan . ' - ' . $this->namaKelurahan;
        }
        if ($this->kecamatan) {
            $headerKecamatan = $this->padRight('KECAMATAN', 16) . ' : ' . $this->kodeKecamatan . ' - ' . $this->namaKecamatan;
        }

        $text = array();
		$text[] = $this->getBR();
        $text[] = $this->rightText('Halaman ' . $page . ' dari ' . $this->totalPages . $this->spacingHeaderHalaman);
        $text[] = $this->centerText('DAFTAR HIMPUNAN KETETAPAN PAJAK & PEMBAYARAN BUKU ' . $this->buku);
        $text[] = $this->centerText('TAHUN : ' . $this->thn);
        // $text[] = $this->getBR();
        $text[] = $this->padRight(($this->padRight(' ', 18) ), ($this->totalChars / 2));
        $text[] = $this->padRight(($this->padRight('TEMPAT PEMBAYARAN', 18) . ' : ' . $this->tempatPembayaran), ($this->totalChars / 2));
        $text[] = $this->padRight(($this->padRight('PROPINSI', 18) . ' : ' . $this->kodePropinsi . ' - ' . $this->namaPropinsi), ($this->totalChars / 2)) . $headerKecamatan;
        $text[] = $this->padRight(($this->padRight('DATI II', 18) . ' : ' . $this->kodeKota . ' - ' . $this->namaKota), ($this->totalChars / 2)) . $headerKelurahan;
        // $text[] = $this->getBR();
        $text[] = $this->repeatText('=', $this->totalChars);
        $text[] = $this->headerRow;
        $text[] = $this->repeatText('=', $this->totalChars);

        return implode($this->getBR(), $text);
    }

    public function setFooter($totalTagihanPerPage, $totalTagihanUntilPage)
    {
        $text = array();
        $text[] = $this->repeatText('=', $this->totalChars);
        $text[] = $this->setTableRow(array('foot_desc' => array('text' => ' Total Halaman Ini'), 'foot_val' => array('text' => $totalTagihanPerPage, 'pad' => 'left')));
        $text[] = $this->setTableRow(array('foot_desc' => array('text' => ' Total Sampai Dengan Halaman Ini'), 'foot_val' => array('text' => $totalTagihanUntilPage, 'pad' => 'left')));
        $text[] = $this->repeatText('=', $this->totalChars);

        return implode($this->getBR(), $text);
    }

    public function centerText($text, $totalContainerChars = null)
    {
        $totalChars = ($totalContainerChars === null ? $this->totalChars : $totalContainerChars);
        return str_pad($text, $totalChars, ' ', STR_PAD_BOTH);
    }

    public function rightText($text)
    {
        return $this->padLeft($text, $this->totalChars);
    }

    public function padLeft($text, $length, $string = ' ')
    {
        return str_pad($text, $length, $string, STR_PAD_LEFT);
    }

    public function padRight($text, $length, $string = ' ')
    {
        return str_pad($text, $length, $string, STR_PAD_RIGHT);
    }

    public function repeatText($text, $repeat = 1)
    {
        return str_repeat($text, $repeat);
    }

    public function formatNOP($nop)
    {
        return substr($nop, -8, 3) . '.' . substr($nop, -5, 4) . '-' . substr($nop, -1, 1);
    }

    public function formatToOnePage(array $text)
    {
        $imploded = implode($this->getBR(), $text);
        $currentLine = count(explode($this->getBR(), $imploded));

        $mustAddLine = $this->perPageMaxLine - $currentLine;

        return $imploded . $this->getBR($mustAddLine);
    }

    public function toCurrency($value)
    {
        return number_format($value, '0', ',', '.');
    }

    public function getBuku($tagihan)
    {
        switch ($tagihan) {
            case ($tagihan >= 0) && ($tagihan <= 100000):
                $buku = 1;
                break;
            case ($tagihan >= 100001) && ($tagihan <= 500000):
                $buku = 2;
                break;
            case ($tagihan >= 500001) && ($tagihan <= 2000000):
                $buku = 3;
                break;
            case ($tagihan >= 2000001) && ($tagihan <= 5000000):
                $buku = 4;
                break;
            case ($tagihan >= 5000001):
                $buku = 5;
                break;
        }

        return $buku;
    }

    public function getDHKP()
    {
        $text = array();
        $text[] = $this->centerText('DDDDDDDDDDDDD             HHHHHHHHH     HHHHHHHHH     KKKKKKKKK    KKKKKKK     PPPPPPPPPPPPPPPPP   ');
        $text[] = $this->centerText('D::::::::::::DDD          H:::::::H     H:::::::H     K:::::::K    K:::::K     P::::::::::::::::P  ');
        $text[] = $this->centerText('D:::::::::::::::DD        H:::::::H     H:::::::H     K:::::::K    K:::::K     P::::::PPPPPP:::::P ');
        $text[] = $this->centerText('DDD:::::DDDDD:::::D       HH::::::H     H::::::HH     K:::::::K   K::::::K     PP:::::P     P:::::P');
        $text[] = $this->centerText('  D:::::D    D:::::D        H:::::H     H:::::H       KK::::::K  K:::::KKK       P::::P     P:::::P');
        $text[] = $this->centerText('  D:::::D     D:::::D       H:::::H     H:::::H         K:::::K K:::::K          P::::P     P:::::P');
        $text[] = $this->centerText('  D:::::D     D:::::D       H::::::HHHHH::::::H         K::::::K:::::K           P::::PPPPPP:::::P ');
        $text[] = $this->centerText('  D:::::D     D:::::D       H:::::::::::::::::H         K:::::::::::K            P:::::::::::::PP  ');
        $text[] = $this->centerText('  D:::::D     D:::::D       H:::::::::::::::::H         K:::::::::::K            P::::PPPPPPPPP    ');
        $text[] = $this->centerText('  D:::::D     D:::::D       H::::::HHHHH::::::H         K::::::K:::::K           P::::P            ');
        $text[] = $this->centerText('  D:::::D     D:::::D       H:::::H     H:::::H         K:::::K K:::::K          P::::P            ');
        $text[] = $this->centerText('  D:::::D    D:::::D        H:::::H     H:::::H       KK::::::K  K:::::KKK       P::::P            ');
        $text[] = $this->centerText('DDD:::::DDDDD:::::D       HH::::::H     H::::::HH     K:::::::K   K::::::K     PP::::::PP          ');
        $text[] = $this->centerText('D:::::::::::::::DD        H:::::::H     H:::::::H     K:::::::K    K:::::K     P::::::::P          ');
        $text[] = $this->centerText('D::::::::::::DDD          H:::::::H     H:::::::H     K:::::::K    K:::::K     P::::::::P          ');
        $text[] = $this->centerText('DDDDDDDDDDDDD             HHHHHHHHH     HHHHHHHHH     KKKKKKKKK    KKKKKKK     PPPPPPPPPP          ');

        return implode($this->getBR(), $text);
    }

    public function getBR($loop = 1)
    {

        if ($this->formatHtml) {
            $breakline = '<br />';
        } else {
            $breakline = chr(13) . chr(10);
        }

        return $this->repeatText($breakline, $loop);
    }

    public function getHtmlHead()
    {
        if (!$this->formatHtml) return '';

        $html = "";
        $html .= "<!DOCTYPE html>";
        $html .= "<html>";
        $html .= "<head>";
        $html .= "<meta charset=\"UTF-8\">";;
        $html .= "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">";
        $html .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">";
        $html .= "<title>SPPT</title>";
        $html .= "<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css\" integrity=\"sha512-NhSC1YmyruXifcj/KFRWoC561YpHpc5Jtzgvbuzx5VozKpWvQ+4nXhPdFgmx8xqexRcpAglTj9sIBWINXa8x5w==\" crossorigin=\"anonymous\" />";
        $html .= "<style>";
        $html .= $this->getHtmlStyles();
        $html .= "</style>";
        $html .= "</head>";
        $html .= "<body>";
        $html .= "<pre style=\"font-family: 'Courier New', Courier, monospace\">";

        return $html;
    }

    public function getHtmlFoot()
    {
        if (!$this->formatHtml) return '';

        $html = "</pre>";
        $html .= "</body>";
        $html .= "</html>";

        return $html;
    }

    public function getHtmlStyles()
    {
        $styles[] = '@page { size: 37cm 28.5cm; }';

        return implode("\r\n", $styles);
    }

    public function strLimit($value, $limit = 136, $end = '', $replaceLastChar = false)
    {

        if (strlen($value) <= $limit) {
            return $value;
        }

        if ($replaceLastChar) {
            $limit -= strlen($end);
        }

        return substr($value, 0, $limit) . $end;
    }
}
