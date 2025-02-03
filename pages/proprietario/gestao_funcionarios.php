<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  // Certifique-se de que o caminho está correto

// Verifica se o proprietário está logado e o ID do restaurante está definido na sessão
if (!isset($_SESSION['id']) || !isset($_SESSION['id_restaurante'])) {
    die('Por favor, faça login como proprietário e selecione um restaurante.');
}

$id_restaurante = $_SESSION['id_restaurante']; 

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verifica se o ID do funcionário foi passado via POST para remoção
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_funcionario']) && isset($_POST['action']) && $_POST['action'] == 'remove') {
    $id_funcionario = $_POST['id_funcionario'];

    // Verifica se o funcionário pertence ao restaurante do proprietário
    $sql_verifica_funcionario = "SELECT id FROM empregado WHERE id = ? AND id_restaurante = ?";
    if ($stmt_verifica_funcionario = $conn->prepare($sql_verifica_funcionario)) {
        $stmt_verifica_funcionario->bind_param("ii", $id_funcionario, $id_restaurante);
        $stmt_verifica_funcionario->execute();
        $stmt_verifica_funcionario->store_result();

        if ($stmt_verifica_funcionario->num_rows == 0) {
            die("O funcionário não pertence a este restaurante.");
        }
        $stmt_verifica_funcionario->close();
    } else {
        die("Erro ao preparar a consulta de verificação do funcionário.");
    }

    // Remove o funcionário da tabela de empregados
    $sql_remover = "DELETE FROM empregado WHERE id = ? AND id_restaurante = ?";
    if ($stmt_remover = $conn->prepare($sql_remover)) {
        $stmt_remover->bind_param("ii", $id_funcionario, $id_restaurante);
        if ($stmt_remover->execute()) {
            echo "<p style='color: green;'>Funcionário removido com sucesso!</p>";
        } else {
            echo "<p style='color: red;'>Erro ao remover o funcionário: " . $conn->error . "</p>";
        }
        $stmt_remover->close();
    } else {
        echo "<p style='color: red;'>Erro ao preparar a consulta de remoção do funcionário.</p>";
    }
}

// Recupera a lista de funcionários associados ao restaurante
$sql = "SELECT e.id, u.nome, u.email, u.telefone, e.cargo 
        FROM empregado e
        JOIN utilizador u ON e.id = u.id 
        WHERE e.id_restaurante = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_restaurante);    
$stmt->execute();
$result = $stmt->get_result();

// Verifica se há funcionários cadastrados
if ($result->num_rows == 0) {
    echo "<p>Não há funcionários associados a este restaurante.</p>";
}

// Lista fixa de cargos
$cargos_disponiveis = ['empregado', 'cozinheiro', 'gerente'];

// Fecha a consulta
$stmt->close();

// Conta o número de funcionários associados ao restaurante
$sql_count = "SELECT COUNT(*) as total_funcionarios FROM empregado WHERE id_restaurante = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $id_restaurante);
$stmt_count->execute();
$stmt_count->bind_result($total_funcionarios);
$stmt_count->fetch();
$stmt_count->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Funcionários</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #343a40;
            text-align: center;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e2e6ea;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .actions form {
            display: inline;
        }

        input[type="submit"], select {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .btn-info {
            background-color: #007bff;
        }

        .btn-info:hover {
            background-color: #0056b3;
        }

        .btn-remove {
            background-color: #dc3545;
        }

        .btn-remove:hover {
            background-color: #c82333;
        }

        .button-container {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestão de Funcionários</h1>

        <!-- Exibe o número total de funcionários -->
        <p>Número total de funcionários associados ao restaurante: <strong><?php echo $total_funcionarios; ?></strong></p>

        <!-- Botão para contratar um novo funcionário -->
        <div class="button-container">
            <form action="contratar_empregados.php" method="get">
                <input type="submit" value="Contratar Funcionário">
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Cargo Atual</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['nome']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['telefone']); ?></td>
                            <td><?php echo htmlspecialchars($row['cargo']); ?></td>
                            <td>
                                <div class="actions">

                                    <!-- Formulário para remover o funcionário -->
                                    <form method="post" action="">
                                        <input type="hidden" name="id_funcionario" value="<?php echo htmlspecialchars($row['id']); ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="submit" class="btn-remove" value="Remover" onclick="return confirm('Tem certeza de que deseja remover este funcionário?');">
                                    </form>

                                    <!-- Formulário para atualizar o cargo -->
                                    <form method="post" action="atualizar_cargo.php">
                                        <input type="hidden" name="id_funcionario" value="<?php echo htmlspecialchars($row['id']); ?>">
                                        
                                        <!-- Seleciona o novo cargo -->
                                        <select name="novo_cargo">
                                            <?php foreach ($cargos_disponiveis as $cargo): ?>
                                                <option value="<?php echo htmlspecialchars($cargo); ?>">
                                                    <?php echo htmlspecialchars($cargo); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        
                                        <input type="submit" value="Atualizar Cargo">
                                    </form>
                                        <!-- Botão + Info -->
                                        <form action="detalhes_empregado.php" method="get">
                                        <input type="hidden" name="id_funcionario" value="<?php echo htmlspecialchars($row['id']); ?>">
                                        <input type="submit" class="btn-info" value="+ Info">
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Não há funcionários associados a este restaurante.</p>
        <?php endif; ?>
    </div>

    <?php 
    $conn->close(); 
    ?>
</body>
</html>
