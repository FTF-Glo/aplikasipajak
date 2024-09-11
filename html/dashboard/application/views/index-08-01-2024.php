<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <link rel="icon" type="text/css" href="<?= base_url() ?>assets/images/Logo_lamteng.png">
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/plugins/fontawesome-free/css/all.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/dist/css/adminlte.min.css">
</head>

<body class="dark-mode layout-fixed layout-navbar-fixed layout-footer-fixed sidebar-collapse">
  <div class="wrapper">

    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
    </div>

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark d-flex justify-content-between" style="background-color: #1887bd">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item d-none d-sm-inline-block">
          <img src="<?= base_url() ?>assets/images/Logo_lamteng.png" style="width: 61px;height: auto;margin-right: 18px;margin-left: 10px;">
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <p style="font-size: 26px;margin-bottom: 0rem;color: #ffffff;margin-top: 7px;">
            <b>KABUPATEN LAMPUNG TENGAH</b>
          </p>
          <p style="font-size: 18px;margin-bottom: 0rem;margin-top: -10px;color: #ffffff">
            DASHBOARD PENERIMAAN DAERAH
          </p>
        </li>
      </ul>

      <ul class="navbar-nav">
        <li class="nav-item d-none d-sm-inline-block">
          <p style="font-size: 26px;margin-bottom: 0rem;color: yellow;letter-spacing: 5px;margin-right: auto;width: 100%;">
            <b>BERJAYA TAX</b>
          </p>
        </li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item">
          <p style="font-size: 26px;margin-bottom: 0rem;color: #ffffff;margin-top: -5px;">
            Total Persentasi Pendapatan <span id="persentage_">...</span>
          </p>
      </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="min-height: 178px;margin-top: calc(1px + 6.5rem);">
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h4 class="m-0">Rekapitulasi Pendapatan Daerah</h4>
              <div class="d-flex">
                <select name='version' class="form-control" style="width:160px">
                  <option value="1">RINGKAS</option>
                  <option value="2">DETAIL</option>
                </select>
                <select name='chngthn' id="chngthn" class="form-control" style="width:100px;margin-left:10px">
                  <?php for ($i = 2019; $i <= date("Y"); $i++) : ?>
                    <option value="<?= $i ?>" <?php if (date("Y") == $i) echo " selected"; ?>><?= $i ?></option>
                  <?php endfor; ?>
                </select>
                <label style="margin-top:5px;margin-left:20px">Rangking : </label>
                <select id="rangking" name='Target' class="form-control" style="width:120px;margin-left:10px">
                  <!-- <option>-- Pilih --</option> -->
                  <option value="0" selected>Terkecil</option>
                  <option value="1">Terbesar</option>
                </select>
              </div>
            </div><!-- /.col -->

            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="" style="margin-right: 38px;">
                  <!-- <div class="callout callout-info"> -->
                  <span id="timeTgl">---, --- --- ---</span>&nbsp;&nbsp;<span id="timeWaktu">--:--:--</span>
                  <!-- </div>  -->
                </li>
                <!-- <li class="breadcrumb-item"><a href="<?= base_url() ?>index.php/Auth/logout" class="btn btn-block btn-danger btn-sm" style="height: 33px">Logout</a></li> -->
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      <!-- /.content-header -->

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <!-- Info boxes -->
          <div class="row">
            <div class="col-12">
              <div class="card">
                <!-- <div class="card-header"> -->
                <!-- Judul card -->
                <!-- <h3 card-title>Disini</h3> -->
                <!-- </div> -->
                <div class="card-body">
                  <div class="table-responsive p-0">
                    <table class="table table-hover" id="table_">
                      <thead>
                        <tr style="text-align: center;background-color: #1887bd">
                          <th style="width: 5%;">No.</th>
                          <th style="width: 35%;">Uraian</th>
                          <th>Target</th>
                          <th>
                            Realisasi
                            <br>
                            <span id="bulan_lalu">...</span> s/d <span id="bulan_sampai">...</span>
                          </th>
                          <th>
                            Realisasi
                            <br>
                            <span id="bulan_ini">...</span>
                          </th>
                          <th>Jumlah</th>
                          <th>Persen</th>
                        </tr>
                        <tr style="text-align: center;background-color: orange">
                          <td>(1)</td>
                          <td>(2)</td>
                          <td>(3)</td>
                          <td>(4)</td>
                          <td>(5)</td>
                          <td>(6)</td>
                          <td>(7)</td>
                        </tr>
                      </thead>
                      <tbody>

                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /.row -->
        </div><!--/. container-fluid -->
      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
      <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <footer class="main-footer">
      <!-- <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.1.0-rc
    </div> -->
    </footer>
  </div>
  <!-- ./wrapper -->
  <!-- REQUIRED SCRIPTS -->
  <!-- jQuery -->
  <script src="<?= base_url() ?>assets/plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap -->
  <script src="<?= base_url() ?>assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- overlayScrollbars -->
  <script src="<?= base_url() ?>assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <!-- AdminLTE App -->
  <script src="<?= base_url() ?>assets/dist/js/adminlte.js"></script>

  <!-- PAGE PLUGINS -->
  <!-- jQuery Mapael -->
  <script src="<?= base_url() ?>assets/plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
  <script src="<?= base_url() ?>assets/plugins/raphael/raphael.min.js"></script>
  <script src="<?= base_url() ?>assets/plugins/jquery-mapael/jquery.mapael.min.js"></script>
  <script src="<?= base_url() ?>assets/plugins/jquery-mapael/maps/usa_states.min.js"></script>
  <!-- ChartJS -->
  <script src="<?= base_url() ?>assets/plugins/chart.js/Chart.min.js"></script>

  <!-- AdminLTE for demo purposes -->
  <!-- <script src="<?= base_url() ?>assets/dist/js/demo.js"></script> -->
  <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
  <!-- <script src="<?= base_url() ?>assets/dist/js/pages/dashboard2.js"></script> -->
  <script type="text/javascript">
    $(document).ready(function() {
      var rangking = 'asc';
      var jsonFirstUpdate = false;
      var backgroundProcess = false;

      var clockElement = document.getElementById('timeWaktu');
      var tglElement = document.getElementById('timeTgl');

      function startTime() {
        var today = new Date();
        var h = today.getHours();
        var m = today.getMinutes();
        var s = today.getSeconds();
        m = checkTime(m);
        s = checkTime(s);
        clockElement.textContent = h + ":" + m + ":" + s;
        // var t = setTimeout(startTime, 500);


        var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        var myDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu'];
        var date = new Date();
        var day = date.getDate();
        var month = date.getMonth();
        var thisDay = date.getDay(),
          thisDay = myDays[thisDay];
        var yy = date.getYear();
        var year = (yy < 1000) ? yy + 1900 : yy;

        tglElement.textContent = thisDay + ', ' + day + ' ' + months[month] + ' ' + year;
      }

      function checkTime(i) {
        if (i < 10) {
          i = "0" + i
        }; // add zero in front of numbers < 10
        return i;
      }
      setInterval(startTime, 1000);

      tbl();

      function tbl() {
        if(!backgroundProcess){
          backgroundProcess = true;
          var tahun = $("#chngthn").val();
          $.ajax({
            type: "POST",
            data: {
              tahun: tahun,
              rangking: rangking,
              mode: "getJson"
            },
            url: "<?= base_url() ?>index.php/Dashboard/getdataapi",
            beforeSend: function() {
              $("#chngthn").attr('disabled', 'disabled');
              if(!jsonFirstUpdate){
                $("#table_ tbody").html("<tr><td align='center' colspan='7' style='font-size:18px'><i class='fas fa-circle-notch fa-spin'></i> Mohon tunggu sebentar ...</td></tr>");
              }
            },
            error: function(request, error) {
              $("#table_ tbody").html("<tr><td align='center' colspan='7' style='color:#ffffff;cursor:pointer' id='refresh_'><i class='fa fa-times'></i> Terjadi kesalahan. Klik disini untuk refresh.</td></tr>")
            },
            success: function(data) {
              var arr = JSON.parse(data)
              // console.log(arr);
              $("#table_ tbody").html(arr['table'])
              $("#persentage_").html(arr['persentage'])
              $("#bulan_lalu").html(arr['bulan_lalu'])
              $("#bulan_sampai").html(arr['bulan_sampai'])
              $("#bulan_ini").html(arr['bulan_ini'])
              $("#persentage_").attr("style", "font-size:46px;color:#80ff00;font-weight:bold")
              $('#chngthn').removeAttr("disabled");
              backgroundProcess = false;
              if(!jsonFirstUpdate){
                jsonFirstUpdate = true;
                updateJson();
              }
            }
          })
        }
      }

      function updateJson() {
        backgroundProcess = true;
        var tahun = $("#chngthn").val();
        $.ajax({
          type: "POST",
          data: {
            tahun: tahun,
            rangking: rangking,
            mode: "updateJson"
          },
          url: "<?= base_url() ?>index.php/Dashboard/getdataapi",
          success: function(data) {
            backgroundProcess = false;
            tbl();
            setTimeout(updateJson, 180000);
          }
        })
      }

      $(document).on("click", "#refresh_", function() {
        $("#table_ tbody").html("<tr><td align='center' colspan='7' style='font-size:18px'><i class='fas fa-circle-notch fa-spin'></i> Mohon tunggu sebentar ...</td></tr>");
        jsonFirstUpdate = false;
        tbl();
      })
      $(document).on("change", "#chngthn", function() {
        $("#table_ tbody").html("<tr><td align='center' colspan='7' style='font-size:18px'><i class='fas fa-circle-notch fa-spin'></i> Mohon tunggu sebentar ...</td></tr>");
        jsonFirstUpdate = false;
        tbl();
      })
      setInterval(function() {
        tbl();
      }, 30000)


      $("[name='version']").change(function() {
        if ($(this).val() == 1) {
          window.location = '<?= base_url() ?>'
        } else {
          window.location = '<?= base_url() ?>index.php/Dashboard_new'
        }
      })

      $("#rangking").change(function() {
        $("#table_ tbody").html("<tr><td align='center' colspan='7' style='font-size:18px'><i class='fas fa-circle-notch fa-spin'></i> Mohon tunggu sebentar ...</td></tr>");
        if ($(this).val() == 1) {
          rangking = 'desc';
        } else {
          rangking = 'asc';
        }
        jsonFirstUpdate = false;
        tbl();
      })
    })
  </script>
</body>

</html>