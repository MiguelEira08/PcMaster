<?php
ob_start();
session_start();
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

$id_utilizador = $_SESSION['user_id'];

// Definir volta inteligente
$_SESSION['voltar_inteligente'] = 'conta.php';

// Verificar se é admin
$stmt = $conn->prepare("SELECT nome, email, tipo FROM utilizadores WHERE id = ?");
if (!$stmt) {
    die('Erro no prepare da verificação de admin: ' . $conn->error);
}
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$utilizador_atual = $stmt->get_result()->fetch_assoc();
$stmt->close();

$is_admin = isset($utilizador_atual['tipo']) && $utilizador_atual['tipo'] === 'admin';

// Processar ações
$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $acao = $_POST['action'];
    $agendamento_id = (int)$_POST['id'];

    $estados_validos = ['confirmado', 'cancelado', 'concluido', 'pendente'];

    if (in_array($acao, $estados_validos)) {
        if ($is_admin) {
            $stmt = $conn->prepare("UPDATE agendamentos SET estado = ? WHERE id = ?");
            if (!$stmt) {
                die('Erro no prepare do update: ' . $conn->error);
            }
            $stmt->bind_param("si", $acao, $agendamento_id);
        } else {
            if ($acao !== 'cancelado') {
                $msg = 'Não tem permissão para esta ação.';
                $msg_type = 'error';
                goto render;
            }
            $stmt = $conn->prepare("UPDATE agendamentos SET estado = ? WHERE id = ? AND utilizador_id = ?");
            if (!$stmt) {
                die('Erro no prepare do update (user): ' . $conn->error);
            }
            $stmt->bind_param("sii", $acao, $agendamento_id, $id_utilizador);
        }

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $msg = 'Agendamento atualizado com sucesso.';
            $msg_type = 'success';
        } else {
            $msg = 'Não foi possível atualizar o agendamento.';
            $msg_type = 'error';
        }
        $stmt->close();
    }
}

render:
// Capturar Filtros do GET
$filtro_estado = $_GET['estado'] ?? '';
$filtro_data   = $_GET['data'] ?? '';
$filtro_tipo   = $_GET['tipo'] ?? '';

// Buscar agendamentos
$where = [];
$params = [];
$types  = '';

if (!$is_admin) {
    $where[] = 'a.utilizador_id = ?';
    $params[] = $id_utilizador;
    $types .= 'i';
}

// Aplicar filtros à query
if ($filtro_estado !== '') {
    $where[] = 'a.estado = ?';
    $params[] = $filtro_estado;
    $types .= 's';
}
if ($filtro_data !== '') {
    $where[] = 'a.data_agendamento = ?';
    $params[] = $filtro_data;
    $types .= 's';
}
if ($filtro_tipo !== '') {
    $where[] = 'a.tipo_servico = ?';
    $params[] = $filtro_tipo;
    $types .= 's';
}

$sql = "SELECT a.*, u.nome, u.email 
        FROM agendamentos a 
        JOIN utilizadores u ON u.id = a.utilizador_id";

if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY a.data_agendamento ASC, a.hora_inicio ASC';

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$agendamentos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Agrupar agendamentos por estado
$agendamentos_pendente = [];
$agendamentos_confirmado = [];
$agendamentos_concluido = [];
$agendamentos_cancelado = [];

