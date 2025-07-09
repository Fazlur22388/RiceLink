<?php
session_start();
session_destroy();
echo '<script>
      alert ("you are now logged out.see you again soon!");
      window.location.href ="../admin-module/login.php";
      </script>'; 
exit;

?>
