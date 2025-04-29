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
$pedidos_arquivados = [];

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
    header('Content-Type: application/json');

    if (isset($_POST['get_details']) && isset($_POST['pedido_id'])) {
        $pedido_id = $_POST['pedido_id'];
        $sql = "SELECT pa.id, pa.id_mesa, pa.id_prato, pa.quantidade, pa.data_pedido, pa.status, pa.preco_total, pa.id_pedido, pr.nome AS prato_nome 
                FROM pedidos_arquivados pa 
                LEFT JOIN pratos pr ON pa.id_prato = pr.id 
                WHERE pa.id_pedido = ? AND pa.id_restaurante = ? AND pa.status IN ('Pago', 'Cancelado')";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $pedido_id, $restaurante_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Pedido arquivado não encontrado']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
        }
        $conn->close();
        exit();
    } elseif (isset($_POST['recover_pedido']) && isset($_POST['pedido_id'])) {
        $pedido_id = $_POST['pedido_id'];

        $sql_select = "SELECT id_restaurante, id_mesa, id_prato, quantidade, data_pedido, preco_total, id_pedido 
                       FROM pedidos_arquivados 
                       WHERE id_pedido = ? AND id_restaurante = ?";
        if ($stmt_select = $conn->prepare($sql_select)) {
            $stmt_select->bind_param("ii", $pedido_id, $restaurante_id);
            $stmt_select->execute();
            $result = $stmt_select->get_result();
            if ($row = $result->fetch_assoc()) {
                $sql_check = "SELECT id_pedido FROM pedidos WHERE id_pedido = ?";
                if ($stmt_check = $conn->prepare($sql_check)) {
                    $stmt_check->bind_param("i", $pedido_id);
                    $stmt_check->execute();
                    if ($stmt_check->get_result()->num_rows > 0) {
                        echo json_encode(['status' => 'error', 'message' => 'O ID do pedido já existe na tabela pedidos']);
                        $stmt_check->close();
                        $conn->close();
                        exit();
                    }
                    $stmt_check->close();
                }

                $sql_insert = "INSERT INTO pedidos (id_pedido, id_restaurante, id_mesa, id_prato, quantidade, data_pedido, status, preco_total) 
                               VALUES (?, ?, ?, ?, ?, ?, 'Pendente', ?)";
                if ($stmt_insert = $conn->prepare($sql_insert)) {
                    $stmt_insert->bind_param("iiiiisd", $row['id_pedido'], $row['id_restaurante'], $row['id_mesa'], $row['id_prato'], $row['quantidade'], $row['data_pedido'], $row['preco_total']);
                    if ($stmt_insert->execute()) {
                        $sql_delete = "DELETE FROM pedidos_arquivados WHERE id_pedido = ? AND id_restaurante = ?";
                        if ($stmt_delete = $conn->prepare($sql_delete)) {
                            $stmt_delete->bind_param("ii", $pedido_id, $restaurante_id);
                            if ($stmt_delete->execute()) {
                                echo json_encode(['status' => 'success']);
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir pedido arquivado: ' . $conn->error]);
                            }
                            $stmt_delete->close();
                        } else {
                            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query de exclusão: ' . $conn->error]);
                        }
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Erro ao recuperar pedido: ' . $conn->error]);
                    }
                    $stmt_insert->close();
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query de inserção: ' . $conn->error]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Pedido arquivado não encontrado para recuperação']);
            }
            $stmt_select->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query de seleção: ' . $conn->error]);
        }
        $conn->close();
        exit();
    }
}