foreach ($agendamentos as $a) {
    if ($a['estado'] === 'pendente') {
        $agendamentos_pendente[] = $a;
    } elseif ($a['estado'] === 'confirmado') {
        $agendamentos_confirmado[] = $a;
    } elseif ($a['estado'] === 'concluido') {
        $agendamentos_concluido[] = $a;
    } elseif ($a['estado'] === 'cancelado') {
        $agendamentos_cancelado[] = $a;
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../imagens/icon.png">
    <title><?= $is_admin ? 'Gestão de Agendamentos' : 'Os Meus Agendamentos' ?></title>
    <link rel="stylesheet" href="../css/conta_compra.css">
</head>
<body>
    <a href="javascript:history.back()" class="botao-voltar voltar-fixo">← Voltar</a>
<div class="bg">
    <div class="overlay"></div>
    <div class="content">
<div class="estado-bloco" style="margin-top: 50px;">
            <form method="GET" style="display: flex; flex-direction: column; align-items: center;">
                
                <h2 class="titulo-estado" style="width: 100%;"><?= $is_admin ? 'Gestão de Agendamentos' : 'Os Meus Agendamentos' ?></h2>
                
                <a href="pedido_agenda.php" class="botao-link" style="margin-bottom: 20px; text-decoration: none;">Novo Agendamento</a>

                <?php if ($msg): ?>
                    <p class="<?= $msg_type === 'success' ? 'success-message' : 'error-message' ?>" style="padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.5);">
                        <?= htmlspecialchars($msg) ?>
                    </p>
                <?php endif; ?>

                <h2 style="margin-bottom: 20px;">Filtrar</h2>

                <div class="caixa-container" style="margin-bottom: 20px;">
                    <select name="tipo" style="padding: 10px; border-radius: 8px; border: none; font-family: 'Poppins', sans-serif; font-size: 1rem; cursor: pointer;">
                        <option value="">Serviço: Todos</option>
                        <option value="montagem"  <?= $filtro_tipo==='montagem'  ? 'selected':'' ?>>Montagem</option>
                        <option value="reparação" <?= $filtro_tipo==='reparação' ? 'selected':'' ?>>Reparação</option>
                    </select>

                    <input type="date" name="data" value="<?= htmlspecialchars($filtro_data) ?>" title="Data" style="padding: 10px; border-radius: 8px; border: none; font-family: 'Poppins', sans-serif; font-size: 1rem; cursor: pointer;">
                </div>

                <div class="caixa-container">
                    <button type="submit" class="botao-link" style="border: none;">Aplicar</button>
                    <a href="gerir_agendamento.php" class="botao-link" style="text-decoration: none;">Limpar</a>
                </div>
                
            </form>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 20px;">
            <div class="estado-bloco">
                <h2 class="titulo-estado">Pendentes</h2>
                <div class="cards-container">
                    <?php if ($agendamentos_pendente): ?>
                        <?php foreach ($agendamentos_pendente as $a): ?>
                            <div class="encomenda-card">
                                <div class="card-header">
                                    <span class="order-id">#<?= $a['id'] ?></span>
                                    <span class="status-badge pendente2">Pendente</span>
                                </div>
                                <div class="card-body">
                                    <?php if ($is_admin): ?>
                                        <p2><strong>Cliente:</strong> <?= htmlspecialchars($a['nome']) ?></p2><br>
                                        <p2><strong>Email:</strong> <?= htmlspecialchars($a['email']) ?></p2><br>
                                    <?php endif; ?>
                                    <p2><strong>Data:</strong> <?= date('d/m/Y', strtotime($a['data_agendamento'])) ?></p2><br>
                                    <p2><strong>Horário:</strong> <?= substr($a['hora_inicio'], 0, 5) ?> → <?= substr($a['hora_fim'], 0, 5) ?></p2><br>
                                    <p2><strong>Serviço:</strong> <?= ucfirst($a['tipo_servico']) ?></p2><br>
                                    <p2><strong>Local:</strong> <?= htmlspecialchars($a['localidade']) ?></p2><br>
                                    <div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                            <?php if ($is_admin): ?>
                                                <button type="submit" name="action" value="confirmado" class="botao" style="padding: 8px 16px; font-size: 12px;">Confirmar</button>
                                                <button type="submit" name="action" value="cancelado" class="botao" onclick="return confirm('Cancelar agendamento?')">Cancelar</button>
                                            <?php else: ?>
                                                <button type="submit" name="action" value="cancelado" class="botao-cancelar" style="padding: 8px 16px; font-size: 12px;" onclick="return confirm('Tens a certeza?')">Cancelar</button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p2 class="empty-msg">Nenhum agendamento pendente.</p2>
                    <?php endif; ?>
                </div>
            </div>

            <div class="estado-bloco">
                <h2 class="titulo-estado">Confirmados</h2>
                <div class="cards-container">
                    <?php if ($agendamentos_confirmado): ?>
                        <?php foreach ($agendamentos_confirmado as $a): ?>
                            <div class="encomenda-card">
                                <div class="card-header">
                                    <span class="order-id">#<?= $a['id'] ?></span>
                                    <span class="status-badge confirmado">Confirmado</span>
                                </div>
                                <div class="card-body">
                                    <?php if ($is_admin): ?>
                                        <p2><strong>Cliente:</strong> <?= htmlspecialchars($a['nome']) ?></p2><br>
                                        <p2><strong>Email:</strong> <?= htmlspecialchars($a['email']) ?></p2><br>
                                    <?php endif; ?>
                                    <p2><strong>Data:</strong> <?= date('d/m/Y', strtotime($a['data_agendamento'])) ?></p2><br>
                                    <p2><strong>Horário:</strong> <?= substr($a['hora_inicio'], 0, 5) ?> → <?= substr($a['hora_fim'], 0, 5) ?></p2><br>
                                    <p2><strong>Serviço:</strong> <?= ucfirst($a['tipo_servico']) ?></p2><br>
                                    <p2><strong>Local:</strong> <?= htmlspecialchars($a['localidade']) ?></p2><br>
                                    <div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                            <?php if ($is_admin): ?>
                                                <button type="submit" name="action" value="concluido" class="botao" style="padding: 8px 16px; font-size: 12px;">Concluir</button>
                                                <button type="submit" name="action" value="cancelado" class="botao" style="padding: 8px 16px; font-size: 12px;" onclick="return confirm('Cancelar agendamento?')">Cancelar</button>
                                            <?php else: ?>
                                                <p style="color: var(--accent2); font-weight: bold; margin: 0;">Agendamento confirmado</p>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p2 class="empty-msg">Nenhum agendamento confirmado.</p2>
                    <?php endif; ?>
                </div>
            </div>

            <div class="estado-bloco">
                <h2 class="titulo-estado">Concluídos</h2>
                <div class="cards-container">
                    <?php if ($agendamentos_concluido): ?>
                        <?php foreach ($agendamentos_concluido as $a): ?>
                            <div class="encomenda-card">
                                <div class="card-header">
                                    <span class="order-id">#<?= $a['id'] ?></span>
                                    <span class="status-badge entregue">Concluído</span>
                                </div>
                                <div class="card-body">
                                    <?php if ($is_admin): ?>
                                        <p2><strong>Cliente:</strong> <?= htmlspecialchars($a['nome']) ?></p2><br>
                                        <p2><strong>Email:</strong> <?= htmlspecialchars($a['email']) ?></p2><br>
                                    <?php endif; ?>
                                    <p2><strong>Data:</strong> <?= date('d/m/Y', strtotime($a['data_agendamento'])) ?></p2><br>
                                    <p2><strong>Horário:</strong> <?= substr($a['hora_inicio'], 0, 5) ?> → <?= substr($a['hora_fim'], 0, 5) ?></p2><br>
                                    <p2><strong>Serviço:</strong> <?= ucfirst($a['tipo_servico']) ?></p2><br>
                                    <p2><strong>Local:</strong> <?= htmlspecialchars($a['localidade']) ?></p2><br>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p2 class="empty-msg">Nenhum agendamento concluído.</p2>
                    <?php endif; ?>
                </div>
            </div>

            <div class="estado-bloco">
                <h2 class="titulo-estado">Cancelados</h2>
                <div class="cards-container">
                    <?php if ($agendamentos_cancelado): ?>
                        <?php foreach ($agendamentos_cancelado as $a): ?>
                            <div class="encomenda-card">
                                <div class="card-header">
                                    <span class="order-id">#<?= $a['id'] ?></span>
                                    <span class="status-badge pendente">Cancelado</span>
                                </div>
                                <div class="card-body">
                                    <?php if ($is_admin): ?>
                                        <p2><strong>Cliente:</strong> <?= htmlspecialchars($a['nome']) ?></p2><br>
                                        <p2><strong>Email:</strong> <?= htmlspecialchars($a['email']) ?></p2><br>
                                    <?php endif; ?>
                                    <p2><strong>Data:</strong> <?= date('d/m/Y', strtotime($a['data_agendamento'])) ?></p2><br>
                                    <p2><strong>Horário:</strong> <?= substr($a['hora_inicio'], 0, 5) ?> → <?= substr($a['hora_fim'], 0, 5) ?></p2><br>
                                    <p2><strong>Serviço:</strong> <?= ucfirst($a['tipo_servico']) ?></p2><br>
                                    <p2><strong>Local:</strong> <?= htmlspecialchars($a['localidade']) ?></p2><br>
                                    <div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                            <?php if ($is_admin): ?>
                                                <button type="submit" name="action" value="pendente" class="botao" style="padding: 8px 16px; font-size: 12px;">Reabrir</button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p2 class="empty-msg">Nenhum agendamento cancelado.</p2>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
</body>
</html>