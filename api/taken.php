<?php
// api/taken.php
require_once __DIR__ . '/helper.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if($method === 'GET' && ($action === 'list' || $action === '')){
    $status = $_GET['status'] ?? '';
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';

    $sql = "SELECT t.*, a.naam AS attractie_naam, p.naam AS personeel_naam
            FROM onderhoudstaak t
            LEFT JOIN attractie a ON a.id = t.attractie_id
            LEFT JOIN personeel p ON p.id = t.personeel_id";
    $clauses = []; $params = [];
    if($status){
        $clauses[] = "t.status = :status"; $params['status'] = $status;
    }
    if($from){
        $clauses[] = "t.datum >= :from"; $params['from'] = $from;
    }
    if($to){
        $clauses[] = "t.datum <= :to"; $params['to'] = $to;
    }
    if($clauses) $sql .= " WHERE " . implode(' AND ', $clauses);
    $sql .= " ORDER BY t.datum DESC, t.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_response(['success'=>true,'data'=>$stmt->fetchAll()]);
}

if($method === 'GET' && $action === 'get'){
    $id = (int)($_GET['id'] ?? 0);
    if(!$id) json_response(['success'=>false,'message'=>'id required'],400);
    $stmt = $pdo->prepare("SELECT t.*, a.naam AS attractie_naam, p.naam AS personeel_naam FROM onderhoudstaak t
      LEFT JOIN attractie a ON a.id = t.attractie_id
      LEFT JOIN personeel p ON p.id = t.personeel_id
      WHERE t.id = :id");
    $stmt->execute(['id'=>$id]);
    json_response(['success'=>true,'data'=>$stmt->fetch()]);
}

if($method === 'POST' && $action === 'create'){
    $input = get_json_input();
    $aid = (int)($input['attractie_id'] ?? 0);
    $datum = $input['datum'] ?? null;
    if(!$aid || !$datum) json_response(['success'=>false,'message'=>'attractie & datum vereist'],400);
    $pid = $input['personeel_id'] ?? null;
    $opm = $input['opmerkingen'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO onderhoudstaak (attractie_id, datum, status, opmerkingen, personeel_id) VALUES (:aid, :datum, 'open', :opm, :pid)");
    $stmt->execute(['aid'=>$aid,'datum'=>$datum,'opm'=>$opm,'pid'=>$pid]);
    json_response(['success'=>true,'id'=>$pdo->lastInsertId()],201);
}

if($method === 'POST' && $action === 'update'){
    $input = get_json_input();
    $id = (int)($input['id'] ?? 0);
    if(!$id) json_response(['success'=>false,'message'=>'id required'],400);

    $fields = ['status','opmerkingen','personeel_id','datum'];
    $sets = []; $params = ['id'=>$id];
    foreach($fields as $f){
        if(array_key_exists($f, $input)){
            $sets[] = "$f = :$f";
            $params[$f] = $input[$f];
        }
    }
    if(empty($sets)) json_response(['success'=>false,'message'=>'nothing to update'],400);
    $sql = "UPDATE onderhoudstaak SET " . implode(', ', $sets) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_response(['success'=>true]);
}

json_response(['success'=>false,'message'=>'unknown action'],400);
