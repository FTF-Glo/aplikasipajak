<?php
  if(!isset($_SESSION['user_name'])){
    redirect('auth');
  }
?>
<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta name="description" content="Angkasa Bali Furniture">
  <meta name="keywords" content="Angkasa Bali Furniture">
  <meta name="author" content="Aldiyan@kotoabiru.com">
  <title><?php echo (isset($title))?$title:$this->fun->getConfig('c_title');?>
  </title>
  <link rel="apple-touch-icon" href="<?php echo base_url();?>assets/images/ico/apple-icon-120.png">
  <link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url();?>/assets/images/ico/favicon.ico">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Muli:300,400,500,700"
  rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/css/vendors.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/vendors/css/charts/jquery-jvectormap-2.0.3.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/vendors/css/charts/morris.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/vendors/css/extensions/unslider.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/vendors/css/weather-icons/climacons.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/css/app.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/css/core/menu/menu-types/vertical-menu.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/css/core/colors/palette-gradient.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/css/plugins/calendars/clndr.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/fonts/meteocons/style.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/custom/css/style.css">
  <script src="<?php echo base_url();?>assets/js/core/libraries/jquery.min.js" type="text/javascript"></script>
  <script src="<?php echo base_url();?>assets/vendors/js/ui/popper.min.js" type="text/javascript"></script>
  <script src="<?php echo base_url();?>assets/js/core/libraries/bootstrap.min.js" type="text/javascript"></script>
  <script src="<?php echo base_url();?>assets/vendors/js/ui/perfect-scrollbar.jquery.min.js" type="text/javascript"></script>
  <script src="<?php echo base_url();?>assets/vendors/js/ui/unison.min.js" type="text/javascript"></script>
  <script src="<?php echo base_url();?>assets/vendors/js/ui/blockUI.min.js" type="text/javascript"></script>
  <script src="<?php echo base_url();?>assets/vendors/js/ui/jquery-sliding-menu.js" type="text/javascript"></script>
  <script src="<?php echo base_url();?>assets/vendors/js/menu/jquery.mmenu.all.min.js" type="text/javascript"></script>
</head>
<body class="vertical-layout vertical-menu 2-columns menu-expanded fixed-navbar"
data-open="click" data-menu="vertical-menu" data-col="2-columns">
