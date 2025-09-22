<?php
// api/attracties.php
require_once __DIR__ . '/helper.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if($method === 'GET' && ($action === 'list' || $action === '')){
    $stmt = $pdo->query("SELECT id,naam,locatie,type,specificaties,foto FROM attractie ORDER BY naam");
    $rows = $stmt->fetchAll();
    json_response(['success'=>true,'data'=>$rows]);
}

if($method === 'GET' && $action === 'get'){
    $id = (int)($_GET['id'] ?? 0);
    if(!$id) json_response(['success'=>false,'message'=>'id required'],400);
    $stmt = $pdo->prepare("SELECT * FROM attractie WHERE id = :id LIMIT 1");
    $stmt->execute(['id'=>$id]);
    $row = $stmt->fetch();
    json_response(['success'=>true,'data'=>$row]);
}

if($method === 'POST' && $action === 'create'){
    // minimal validation
    $input = get_json_input();
    $naam = trim($input['naam'] ?? '');
    if(!$naam) json_response(['success'=>false,'message'=>'Naam vereist'],400);
    // optional fields
    $loc = $input['locatie'] ?? null;
    $type = $input['type'] ?? null;
    $spec = $input['specificaties'] ?? null;
    $foto = $input['foto'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO attractie (naam, locatie, type, foto, specificaties) VALUES (:n, :loc, :type, :foto, :spec)");
    $stmt->execute(['n'=>$naam,'loc'=>$loc,'type'=>$type,'foto'=>$foto,'spec'=>$spec]);
    json_response(['success'=>true,'id'=>$pdo->lastInsertId()],201);
}

if($method === 'POST' && $action === 'update'){
    $input = get_json_input();
    $id = (int)($input['id'] ?? 0);
    if(!$id) json_response(['success'=>false,'message'=>'id required'],400);
    $fields = ['naam','locatie','type','foto','specificaties'];
    $sets = []; $params = ['id'=>$id];
    foreach($fields as $f){
        if(array_key_exists($f, $input)){
            $sets[] = "$f = :$f";
            $params[$f] = $input[$f];
        }
    }
    if(empty($sets)) json_response(['success'=>false,'message'=>'nothing to update'],400);
    $sql = "UPDATE attractie SET " . implode(', ', $sets) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_response(['success'=>true]);
}

if($method === 'POST' && $action === 'delete'){
    $input = get_json_input();
    $id = (int)($input['id'] ?? 0);
    if(!$id) json_response(['success'=>false,'message'=>'id required'],400);
    $stmt = $pdo->prepare("DELETE FROM attractie WHERE id = :id");
    $stmt->execute(['id'=>$id]);
    json_response(['success'=>true]);
}

json_response(['success'=>false,'message'=>'unknown action'],400);
