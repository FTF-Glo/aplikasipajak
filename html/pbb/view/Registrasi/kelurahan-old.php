<?
print_r($User);
if(isset($_POST['data_type'])){
	$data_type = $_POST['data_type'];
	switch($data_type){
			case 'kelurahan': 
						if(isset($data_type['kota']=="Bandung"))
						{
							$arConfig=$User->GetAreaConfig($area);
							$Bandung=$arConfig["Bandung"];
							$Bdg[1]=substr($Bandung,0,7);
							$Bdg[2]=substr($Bandung,9,6);
							$loop=0;
							while($loop<=2){
							$loop++;
							?>
							<option value="<?php echo  $Bdg[$loop];?>"><?php echo  $Bdg[$loop];?></option>
							<?
							}
						}
						else if($data_type['kota']=="Subang"))
						{
							$arConfig=$User->GetAreaConfig($area);
							$Subang=$arConfig["Subang"];
							$Sbg[1]=substr($Subang,0,11);
							$Sbg[2]=substr($Subang,12,8);
							$loop=0;
							while($loop<=2){
							$loop++;
							?>
							<option value="<?php echo  $Sbg[$loop];?>"><?php echo  $Sbg[$loop];?></option>
							<?
						}
			break;
			case 'Kota':
			default:
						$arConfig=$User->GetAreaConfig($area);
						$data_type=$arConfig["$data_type"];
						$Kot[1]=substr($data_type,0,7);
						$Kot[2]=substr($data_type,8,6);
						$Kot[3]=substr($data_type,15,5);
						$Kot[4]=substr($data_type,21,6);
						$loop=0;
						while($loop<=3){
						$loop++;
						?>	
							<option value="<?php echo  $Kot[$loop];?>"><?php echo  $Kot[$loop];?></option>
						<?
						}
	}
	$response = array(); // siapkan respon yang nanti akan di convert menjadi JSON
	die(json_encode($response)); // convert variable respon menjadi JSON, lalu tampilkan 
}

?>