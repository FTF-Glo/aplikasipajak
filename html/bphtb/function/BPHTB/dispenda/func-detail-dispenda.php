<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'dispenda', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/comm-central.php");

function dataDummy () {
	$data = "{'data':[{'CPM_SSB_ID':'12345678901234567890123456789011',
			  'CPM_KPP':'KPP UJICOBA',
			  'CPM_KPP_ID':'001',
			  'CPM_WP_NAMA':'PAK UJI BIN COBA-1',
			  'CPM_WP_NPWP':'123456789012345',
			  'CPM_WP_ALAMAT':'JL. UJICOBA NO.53',
			  'CPM_WP_RT':'04',
			  'CPM_WP_RW':'05',
			  'CPM_WP_KELURAHAN':'TESTING',
			  'CPM_WP_KECAMATAN':'TESTING',
			  'CPM_WP_KABUPATEN':'TESTING TENGAH',
			  'CPM_WP_KODEPOS':'40333',
			  'CPM_OP_NOMOR':'12345678901',
			  'CPM_OP_LETAK':'JL. UJICOBA NO.53',
			  'CPM_OP_RT':'04',
			  'CPM_OP_RW':'05',
			  'CPM_OP_KELURAHAN':'TESTING',
			  'CPM_OP_KECAMATAN':'TESTING',
			  'CPM_OP_KABUPATEN':'TESTING TENGAH',
			  'CPM_OP_KODEPOS':'40333',
			  'CPM_OP_THN_PEROLEH':'2011',
			  'CPM_OP_LUAS_TANAH':'1000',
			  'CPM_OP_LUAS_BANGUN':'500',
			  'CPM_OP_NJOP_TANAH':'130000',
			  'CPM_OP_NJOP_BANGUN':'130000', 
			  'CPM_OP_JENIS_HAK':'Jual Beli',
			  'CPM_OP_HARGA' :'200000000',
			  'CPM_OP_NMR_SERTIFIKAT':'123456789012345678901234567890',
			  'CPM_OP_NPOPTKP':'60000000', 
			  'CPM_PAYMENT_TIPE':'1',
			  'CPM_PAYMENT_TIPE_SURAT':'12345678',
			  'CPM_PAYMENT_TIPE_SURAT_NOMOR':'12345678901234567890',
			  'CPM_PAYMENT_TIPE_SURAT_TANGGAL':'12-12-2011',
			  'CPM_PAYMENT_TIPE_PENGURANGAN':'12',
			  'CPM_PAYMENT_TIPE_OTHER':'AABBCCDD',
			  'CPM_TRAN_DATE':'12-12-2011',
			  'CPM_TRAN_OPR':'1',
			  'CPM_TRAN_STATUS':'1',
			  'CPM_TRAN_SSB_VERS':'1.0'  
			  },{'CPM_SSB_ID':'12345678901234567890123456789012',
			  'CPM_KPP':'KPP UJICOBA',
			  'CPM_KPP_ID':'001',
			  'CPM_WP_NAMA':'PAK UJI BIN COBA-2',
			  'CPM_WP_NPWP':'123456789012345',
			  'CPM_WP_ALAMAT':'JL. UJICOBA NO.53',
			  'CPM_WP_RT':'04',
			  'CPM_WP_RW':'05',
			  'CPM_WP_KELURAHAN':'TESTING',
			  'CPM_WP_KECAMATAN':'TESTING',
			  'CPM_WP_KABUPATEN':'TESTING TENGAH',
			  'CPM_WP_KODEPOS':'40333',
			  'CPM_OP_NOMOR':'12345678901',
			  'CPM_OP_LETAK':'JL. UJICOBA NO.53',
			  'CPM_OP_RT':'04',
			  'CPM_OP_RW':'05',
			  'CPM_OP_KELURAHAN':'TESTING',
			  'CPM_OP_KECAMATAN':'TESTING',
			  'CPM_OP_KABUPATEN':'TESTING TENGAH',
			  'CPM_OP_KODEPOS':'40333',
			  'CPM_OP_THN_PEROLEH':'2011',
			  'CPM_OP_LUAS_TANAH':'1000',
			  'CPM_OP_LUAS_BANGUN':'500',
			  'CPM_OP_NJOP_TANAH':'130000',
			  'CPM_OP_NJOP_BANGUN':'130000', 
			  'CPM_OP_JENIS_HAK':'Jual Beli',
			  'CPM_OP_HARGA' :'200000000',
			  'CPM_OP_NMR_SERTIFIKAT':'123456789012345678901234567890',
			  'CPM_OP_NPOPTKP':'60000000',
			  'CPM_PAYMENT_TIPE':'1',
			  'CPM_PAYMENT_TIPE_SURAT':'12345678',
			  'CPM_PAYMENT_TIPE_SURAT_NOMOR':'12345678901234567890',
			  'CPM_PAYMENT_TIPE_SURAT_TANGGAL':'12-12-2011',
			  'CPM_PAYMENT_TIPE_PENGURANGAN':'12',
			  'CPM_PAYMENT_TIPE_OTHER':'AABBCCDD',
			  'CPM_TRAN_DATE':'12-12-2011',
			  'CPM_TRAN_OPR':'1',
			  'CPM_TRAN_STATUS':'1',
			  'CPM_TRAN_SSB_VERS':'1.0'  
			  },
			  {'CPM_SSB_ID':'12345678901234567890123456789013',
			  'CPM_KPP':'KPP UJICOBA',
			  'CPM_KPP_ID':'001',
			  'CPM_WP_NAMA':'PAK UJI BIN COBA-3',
			  'CPM_WP_NPWP':'123456789012345',
			  'CPM_WP_ALAMAT':'JL. UJICOBA NO.53',
			  'CPM_WP_RT':'04',
			  'CPM_WP_RW':'05',
			  'CPM_WP_KELURAHAN':'TESTING',
			  'CPM_WP_KECAMATAN':'TESTING',
			  'CPM_WP_KABUPATEN':'TESTING TENGAH',
			  'CPM_WP_KODEPOS':'40333',
			  'CPM_OP_NOMOR':'12345678901',
			  'CPM_OP_LETAK':'JL. UJICOBA NO.53',
			  'CPM_OP_RT':'04',
			  'CPM_OP_RW':'05',
			  'CPM_OP_KELURAHAN':'TESTING',
			  'CPM_OP_KECAMATAN':'TESTING',
			  'CPM_OP_KABUPATEN':'TESTING TENGAH',
			  'CPM_OP_KODEPOS':'40333',
			  'CPM_OP_THN_PEROLEH':'2011',
			  'CPM_OP_LUAS_TANAH':'1000',
			  'CPM_OP_LUAS_BANGUN':'500',
			  'CPM_OP_NJOP_TANAH':'130000',
			  'CPM_OP_NJOP_BANGUN':'130000', 
			  'CPM_OP_JENIS_HAK':'Jual Beli',
			  'CPM_OP_HARGA' :'200000000',
			  'CPM_OP_NMR_SERTIFIKAT':'123456789012345678901234567890',
			  'CPM_OP_NPOPTKP':'60000000',
			  'CPM_PAYMENT_TIPE':'1',
			  'CPM_PAYMENT_TIPE_SURAT':'12345678',
			  'CPM_PAYMENT_TIPE_SURAT_NOMOR':'12345678901234567890',
			  'CPM_PAYMENT_TIPE_SURAT_TANGGAL':'12-12-2011',
			  'CPM_PAYMENT_TIPE_PENGURANGAN':'12',
			  'CPM_PAYMENT_TIPE_OTHER':'AABBCCDD', 
			  'CPM_TRAN_DATE':'12-12-2011',
			  'CPM_TRAN_OPR':'1',
			  'CPM_TRAN_STATUS':'1',
			  'CPM_TRAN_SSB_VERS':'1.0' 
			  },
			  {'CPM_SSB_ID':'12345678901234567890123456789014',
			  'CPM_KPP':'KPP UJICOBA',
			  'CPM_KPP_ID':'001',
			  'CPM_WP_NAMA':'PAK UJI BIN COBA-4',
			  'CPM_WP_NPWP':'123456789012345',
			  'CPM_WP_ALAMAT':'JL. UJICOBA NO.53',
			  'CPM_WP_RT':'04',
			  'CPM_WP_RW':'05',
			  'CPM_WP_KELURAHAN':'TESTING',
			  'CPM_WP_KECAMATAN':'TESTING',
			  'CPM_WP_KABUPATEN':'TESTING TENGAH',
			  'CPM_WP_KODEPOS':'40333',
			  'CPM_OP_NOMOR':'12345678901',
			  'CPM_OP_LETAK':'JL. UJICOBA NO.53',
			  'CPM_OP_RT':'04',
			  'CPM_OP_RW':'05',
			  'CPM_OP_KELURAHAN':'TESTING',
			  'CPM_OP_KECAMATAN':'TESTING',
			  'CPM_OP_KABUPATEN':'TESTING TENGAH',
			  'CPM_OP_KODEPOS':'40333',
			  'CPM_OP_THN_PEROLEH':'2011',
			  'CPM_OP_LUAS_TANAH':'1000',
			  'CPM_OP_LUAS_BANGUN':'500',
			  'CPM_OP_NJOP_TANAH':'130000',
			  'CPM_OP_NJOP_BANGUN':'130000', 
			  'CPM_OP_JENIS_HAK':'Jual Beli',
			  'CPM_OP_HARGA' :'200000000',
			  'CPM_OP_NMR_SERTIFIKAT':'123456789012345678901234567890',
			  'CPM_OP_NPOPTKP':'60000000',
			  'CPM_PAYMENT_TIPE':'1',
			  'CPM_PAYMENT_TIPE_SURAT':'12345678',
			  'CPM_PAYMENT_TIPE_SURAT_NOMOR':'12345678901234567890',
			  'CPM_PAYMENT_TIPE_SURAT_TANGGAL':'12-12-2011',
			  'CPM_PAYMENT_TIPE_PENGURANGAN':'12',
			  'CPM_PAYMENT_TIPE_OTHER':'AABBCCDD',
			  'CPM_TRAN_DATE':'12-12-2011',
			  'CPM_TRAN_OPR':'1',
			  'CPM_TRAN_STATUS':'1',
			  'CPM_TRAN_SSB_VERS':'1.0'  
			  },
			  {'CPM_SSB_ID':'12345678901234567890123456789015',
			  'CPM_KPP':'KPP UJICOBA',
			  'CPM_KPP_ID':'001',
			  'CPM_WP_NAMA':'PAK UJI BIN COBA-5',
			  'CPM_WP_NPWP':'123456789012345',
			  'CPM_WP_ALAMAT':'JL. UJICOBA NO.53',
			  'CPM_WP_RT':'04',
			  'CPM_WP_RW':'05',
			  'CPM_WP_KELURAHAN':'TESTING',
			  'CPM_WP_KECAMATAN':'TESTING',
			  'CPM_WP_KABUPATEN':'TESTING TENGAH',
			  'CPM_WP_KODEPOS':'40333',
			  'CPM_OP_NOMOR':'12345678901',
			  'CPM_OP_LETAK':'JL. UJICOBA NO.53',
			  'CPM_OP_RT':'04',
			  'CPM_OP_RW':'05',
			  'CPM_OP_KELURAHAN':'TESTING',
			  'CPM_OP_KECAMATAN':'TESTING',
			  'CPM_OP_KABUPATEN':'TESTING TENGAH',
			  'CPM_OP_KODEPOS':'40333',
			  'CPM_OP_THN_PEROLEH':'2011',
			  'CPM_OP_LUAS_TANAH':'1000',
			  'CPM_OP_LUAS_BANGUN':'500',
			  'CPM_OP_NJOP_TANAH':'130000',
			  'CPM_OP_NJOP_BANGUN':'130000', 
			  'CPM_OP_JENIS_HAK':'Jual Beli',
			  'CPM_OP_HARGA' :'200000000',
			  'CPM_OP_NMR_SERTIFIKAT':'123456789012345678901234567890',
			  'CPM_OP_NPOPTKP':'60000000',
			  'CPM_PAYMENT_TIPE':'1',
			  'CPM_PAYMENT_TIPE_SURAT':'12345678',
			  'CPM_PAYMENT_TIPE_SURAT_NOMOR':'12345678901234567890',
			  'CPM_PAYMENT_TIPE_SURAT_TANGGAL':'12-12-2011',
			  'CPM_PAYMENT_TIPE_PENGURANGAN':'12',
			  'CPM_PAYMENT_TIPE_OTHER':'AABBCCDD',
			  'CPM_TRAN_DATE':'12-12-2011',
			  'CPM_TRAN_OPR':'1',
			  'CPM_TRAN_STATUS':'1',
			  'CPM_TRAN_SSB_VERS':'1.0'  
			  },
			  {'CPM_SSB_ID':'12345678901234567890123456789016',
			  'CPM_KPP':'KPP UJICOBA',
			  'CPM_KPP_ID':'001',
			  'CPM_WP_NAMA':'PAK UJI BIN COBA-6',
			  'CPM_WP_NPWP':'123456789012345',
			  'CPM_WP_ALAMAT':'JL. UJICOBA NO.53',
			  'CPM_WP_RT':'04',
			  'CPM_WP_RW':'05',
			  'CPM_WP_KELURAHAN':'TESTING',
			  'CPM_WP_KECAMATAN':'TESTING',
			  'CPM_WP_KABUPATEN':'TESTING TENGAH',
			  'CPM_WP_KODEPOS':'40333',
			  'CPM_OP_NOMOR':'12345678901',
			  'CPM_OP_LETAK':'JL. UJICOBA NO.53',
			  'CPM_OP_RT':'04',
			  'CPM_OP_RW':'05',
			  'CPM_OP_KELURAHAN':'TESTING',
			  'CPM_OP_KECAMATAN':'TESTING',
			  'CPM_OP_KABUPATEN':'TESTING TENGAH',
			  'CPM_OP_KODEPOS':'40333',
			  'CPM_OP_THN_PEROLEH':'2011',
			  'CPM_OP_LUAS_TANAH':'1000',
			  'CPM_OP_LUAS_BANGUN':'500',
			  'CPM_OP_NJOP_TANAH':'130000',
			  'CPM_OP_NJOP_BANGUN':'130000', 
			  'CPM_OP_JENIS_HAK':'Jual Beli',
			  'CPM_OP_HARGA' :'200000000',
			  'CPM_OP_NMR_SERTIFIKAT':'123456789012345678901234567890',
			  'CPM_OP_NPOPTKP':'60000000',
			  'CPM_PAYMENT_TIPE':'1',
			  'CPM_PAYMENT_TIPE_SURAT':'12345678',
			  'CPM_PAYMENT_TIPE_SURAT_NOMOR':'12345678901234567890',
			  'CPM_PAYMENT_TIPE_SURAT_TANGGAL':'12-12-2011',
			  'CPM_PAYMENT_TIPE_PENGURANGAN':'12',
			  'CPM_PAYMENT_TIPE_OTHER':'AABBCCDD',
			  'CPM_TRAN_DATE':'12-12-2011',
			  'CPM_TRAN_OPR':'1',
			  'CPM_TRAN_STATUS':'1',
			  'CPM_TRAN_SSB_VERS':'1.0'  
			  }
			  ]}";
	 $json = new Services_JSON();
	  
	 return $json->decode($data);
}
	$data = dataDummy ();
	//print_r($_REQUEST);
	for ($i=0;$i<count($data->data);$i++) {
		if (base64_encode($data->data[$i]->CPM_SSB_ID)==base64_encode($_REQUEST['idssb'])){
	
?>
<form id="form1" name="form1" method="post" action="">
  <table width="100%" border="0" cellspacing="1" cellpadding="4">
    <tr>
      <td colspan="2"><strong><font size="+2">Form Surat Setoran Bea Perolehan Hak Atas Tanah dan Bangunan (SSB)</font></strong><br /><br />
      <p>
        <label for="tax_services_office">Kantor Pelayanan Pajak Pratama</label>
        <strong><?php echo $data->data[$i]->CPM_KPP?></strong>&nbsp;&nbsp;&nbsp;&nbsp;
      <label for="tax-services-office-code">Kode KPP Pratama</label>
      <strong><?php echo $data->data[$i]->CPM_KPP_ID?></strong>
    </p></td>
    </tr>
    <tr>
      <td width="3%" align="center"><strong><font size="+2">A</font></strong></td>
      <td width="97%"><table width="650" border="0" cellspacing="1" cellpadding="4">
        <tr>
          <td width="4%"><div align="right">1.</div></td>
          <td width="25%">Nama Wajib Pajak</td>
          <td width="44%"><strong><?php echo $data->data[$i]->CPM_WP_NAMA?></strong></td>
           <td width="25%">&nbsp;</td>
           <td width="2%">&nbsp;</td>
        </tr>
        <tr>
          <td><div align="right">2.</div></td>
          <td>NPWP</td>
          <td><strong><?php echo $data->data[$i]->CPM_WP_NPWP?></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><div align="right">3.</div></td>
          <td>Alamat Wajib Pajak</td>
          <td><strong><?php echo $data->data[$i]->CPM_WP_ALAMAT?></td>
          <td>Blok/Kav/No</td>
          <td><strong><?php echo $data->data[$i]->CPM_WP_NOMOR?></td>
        </tr>
        <tr>
          <td><div align="right">4.</div></td>
          <td>Kelurahan/Desa</td>
          <td><strong><?php echo $data->data[$i]->CPM_WP_KELURAHAN?></strong></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><div align="right">5.</div></td>
          <td>RT/RW</td>
          <td><strong><?php echo $data->data[$i]->CPM_WP_RT?>/<?php echo $data->data[$i]->CPM_WP_RW?></strong></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><div align="right">6.</div></td>
          <td>Kecamatan</td>
          <td><strong><?php echo $data->data[$i]->CPM_WP_KECAMATAN?></strong></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><div align="right">7.</div></td>
          <td>Kabupaten/Kota</td>
          <td><strong><?php echo $data->data[$i]->CPM_WP_KABUPATEN?></strong></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><div align="right">8.</div></td>
          <td>Kode Pos</td>
          <td><strong><?php echo $data->data[$i]->CPM_WP_KODEPOS?></strong></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td align="center" valign="middle"><strong><font size="+2">B</font></strong></td>
      <td><table width="650" border="0" cellspacing="1" cellpadding="4">
        <tr>
          <td width="4%"><div align="right">1.</div></td>
          <td width="25%">NOP PBB</td>
          <td width="44%"><strong><?php echo $data->data[$i]->CPM_OP_NOMOR?></strong></td>
          <td colspan="2">&nbsp;</td>
          </tr>
        <tr>
          <td><div align="right">2.</div></td>
          <td>Lokasi Objek Pajak</td>
          <td><strong><?php echo $data->data[$i]->CPM_OP_LETAK?></strong></td>
          <td>Blok/Kav/No</td>
          <td><strong><?php echo $data->data[$i]->CPM_OP_KAVLING?></strong></td>
        </tr>
        <tr>
          <td><div align="right">3.</div></td>
          <td>Kelurahan/Desa</td>
          <td><strong><?php echo $data->data[$i]->CPM_OP_KELURAHAN?></strong></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><div align="right">4.</div></td>
          <td>RT/RW</td>
          <td><strong><?php echo $data->data[$i]->CPM_OP_RT?></strong>
            /
            <strong><?php echo $data->data[$i]->CPM_OP_RW?></strong></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><div align="right">5.</div></td>
          <td>Kecamatan</td>
          <td><strong><?php echo $data->data[$i]->CPM_OP_KECAMATAN?></strong></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><div align="right">6.</div></td>
          <td>Kabupaten/Kota</td>
          <td><strong><?php echo $data->data[$i]->CPM_OP_KABUPATEN?></strong></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><div align="right">7.</div></td>
          <td>Kode Pos</td>
          <td><strong><?php echo $data->data[$i]->CPM_OP_KODEPOS?></strong></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
      </table><table width="650" border="0" cellspacing="1" cellpadding="4">
  <tr>
    <td colspan="5"><strong>Penghitungan NJOP PBB:</strong></td>
    </tr>
  <tr>
    <td width="77" align="center" valign="middle" bgcolor="#999999">Objek pajak</td>
    <td width="176" align="center" valign="middle" bgcolor="#CCCCCC">Diisi luas tanah atau bangunan yang haknya diperoleh</td>
    <td width="197" align="center" valign="middle" bgcolor="#999999">Diisi berdasakan SPPT PBB terjadi perolehan hak tahun 
      <strong><?php echo $data->data[$i]->CPM_OP_THN_PEROLEH?></strong></td>
    <td colspan="2" align="center" valign="middle" bgcolor="#CCCCCC">Luas x NJOP PBB /m²</td>
    </tr>
  <tr>
    <td rowspan="2" align="center" valign="middle" bgcolor="#999999">Tanah / Bumi</td>
    <td align="center" valign="middle" bgcolor="#00FFFF">7. Luas Tanah (Bumi)</td>
    <td align="center" valign="middle" bgcolor="#00CCFF">9. NJOP Tanah (Bumi) /m²</td>
    <td colspan="2" align="center" valign="middle" bgcolor="#00FFFF">Angka (7x9)</td>
    </tr>
  <tr>
    <td valign="middle" bgcolor="#CCCCCC" align="right"><strong><?php echo number_format(strval($data->data[$i]->CPM_OP_LUAS_TANAH), 0, ',', '.')?></strong>
      m²</td>
    <td valign="middle" bgcolor="#999999" align="right"><strong><?php echo number_format(strval($data->data[$i]->CPM_OP_NJOP_TANAH), 0, ',', '.')?></strong></td>
    <td width="28" align="center" valign="middle" bgcolor="#00FFFF">11.</td>
    <td width="126" valign="middle" bgcolor="#CCCCCC" align="right"><strong><?php echo number_format(strval($data->data[$i]->CPM_OP_LUAS_TANAH)*strval($data->data[$i]->CPM_OP_NJOP_TANAH), 0, ',', '.')?></strong></td>
  </tr>
  <tr>
    <td rowspan="2" align="center" valign="middle" bgcolor="#999999">Bangunan</td>
    <td align="center" valign="middle" bgcolor="#00FFFF">8. Luas Bangunan</td>
    <td align="center" valign="middle" bgcolor="#00CCFF">10. NJOP Bangunan / m²</td>
    <td colspan="2" align="center" valign="middle" bgcolor="#00FFFF">Angka (8x10)</td>
    </tr>
  <tr>
    <td valign="middle" bgcolor="#CCCCCC" align="right"><strong><?php echo number_format(strval($data->data[$i]->CPM_OP_LUAS_BANGUN), 0, ',', '.');?></strong>
m²</td>
    <td valign="middle" bgcolor="#999999" align="right"><strong><?php echo number_format(strval($data->data[$i]->CPM_OP_NJOP_BANGUN), 0, ',', '.')?></strong></td>
    <td align="center" valign="middle" bgcolor="#00FFFF">12.</td>
    <td valign="middle" bgcolor="#CCCCCC" align="right"><strong><?php echo number_format(strval($data->data[$i]->CPM_OP_LUAS_BANGUN)*strval($data->data[$i]->CPM_OP_NJOP_BANGUN), 0, ',', '.')?></strong></td>
  </tr>
  <tr>
    <td colspan="3" align="right" valign="middle" bgcolor="#999999">NJOP PBB </td>
    <td align="center" valign="middle" bgcolor="#00FFFF">13.</td>9
    <td valign="middle" bgcolor="#CCCCCC" align="right"><strong><?php echo number_format((strval($data->data[$i]->CPM_OP_LUAS_TANAH)*strval($data->data[$i]->CPM_OP_NJOP_TANAH))+(strval($data->data[$i]->CPM_OP_LUAS_BANGUN)*strval($data->data[$i]->CPM_OP_NJOP_BANGUN)), 0, ',', '.')?></strong></td>
  </tr>
      </table><table width="650" border="0" cellspacing="1" cellpadding="4">
        <tr>
          <td width="14"><div align="right">14.</div></td>
          <td width="400">Jenis perolehan hak atas tanah atau bangunan</td>
          <td width="208"><strong><?php echo $data->data[$i]->CPM_OP_JENIS_HAK?></strong></td>
        </tr>
        <tr>
          <td><div align="right">15.</div></td>
          <td>Harga transaksi</td>
          <td align="right"><strong>Rp.<?php echo number_format(strval($data->data[$i]->CPM_OP_HARGA), 0, ',', '.')?></strong></td>
        </tr>
        <tr>
          <td><div align="right">16.</div></td>
          <td>Nomor sertifikasi tanah</td>
          <td><strong><?php echo $data->data[$i]->CPM_OP_NMR_SERTIFIKAT?></strong></td>
        </tr>
      </table>
</td>
    </tr>
    <tr>
      <td align="center" valign="middle"><strong><font size="+2">C</font></strong></td>
      <td><table width="650" border="0" cellspacing="1" cellpadding="4">
        <tr>
          <td width="443"><strong>Penghitungan PBB</strong></td>
          <td width="188"> <em><strong>Dalam rupiah</strong></em></td>
        </tr>
        <tr>
          <td>Nilai Perolehan Objek Pajak (NPOP)</td>
          <td align="right"><strong><?php echo number_format(strval($data->data[$i]->CPM_OP_HARGA), 0, ',', '.')?></strong></td>
        </tr>
        <tr>
          <td>Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
          <td align="right"><strong><?php echo number_format(strval($data->data[$i]->CPM_OP_NPOPTKP), 0, ',', '.')?></strong></td>
        </tr>
        <tr>
          <td>Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
          <td align="right"><strong><?php echo number_format(strval($data->data[$i]->CPM_OP_HARGA)-strval($data->data[$i]->CPM_OP_NPOPTKP), 0, ',', '.')?></strong></td>
        </tr>
        <tr>
          <td>Bea Perolehan atas Hak Tanah dan Bangunan yang terutang</td>
          <td align="right"><strong><?php echo number_format((strval($data->data[$i]->CPM_OP_HARGA)-strval($data->data[$i]->CPM_OP_NPOPTKP))*0.05, 0, ',', '.')?></strong></td>
        </tr>
        <tr>
          <td>Pengenaan 50% karena waris/ hibah wasiat/ pemberian hak pengelolaan</td>
          <td>-</td>
        </tr>
        <tr>
          <td><strong>Bea Perolehan atas Hak Tanah dan Bangunan yang harus dibayar</strong></td>
          <td align="right"><strong><?php echo number_format((strval($data->data[$i]->CPM_OP_HARGA)-strval($data->data[$i]->CPM_OP_NPOPTKP))*0.05, 0, ',', '.')?></strong></td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td align="center" valign="middle"><strong><font size="+2">D</font></strong></td>
      <td><table width="650" border="0" cellspacing="1" cellpadding="4">
        <tr>
          <td colspan="3"><strong>Jumlah Setoran Berdasarkan : </strong><em>(tekan kotak yang sesuai)</em></td>
          </tr>
        <tr>
          <td width="583" valign="top"><strong>Penghitungan Wajib Pajak</strong></td>
        </tr>
        
       
      </table></td>
    </tr>
    <tr>
      <td align="center" valign="middle">&nbsp;</td>
      <td>
      <input type="button" name="approved" id="approved" value="Disetujui">&nbsp;&nbsp;&nbsp; <input type="button" name="reject" id="reject" value="Ditolak"></td>
    </tr>
    
  </table>
</form>

<?php
		}
	}
?>