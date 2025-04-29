<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start(); 
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id_cliente = $_SESSION['id'];

$sql = "SELECT id, nome, email, senha, tipo FROM Utilizador WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $stmt->store_result();
    
    $stmt->bind_result($id, $nome, $email, $senha, $tipo);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
    } else {
        echo "Utilizador não encontrado.";
        exit();
    }
    $stmt->close();
} else {
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
            color: #FF5722;
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
            color: #FF5722;
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: border-bottom 0.3s;
        }
        a:hover {
            border-bottom: 1px solid #FF5722;
        }
        .button {
            display: inline-block;
            padding: 10px 15px;
            color: white;
            background-color: #FF5722;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #E64A19;
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
        <li>
            <a href="<?php 
                switch($tipo) {
                    case 'proprietario':
                        echo '../dashboard/proprietario/index.php';
                        break;
                    case 'fornecedor':
                        echo '../dashboard/fornecedor/index.php';
                        break;
                    case 'admin':
                        echo '../dashboard/admin/index.php';
                        break;
                    default:
                        echo '#';
                }
            ?>">Dashboard</a>
        </li>
        <li><a href="atualizar_informacoes_cliente.php">Atualizar Informações</a></li>
        <li><a href="logout.php">Terminar Sessão</a></li>
    </ul>
</div>

</body>
</html>