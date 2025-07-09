<?php 

$server="localhost";
$user="root";
$password="";
$dbase="ricelink";

$conn=mysqli_connect($server, $user, $password, $dbase );

if(!$conn){

	die("Connection failed: ".mysqli_error());

}

echo '<script>
      alert ("Connected successfully");
      
      </script>'; 


?>
