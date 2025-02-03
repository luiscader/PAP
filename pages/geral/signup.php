<?php
// Inclui a configuração do banco de dados
include 'C:/wamp64/www/PAP/includes/config.php';  // Certifique-se de que este caminho esteja correto para o seu arquivo config.php

// Inicializa variáveis de erro e sucesso
$erro = "";
$sucesso = "";
$nome = "";
$email = "";
$telefone = "";
$data_nascimento = "";
$nif = "";
$pais = "";
$distrito = "";
$morada = "";
$codigo_postal = "";

// Define o tipo de usuário automaticamente como 'cliente'
$tipo = 'cliente';

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $data_nascimento = $_POST['data_nascimento'];
    $nif = $_POST['nif'];
    $pais = $_POST['pais'];
    $distrito = $_POST['distrito'];
    $morada = $_POST['morada'];
    $codigo_postal = $_POST['codigo_postal'];
    $senha = $_POST['password'];
    $confirmar_senha = $_POST['confirm_password'];

    // Validação básica
    if (empty($nome) || empty($email) || empty($telefone) || empty($senha) || empty($confirmar_senha)) {
        $erro = "Todos os campos obrigatórios devem ser preenchidos.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        // Verifica se o email já está registrado
        $sql = "SELECT id FROM utilizador WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $erro = "O email já está registrado.";
        } else {
            // Verifica se o telefone já está registrado
            $sql_telefone = "SELECT id FROM utilizador WHERE telefone = ?";
            $stmt_telefone = $conn->prepare($sql_telefone);
            $stmt_telefone->bind_param("s", $telefone);
            $stmt_telefone->execute();
            $stmt_telefone->store_result();

            if ($stmt_telefone->num_rows > 0) {
                $erro = "O número de telefone já está registrado.";
            } else {
                // Hash da senha
                $hash_senha = password_hash($senha, PASSWORD_BCRYPT);

                // Insere o novo usuário na base de dados (inclui campos novos)
                $sql = "INSERT INTO utilizador (nome, email, telefone, senha, tipo, data_nascimento, nif, pais, distrito, morada, codigo_postal) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssssss", $nome, $email, $telefone, $hash_senha, $tipo, $data_nascimento, $nif, $pais, $distrito, $morada, $codigo_postal);

                if ($stmt->execute()) {
                    $sucesso = "Registro realizado com sucesso!";
                    header("Location: login.php");
                    exit();
                } else {
                    $erro = "Erro ao registrar usuário: " . $stmt->error;
                }
            }
            $stmt_telefone->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .register-container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            background-color: #ffffff;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            border-radius: 15px;
            padding: 40px;
        }

        .form-column {
            width: 50%;
            padding: 20px;
        }

        .form-column:first-child {
            border-right: 2px solid #f0f0f0;
        }

        .form-column h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2rem;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-size: 1rem;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: none;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.05);
            color: #333;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            background: rgba(0, 0, 0, 0.1);
            outline: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        button.register-button {
            width: 100%;
            padding: 12px;
            background-color: #ff4b2b;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 20px;
        }

        button.register-button:hover {
            background-color: #ff6b4b;
        }

        p.error {
            color: #f44336;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        p.success {
            color: #4caf50;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .links {
            text-align: center;
            margin-top: 10px;
        }

        a {
            color: #6e45e2;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #88d3ce;
        }

        .forca-senha {
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: none;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.05);
            color: #333;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        select:focus {
            background: rgba(0, 0, 0, 0.1);
            outline: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }


    </style>
