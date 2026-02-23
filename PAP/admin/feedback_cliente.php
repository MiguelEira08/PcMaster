<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index/index.php");
    exit;
}
$nome_filtro   = isset($_GET['nome']) ? trim($_GET['nome']) : '';
$status_filtro = isset($_GET['status']) ? trim($_GET['status']) : '';

$query = "
    SELECT f.id, u.nome AS nome_utilizador, f.Motivo, f.origem_pagina, f.feedback, f.data_envio, f.status,
           ra.resposta_admin, ra.nome_admin, ra.data_envio AS data_resposta
    FROM feedback f
    JOIN utilizadores u ON f.user_id = u.id
    LEFT JOIN respostas_admin ra ON ra.feedback_id = f.id
    WHERE 1=1
";

$params = [];
$types  = "";

if ($nome_filtro !== '') {
    $query .= " AND u.nome LIKE ?";
    $params[] = "%$nome_filtro%";
    $types .= "s";
}

if ($status_filtro !== '') {
    $query .= " AND f.status = ?";
    $params[] = $status_filtro;
    $types .= "s";
}

$query .= " ORDER BY f.data_envio DESC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Feedback</title>
    <link rel="stylesheet" href="../css/admin_produto.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.6/css/dataTables.dataTables.css" />
</head>
<body>
                       <a href="../admin/admin_dashboard.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
    <div class="overlay"></div>
    <br><br><br>
    <div class="content">
        <div class="admin-container">
            <h1>Gestão de Feedback</h1>

            <?php if ($result->num_rows > 0): ?>
                   <table  id="tabela" class="datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilizador</th>
                            <th>Motivo</th>
                            <th>Origem</th>
                            <th>Mensagem</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['nome_utilizador']) ?></td>
                                <td><?= htmlspecialchars($row['Motivo']) ?></td>
                                <td><?= htmlspecialchars($row['origem_pagina']) ?></td>
                                <td><?= nl2br(htmlspecialchars($row['feedback'])) ?></td>
                                <td><?= htmlspecialchars($row['data_envio']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                                <td>
                                    <?php if (empty($row['resposta_admin'])): ?>
                                        <a href="responder_feedback.php?id=<?= $row['id'] ?>" class="btn responder">Responder</a>
                                    <?php else: ?>
                                        <span style="color: green; font-weight: bold;">Respondido</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum feedback encontrado com os critérios selecionados.</p>
            <?php endif; ?>
            
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.3.6/js/dataTables.js"></script>
<script src="scriptadmin.js"></script>
        </div>

    </div>
</div>
</body>
</html>
