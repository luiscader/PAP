<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  // Certifique-se de que o caminho está correto

// Funções de conversão
function converterParaGramas($quantidade, $unidade) {
    switch ($unidade) {
        case 'Kg':
            return $quantidade * 1000; // 1 Kg = 1000 Gr
        case 'Gr':
            return $quantidade; // Já está em Gr
        case 'L':
            return $quantidade * 1000; // 1 L = 1000 Ml
        case 'Ml':
            return $quantidade; // Já está em Ml
        default:
            return 0; // Unidade desconhecida
    }
}

function converterParaUnidadeOriginal($quantidade, $unidade_original, $unidade_desejada) {
    if ($unidade_original == $unidade_desejada) {
        return $quantidade; // Não precisa converter
    }
    
    switch ($unidade_original) {
        case 'Kg':
            if ($unidade_desejada == 'Gr') {
                return $quantidade * 1000;
            }
            break;
        case 'Gr':
            if ($unidade_desejada == 'Kg') {
                return $quantidade / 1000;
            }
            break;
        case 'L':
            if ($unidade_desejada == 'Ml') {
                return $quantidade * 1000;
            }
            break;
        case 'Ml':
            if ($unidade_desejada == 'L') {
                return $quantidade / 1000;
            }
            break;
    }
    
    return $quantidade; // Se não houver conversão definida
}

// Verifica se o utilizador está logado e se é proprietário
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'proprietario') {
    die('Acesso restrito. Apenas proprietários podem acessar esta página.');
}

$id_restaurante = $_SESSION['id_restaurante'];

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Processa a criação do prato e a adição de ingredientes
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_prato = $_POST['nome_prato'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $ingredientes = isset($_POST['ingredientes']) ? $_POST['ingredientes'] : [];
    $quantidades = isset($_POST['quantidades']) ? $_POST['quantidades'] : [];
    $unidades = isset($_POST['unidades']) ? $_POST['unidades'] : [];
    
    if (!empty($nome_prato) && !empty($preco) && !empty($ingredientes)) {
        // Insere o novo prato no banco de dados
        $sql_prato = "INSERT INTO pratos (id_restaurante, nome, descricao, preco, data_criacao) VALUES (?, ?, ?, ?, NOW())";
        if ($stmt_prato = $conn->prepare($sql_prato)) {
            $stmt_prato->bind_param("issd", $id_restaurante, $nome_prato, $descricao, $preco);
            if ($stmt_prato->execute()) {
                $id_prato = $stmt_prato->insert_id; // ID do prato recém-criado
                
                // Insere os ingredientes para o prato
                $sql_ingrediente = "INSERT INTO ingrediente_prato (id_prato, id_produto, quantidade_necessaria, unidade_medida) VALUES (?, ?, ?, ?)";
                $stmt_ingrediente = $conn->prepare($sql_ingrediente);
                
                foreach ($ingredientes as $index => $id_produto) {
                    $quantidade_necessaria = isset($quantidades[$index]) ? $quantidades[$index] : 0;
                    $unidade_medida = isset($unidades[$index]) ? $unidades[$index] : 'unidade';
                    
                    // Obtém a unidade de medida do produto
                    $sql_unidade_produto = "SELECT unidade_medida FROM produto WHERE id = ?";
                    $stmt_unidade_produto = $conn->prepare($sql_unidade_produto);
                    $stmt_unidade_produto->bind_param("i", $id_produto);
                    $stmt_unidade_produto->execute();
                    $stmt_unidade_produto->bind_result($unidade_produto);
                    $stmt_unidade_produto->fetch();
                    $stmt_unidade_produto->close();
                    
                    if ($unidade_produto == 'unidade' && $unidade_medida != 'unidade') {
                        echo "A unidade selecionada para o produto ID $id_produto não é compatível. Apenas 'Unidade' é permitida.";
                        exit();
                    }
                    
                    // Converte a quantidade para a unidade de medida do produto
                    $quantidade_necessaria_convertida = converterParaUnidadeOriginal($quantidade_necessaria, $unidade_medida, $unidade_produto);
                    
                    $stmt_ingrediente->bind_param("iids", $id_prato, $id_produto, $quantidade_necessaria_convertida, $unidade_produto);
                    $stmt_ingrediente->execute();
                }

                $stmt_ingrediente->close();
                echo "Prato criado com sucesso!";
            } else {
                echo "Erro ao criar prato: " . $conn->error;
            }
            $stmt_prato->close();
        } else {
            echo "Erro ao preparar a consulta: " . $conn->error;
        }
    } else {
        echo "Por favor, preencha todos os campos obrigatórios.";
    }
}

