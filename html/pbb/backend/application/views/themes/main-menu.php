 <nav class="header-navbar navbar-expand-md navbar navbar-with-menu fixed-top navbar-semi-dark navbar-shadow">
    <div class="navbar-wrapper">
      <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
          <li class="nav-item mobile-menu d-md-none mr-auto"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ft-menu font-large-1"></i></a></li>
          <li class="nav-item">
            <a class="navbar-brand" href="<?php echo base_url();?>">
             <img class="brand-logo" alt="robust admin logo" src="<?php echo base_url();?>images/logo.png">
              <h3 class="brand-text ">Administrasi</h3>
            </a>
          </li>
          <li class="nav-item d-md-none">
            <a class="nav-link open-navbar-container" data-toggle="collapse" data-target="#navbar-mobile"><i class="fa fa-ellipsis-v"></i></a>
          </li>
        </ul>
      </div>
      <div class="navbar-container content">
        <div class="collapse navbar-collapse" id="navbar-mobile">
          <ul class="nav navbar-nav mr-auto float-left">
            <li class="nav-item d-none d-md-block"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ft-menu">         </i></a></li>
            <li class="nav-item d-none d-md-block p-title"></li>
          </ul>
          <ul class="nav navbar-nav float-right">
          
          
          <li class="dropdown dropdown-notification nav-item">
              <a class="nav-link nav-link-label" href="#" data-toggle="dropdown"><i class="ficon ft-bell"></i>
                <span class="badge badge-pill badge-default badge-danger badge-default badge-up">5</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                <li class="dropdown-menu-header">
                  <h6 class="dropdown-header m-0">
                    <span class="grey darken-2">Notifications</span>
                  </h6>
                  <span class="notification-tag badge badge-default badge-danger float-right m-0">5 New</span>
                </li>
                <li class="scrollable-container media-list w-100 ps-container ps-theme-dark ps-active-y" data-ps-id="1e0805a2-cf09-c949-91f1-e45a85892654">
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-plus-square icon-bg-circle bg-cyan"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading">You have new order!</h6>
                        <p class="notification-text font-small-3 text-muted">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">30 minutes ago</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-download-cloud icon-bg-circle bg-red bg-darken-1"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading red darken-1">99% Server load</h6>
                        <p class="notification-text font-small-3 text-muted">Aliquam tincidunt mauris eu risus.</p>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Five hour ago</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-alert-triangle icon-bg-circle bg-yellow bg-darken-3"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading yellow darken-3">Warning notifixation</h6>
                        <p class="notification-text font-small-3 text-muted">Vestibulum auctor dapibus neque.</p>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Today</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-check-circle icon-bg-circle bg-cyan"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading">Complete the task</h6>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Last week</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-file icon-bg-circle bg-teal"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading">Generate monthly report</h6>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Last month</time>
                        </small>
                      </div>
                    </div>
                  </a>
                <div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 3px;"><div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps-scrollbar-y-rail" style="top: 0px; height: 255px; right: 3px;"><div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 162px;"></div></div></li>
                <li class="dropdown-menu-footer"><a class="dropdown-item text-muted text-center" href="javascript:void(0)">Read all notifications</a></li>
              </ul>
            </li>
          
          
          
          
            <li class="dropdown dropdown-user nav-item">
              <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
                <span class="user-name"><?php echo $_SESSION['user_name'];?></span>
              </a>
              <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item " href="#" id="btnChangePass" ><i class="ft-shield"></i> Ubah Password</a>
                  <div class="dropdown-divider"></div>
				  <a class="dropdown-item" href="<?php echo base_url();?>auth/logout"><i class="ft-power"></i> Logout</a>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
  <!-- ////////////////////////////////////////////////////////////////////////////-->
  
  <div class="main-menu menu-fixed menu-dark menu-accordion menu-shadow " data-scroll-to-active="true">
      <div class="main-menu-content">
          <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">		
            <?php
              $menu = $this->u_access->getMenu();
              $uri = uri_string();
              $uri = explode('/',$uri);
              $count_usaha = $this->fun->getCountValidasiUsaha();
              $count_verification = $this->fun->getCountVerificationSppt();
              foreach($menu as $row){
                $span = ""; 
                  if($row['id'] == '6'){
                    if($count_usaha > 0){
                      $span = '<span class="badge badge badge-pill badge-danger float-right mr-2">'.$count_usaha.'</span>';
                    }
                  }
                  if($row['id'] == '8'){
                    if($count_verification > 0){
                      $span = '<span class="badge badge badge-pill badge-danger float-right mr-2">'.$count_verification.'</span>';
                    }
                  }
                    
                  echo '
                      <li class="nav-item "><a href="#"><i class="'.$row['icon'].'"></i><span class="menu-title" data-i18n="nav.page_layouts.main">'.$row['name'].'</span> '.$span.'</a>
                          <ul class="menu-content">
                  ';
                  foreach($row['sub_menu'] as $sm){
                      $active = "";
                      $module = explode('/',$sm['module']);
                      if(isset($parent)){
                        if($parent == $sm['module']){
                          $active = "active";
                        }
                      } else {
                        if($uri[0] == $module[0] && $uri[1] == $module[1]){
                          $active = "active";
                        } 
                      }
                      $spanm = "";
                      if($sm['id'] == '55'){
                        if($count_usaha > 0){
                          $spanm = '<span class="badge badge badge-pill badge-danger float-right mr-2">'.$count_usaha.'</span>';
                        }
                      }
                      if($sm['id'] == '63'){
                        if($count_verification > 0){
                          $spanm = '<span class="badge badge badge-pill badge-danger float-right mr-2">'.$count_verification.'</span>';
                        }
                      }
                      echo '
                              <li class="'.$active.'">
                                  <a href="'.base_url().$sm['module'].'" data-i18n="nav.page_layouts.1_column"><i class="'.$sm['icon'].' mr-icon"></i>'.$sm['name'].$spanm.'</a>
                              </li>
                      ';
                  }
                  echo '  
                          </ul>
                      </li>    
                  ';
              }
            ?>
          </ul>
      </div>
  </div>

  <div class="modal fade" id="modEditPassword" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <?php echo form_open('tools_users/selfEdit', 'id="myFormSelf" name="myFormSelf" enctype="multipart/form-data"');?>
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Ubah Password</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <input type="text" name="tbxPage" value="<?php echo uri_string();?>" hidden />
		  <input type="text" name="tbxIDSelf" value="<?php echo $_SESSION['user_id'];?>" hidden />
		  <div class="modal-body">
			<input name="tbxPage" value="<?php echo current_url();?>" hidden> 
			<div class="form-group">
				<label>Password Baru: </label>
				<input type="password" name="tbxPasswordSelf" id="tbxPasswordSelf" class="form-control required"/>
			</div>
			<div class="form-group">
				<label>Ulangi Password Baru: </label>
				<input type="password" name="tbxPasswordSelf2" id="tbxPasswordSelf2" class="form-control required"/>
			</div>			
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
			<button type="submit" class="btn btn-primary">Simpan</button>
		  </div>
		</div>
	  </div>
	   <script src="<?php echo base_url();?>assets/vendors/js/forms/validation/jquery.validate.min.js" ></script>
  </form>
  <script>
  $(function() {

  $("#myFormSelf ").validate({
    rules: {
      tbxPasswordSelf: {
        required: true,
        minlength: 3
      },
	   tbxPasswordSelf2: {
		   equalTo: "#tbxPasswordSelf"
	  },
      action: "required"
    },
    messages: {
      tbxPasswordSelf: {
        required: "Silahkan masukkan password baru",
        minlength: "Password harus terdiri minimal 3 karakter"
      },
	  tbxPasswordSelf2: {
		equalTo : "Password harus sama",
		required: "Silahkan masukkan ulangi password"
	  },
      action: "Silahkan masukkan password"
    }
  });
});

(function($) {

var infoModal = $('#modEditPassword');
$('#btnChangePass').on('click', function(){
    infoModal.modal('show');
    return false;
});

})(jQuery);
</script>

  
</div>


