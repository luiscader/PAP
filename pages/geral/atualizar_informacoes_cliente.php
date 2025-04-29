<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  

if (!isset($_SESSION['id'])) {
    die('Por favor, faça login para acessar esta página.');
}

$id_utilizador = $_SESSION['id']; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$erro = "";
$sucesso = "";

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

    if (filter_var($novo_email, FILTER_VALIDATE_EMAIL) && preg_match('/^[0-9]{9}$/', $novo_telefone)) {
        $sql_atualiza = "UPDATE utilizador 
                         SET nome = ?, email = ?, telefone = ?, data_nascimento = ?, nif = ?, pais = ?, distrito = ?, morada = ?, codigo_postal = ?
                         WHERE id = ?";
        if ($stmt_atualiza = $conn->prepare($sql_atualiza)) {
            $stmt_atualiza->bind_param("sssssssssi", $novo_nome, $novo_email, $novo_telefone, $nova_data_nascimento, $novo_nif, $novo_pais, $novo_distrito, $nova_morada, $novo_codigo_postal, $id_utilizador);
            if ($stmt_atualiza->execute()) {
                $sucesso = "Informações atualizadas com sucesso!";
            } else {
                $erro = "Erro ao atualizar as informações: " . $conn->error;
            }
            $stmt_atualiza->close();
        } else {
            $erro = "Erro ao preparar a consulta: " . $conn->error;
        }
    } else {
        $erro = "Por favor, insira um email válido e um telefone com 9 dígitos.";
    }
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Informações</title>
    <link rel="shortcut icon" href="../geral/assets/images/favicon.png" />
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
            min-height: 100vh;
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

        .form-column h1 {
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
            <h1>Informações Pessoais</h1>
            <form action="" method="POST">
                <label for="nome">Nome e Apelido *</label>
                <input type="text" id="nome" name="nome" placeholder="Digite seu nome" value="<?php echo htmlspecialchars($utilizador['nome']); ?>" required>
                
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" placeholder="Digite seu email" value="<?php echo htmlspecialchars($utilizador['email']); ?>" required>

                <label for="telefone">Telefone *</label>
                <input type="text" id="telefone" name="telefone" placeholder="Digite seu telefone" value="<?php echo htmlspecialchars($utilizador['telefone']); ?>" required>

                <label for="data_nascimento">Data de Nascimento</label>
                <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($utilizador['data_nascimento']); ?>">
        </div>
        <div class="form-column">
            <h1>Endereço</h1>
                <label for="nif">NIF *</label>
                <input type="text" id="nif" name="nif" placeholder="Digite seu NIF" value="<?php echo htmlspecialchars($utilizador['nif']); ?>" required>

                <label for="pais">País *</label>
                <select id="pais" name="pais" required>
                    <option value="<?php echo htmlspecialchars($utilizador['pais']); ?>" selected><?php echo htmlspecialchars($utilizador['pais']); ?></option>
                </select>

                <label for="morada">Morada *</label>
                <input type="text" id="morada" name="morada" placeholder="Digite sua morada" value="<?php echo htmlspecialchars($utilizador['morada']); ?>" required>

                <label for="codigo_postal">Código Postal *</label>
                <input type="text" id="codigo_postal" name="codigo_postal" placeholder="Digite seu código postal" value="<?php echo htmlspecialchars($utilizador['codigo_postal']); ?>" required>

                <label for="distrito">Distrito *</label>
                <input type="text" id="distrito" name="distrito" placeholder="Digite seu distrito" value="<?php echo htmlspecialchars($utilizador['distrito']); ?>" readonly>

                <button type="submit" class="register-button">Atualizar Informações</button>
                <?php if (!empty($erro)) echo "<p class='error'>$erro</p>"; ?>
                <?php if (!empty($sucesso)) echo "<p class='success'>$sucesso</p>"; ?>
            </form>
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
    </script>
</body>
</html>