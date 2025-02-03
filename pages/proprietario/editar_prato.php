<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  // Certifique-se de que o caminho está correto

// Verifica se o usuário está logado e se é um proprietário ou gerente
if (!isset($_SESSION['id']) || ($_SESSION['tipo'] !== 'proprietario' && $_SESSION['tipo'] !== 'gerente')) {
    die(json_encode(['success' => false, 'message' => 'Acesso restrito. Apenas proprietários e gerentes podem acessar esta página.']));
}

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Falha na conexão: ' . $conn->connect_error]));
}

// Processa a atualização do prato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prato_id = $_POST['prato_id'];
    $ingredientes = $_POST['ingredientes'];
    $quantidades = $_POST['quantidades'];
    $unidades = $_POST['unidades'];

    // Atualizar ou inserir os ingredientes do prato
    $conn->query("DELETE FROM ingrediente_prato WHERE id_prato = $prato_id");

    foreach ($ingredientes as $index => $ingrediente_id) {
        $quantidade = $quantidades[$index];
        $unidade = $unidades[$index];
        $conn->query("INSERT INTO ingrediente_prato (id_prato, id_produto, quantidade_necessaria, unidade_medida) VALUES ($prato_id, $ingrediente_id, $quantidade, '$unidade')");
    }

    // Retornar resposta JSON
    echo json_encode(['success' => true, 'message' => 'Prato atualizado com sucesso!']);
    exit;
}

// Recuperar dados dos produtos
$produtos_query = "SELECT id, nome, descricao, unidade_medida FROM produto";
$produtos_result = $conn->query($produtos_query);
$produtos = $produtos_result->fetch_all(MYSQLI_ASSOC);

// Recuperar dados dos ingredientes do prato
$prato_id = isset($_GET['id']) ? intval($_GET['id']) : 0; // Supondo que o ID do prato está na URL
$ingredientes_query = "
    SELECT ip.id, ip.id_produto, ip.quantidade_necessaria, ip.unidade_medida, p.nome, p.descricao 
    FROM ingrediente_prato ip
    JOIN produto p ON ip.id_produto = p.id
    WHERE ip.id_prato = $prato_id
