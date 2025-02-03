<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  

// Verifica se o proprietário está logado e o ID do restaurante está definido na sessão
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'proprietario' || !isset($_SESSION['id_restaurante'])) {
    die('Por favor, faça login como proprietário e selecione um restaurante.');
}

$id_restaurante = $_SESSION['id_restaurante']; 

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Processa a exclusão de imagens
if (isset($_POST['excluir_imagem'])) {
    $imagem_a_excluir = $_POST['imagem_a_excluir'];

    // Remove a imagem do banco de dados
    $sql_delete_imagem = "DELETE FROM imagem_restaurante WHERE caminho_imagem = ? AND id_restaurante = ?";
    if ($stmt_delete_imagem = $conn->prepare($sql_delete_imagem)) {
        $stmt_delete_imagem->bind_param("si", $imagem_a_excluir, $id_restaurante);
        if ($stmt_delete_imagem->execute()) {
            // Remove o arquivo de imagem do servidor
            if (file_exists($imagem_a_excluir)) {
                unlink($imagem_a_excluir);
                echo "Imagem excluída com sucesso!";
            } else {
                echo "Erro: O arquivo de imagem não existe no servidor.";
            }
        } else {
            echo "Erro ao excluir imagem do banco de dados: " . $conn->error;
        }
        $stmt_delete_imagem->close();
    }
}

// Processa a atualização das informações do restaurante e o upload de imagens
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['excluir_imagem'])) {
    $nome_empresa = $_POST['nome_empresa'];
    $nif = $_POST['nif'];
    $designacao_legal = $_POST['designacao_legal'];
    $morada = $_POST['morada'];
    $codigo_postal = $_POST['codigo_postal'];
    $distrito = $_POST['distrito'];
    $pais = $_POST['pais'];
    $telefone = $_POST['telefone'];
    $nome_banco = $_POST['nome_banco'];
    $iban = $_POST['iban'];
    $titular_conta = $_POST['titular_conta'];

    // Validação simples para o telefone (9 dígitos) e IBAN
    if (!empty($nome_empresa) && preg_match('/^[0-9]{9}$/', $telefone) && !empty($iban)) {
        // Atualiza as informações do restaurante no banco de dados
        $sql_atualiza = "UPDATE restaurante SET nome_empresa = ?, nif = ?, designacao_legal = ?, morada = ?, codigo_postal = ?, distrito = ?, pais = ?, telefone = ?, nome_banco = ?, iban = ?, titular_conta = ? WHERE id = ?";
        
        if ($stmt_atualiza = $conn->prepare($sql_atualiza)) {
            $stmt_atualiza->bind_param("sssssssssssi", $nome_empresa, $nif, $designacao_legal, $morada, $codigo_postal, $distrito, $pais, $telefone, $nome_banco, $iban, $titular_conta, $id_restaurante);
            if ($stmt_atualiza->execute()) {
                echo "Informações do restaurante atualizadas com sucesso!";
            } else {
                echo "Erro ao atualizar as informações: " . $conn->error;
            }
            $stmt_atualiza->close();
        } else {
            echo "Erro ao preparar a consulta: " . $conn->error;
        }
    } else {
        echo "Por favor, preencha todos os campos corretamente.";
    }

    // Processa o upload de imagens
    if (isset($_FILES['imagens_restaurante']) && $_FILES['imagens_restaurante']['error'][0] != UPLOAD_ERR_NO_FILE) {
        $errors = [];
        $uploaded_files = [];

        foreach ($_FILES['imagens_restaurante']['name'] as $key => $name) {
            $tmp_name = $_FILES['imagens_restaurante']['tmp_name'][$key];
            $error = $_FILES['imagens_restaurante']['error'][$key];

            if ($error === UPLOAD_ERR_OK) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $new_name = 'img_' . uniqid() . '.' . $ext;
                $upload_dir = '../geral/uploads/';
                $upload_file = $upload_dir . $new_name;

                if (move_uploaded_file($tmp_name, $upload_file)) {
                    // Salva o caminho da imagem no banco de dados
                    $sql_upload = "INSERT INTO imagem_restaurante (id_restaurante, caminho_imagem) VALUES (?, ?)";
                    if ($stmt_upload = $conn->prepare($sql_upload)) {
                        $stmt_upload->bind_param("is", $id_restaurante, $upload_file);
                        $stmt_upload->execute();
                        $stmt_upload->close();
                        $uploaded_files[] = $upload_file;
                    } else {
                        $errors[] = "Erro ao preparar a consulta para upload de imagem: " . $conn->error;
                    }
                } else {
                    $errors[] = "Erro ao mover o arquivo para o diretório de uploads.";
                }
            } else if ($error != UPLOAD_ERR_NO_FILE) {
                $errors[] = "Erro ao enviar o arquivo: " . $error;
            }
        }

        if (!empty($errors)) {
            echo "Erros ao fazer upload das imagens: " . implode(", ", $errors);
        } else {
            echo "Imagens carregadas com sucesso!";
        }
    }
}

