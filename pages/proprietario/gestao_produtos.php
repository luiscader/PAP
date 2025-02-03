<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  // Certifique-se de que o caminho está correto

// Verifica se o utilizador está logado e se é proprietário
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'proprietario') {
    die('Acesso restrito. Apenas proprietários podem acessar esta página.');
}

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Processa a exclusão de um produto
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM produto WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "Produto excluído com sucesso!";
        } else {
            echo "Erro ao excluir produto: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Erro ao preparar a consulta: " . $conn->error;
    }
}

// Consulta para obter produtos
$sql = "SELECT p.id, p.nome, p.descricao, p.quantidade, p.unidade_medida, p.data_criacao, p.data_atualizacao, 
        c.nome AS categoria_nome, f.nome_representante AS fornecedor_nome 
        FROM produto p 
        LEFT JOIN categoria c ON p.id_categoria = c.id 
        LEFT JOIN fornecedor f ON p.id_fornecedor = f.id 
        WHERE p.id_restaurante = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['id_restaurante']); // Assumindo que o id_restaurante está na sessão
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Produtos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f4f4f4;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .actions a {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-align: center;
        }

        .actions a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Gerir Produtos</h1>
    <a href="criar_produto.php" class="actions">Criar Novo Produto</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Quantidade</th>
                <th>Unidade de Medida</th>
                <th>Categoria</th>
                <th>Fornecedor</th>
                <th>Data Criação</th>
                <th>Data Atualização</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['nome']); ?></td>
                    <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                    <td><?php echo htmlspecialchars($row['quantidade']); ?></td>
                    <td><?php echo htmlspecialchars($row['unidade_medida']); ?></td>
                    <td><?php echo htmlspecialchars($row['categoria_nome']); ?></td>
                    <td><?php echo htmlspecialchars($row['fornecedor_nome']); ?></td>
                    <td><?php echo htmlspecialchars($row['data_criacao']); ?></td>
                    <td><?php echo htmlspecialchars($row['data_atualizacao']); ?></td>
                    <td class="actions">
                        <a href="editar_produto.php?id=<?php echo htmlspecialchars($row['id']); ?>">Editar</a>
                        <a href="?action=delete&id=<?php echo htmlspecialchars($row['id']); ?>" onclick="return confirm('Tem certeza que deseja excluir este produto?');">Excluir</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>
