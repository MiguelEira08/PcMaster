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


$stmt = $conn->prepare("SELECT nome, email, tipo FROM utilizadores WHERE id = ?");
if (!$stmt) die('Erro no prepare: ' . $conn->error);
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$utilizador_atual = $stmt->get_result()->fetch_assoc();
$stmt->close();

$is_admin = isset($utilizador_atual['tipo']) && $utilizador_atual['tipo'] === 'admin';


$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $acao = $_POST['action'];
    $agendamento_id = (int)$_POST['id'];
    $descricao = trim($_POST['descricao'] ?? '');
    $estados_validos = ['confirmado', 'cancelado', 'concluido', 'pendente'];

  
    if ($acao === 'descricao') {
        if ($is_admin) {
            $stmt = $conn->prepare("UPDATE agendamentos SET descricao = ? WHERE id = ?");
            $stmt->bind_param("si", $descricao, $agendamento_id);
        } else {
           
            $stmt = $conn->prepare("UPDATE agendamentos SET descricao = ? WHERE id = ? AND utilizador_id = ?");
            $stmt->bind_param("sii", $descricao, $agendamento_id, $id_utilizador);
        }

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $msg = 'Descrição atualizada com sucesso.';
                $msg_type = 'success';
            } else {
                $msg = 'Nenhuma alteração foi feita na descrição.';
                $msg_type = 'error'; // ou success, dependendo da tua preferência
            }
        } else {
            $msg = 'Não foi possível atualizar a descrição.';
            $msg_type = 'error';
        }
        $stmt->close();
    } 
    // Outras ações de estado
    elseif (in_array($acao, $estados_validos)) {
        if ($is_admin) {
            $stmt = $conn->prepare("UPDATE agendamentos SET estado = ?, descricao = ? WHERE id = ?");
            $stmt->bind_param("ssi", $acao, $descricao, $agendamento_id);
        } else {
            if ($acao !== 'cancelado') {
                $msg = 'Não tem permissão para esta ação.';
                $msg_type = 'error';
                goto render;
            }
            $stmt = $conn->prepare("UPDATE agendamentos SET estado = ?, descricao = ? WHERE id = ? AND utilizador_id = ?");
            $stmt->bind_param("ssii", $acao, $descricao, $agendamento_id, $id_utilizador);
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
$filtro_estado = $_GET['estado'] ?? '';
$filtro_data   = $_GET['data'] ?? '';
$filtro_tipo   = $_GET['tipo'] ?? '';

$where = [];
$params = [];
$types  = '';

if (!$is_admin) {
    $where[] = 'a.utilizador_id = ?';
    $params[] = $id_utilizador;
    $types .= 'i';
}
if ($filtro_estado !== '') { $where[] = 'a.estado = ?';            $params[] = $filtro_estado; $types .= 's'; }
if ($filtro_data   !== '') { $where[] = 'a.data_agendamento = ?';  $params[] = $filtro_data;   $types .= 's'; }
if ($filtro_tipo   !== '') { $where[] = 'a.tipo_servico = ?';      $params[] = $filtro_tipo;   $types .= 's'; }

$sql = "SELECT a.*, u.nome, u.email FROM agendamentos a JOIN utilizadores u ON u.id = a.utilizador_id";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY a.data_agendamento ASC, a.hora_inicio ASC';

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$agendamentos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Agrupar por estado
$grupos = ['pendente' => [], 'confirmado' => [], 'concluido' => [], 'cancelado' => []];
foreach ($agendamentos as $a) {
    if (isset($grupos[$a['estado']])) $grupos[$a['estado']][] = $a;
}

// Contagens totais sem filtro de estado
$sql_count = "SELECT estado, COUNT(*) as total FROM agendamentos" . (!$is_admin ? " WHERE utilizador_id = $id_utilizador" : "") . " GROUP BY estado";
$res_count = $conn->query($sql_count);
$contagens = ['pendente'=>0,'confirmado'=>0,'concluido'=>0,'cancelado'=>0];
while ($row = $res_count->fetch_assoc()) $contagens[$row['estado']] = $row['total'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../imagens/icon.png">
    <title><?= $is_admin ? 'Gestão de Agendamentos' : 'Os Meus Agendamentos' ?> — PcMaster</title>
    <link rel="stylesheet" href="../css/admin_produto.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.6/css/dataTables.dataTables.css" />
    <style>
    .modal-overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex; align-items: center; justify-content: center;
        z-index: 1000;
    }
    .modal-content {
    background: rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(1px);
  -webkit-backdrop-filter: blur(15px);
        padding: 20px;
        border-radius: 10px;
        width: 90%; max-width: 400px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.2);
    }
    .modal-content textarea { 
        width:100%; 
        box-sizing:border-box; 
        resize:none; 
        padding: 10px; 
        font-family: inherit;    
        background: rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(1px);
  -webkit-backdrop-filter: blur(15px);
    border-radius: 10px;
}

    .modal-buttons { display:flex; justify-content:flex-end; gap:6px; margin-top:10px; }
    </style>

</head>
<body>
                       <a href="../admin/admin_dashboard.php" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
    <div class="overlay"></div>
    <br><br><br>
    <div class="content">
        <h1><?= $is_admin ? 'Gestão de Agendamentos' : 'Os Meus Agendamentos' ?></h1>

        <?php if ($msg): ?>
            <div class="msg-box <?= $msg_type === 'success' ? 'msg-success' : 'msg-error' ?>" style="background: burlywood; padding: 10px; border-radius: 5px; color: #000; margin-bottom: 15px;">
                <span><?= $msg_type === 'success' ? '✔' : '✖' ?></span>
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

         <div class="table-container">
            <table id="tabela" class="datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <?php if ($is_admin): ?>
                    <th>Cliente</th>
                    <th>Email</th>
                    <?php endif; ?>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Serviço</th>
                    <th>Localidade</th>
                    <th>Descrição</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($agendamentos)): ?>
                    <?php foreach ($agendamentos as $a): ?>
                        <tr>
                            <td><?= $a['id'] ?></td>
                            <?php if ($is_admin): ?>
                                <td><?= htmlspecialchars($a['nome']) ?></td>
                                <td><?= htmlspecialchars($a['email']) ?></td>
                            <?php endif; ?>
                            <td><?= date('d/m/Y', strtotime($a['data_agendamento'])) ?></td>
                            <td><?= substr($a['hora_inicio'],0,5) ?>‑<?= substr($a['hora_fim'],0,5) ?></td>
                            <td><?= ucfirst(htmlspecialchars($a['tipo_servico'])) ?></td>
                            <td><?= htmlspecialchars($a['localidade']) ?></td>
                            
                            <td>
                                <button type="button" class="btn ver btn-ver-desc" style="padding: 4px 10px; font-size: 0.85rem;" 
                                        data-id="<?= $a['id'] ?>" 
                                        data-desc="<?= htmlspecialchars($a['descricao'] ?? '', ENT_QUOTES) ?>">
                                    Ver
                                </button>
                            </td>

                            <td><?= htmlspecialchars($a['estado']) ?></td>
                            <td>
                                <form method="POST" style="display:inline" class="acao-form">
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <input type="hidden" name="descricao" class="descricao-field" value="">
                                    <?php if ($is_admin): ?>
                                        <?php if ($a['estado'] === 'pendente'): ?>
    <div class="acoes-vertical">
        <button type="button" value="confirmado" class="btn ver modal-trigger">✔</button>
        <button type="button" value="cancelado" class="btn ver modal-trigger">✖</button>
    </div>
                                        <?php elseif ($a['estado'] === 'confirmado'): ?>
                                            <button type="button" value="concluido" class="btn ver modal-trigger">✔</button>
                                            <button type="button" value="cancelado" class="btn ver modal-trigger">✖</button>
                                        <?php elseif ($a['estado'] === 'cancelado'): ?>
                                            <button type="button" value="pendente" class="btn ver modal-trigger">↩</button>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($a['estado'] === 'pendente'): ?>
                                            <button type="button" value="cancelado" class="btn ver modal-trigger">✖</button>
                                        <?php elseif ($a['estado'] === 'confirmado'): ?>
                                            ✔
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="<?= $is_admin ? 10 : 8 ?>">Nenhum agendamento encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

   
     </div>
    </div>
