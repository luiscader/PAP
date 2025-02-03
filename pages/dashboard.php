<?php
session_start();
include 'C:\wamp64\www\PAP\includes\config.php';// Certifique-se de que o caminho está correto

// Verifica se o utilizador está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Obtém informações do utilizador da sessão
$id = $_SESSION['id'];
$tipo = $_SESSION['tipo'];

// Redireciona para o dashboard apropriado
if ($tipo === 'proprietario') {
    header("Location: dashboard_restaurante.php");
    exit();
} elseif ($tipo === 'fornecedor') {
    header("Location: dashboard_fornecedor.php");
    exit();
} else {
    echo "Tipo de usuário não reconhecido.";
    exit();
}

// Prepara a consulta SQL para obter o nome do utilizador
$sql = "SELECT nome FROM Utilizador WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($nome);

if ($stmt->num_rows > 0) {
    $stmt->fetch();
} else {
    $nome = "Usuário"; // Nome padrão caso não seja encontrado
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle</title>
    <link rel="stylesheet" href="../assets/style.css"> <!-- Atualize o caminho do CSS se necessário -->
</head>
<body>
    <div class="dashboard-container">
        <h1>Bem-vindo, <?php echo htmlspecialchars($nome); ?>!</h1>
        
        <!-- Conteúdo do painel -->
        <p>Este é o painel de controle.</p>
        
        <!-- Exemplo de links de navegação para diferentes funcionalidades -->
        <ul>
            <li><a href="perfil.php">Meu Perfil</a></li>
            <li><a href="configuracoes.php">Configurações</a></li>
        </ul>
        
        <a href="geral/logout.php">Terminar Sessão</a>
    </div>
</body>
</html>
