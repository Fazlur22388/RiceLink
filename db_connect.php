<?php 

$server="localhost";
$user="root";
$password="";
$dbname="ricelink";

$conn=mysqli_connect($server, $user, $password, $dbname);

if(!$conn){

	die("Database connection failed: " . mysqli_connect_error());
}

 ?>