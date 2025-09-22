<?php
// api/helper.php
require_once __DIR__ . '/db.php';

function json_response($data, $status=200){
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function get_json_input(){
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function require_login(){
    if(empty($_SESSION['user_id'])) {
        json_response(['success'=>false,'message'=>'Not authenticated'],401);
    }
}

function current_user(){
    global $pdo;
    if(empty($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare("SELECT id,naam,rol,gebruikersnaam,adres FROM personeel WHERE id = :id LIMIT 1");
    $stmt->execute(['id'=>$_SESSION['user_id']]);
    return $stmt->fetch();
}
