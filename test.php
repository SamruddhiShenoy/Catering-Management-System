<?php 
session_start(); 
$_SESSION['user_id']=2; 
$_SESSION['user_role']='client'; 
require 'config.php'; 
ob_start(); 
require 'client/book.php'; 
file_put_contents('html.txt', ob_get_clean()); 
?>