";
$ingredientes_result = $conn->query($ingredientes_query);
$ingredientes = $ingredientes_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Prato</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }

        h1 {
            color: #333;
            text-align: center;
        }

        .form-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input[type="text"], input[type="number"], textarea, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"], button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        input[type="submit"]:hover, button:hover {
            background-color: #0056b3;
        }

        .remove-ingrediente {
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 5px;
            margin-left: 10px;
        }

        .remove-ingrediente:hover {
            background-color: #c82333;
        }

        .ingrediente {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
        }

        .ingrediente select, .ingrediente input {
            flex: 1;
        }

        .ingrediente button {
            margin-left: 10px;
        }

        .button-container {
            text-align: center;
            margin-top: 20px;
        }

        .button-container a {
            text-decoration: none;
            color: white;
            background-color: #28a745;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }

        .button-container a:hover {
            background-color: #218838;
        }

        /* Estilo do botão para adicionar ingrediente */
        .adicionar-ingrediente {
            display: block;
            width: 100%;
            text-align: center;
            background-color: #28a745;
            margin-top: 15px;
        }

        .adicionar-ingrediente:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h1>Atualizar Prato</h1>
    <div class="form-container">
        <form id="updateForm">
            <input type="hidden" name="prato_id" value="<?php echo htmlspecialchars($prato_id); ?>">
            <label for="nome_prato">Nome do Prato:</label>
            <input type="text" id="nome_prato" name="nome_prato" value="Hamburger na agua" required>

            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" required>Hamburger na agua</textarea>

            <label for="preco">Preço:</label>
            <input type="number" id="preco" name="preco" value="40.00" step="0.01" required>

            <label for="ingredientes">Ingredientes:</label>
            <div id="ingredientes">
                <?php foreach ($ingredientes as $ingrediente): ?>
                    <div class="ingrediente">
                        <select name="ingredientes[]" onchange="atualizarUnidades(this)" required>
                            <option value="<?php echo htmlspecialchars($ingrediente['id_produto']); ?>" data-unidade="<?php echo htmlspecialchars($ingrediente['unidade_medida']); ?>" selected>
                                <?php echo htmlspecialchars($ingrediente['nome']) . ' - ' . htmlspecialchars($ingrediente['descricao']); ?>
                            </option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?php echo htmlspecialchars($produto['id']); ?>" data-unidade="<?php echo htmlspecialchars($produto['unidade_medida']); ?>">
                                    <?php echo htmlspecialchars($produto['nome']) . ' - ' . htmlspecialchars($produto['descricao']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="quantidades[]" value="<?php echo htmlspecialchars($ingrediente['quantidade_necessaria']); ?>" placeholder="Quantidade" step="0.01" required>
                        <select name="unidades[]" required>
                            <!-- As opções serão atualizadas via JavaScript -->
                        </select>
                        <button type="button" class="remove-ingrediente" onclick="removerIngrediente(this)">X</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="adicionar-ingrediente" onclick="adicionarIngrediente()">Adicionar Ingrediente</button>
            <input type="submit" value="Atualizar Prato">
        </form>
    </div>

    <div id="message"></div>

    <div class="button-container">
        <a href="gestao_pratos.php">Voltar</a>
    </div>

    <script>
        const produtos = <?php echo json_encode($produtos); ?>;
        const unidadesPeso = ['Kg', 'Gr'];
        const unidadesLiquido = ['L', 'Ml'];

        function adicionarIngrediente() {
            const div = document.getElementById('ingredientes');
            const div_ingrediente = document.createElement('div');
            div_ingrediente.classList.add('ingrediente');

            div_ingrediente.innerHTML = `
                <select name="ingredientes[]" onchange="atualizarUnidades(this)" required>
                    <option value="" data-unidade="">Selecione um ingrediente</option>
                    ${produtos.map(produto => `
                        <option value="${produto.id}" data-unidade="${produto.unidade_medida}">
                            ${produto.nome} - ${produto.descricao}
                        </option>
                    `).join('')}
                </select>
                <input type="number" name="quantidades[]" placeholder="Quantidade" step="0.01" required>
                <select name="unidades[]" required>
                    <!-- As opções serão atualizadas via JavaScript -->
                </select>
                <button type="button" class="remove-ingrediente" onclick="removerIngrediente(this)">X</button>
            `;
            div.appendChild(div_ingrediente);
        }

        function removerIngrediente(button) {
            button.parentElement.remove();
        }

        function atualizarUnidades(select) {
            const unidadeSelecionada = select.options[select.selectedIndex].dataset.unidade;
            const parentDiv = select.closest('div');
            const unidadeSelect = parentDiv.querySelector('select[name="unidades[]"]');
            
            unidadeSelect.innerHTML = ''; // Limpa as opções existentes
            
            if (unidadeSelecionada) {
                if (unidadesPeso.includes(unidadeSelecionada)) {
                    unidadeSelect.innerHTML = `
                        <option value="Kg">Kg</option>
                        <option value="Gr">Gr</option>
                    `;
                } else if (unidadesLiquido.includes(unidadeSelecionada)) {
                    unidadeSelect.innerHTML = `
                        <option value="L">L</option>
                        <option value="Ml">Ml</option>
                    `;
                } else {
                    unidadeSelect.innerHTML = `
                        <option value="${unidadeSelecionada}">${unidadeSelecionada}</option>
                    `;
                }
                unidadeSelect.disabled = false;
                unidadeSelect.value = unidadeSelecionada;
            } else {
                unidadeSelect.innerHTML = '<option value="">Selecione um ingrediente</option>';
                unidadeSelect.disabled = true;
            }
        }

        // Inicializa as unidades dos ingredientes existentes
        document.querySelectorAll('select[name="ingredientes[]"]').forEach(select => {
            atualizarUnidades(select);
        });

        // Enviar formulário via AJAX
        document.getElementById('updateForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Evita o envio padrão do formulário

            const formData = new FormData(this);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('message').innerHTML = `<p>${data.message}</p>`;
            })
            .catch(error => {
                document.getElementById('message').innerHTML = '<p>Erro na requisição.</p>';
                console.error('Erro:', error);
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
