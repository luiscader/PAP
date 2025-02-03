<?php
session_start();
include 'C:\wamp64\www\PAP\includes\config.php'; // Certifique-se de que o caminho está correto

// Verifica se o utilizador está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Obtém informações do utilizador da sessão
$id = $_SESSION['id'];
$tipo = $_SESSION['tipo'];

// Verifica se o tipo de utilizador é 'proprietario'
if ($tipo !== 'proprietario') {
    echo "Acesso restrito. Apenas proprietários podem acessar esta página.";
    exit();
}

// Prepara a consulta SQL para obter o nome do utilizador
$sql = "SELECT nome FROM utilizador WHERE id = ?";
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

// Prepara a consulta SQL para obter todos os clientes
$sql_clientes = "SELECT nome, email FROM utilizador WHERE tipo = 'cliente'";
$clientes = [];
if ($stmt_clientes = $conn->prepare($sql_clientes)) {
    $stmt_clientes->execute();
    $stmt_clientes->store_result();
    $stmt_clientes->bind_result($cliente_nome, $cliente_email);

    while ($stmt_clientes->fetch()) {
        $clientes[] = ['nome' => htmlspecialchars($cliente_nome), 'email' => htmlspecialchars($cliente_email)];
    }
    $stmt_clientes->close();
} else {
    echo "Erro ao preparar a consulta de clientes.";
}

$conn->close();
?>

<!-- Página principal, por exemplo, home.php -->
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página com Sidebar</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        /* Inclua aqui o CSS necessário para a sidebar, ou use um ficheiro CSS externo */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");
        /* Todo o seu CSS da sidebar ou referência a um ficheiro CSS separado */
    </style>
</head>
<body>
    <?php include 'sidebar_restaurante.php'; ?> <!-- Inclui a sidebar -->
    <main>
        <!-- Conteúdo da página -->
    </main>
</body>

</html>