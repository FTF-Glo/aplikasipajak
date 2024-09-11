<?php

?>
<style type='text/css'>
/*
.collapsable {
    padding		: 1em;
    border		: 1px solid black;
    background	: #eee;
}
*/
</style>

<script type="text/javascript">
var COLLAPSABLE_PARENT_NAME = "collapsable";
var COLLAPSABLE_PARENT_TYPE = "div";
var COLLAPSABLE_CHILD_TYPE = "ol";

var COLLAPSABLE_EXPAND = "[Buka detil]";
var COLLAPSABLE_SHRINK = "[Tutup detil]";

init = function() {
    if(document.getElementById && document.createTextNode) {
        var entries = document.getElementsByTagName(COLLAPSABLE_PARENT_TYPE);
        for(i=0;i<entries.length;i++)
            if (entries[i].className==COLLAPSABLE_PARENT_NAME)
                assignCollapse(entries[i]);
    }
}

assignCollapse = function (div) {
    var button = document.createElement('a');
    button.style.cursor='pointer';
	button.style.color='black';
	button.style.borderBottom='dotted 1px black';
    button.setAttribute('expand', COLLAPSABLE_EXPAND);
    button.setAttribute('shrink', COLLAPSABLE_SHRINK);
    button.setAttribute('state', -1);
    button.innerHTML='dsds';
    div.insertBefore(button, div.getElementsByTagName(COLLAPSABLE_CHILD_TYPE)[0]);

    button.onclick=function(){
        var state = -(1*this.getAttribute('state'));
        this.setAttribute('state', state);
        this.parentNode.getElementsByTagName(COLLAPSABLE_CHILD_TYPE)[0].style.display=state==1?'none':'block';
        this.innerHTML = this.getAttribute(state==1?'expand':'shrink');
    };                   
    button.onclick();
}

window.onload=init;
</script>

<div>(Bahasa Indonesia)</div>
<div class='spacer10'></div>
<div class='collapsable'>
<span><b>&bull;&nbsp;&nbsp;Cara membuat module</b></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<ol style='display:none'>
	<div class='spacer5'></div>
	<li>
		Cek daftar database <a href='main.php?param=c2V0dGluZz0xJm09ZA=='>disini</a>, jika belum ada tambahkan.<br />
		Tambahkan konfigurasi tabel yang diperlukan.<br />
	</li><br />
	<li>
		Cek daftar app/application <a href='main.php?param=c2V0dGluZz0xJm09YQ=='>disini</a>, jika belum ada tambahkan.<br />
		a. Field Query adalah SQL query yang berfungsi menyaring daftar terminal apa saja yang tampil.<br />
		b. Tambahkan konfigurasi app/application yang diperlukan.<br />
	</li><br />
	<li>
		Cek daftar module, dan buat module baru di <a href='main.php?param=c2V0dGluZz0xJm09bQ=='>disini</a>.<br />
		a. Field View adalah nama file .php baru yang terletak pada folder 'view'.<br />
		b. Tambahkan konfigurasi module yang diperlukan.<br />
	</li><br />
	<li>
		Buat function yang diperlukan <a href='main.php?param=c2V0dGluZz0xJm09Zg=='>disini</a>.<br />
		a. Field Function adalah nama file .php baru yang terletak pada folder 'function'.<br />
		b. Pilih icon function dengan menekan tombol List; jika belum ada upload image 16x16 pixel di folder 'image/icon'.<br />
		c. Setiap module harus ada minimal 1 function; kalau tidak ada bisa ditambahkan dummy function, yang posisi-nya Hide.<br />
	</li><br />
	<li>
		Buat role baru <a href='main.php?param=c2V0dGluZz0xJm09cg=='>disini</a> jika belum ada yang sesuai, atau pilih role yang sudah ada.<br />
		a. <i>Grant/decline</i> akses ke function pada module tersebut, dengan meng-klik tombol yang bersangkutan.<br />
		b. Supaya module tampil di menu, maka role harus memiliki akses terhadap minimal 1 function pada module.<br />
	</li><br />
	<li>
		Beri akses pada app/application terhadap module.<br />
		a. Pada daftar, pilih module yang baru, lalu tekan tombol Save.<br />
	</li><br />
	<li>
		Buat user baru jika belum ada yang sesuai <a href='main.php?param=c2V0dGluZz0xJm09dQ=='>disini</a>, atau pilih user yang sudah ada.<br />
	</li><br />
	<li>
		Tambahkan role pada user.<br />
		a. Pilih user yang akan memiliki akses terhadap module, lalu tekan List role.<br />
		b. Untuk app/application tertentu, ubah role yang sesuai.<br />
	</li><br />
	<li>
		Selesai. Module akan terbentuk, dan dapat langsung diakses oleh user.<br />
	</li>
