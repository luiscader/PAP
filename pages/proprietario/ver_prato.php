<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  // Certifique-se de que o caminho está correto

// Verifica se o usuário está logado e se é um proprietário ou gerente
if (!isset($_SESSION['id']) || ($_SESSION['tipo'] !== 'proprietario' && $_SESSION['tipo'] !== 'gerente')) {
    die('Acesso restrito. Apenas proprietários e gerentes podem acessar esta página.');
}

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Obtém o ID do prato
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID do prato inválido.');
}
$id_prato = $_GET['id'];

// Recupera os dados do prato
$sql_prato = "SELECT id, nome, descricao, preco, data_criacao, data_atualizacao FROM pratos WHERE id = ?";
$stmt_prato = $conn->prepare($sql_prato);
$stmt_prato->bind_param("i", $id_prato);
$stmt_prato->execute();
$result_prato = $stmt_prato->get_result();

if ($result_prato->num_rows !== 1) {
    die('Prato não encontrado.');
}

$prato = $result_prato->fetch_assoc();
$stmt_prato->close();

// Recupera os ingredientes do prato
$sql_ingredientes = "
    SELECT p.nome AS produto_nome, p.descricao AS produto_descricao, ip.quantidade_necessaria, ip.unidade_medida
    FROM ingrediente_prato ip
    JOIN produto p ON ip.id_produto = p.id
    WHERE ip.id_prato = ?
";
$stmt_ingredientes = $conn->prepare($sql_ingredientes);
$stmt_ingredientes->bind_param("i", $id_prato);
$stmt_ingredientes->execute();
$result_ingredientes = $stmt_ingredientes->get_result();
$ingredientes = [];
while ($row = $result_ingredientes->fetch_assoc()) {
    $ingredientes[] = $row;
}
$stmt_ingredientes->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Prato</title>
    <style>
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }

        h1 {
            text-align: center;
        }

        .prato-info, .ingredientes-lista {
            margin-bottom: 20px;
        }

        .ingredientes-lista table {
            width: 100%;
            border-collapse: collapse;
        }

        .ingredientes-lista th, .ingredientes-lista td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        .ingredientes-lista th {
            background-color: #f2f2f2;
        }

        .voltar {
            display: block;
            margin-top: 20px;
            text-align: center;
        }

        .voltar a {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .voltar a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Visualizar Prato</h1>

        <div class="prato-info">
            <h2><?php echo htmlspecialchars($prato['nome']); ?></h2>
            <p><strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($prato['descricao'])); ?></p>
            <p><strong>Preço:</strong> € <?php echo number_format($prato['preco'], 2, ',', '.'); ?></p>
            <p><strong>Data de Criação:</strong> <?php echo date('d/m/Y H:i:s', strtotime($prato['data_criacao'])); ?></p>
            <p><strong>Data de Atualização:</strong> <?php echo date('d/m/Y H:i:s', strtotime($prato['data_atualizacao'])); ?></p>
        </div>

        <div class="ingredientes-lista">
            <h3>Ingredientes</h3>
            <?php if (!empty($ingredientes)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Quantidade</th>
                            <th>Unidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingredientes as $ingrediente): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ingrediente['produto_nome']); ?></td>
                                <td><?php echo htmlspecialchars($ingrediente['produto_descricao']); ?></td>
                                <td><?php echo number_format($ingrediente['quantidade_necessaria'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($ingrediente['unidade_medida']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Sem ingredientes.</p>
            <?php endif; ?>
        </div>

        <div class="voltar">
            <a href="gestao_pratos.php">Voltar para a Lista de Pratos</a>
        </div>
    </div>
</body>
</html>