// Recupera a lista de produtos (ingredientes) disponíveis e suas unidades de medida e descrições
$sql_produtos = "SELECT id, nome, descricao, unidade_medida FROM produto WHERE id_restaurante = ?";
$stmt_produtos = $conn->prepare($sql_produtos);
$stmt_produtos->bind_param("i", $id_restaurante);
$stmt_produtos->execute();
$result_produtos = $stmt_produtos->get_result();
$produtos = [];
while ($row = $result_produtos->fetch_assoc()) {
    $produtos[] = $row;
}
$stmt_produtos->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Prato</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f4f8;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #007bff;
        }

        form {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        select, input[type="number"], textarea {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="number"] {
            -moz-appearance: textfield; /* Remove o spinner do input number no Firefox */
        }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        textarea {
            height: 100px;
            resize: vertical; /* Permite que o textarea seja redimensionado verticalmente */
        }

        input[type="submit"] {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838;
        }

        .disabled {
            background-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }

        .ingrediente-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ingrediente-item select, 
        .ingrediente-item input[type="number"] {
            flex: 1;
            margin-right: 10px;
        }

        .ingrediente-item button {
            margin-left: 10px;
        }
    </style>
</head>
<body>

<h1>Criar Novo Prato</h1>

<form method="post" action="">
        <label for="nome_prato">Nome do Prato:</label>
        <input type="text" id="nome_prato" name="nome_prato" required>
        <br><br>
        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao"></textarea>

        <label for="preco">Preço:</label>
        <input type="number" id="preco" name="preco" step="0.01" required>

        <label for="ingredientes">Ingredientes:</label>
        <div id="ingredientes">
            <div class="ingrediente-item">
                <select name="ingredientes[]" onchange="atualizarUnidades(this)">
                    <?php foreach ($produtos as $produto): ?>
                        <option value="<?php echo $produto['id']; ?>" data-unidade="<?php echo $produto['unidade_medida']; ?>">
                            <?php echo $produto['nome'] . " (" . $produto['unidade_medida'] . "): " . htmlspecialchars($produto['descricao']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="quantidades[]" placeholder="Quantidade necessária" step="0.01" required>
                <select name="unidades[]" class="unidade-select">
                    <!-- Opções serão atualizadas pelo JavaScript -->
                </select>
            </div>
        </div>

        <button type="button" onclick="adicionarIngrediente()">Adicionar Ingrediente</button><br><br>

        <input type="submit" value="Criar Prato">
    </form>

<script>
        function adicionarIngrediente() {
            const div = document.getElementById('ingredientes');
            const item = div.querySelector('.ingrediente-item').cloneNode(true);
            
            // Limpa os valores dos novos campos
            item.querySelector('select[name="ingredientes[]"]').value = '';
            item.querySelector('input[name="quantidades[]"]').value = '';
            item.querySelector('select[name="unidades[]"]').innerHTML = ''; // Limpa as opções
            
            item.querySelector('select[name="ingredientes[]"]').addEventListener('change', function() {
                atualizarUnidades(this);
            });
            
            div.appendChild(document.createElement('br'));
            div.appendChild(item);
        }

        function atualizarUnidades(select) {
            const unidadeSelecionada = select.options[select.selectedIndex].dataset.unidade;
            const parentDiv = select.closest('div');
            const unidadeSelect = parentDiv.querySelector('select[name="unidades[]"]');
            
            unidadeSelect.innerHTML = ''; // Limpa as opções existentes
            
            if (unidadeSelecionada === 'unidade') {
                unidadeSelect.innerHTML = '<option value="unidade">Unidade</option>';
                unidadeSelect.disabled = true;
            } else {
                // Define opções disponíveis com base na unidade do produto
                if (['Kg', 'Gr'].includes(unidadeSelecionada)) {
                    unidadeSelect.innerHTML = `
                        <option value="Kg">Kg</option>
                        <option value="Gr">Gr</option>
                    `;
                } else if (['L', 'Ml'].includes(unidadeSelecionada)) {
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
            }
        }
        
        // Inicializa as unidades de medida para os ingredientes existentes
        document.querySelectorAll('select[name="ingredientes[]"]').forEach(select => {
            atualizarUnidades(select);
        });
    </script>
</body>

</body>
</html>
