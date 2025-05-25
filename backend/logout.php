<?php
<<<<<<< HEAD

session_start();
session_destroy();

header("Location: login.php");
exit();

=======
// Start the session
session_start();

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to login page or homepage
header("Location: login.php");
exit();
>>>>>>> 17c5f90 (added sql databse file)
?>