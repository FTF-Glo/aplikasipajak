<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'deposit', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/deposit/ServiceDeposit.php");

//prod
$Dep = new ServiceDeposit("127.0.0.1", "29090", 60);
$account = "5301030195";
/*
$success = $Dep->Register($rc, $account,0,"Deposit Test");
if($success)
	echo "Register account ". $account." Success<br>";
else{
	echo "Register account ". $account." Failed RC $rc  :".$Dep->getRCMessage("2610",$rc)."<br>";
}


echo "<br>Using Account number is ".$account."<br><br>";

$balance = $Dep->BalanceCheck($rc, $account);
if($rc=="0000")
	echo "Current balance is ".$balance."<br>";
else
	echo "Check balance is Failed RC $rc  :".$Dep->getRCMessage("2210",$rc)."<br>";

echo "<br>";


$amount = 10000;
$Dep->Debit($rc,$account, $amount,"test1","88001","0110101");
if($rc=="0000")
	echo "Debit ".$amount." Succsess<br>";
else
	echo "Debit $amount Failed RC $rc  :".$Dep->getRCMessage("2210",$rc)."<br>";
$balance = $Dep->BalanceCheck($rc, $account);
if($rc=="0000")
	echo "Current balance is ".$balance."<br>";
else
	echo "Check balance is Failed RC $rc  :".$Dep->getRCMessage("2210",$rc)."<br>";

echo "<br>";

$Dep->Credit($rc,$account, $amount);
if($rc=="0000")
	echo "Credit ".$amount." Succsess<br>";
else
	echo "Credit $amount Failed RC $rc  :".$Dep->getRCMessage("2210",$rc)."<br>";
$balance = $Dep->BalanceCheck($rc, $account);
if($rc=="0000")
	echo "Current balance is ".$balance."<br>";
else
	echo "Check balance is Failed RC $rc  :".$Dep->getRCMessage("2210",$rc)."<br>";

echo "<br>";
*/

echo "Melihat list transaksi <br>";
$list = $Dep->ListTransaction($rc,$account, "20110419", "20110426",2,10);
//$list = $Dep->ListTransaction($rc,$account, "20110426", "20110426",1,1,0,1,"FE77926DA24740839C38C2ABEB5FF6C9");
echo "<pre>";
print_r($list);
echo "</pre>";
echo "RC:$rc<br>\n";

/*
echo "Melakukan permintaan maintenance<br>";
if ($Dep->Maintenance($rc,$account)) {
	echo "RC:$rc<br>";
	echo "Maintenance berhasil<br>";
 } else {
	 echo "RC:$rc<br>";
	 echo "Maintenance gagal<br>";
 }
*/
// $target_account = "1849890629672405422";
// echo "Account target is $target_account with balance is ".$Dep->BalanceCheck($target_account)."<br>";
// echo "Melakukan transfer sebesar $amount ke akun $target_account<br>";
// if ($Dep->Transfer($account, $amount, "Test transfer", $target_account)) {
	// echo "Transfer berhasil<br>";
// } else {
	// echo "Transfer gagal<br>";
// }
// echo "Account target $target_account balance is now ".$Dep->BalanceCheck($target_account)."<br>";

/*
echo "Menampilkan seluruh akun<br>";
$acc_list = $Dep->ListAccount($rc, "4");
echo "RC : $rc";
echo "<pre>";
print_r($acc_list);
echo "</pre>";

/*
echo "Menampilkan relasi kepada akun $account<br>";
$acc_rel = $Dep->ListRelation($rc, $account, 4, 0);
echo "RC : $rc";
echo "<pre>";
print_r($acc_rel);
echo "</pre>";

*/
echo "Menampilkan info account<br>";
$acc_list = $Dep->QueryAccount($rc,0,"5201050513",1);

if($acc_list ==false){
	echo "RC : $rc " . $Dep->getRCMessage("2610",$rc);
}else{
	echo "<pre>";
	print_r($acc_list);
	echo "</pre>";
} 
 
?>
