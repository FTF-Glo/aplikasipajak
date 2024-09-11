<?php
// echo '<pre>'.print_r($dtWp,true).'</pre>';

?>
<script type="text/javascript">
    var a = '<?=$a?>';
    var tahunTagihan = <?=$appConfig['tahun_tagihan']?>;

    function changeFocus(el, next) {
        var maxlen = el.getAttribute("maxlength");
        if (el.value.length == (maxlen)) {
            document.getElementById(next).focus().select();
        }
    }

    function iniAngka(evt, x) {
        var charCode = (evt.which) ? evt.which : event.keyCode;

        if ((charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13) {
            return true;
        } else {
            alert("Input hanya boleh angka!");
            return false;
        }
    }

    function iniAngkaDenganKoma(evt, x) {
        var charCode = (evt.which) ? evt.which : event.keyCode;

        if ((charCode >= 48 && charCode <= 57) || charCode == 46 || charCode == 8 || charCode == 13) {
            return true;
        } else {
            alert("Input hanya boleh angka dan titik!");
            return false;
        }
    }

    function checkVal(el, dest) {
        if (el.value != "") {
            el.value = document.getElementById(dest).value;
        }
    }

    function showKel(x) {
        var val = x.value;
        <?php foreach ($bKecamatanOP as $row) { ?>
            if (val == "<?php echo $row['CPC_TKC_ID']; ?>") {
                document.getElementById('sKel').innerHTML = "<?php
                                                                echo "<select name='OP_KELURAHAN' id='OP_KELURAHAN' onchange='changeKel(this);'><option value=''>" . $appConfig['LABEL_KELURAHAN'] . "</option>";
                                                                foreach ($bKelurahanOP as $row2) {
                                                                    if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
                                                                        echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($OP_KELURAHAN) && $OP_KELURAHAN == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . "</option>";
                                                                    }
                                                                }
                                                                echo "</select>";
                                                                ?>";
            }
        <?php } ?>

        $('#KEC_VIEW').val($('#OP_KECAMATAN option:selected').text());
    }

    function changeKel(x) {
        var val = x.value;
        $('#KEL_VIEW').val($('#OP_KELURAHAN option:selected').text());
        $('#NOP1').val(val.substring(0, 2));
        $('#NOP2').val(val.substring(2, 4));
        $('#NOP3').val(val.substring(4, 7));
        $('#NOP4').val(val.substring(7, 10));

        var params = "{'nop' : '" + val + "'}";
        params = Base64.encode(params);
        $.ajax({
            type: "POST",
            url: "inc/PBB/svc-znt.php",
            data: "req=" + params,
            success: function(msg) {

                msg = jQuery.parseJSON(msg)
                $('#OT_ZONA_NILAI').html(msg.str);
            }
        });
    }

    function showKelEdit(x, sel_value) {
        var val = document.getElementById(x).value;

        <?php if (isset($aKecamatan)) {
            foreach ($aKecamatan as $row) {
        ?>
                if (val == "<?php echo $row['CPC_TKC_ID']; ?>") {
                    document.getElementById('sKel').innerHTML = "<?php
                                                                    echo "<select name='OP_KELURAHAN' id='OP_KELURAHAN'><option value=''>-</option>";
                                                                    foreach ($aKelurahan as $row2) {
                                                                        if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
                                                                            echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($OP_KELURAHAN) && $OP_KELURAHAN == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . "</option>";
                                                                        }
                                                                    }
                                                                    echo "</select>";
                                                                    ?>";
                }
        <?php }
        } ?>
        document.getElementById("OP_KELURAHAN").value = sel_value;
    }

    $(document).ready(function() {
        $("input:submit, input:button").button();
        $("#form1").validate({
            rules: {
                OP_PENETAPAN_INDUK: "required",
                OP_ALAMAT: "required",
                //OP_NOMOR: "required",
                OP_RT: "required",
                OP_RW: "required",
                WP_NAMA: "required",
                WP_ALAMAT: "required",
                WP_RT: "required",
                WP_RW: "required",
                WP_PROV: "required",
                WP_KOTAKAB: "required",
                WP_KECAMATAN: "required",
                WP_KELURAHAN: "required",
                WP_KODEPOS: "required",
                WP_NO_KTP: "required",
                WP_NO_HP: "required",
                OP_LUAS_TANAH: "required"
            },
            messages: {
                OP_PENETAPAN_INDUK: "Wajib diisi",
                OP_ALAMAT: "Wajib diisi",
                //OP_NOMOR: "Wajib diisi",
                OP_RT: "Wajib diisi",
                OP_RW: "Wajib diisi",
                WP_NAMA: "Wajib diisi",
                WP_ALAMAT: "Wajib diisi",
                WP_RT: "Wajib diisi",
                WP_RW: "Wajib diisi",
                WP_PROV: "Wajib diisi",
                WP_KOTAKAB: "Wajib diisi",
                WP_KECAMATAN: "Wajib diisi",
                WP_KELURAHAN: "Wajib diisi",
                WP_KODEPOS: "Wajib diisi",
                WP_NO_KTP: "Wajib diisi",
                WP_NO_HP: "Wajib diisi",
                OP_LUAS_TANAH: "Wajib diisi"
            }
        });
    });
</script>
<style>
    #form1 input.error {
        border-color: #ff0000;
    }

    #form1 textarea.error {
        border-color: #ff0000;
    }
