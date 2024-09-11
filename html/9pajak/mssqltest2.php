<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

 
$server = '103.76.172.165:1433';
$username = 'iprotax';
$password = 'iprotax';
$database = 'IPROTAX';
$connection = mssql_connect($server, $username, $password);
 
if($connection != FALSE)
{
echo "Connected to the database server OK<br />";
}
else
{
die("Couldn't connect");
}
 
if(mssql_select_db($database, $connection))
{
echo "Selected $database ok<br />";
}
else
{
die('Failed to select DB');
}
 
$query_result = mssql_query('SELECT @@VERSION');
$row = mssql_fetch_array($query_result);
 
if($row != FALSE)
{
echo "Version is {$row[0]}<br />";
}
mssql_free_result($query_result);
mssql_close($connection);