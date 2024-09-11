<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
	<style>
		.form-group{
			font-size:1rem;
		}
	</style>
	
  </head>
  
  <body>


            <div class="card">
                <div class="card-header" style="text-align:center;font-size:22px;">
                    Pembatalan Pembayaran
                </div>

                <div class="card-body p-2">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <form>
                                <div class="form-group">
                                    <label for="kodebayar">Kode Bayar</label>
                                    <input type="text" class="form-control" id="kodebayar">
                                </div>
                                <button type="button" id="button" class="btn btn-success" onclick="check_kodebayar()">CEK KODE BAYAR</button>
                            </form>
								<br>
								<p class="text-justify text-white bg-info p-1 font-italic">Cara Pembatalan Pembayaran :
									<br>1. Masukan Kode Bayar lalu Klik tombol hijau tulisan "CEK KODE BAYAR" tunggu hingga data tampil.
									<br>2. Setalah data tampil kemudian Klik tombol biru tulisan "BATALKAN PEMBAYARAN" tunggu sampai muncul pesan Sukses.
								</p>

                        </div>

                        <div class="col-md-6 col-sm-12">
                            <form>
                                <div class="form-group">
                                    <label for="B_KODEBAYAR">Kode Bayar</label>
                                    <input type="text" class="form-control" id="B_KODEBAYAR" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="B_NAMA">Nama</label>
                                    <input type="text" class="form-control" id="B_NAMA" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="B_ALAMAT_WP">Alamat WP</label>
                                    <input type="text" class="form-control" id="B_ALAMAT_WP" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="B_ALAMAT_OP">Alamat OP</label>
                                    <input type="text" class="form-control" id="B_ALAMAT_OP" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="B_JP">Jenis Pajak</label>
                                    <input type="text" class="form-control" id="B_JP" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="B_TOTAL">Jumlah Bayar</label>
                                    <input type="text" class="form-control" id="B_TOTAL" readonly>
                                </div>

                                <button type="button" id="button" class="btn btn-primary" onclick="batalkan_pembayaran()">BATALKAN PEMBAYARAN</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  </body>
</html>

<script src="/view/PATDA-V1/payment_pembatalan/script.js"></script>