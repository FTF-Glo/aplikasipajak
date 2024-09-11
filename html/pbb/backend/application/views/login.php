<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 ?>

<?php echo form_open('auth/check', 'id="myForm" name="myForm" enctype="multipart/form-data"');?>
<style>
.header-image{ position: relative;z-index: 1;height: 100%;min-height: 23.75rem;background: url(./images/city.jpg) no-repeat;background-size: cover;background-attachment: fixed;
    -webkit-animation-name: example; /* Chrome, Safari, Opera */
    -webkit-animation-duration: 50s; /* Chrome, Safari, Opera */
    -webkit-animation-iteration-count: 10; /* Chrome, Safari, Opera */
    animation-name: example;
    animation-duration: 50s;
    animation-iteration-count: 10;
	background-position:
}


/* Chrome, Safari, Opera */
@-webkit-keyframes example {
    0%   {background-position: 0% 0%; background-size: 120% 120%;}
    25%   {background-position: 100% 0%; background-size: 100% 100%; }
	50%   {background-position: 100% 100%; background-size: 130% 130%; }
	75%   {background-position: 0% 100%; background-size: 100% 100%; }
    100%  {background-position: 0% 0%; background-size: 120% 120%;}
}

</style>
<div class="app-content content header-image">
	<div class="content-wrapper">
	  <div class="content-header row">
	  </div>
	  <div class="content-body">
		<section class="flexbox-container">
		  <div class="col-12 d-flex align-items-center justify-content-center">
			<div class="col-md-4 col-10 box-shadow-2 p-0">
			  <div class="card border-grey border-lighten-3 px-1 py-1 m-0">
				<div class="card-header border-0">
				  <div class="card-title text-center">

					<hr />
				</div>
		
				<div class="card-content">

				  <div class="card-body">
					<form class="form-horizontal" action="index.html" novalidate>
					  <fieldset class="form-group position-relative has-icon-left">
						<input type="text" class="form-control" id="user-name" name="tbxUsername" placeholder="Your Username"
						required>
						<div class="form-control-position">
						  <i class="ft-user"></i>
						</div>
					  </fieldset>
					  <fieldset class="form-group position-relative has-icon-left">
						<input type="password" class="form-control" id="user-password" name="tbxPassword" placeholder="Enter Password"
						required>
						<div class="form-control-position">
						  <i class="fa fa-key"></i>
						</div>
					  </fieldset>
						<?php
							if(isset($_SESSION['message'])){
								echo ' <div class="text-center text-warning p-1">'.$_SESSION['message'].'</div>'; 
							}
						?>
					  <button type="submit" class="btn btn-outline-info btn-block"><i class="ft-unlock"></i> Login</button>
					</form>
				  </div>

				</div>
			  </div>
			</div>
		  </div>
		</section>
	  </div>
	</div>
</div>
</form>