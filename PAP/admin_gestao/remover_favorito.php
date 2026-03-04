<?php
session_start();
include_once __DIR__ . '/../db.php';

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    exit('erro');
}

$id = intval($_POST['id']);

$stmt = $conn->prepare("DELETE FROM favoritos WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "ok";
} else {
    echo "erro";
}