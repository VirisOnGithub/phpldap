<?php
function getIP(){
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

function log_error(string $username, string $status){
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=logs', 'root', 'root');
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int) $e->getCode());
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $query = "INSERT INTO authentification_attempts(username, status, timestamp, ip_address) VALUES (:username, :status, FROM_UNIXTIME(:timestamp), :ip_address)";
    $stmt = $pdo->prepare($query);
    $timestamp = time();
    $ip = getIP();
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':timestamp', $timestamp);
    $stmt->bindParam(':ip_address', $ip);
    return $stmt->execute();
}