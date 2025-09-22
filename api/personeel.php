<?php
// api/personeel.php
require_once __DIR__ . '/helper.php';

$action = $_GET['action'] ?? 'list';

if($action === 'list'){
    $stmt = $pdo->query("SELECT id, naam, rol, gebruikersnaam, adres FROM personeel ORDER BY naam");
    json_response(['success'=>true,'data'=>$stmt->fetchAll()]);
}

if($action === 'get'){
    $id = (int)($_GET['id'] ?? 0);
    if(!$id) json_response(['success'=>false,'message'=>'id required'],400);
    $stmt = $pdo->prepare("SELECT id, naam, rol, gebruikersnaam, adres FROM personeel WHERE id = :id LIMIT 1");
    $stmt->execute(['id'=>$id]);
    json_response(['success'=>true,'data'=>$stmt->fetch()]);
}

json_response(['success'=>false,'message'=>'unknown action'],400);
