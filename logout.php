<?php
session_start();
session_unset();
session_destroy();

// Redirigir al home
header('Location: index.php');
exit();
?>