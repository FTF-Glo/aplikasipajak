<?php
    $sRoot = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'Monitoring', '', dirname(__FILE__))) . '/';
	require_once($sRoot."inc/payment/ctools.php");
	require_once($sRoot."inc/payment/comm-central.php");
	require_once($sRoot."inc/payment/json.php");
	
    $tmp = array();
    $json = new Services_JSON();
                           
  if($_POST['download']=='2'){
			
                        if(isset($_POST['kc'])){
                           $kec = $_POST['kc'];
                        }   
                        if($kec != '0'){
                           $_SESSION['lst'] =  " a.KD_KECAMATAN=".$kec;                            
                        }
                        if(isset($_POST['kl'])){
                             $kel = $_POST['kl'];
                        }
                        if($kel  !='0'){
                            $_SESSION['lst'] .= " and a.KD_KELURAHAN=".$kel; 
                        }
                        
                        if(isset($_SESSION['Nama_kec2'])){
                            $nam3 = $_SESSION['Nama_kec2'];
                        }
                         if(isset($_SESSION['Nam_kel2'])){
                            $nam4 = $_SESSION['Nam_kel2'];
                        }
                        $_SESSION['lst'] .=' and (b.STATUS_PEMBAYARAN_SPPT=0 or b.STATUS_PEMBAYARAN_SPPT=2 or b.STATUS_PEMBAYARAN_SPPT=3)';
                       
                        if(isset($_POST['tgl3']) && isset($_POST['tgl4']) ){
                           $tgl1 = $_POST['tgl3'];
                           $tgl2 = $_POST['tgl4'];
                        }
                        if(isset($_POST['tgl3']) && !isset($_POST['tgl4']) ){
                           $tg1l=$_POST['tgl3'];
                        }
                        
                        if($tgl1 !='' && $tgl2 !=''){
                            $_SESSION['lst'] .= " and b.TGL_TERBIT_SPPT between TO_DATE('".$tgl1."','yyyy/mm/dd') and TO_DATE('".$tgl2."','yyyy/mm/dd ')"; 
                        }
                        if($tgl1 !='' && $tgl2 ==''){
                            $_SESSION['lst'] .= " and b.TGL_TERBIT_SPPT between TO_DATE('".$tgl1."','yyyy/mm/dd') and TO_DATE('".$tgl1."','yyyy/mm/dd ')";   
                        }
                       if(isset($_POST['awal'])){  
                            $_SESSION['lst'] .=  " and a.THN_PAJAK_SPPT =".$_POST['awal'];
                        }  
                        if(isset($_POST['src'])){
                            $cari =$_PORT['src'];
                        }
                        if($cari !=''){
                            $_SESSION['lst'] .=  " and (b.NM_WP_SPPT LIKE '%".$cari."%' or b.KD_PROPINSI || b.KD_DATI2 || b.KD_KECAMATAN || b.KD_KELURAHAN || b.KD_BLOK || b.NO_URUT || b.KD_JNS_OP LIKE '%".$cari."%')";
                        }
                        
                        $kosong1['dimana'] = $_SESSION['lst'].')';		
			$timeOut = '1000';
                        $tmp1['f']='pbbv21.list';
                        
                        $tmp1['i']= $kosong1 ;
                        $tmp1['PAN']='11000'; 
                        $tmp1['IS_VALIDATE'] ='0';
                        
			$host = $_POST['host'];
		        $port = $_POST['port'];
			$timeOut = $_POST['time'];
			
			$sRequest = $json->encode($tmp1);
                        $bOK = GetRemoteResponse($host, $port, '500', $sRequest, $sResp);
			$ts1 = $json->decode($sResp);
                        
                        $hsl[] = $json->decode($ts1->o);
                        
                        $res = array();
                        unset($temp);
                        foreach($hsl as $key=>$value){
                           foreach($value as $isi=>$val){
                                 $temp['Npwp'] = $val->NOP.'&nbsp;';
                                 $temp['Nama'] = $val->NM_WP_SPPT;
                                 $temp['Kec']  = $nam3;//$val->KD_KECAMATAN;
                                 $temp['Kel']  = $nam4;//$val->KD_KELURAHAN;
                                 $tgl = explode('-' ,$val->TGL_TERBIT_SPPT);
                                 $temp['Terbit'] = substr($tgl[2],0,2).'-'.$tgl[1].'-'.$tgl[0];
                                 $tgl = explode('-' ,$val->TGL_JATUH_TEMPO_SPPT);                                 
                                 $temp['Bayar'] = substr($tgl[2],0,2).'-'.$tgl[1].'-'.$tgl[0];
                                 $temp['Jumlah'] =number_format($val->PBB_YG_HARUS_DIBAYAR_SPPT,2,",",".");
                                 
                                 $res[] = $temp;
                           }
                               
                        }
                                 
                        echo '
                            <table>
                                <tr>
                                    <td>NOP</td>
                                    <td>Nnama WP</td>
                                    <td>Kecamatan</td>
                                    <td>Kelurahan</td>
                                    <td>Tanggal Terbit</td>
                                    <td>Tanggal Jatuh Tempo</td>
                                    <td>Jumlah</td>
                                    
                                </tr>';
                        
                        foreach($res as $key=>$value){
                           
                              echo'<tr>';
                                  echo '<td>'.$value['Npwp'];
                                  echo '<td>'.$value['Nama'];
                                  echo '<td>'.$value['Kec'];
                                  echo '<td>'.$value['Kel'];
                                  echo '<td>'.$value['Terbit'];
                                  echo '<td>'.$value['Bayar'];
                                  echo '<td>'.$value['Jumlah'];
                                  
                              echo'</tr>';    
                            
                        }
                         echo'</table>';
                        
//		        $file = explode('/',$addr->filename);
//			$hps =$addr->filename;
//			$alamat = '/'.$file[4].'/'.$file[5];
//			echo $alamat;
	        
  }			
//  if(isset($_POST['hapus'])){
//  	  unlink('/var/www/html/'.$_POST['fl']);
//	  $tmp = 1;
//	  echo $tmp;
//  }
 
?>