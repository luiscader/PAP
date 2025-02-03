<?php
session_start();
session_destroy(); // Destrói a sessão atual
header("Location: index.php"); // Redireciona para a página de index
exit();
?>
