<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../geral/login.php");
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

        if ($tipo !== "proprietario") {
            header("Location: ../../geral/index.php");
            exit();
        }
    } else {
        echo "Utilizador não encontrado.";
        exit();
    }
    $stmt->close();
}

$sql = "SELECT r.id FROM restaurante r 
        LEFT JOIN funcionarios f ON r.id = f.id_restaurante 
        WHERE r.id_proprietario = ? OR f.id_utilizador = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $id_cliente, $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $id_restaurante = $row['id'];
    } else {
        echo "Usuário não está associado a nenhum restaurante.";
        exit();
    }
    $stmt->close();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_pedido'])) {
    $id_mesa = $_POST['id_mesa'];
    $prato_ids = $_POST['prato_id'];
    $quantidades = $_POST['quantidade'];
    $observacoes = $_POST['observacoes'];
    
    $conn->begin_transaction();
    
    try {
        foreach ($prato_ids as $key => $id_prato) {
            if ($quantidades[$key] > 0) {
                $stmt = $conn->prepare("SELECT preco FROM pratos WHERE id = ?");
                $stmt->bind_param("i", $id_prato);
                $stmt->execute();
                $result = $stmt->get_result();
                $prato = $result->fetch_assoc();
                $preco_unit = $prato['preco'];
                $preco_total = $preco_unit * $quantidades[$key];
                
                $stmt = $conn->prepare("INSERT INTO pedidos (id_restaurante, id_mesa, id_prato, quantidade, preco_total, observacoes) 
                                        VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiids", $id_restaurante, $id_mesa, $id_prato, $quantidades[$key], $preco_total, $observacoes[$key]);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        $mensagem_sucesso = "Pedido criado com sucesso!";
    } catch (Exception $e) {
        $conn->rollback();
        $mensagem_erro = "Erro ao criar pedido: " . $e->getMessage();
    }
}

$mesas = [];
for ($i = 1; $i <= 20; $i++) { 
    $mesas[] = $i;
}


$categorias = [];
$sql = "SELECT id, nome FROM categoria WHERE id_restaurante = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_restaurante);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $categorias[$row['id']] = [
            'nome' => $row['nome'],
            'pratos' => []
        ];
    }
    $stmt->close();
}

$sql = "SELECT p.id, p.nome, p.descricao, p.preco, p.id_categoria 
        FROM pratos p 
        WHERE p.id_restaurante = ? 
        ORDER BY p.id_categoria, p.nome";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_restaurante);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!isset($categorias[0])) {
        $categorias[0] = [
            'nome' => 'Sem categoria',
            'pratos' => []
        ];
    }
    
    while ($row = $result->fetch_assoc()) {
        $categoria_id = $row['id_categoria'] ? $row['id_categoria'] : 0;

        if (!isset($categorias[$categoria_id])) {
            $categorias[$categoria_id] = [
                'nome' => 'Categoria ' . $categoria_id,
                'pratos' => []
            ];
        }
        
        $categorias[$categoria_id]['pratos'][] = $row;
    }
    $stmt->close();
}

