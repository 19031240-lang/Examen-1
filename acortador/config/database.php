<?php

$host = 'localhost';        
$puerto = '3307';           
$dbname = 'url_shortener';  
$username = 'root';          
$password = '';              

try {
    
    $pdo = new PDO("mysql:host=$host;port=$puerto;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
} catch(PDOException $e) {
    die("Error de conexion:" . $e->getMessage());
}
?>