</div>

<div id="modal-acao" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <center><h2>Descrição da ação</h2></center><br>
        <textarea id="modal-text" rows="4" placeholder="Escreve aqui o motivo ou detalhes da atualização..."></textarea>
        <div class="modal-buttons">
            <button id="modal-cancel" class="btn voltar">Cancelar</button>
            <button id="modal-ok" class="btn voltar">OK</button>
        </div>
    </div>
</div>

<div id="modal-ver-descricao" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <center><h3>Descrição</h3></center><br>
        <form method="POST">
            <input type="hidden" name="action" value="descricao">
            <input type="hidden" name="id" id="edit-desc-id" value="">
            <textarea name="descricao" id="edit-desc-text" rows="6" placeholder="Escreve aqui a descrição..."></textarea>
            <div class="modal-buttons">
                <button type="button" id="fechar-desc" class="btn voltar">Cancelar</button>
                <button type="submit" class="btn voltar">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
// ---------- LÓGICA DO MODAL DE AÇÕES (Estados) ----------
let currentForm = null;
let currentAction = null;

document.addEventListener('DOMContentLoaded', function() {
    // Abrir Modal de Ação
    document.querySelectorAll('.modal-trigger').forEach(function(btn) {
        btn.addEventListener('click', function(evt) {
            evt.preventDefault();
            currentForm = btn.closest('form');
            currentAction = btn.value; 
            
            document.getElementById('modal-text').value = '';
            document.getElementById('modal-acao').style.display = 'flex';
        });
    });

    // Fechar Modal de Ação
    document.getElementById('modal-cancel').addEventListener('click', function(evt) {
        evt.preventDefault();
        document.getElementById('modal-acao').style.display = 'none';
        currentForm = null;
        currentAction = null;
    });

    // Confirmar Modal de Ação
    document.getElementById('modal-ok').addEventListener('click', function(evt) {
        evt.preventDefault();
        if (currentForm && currentAction) {
            var desc = document.getElementById('modal-text').value.trim();
            currentForm.querySelector('.descricao-field').value = desc;
            
            let actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = currentAction;
            currentForm.appendChild(actionInput);
            
            currentForm.submit(); 
        }
    });

    // ---------- LÓGICA DO MODAL DE VER/EDITAR DESCRIÇÃO ----------
    // Abrir Modal de Descrição
    document.querySelectorAll('.btn-ver-desc').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = btn.getAttribute('data-id');
            var desc = btn.getAttribute('data-desc');
            
            document.getElementById('edit-desc-id').value = id;
            document.getElementById('edit-desc-text').value = desc;
            
            document.getElementById('modal-ver-descricao').style.display = 'flex';
        });
    });

    // Fechar Modal de Descrição
    document.getElementById('fechar-desc').addEventListener('click', function(evt) {
        evt.preventDefault();
        document.getElementById('modal-ver-descricao').style.display = 'none';
    });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.3.6/js/dataTables.js"></script>
<script src="scriptadmin.js"></script>

<script>
$(document).ready(function(){
    $('.btn-remover').click(function(){
        const botao = $(this);
        const id = botao.data('id');

        if(confirm('Tem a certeza que quer apagar este item do menu?')) {
            $.post('../admin_gestao/remover_menu.php', { id: id }, function(resposta){
                if(resposta.trim() === 'ok'){
                    botao.closest('tr').remove();
                } else {
                    alert('Erro ao apagar o item do menu.');
                }
            });
        }
    });
});
</script>

</body>
</html>