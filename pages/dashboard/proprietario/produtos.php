<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../geral/login.php");
    exit();
}

$id_cliente = $_SESSION['id'];
$message = '';
$restaurante_id = null;
$products = [];
$fornecedores = [];

$sql = "SELECT tipo, id FROM utilizador WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tipo_usuario = $row['tipo'];
    } else {
        echo "Usuário não encontrado.";
        exit();
    }
    $stmt->close();
}

$sql = "SELECT id FROM restaurante WHERE id_proprietario = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $restaurante_id = $row['id'];
    }
    $stmt->close();
}

$sql = "SELECT id, empresa FROM fornecedor";
if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    $fornecedores = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['restaurante_id']) && isset($_POST['product_id']) && !isset($_POST['get_details']) && !isset($_POST['edit_product'])) {
        $restaurante_id = $_POST['restaurante_id'];
        $product_id = $_POST['product_id'];

        $sql = "DELETE FROM produtos WHERE id_restaurante = ? AND id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $restaurante_id, $product_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao remover produto: ' . $conn->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
        }
        $conn->close();
        exit();
    } elseif (isset($_POST['get_details']) && isset($_POST['product_id'])) {
        $product_id = $_POST['product_id'];
        $sql = "SELECT p.nome, p.descricao, p.quantidade, p.unidade_medida, p.data_atualizacao, p.id_fornecedor,
                c.nome AS categoria_nome, f.empresa AS fornecedor_nome 
                FROM produtos p 
                LEFT JOIN categoria c ON p.id_categoria = c.id 
                LEFT JOIN fornecedor f ON p.id_fornecedor = f.id 
                WHERE p.id = ? AND p.id_restaurante = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $product_id, $restaurante_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Produto não encontrado']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
        }
        $conn->close();
        exit();
    } elseif (isset($_POST['edit_product']) && isset($_POST['product_id']) && isset($_POST['nome']) && isset($_POST['descricao']) && isset($_POST['quantidade']) && isset($_POST['unidade_medida']) && isset($_POST['fornecedor'])) {
        $product_id = $_POST['product_id'];
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $quantidade = $_POST['quantidade'];
        $unidade_medida = $_POST['unidade_medida'];
        $fornecedor = $_POST['fornecedor'] === '' ? null : $_POST['fornecedor'];
        $restaurante_id = $_POST['restaurante_id'];

        error_log("Dados recebidos: product_id=$product_id, nome=$nome, descricao=$descricao, quantidade=$quantidade, unidade_medida=$unidade_medida, fornecedor=" . ($fornecedor ?? 'NULL'));

        $valid_units = ['Unidade', 'Kg', 'Gr', 'L', 'Ml'];
        if (!in_array($unidade_medida, $valid_units)) {
            error_log("Unidade de medida inválida recebida: $unidade_medida");
            echo json_encode(['status' => 'error', 'message' => 'Unidade de medida inválida: ' . $unidade_medida]);
            $conn->close();
            exit();
        }

        $sql = "UPDATE produtos SET nome = ?, descricao = ?, quantidade = ?, unidade_medida = ?, id_fornecedor = ?, data_atualizacao = NOW() WHERE id = ? AND id_restaurante = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssdssii", $nome, $descricao, $quantidade, $unidade_medida, $fornecedor, $product_id, $restaurante_id);
            if ($stmt->execute()) {
                error_log("Produto atualizado com sucesso: unidade_medida=$unidade_medida");
                echo json_encode(['status' => 'success']);
            } else {
                error_log("Erro ao executar UPDATE: " . $conn->error);
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar produto: ' . $conn->error]);
            }
            $stmt->close();
        } else {
            error_log("Erro na preparação da query: " . $conn->error);
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
        }
        $conn->close();
        exit();
    }
}

