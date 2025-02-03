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

// Verifica se o formulário foi enviado e se os parâmetros necessários estão presentes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_funcionario']) && isset($_POST['novo_cargo'])) {
    $id_funcionario = $_POST['id_funcionario'];
    $novo_cargo = $_POST['novo_cargo'];

    // Atualiza o cargo do funcionário na base de dados
    $sql_atualiza_cargo = "UPDATE empregado SET cargo = ? WHERE id = ? AND id_restaurante = ?";
    if ($stmt_atualiza_cargo = $conn->prepare($sql_atualiza_cargo)) {
        $stmt_atualiza_cargo->bind_param("sii", $novo_cargo, $id_funcionario, $id_restaurante);
        if ($stmt_atualiza_cargo->execute()) {
            echo "Cargo atualizado com sucesso!";
        } else {
            echo "Erro ao atualizar o cargo: " . $conn->error;
        }
        $stmt_atualiza_cargo->close();
    } else {
        echo "Erro ao preparar a consulta de atualização do cargo.";
    }
} else {
    echo "Dados inválidos ou não fornecidos.";
}

$conn->close();

// Redireciona de volta para a página de gestão de funcionários
header("Location: gestao_funcionarios.php");
exit();
?>
