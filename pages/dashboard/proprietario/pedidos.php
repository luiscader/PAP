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
$pedidos = [];

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
    if (isset($_POST['restaurante_id']) && isset($_POST['pedido_id']) && !isset($_POST['get_details']) && !isset($_POST['edit_pedido'])) {
        $restaurante_id = $_POST['restaurante_id'];
        $pedido_id = $_POST['pedido_id'];

        $sql = "DELETE FROM pedidos WHERE id_restaurante = ? AND id_pedido = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $restaurante_id, $pedido_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao remover pedido: ' . $conn->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
        }
        $conn->close();
        exit();
    } elseif (isset($_POST['get_details']) && isset($_POST['pedido_id'])) {
        $pedido_id = $_POST['pedido_id'];
        $sql = "SELECT p.id_pedido, p.id_mesa, p.id_prato, p.quantidade, p.data_pedido, p.status, p.preco_total, p.observacoes, pr.nome AS prato_nome 
                FROM pedidos p 
                LEFT JOIN pratos pr ON p.id_prato = pr.id 
                WHERE p.id_pedido = ? AND p.id_restaurante = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $pedido_id, $restaurante_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Pedido não encontrado']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
        }
        $conn->close();
        exit();
    } elseif (isset($_POST['edit_pedido']) && isset($_POST['pedido_id']) && isset($_POST['status'])) {
        $pedido_id = $_POST['pedido_id'];
        $status = $_POST['status'];
        $restaurante_id = $_POST['restaurante_id'];

        if (in_array($status, ['Pago', 'Cancelado'])) {
            $sql_select = "SELECT id_restaurante, id_mesa, id_prato, quantidade, data_pedido, status, preco_total 
                           FROM pedidos 
                           WHERE id_pedido = ? AND id_restaurante = ?";
            if ($stmt_select = $conn->prepare($sql_select)) {
                $stmt_select->bind_param("ii", $pedido_id, $restaurante_id);
                $stmt_select->execute();
                $result = $stmt_select->get_result();
                if ($row = $result->fetch_assoc()) {
                    $sql_insert = "INSERT INTO pedidos_arquivados (id, id_restaurante, id_mesa, id_prato, quantidade, id_pedido, data_pedido, status, preco_total) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    if ($stmt_insert = $conn->prepare($sql_insert)) {
                        $stmt_insert->bind_param("iiiiisssd", $row['id_restaurante'], $row['id_restaurante'], $row['id_mesa'], $row['id_prato'], $row['quantidade'], $pedido_id, $row['data_pedido'], $status, $row['preco_total']);
                        if ($stmt_insert->execute()) {
                            $sql_delete = "DELETE FROM pedidos WHERE id_pedido = ? AND id_restaurante = ?";
                            if ($stmt_delete = $conn->prepare($sql_delete)) {
                                $stmt_delete->bind_param("ii", $pedido_id, $restaurante_id);
                                if ($stmt_delete->execute()) {
                                    echo json_encode(['status' => 'success']);
                                } else {
                                    echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir pedido após arquivamento: ' . $conn->error]);
                                }
                                $stmt_delete->close();
                            }
                        } else {
                            echo json_encode(['status' => 'error', 'message' => 'Erro ao arquivar pedido: ' . $conn->error]);
                        }
                        $stmt_insert->close();
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Pedido não encontrado para arquivamento']);
                }
                $stmt_select->close();
            }
        } else {
            $sql = "UPDATE pedidos SET status = ? WHERE id_pedido = ? AND id_restaurante = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sii", $status, $pedido_id, $restaurante_id);
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar pedido: ' . $conn->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
            }
        }
        $conn->close();
        exit();
    }
}

