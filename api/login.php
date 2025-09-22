<?php
// api/login.php
require_once __DIR__ . '/helper.php';

$data = get_json_input();
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if(!$username || !$password) json_response(['success'=>false,'message'=>'Missing username/password'],400);

$stmt = $pdo->prepare("SELECT id,naam,rol,gebruikersnaam,wachtwoord,adres FROM personeel WHERE gebruikersnaam = :u LIMIT 1");
$stmt->execute(['u'=>$username]);
$user = $stmt->fetch();

if(!$user) json_response(['success'=>false,'message'=>'User not found'],404);

if(password_verify($password, $user['wachtwoord'])){
    // set session
    $_SESSION['user_id'] = (int)$user['id'];
    unset($user['wachtwoord']);
    json_response(['success'=>true,'user'=>$user]);
} else {
    json_response(['success'=>false,'message'=>'Invalid credentials'],401);
}