</style>
<div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-11" style="max-width:840px;background:#FFF;padding:25px;box-shadow:0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);border:solid 1px #54936f">
        <form enctype="multipart/form-data" method="post" name="form1" id="form1">
            <input type="hidden" id="uname" value="<?=$uname?>">
            <div style="text-align:center"><h2 style="width:100%">Form Surat Pemberitahuan Objek Pajak (1/2)</h2></div>
            <?php
                $idds = null;
                $vs = null;

                if (isset($idd)) {
                    $idds = $idd;
                }

                if (isset($v)) {
                    $vs = $v;
                }
                $param = base64_encode("{'id':'$idds', 'v':'$vs'}");
            ?>
            <!-- <div align="left">Print to PDF <img src="image/icon/adobeacrobat.png" width="16px" height="16px" title="Dokumen PDF" onclick="printToPDF('<?php echo  $param ?>')" style="cursor:pointer"></div>-->
            <?php echo (isset($errorMsg)) ? $errorMsg : "" ?><br>
            Denah : <?php echo  isset($OP_SKET) ? "<a href='" . $OP_SKET . "'>" . substr($OP_SKET, strrpos($OP_SKET, '/') + 1) . "</a>" : "-" ?><br>
            Foto : <?php echo  isset($OP_FOTO) ? "<a href='" . $OP_FOTO . "'>" . substr($OP_FOTO, strrpos($OP_FOTO, '/') + 1) . "</a>" : "-" ?><br>
            Daftar Lampiran <?php echo  isset($btnExt) ? $btnExt : "" ?>
            <ul>
                <?php echo  isset($HtmlExt) ? $HtmlExt : "<li>-</li>" ?>
            </ul><br>

            <div id="newl"></div>
            <span id="spacer">1. Pilih</span>
            <select name="OP_KECAMATAN" id="OP_KECAMATAN" onchange="showKel(this)" style="float:left;">
                <option value="">Kecamatan</option>
                <?php
                foreach ($bKecamatanOP as $row)
                    echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($OP_KECAMATAN) && $OP_KECAMATAN == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . "</option>";
                ?>
            </select>
            <div id="sKel" style="float:left; margin-left:5px;">
                <select name="OP_KELURAHAN" id="OP_KELURAHAN">
                    <option value=""><?php echo $appConfig['LABEL_KELURAHAN']; ?></option>
                    <option value="">-</option><?php
                                                foreach ($bKelurahanOP as $row)
                                                    echo "<option value='" . $row['CPC_TKL_ID'] . "' " . ((isset($OP_KELURAHAN) && trim($OP_KELURAHAN) == $row['CPC_TKL_ID']) ? "selected" : "") . ">" . $row['CPC_TKL_KELURAHAN'] . "</option>"
                                                ?>
                </select>
            </div>
            <br clear="all" />
            <div id="newl"></div>
            <span id="spacer">&nbsp;</span>
            <input type="text" style="width:30px" id="nopsub" disabled value="PR">
            <input type="text" style="width:30px" id="nopsub" disabled value="DTII">
            <input type="text" style="width:40px" id="nopsub" disabled value="KEC">
            <input type="text" style="width:40px" id="nopsub" disabled value="KEL">
            <input type="text" style="width:40px" id="nopsub" disabled value="BLOK">
            <input type="text" style="width:53px" id="nopsub" disabled value="NO.URUT">
            <input type="text" style="width:38px" id="nopsub" disabled value="KODE"><br>
            <div id="newl"></div>
            <span id="spacer">2. NOP</span>
            <?php
            $nop = null;
            $nop2 = null;
            $nop3 = null;
            $nop4 = null;
            $nop5 = null;
            $nop6 = null;
            $nop7 = null;

            if (isset($NOP) && !is_array($NOP)) {
                $nop = substr($NOP, 0, 2);
                $nop2 = substr($NOP, 2, 2);
                $nop3 = substr($NOP, 4, 3);
                $nop4 = substr($NOP, 7, 3);
                $nop5 = substr($NOP, 10, 3);
                $nop6 = substr($NOP, 13, 4);
                $nop7 = substr($NOP, 17, 1);
            }

            /*if (isset($NOP[0])) {
                $nop = $NOP[0];
            }

            if (isset($NOP[1])) {
                $nop2 = $NOP[1];
            }

            if (isset($NOP[2])) {
                $nop3 = $NOP[2];
            }

            if (isset($NOP[3])) {
                $nop4 = $NOP[3];
            }

            if (isset($NOP[4])) {
                $nop5 = $NOP[4];
            }

            if (isset($NOP[5])) {
                $nop6 = $NOP[5];
            }

            if (isset($NOP[6])) {
                $nop7 = $NOP[6];
            }*/
            ?>
            <input type="text" name="NOP[]" readonly="true" id="NOP1" style="width:30px;text-align:center" maxlength="2" onkeyup="changeFocus(this, 'NOP2')" value="<?php echo  $nop; //isset($NOP) && !is_array($NOP) ? substr($NOP, 0, 2) : $NOP[0] 
                                                                                                                                                    ?>" onkeypress="return iniAngka(event, this)">
            <input type="text" name="NOP[]" readonly="true" id="NOP2" style="width:30px;text-align:center" maxlength="2" onkeyup="changeFocus(this, 'NOP3')" value="<?php echo  $nop2 ?>" onkeypress="return iniAngka(event, this)">
            <input type="text" name="NOP[]" readonly="true" id="NOP3" style="width:40px;text-align:center" maxlength="3" onkeyup="changeFocus(this, 'NOP4')" value="<?php echo  $nop3 ?>" onkeypress="return iniAngka(event, this)">
            <input type="text" name="NOP[]" readonly="true" id="NOP4" style="width:40px;text-align:center" maxlength="3" onkeyup="changeFocus(this, 'NOP5')" value="<?php echo  $nop4 ?>" onkeypress="return iniAngka(event, this)">
            <input type="text" name="NOP[]" <?php if ($mode == 'edit') echo 'readonly="true"'; ?> id="NOP5" style="width:40px;text-align:center" maxlength="3" onkeyup="changeFocus(this, 'NOP6')" value="<?php echo $nop5 ?>" onkeypress="return iniAngka(event, this)" onfocus="checkNOP2()">
            <input type="text" name="NOP[]" data-d="x" readonly="true" id="NOP6" style="width:53px;text-align:center" maxlength="4" onkeyup="changeFocus(this, 'NOP7')" value="<?php echo $nop6 ?>" onkeypress="return iniAngka(event, this)" onfocus="checkNOP2()">
            <input type="text" name="NOP[]" <?php if ($mode == 'edit' || (isset($OP_TYPE) && $OP_TYPE==12)) echo 'readonly="true"'; ?> id="NOP7" style="width:38px;text-align:center" maxlength="1" value="<?=(isset($OP_TYPE) && $OP_TYPE==12)? 3 : $nop7?>" onkeypress="return iniAngka(event, this)" onfocus="checkNOP2()">
            <?php
            if ($mode != 'edit') { ?>
                <span id='generateMethodSpan'>
                    <input type="button" class="btn btn-primary bg-orange" value="Generate NOP" onclick="generateNOP();"> <span id="div-generatenop-wait"></span>
                </span>
                <?php
                $userRole = $User->GetUserRole($uid, $a);
                $arrUserRole = explode(',', $arConfig['role_id_admin_pendataan']);
                if (in_array($userRole, $arrUserRole)) {
                ?>
                    <span id='manualMethodSpan' style="display: none">
                        <input type="button" class="btn btn-primary bg-orange" value="Check NOP" onclick="checkNOP();"> <span id="div-generatenop-wait"></span>
                    </span>
                    klik untuk =>
                    <a href='javascript:void(0)' title='Klik untuk merubah metode Input NOP'><span onclick="javascript:changeMethodNOP(this)" method='1' id="changemethod">Manual Input</span></a>
            <?php
                }
            }
            ?>
            <br>

            <?php
            if (isset($NOP_INDUK)) { // JIKA MELAKUKAN PEMECAHAN
                echo '<div id="newl"></div>
                        <span id="spacer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NOP Induk</span>				
                        <input type="text" name="NOPI1" readonly="true" id ="NOPI1" onkeyup="changeFocus(this, \'NOPI2\')" style="width:30px;text-align:center" maxlength="2" value="' . substr($NOP_INDUK, 0, 2) . '">
                        <input type="text" name="NOPI2" readonly="true" id ="NOPI2" onkeyup="changeFocus(this, \'NOPI3\')" style="width:30px;text-align:center" maxlength="2" value="' . substr($NOP_INDUK, 2, 2) . '">
                        <input type="text" name="NOPI3" readonly="true" id ="NOPI3" onkeyup="changeFocus(this, \'NOPI4\')" style="width:40px;text-align:center" maxlength="3" value="' . substr($NOP_INDUK, 4, 3) . '">
                        <input type="text" name="NOPI4" readonly="true" id ="NOPI4" onkeyup="changeFocus(this, \'NOPI5\')" style="width:40px;text-align:center" maxlength="3" value="' . substr($NOP_INDUK, 7, 3) . '">
                        <input type="text" name="NOPI5" readonly="true" id ="NOPI5" onkeyup="changeFocus(this, \'NOPI6\')" style="width:40px;text-align:center" maxlength="3" value="' . substr($NOP_INDUK, 10, 3) . '">
                        <input type="text" name="NOPI6" readonly="true" id ="NOPI6" onkeyup="changeFocus(this, \'NOPI7\')" style="width:53px;text-align:center" maxlength="4" value="' . substr($NOP_INDUK, 13, 4) . '">
                        <input type="text" name="NOPI7" readonly="true" id ="NOPI7" style="width:38px;text-align:center" maxlength="1" value="' . substr($NOP_INDUK, 17, 1) . '" ><br>		
                        <br>';
                echo '<input type="hidden" name="OP_INDUK_LUAS" id="OP_INDUK_LUAS" value="' . $OP_INDUK_LUAS . '"/>';
                echo '<input type="hidden" name="NOP_INDUK" id="NOP_INDUK" value="' . $NOP_INDUK . '"/>';
            }
            ?>

            <?php
            $nopbersama = null;
            $nopbersama2 = null;
            $nopbersama3 = null;
            $nopbersama4 = null;
            $nopbersama5 = null;
            $nopbersama6 = null;
            $nopbersama7 = null;

            if (isset($NOP_BERSAMA) && !is_array($NOP_BERSAMA)) {
                $nopbersama = substr($NOP_BERSAMA, 0, 2);
                $nopbersama2 = substr($NOP_BERSAMA, 2, 2);
                $nopbersama3 = substr($NOP_BERSAMA, 4, 3);
                $nopbersama4 = substr($NOP_BERSAMA, 7, 3);
                $nopbersama5 = substr($NOP_BERSAMA, 10, 3);
                $nopbersama6 = substr($NOP_BERSAMA, 13, 4);
                $nopbersama7 = substr($NOP_BERSAMA, 17, 1);
            }

            if (isset($NOP_BERSAMA[0])) {
                $nopbersama = $NOP_BERSAMA[0];
            }

            if (isset($NOP_BERSAMA[1])) {
                $nopbersama2 = $NOP_BERSAMA[1];
            }

            if (isset($NOP_BERSAMA[2])) {
                $nopbersama3 = $NOP_BERSAMA[2];
            }

            if (isset($NOP_BERSAMA[3])) {
                $nopbersama4 = $NOP_BERSAMA[3];
            }

            if (isset($NOP_BERSAMA[4])) {
                $nopbersama5 = $NOP_BERSAMA[4];
            }

            if (isset($NOP_BERSAMA[5])) {
                $nopbersama6 = $NOP_BERSAMA[5];
            }

            if (isset($NOP_BERSAMA[6])) {
                $nopbersama7 = $NOP_BERSAMA[6];
            }
            ?>
            <div id="newl"></div>
            <span id="spacer">3. NOP Bersama</span>
            <input type="text" name="NOP_BERSAMA[]" readonly="true" id="NOPB1" onkeyup="changeFocus(this, 'NOPB2')" onBlur="checkVal(this, 'NOP1')" style="width:30px;text-align:center" maxlength="2" value="<?php echo $nopbersama; //isset($NOP_BERSAMA) && !is_array($NOP_BERSAMA) ? substr($NOP_BERSAMA, 0, 2) : $NOP_BERSAMA[0] 
                                                                                                                                                                                            ?>" onkeypress="return iniAngka(event, this)">
            <input type="text" name="NOP_BERSAMA[]" readonly="true" id="NOPB2" onkeyup="changeFocus(this, 'NOPB3')" onBlur="checkVal(this, 'NOP2')" style="width:30px;text-align:center" maxlength="2" value="<?php echo $nopbersama2; ?>" onkeypress="return iniAngka(event, this)">
            <input type="text" name="NOP_BERSAMA[]" readonly="true" id="NOPB3" onkeyup="changeFocus(this, 'NOPB4')" onBlur="checkVal(this, 'NOP3')" style="width:40px;text-align:center" maxlength="3" value="<?php echo $nopbersama3; ?>" onkeypress="return iniAngka(event, this)">
            <input type="text" name="NOP_BERSAMA[]" readonly="true" id="NOPB4" onkeyup="changeFocus(this, 'NOPB5')" onBlur="checkVal(this, 'NOP4')" style="width:40px;text-align:center" maxlength="3" value="<?php echo $nopbersama4; ?>" onkeypress="return iniAngka(event, this)">
            <input type="text" name="NOP_BERSAMA[]" readonly="true" id="NOPB5" onkeyup="changeFocus(this, 'NOPB6')" onBlur="checkVal(this, 'NOP5')" style="width:40px;text-align:center" maxlength="3" value="<?php echo $nopbersama5; ?>" onkeypress="return iniAngka(event, this)">
            <input type="text" name="NOP_BERSAMA[]" onkeyup="changeFocus(this, 'NOPB7')" style="width:53px;text-align:center" maxlength="4" value="<?php $nopbersama6; ?>" onkeypress="return iniAngka(event, this)">
            <input type="text" name="NOP_BERSAMA[]" readonly="true" id="NOPB7" style="width:38px;text-align:center" maxlength="1" value="<?php echo $nopbersama7; ?>" onkeypress="return iniAngka(event, this)">
            <?php if ($mode != 'edit') {
            ?> <input type="button" value="Set" class="btn btn-primary bg-orange" onclick="copyNOPBersama();"> &nbsp; <input type="button" value="Reset" class="btn btn-primary bg-blue" onclick="resetNOPBersama();"><br>
            <?php } ?>
            <br>
            <h3 style="width:unset">B. Data Letak Objek Pajak</h3>
            <span id="spacer">4. Nama Jalan</span> <input type="text" name="OP_ALAMAT" id="OP_ALAMAT" maxlength="70" value="<?php echo  isset($OP_ALAMAT) ? str_replace($bSlash, $ktip, $OP_ALAMAT) : "" ?>" size=50>
            <div id="newl"></div>
            <label style='display:none'><span id="spacer"> Blok/Kav/Nomor</span> <input type="text" name="OP_NOMOR" id="OP_NOMOR" maxlength="10" size=12>
                <div id="newl"></div>
            </label>
            <span id="spacer">5. RT</span>
            <select name="OP_RT" id="OP_RT" style="width:70px;background:transparent;border:#8f8f9d solid 1px">
                <?php
                for ($i=0; $i <= 225; $i++) {
                    $x_num = sprintf("%03d", $i);
                    $selectit = (isset($OP_RT) && $i==(int)$OP_RT) ? 'selected':'';
                    echo "<option $selectit value=$x_num>$x_num</option>";
                }
                ?>
            </select>
            <!--input type="text" name="OP _ RT" id="OP _ RT" maxlength="3" value="<?php echo  isset($OP_RT) ? $OP_RT : "" ?>" onkeypress="return iniAngka(event, this)" size=3-->
            <div id="newl"></div>
            <span id="spacer">6. RW</span>
            <select name="OP_RW" id="OP_RW" style="width:50px;background:transparent;border:#8f8f9d solid 1px">
                <?php
                for ($i=0; $i <= 99; $i++) {
                    $x_num = sprintf("%02d", $i);
                    $selectit = (isset($OP_RW) && $i==(int)$OP_RW) ? 'selected':'';
                    echo "<option $selectit value=$x_num>$x_num</option>";
                }
                ?>
            </select>
            <!--input type="text" name="OP _ RW" id="OP _ RW" maxlength="3" value="<?php echo  isset($OP_RW) ? $OP_RW : "" ?>" onkeypress="return iniAngka(event, this)" size=2-->

            <div id="newl"></div>
            <span id="spacer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kabupaten</span>
            <input type="hidden" size="35" readonly="true" value="<?php echo $appConfig['NAMA_PROVINSI']; ?>" id="OP_PROPINSI" name="OP_PROPINSI">
            <input type="text" size="35" readonly="true" value="<?php echo $aKabKota[0]['CPC_TK_KABKOTA']; ?>" id="OP_KOTAKABNAME" name="OP_KOTAKABNAME">
            <input type="hidden" value="<?php echo $aKabKota[0]['CPC_TK_ID']; ?>" id="OP_KOTAKAB" name="OP_KOTAKAB">


            <div id="newl"></div>
            <span id="spacer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kecamatan</span>
            <input type="text" size="35" readonly="true" id="KEC_VIEW" name="kec" value="<?php echo  isset($OP_KECAMATAN_NAMA) ? $OP_KECAMATAN_NAMA : "" ?>">

            <div id="newl"></div>
            <span id="spacer">7. Desa</span>
            <input type="text" size="35" readonly="true" id="KEL_VIEW" name="kel" value="<?php echo  isset($OP_KELURAHAN_NAMA) ? $OP_KELURAHAN_NAMA : "" ?>">

            <div id="newl"></div>
            <br>
            <h3 style="width:unset">C. Data Wajib Pajak</h3>
            <input type="button" id="btn-copy-address" value="Samakan dengan Objek Pajak" onclick="copyAddress()">
            <div id="newl"></div>
            <span id="spacer">8. Nomor KTP</span><input type="text" name="WP_NO_KTP" id="WP_NO_KTP" maxlength="30" value="<?php echo  isset($WP_NO_KTP) ? $WP_NO_KTP : "" ?>" size=30 onblur="return cekWP(event, this)">
            <span id="div-loadwp-wait"></span>
            <span id="div-tmbahwp"><?php echo isset($WP_NO_KTP) ? "<a href=javascript:displayFormWp('{$WP_NO_KTP}')>Edit WP?</a>" : ''; ?></span>
            <table border=0 class='transparent'>
                <tr>
                    <td>9.&nbsp;</td>
                    <td id="spacer">Status</td>
                    <td>10.&nbsp;</td>
                    <td>Pekerjaan</td>
                </tr>
                <tr>
                    <td></td>
                    <td valign="top" style="padding-left:50px">
                        <label><input type="radio" name="WP_STATUS" value="Pemilik" <?php echo (isset($WP_STATUS) && $WP_STATUS == "Pemilik") ? "checked" : "" ?> checked> Pemilik</label><br>
                        <label><input type="radio" name="WP_STATUS" value="Penyewa" <?php echo (isset($WP_STATUS) && $WP_STATUS == "Penyewa") ? "checked" : "" ?>> Penyewa</label><br>
                        <label><input type="radio" name="WP_STATUS" value="Pengelola" <?php echo (isset($WP_STATUS) && $WP_STATUS == "Pengelola") ? "checked" : "" ?>> Pengelola</label><br>
                        <label><input type="radio" name="WP_STATUS" value="Pemakai" <?php echo (isset($WP_STATUS) && $WP_STATUS == "Pemakai") ? "checked" : "" ?>> Pemakai</label><br>
                        <label><input type="radio" name="WP_STATUS" value="Sengketa" <?php echo (isset($WP_STATUS) && $WP_STATUS == "Sengketa") ? "checked" : "" ?>> Sengketa</label>
                    </td>
                    <td></td>
                    <td valign="top">
                        <?php
                        // echo $WP_PEKERJAAN ;
                        // var_dump($WP_PEKERJAAN);
                        ?>
                        <label><input type="radio" name="WP_PEKERJAAN" value="PNS" <?php echo (isset($WP_PEKERJAAN) && $WP_PEKERJAAN == "PNS") ? "checked" : "disabled" ?> checked> PNS *)</label><br>
                        <label><input type="radio" name="WP_PEKERJAAN" value="TNI" <?php echo (isset($WP_PEKERJAAN) && $WP_PEKERJAAN == "TNI") ? "checked" : "disabled" ?>> TNI *)</label><br>
                        <label><input type="radio" name="WP_PEKERJAAN" value="Pensiunan" <?php echo (isset($WP_PEKERJAAN) && $WP_PEKERJAAN == "Pensiunan") ? "checked" : "disabled" ?>> Pensiunan *)</label><br>
                        <label><input type="radio" name="WP_PEKERJAAN" value="Badan" <?php echo (isset($WP_PEKERJAAN) && $WP_PEKERJAAN == "Badan") ? "checked" : "disabled" ?>> Badan</label><br>
                        <label><input type="radio" name="WP_PEKERJAAN" value="Lainnya" <?php echo (isset($WP_PEKERJAAN) && $WP_PEKERJAAN == "Lainnya") ? "checked" : "disabled" ?>> Lainnya</label><br>
                        *)Yang penghasilannya semata-mata berasal dari gaji atau uang pensiunan
                    </td>
            </table>
            <span id="spacer">11. Nama Wajib Pajak</span><input type="text" name="WP_NAMA" id="WP_NAMA" maxlength="50" value="<?php echo  isset($WP_NAMA) ? str_replace($bSlash, $ktip, $WP_NAMA) : "" ?>" size=40 readonly>
            <div id="newl"></div>
            <span id="spacer">12. Nama Jalan</span><input type="text" name="WP_ALAMAT" id="WP_ALAMAT" maxlength="70" id="WP_ALAMAT" value="<?php echo  isset($WP_ALAMAT) ? str_replace($bSlash, $ktip, $WP_ALAMAT) : "" ?>" size=70 readonly>
            <div id="newl"></div>
            <span id="spacer">13. RT</span><input type="text" name="WP_RT" id="WP_RT" maxlength="3" value="<?php echo  isset($WP_RT) ? $WP_RT : "" ?>" size=6 onkeypress="return iniAngka(event, this)" readonly>
            <div id="newl"></div>
            <span id="spacer">14. RW</span><input type="text" name="WP_RW" id="WP_RW" maxlength="3" value="<?php echo  isset($WP_RW) ? $WP_RW : "" ?>" size=4 onkeypress="return iniAngka(event, this)" readonly>

            <div id="newl"></div>
            <span id="spacer">15. Provinsi</span>
            <input type="text" name="WP_PROPINSI" size="35" maxlength="25" id="WP_PROPINSI" value="<?php echo  isset($WP_PROPINSI) ? $WP_PROPINSI : "" ?>" readonly>
            <div id="newl"></div>
            <span id="spacer">16. Kab/kodya</span>
            <div id="sKota">
                <input type="text" name="WP_KOTAKAB" id="WP_KOTAKAB" size="35" maxlength="25" id="WP_KOTAKAB" value="<?php echo  isset($WP_KOTAKAB) ? $WP_KOTAKAB : "" ?>" readonly>
            </div><span id="div-sKota-wait"></span>

            <div id="newl"></div>
            <span id="spacer">17. Kecamatan</span>
            <div id="sKec">
                <input type="text" name="WP_KECAMATAN" id="WP_KECAMATAN" size="35" maxlength="25" id="WP_KECAMATAN" value="<?php echo  isset($WP_KECAMATAN) ? $WP_KECAMATAN : "" ?>" readonly>
            </div><span id="div-sKec-wait"></span>

            <div id="newl"></div>
            <span id="spacer">18. <?php echo $appConfig['LABEL_KELURAHAN']; ?></span>
            <div id="sKel2">
                <input type="text" name="WP_KELURAHAN" id="WP_KELURAHAN" size="35" maxlength="25" id="WP_KELURAHAN" value="<?php echo  isset($WP_KELURAHAN) ? $WP_KELURAHAN : "" ?>" readonly>
            </div><span id="div-sKel2-wait"></span>

            <div id="newl"></div>
            <span id="spacer">19. Kode Pos</span><input type="text" name="WP_KODEPOS" id="WP_KODEPOS" value="<?php echo  isset($WP_KODEPOS) ? $WP_KODEPOS : "" ?>" maxlength="5" size=7 onkeypress="return iniAngka(event, this)" readonly>
            <div id="newl"></div>
            <span id="spacer">20. Nomor HP</span><input type="text" name="WP_NO_HP" id="WP_NO_HP" value="<?php echo  isset($WP_NO_HP) ? $WP_NO_HP : "" ?>" maxlength="15" size=15 onkeypress="return iniAngka(event, this)" readonly>
            <div id="newl"></div>
            <br>
            <h3 style="width:unset">D. Data Tanah</h3>
            <?php
            if (isset($NOP_INDUK)) {
                echo '<span id="spacer">Luas Tanah NOP Induk Sebelumnya </span><input type="text" disabled=true name="OP_INDUK_LUAS" id="OP_INDUK_LUAS" value="' . $OP_INDUK_LUAS . '"/> Menjadi <input type="text" disabled=true name="OP_INDUK_LUAS_AKHIR" id="OP_INDUK_LUAS_AKHIR" value="' . $OP_INDUK_LUAS . '"/><div id="newl"></div>';

                echo '
                <br>
                <span id="spacer">Penetapan NOP Induk </span>
                    <select name="OP_PENETAPAN_INDUK" id="OP_PENETAPAN_INDUK"  id="thn-penetapan-nop-induk">
                        <option value="">Pilih</option>
                        <option value="1">Tetapkan NOP Induk Tahun Berjalan saat ini</option>
                        <option value="0">Tetapkan NOP Induk Tahun depan</option>
                    </select>
                </span>
                    <br>
                    <br>
                ';


                echo '<script type="text/javascript">
                        $(document).ready(function() {
                            $(\'#OP_LUAS_TANAH\').keyup(function() {
                                var value = parseInt($(\'#OP_LUAS_TANAH\').val()) == NaN ? 0 : parseInt($(\'#OP_LUAS_TANAH\').val())
                                var sisa = $(\'#OP_INDUK_LUAS\').val() - value;
                                if (sisa <= 0)
                                    $(\'#OP_INDUK_LUAS_AKHIR\').val(\'NOP habis dipecah\');
                                else $(\'#OP_INDUK_LUAS_AKHIR\').val(sisa);
                            });
                        });
                    </script>';
            }
            ?>
            <span id="spacer">21. Luas Tanah</span><input type="text" name="OP_LUAS_TANAH" id="OP_LUAS_TANAH" maxlength="10" style="text-align:center" onkeypress="return iniAngkaDenganKoma(event, this)" value="<?=(isset($OP_LUAS_TANAH)) ? $OP_LUAS_TANAH : 0 ?>" size=10> m&sup2; &nbsp;&nbsp;<font size="1"><i>Gunakan titik "." sebagai pemisah desimal</i></font>
            <div id="newl"></div>

            <span id="spacer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nomor Sertifikat</span>
            <input type="text" name="NOMOR_SERTIFIKAT" id="NOMOR_SERTIFIKAT" value="<?php echo  isset($NOMOR_SERTIFIKAT) ? $NOMOR_SERTIFIKAT : "" ?>" size=20>
            <div id="newl"></div>
            <span id="spacer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tanggal Sertifikat</span>
            <input type="text" name="TANGGAL_SERTIFIKAT" id="TANGGAL_SERTIFIKAT" value="<?php echo  isset($TANGGAL_SERTIFIKAT) ? $TANGGAL_SERTIFIKAT : "" ?>" size=14>
            <div id="newl"></div>
            <span id="spacer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama di Sertifikat</span>
            <input type="text" name="NAMA_SERTIFIKAT" id="NAMA_SERTIFIKAT" value="<?php echo  isset($NAMA_SERTIFIKAT) ? $NAMA_SERTIFIKAT : "" ?>" size=35>
            <div id="newl"></div>
            <span id="spacer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jenis Sertifikat</span>
            <select name="JENIS_HAK" id="JENIS_HAK">
                <option value="">Pilih jenis hak</option>
                <option value="HM"<?=(isset($JENIS_HAK) && $JENIS_HAK=='HM') ? ' selected':''?>>HAK MILIK (HM)</option>
                <option value="HGU"<?=(isset($JENIS_HAK) && $JENIS_HAK=='HGU') ? ' selected':''?>>HAK GUNA USAH (HGU)</option>
                <option value="HGM"<?=(isset($JENIS_HAK) && $JENIS_HAK=='HGM') ? ' selected':''?>>HAK GUNA BANGUNAN (HGM)</option>
                <option value="HP"<?=(isset($JENIS_HAK) && $JENIS_HAK=='HP') ? ' selected':''?>>HAK PAKAI (HP)</option>
                <option value="HPL"<?=(isset($JENIS_HAK) && $JENIS_HAK=='HPL') ? ' selected':''?>>HAK PENGELOLAAN (HPL)</option>
            </select>
            <div id="newl"></div>

            <span id="spacer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Latitude</span><input type="text" name="OT_LATITUDE" id="OT_LATITUDE" value="<?php echo  isset($OT_LATITUDE) ? $OT_LATITUDE : "" ?>" size=15>&nbsp;&nbsp;
            <button type="button" class="btn" onclick="setTabMap()"><img src="image/icon/polygon.png" height=16 width=auto/> Garis Batas Baru</button>
            <button type="button" class="btn" onclick="openTabMap()"><img src="https://developers.google.com/static/maps/images/maps-icon.svg" height=16 width=auto/></button>&nbsp;
            <button type="button" class="btn" onclick="openGoogleMap()"><img src="https://seeklogo.com/images/N/new-google-maps-icon-logo-263A01C734-seeklogo.com.png" height=16 width=auto/></button>&nbsp;
            <div id="newl"></div>
            <span id="spacer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Longitude</span><input type="text" name="OT_LONGITUDE" id="OT_LONGITUDE" value="<?php echo  isset($OT_LONGITUDE) ? $OT_LONGITUDE : "" ?>" size=15>
            <div id="newl"></div>
            <span id="spacer">22. Zona Nilai Tanah</span>
            <select name="OT_ZONA_NILAI" id="OT_ZONA_NILAI">
                <?php
                $isDisableZnt = false;
                foreach ($bZNT as $row) {
                    if (isset($NOP_INDUK) && (isset($OT_ZONA_NILAI) && trim($OT_ZONA_NILAI) == $row['CPM_KODE_ZNT'])) {
                        $isDisableZnt = true;
                    }
                    echo "<option value='" . $row['CPM_KODE_ZNT'] . " - " . number_format($row['CPM_NIR'], 0, ",", ".") . "' " . ((isset($OT_ZONA_NILAI) && trim($OT_ZONA_NILAI) == $row['CPM_KODE_ZNT']) ? "selected" : "") . ">" . $row['CPM_KODE_ZNT'] . " - " . number_format($row['CPM_NIR'], 0, ",", ".") . " m2</option>";
                }
                ?>
            </select> Pilih kelurahan di atas...
            <?php
            if ($isDisableZnt) {
                echo '<input type="hidden" name="OT_ZONA_NILAI_INDUK" value="' . $OT_ZONA_NILAI . '">';
            }
            ?>
            <div id="newl"></div>

            <?php
                if(!isset($OT_JENIS)) $OT_JENIS = 3 ;
            ?>

            <span id="spacer">23. Jenis Tanah</span> <label><input type="radio" name="OT_JENIS" VALUE="1" <?php echo (isset($OT_JENIS) && $OT_JENIS == 1) ? "checked" : "" ?>> Tanah + Bangunan</input></label><br>
            <span id="spacer">&nbsp;</span> <label><input type="radio" name="OT_JENIS" VALUE="2" <?php echo (isset($OT_JENIS) && $OT_JENIS == 2) ? "checked" : "" ?>> Kavling siap bangun</input></label><br>

            <span id="spacer">&nbsp;</span> <label><input type="radio" name="OT_JENIS" VALUE="6" <?php echo (isset($OT_JENIS) && $OT_JENIS == 6) ? "checked" : "" ?>> Tanah Pertanian <i style="color:#322bff">New</i></input></label><br>
            <span id="spacer">&nbsp;</span> <label><input type="radio" name="OT_JENIS" VALUE="7" <?php echo (isset($OT_JENIS) && $OT_JENIS == 7) ? "checked" : "" ?>> Tanah Peternakan / Perikanan <i style="color:#322bff">New</i></input></label><br>

            <span id="spacer">&nbsp;</span> <label><input type="radio" name="OT_JENIS" VALUE="3" <?php echo (isset($OT_JENIS) && $OT_JENIS == 3) ? "checked" : "" ?>> Tanah kosong</input></label><br>
            <span id="spacer">&nbsp;</span> <label><input type="radio" name="OT_JENIS" VALUE="4" <?php echo (isset($OT_JENIS) && $OT_JENIS == 4) ? "checked" : "" ?>> Fasilitas umum</input></label><br>

            <span id="spacer">&nbsp;</span> <label><input type="radio" name="OT_JENIS" VALUE="5" <?php echo (isset($OT_JENIS) && $OT_JENIS == 5) ? "checked" : "" ?>> Objek Non-Aktif</input> <i style="color:#322bff">New</i></label>
            <div id="newl"></div>
            <span id="spacer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nilai Tanah</span><input type="text" readonly="true" name="OT_PAYMENT_SISTEM" id="OT_PAYMENT_SISTEM" size="14" style="text-align:center" value="<?php echo  isset($NJOP_TANAH) ? $NJOP_TANAH : "" ?>">&nbsp;&nbsp;<input type="button" value="Hitung Nilai" onclick="loadNT()"> <span id="div-nt-wait"></span>
            <div id="newl"></div>
            <input type="hidden" name="OT_PENILAIAN_TANAH" value="sistem">
            <input type="hidden" name="OP_KELAS_TANAH" id="OP_KELAS_TANAH" value="<?php echo  isset($OP_KELAS_TANAH) ? $OP_KELAS_TANAH : "" ?>">
            
            <!-- ARD+ jika map active maka peta dapat diakses -->
            <?php if ($appConfig['MAP_ACTIVE'] == 1) :  ?>
                <?php
                    $tgl = date('Ymd');
                    $browser = $_SERVER['HTTP_USER_AGENT'];
                    $key = 'P_E_S_A_W_A_R_A_N';
                    $tokenMap = sha1($browser.$tgl.$uname.$key);
                ?>
                <script type="text/javascript">
                    function openTabMap() {
                        var nop = $("#NOP1").val() + $("#NOP2").val() + $("#NOP3").val() + $("#NOP4").val() + $("#NOP5").val() + $("#NOP6").val() + $("#NOP7").val();
                        if (nop.length == 18) {
                            let lat = $("#OT_LATITUDE").val();
                            let lng = $("#OT_LONGITUDE").val();
                            let lbl = $("#WP_NAMA").val();
                            let addr = $("#OP_ALAMAT").val();
                            let rt = $("#OP_RT").val();
                            let rw = $("#OP_RW").val();
                            let kel = $("#KEL_VIEW").val();
                            let kec = $("#KEC_VIEW").val();
                            let kota = $("#OP_KOTAKABNAME").val();
                            let luas = $("#OP_LUAS_TANAH").val();
                            let znt = $("#OT_ZONA_NILAI").val();
                                znt = znt.substring(0, 2);
                            let urlx = "<?=$appConfig['MAP_URL']?>?";
                            var datax = {
                                        act:"viewPolygon",
                                        info:[
                                            {label:"Kode / NOP",    value:nop},
                                            {label:"Nama Label",    value:lbl},
                                            {label:"Latitude",      value:lat},
                                            {label:"Longitude",     value:lng},
                                            {label:"Jalan",         value:addr},
                                            {label:"RT / RW",       value:(rt+" / "+rw)},
                                            {label:"Desa/Kel.",     value:kel},
                                            {label:"Kecamatan",     value:kec},
                                            {label:"Kabupaten",     value:kota},
                                            {label:"Luas Tanah",    value:(luas+" m2")},
                                            {label:"ZNT",           value:znt}
                                        ],
                                        mapType:'hybrid', 
                                        code:nop,
                                        lbl:lbl,
                                        lat:lat,
                                        lng:lng,
                                        addr:addr,
                                        rt:rt,
                                        rw:rw,
                                        kel:kel,
                                        kec:kec,
                                        kota:kota,
                                        luas:luas,
                                        znt:znt,
                                        user:'<?=$uname?>'
                                    };
                                    // roadmap, hybrid, satellite, terrain
                            datax = JSON.stringify(datax);
                            datax = Base64.encode(datax);
                            window.open(urlx+"_token=<?=$tokenMap?>&_data="+datax, '_blank').focus();
                        }
                    }
                    function setTabMap() {
                        var nop = $("#NOP1").val() + $("#NOP2").val() + $("#NOP3").val() + $("#NOP4").val() + $("#NOP5").val() + $("#NOP6").val() + $("#NOP7").val();
                        if (nop.length == 18) {
                            let lat = $("#OT_LATITUDE").val();
                            let lng = $("#OT_LONGITUDE").val();
                            let lbl = $("#WP_NAMA").val();
                            let addr = $("#OP_ALAMAT").val();
                            let rt = $("#OP_RT").val();
                            let rw = $("#OP_RW").val();
                            let kel = $("#KEL_VIEW").val();
                            let kec = $("#KEC_VIEW").val();
                            let kota = $("#OP_KOTAKABNAME").val();
                            let luas = $("#OP_LUAS_TANAH").val();
                            let znt = $("#OT_ZONA_NILAI").val();
                                znt = znt.substring(0, 2);
                            let urlx = "<?=$appConfig['MAP_URL']?>?";
                            var datax = {
                                        act:"createPolygon",
                                        callback:"http://127.0.0.1/peta/callback.php",
                                        info:[
                                            {label:"Kode / NOP",    value:nop},
                                            {label:"Nama Label",    value:lbl},
                                            {label:"Latitude",      value:lat},
                                            {label:"Longitude",     value:lng},
                                            {label:"Jalan",         value:addr},
                                            {label:"RT / RW",       value:(rt+" / "+rw)},
                                            {label:"Desa/Kel.",     value:kel},
                                            {label:"Kecamatan",     value:kec},
                                            {label:"Kabupaten",     value:kota},
                                            {label:"Luas Tanah",    value:(luas+" m2")},
                                            {label:"ZNT",           value:znt}
                                        ],
                                        mapType:'hybrid', 
                                        code:nop,
                                        lbl:lbl,
                                        lat:lat,
                                        lng:lng,
                                        addr:addr,
                                        rt:rt,
                                        rw:rw,
                                        kel:kel,
                                        kec:kec,
                                        kota:kota,
                                        luas:luas,
                                        znt:znt,
                                        user:'<?=$uname?>'
                                    };
                                    // roadmap, hybrid, satellite, terrain
                            datax = JSON.stringify(datax);
                            datax = Base64.encode(datax);
                            window.open(urlx+"_token=<?=$tokenMap?>&_data="+datax, '_blank').focus();
                        }
                    }
                    function openGoogleMap() {
                        let lat = $("#OT_LATITUDE").val();
                        let lng = $("#OT_LONGITUDE").val();
                        window.open("https://maps.google.com/?q="+lat+","+lng, '_blank').focus();
                    }
                    $(document).ready(function() {
                        var nop = $("#NOP1").val() + $("#NOP2").val() + $("#NOP3").val() + $("#NOP4").val() + $("#NOP5").val() + $("#NOP6").val() + $("#NOP7").val();
                        if (nop.length == 18) {
                            $('#btn-load-peta').trigger('click');
                        }
                    })
                </script>
                <!-- <input type="button" value="Buka Peta" id="btn-load-peta" onclick="loadPeta();"><br /><br /> -->
                <!-- <iframe src="about:blank" id="maparea" style="border:none;display:none" width="60%" height="500"></iframe> -->
            <?php endif; ?>

            <br>
            <h3 style="width:unset">E. Data Bangunan</h3>
            <span id="spacer">24. Jumlah Bangunan</span>
            <?php
            $onBlur = ($mode != 'edit') ? 'onblur="javascript:if(this.value>0){$(\'#finalkan\').hide()}else{$(\'#finalkan\').show()}"' : "";
            if (isset($OT_JENIS) && ($OT_JENIS == '2' || $OT_JENIS == '3')) $disabledOP_JML_BANGUNAN = ' disabled ';
            else $disabledOP_JML_BANGUNAN = '';
            ?>

            <input type="text" name="OP_JML_BANGUNAN" maxlength="2" <?php echo $onBlur . ' ' . $disabledOP_JML_BANGUNAN; ?> onkeypress="return iniAngka(event, this)" value="<?php echo  isset($OP_JML_BANGUNAN) ? $OP_JML_BANGUNAN : "0" ?>" size=4 style="text-align:center">
            <div id="newl"></div>
            <br>
            <h3 style="width:unset">F. Pernyataan Wajib Pajak</h3>
            <span id="spacer">25. <select name="PP_TIPE">
                    <option <?php echo (isset($PP_TIPE) && $PP_TIPE == "Wajib Pajak") ? "selected" : "" ?>>Wajib Pajak</option>
                    <option <?php echo (isset($PP_TIPE) && $PP_TIPE == "Kuasa") ? "selected" : "" ?>>Kuasa</option>
                </select></span>dengan nama <input type="text" name="PP_NAMA" maxlength="25" value="<?php echo  isset($PP_NAMA) ? str_replace($bSlash, $ktip, $PP_NAMA) : "" ?>" size=40>
            <div id="newl"></div>
            <span id="spacer">26. Tanggal</span><input type="text" name="PP_DATE" id="PP_DATE" maxlength="10" size="10" value="<?php echo isset($PP_DATE) ? $PP_DATE : "" ?>" datepicker="true" datepicker_format="DD/MM/YYYY" style="text-align:center"/>
            <div id="newl"></div>
            <br>
            <h3 style="width:unset">G. Identitas Petugas Pendata Yang Berwenang</h3>
            <span id="spacer">27. Tanggal</span><input type="text" name="OPR_TGL_PENDATAAN" id="OPR_TGL_PENDATAAN" maxlength="10" size="10" value="<?php echo (isset($OPR_TGL_PENDATAAN) && $OPR_TGL_PENDATAAN != "") ? $OPR_TGL_PENDATAAN : date("d/m/Y") ?>" datepicker="true" datepicker_format="DD/MM/YYYY" style="text-align:center"/>
            <div id="newl"></div>
            <span id="spacer">28. Nama Jelas</span><input type="text" name="OPR_NAMA" size="20" maxlength="32" value="<?php echo (isset($OPR_NAMA) && $OPR_NAMA != "") ? $OPR_NAMA : $nm_lengkap ?>">
            <div id="newl"></div>
            <span id="spacer">29. NIP</span><input type="text" name="OPR_NIP" size="20" maxlength="18" value="<?php echo (isset($OPR_NIP) && $OPR_NIP != "") ? $OPR_NIP : $nip ?>">
            <div id="newl"></div>
            <br>
            <input type="hidden" id="NJOP_TANAH" name="NJOP_TANAH" value="<?php echo  isset($NJOP_TANAH) ? $NJOP_TANAH : "0" ?>" />
            <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />

            <h3 style="width:unset">Foto Lokasi Objek Pajak</h3>
            <div id="newl"></div>
            Upload File Foto <input type="file" name="OP_FOTO"><br>
            <br>

            <h3 style="width:unset">Sket/Denah Lokasi Objek Pajak</h3><br>
            <br>
            Silahkan membuat sket menggunakan aplikasi lain, lalu upload file sket di bagian ini<br>
            <div id="newl"></div>
            Upload File Sket <input type="file" name="OP_SKET"><br>
            <br>

            <h3 style="width:unset">Gambar</h3>
            <table border=0>
                <tr>
                    <td valign="top">Contoh Penggambaran: <br><img src="function/PBB/consol/cthgbr.jpg" alt="contoh gambar" height="70%" width="70%"></td>
                    <td valign="top">
                        Keterangan:<br>
                        - Gambarkan sket/ denah lokasi objek pajak<br>
                        (tanpa skala), yang dihubungkan dengan jalan raya/<br>
                        jalan protokol, jalan lingkungan dan lain-lain, yang <br>
                        mudah diketahui oleh umum.<br>
                        - Sebutkan batas-batas pemilikan sebelah utara, <br>
                        Selatan, timur dan barat
                    </td>
            </table>
            <br />
            <input type="submit" name="newForm1" class="btn btn-primary bg-orange" value="Simpan dan lanjutkan" onclick="return valid_form();">
            <?php if ($arConfig['usertype'] == "consol") { ?>
                <input type="submit" name="newForm1" class="btn btn-primary bg-blue" value="Simpan dan Finalkan" onclick="return valid_form('final');" id="finalkan">
            <?php } ?>
            <input type="button" value="Batal" class="btn btn-primary bg-maka" onClick="if (confirm('PERINGATAN! Perubahan pada halaman ini belum disimpan! \nBatalkan?'))
                    javascript:window.location = 'main.php?param=<?php echo  base64_encode("a=" . $a . "&m=" . $m) ?>';">
            <?php
            if (isset($idServices)) {
                echo '<input type="hidden" name="idServices" value="' . $idServices . '">';
            }
            if (!isset($idt)) {
                echo '<input type="hidden" name="is_new_nop" id="is_new_nop" value="generate">';
            }
            ?>
        </form>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        <?php
        if ($isDisableZnt) {
            echo "$('#OT_ZONA_NILAI').prop('disabled', true);";
        }
        ?>
        //showKelEdit("OP_KECAMATAN",<?php echo (!empty($OP_KELURAHAN)) ? $OP_KELURAHAN : ""; ?>);
        //showKel2Edit("WP_KECAMATAN",<?php echo (!empty($WP_KELURAHAN)) ? $WP_KELURAHAN : ""; ?>);

        $(':radio[name="OT_JENIS"]').change(function() {
            var jenis_bumi = $(this).filter(':checked').val();
            if (jenis_bumi == '2' || jenis_bumi == '3') {
                $(':text[name="OP_JML_BANGUNAN"]').val(0);
                $(':text[name="OP_JML_BANGUNAN"]').attr('disabled', 'disabled');
            } else {
                $(':text[name="OP_JML_BANGUNAN"]').removeAttr('disabled');
            }
        });
    });
</script>
<div id="modalDialog"></div>