<?php
require_once __DIR__.'/../models/Item.php';
$item = new Item();
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$path = trim($_SERVER['PATH_INFO'] ?? '', '/');
try{
    if($method=='GET'){
        if($path=='') echo json_encode($item->all());
        else echo json_encode($item->find((int)$path));
        exit;
    }
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    if($method=='POST'){
        $data = ['name'=>$input['name']??'','description'=>$input['description']??'','quantity'=>intval($input['quantity']??0)];
        echo json_encode($item->create($data)); exit;
    }
    if($method=='PUT'){
        $id = (int)$path; echo json_encode($item->update($id,['name'=>$input['name']??'','description'=>$input['description']??'','quantity'=>intval($input['quantity']??0)])); exit;
    }
    if($method=='DELETE'){
        $id = (int)$path; echo json_encode(['deleted'=>$item->delete($id)]); exit;
    }
}catch(Exception $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
