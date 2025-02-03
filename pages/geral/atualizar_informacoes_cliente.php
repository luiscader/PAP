<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  

// Verifica se o utilizador está logado
if (!isset($_SESSION['id'])) {
    die('Por favor, faça login para acessar esta página.');
}

$id_utilizador = $_SESSION['id']; 

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Processa a atualização do utilizador
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_nome = $_POST['nome'];
    $novo_email = $_POST['email'];
    $novo_telefone = $_POST['telefone'];
    $nova_data_nascimento = $_POST['data_nascimento'];
    $novo_nif = $_POST['nif'];
    $novo_pais = $_POST['pais'];
    $novo_distrito = $_POST['distrito'];
    $nova_morada = $_POST['morada'];
    $novo_codigo_postal = $_POST['codigo_postal'];

    // Validação simples (você pode adicionar mais validações aqui)
    if (filter_var($novo_email, FILTER_VALIDATE_EMAIL) && preg_match('/^[0-9]{9}$/', $novo_telefone)) {
        // Atualiza os dados do utilizador no banco de dados
        $sql_atualiza = "UPDATE utilizador 
                         SET nome = ?, email = ?, telefone = ?, data_nascimento = ?, nif = ?, pais = ?, distrito = ?, morada = ?, codigo_postal = ?
                         WHERE id = ?";
        if ($stmt_atualiza = $conn->prepare($sql_atualiza)) {
            $stmt_atualiza->bind_param("sssssssssi", $novo_nome, $novo_email, $novo_telefone, $nova_data_nascimento, $novo_nif, $novo_pais, $novo_distrito, $nova_morada, $novo_codigo_postal, $id_utilizador);
            if ($stmt_atualiza->execute()) {
                echo "Informações atualizadas com sucesso!";
            } else {
                echo "Erro ao atualizar as informações: " . $conn->error;
            }
            $stmt_atualiza->close();
        } else {
            echo "Erro ao preparar a consulta: " . $conn->error;
        }
    } else {
        echo "Por favor, insira um email válido e um telefone com 9 dígitos.";
    }
}

// Recupera as informações atuais do utilizador
$sql = "SELECT nome, email, telefone, data_nascimento, nif, pais, distrito, morada, codigo_postal FROM utilizador WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utilizador);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $utilizador = $result->fetch_assoc();
} else {
    die("Erro: utilizador não encontrado.");
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Atualizar Informações</title>
    <style></style>
</head>
<body>
    <h1>Atualizar Informações</h1>

    <form method="post" action="">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($utilizador['nome']); ?>" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($utilizador['email']); ?>" required><br><br>

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($utilizador['telefone']); ?>" required><br><br>

        <label for="data_nascimento">Data de Nascimento:</label>
        <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($utilizador['data_nascimento']); ?>" required><br><br>

        <label for="nif">NIF:</label>
        <input type="text" id="nif" name="nif" value="<?php echo htmlspecialchars($utilizador['nif']); ?>" required><br><br>

        <label for="pais">País:</label>
        <input type="text" id="pais" name="pais" value="<?php echo htmlspecialchars($utilizador['pais']); ?>" required><br><br>

        <label for="distrito">Distrito:</label>
        <input type="text" id="distrito" name="distrito" value="<?php echo htmlspecialchars($utilizador['distrito']); ?>" required><br><br>

        <label for="morada">Morada:</label>
        <input type="text" id="morada" name="morada" value="<?php echo htmlspecialchars($utilizador['morada']); ?>" required><br><br>

        <label for="codigo_postal">Código Postal:</label>
        <input type="text" id="codigo_postal" name="codigo_postal" value="<?php echo htmlspecialchars($utilizador['codigo_postal']); ?>" required><br><br>

        <input type="submit" value="Atualizar Informações">
    </form>
</body>
</html>