</head>
<body>
    <div class="register-container">
        <div class="form-column">
            <h2>Informações Pessoais</h2>
            <form action="signup.php" method="POST">
                <label for="nome">Nome e Apelido *</label>
                <input type="text" id="nome" name="nome" placeholder="Digite seu primeiro e ultimo nome" value="<?php echo htmlspecialchars($nome ?? ''); ?>" required>
                
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" placeholder="Digite seu email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>

                <label for="telefone">Telefone *</label>
                <input type="text" id="telefone" name="telefone" placeholder="Digite seu telefone" value="<?php echo htmlspecialchars($telefone ?? ''); ?>" required>

                <label for="data_nascimento">Data de Nascimento</label>
                <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($data_nascimento ?? ''); ?>">
        </div>
        <div class="form-column">
            <h2>Endereço e Segurança</h2>

            <label for="nif">NIF *</label>
            <input type="text" id="nif" name="nif" placeholder="Digite seu NIF" value="<?php echo htmlspecialchars($nif ?? ''); ?>" required>

            <label for="pais">País *</label>
            <select id="pais" name="pais" required>
                <option value="">Selecione seu país</option>
            </select>

            <label for="morada">Morada *</label>
            <input type="text" id="morada" name="morada" placeholder="Digite sua morada" value="<?php echo htmlspecialchars($morada ?? ''); ?>" required>

            <label for="codigo_postal">Código Postal *</label>
            <input type="text" id="codigo_postal" name="codigo_postal" placeholder="Digite seu código postal" required>

            <label for="distrito">Distrito *</label>
            <input type="text" id="distrito" name="distrito" placeholder="Digite seu distrito" value="<?php echo htmlspecialchars($distrito ?? ''); ?>" readonly>


            <label for="password">Senha *</label>
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Digite sua senha" required>
                <span id="togglePassword" class="toggle-password">👁️‍🗨️</span>
                <p id="forca-senha" class="forca-senha"></p>
            </div>

            <label for="confirm_password">Confirmar Senha *</label>
            <div class="password-container">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirme sua senha" required>
                <span id="toggleConfirmPassword" class="toggle-password">👁️‍🗨️</span>
            </div>

            <button type="submit" class="register-button">Registrar</button>
            <?php if (!empty($erro)) echo "<p class='error'>$erro</p>"; ?>
            <?php if (!empty($sucesso)) echo "<p class='success'>$sucesso</p>"; ?>
        </div>
    </div>

    <script>
    fetch('https://restcountries.com/v3.1/all?fields=name,tld')
        .then(response => response.json())
        .then(data => {
            const paisSelect = document.getElementById('pais');
            data.sort((a, b) => a.name.common.localeCompare(b.name.common));
            data.forEach(country => {
                const option = document.createElement('option');
                option.value = country.name.common;
                option.textContent = country.name.common;
                paisSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Erro ao carregar a lista de países:', error));

    // Busca informações de endereço com a API Zippopotam.us ao preencher o campo de código postal
    const codigoPostalInput = document.getElementById('codigo_postal');
    codigoPostalInput.addEventListener('blur', buscarEndereco);
    
    function buscarEndereco() {
        const codigoPostal = codigoPostalInput.value.trim();
        if (!codigoPostal) {
            alert('Por favor, insira um código postal.');
            return;
        }

        fetch(`https://api.zippopotam.us/PT/${codigoPostal}`)
            .then(response => {
                if (!response.ok) throw new Error('Código postal inválido ou não encontrado.');
                return response.json();
            })
            .then(data => {
                const place = data.places[0];
                document.getElementById('distrito').value = place['state'] || '';
            })
            .catch(error => alert(error.message));
    }

    const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const forcaSenha = document.getElementById('forca-senha');
        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

        passwordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            let strength = '';
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            if (password.length < 6) {
                strength = 'Senha fraca';
                forcaSenha.style.color = 'red';
            } else if (password.length >= 6 && password.length < 10 && hasNumbers && !hasUpperCase && !hasLowerCase) {
                strength = 'Senha média';
                forcaSenha.style.color = 'orange';
            } else if (password.length >= 6 && password.length < 10 && hasUpperCase && hasLowerCase && hasNumbers) {
                strength = 'Senha moderada';
                forcaSenha.style.color = '#EB7300';
            } else if (password.length >= 10 && hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChars) {
                strength = 'Senha forte';
                forcaSenha.style.color = 'green';
            } else {
                strength = 'Senha média';
                forcaSenha.style.color = 'orange';
            }

            forcaSenha.textContent = strength;
        });

        confirmPasswordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value !== passwordInput.value) {
                confirmPasswordInput.setCustomValidity('As senhas não coincidem.');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        });

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️‍🗨️' : '👁️‍🗨️'; // Altera o ícone
        });

        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️‍🗨️' : '👁️‍🗨️'; // Altera o ícone
        });
</script>
</body>
</html>
