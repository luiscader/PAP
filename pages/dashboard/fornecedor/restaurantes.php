<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();


if (!isset($_SESSION['id'])) {
    header("Location: ../../geral/login.php");
    exit();
}

$id_usuario = $_SESSION['id'];
$restaurantes = [];


$sql = "SELECT tipo, id_fornecedor FROM utilizador WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tipo_usuario = $row['tipo'];
        $id_fornecedor = $row['id_fornecedor'];
    } else {
        echo "Usuário não encontrado.";
        exit();
    }
    $stmt->close();
} else {
    echo "Erro na preparação da consulta.";
    exit();
}

if ($tipo_usuario !== 'fornecedor') {
    $_SESSION['message'] = "Acesso negado. Esta página é exclusiva para fornecedores.";
    header("Location: ../../geral/index.php");
    exit();
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
    } elseif (isset($_POST['get_details']) && isset($_POST['restaurante_id'])) {
        $restaurante_id = $_POST['restaurante_id'];
        $sql = "SELECT nome_empresa, nif, morada, codigo_postal, distrito, pais, telefone, email_contato, capacidade 
                FROM restaurante WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $restaurante_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Restaurante não encontrado']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
        }
        $conn->close();
        exit();
    }
}

$sql = "SELECT id, nome, email, senha, tipo FROM utilizador WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $nome, $email, $senha, $tipo);
    if ($stmt->num_rows > 0) {
        $stmt->fetch();
    } else {
        echo "Utilizador não encontrado.";
        exit();
    }
    $stmt->close();
}

if ($id_fornecedor) {
    $sql = "SELECT r.id, r.nome_empresa, r.email_contato, r.telefone 
            FROM restaurante r
            JOIN restaurante_fornecedor rf ON r.id = rf.id_restaurante
            WHERE rf.id_fornecedor = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_fornecedor);
        $stmt->execute();
        $result = $stmt->get_result();
        $restaurantes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} else {
    echo "Nenhum fornecedor associado a este utilizador.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gestão de Restaurantes - Restomate</title>
    
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
                        <h4 class="card-title">Restaurantes Associados</h4>

                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="order-listing" class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Restaurante</th>
                                                <th>Email</th>
                                                <th>Telefone</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($restaurantes)): ?>
                                                <?php foreach($restaurantes as $row): ?>
                                                <tr>
                                                    <td><?= $row['id'] ?></td>
                                                    <td><?= htmlspecialchars($row['nome_empresa']) ?></td>
                                                    <td><?= htmlspecialchars($row['email_contato']) ?></td>
                                                    <td><?= htmlspecialchars($row['telefone']) ?></td>
                                                    <td>
                                                        <button class="btn btn-outline-primary btn-sm details-btn" 
                                                                data-restaurante-id="<?= $row['id'] ?>">Detalhes</button>
                                                        <button class="btn btn-outline-danger btn-sm remove-btn" 
                                                                data-restaurante-id="<?= $row['id'] ?>" 
                                                                data-restaurante-nome="<?= htmlspecialchars($row['nome_empresa']) ?>">Remover</button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td>-</td>
                                                    <td>Nenhum restaurante associado</td>
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
                                <h5 class="modal-title" id="removeModalLabel">Confirmar Remoção</h5>
                                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Tem certeza que deseja remover a associação com o restaurante <strong id="restaurante-nome"></strong>? Esta ação não pode ser desfeita.</p>
                                <input type="hidden" id="restaurante-id">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-danger" id="confirmRemove">Confirmar Remoção</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">Detalhes do Restaurante</h5>
                                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered">
                                    <tr><th>Nome</th><td id="detalhe-nome"></td></tr>
                                    <tr><th>NIF</th><td id="detalhe-nif"></td></tr>
                                    <tr><th>Email</th><td id="detalhe-email"></td></tr>
                                    <tr><th>Telefone</th><td id="detalhe-telefone"></td></tr>
                                    <tr><th>Morada</th><td id="detalhe-morada"></td></tr>
                                    <tr><th>Código Postal</th><td id="detalhe-codigo-postal"></td></tr>
                                    <tr><th>Distrito</th><td id="detalhe-distrito"></td></tr>
                                    <tr><th>País</th><td id="detalhe-pais"></td></tr>
                                    <tr><th>Capacidade</th><td id="detalhe-capacidade"></td></tr>
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

            <?php if (!empty($restaurantes)): ?>
                $('#order-listing').DataTable();
            <?php endif; ?>

            $('.remove-btn').on('click', function() {
                var restauranteId = $(this).data('restaurante-id');
                var restauranteNome = $(this).data('restaurante-nome');

                $('#restaurante-nome').text(restauranteNome);
                $('#restaurante-id').val(restauranteId);
                $('#removeModal').modal('show');
            });

            $('#confirmRemove').on('click', function() {
                var restauranteId = $('#restaurante-id').val();
                var fornecedorId = <?php echo json_encode($fornecedor_id ?? 'null'); ?>;

                if (fornecedorId === null) {
                    alert('Erro: Fornecedor não identificado.');
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
                var restauranteId = $(this).data('restaurante-id');

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        get_details: true,
                        restaurante_id: restauranteId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            var data = response.data;
                            $('#detalhe-nome').text(data.nome_empresa || '-');
                            $('#detalhe-nif').text(data.nif || '-');
                            $('#detalhe-email').text(data.email_contato || '-');
                            $('#detalhe-telefone').text(data.telefone || '-');
                            $('#detalhe-morada').text(data.morada || '-');
                            $('#detalhe-codigo-postal').text(data.codigo_postal || '-');
                            $('#detalhe-distrito').text(data.distrito || '-');
                            $('#detalhe-pais').text(data.pais || '-');
                            $('#detalhe-capacidade').text(data.capacidade || '-');
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