<?php
// prevent direct access
if (!isset($data)) {
    return;
}

$uid = $data->uid;

// get module
$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);
$appConfig = $User->GetAppConfig($application);

//prevent access to not accessible module
if (!$bOK) {
    return false;
}

if (!isset($opt)) {
?>
    <link href="function/PBB/keberatan/list-keberatan.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
    <!-- <script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script> -->

    <script>
        $(document).ready(function() {
            $("#closeCBox").click(function() {
                $("#cBox").css("display", "none");
            })
        })

        $(function() {
            $("#tabs").tabs();
        });

        function showKecamatanAll() {
            var request = $.ajax({
                url: "function/PBB/keberatan/svc-kecamatan.php",
                type: "POST",
                data: {
                    id: "<?php echo $appConfig['KODE_KOTA'] ?>"
                },
                dataType: "json",
                success: function(data) {
                    var c = data.msg.length;
                    var options = '';
                    options += '<option value="">Pilih Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                        $("select#kecamatan-4").html(options);
                    }
                }
            });
        }

        function showPengurangan() {
            var kecamatan = $("#kecamatan-4").val();
            var tahun = $("#tahun").val();
            var namakec = $("#kecamatan-4 option:selected").text();
            var sts = 1;
            $("#monitoring-content-4").html("loading ...");
            $("#monitoring-content-4").load("function/PBB/keberatan/svc-list-keberatan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid'}"); ?>", {
                st: sts,
                kc: kecamatan,
                n: namakec,
                th: tahun
            }, function(response, status, xhr) {
                if (status == "error") {
                    var msg = "Sorry but there was an error: ";
                    $("#monitoring-content-4").html(msg + xhr.status + " " + xhr.statusText);
                }
            });
        }

        function showPenguranganPage(page) {
            var kecamatan = $("#kecamatan-4").val();
            var tahun = $("#tahun").val();
            var namakec = $("#kecamatan-4 option:selected").text();
            var sts = 1;
            $("#monitoring-content-4").html("loading ...");
            $("#monitoring-content-4").load("function/PBB/keberatan/svc-list-keberatan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid'}"); ?>", {
                st: sts,
                kc: kecamatan,
                n: namakec,
                page: page,
                th: tahun
            }, function(response, status, xhr) {
                if (status == "error") {
                    var msg = "Sorry but there was an error: ";
                    $("#monitoring-content-4").html(msg + xhr.status + " " + xhr.statusText);
                }
            });
        }

        function excelPengurangan() {
            var kecamatan = $("#kecamatan-4").val();
            var namakec = $("#kecamatan-4 option:selected").text();
            window.open("function/PBB/keberatan/svc-to-excel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid'}"); ?>" + "&nkc=" + namakec + "&kc=" + kecamatan);
        }

        $(document).ready(function() {
            showKecamatanAll();
            $('#tabs').tabs({
                select: function(event, ui) { // select event
                    $(ui.tab); // the tab selected
                    if (ui.index == 2) {}
                }
            });
        });
    </script>

    <body>
        <div class="row">
            <div class="col-md-12">
                <div id="div-search">
                    <div id="tabs">
                        <ul>
                            <li><a href="#tabs-4">Daftar Keberatan</a></li>
                        </ul>
                        <div id="tabs-4">
                            <fieldset>
                                <form id="TheForm-4" method="post" action="view/PBB/monitoring/svc-export.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" target="TheWindow">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="2">
                                        <tr>
                                            <td width="69">Kecamatan</td>
                                            <td width="3">:</td>
                                            <td width="138"><select name="kecamatan-4" id="kecamatan-4"></select></td>
                                            <td width="20">Tahun</td>
                                            <td width="3">:</td>
                                            <td width="138">
                                                <select name="tahun" id="tahun">
                                                    <?php
                                                    $thn = date("Y");
                                                    $thnTagihan = $appConfig['tahun_tagihan'];
                                                    echo "<option value=\"\">Semua</option>";
                                                    for ($t = $thn; $t > ($thn - 6); $t--) {
                                                        if ($t == $thnTagihan) {
                                                            echo "<option value=\"$t\" selected>$t</option>";
                                                        } else
                                                            echo "<option value=\"$t\">$t</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                            <td width="380">
                                                <input type="button" name="button4" id="button" value="Tampilkan" onClick="showPengurangan()" />
                                                <input type="button" name="buttonToExcel" id="buttonToExcel" value="Ekspor ke xls" onClick="excelPengurangan()" />
                                            </td>
                                            <td width="5">&nbsp;</td>
                                            <td width="126"></td>
                                        </tr>
                                    </table>
                                    <input type="hidden" id="export_e2" />
                                </form>
                            </fieldset>
                            <div id="frame-tbl-monitoring" class="tbl-monitoring">
                                <div id="monitoring-content-4" class="monitoring-content"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
}
    ?>
    <div id="cBox" style="width: 205px; height: 300px; position: absolute; left: 555px; top: 255px; border: 1px solid gray; background-color: #eaeaea; display: none; overflow: auto;">
        <div style="overflow: auto; background-color: #c0c0c0; width: 198px; padding: 3px;">
            <div style="float: left;">
                <span style="font-size: 12px;">Link Download</span>
            </div>
            <div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>
        </div>
        <div id="contentLink" style="padding: 3px; width: 196px; height: 260px; overflow: auto;"></div>
    </div>
    </body>
    <script language="javascript">
        $("input:submit, input:button").button();
    </script>
    <script language="javascript">
        $(document).ready(function() {
            $("#closeCBox").click(function() {
                $("#cBox").css("display", "none");
            })
        })
    </script>