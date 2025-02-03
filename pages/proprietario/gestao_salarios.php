<?php
include 'C:/wamp64/www/PAP/includes/config.php';

// Atualiza o salário e o cargo se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_funcionario'])) {
    $id_funcionario = $_POST['id_funcionario'];
    $novo_salario = $_POST['salario'];
    $novo_cargo = $_POST['cargo'];

    $sql_update = "UPDATE funcionarios SET salario = ?, cargo = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("dsi", $novo_salario, $novo_cargo, $id_funcionario);
    $stmt->execute();
    $stmt->close();
}

// Consulta os funcionários
$sql = "SELECT * FROM funcionarios ORDER BY nome";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Salários</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn-editar {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-editar:hover {
            background-color: #0056b3;
        }
        select, input {
            padding: 5px;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Gestão de Salários</h1>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Cargo</th>
                <th>Salário (€)</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="id_funcionario" value="<?php echo $row['id']; ?>">
                                <!-- Select para escolher o cargo -->
                                <select name="cargo">
                                    <option value="Chefe" <?php echo ($row['cargo'] == 'Chefe') ? 'selected' : ''; ?>>Chefe</option>
                                    <option value="Gerente" <?php echo ($row['cargo'] == 'Gerente') ? 'selected' : ''; ?>>Gerente</option>
                                    <option value="Cozinheiro" <?php echo ($row['cargo'] == 'Cozinheiro') ? 'selected' : ''; ?>>Cozinheiro</option>
                                    <option value="Empregado de Mesa" <?php echo ($row['cargo'] == 'Empregado de Mesa') ? 'selected' : ''; ?>>Empregado de Mesa</option>
                                </select>
                        </td>
                        <td>€<input type="number" name="salario" value="<?php echo $row['salario']; ?>" step="0.01" required></td>
                        <td>
                            <button type="submit" class="btn-editar">Atualizar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Nenhum funcionário encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="footer">
    <p>&copy; 2024 Seu Nome ou Sua Empresa. Todos os direitos reservados.</p>
</div>

</body>
</html>

<?php
$conn->close();
?>
