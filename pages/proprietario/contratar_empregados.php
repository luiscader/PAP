<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  

// Verifica se o proprietário está logado e o ID do restaurante está definido na sessão
if (!isset($_SESSION['id']) || !isset($_SESSION['id_restaurante']) || $_SESSION['tipo'] !== 'proprietario') {
    die('Por favor, faça login como proprietário e selecione um restaurante.');
}

$id_restaurante = $_SESSION['id_restaurante']; 

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Processa a contratação de um funcionarios
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contratar'])) {
    $id = $_POST['id'];

    // Verifica se o utilizador já está na tabela de funcionarioss
    $sql_verifica_funcionarios = "SELECT id FROM funcionarios WHERE id_restaurante = ? AND id = ?";
    if ($stmt_verifica_funcionarios = $conn->prepare($sql_verifica_funcionarios)) {
        $stmt_verifica_funcionarios->bind_param("ii", $id_restaurante, $id);
        $stmt_verifica_funcionarios->execute();
        $stmt_verifica_funcionarios->store_result();

        if ($stmt_verifica_funcionarios->num_rows > 0) {
            echo "Este cliente já está contratado.";
        } else {
            // Adiciona o utilizador à tabela de funcionarioss com o cargo 'funcionarios'
            $sql_adiciona = "INSERT INTO funcionarios (nome, cargo, id_restaurante, id)
                             SELECT nome, 'funcionarios', ?, id 
                             FROM utilizador 
                             WHERE id = ?";
            if ($stmt_adiciona = $conn->prepare($sql_adiciona)) {
                $stmt_adiciona->bind_param("ii", $id_restaurante, $id);
                if ($stmt_adiciona->execute()) {
                    echo "Usuário contratado com sucesso!";
                } else {
                    echo "Erro ao contratar o usuário: " . $conn->error;
                }
                $stmt_adiciona->close();
            } else {
                echo "Erro ao preparar a consulta de contratação: " . $conn->error;
            }
        }
        $stmt_verifica_funcionarios->close();
    } else {
        echo "Erro ao preparar a consulta de verificação do funcionarios: " . $conn->error;
    }
}

// Conecta ao banco de dados para recuperar a lista de utilizadores
// A consulta agora exclui clientes que já estão associados a qualquer restaurante
$sql = "SELECT u.id, u.nome, u.email, u.telefone 
        FROM utilizador u 
        LEFT JOIN funcionarios e ON u.id = e.id 
        WHERE u.tipo = 'cliente' 
        AND e.id_restaurante IS NULL"; // Garante que só clientes não contratados apareçam

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Contratar funcionarioss</title>
</head>
<body>
    <h1>Contratar funcionarioss</h1>
    
    <?php if ($result->num_rows > 0): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Ação</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['nome']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['telefone']); ?></td>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <input type="submit" name="contratar" value="Contratar">
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Não há clientes disponíveis para contratação.</p>
    <?php endif; ?>

    <?php 
    $stmt->close();
    $conn->close(); 
    ?>
</body>
</html>
