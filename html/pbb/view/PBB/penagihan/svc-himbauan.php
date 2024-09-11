<?php
 

    echo"
    
     
            <html>
            <table border=\"0\" cellpadding=\"20\" width=\"100%\">
                <tr>
                    <!--LOGO-->
                    <td align=\"center\" width=\"20%\">
                        
                    </td>
                    <!--COP-->
                    <td align=\"center\" width=\"79%\">
                        
                    </td>
                    <!--KOSONG-->
                    <td align=\"center\" width=\"1%\">
                    </td>
                </tr>
                <tr>
                    <td colspan=\"3\"><hr></td>
                </tr>

                    
                <tr>
                    <img src=\"style/default/logo.png\"width=\"10%\">
                    <td colspan=\"3\" >
                    
                    <br/>
                      
                    <table width=\"100%\" border=\"0\">
                        <tr>
                            <td width=\"90\">Nomor</td>
                            <td width=\"10\">:</td>
                            <td width=\"350\">800/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/V.04/2022</td>
                            <td width=\"250\">" . ucfirst(strtolower($kota)) . ", &nbsp;&nbsp;&nbsp;" . $month . " </td>
                        </tr>
                        <tr>
                            <td>Sifat</td>
                            <td>:</td>
                            <td>Biasa</td>
                            <td>Kepada</td>
                        </tr>
                        <tr>
                            <td>Perihal</td>
                            <td>:</td>
                            <td><b>Himbauan Pembayaran PBB-P2 Tahun 2022</b></td>
                            <td>Yth. Sdr. " . $op['WP_NAMA'] . "</td>
                        </tr>
                        
                        <tr>
                            <td></td><td></td><td></td><td>" . ucfirst(strtolower($kota)) . "</td>
                        </tr>
                    </table>        
                    <br/>
                    <br/>
                    &nbsp;&nbsp;&nbsp;Disampaikan dengan hormat, sehubungan dengan telah terbitnya Surat Pemberitahuan Pajak Terhutang Pajak Bumi dan Bangunan (SPPT PBB) Tahun 2022, dengan ini kami menghimbau kepada para Wajib Pajak Daerah untuk dapat membayar Ketetapan Pajak terhutang Pajak Bumi dan Bangunan : <br/><br/>
                    
                    <br/><br/>
                    &nbsp;&nbsp;&nbsp;Berkenan dengan hal tersebut diatas dan membantu tercapainya target Pajak Bumi dan Bangunan pada triwulan ke III kami harapkan kepada PT Sinar Mas untuk dapat membayarkan Pajak terhutang tersebut melalui transfer ke Bank Lampung Cabang Kalianda atau Pembayaran melalui indomaret terdekat ke <b>NOMOR REKENING BANK LAMPUNG (383.00.09.000039) a.n Kas Umum Daerah Kabupaten Lampung Selatan</b> dengan mencantumkan Nomor Obyek Pajak (NOP) dan Tahun Pajak Berjalan.
                    <br/>
                    <br/>
                     Demikian kami sampaikan atas partisipasi saudara dalam pembangunan Kabupaten Lampung Selatan kami ucapkan terima kasih
                    <br/>
                    <br/>
                    <br/>
                    <br/>
                        <table border=\"0\">
                            <tr>
                                <td>&nbsp;</td>
                                <td align=\"left\">Kepala Badan Pengelolaan Pajak Dan Retibusi
                                    <br/>
                                    Kabupaten  Lampung Selatan
                                    <br/>
                                    <br/>
                                    <br/>
                                    <br/>
                                    <br/>
                                    <br/>
                                    $kepala
                                    <br/>
                                    $jabatan
                                    <br/>
                                    NIP. $nip
                                </td>
                            </tr>
                        </table>
                        <br><br>
                        <table border=\"1\" width=\"310\" cellpadding=\"5\">
                            <tr><td>
                        
                        
                        </td></tr>
                        </table>
                    </td>
                </tr>
            </table>
            ";

    


?>


