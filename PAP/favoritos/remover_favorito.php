<?php
session_start();
include_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    echo json_encode(['status'=>'erro']);
    exit;
}

$id = intval($_POST['id']);

$sql = "DELETE FROM favoritos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();

echo json_encode(['status'=>'ok']);