// Recupera as informações atuais do restaurante
$sql = "SELECT nome_empresa, nif, designacao_legal, morada, codigo_postal, distrito, pais, telefone, nome_banco, iban, titular_conta FROM restaurante WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_restaurante);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $restaurante = $result->fetch_assoc();
} else {
    die("Erro: restaurante não encontrado.");
}

$stmt->close();

// Recupera as imagens atuais do restaurante
$sql_imagens = "SELECT caminho_imagem FROM imagem_restaurante WHERE id_restaurante = ?";
$stmt_imagens = $conn->prepare($sql_imagens);
$stmt_imagens->bind_param("i", $id_restaurante);
$stmt_imagens->execute();
$result_imagens = $stmt_imagens->get_result();

$imagens = [];
while ($row = $result_imagens->fetch_assoc()) {
    $imagens[] = $row['caminho_imagem'];
}

$stmt_imagens->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Informações do Restaurante</title>
    <style>
        /* Reset básico */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto 40px;
        }

        form label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
            color: #2c3e50;
        }

        form input[type="text"],
        form input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        form input[type="submit"] {
            background-color: #2980b9;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        form input[type="submit"]:hover {
            background-color: #1f6391;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .imagem-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }

        .imagem-container div {
            background-color: #fff;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .imagem-container img {
            width: 150px;
            height: auto;
            border-radius: 5px;
        }

        .imagem-container form {
            margin-top: 10px;
        }

        .imagem-container input[type="submit"] {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .imagem-container input[type="submit"]:hover {
            background-color: #c0392b;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .imagem-container img {
                width: 100px;
            }

            form {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <h1>Atualizar Informações do Restaurante</h1>

    <form method="post" action="" enctype="multipart/form-data">
        <label for="nome_empresa">Nome da Empresa:</label>
        <input type="text" id="nome_empresa" name="nome_empresa" value="<?php echo htmlspecialchars($restaurante['nome_empresa']); ?>" required>

        <label for="nif">NIF:</label>
        <input type="text" id="nif" name="nif" value="<?php echo htmlspecialchars($restaurante['nif']); ?>" required>

        <label for="designacao_legal">Designação Legal:</label>
        <input type="text" id="designacao_legal" name="designacao_legal" value="<?php echo htmlspecialchars($restaurante['designacao_legal']); ?>" required>

        <label for="morada">Morada:</label>
        <input type="text" id="morada" name="morada" value="<?php echo htmlspecialchars($restaurante['morada']); ?>" required>

        <label for="codigo_postal">Código Postal:</label>
        <input type="text" id="codigo_postal" name="codigo_postal" value="<?php echo htmlspecialchars($restaurante['codigo_postal']); ?>" required>

        <label for="distrito">Distrito:</label>
        <input type="text" id="distrito" name="distrito" value="<?php echo htmlspecialchars($restaurante['distrito']); ?>" required>

        <label for="pais">País:</label>
        <input type="text" id="pais" name="pais" value="<?php echo htmlspecialchars($restaurante['pais']); ?>" required>

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($restaurante['telefone']); ?>" required>

        <label for="nome_banco">Nome do Banco:</label>
        <input type="text" id="nome_banco" name="nome_banco" value="<?php echo htmlspecialchars($restaurante['nome_banco']); ?>" required>

        <label for="iban">IBAN:</label>
        <input type="text" id="iban" name="iban" value="<?php echo htmlspecialchars($restaurante['iban']); ?>" required>

        <label for="titular_conta">Titular da Conta:</label>
        <input type="text" id="titular_conta" name="titular_conta" value="<?php echo htmlspecialchars($restaurante['titular_conta']); ?>" required>

        <label for="imagens_restaurante">Imagens do Restaurante:</label>
        <input type="file" id="imagens_restaurante" name="imagens_restaurante[]" multiple>

        <input type="submit" value="Atualizar Informações">
    </form>

    <h2>Imagens Atuais</h2>
    <div class="imagem-container">
        <?php if (!empty($imagens)): ?>
            <?php foreach ($imagens as $imagem): ?>
                <div>
                    <img src="<?php echo htmlspecialchars($imagem); ?>" alt="Imagem do Restaurante">
                    <form method="post" action="">
                        <input type="hidden" name="imagem_a_excluir" value="<?php echo htmlspecialchars($imagem); ?>">
                        <input type="submit" name="excluir_imagem" value="Excluir Imagem">
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nenhuma imagem disponível.</p>
        <?php endif; ?>
    </div>
</body>
</html>
