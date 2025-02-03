<?php
include 'C:/wamp64/www/PAP/includes/config.php';  // Inclui a configuração do banco de dados

session_start(); // Inicia a sessão

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Redireciona para o login se não estiver autenticado
    exit();
}

$id_cliente = $_SESSION['id']; // Obtém o ID do cliente da sessão

// Prepara a consulta SQL para obter as informações do cliente
$sql = "SELECT id, nome, email, senha, tipo FROM Utilizador WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $stmt->store_result();
    
    // Certifique-se de que o número de variáveis em bind_result corresponde ao número de colunas na consulta
    $stmt->bind_result($id, $nome, $email, $senha, $tipo);

    // Exibir perfil do cliente
    if ($stmt->num_rows > 0) {
        $stmt->fetch();
    } else {
        echo "Cliente não encontrado.";
        exit();
    }
    $stmt->close();
} else {
    // Lidar com erro de preparação de consulta SQL
    echo "Erro ao preparar a consulta.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Cliente</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #F0F4FF;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #FF5722; /* Laranja */
        }
        .info {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        a {
            display: inline-block;
            margin: 10px 0;
            color: #FF5722; /* Laranja */
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: border-bottom 0.3s;
        }
        a:hover {
            border-bottom: 1px solid #FF5722; /* Laranja */
        }
        .button {
            display: inline-block;
            padding: 10px 15px;
            color: white;
            background-color: #FF5722; /* Laranja */
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #E64A19; /* Tom mais escuro de laranja */
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Perfil de <?php echo htmlspecialchars($nome); ?></h1>
    <div class="info">
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>Tipo:</strong> <?php echo htmlspecialchars($tipo); ?></p>
    </div>

    <?php if ($tipo == 'cliente'): ?>
        <a class="button" href="registrar_restaurante.php">Registrar Restaurante</a><br>
        <a class="button" href="registrar_fornecedor.php">Registrar Fornecedor</a><br>
    <?php endif; ?>
    
    <hr>
    <h2>Opções</h2>
    <ul>
        <li><a href="../proprietario/dist/index.php">Dashborad em Desemvolvimento</a></li>
        <li><a href="..\proprietario\dashboard_restaurante.php">Dashboard Restaurante</a></li>
        <li><a href=" atualizar_informacoes_cliente.php">Atualizar Informações</a></li>
        <li><a href="logout.php">Terminar Sessão</a></li>
    </ul>
</div>

</body>
</html>
