<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
if($data) {
	$uid = $data->uid;	
	$bOK = $User->IsModuleGranted($uid, $a, $m);
	if (!$bOK) {return false;}
	
	$bOk = $Setting->GetModule2($arModule, $m, array('start'=>1, 'end'=>1));
	echo '<div class="subTitle">',$arModule[0]['name'],'</div>';
	ScreenDefault();
}

function ScreenDefault() {
	global $NC, $a, $m, $func, $config, $Menu, $layout, $numRow, $hal, $keyfield, $keyval, $keystatus, $search;
	
	$aField = array('0'=>'External Application', '1'=>'NC Subsystem', '2'=>'Message Type', '3'=>'Module Code');
	ScriptFunction();
	
	$aHead = array('NO.', 'EXTERNAL APPLICATION', 'SUBSYSTEM', 'PORT', 'TYPE', 'MC', 'STATUS', 'ACTION');
	$nHead = count($aHead);
	if($keyfield == 0 || !isset($keyfield)) $arData['a'] = 'NCC_SS_SE_ID';
	elseif($keyfield == 0) $arData['a'] = 'NCC_SS_SI_ID';
	elseif($keyfield == 0) $arData['a'] = 'NCC_SS_SI_TYPE';
	else $arData['a'] = 'NCC_SS_SI_MC';
	$arData['b'] = (isset($keyval) ? $keyval : NULL);
	$arData['c'] = (isset($keystatus) ? $keystatus : 1);
	
	$form = new templateForm();
	$action = $config['url']['index'].'?PID='.base64_encode('a='.$a.'&m='.$m);
	$title = 'Search';
	$foot = array(setInput('search', 'Search', NULL, 'submit'));
	$field = array(
		setSelect(array('keyfield', $keyfield), $aField, array('id'=>'keyfield')) => '<span id="keyval"></span>',
		'Status' => setSelect(array('keystatus', $keystatus), array('1'=>'Active', '0'=>'Not Active')),
	);
	$forms = array('param' => $field, 'title' => NULL, 'foot' => $foot, 'notefoot' => NULL);
	echo $form->generateForm($action, $title, $forms);
	
	$bOk = $NC->SqlExec($arApp2SubNumRow, 'GetAppToSubNumRows', $arData);
	$arPage['param_url'] = (isset($search) ? 'a='.$a.'&m='.$m.'&search=search&keyfield='.$keyfield.'&keyval='.$keyval : 'a='.$a.'&m='.$m);
	$arPage['param_url'] .= (isset($numRow) ? '&numRow='.$numRow : NULL);
	$arPage['cur_page'] = (isset($hal) ? $hal : 1);
	$arPage['per_page'] = (isset($numRow) ? $numRow : 10);
	$arPage['total_rows'] = $arApp2SubNumRow[0]['NUM_ROWS'];
	$paging = new pagination($arPage);
	$arData['d'] = $paging->start-1;
	$arData['e'] = $paging->end;
	$sPage = $paging->create_links($nHead);
	
	$bOk = $NC->SqlExec($arApp2Sub, 'GetAppToSub', $arData);
	$nRes = count($arApp2Sub);
	if($arApp2SubNumRow[0]['NUM_ROWS'] > 0) {
		$aList = array();
		for($i=0;$i<$nRes;$i++) {
			$bOk = $NC->SqlExec($MCName, 'GetMCNameByCode', array('a'=>$arApp2Sub[$i]['NCC_SS_SI_MC']));
			$aList[$i]['NO'] = $i+1;
			$aList[$i]['APPNAME'] = $arApp2Sub[$i]['NCC_SE_NAME'];
			$aList[$i]['APPSUB'] = $arApp2Sub[$i]['NCC_SI_NAME'];
			$aList[$i]['APPPORT'] = (empty($arApp2Sub[$i]['NCC_SS_SI_PORT']) ? 'Not Set' : $arApp2Sub[$i]['NCC_SS_SI_PORT']);
			$aList[$i]['APPTYPE'] = '<a href="" title="'.$arApp2Sub[$i]['NCC_MT_DESC'].'" onclick="return false;">'.$arApp2Sub[$i]['NCC_MT_NAME'].'</a>';
			$aList[$i]['APPMC'] = $MCName[0]['NCM_MC_MODULE'];
			$aList[$i]['APPSTATUS'] = ($arApp2Sub[$i]['NCC_SS_STATUS'] == 1 ? 'Active' : 'Not Active');
			$optAttr[0] = array('onclick="return confirm(\'Delete '.$arApp2Sub[$i]['NCC_SE_NAME'].' to '.$arApp2Sub[$i]['NCC_SI_NAME'].'?\');"');
			$optAttr[1] = array('onclick'=>'return confirm(\'Edit '.$arApp2Sub[$i]['NCC_SE_NAME'].' to '.$arApp2Sub[$i]['NCC_SI_NAME'].'?\');');
			if($arApp2Sub[$i]['NCC_SS_STATUS'] == 1) {$src = '<a href='.$config['url']['index'].'?PID='.base64_encode('a='.$a.'&m='.$m.'&f='.$func[3]['id'].'&appext='.$arApp2Sub[$i]['NCC_SS_SE_ID'].'&set=0').' title="Set to Not Active"><div class="icon icon_MD_stop"></div></a>';}
			else {$src = '<a href='.$config['url']['index'].'?PID='.base64_encode('a='.$a.'&m='.$m.'&f='.$func[3]['id'].'&appext='.$arApp2Sub[$i]['NCC_SS_SE_ID'].'&set=1').' title="Set to Active"><div class="icon icon_MD_play"></div></a>';}
			$aList[$i]['ACTION'] = printOption(array('appext'=>$arApp2Sub[$i]['NCC_SS_SE_ID']), $optAttr).$src;
		}
		$list = new templateList(NULL, $aHead, $aList);
		$list->set_title(NULL, array('url'=>'a='.$a.'&m='.$m.'&search=search&keyfield='.$keyfield.'&keyval='.$keyval, 'numrow'=>$numRow));
		$list->set_tPage($sPage);
		echo $list->generateList();
	} else echo '<div class="message info">No data found</div>';
}