if ($restaurante_id) {
    $sql = "SELECT p.id_pedido, p.id_mesa, p.id_prato, p.quantidade, p.data_pedido, p.status, p.preco_total, pr.nome AS prato_nome 
            FROM pedidos p 
            LEFT JOIN pratos pr ON p.id_prato = pr.id 
            WHERE p.id_restaurante = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $restaurante_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedidos = $result->fetch_all(MYSQLI_ASSOC);
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
    <title>Gestão de Pedidos - Restomate</title>
    
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
                        <h3 class="page-title">Gestão de Pedidos</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="pedidos.php">Pedidos</a></li>
                                <li class="breadcrumb-item" aria-current="page"><strong>Pedidos</strong></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title">Pedidos</h4>
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
                                                    <th>Mesa</th>
                                                    <th>Prato</th>
                                                    <th>Quantidade</th>
                                                    <th>Data do Pedido</th>
                                                    <th>Status</th>
                                                    <th>Total</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($pedidos)): ?>
                                                    <?php foreach ($pedidos as $pedido): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($pedido['id_pedido']); ?></td>
                                                            <td><?php echo htmlspecialchars($pedido['id_mesa']); ?></td>
                                                            <td><?php echo htmlspecialchars($pedido['prato_nome']); ?></td>
                                                            <td><?php echo htmlspecialchars($pedido['quantidade']); ?></td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                                                            <td>
                                                                <?php 
                                                                $badge_class = [
                                                                    'Pendente' => 'badge-warning',
                                                                    'Em Preparacao' => 'badge-info',
                                                                    'Pronto' => 'badge-primary',
                                                                    'Entregue' => 'badge-success',
                                                                    'Pago' => 'badge-success',
                                                                    'Cancelado' => 'badge-danger'
                                                                ][$pedido['status']] ?? 'badge-secondary';
                                                                ?>
                                                                <label class="badge <?php echo $badge_class; ?>">
                                                                    <?php echo htmlspecialchars($pedido['status']); ?>
                                                                </label>
                                                            </td>
                                                            <td><?php echo number_format($pedido['preco_total'], 2); ?>€</td>
                                                            <td>
                                                                <button class="btn btn-outline-primary btn-sm details-btn" 
                                                                        data-pedido-id="<?php echo $pedido['id_pedido']; ?>">Detalhes</button>
                                                                <button class="btn btn-outline-primary btn-sm edit-btn" 
                                                                        data-pedido-id="<?php echo $pedido['id_pedido']; ?>" 
                                                                        data-pedido-status="<?php echo htmlspecialchars($pedido['status']); ?>">Editar</button>
                                                                <button class="btn btn-outline-danger btn-sm remove-btn" 
                                                                        data-pedido-id="<?php echo $pedido['id_pedido']; ?>" 
                                                                        data-pedido-nome="<?php echo htmlspecialchars($pedido['id_pedido']); ?>">Excluir</button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td>Nenhum pedido associado</td>
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
                                    <p>Tem certeza que deseja excluir o pedido <strong id="pedido-nome"></strong>? Esta ação não pode ser desfeita.</p>
                                    <input type="hidden" id="pedido-id">
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
                                    <h5 class="modal-title" id="detailsModalLabel">Detalhes do Pedido</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <tr><th>ID</th><td id="detalhe-id"></td></tr>
                                        <tr><th>Mesa</th><td id="detalhe-mesa"></td></tr>
                                        <tr><th>Prato</th><td id="detalhe-prato"></td></tr>
                                        <tr><th>Quantidade</th><td id="detalhe-quantidade"></td></tr>
                                        <tr><th>Data do Pedido</th><td id="detalhe-data-pedido"></td></tr>
                                        <tr><th>Status</th><td id="detalhe-status"></td></tr>
                                        <tr><th>Total</th><td id="detalhe-total"></td></tr>
                                        <tr><th>Observações</th><td id="detalhe-observacoes"></td></tr>
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
                                    <h5 class="modal-title" id="editModalLabel">Editar Pedido</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="editPedidoForm">
                                        <div class="form-group">
                                            <label for="edit-status">Status</label>
                                            <select class="form-control" id="edit-status" name="status" required>
                                                <option value="Pendente">Pendente</option>
                                                <option value="Em Preparacao">Em Preparação</option>
                                                <option value="Pronto">Pronto</option>
                                                <option value="Entregue">Entregue</option>
                                                <option value="Pago">Pago</option>
                                                <option value="Cancelado">Cancelado</option>
                                            </select>
                                        </div>
                                        <input type="hidden" id="edit-pedido-id">
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
            <?php if (!empty($pedidos)): ?>
                $('#order-listing').DataTable();
            <?php endif; ?>

            $('.remove-btn').on('click', function() {
                var pedidoId = $(this).data('pedido-id');
                var pedidoNome = $(this).data('pedido-nome');

                $('#pedido-nome').text(pedidoId);
                $('#pedido-id').val(pedidoId);
                $('#removeModal').modal('show');
            });

            $('#confirmRemove').on('click', function() {
                var pedidoId = $('#pedido-id').val();
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
                        pedido_id: pedidoId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#removeModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao remover pedido: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });

            $('.details-btn').on('click', function() {
                var pedidoId = $(this).data('pedido-id');

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        get_details: true,
                        pedido_id: pedidoId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            var data = response.data;
                            $('#detalhe-id').text(data.id_pedido || '-');
                            $('#detalhe-mesa').text(data.id_mesa || '-');
                            $('#detalhe-prato').text(data.prato_nome || '-');
                            $('#detalhe-quantidade').text(data.quantidade || '-');
                            $('#detalhe-data-pedido').text(data.data_pedido ? new Date(data.data_pedido).toLocaleString('pt-BR') : '-');
                            $('#detalhe-status').text(data.status || '-');
                            $('#detalhe-total').text(data.preco_total ? Number(data.preco_total).toFixed(2) + '€' : '-');
                            $('#detalhe-observacoes').text(data.observacoes || '-');
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
                var pedidoId = $(this).data('pedido-id');
                var pedidoStatus = $(this).data('pedido-status');

                $('#edit-pedido-id').val(pedidoId);
                $('#edit-status').val(pedidoStatus);
                $('#editModal').modal('show');
            });

            $('#confirmEdit').on('click', function() {
                var pedidoId = $('#edit-pedido-id').val();
                var novoStatus = $('#edit-status').val();
                var restauranteId = <?php echo json_encode($restaurante_id ?? 'null'); ?>;

                if (restauranteId === null) {
                    alert('Erro: Restaurante não identificado.');
                    return;
                }

                if (!novoStatus) {
                    alert('Por favor, selecione um status.');
                    return;
                }

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        edit_pedido: true,
                        restaurante_id: restauranteId,
                        pedido_id: pedidoId,
                        status: novoStatus
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#editModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao atualizar pedido: ' + response.message);
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