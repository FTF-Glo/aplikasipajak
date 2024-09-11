<?php
session_start();
?>
<HTML>
<HEAD>
<TITLE>PHP-CAPTCHA </TITLE>
<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=iso-8859-1\">
</HEAD>
<BODY onLoad="return focuson();">
<script   language="javascript">
function focuson()
  { document.form1.number.focus()}

function check()
	{
	if(document.form1.number.value==0)
		{
		alert("Please enter your Category Name");
		document.form1.number.focus();
		return false;
		}
	}

</script>
<?php

   if(isset($_REQUEST['Submit'])){
      $key=substr($_SESSION['key'],0,5);
      $number = $_REQUEST['number'];
      if($number!=$key){
          echo '<center><font face="Verdana, Arial, Helvetica, sans-serif" color="#FF0000">
		   Validation string not valid! Please try again!</font></center>';}
      else{
           echo '<center><font face="Verdana, Arial, Helvetica, sans-serif"  color="#66CC00">
		    Your string is valid!</font></center>';} 
     }
?>
<form name="form1" method="post" action="form.php"   onSubmit="return check();">
<table width="342" align="center" cellspacing="0" bgcolor="#D4D0C8">
<tr>
  <td colspan="4" align="center"><hr></td>
  </tr>
<tr>
  <td width="8" align="center">&nbsp;</td>
  <td width="330" align="right" valign="top">&nbsp;</td>
  <td width="330" align="right" valign="top">&nbsp;</td>
  <td width="2" align="center">&nbsp;</td>
</tr>
<tr>
  <td align="center">&nbsp;</td>
  <td align="right" valign="top">&nbsp;</td>
  <td align="right" valign="top">&nbsp;</td>
  <td align="center">&nbsp;</td>
</tr> 

<tr>
  <td align="center">&nbsp;</td>
  <td align="center">&nbsp;</td>
  <td align="center"><img src="php_captcha.php"></td>
  <td align="center">&nbsp;</td>
</tr>
<tr>
  <td align="center">&nbsp;</td>
  <td align="center">&nbsp;</td>
  <td align="center">&nbsp;</td>
  <td align="center">&nbsp;</td>
</tr>
<tr>
  <td align="center">&nbsp;</td>
  <td align="center"> Please enter the string shown in the image in the form.<br></td>
  <td align="center"><input name="number" type="text" id=\&quot;number\&quot;></td>
  <td align="center">&nbsp;</td>
</tr>
<tr>
  <td align="center">&nbsp;</td>
  <td align="center">&nbsp;</td>
  <td align="center">&nbsp;</td>
  <td align="center">&nbsp;</td>
</tr>
<tr>
  <td align="center">&nbsp;</td>
  <td align="center">&nbsp;</td>
  <td align="center"><input name="Submit" type="submit"   value="Submit"></td>
  <td align="center">&nbsp;</td>
</tr>
<tr>
  <td colspan="4" align="center"><hr></td>
  </tr>
</table>
</form>
</BODY>
</HTML>