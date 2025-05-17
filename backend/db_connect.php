<?php 


$server="mysql";
$user="ricelink_user";
$password="ricelink_password";
$dbname="ricelink";

$conn=mysqli_connect($server, $user, $password, $dbname);

if(!$conn){

	die("Database connection failed: " . mysqli_connect_error());
}

 ?>