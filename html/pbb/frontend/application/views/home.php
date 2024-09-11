<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div id="carouselExampleIndicators" class="carousel slide hidden-xs" data-ride="carousel">
  <ol class="carousel-indicators">
    <?php
	      foreach($slider as $key => $row){
          $active = "";
          if($key == 0){
              $active = "active";
          }
          echo '<li data-target="#carouselExampleIndicators" data-slide-to="'.$key.'" class="'.$active.'"></li>';
        }
    ?>
  </ol>
  <div class="carousel-inner">
    <?php
	        foreach($slider as $key => $row){
	            $active = "";
	            if($key == 0){
	                $active = "active";
	            }
				    echo '<div class="carousel-item '.$active.'">
						        <img src="'.base_url('images/slider/'.$row['image']).'" class="d-block w-100" alt="'.$row['title'].'">
			            </div>';
	        }
	  ?>
  </div>
  <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div>

<div class="bg-red tax-type">
  <div class="container">
    <div class="text-center p-3 pb-4" id="jenispajak">
        <h3 class="c-white bold mt-3">JENIS PAJAK <a href="#jenispajak"></a>
        </h3>
        <div class="row">
          <?php
            foreach($pajak as $row){
              echo '
                    <div class="col-md-2 col-4 my-3 ">
                      <div class="bg-light rounded-lg">
                        <div class="p-3"><a href="'.base_url($row['link']).'"><img src="'.base_url('images/icon/'.$row['icon']).'"></a></div>
                        <div class="text-center pb-2"><a href="'.base_url($row['link']).'" class="bold">'.$row['name'].'</a></div>
                      </div>
                    </div>
                   ';
            }
          ?>
        </div>
    </div>
  </div>
</div>