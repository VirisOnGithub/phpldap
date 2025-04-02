<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "PDO connection success!";
} catch (PDOException $e) {
    echo "PDO error: " . $e->getMessage();
}
?>