<html class="loading" lang="en" data-textdirection="ltr">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="<?php echo $this->config->item('desc'); ?>">
	<meta name="keywords" content="Pajak Online">
	<meta name="author" content="aldiyan@kotoabiru.com">
	<title><?php echo $this->config->item('title'); ?></title>
	<link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet">
	<script src="<?php echo base_url('ext/jquery/jquery-3.4.1.js'); ?>"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="<?php echo base_url("ext/style.css"); ?>">
	<link rel="stylesheet" href="<?php echo base_url("ext/icomoon/style.css"); ?>">

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

	<script>
		$(function() {
			let elmnt = document.getElementById("jenispajak");
			if (elmnt) {
				elmnt.scrollIntoView();
				let headerHeight = $("#myHeader").height();
				let scrolledY = window.scrollY;
				if (scrolledY) {
					window.scrollTo({
						top: (scrolledY - headerHeight),
						behavior: 'smooth'
					});
				}
			}

			document.querySelectorAll('a[href^="#"]').forEach(anchor => {
				anchor.addEventListener('click', function(e) {
					e.preventDefault();

					document.querySelector(this.getAttribute('href')).scrollIntoView({
						behavior: 'smooth'
					});
				});
			});
		});

		function scrollToResult() {
			document.getElementById("txResult").scrollIntoView();
			let headerHeight = $("#myHeader").height();
			let scrolledY = window.scrollY;
			if (scrolledY) {
				window.scrollTo({
					top: (scrolledY - headerHeight),
					behavior: 'smooth'
				});
			}
		}
	</script>
</head>

<body>

	<?php if (getConfig('contact_whatsapp_enabled')) : ?>
		<a style="z-index: 999992;" href="https://api.whatsapp.com/send?phone=<?= getConfig('contact_whatsapp') ?>&text=<?= urlencode(getConfig('contact_whatsapp_msg')) ?>" class="float-wa" target="_blank">
			<i class="fa fa-whatsapp float-wa-icon"></i>
		</a>
	<?php endif; ?>

	<div style="position: fixed;z-index: 999991;width: 100%;height: 100%; background-color:rgba(255, 255, 255, 0.8)" id="overlay" class="d-none">
		<div style="width: 100%;height: 100%;display: flex;align-items: center;justify-content: center;">
			<div style="text-align: center;">
				<i class="fa fa-spin fa-circle-o-notch fa-5x"></i>
				<p>Harap tunggu sebentar...</p>
			</div>
		</div>
	</div>

	<div class="header head-menu fixed-top" id="myHeader">
		<div class=" bg-head">
			<?php
			if ($this->session->userdata('usera_id')) {
				echo '	<div class="container py-1 temp-right"><div class="px-4"><span class="border-right pr-2 mr-1"><i class="icon-user mr-1"></i>' . $this->session->userdata('usera_name') . '</span> <a href="' . base_url('auth/logout') . '">Logout</a></div></div>';
				$h_col = 'header-col';
			} else {
				$h_col = "header-col-min";
			}
			?>
		</div>
		<div class="container py-1">
			<nav class="navbar navbar-expand-lg navbar-light ">
				<a class="navbar-brand" href="<?php echo base_url(); ?>"><img class="logo-xs show-xs" src="<?php echo base_url('images/logo-small.png'); ?>"><img class="logo hidden-xs" src="<?php echo base_url('images/logo-small.png'); ?>"></a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarNav">
					<ul class="nav navbar-nav ml-auto">
						<li>
							<a href="<?= base_url() ?>" class="btn btn-primary m-1">Home</a>
						</li>
						<li>
							<div class="dropdown">
								<button class="btn btn-primary dropdown-toggle m-1" type="button" id="dropdownPendaftaran" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									Pendaftaran
								</button>
								<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownPendaftaran">
									<a class="dropdown-item" target="_blank" href="<?= getConfig('external_link_daftar_wp') ?>">Pendaftaran Wajib Pajak (8PAJAK)</a>
									<a class="dropdown-item" target="_blank" href="<?= getConfig('external_link_daftar_notaris') ?>">Pendaftaran Notaris (BPHTB)</a>
								</div>
							</div>
						</li>
						<li>
							<div class="dropdown">
								<button class="btn btn-primary dropdown-toggle m-1" type="button" id="dropdownPendaftaran" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									Pelaporan
								</button>
								<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownPendaftaran">
									<a class="dropdown-item" target="_blank" href="<?= getConfig('external_link_pelaporan_1') ?>">E-SPTPD, SKPD & SSPD</a>
									<a class="dropdown-item" target="_blank" href="<?= getConfig('external_link_pelaporan_2') ?>">BPHTB NOTARIS</a>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</nav>
		</div>
	</div>
	<div class="<?php echo $h_col; ?>"></div>