if ($restaurante_id) {
    $sql = "SELECT pa.id, pa.id_mesa, pa.id_prato, pa.quantidade, pa.data_pedido, pa.status, pa.preco_total, pa.id_pedido, pr.nome AS prato_nome 
            FROM pedidos_arquivados pa 
            LEFT JOIN pratos pr ON pa.id_prato = pr.id 
            WHERE pa.id_restaurante = ? AND pa.status IN ('Pago', 'Cancelado')";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $restaurante_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedidos_arquivados = $result->fetch_all(MYSQLI_ASSOC);
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
    <title>Histórico de Pedidos - Restomate</title>
    
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
                        <h3 class="page-title">Histórico de Pedidos</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="historico_pedidos.php">Histórico</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><strong>Pedidos Arquivados</strong></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title">Pedidos Arquivados</h4>
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
                                                    <th>ID Pedido</th>
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
                                                <?php if (!empty($pedidos_arquivados)): ?>
                                                    <?php foreach ($pedidos_arquivados as $pedido): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($pedido['id_pedido']); ?></td>
                                                            <td><?php echo htmlspecialchars($pedido['id_mesa']); ?></td>
                                                            <td><?php echo htmlspecialchars($pedido['prato_nome']); ?></td>
                                                            <td><?php echo htmlspecialchars($pedido['quantidade']); ?></td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                                                            <td>
                                                                <?php 
                                                                $badge_class = [
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
                                                                <button class="btn btn-outline-success btn-sm recover-btn" 
                                                                        data-pedido-id="<?php echo $pedido['id_pedido']; ?>" 
                                                                        data-pedido-nome="<?php echo htmlspecialchars($pedido['id_pedido']); ?>">Recuperar</button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td>Nenhum pedido arquivado</td>
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

                    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detailsModalLabel">Detalhes do Pedido Arquivado</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <tr><th>ID Pedido</th><td id="detalhe-id-pedido"></td></tr>
                                        <tr><th>Mesa</th><td id="detalhe-mesa"></td></tr>
                                        <tr><th>Prato</th><td id="detalhe-prato"></td></tr>
                                        <tr><th>Quantidade</th><td id="detalhe-quantidade"></td></tr>
                                        <tr><th>Data do Pedido</th><td id="detalhe-data-pedido"></td></tr>
                                        <tr><th>Status</th><td id="detalhe-status"></td></tr>
                                        <tr><th>Total</th><td id="detalhe-total"></td></tr>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="recoverModal" tabindex="-1" role="dialog" aria-labelledby="recoverModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="recoverModalLabel">Confirmar Recuperação</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Tem certeza que deseja recuperar o pedido <strong id="pedido-nome"></strong>? Ele será movido para a lista de pedidos ativos com status "Pendente".</p>
                                    <input type="hidden" id="pedido-id">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-success" id="confirmRecover">Confirmar Recuperação</button>
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
            <?php if (!empty($pedidos_arquivados)): ?>
                $('#order-listing').DataTable();
            <?php endif; ?>

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
                            $('#detalhe-id-pedido').text(data.id_pedido || '-');
                            $('#detalhe-mesa').text(data.id_mesa || '-');
                            $('#detalhe-prato').text(data.prato_nome || '-');
                            $('#detalhe-quantidade').text(data.quantidade || '-');
                            $('#detalhe-data-pedido').text(data.data_pedido ? new Date(data.data_pedido).toLocaleString('pt-BR') : '-');
                            $('#detalhe-status').text(data.status || '-');
                            $('#detalhe-total').text(data.preco_total ? Number(data.preco_total).toFixed(2) + '€' : '-');
                            $('#detailsModal').modal('show');
                        } else {
                            alert('Erro ao carregar detalhes: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Erro AJAX:', xhr.responseText);
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });

            $('.recover-btn').on('click', function() {
                var pedidoId = $(this).data('pedido-id');
                var pedidoNome = $(this).data('pedido-nome');

                $('#pedido-nome').text(pedidoNome);
                $('#pedido-id').val(pedidoId);
                $('#recoverModal').modal('show');
            });

            $('#confirmRecover').on('click', function() {
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
                        recover_pedido: true,
                        pedido_id: pedidoId
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Resposta do servidor:', response);
                        if (response.status === 'success') {
                            $('#recoverModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao recuperar pedido: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Erro AJAX:', xhr.responseText);
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });
        });
    </script>
</body>
</html>