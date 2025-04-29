<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['password'];

    $sql = "SELECT id, senha, tipo, id_restaurante, id_fornecedor FROM Utilizador WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $hash_senha, $tipo, $id_restaurante, $id_fornecedor);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            if (password_verify($senha, $hash_senha)) {
                $_SESSION['id'] = $id;
                $_SESSION['tipo'] = $tipo;
                $_SESSION['id_restaurante'] = $id_restaurante;
                $_SESSION['id_fornecedor'] = $id_fornecedor;
                header("Location: index.php");
                exit();
            } else {
                $erro = "Senha incorreta.";
            }
        } else {
            $erro = "Email não encontrado.";
        }
        $stmt->close();
    } else {
        $erro = "Erro ao preparar a consulta.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        .login-container, .register-container {
            width: 300px;
            margin: 100px auto;
            padding: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            text-align: left;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 5px;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        .remember-me {
            text-align: left;
            margin-bottom: 15px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #ff4b2b;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #ff6b4b;
        }

        .links {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .links a {
            color: #ff4b2b;
            text-decoration: none;
        }

        .links a:hover {
            text-decoration: underline;
        }


    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <form method="post" action="login.php">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Entrar</button>
            <?php if (isset($erro)) echo "<p class='error'>$erro</p>"; ?>
        </form>
        <div class="links">
            <a href="signup.php">Ainda não tem uma conta?</a>
        </div>
    </div>
</body>
</html>
