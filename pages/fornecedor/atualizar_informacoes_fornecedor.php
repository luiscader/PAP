<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';

// Verifica se o fornecedor está logado e o ID do fornecedor está definido na sessão
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'fornecedor' || !isset($_SESSION['id_fornecedor'])) {
    die('Por favor, faça login como fornecedor e selecione um fornecedor.');
}

$id_fornecedor = $_SESSION['id_fornecedor']; 

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Processa a atualização das informações do fornecedor
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_representante = $_POST['nome_representante'];
    $telefone_representante = $_POST['telefone_representante'];
    $email_representante = $_POST['email_representante'];
    $nif_empresa = $_POST['nif_empresa'];
    $morada_sede = $_POST['morada_sede'];
    $codigo_postal = $_POST['codigo_postal'];
    $distrito = $_POST['distrito'];
    $pais = $_POST['pais'];
    $iban = $_POST['iban'];

    // Validação simples para telefone e IBAN
    if (!empty($nome_representante) && !empty($nif_empresa) && !empty($iban)) {
        // Atualiza as informações do fornecedor no banco de dados
        $sql_atualiza = "UPDATE fornecedor SET nome_representante = ?, telefone_representante = ?, email_representante = ?, nif_empresa = ?, morada_sede = ?, codigo_postal = ?, distrito = ?, pais = ?, iban = ? WHERE id = ?";
        
        if ($stmt_atualiza = $conn->prepare($sql_atualiza)) {
            // Corrija a string de definição de tipos para 10 parâmetros: 9 strings e 1 inteiro
            $stmt_atualiza->bind_param("sssssssssi", $nome_representante, $telefone_representante, $email_representante, $nif_empresa, $morada_sede, $codigo_postal, $distrito, $pais, $iban, $id_fornecedor);
            if ($stmt_atualiza->execute()) {
                echo "Informações do fornecedor atualizadas com sucesso!";
            } else {
                echo "Erro ao atualizar as informações: " . $stmt_atualiza->error;
            }
            $stmt_atualiza->close();
        } else {
            echo "Erro ao preparar a consulta: " . $conn->error;
        }
    } else {
        echo "Por favor, preencha todos os campos corretamente.";
    }
}

// Recupera as informações atuais do fornecedor
$sql = "SELECT nome_representante, telefone_representante, email_representante, nif_empresa, morada_sede, codigo_postal, distrito, pais, iban FROM fornecedor WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_fornecedor);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $fornecedor = $result->fetch_assoc();
} else {
    die("Erro: fornecedor não encontrado.");
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Informações do Fornecedor</title>
</head>
<body>
    <h1>Atualizar Informações do Fornecedor</h1>

    <form method="post" action="">
        <label for="nome_representante">Nome do Representante:</label>
        <input type="text" id="nome_representante" name="nome_representante" value="<?php echo htmlspecialchars($fornecedor['nome_representante']); ?>" required><br><br>

        <label for="telefone_representante">Telefone:</label>
        <input type="text" id="telefone_representante" name="telefone_representante" value="<?php echo htmlspecialchars($fornecedor['telefone_representante']); ?>" required><br><br>

        <label for="email_representante">Email:</label>
        <input type="email" id="email_representante" name="email_representante" value="<?php echo htmlspecialchars($fornecedor['email_representante']); ?>" required><br><br>

        <label for="nif_empresa">NIF da Empresa:</label>
        <input type="text" id="nif_empresa" name="nif_empresa" value="<?php echo htmlspecialchars($fornecedor['nif_empresa']); ?>" required><br><br>

        <label for="morada_sede">Morada da Sede:</label>
        <input type="text" id="morada_sede" name="morada_sede" value="<?php echo htmlspecialchars($fornecedor['morada_sede']); ?>" required><br><br>

        <label for="codigo_postal">Código Postal:</label>
        <input type="text" id="codigo_postal" name="codigo_postal" value="<?php echo htmlspecialchars($fornecedor['codigo_postal']); ?>" required><br><br>

        <label for="distrito">Distrito:</label>
        <input type="text" id="distrito" name="distrito" value="<?php echo htmlspecialchars($fornecedor['distrito']); ?>" required><br><br>

        <label for="pais">País:</label>
        <input type="text" id="pais" name="pais" value="<?php echo htmlspecialchars($fornecedor['pais']); ?>" required><br><br>

        <label for="iban">IBAN:</label>
        <input type="text" id="iban" name="iban" value="<?php echo htmlspecialchars($fornecedor['iban']); ?>" required><br><br>

        <input type="submit" value="Atualizar Informações">
    </form>
</body>
</html>
