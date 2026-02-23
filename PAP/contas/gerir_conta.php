<?php
session_start();
$_SESSION['voltar_inteligente'] = 'conta.php';
include_once __DIR__ . '/../botao_voltar.php';
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../cabecindex.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer/Exception.php';
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

$id = $_SESSION['user_id'];
$erro = '';
$sucesso = '';


$stmt = $conn->prepare("
    SELECT nome, email, numtel, caminho_arquivo 
    FROM utilizadores 
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$utilizador = $result->fetch_assoc();
$stmt->close();

if (!$utilizador) {
    $erro = 'Utilizador não encontrado!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $numtel = trim($_POST['numtel']);
    $senhaNova = trim($_POST['password']);

    if (empty($nome) || empty($email) || empty($numtel)) {
        $erro = 'Todos os campos exceto a password são obrigatórios!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido!';
    } elseif (!preg_match('/^\d{9}$/', $numtel)) {
        $erro = 'Telefone inválido (9 dígitos)!';
    } else {

        $caminho_arquivo = $utilizador['caminho_arquivo'];

        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === 0) {

            $pasta = '../imagens/'; 
            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $permitidas)) {

                $novo_nome = uniqid('perfil_') . '.' . $ext;
                $destino = $pasta . $novo_nome;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {

                    $caminho_arquivo = './imagens/' . $novo_nome;
                }

            } else {
                $erro = 'Formato de imagem inválido!';
            }
        }

        if (!$erro) {

            if ($senhaNova !== '') {
                $hash = password_hash($senhaNova, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    UPDATE utilizadores
                    SET nome = ?, email = ?, numtel = ?, password = ?, caminho_arquivo = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("sssssi", $nome, $email, $numtel, $hash, $caminho_arquivo, $id);
                $senhaEnviada = $senhaNova;
            } else {
                $stmt = $conn->prepare("
                    UPDATE utilizadores
                    SET nome = ?, email = ?, numtel = ?, caminho_arquivo = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("ssssi", $nome, $email, $numtel, $caminho_arquivo, $id);
                $senhaEnviada = '';
            }

            if ($stmt->execute()) {
                $sucesso = 'Conta atualizada com sucesso!';
                $utilizador['nome'] = $nome;
                $utilizador['email'] = $email;
                $utilizador['numtel'] = $numtel;
                $utilizador['caminho_arquivo'] = $caminho_arquivo;
                $stmt->close();

                // EMAIL
                $mail = new PHPMailer(true);
                try {
                    $mail->CharSet = 'UTF-8';
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'pcmastergeral@gmail.com';
                    $mail->Password = 'mjsv oxar shbz dfzp';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('pcmastergeral@gmail.com', 'PcMaster');
                    $mail->addAddress($email, $nome);
                    $mail->isHTML(true);
                    $mail->Subject = 'Credenciais atualizadas';

                    $body = "<h3>Olá, $nome!</h3>
                        <p>A sua conta foi atualizada com sucesso.</p>
                        <ul>
                            <li><strong>Email:</strong> $email</li>";
                    if ($senhaEnviada !== '') {
                        $body .= "<li><strong>Nova Password:</strong> $senhaEnviada</li>";
                    }
                    $body .= "</ul><p>PcMaster</p>";

                    $mail->Body = $body;
                    $mail->send();
                    $redir = true;
                } catch (Exception $e) {}
            } else {
                $erro = 'Erro ao atualizar dados!';
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="../imagens/icon.png">
    <title>Editar Conta</title>
    <link rel="stylesheet" href="../css/conta.css">
</head>
<body>
   <a href="javascript:history.back()" class="botao-voltar voltar-fixo">
    ← Voltar
</a>
<div class="bg">
    <div class="overlay"></div>

    <div class="content">
        

        <form method="POST" enctype="multipart/form-data" class="form-conta">
<?php if ($erro): ?>
    <p class="error-message"><?= htmlspecialchars($erro) ?></p>
<?php elseif ($sucesso): ?>
    <p class="success-message"><?= htmlspecialchars($sucesso) ?></p>
                <?php if (!empty($redir)): ?>
        <script>
            setTimeout(function () {
                window.location.href = 'conta.php';
            }, 1000);
        </script>
    <?php endif; ?>
<?php endif; ?>

<center>
    <h2 align="center">Editar Conta</h2>
    <label>Imagem atual:</label><br> <br>

    <img src="../<?= htmlspecialchars($utilizador['caminho_arquivo']) ?>" alt="Foto de perfil" class="foto-perfil">
</center>

            <label>Alterar foto de perfil</label>
            <input type="file" name="foto" accept="image/*">
            <br>
            <label>Nome</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($utilizador['nome']) ?>" required>
            <br>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($utilizador['email']) ?>" required>
            <br>

            <label>Telefone</label>
            <input type="text" name="numtel" value="<?= htmlspecialchars($utilizador['numtel']) ?>" pattern="\d{9}" required>
            <br>

            <label>Nova password (opcional)</label>
            <input type="password" name="password" minlength="6">

<center>
    <br>
    <button type="submit" class="botao">Guardar alterações</button><br>
</center>

        </form>
    </div>
</div>

</body>
</html>
