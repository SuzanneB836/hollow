<?php
// api/db.php
declare(strict_types=1);
session_start();
ini_set('display_errors','1');
error_reporting(E_ALL);

// XAMPP defaults
$DB_HOST = '127.0.0.1';
$DB_NAME = 'hollow_mountains';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP default: leeg

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'message'=>'DB connect error: '.$e->getMessage()]);
    exit;
}
