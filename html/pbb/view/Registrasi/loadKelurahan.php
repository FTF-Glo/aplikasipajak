<?php
if(isset($_POST['Kota']))//Jika username telah disubmit
{
	$tampil="
	<tr>
		<td>Kelurahan</td>
		<td>
		<select name='kelurahan' id='kelurahan'>
			<option value='pilih' selected>Pilih...</option>
	";
	$kota=mysql_real_escape_string($_POST['Kota']);
	if($kota=="Bandung"){
			$arConfig=$User->GetAreaConfig($area);
			$Bandung=$arConfig["Bandung"];
			$Bdg[1]=substr($Bandung,0,7);
			$Bdg[2]=substr($Bandung,9,6);
			$loop=0;
			while($loop<=2){
				$loop++;
				
				$tampil.="<option value=".$Bdg[$loop].">".$Bdg[$loop]."</option>";
				
			}
	}
	else if($kota=="Subang")
	{
			$arConfig=$User->GetAreaConfig($area);
			$Subang=$arConfig["Subang"];
			$Sbg[1]=substr($Subang,0,11);
			$Sbg[2]=substr($Subang,12,8);
			$loop=0;
			while($loop<=2){
			$loop++;
			
			$tampil.="<option value=".$Sbg[$loop].">."$Sbg[$loop]."</option>";
			
			}
	}
	
	$tampil.="
			</select>
			</td>
	</tr>
	";
	echo $tampil;

}
?>