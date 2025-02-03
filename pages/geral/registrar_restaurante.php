<?php
include 'C:/wamp64/www/PAP/includes/config.php'; 
include 'C:/wamp64/www/PAP/includes/atualizar_tipo_usuario.php'; 

session_start(); // Inicia a sessão

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); 
    exit();
}

// Verifica se o usuário é cliente
if ($_SESSION['tipo'] != 'cliente') {
    echo "Acesso negado. Somente clientes podem registrar um restaurante.";
    exit();
}

// Carregar os tipos de gastronomia
$sql_tipos_gastronomia = "SELECT id, nome FROM TipoCozinha";
$result_tipos_gastronomia = $conn->query($sql_tipos_gastronomia);

if ($result_tipos_gastronomia === FALSE) {
    echo "Erro ao carregar os tipos de gastronomia: " . $conn->error;
    exit();
}

// Registro de Restaurante
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_restaurante'])) {
    $nome_restaurante = $_POST['nome_restaurante'] ?? null;
    $nif_restaurante = $_POST['nif_restaurante'] ?? null;
    $designacao_legal = $_POST['designacao_legal'] ?? null;
    $morada_restaurante = $_POST['morada'] ?? null;
    $codigo_postal_restaurante = $_POST['codigo_postal'] ?? null;
    $distrito_restaurante = $_POST['distrito'] ?? null;
    $pais_restaurante = $_POST['pais'] ?? null;
    $telefone_restaurante = $_POST['telefone_restaurante'] ?? null;
    $nome_banco = $_POST['nome_banco'] ?? null;
    $iban = $_POST['iban'] ?? null;
    $titular_conta = $_POST['titular_conta'] ?? null;
    $email_contato = $_POST['email_contato'] ?? null;
    $numero_contato = $_POST['numero_contato'] ?? null;
    $tipos_gastronomia = $_POST['tipos_gastronomia'] ?? []; // Recebe os tipos de gastronomia selecionados
    $id_proprietario = $_SESSION['id']; // Usa o ID do usuário logado

    // Validações adicionais
    if (!$nome_restaurante || !$nif_restaurante || !$designacao_legal || !$morada_restaurante || !$codigo_postal_restaurante || !$distrito_restaurante || !$pais_restaurante || !$telefone_restaurante || !$nome_banco || !$iban || !$titular_conta || !$email_contato || !$numero_contato || empty($tipos_gastronomia)) {
        $erro = "Todos os campos obrigatórios devem ser preenchidos.";
    } else {
        // Inserção na tabela Restaurante
        $sql_restaurante = "INSERT INTO Restaurante 
            (nome_empresa, nif, designacao_legal, morada, codigo_postal, distrito, pais, telefone, email_contato, numero_contato, nome_banco, iban, titular_conta, id_proprietario) 
            VALUES 
            ('$nome_restaurante', '$nif_restaurante', '$designacao_legal', '$morada_restaurante', '$codigo_postal_restaurante', '$distrito_restaurante', '$pais_restaurante', '$telefone_restaurante', '$email_contato', '$numero_contato', '$nome_banco', '$iban', '$titular_conta', '$id_proprietario')";

        if ($conn->query($sql_restaurante) === TRUE) {
            // Recupera o ID do restaurante inserido
            $id_restaurante = $conn->insert_id;

            // Inserir associações na tabela intermediária Restaurante_TipoCozinha
            if (!empty($tipos_gastronomia)) {
                foreach ($tipos_gastronomia as $id_tipo) {
                    if (is_numeric($id_tipo)) {
                        $sql_associacao = "INSERT INTO Restaurante_TipoCozinha (id_restaurante, id_tipo_cozinha) VALUES ('$id_restaurante', '$id_tipo')";
                        $conn->query($sql_associacao);
                    }
                }
            }

            // Atualiza a tabela Utilizador com o ID do restaurante
            $sql_update_utilizador = "UPDATE Utilizador SET id_restaurante = '$id_restaurante' WHERE id = '$id_proprietario'";
            if ($conn->query($sql_update_utilizador) === TRUE) {
                // Atualiza o tipo de usuário para 'proprietario'
                if (atualizar_tipo_usuario($conn, $id_proprietario, 'proprietario')) {
                    // Finaliza a sessão do usuário e redireciona para a página de login
                    session_unset(); // Limpa a sessão
                    session_destroy(); // Destroi a sessão
                    header("Location: login.php?msg=registro_sucesso"); // Redireciona com uma mensagem opcional
                    exit();
                } else {
                    echo "Erro ao atualizar o tipo de usuário.";
                }
            } else {
                echo "Erro ao atualizar a tabela Utilizador: " . $conn->error;
            }
        } else {
            echo "Erro ao registrar restaurante: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Restaurante</title>
    <style>
        /* Estilos gerais do formulário */
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
            flex-wrap: wrap;
        }

        form {
            display: flex;
            width: 100%;
            flex-wrap: wrap;
        }

        .form-column {
            width: 30%;
            padding: 20px;
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
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

        input, select {
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

        input:focus, select:focus {
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
        }

        button.register-button:hover {
            background-color: #ff6b4b;
        }

        p.error {
            color: #f44336;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        /* Ajustando o container da checkbox */
        .checkbox-container {
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background-color: #f7f7f7;
            border-radius: 8px;
            padding: 10px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .checkbox-container label {
            display: flex;
            align-items: center;
            font-size: 1rem;
            margin-bottom: 10px;
            cursor: pointer;
        }

        .checkbox-container input[type="checkbox"] {
            margin-right: 10px;
        }
    </style>
</head>
<body>

<div class="register-container">
    <form action="" method="post">
        <div class="form-column">
            <h2>Registrar Restaurante</h2>

            <label for="nome_restaurante">Nome do Restaurante</label>
            <input type="text" id="nome_restaurante" name="nome_restaurante" placeholder="Nome do Restaurante" required>
            
            <label for="nif_restaurante">NIF</label>
            <input type="text" id="nif_restaurante" name="nif_restaurante" placeholder="NIF" required>

            <label for="designacao_legal">Designação Legal</label>
            <input type="text" id="designacao_legal" name="designacao_legal" placeholder="Designação Legal" required>
            
            <label for="morada">Morada</label>
            <input type="text" id="morada" name="morada" placeholder="Morada" required>
            
            <label for="codigo_postal">Código Postal</label>
            <input type="text" id="codigo_postal" name="codigo_postal" placeholder="Código Postal" required>
            
            <label for="distrito">Distrito</label>
            <input type="text" id="distrito" name="distrito" placeholder="Distrito" required>
            
            <label for="pais">País</label>
            <input type="text" id="pais" name="pais" placeholder="País" required>
        </div>

        <div class="form-column">
            <label for="telefone_restaurante">Telefone</label>
            <input type="text" id="telefone_restaurante" name="telefone_restaurante" placeholder="Telefone" required>
            
            <label for="nome_banco">Nome do Banco</label>
            <input type="text" id="nome_banco" name="nome_banco" placeholder="Nome do Banco" required>

            <label for="iban">IBAN</label>
            <input type="text" id="iban" name="iban" placeholder="IBAN" required>
            
            <label for="titular_conta">Titular da Conta</label>
            <input type="text" id="titular_conta" name="titular_conta" placeholder="Titular da Conta" required>

            <label for="email_contato">Email de Contato</label>
            <input type="email" id="email_contato" name="email_contato" placeholder="Email" required>
            
            <label for="numero_contato">Número de Contato</label>
            <input type="text" id="numero_contato" name="numero_contato" placeholder="Número de Contato" required>
        </div>

        <div class="form-column">
            <label>Tipos de Gastronomia</label>
            <div class="checkbox-container">
                <?php while($row = $result_tipos_gastronomia->fetch_assoc()): ?>
                    <label>
                        <input type="checkbox" name="tipos_gastronomia[]" value="<?php echo $row['id']; ?>"> 
                        <?php echo $row['nome']; ?>
                    </label>
                <?php endwhile; ?>
            </div>

            <?php if(isset($erro)): ?>
                <p class="error"><?php echo $erro; ?></p>
            <?php endif; ?>

            <button type="submit" name="registrar_restaurante" class="register-button">Registrar Restaurante</button>
        </div>
    </form>
</div>

</body>
</html>



<script>
    // Preenche a lista de países usando a API RestCountries
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
</script>
</html>