if ($restaurante_id) {
    $sql = "SELECT p.id, p.nome, p.descricao, p.quantidade, p.unidade_medida, 
            c.nome AS categoria_nome, p.data_atualizacao, f.empresa AS fornecedor_nome 
            FROM produtos p 
            LEFT JOIN categoria c ON p.id_categoria = c.id 
            LEFT JOIN fornecedor f ON p.id_fornecedor = f.id 
            WHERE p.id_restaurante = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $restaurante_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
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
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gestão de Produtos - Restomate</title>
    
    <link rel="stylesheet" href="../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
</head>
<body class="sidebar-fixed">
    <div class="container-scroller">
        <?php include 'sidebar.php'?>
        <div class="page-body-wrapper">
            <?php include 'navbar.php'?>
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title">Gestão de Produtos</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="produtos.php">Produtos</a></li>
                                <li class="breadcrumb-item" aria-current="page"><strong>Produtos</strong></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title">Produtos</h4>
                                <a href="criar_produto.php" class="btn btn-primary">Criar Produto</a>
                            </div>
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-<?php echo $message_type ?? 'info'; ?> alert-dismissible fade show" role="alert">
                                    <?php echo $message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table id="order-listing" class="table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nome</th>
                                                    <th>Descrição</th>
                                                    <th>Quantidade</th>
                                                    <th>Unidade</th>
                                                    <th>Categoria</th>
                                                    <th>Fornecedor</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($products)): ?>
                                                    <?php foreach ($products as $product): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($product['id']); ?></td>
                                                            <td><?php echo htmlspecialchars($product['nome']); ?></td>
                                                            <td><?php echo htmlspecialchars($product['descricao']); ?></td>
                                                            <td><?php echo htmlspecialchars($product['quantidade']); ?></td>
                                                            <td><?php echo htmlspecialchars($product['unidade_medida'] ?: 'Unidade'); ?></td>
                                                            <td><?php echo htmlspecialchars($product['categoria_nome'] ?? '-'); ?></td>
                                                            <td><?php echo htmlspecialchars($product['fornecedor_nome'] ?? '-'); ?></td>
                                                            <td>
                                                                <button class="btn btn-outline-primary btn-sm details-btn" 
                                                                        data-product-id="<?php echo $product['id']; ?>">Detalhes</button>
                                                                <button class="btn btn-outline-primary btn-sm edit-btn" 
                                                                        data-product-id="<?php echo $product['id']; ?>" 
                                                                        data-product-nome="<?php echo htmlspecialchars($product['nome']); ?>" 
                                                                        data-product-descricao="<?php echo htmlspecialchars($product['descricao']); ?>" 
                                                                        data-product-quantidade="<?php echo htmlspecialchars($product['quantidade']); ?>" 
                                                                        data-product-unidade="<?php echo htmlspecialchars($product['unidade_medida'] ?: 'Unidade'); ?>"
                                                                        data-product-fornecedor="<?php echo htmlspecialchars($product['fornecedor_nome'] ?? ''); ?>">Editar</button>
                                                                <button class="btn btn-outline-danger btn-sm remove-btn" 
                                                                        data-product-id="<?php echo $product['id']; ?>" 
                                                                        data-product-nome="<?php echo htmlspecialchars($product['nome']); ?>">Excluir</button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td>-</td>
                                                        <td>Nenhum produto associado</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="removeModal" tabindex="-1" role="dialog" aria-labelledby="removeModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="removeModalLabel">Confirmar Exclusão</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Tem certeza que deseja excluir o produto <strong id="product-nome"></strong>? Esta ação não pode ser desfeita.</p>
                                    <input type="hidden" id="product-id">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-danger" id="confirmRemove">Confirmar Exclusão</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detailsModalLabel">Detalhes do Produto</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <tr><th>Nome</th><td id="detalhe-nome"></td></tr>
                                        <tr><th>Descrição</th><td id="detalhe-descricao"></td></tr>
                                        <tr><th>Quantidade</th><td id="detalhe-quantidade"></td></tr>
                                        <tr><th>Unidade de Medida</th><td id="detalhe-unidade"></td></tr>
                                        <tr><th>Categoria</th><td id="detalhe-categoria"></td></tr>
                                        <tr><th>Fornecedor</th><td id="detalhe-fornecedor"></td></tr>
                                        <tr><th>Data Atualização</th><td id="detalhe-data-atualizacao"></td></tr>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Editar Produto</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="editProductForm">
                                        <div class="form-group">
                                            <label for="edit-nome">Nome</label>
                                            <input type="text" class="form-control" id="edit-nome" name="nome" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-descricao">Descrição</label>
                                            <textarea class="form-control" id="edit-descricao" name="descricao" rows="3" required></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-quantidade">Quantidade</label>
                                            <input type="number" class="form-control" id="edit-quantidade" name="quantidade" min="0" step="0.01" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-unidade">Unidade de Medida</label>
                                            <select class="form-control" id="edit-unidade" name="unidade_medida" required>
                                                <option value="Unidade">Unidade</option>
                                                <option value="Kg">Quilograma (Kg)</option>
                                                <option value="Gr">Grama (Gr)</option>
                                                <option value="L">Litro (L)</option>
                                                <option value="Ml">Mililitro (Ml)</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-fornecedor">Fornecedor</label>
                                            <select class="form-control" id="edit-fornecedor" name="fornecedor">
                                                <option value="">Nenhum fornecedor</option>
                                                <?php foreach ($fornecedores as $fornecedor): ?>
                                                    <option value="<?php echo $fornecedor['id']; ?>">
                                                        <?php echo htmlspecialchars($fornecedor['empresa']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <input type="hidden" id="edit-product-id" name="product_id">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-primary" id="confirmEdit">Salvar Alterações</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <?php include '../footer.php'; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
    
    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="../assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
    <script src="../assets/js/off-canvas.js"></script>
    <script src="../assets/js/misc.js"></script>
    <script src="../assets/js/settings.js"></script>
    <script src="../assets/js/todolist.js"></script>
    <script src="../assets/js/data-table.js"></script>

    <script>
        $(document).ready(function() {
            <?php if (!empty($products)): ?>
                $('#order-listing').DataTable();
            <?php endif; ?>

            $('.remove-btn').on('click', function() {
                var productId = $(this).data('product-id');
                var productNome = $(this).data('product-nome');
                $('#product-nome').text(productNome);
                $('#product-id').val(productId);
                $('#removeModal').modal('show');
            });

            $('#confirmRemove').on('click', function() {
                var productId = $('#product-id').val();
                var restauranteId = <?php echo json_encode($restaurante_id ?? 'null'); ?>;
                if (restauranteId === null) {
                    alert('Erro: Restaurante não identificado.');
                    return;
                }
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { restaurante_id: restauranteId, product_id: productId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#removeModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao remover produto: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });

            $('.details-btn').on('click', function() {
                var productId = $(this).data('product-id');
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { get_details: true, product_id: productId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            var data = response.data;
                            $('#detalhe-nome').text(data.nome || '-');
                            $('#detalhe-descricao').text(data.descricao || '-');
                            $('#detalhe-quantidade').text(data.quantidade || '-');
                            $('#detalhe-unidade').text(data.unidade_medida || 'Unidade');
                            $('#detalhe-categoria').text(data.categoria_nome || '-');
                            $('#detalhe-fornecedor').text(data.fornecedor_nome || '-');
                            $('#detalhe-data-atualizacao').text(data.data_atualizacao ? new Date(data.data_atualizacao).toLocaleString('pt-BR') : '-');
                            $('#detailsModal').modal('show');
                        } else {
                            alert('Erro ao carregar detalhes: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });

            $('.edit-btn').on('click', function() {
                var productId = $(this).data('product-id');
                
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { get_details: true, product_id: productId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            var data = response.data;
                            console.log('Dados do produto carregados:', data);
                            $('#edit-product-id').val(productId);
                            $('#edit-nome').val(data.nome);
                            $('#edit-descricao').val(data.descricao);
                            $('#edit-quantidade').val(data.quantidade);
                            $('#edit-unidade').val(data.unidade_medida || 'Unidade');
                            $('#edit-fornecedor').val(data.id_fornecedor || '');
                            $('#editModal').modal('show');
                        } else {
                            alert('Erro ao carregar dados para edição: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });

            $('#confirmEdit').on('click', function() {
                var productId = $('#edit-product-id').val();
                var novoNome = $('#edit-nome').val();
                var novaDescricao = $('#edit-descricao').val();
                var novaQuantidade = $('#edit-quantidade').val();
                var novaUnidade = $('#edit-unidade').val();
                var novoFornecedor = $('#edit-fornecedor').val();
                var restauranteId = <?php echo json_encode($restaurante_id ?? 'null'); ?>;

                console.log('Dados enviados para o backend:', {
                    product_id: productId,
                    nome: novoNome,
                    descricao: novaDescricao,
                    quantidade: novaQuantidade,
                    unidade_medida: novaUnidade,
                    fornecedor: novoFornecedor,
                    restaurante_id: restauranteId
                });

                if (restauranteId === null) {
                    alert('Erro: Restaurante não identificado.');
                    return;
                }

                if (!novoNome || !novaDescricao || !novaQuantidade || !novaUnidade) {
                    alert('Por favor, preencha todos os campos obrigatórios.');
                    return;
                }

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        edit_product: true,
                        product_id: productId,
                        nome: novoNome,
                        descricao: novaDescricao,
                        quantidade: novaQuantidade,
                        unidade_medida: novaUnidade,
                        fornecedor: novoFornecedor,
                        restaurante_id: restauranteId
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Resposta do servidor:', response);
                        if (response.status === 'success') {
                            $('#editModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao atualizar produto: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Erro AJAX:', status, error);
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });
        });
    </script>
</body>
</html>