function ScriptFunction() {
	global $NC, $keyfield, $keyval;
	
	//External Application
	$bOk = $NC->SqlExec($arExtAppNumRow, 'GetExtAppNumRows', array('a'=>NULL));
	$bOk = $NC->SqlExec($arExtApp, 'GetExtApp', array('a'=>NULL, 'b'=>0, 'c'=>$arExtAppNumRow[0]['NUM_ROWS']));
	$ext[''] = '- ALL EXTERNAL APPLICATION -';
	for($i=0;$i<$arExtAppNumRow[0]['NUM_ROWS'];$i++) {$ext[$arExtApp[$i]['NCC_SE_ID']] = $arExtApp[$i]['NCC_SE_NAME'];}
	//NC Subsystem
	$bOk = $NC->SqlExec($arIntAppNumRow, 'GetIntAppNumRows', array('a'=>NULL));
	$bOk = $NC->SqlExec($arIntApp, 'GetIntApp', array('a'=>NULL, 'b'=>0, 'c'=>$arIntAppNumRow[0]['NUM_ROWS']));
	$int[''] = '- ALL NC SUBSYSTEM -';
	for($i=0;$i<$arIntAppNumRow[0]['NUM_ROWS'];$i++) {$int[$arIntApp[$i]['NCC_SI_ID']] = $arIntApp[$i]['NCC_SI_NAME'];}
	//Message Type
	$bOk = $NC->SqlExec($arMTNumRow, 'GetMsgTypeNumRows', array('a'=>NULL));
	$bOk = $NC->SqlExec($arMT, 'GetMsgType', array('a'=>NULL, 'b'=>0, 'c'=>$arMTNumRow[0]['NUM_ROWS']));
	$mt[''] = '- ALL MESSAGE TYPE -';
	for($i=0;$i<$arMTNumRow[0]['NUM_ROWS'];$i++) {$mt[$arMT[$i]['NCC_MT_ID']] = $arMT[$i]['NCC_MT_NAME'];}
	//Module Code
	$bOk = $NC->SqlExec($arMC, 'GetAllMC', array('a'=>NULL));
	$nMC = count($arMC);
	$mc[''] = '- ALL MODULE CODE -';
	for($i=0;$i<$nMC;$i++) {$mc[$arMC[$i]['NCM_MC_CODE']] = $arMC[$i]['NCM_MC_MODULE'];}
	
	echo '<script type="text/javascript">
		$(function() {
			$(\'#keyfield\').change(function() {ChangeInput($(this).val());});
			DefaultInput($(\'#keyfield\').val());
		});
		function ChangeInput(q) {
			var inputan;
			if(q == \'0\') inputan = \''.setSelect('keyval', $ext).'\';
			else if(q == \'1\') inputan = \''.setSelect('keyval', $int).'\';
			else if(q == \'2\') inputan = \''.setSelect('keyval', $mt).'\';
			else inputan = \''.setSelect('keyval', $mc).'\';
			$(\'#keyval\').html(inputan);
		}
		function DefaultInput(q) {
			var inputan;
			if(q == \'0\') inputan = \''.setSelect(array('keyval', $keyval), $ext).'\';
			else if(q == \'1\') inputan = \''.setSelect(array('keyval', $keyval), $int).'\';
			else if(q == \'2\') inputan = \''.setSelect(array('keyval', $keyval), $mt).'\';
			else inputan = \''.setSelect(array('keyval', $keyval), $mc).'\';
			$(\'#keyval\').html(inputan);
		}
	</script>';
}
?>