<?php
session_start();
include_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

// 🔥 usar user_id correto
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'erro']);
    exit;
}

$id_utilizador = $_SESSION['user_id'];
$id_item = intval($_POST['id_item']);
$tipo_item = $_POST['tipo_item'];

// Verificar se já existe
$sql = "SELECT id FROM favoritos 
        WHERE id_utilizador = ? 
        AND id_item = ? 
        AND tipo_item = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $id_utilizador, $id_item, $tipo_item);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    // remover
    $sql = "DELETE FROM favoritos 
            WHERE id_utilizador = ? 
            AND id_item = ? 
            AND tipo_item = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $id_utilizador, $id_item, $tipo_item);
    $stmt->execute();

    echo json_encode(['status' => 'removido']);

} else {

    // inserir
    $sql = "INSERT INTO favoritos (id_utilizador, id_item, tipo_item) 
            VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $id_utilizador, $id_item, $tipo_item);
    $stmt->execute();

    echo json_encode(['status' => 'adicionado']);
}