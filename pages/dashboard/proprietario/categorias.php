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
$categories = [];

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['restaurante_id']) && isset($_POST['category_id']) && !isset($_POST['get_details']) && !isset($_POST['edit_category'])) {
        $restaurante_id = $_POST['restaurante_id'];
        $category_id = $_POST['category_id'];

        $sql = "DELETE FROM categoria WHERE id_restaurante = ? AND id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $restaurante_id, $category_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao remover categoria: ' . $conn->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
        }
        $conn->close();
        exit();
    } elseif (isset($_POST['get_details']) && isset($_POST['category_id'])) {
        $category_id = $_POST['category_id'];
        $sql = "SELECT nome, descricao, data_criacao, data_atualizacao 
                FROM categoria 
                WHERE id = ? AND id_restaurante = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $category_id, $restaurante_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Categoria não encontrada']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
        }
        $conn->close();
        exit();
    } elseif (isset($_POST['edit_category']) && isset($_POST['category_id']) && isset($_POST['nome']) && isset($_POST['descricao'])) {
        $category_id = $_POST['category_id'];
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $restaurante_id = $_POST['restaurante_id'];

        $sql = "UPDATE categoria SET nome = ?, descricao = ?, data_atualizacao = NOW() WHERE id = ? AND id_restaurante = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssii", $nome, $descricao, $category_id, $restaurante_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar categoria: ' . $conn->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
        }
        $conn->close();
        exit();
    }
}

if ($restaurante_id) {
    $sql = "SELECT id, nome, descricao, data_criacao, data_atualizacao 
            FROM categoria 
            WHERE id_restaurante = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $restaurante_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);
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
    <title>Gestão de Categorias - Restomate</title>
    
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
                        <h3 class="page-title">Gestão de Categorias</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="categorias.php">Categorias</a></li>
                                <li class="breadcrumb-item" aria-current="page"><strong>Categorias</strong></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title">Categorias</h4>
                                <a href="criar_categoria.php" class="btn btn-primary">Criar Categoria</a>
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
                                                    <th>Data Criação</th>
                                                    <th>Data Atualização</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($categories)): ?>
                                                    <?php foreach ($categories as $category): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($category['id']); ?></td>
                                                            <td><?php echo htmlspecialchars($category['nome']); ?></td>
                                                            <td><?php echo htmlspecialchars($category['descricao']); ?></td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($category['data_criacao'])); ?></td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($category['data_atualizacao'])); ?></td>
                                                            <td>
                                                                <button class="btn btn-outline-primary btn-sm details-btn" 
                                                                        data-category-id="<?php echo $category['id']; ?>">Detalhes</button>
                                                                <button class="btn btn-outline-primary btn-sm edit-btn" 
                                                                        data-category-id="<?php echo $category['id']; ?>" 
                                                                        data-category-nome="<?php echo htmlspecialchars($category['nome']); ?>" 
                                                                        data-category-descricao="<?php echo htmlspecialchars($category['descricao']); ?>">Editar</button>
                                                                <button class="btn btn-outline-danger btn-sm remove-btn" 
                                                                        data-category-id="<?php echo $category['id']; ?>" 
                                                                        data-category-nome="<?php echo htmlspecialchars($category['nome']); ?>">Excluir</button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td>-</td>
                                                        <td>Nenhuma categoria associada</td>
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
                                    <p>Tem certeza que deseja excluir a categoria <strong id="category-nome"></strong>? Esta ação não pode ser desfeita.</p>
                                    <input type="hidden" id="category-id">
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
                                    <h5 class="modal-title" id="detailsModalLabel">Detalhes da Categoria</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <tr><th>Nome</th><td id="detalhe-nome"></td></tr>
                                        <tr><th>Descrição</th><td id="detalhe-descricao"></td></tr>
                                        <tr><th>Data Criação</th><td id="detalhe-data-criacao"></td></tr>
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
                                    <h5 class="modal-title" id="editModalLabel">Editar Categoria</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="editCategoryForm">
                                        <div class="form-group">
                                            <label for="edit-nome">Nome</label>
                                            <input type="text" class="form-control" id="edit-nome" name="nome" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-descricao">Descrição</label>
                                            <textarea class="form-control" id="edit-descricao" name="descricao" rows="3" required></textarea>
                                        </div>
                                        <input type="hidden" id="edit-category-id">
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
            <?php if (!empty($categories)): ?>
                $('#order-listing').DataTable();
            <?php endif; ?>

            $('.remove-btn').on('click', function() {
                var categoryId = $(this).data('category-id');
                var categoryNome = $(this).data('category-nome');

                $('#category-nome').text(categoryNome);
                $('#category-id').val(categoryId);
                $('#removeModal').modal('show');
            });

            $('#confirmRemove').on('click', function() {
                var categoryId = $('#category-id').val();
                var restauranteId = <?php echo json_encode($restaurante_id ?? 'null'); ?>;

                if (restauranteId === null) {
                    alert('Erro: Restaurante não identificado.');
                    return;
                }

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        restaurante_id: restauranteId,
                        category_id: categoryId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#removeModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao remover categoria: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });

            $('.details-btn').on('click', function() {
                var categoryId = $(this).data('category-id');

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        get_details: true,
                        category_id: categoryId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            var data = response.data;
                            $('#detalhe-nome').text(data.nome || '-');
                            $('#detalhe-descricao').text(data.descricao || '-');
                            $('#detalhe-data-criacao').text(data.data_criacao ? new Date(data.data_criacao).toLocaleString('pt-BR') : '-');
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
                var categoryId = $(this).data('category-id');
                var categoryNome = $(this).data('category-nome');
                var categoryDescricao = $(this).data('category-descricao');

                $('#edit-category-id').val(categoryId);
                $('#edit-nome').val(categoryNome);
                $('#edit-descricao').val(categoryDescricao);
                $('#editModal').modal('show');
            });

            $('#confirmEdit').on('click', function() {
                var categoryId = $('#edit-category-id').val();
                var novoNome = $('#edit-nome').val();
                var novaDescricao = $('#edit-descricao').val();
                var restauranteId = <?php echo json_encode($restaurante_id ?? 'null'); ?>;

                if (restauranteId === null) {
                    alert('Erro: Restaurante não identificado.');
                    return;
                }

                if (!novoNome || !novaDescricao) {
                    alert('Por favor, preencha todos os campos.');
                    return;
                }

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        edit_category: true,
                        restaurante_id: restauranteId,
                        category_id: categoryId,
                        nome: novoNome,
                        descricao: novaDescricao
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#editModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao atualizar categoria: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });
        });
    </script>
</body>
</html>