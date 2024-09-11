<?php

/**
 * adalah Function untuk menangani Proses monitoring 
 * dengan cara input data kecamata dan kelurahan. Dilengkapi juga 
 * dengan fungsi pencarian agar memudahkan Proses monitoring
 * @package Function
 * @subpackage monitoring
 * @author Bayu kusumah Rahman <bayu.kusumah@vsi.co.id>
 * @copyright (c) 2013, PT VallueStream International
 * @link http://www.vsi.co.id
 * 
 */
if (!isset($data)) {
  return;
}
?>

<script type="text/javascript" src="/inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="/inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="/inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js"></script>

echo '
<form id="form1" name="form1" method="post" action="" class='transparent'>
  <table width="250" border="0" cellpadding="0" cellspacing="1">
    <tr>
      <td width="75">IP</td>
      <td width="12">:</td>
      <td width="162"><label>
          <input type="text" name="ip" id="ip" />
        </label></td>
    </tr>
    <tr>
      <td>Port</td>
      <td>:</td>
      <td><label>
          <input type="text" name="port" id="port" />
        </label></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><label>
          <input type="submit" name="kirim" id="kirim" value="Submit" />
        </label></td>
    </tr>
  </table>
</form> ';