</ol>
</div>


<div class='spacer10'></div>
<div class='collapsable'>
<span><b>&bull;&nbsp;&nbsp;Cara pembuatan file untuk module & function</b></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<ol style='display:none'>
	<div class='spacer5'></div>
	<li>
		Buat file .php baru, di dalam folder:<br />
		&nbsp;&nbsp;&nbsp;- 'view' untuk module<br />
		&nbsp;&nbsp;&nbsp;- 'function' untuk function<br />
		Jika ada banyak file, bisa dibuat di folder baru.<br />
	</li><br />
	<li>
		Ambil konfigurasi yang diperlukan, dari module, application, atau database.<br />
		Dari application:<br />
		&nbsp;&nbsp;&nbsp;<code>$arAppConfig = $User->GetAppConfig($appId);</code><br />
		&nbsp;&nbsp;&nbsp;<code>$value = $arAppConfig["key"];</code><br />
		&nbsp;&nbsp;&nbsp;(<code>$value</code> adalah nilai dari konfigurasi.)<br />

		Dari module:<br />
		&nbsp;&nbsp;&nbsp;<code>$arModuleConfig = $User->GetModuleConfig($moduleId);</code><br />
		&nbsp;&nbsp;&nbsp;<code>$value = $arModuleConfig["key"];</code><br />
		&nbsp;&nbsp;&nbsp;(<code>$value</code> adalah nilai dari konfigurasi.)<br />
	</li><br />
	<li>
		Akan dipakai pada saat pengambilan daftar terminal, dengan kode:<br />
		&nbsp;&nbsp;&nbsp;<code>$successStatus = $User->GetTerminal($appId, $moduleId, $arTerminal);</code><br />
		&nbsp;&nbsp;&nbsp;(<code>$arTerminal</code> adalah output array yang mengandung terminal tersebut.)<br />
	</li><br />
	<li>
		Untuk mengakses ke database switcher, dapat menggunakan 2 cara:<br />
		&nbsp;&nbsp;&nbsp;- Menggunakan file inc/central/dbspec-central.php<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Membuat fungsi baru di file tersebut, lalu dipanggil dari $dbSpec<br />
		&nbsp;&nbsp;&nbsp;- Membuat file baru, dengan koneksi $appDbLink;<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Membuat file class baru di dalam folder 'inc'<br />
	</li><br />
	<li>
		Navigasi halaman memiliki pola sebagai berikut:<br />
		Semua parameter yang ingin di-pass, encode dengan base64. Contoh:<br />
		&nbsp;&nbsp;&nbsp;<code>$url64 = base64_encode("a=$app&m=$module");</code><br />
		Hasil halaman lengkap adalah:<br />
		&nbsp;&nbsp;&nbsp;<code>"main.php?param=$url64"</code><br /><br />
		Pada kode Javascript, encoding Base64 dapat dilakukan dengan<br />
		&nbsp;&nbsp;&nbsp;<code>Base64.encode("")</code><br />
	</li><br />
	<li>
		Menampilkan daftar tombol 'per-terminal' functions, dengan memakai:<br />
		&nbsp;&nbsp;&nbsp;<code>printOption($arParam);</code><br />
		Parameter $arParam adalah array yang memberikan parameter tambahan di url, sebelum di base64. Contoh pemakaian:<br />
		&nbsp;&nbsp;&nbsp;<code>$arParam = array("p" => $terminalId);</code><br />
		&nbsp;&nbsp;&nbsp;<code>echo printOption($arParam);</code><br />
	</li><br />
	<li>
		Pengambilan parameter dari url, dapat dilakukan dengan 2 cara:<br />
		&nbsp;&nbsp;&nbsp;- Memakai $_REQUEST[id]<br />
		&nbsp;&nbsp;&nbsp;- Memakai $id<br />
	</li><br />
	<li>
		Untuk menampilkan tabel ada 2 jenis style:<br />
		&nbsp;&nbsp;&nbsp;- Table tanpa style dipakai untuk menampilkan daftar<br />
		&nbsp;&nbsp;&nbsp;- Table untuk tujuan lain (misal pembuatan form), dapat memakai style 'class=transparent'<br />
	</li>
</ol>
</div>