foreach ($categorias as $id => $categoria) {
    if (empty($categoria['pratos'])) {
        unset($categorias[$id]);
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Criar Pedido</title>
    
    <link rel="stylesheet" href="../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
    <style>
        .categoria-section {
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .categoria-header {
            background-color: #212529;
            color: white;
            padding: 12px 15px;
            font-size: 1.2rem;
            font-weight: 500;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        
        .prato-item {
            border: 1px solid #2c2e33;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background-color: #191c24;
            transition: all 0.3s ease;
        }
        
        .prato-item:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .prato-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .prato-price {
            font-weight: bold;
            color: #4CAF50;
            font-size: 1.1rem;
        }
        
        .controls-row {
            display: flex;
            align-items: center;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .quantity-control {
            width: 120px;
            margin-right: 15px;
        }
        
        .obs-field {
            flex-grow: 1;
            min-width: 250px;
        }
        
        .categoria-accordion {
            margin-bottom: 10px;
        }
        
        .categoria-toggle {
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .categoria-toggle .toggle-icon {
            transition: all 0.3s ease;
        }
        
        .categoria-toggle.collapsed .toggle-icon {
            transform: rotate(-90deg);
        }
        
        .total-section {
            margin-top: 20px;
            background-color: #191c24;
            padding: 15px;
            border-radius: 5px;
            font-size: 1.1rem;
        }
        
        .sticky-bottom {
            position: sticky;
            bottom: 0;
            background-color: #191c24;
            padding: 15px;
            border-top: 1px solid #2c2e33;
            z-index: 1000;
        }
        
        .prato-description {
            color: #adb5bd;
            margin-bottom: 10px;
        }
        
        .mesa-selector {
            max-width: 300px;
        }
    </style>
</head>
<body class="sidebar-fixed">
    <div class="container-scroller">
      <?php include 'sidebar.php'?>
      <div class="page-body-wrapper">
        <?php include 'navbar.php'?>
        
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Criar Pedido</h4>
                        
                        <?php if (isset($mensagem_sucesso)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $mensagem_sucesso; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($mensagem_erro)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $mensagem_erro; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="pedidoForm">
                            <div class="form-group mesa-selector">
                                <label for="id_mesa">Selecione a Mesa</label>
                                <select class="form-control" id="id_mesa" name="id_mesa" required>
                                    <option value="">Selecione uma mesa</option>
                                    <?php foreach ($mesas as $mesa): ?>
                                        <option value="<?php echo $mesa; ?>">Mesa <?php echo $mesa; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <?php if (empty($categorias)): ?>
                                <div class="alert alert-info mt-4">
                                    Não há pratos cadastrados para este restaurante.
                                </div>
                            <?php else: ?>
                                <h5 class="mt-4 mb-3">Menu</h5>
                                
                                <div id="accordion">
                                    <?php foreach ($categorias as $categoria_id => $categoria): ?>
                                        <div class="categoria-section">
                                            <div class="categoria-header categoria-toggle" 
                                                 data-toggle="collapse" 
                                                 data-target="#categoria_<?php echo $categoria_id; ?>" 
                                                 aria-expanded="true">
                                                <span><?php echo htmlspecialchars($categoria['nome']); ?></span>
                                                <i class="mdi mdi-chevron-down toggle-icon"></i>
                                            </div>
                                            
                                            <div id="categoria_<?php echo $categoria_id; ?>" class="collapse show">
                                                <div class="p-3">
                                                    <?php foreach ($categoria['pratos'] as $index => $prato): ?>
                                                        <div class="prato-item">
                                                            <div class="prato-header">
                                                                <h5 class="mb-0"><?php echo htmlspecialchars($prato['nome']); ?></h5>
                                                                <span class="prato-price"><?php echo number_format($prato['preco'], 2, ',', '.'); ?>€</span>
                                                            </div>
                                                            
                                                            <p class="prato-description"><?php echo htmlspecialchars($prato['descricao']); ?></p>
                                                            
                                                            <input type="hidden" name="prato_id[]" value="<?php echo $prato['id']; ?>">
                                                            
                                                            <div class="controls-row">
                                                                <div class="form-group quantity-control">
                                                                    <label for="quantidade_<?php echo $prato['id']; ?>">Quantidade</label>
                                                                    <input type="number" class="form-control quantidade-input" 
                                                                           id="quantidade_<?php echo $prato['id']; ?>" 
                                                                           name="quantidade[]" min="0" value="0"
                                                                           data-preco="<?php echo $prato['preco']; ?>">
                                                                </div>
                                                                
                                                                <div class="form-group obs-field">
                                                                    <label for="observacoes_<?php echo $prato['id']; ?>">Observações</label>
                                                                    <input type="text" class="form-control" 
                                                                           id="observacoes_<?php echo $prato['id']; ?>" 
                                                                           name="observacoes[]" 
                                                                           placeholder="Ex: Sem cebola, bem passado...">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="total-section">
                                    <div class="d-flex justify-content-between">
                                        <span>Total do Pedido:</span>
                                        <span id="total-pedido" class="font-weight-bold">0,00€</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group mt-4 sticky-bottom">
                                <button type="submit" name="submit_pedido" class="btn btn-primary btn-lg">
                                    <i class="mdi mdi-check-circle"></i> Confirmar Pedido
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php include '../footer.php'; ?>
        </div>
      </div>
    </div>

    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="../assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
    <script src="../assets/js/off-canvas.js"></script>
    <script src="../assets/js/misc.js"></script>
    <script src="../assets/js/settings.js"></script>
    <script src="../assets/js/todolist.js"></script>
    <script src="../assets/js/data-table.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quantidadeInputs = document.querySelectorAll('.quantidade-input');
            
            quantidadeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value < 0) this.value = 0;
                    calculateTotal();
                });
            });

            function calculateTotal() {
                let total = 0;
                quantidadeInputs.forEach(input => {
                    const quantidade = parseInt(input.value) || 0;
                    const preco = parseFloat(input.dataset.preco) || 0;
                    total += quantidade * preco;
                });

                const formattedTotal = total.toLocaleString('pt-PT', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                
                document.getElementById('total-pedido').textContent = formattedTotal + '€';
                return total;
            }

            const categoriasHeaders = document.querySelectorAll('.categoria-toggle');
            categoriasHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    this.classList.toggle('collapsed');
                });
            });

            document.getElementById('pedidoForm').addEventListener('submit', function(event) {
                const total = calculateTotal();
                const mesa = document.getElementById('id_mesa').value;
                
                if (total <= 0) {
                    event.preventDefault();
                    alert('Adicione ao menos um item ao pedido antes de confirmar.');
                    return false;
                }
                
                if (!mesa) {
                    event.preventDefault();
                    alert('Selecione uma mesa para o pedido.');
                    return false;
                }
                
                return true;
            });

            calculateTotal();
        });
    </script>
</body>
</html>