<?php


?>

<!-- Login Page -->
<style>
	body {
	overflow :hidden;
     background-image: linear-gradient(to right bottom, #e6ebd8, #c9e3ce, #a6dbce, #84d0d8, #71c2e4, #6cc1eb, #6ac0f1, #69bff8, #52cffe, #41dfff, #46eefa, #5ffbf1);
   background-attachment: fixed;
}

	.button {
  background-color: #4CAF50; /* Green */
  border: none;
  border-radius: 4px;
  color: white;
  padding: 10px 10px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 12px;
  margin: 4px 2px;
  -webkit-transition-duration: 0.4s; /* Safari */
  transition-duration: 0.4s;
  cursor: pointer;
}

.button1 {
  background-color: white; 
  color: black; 
  border: 2px solid #f62a00;
}

.button1:hover {
  background-color: #ddeb02;
  color: white;
}
.button2 {
  background-color: white; 
  color: black; 
  border: 2px solid #1be02f;
}

.button2:hover {
  background-color: #1a85d6;
  color: white;
}

#button-login{
  font-family: "Roboto", sans-serif;
  text-transform: uppercase;
  outline: 0;
  background: #45aba6;
  width: 100%;
  border: 0;
  padding: 15px;
  color: #FFFFFF;
  font-size: 14px;
  -webkit-transition: all 0.3 ease;
  transition: all 0.3 ease;
  cursor: pointer;
  border-radius: 10px;
}

#button-login:hover,#button-login:active,#button-login:focus {
  background: #2f7471;
}
#pwd, #usr {
  font-family: "Roboto", sans-serif;
  outline: 0;
  background: white;
  width: 100%;
  border: 1px solid #D3D3D3;
  margin: 0 0 15px;
  padding: 15px;
  box-sizing: border-box;
  font-size: 14px;
  border-radius: 5px;
}

table{
margin : 0 auto 0;
}

//coba form baru
#formContainer {
	transition: 0.2s ease;
	height: 342.5px;
	transition-delay: 0.3s;
}

.formLeft {
	background: #fff;
	border-radius: 5px 0 0 5px;
	padding: 0 10px;
	box-sizing: border-box;
	align-items: center;
	width: 335px;
	float:left;
	img {
		display: block;
		width: 72px;
		border-radius: 50%;
		box-shadow: 0 5px 5px rgba(0,0,0,0.2);
	}
}

.formRight {
	position: relative;
	overflow: hidden;
	width: 245px;
	border-radius: 0 5px 5px 0;
	display: flex;
	flex-direction: column;
	justify-content: center;
	}

a{
color:#307774;
}
a:link {
  text-decoration: none;
  color:#307774
}
a:hover {
  color: #45aba6;
}
.a{
	margin-top:2px;
	text-align: right;
	font-size:10px;
}

.vl {
width: 5px;
float:left;
  border-left: 1px solid green;
  height: 250px;
}	
	
</style>

<?php
if (isset($_COOKIE["errorLogin"])) {
	// Error message
	$errorLogin = $_COOKIE["errorLogin"];
	echo "					<tr>\n";
	echo "						<td colspan='3' align='center'><div class='error'>$errorLogin</div></td>\n";
	echo "					</tr>\n";
	setcookie("errorLogin", "", time() - 10);
}
			

				// get default auth, if no authenticator is active
				if ($arAuth == null) {
					$auth = $Setting->GetAuth(1);	
					if ($auth != null) {
					 	$arAuth[0] = $auth;
					}
				}
				
				if ($arAuth != null) {
					foreach ($arAuth as $auth) {
						// class name
						$className = $auth["class"];
						$fileName = "inc/auth/" . $className . ".php";
						
						// exist?
						if (file_exists($fileName)) {
							include($fileName);
						
							$authClass = new $className();
							
							// get render input
							$input = $authClass->element();
							//var_dump($_REQUEST);
							foreach ($input as $key2 => $value2) {
								//$label = $value2["label"];
								$id = $value2["id"];
								if (isset($value2["input"])) {
									$element = $value2["input"];
								}
								$td = "";
								if (isset($value2["td"])) {
									$td = $value2["td"];
								}
								
								// NEW: input dipecah
								$type = isset($value2["type"])?$value2["type"]:"text";
								$autocomplete = isset($value2["autocomplete"])?$value2["autocomplete"]:"off";
								if (isset($value2["maxlength"])) {
									$maxlength = $value2["maxlength"];
								}
								
								echo "					<tr>\n";
								
								if (isset($element)) {
									echo "						<td>$element</td>\n";
								} else {
									$value = "";
									if (isset($_COOKIE["inputLogin" . $id])) {
										if ($autocomplete == 'on') {
											$value = $_COOKIE["inputLogin" . $id];
										}
										
										// delete cookie
										setcookie("inputLogin" . $id, "", time() - 10);
									}
								
									echo "						<td><input type='$type' name='$id' id='$id' value='$value' autocomplete='$autocomplete' ";
									if (isset($maxlength)) {
										echo "maxlength='$maxlength'";
									}
									echo "/></td>\n";
								}
								echo "					</tr>\n";
							}
							echo "					<tr>\n";
							
							echo "					</tr>\n";

						}
					}
?>
					<tr>
						<td colspan='3' align='center'><input type='submit' value='Login' id='button-login'></input></td>
					</tr>
<?php
				} else {
?>
					<tr>
						<td colspan='3' align='center'><div class='error'>ERROR: No authenticator installed</div></td>
					</tr>
<?php
				}

				
?>
					<tr>
				<td colspan='3' height='10px'>
				<div class="a">
				<a href='registrasi/registrasi.php'><i class='far fa-address-card'></i>Registrasi user wajib pajak</a><div>
				</td>
					</tr>
					
				</table>
			</form>
			</div>
			</div>
		</div>
	</div>
	<div><br>
	<br>

   <div class="row">
		<div class="col-md-12 txt-center" style="margin-bottom: 5px;">
			<a href="http://36.92.151.83:2010/portlet/portlet.php" target="_blank">
				<button type="button" class="btn btn-primary btn-orange" style="padding-top: 15px;padding-bottom: 15px;background-color: #df781e !important;box-shadow: none;border-radius: 3px;border: none;color: white;"><i class="fa fa-address-card"></i> Lihat Daftar Tagihan</button>
			</a>
		</div>
	</div>
</body>
