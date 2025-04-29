<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../geral/login.php");
    exit();
}

$id_cliente = $_SESSION['id'];
$fornecedores = [];
$restaurante_id = null;


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
    if (isset($_POST['restaurante_id']) && isset($_POST['fornecedor_id']) && !isset($_POST['get_details'])) {
        $restaurante_id = $_POST['restaurante_id'];
        $fornecedor_id = $_POST['fornecedor_id'];

        $sql = "DELETE FROM restaurante_fornecedor WHERE id_restaurante = ? AND id_fornecedor = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $restaurante_id, $fornecedor_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao remover associação: ' . $conn->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
        }
        $conn->close();
        exit();
    } elseif (isset($_POST['get_details']) && isset($_POST['fornecedor_id'])) {
        $fornecedor_id = $_POST['fornecedor_id'];
        $sql = "SELECT empresa, email_empresa, telefone_empresa, nif_empresa, morada_sede, codigo_postal, distrito, pais, iban 
                FROM fornecedor WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $fornecedor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Fornecedor não encontrado']);
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
    $sql = "SELECT f.id, f.empresa, f.email_empresa, f.telefone_empresa 
            FROM fornecedor f
            JOIN restaurante_fornecedor rf ON f.id = rf.id_fornecedor
            WHERE rf.id_restaurante = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $restaurante_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $fornecedores = $result->fetch_all(MYSQLI_ASSOC);
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
    <title>Gestão de Fornecedores - Restomate</title>
    
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
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title">Fornecedores Associados</h4>
                            <a href="contratar_fornecedor.php" class="btn btn-primary">Contratar Fornecedor</a>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="order-listing" class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Empresa</th>
                                                <th>Email</th>
                                                <th>Telefone</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($fornecedores)): ?>
                                                <?php foreach($fornecedores as $row): ?>
                                                <tr>
                                                    <td><?= $row['id'] ?></td>
                                                    <td><?= htmlspecialchars($row['empresa']) ?></td>
                                                    <td><?= htmlspecialchars($row['email_empresa']) ?></td>
                                                    <td><?= htmlspecialchars($row['telefone_empresa']) ?></td>
                                                    <td>
                                                        <button class="btn btn-outline-primary btn-sm details-btn" 
                                                                data-fornecedor-id="<?= $row['id'] ?>">Detalhes</button>
                                                        <button class="btn btn-outline-danger btn-sm remove-btn" 
                                                                data-fornecedor-id="<?= $row['id'] ?>" 
                                                                data-fornecedor-nome="<?= htmlspecialchars($row['empresa']) ?>">Excluir</button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td>-</td>
                                                    <td>Nenhum fornecedor associado</td>
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

                <!-- Modal de Confirmação de Exclusão -->
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
                                <p>Tem certeza que deseja excluir a associação com o fornecedor <strong id="fornecedor-nome"></strong>? Esta ação não pode ser desfeita.</p>
                                <input type="hidden" id="fornecedor-id">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-danger" id="confirmRemove">Confirmar Exclusão</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal de Detalhes do Fornecedor -->
                <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">Detalhes do Fornecedor</h5>
                                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered">
                                    <tr><th>Empresa</th><td id="detalhe-empresa"></td></tr>
                                    <tr><th>NIF</th><td id="detalhe-nif"></td></tr>
                                    <tr><th>Email</th><td id="detalhe-email"></td></tr>
                                    <tr><th>Telefone</th><td id="detalhe-telefone"></td></tr>
                                    <tr><th>Morada</th><td id="detalhe-morada"></td></tr>
                                    <tr><th>Código Postal</th><td id="detalhe-codigo-postal"></td></tr>
                                    <tr><th>Distrito</th><td id="detalhe-distrito"></td></tr>
                                    <tr><th>País</th><td id="detalhe-pais"></td></tr>
                                    <tr><th>IBAN</th><td id="detalhe-iban"></td></tr>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <?php include '../footer.php'; ?>
        </div>
    </div>

    <!-- Dependências -->
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

            <?php if (!empty($fornecedores)): ?>
                $('#order-listing').DataTable();
            <?php endif; ?>

            $('.remove-btn').on('click', function() {
                var fornecedorId = $(this).data('fornecedor-id');
                var fornecedorNome = $(this).data('fornecedor-nome');

                $('#fornecedor-nome').text(fornecedorNome);
                $('#fornecedor-id').val(fornecedorId);
                $('#removeModal').modal('show');
            });

            $('#confirmRemove').on('click', function() {
                var fornecedorId = $('#fornecedor-id').val();
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
                        fornecedor_id: fornecedorId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#removeModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao remover associação: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });

            
            $('.details-btn').on('click', function() {
                var fornecedorId = $(this).data('fornecedor-id');

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        get_details: true,
                        fornecedor_id: fornecedorId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            var data = response.data;
                            $('#detalhe-empresa').text(data.empresa || '-');
                            $('#detalhe-nif').text(data.nif_empresa || '-');
                            $('#detalhe-email').text(data.email_empresa || '-');
                            $('#detalhe-telefone').text(data.telefone_empresa || '-');
                            $('#detalhe-morada').text(data.morada_sede || '-');
                            $('#detalhe-codigo-postal').text(data.codigo_postal || '-');
                            $('#detalhe-distrito').text(data.distrito || '-');
                            $('#detalhe-pais').text(data.pais || '-');
                            $('#detalhe-iban').text(data.iban || '-');
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
        });
    </script>
</body>
</html>