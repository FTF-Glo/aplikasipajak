<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="app-content content">
	<div class="content-wrapper">
        <div class="content-body">
            <div class="text-right mb-1">
                Tahun
                <select name="cbYear" id="cbYear">
                    <?php
                        foreach($active_year as $row){
                            echo '
                                    <option value="'.$row.'">'.$row.'</option>
                                 ';
                        }
                    ?>
                </select>
            </div>
            <div class="card bg-dark text-center">
                <h2 class="c-light p-1">INFORMASI PAJAK DAERAH <?php echo $main['total'][0]['year'];?></h2>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card main-dash text-center border bg-gray">
                        <h5 class="bg-dark-gray p-1">TOTAL PENDAPATAN</h5>
                        <h3><?php echo $this->format->currency($main['total'][0]['pendapatan']);?></h3>
                    </div>
                </div>
                <div class="col-md-6">
                <div class="card main-dash text-center border bg-gray">
                        <h5 class="bg-dark-gray p-1">TOTAL TARGET</h5>
                        <h3><?php echo $main['total'][0]['pencapaian'];?> %</h3>
                        <div><?php echo $this->format->currency($main['total'][0]['target']);?></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php
                    foreach($main['pajak'] as $row){
                        echo '
                                <div class="col-md-4">
                                    <div class="bg-gray text-center my-1">
                                        <h3 class="pt-1">'.$this->format->currency($row['total_pendapatan']).' ('.$row['pencapaian'].' %)</h3>
                                        <small>'.$this->format->currency($row['target']).'</small>
                                        <h5 class="p-1 bg-dark-gray mt-1">'.$row['jenis_pajak'].'</h5>
                                    </div>
                                </div>
                             ';
                    }
                ?>
            </div>
            
            
        </div>
    </div>
</div>

<script>
$("#cbYear").change(function(){
    var year = this.value;
    var url = "<?php echo base_url('main/year/');?>"+year;    
    $(location).attr('href',url);
});
</script>