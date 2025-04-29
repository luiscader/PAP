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
$funcionarios = [];

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
    if (isset($_POST['restaurante_id']) && isset($_POST['funcionario_id']) && !isset($_POST['get_details']) && !isset($_POST['edit_cargo'])) {
        $restaurante_id = $_POST['restaurante_id'];
        $funcionario_id = $_POST['funcionario_id'];

        $conn->begin_transaction();

        try {

            $sql_delete = "DELETE FROM funcionarios WHERE id_restaurante = ? AND id_utilizador = ?";
            if ($stmt_delete = $conn->prepare($sql_delete)) {
                $stmt_delete->bind_param("ii", $restaurante_id, $funcionario_id);
                if (!$stmt_delete->execute()) {
                    throw new Exception('Erro ao executar a exclusão: ' . $conn->error);
                }
                $stmt_delete->close();
            } else {
                throw new Exception('Erro na preparação da query de exclusão: ' . $conn->error);
            }

            $sql_update = "UPDATE utilizador SET tipo = 'cliente' WHERE id = ?";
            if ($stmt_update = $conn->prepare($sql_update)) {
                $stmt_update->bind_param("i", $funcionario_id);
                if (!$stmt_update->execute()) {
                    throw new Exception('Erro ao executar a atualização: ' . $conn->error);
                }
                $stmt_update->close();
            } else {
                throw new Exception('Erro na preparação da query de atualização: ' . $conn->error);
            }

            $conn->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {

            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        $conn->close();
        exit();
    } elseif (isset($_POST['get_details']) && isset($_POST['funcionario_id'])) {
        $funcionario_id = $_POST['funcionario_id'];
        $sql = "SELECT u.nome, u.email, u.telefone, u.nif, u.data_nascimento, u.pais, u.distrito, u.morada, u.codigo_postal, f.cargo 
                FROM utilizador u
                INNER JOIN funcionarios f ON u.id = f.id_utilizador
                WHERE u.id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $funcionario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Funcionário não encontrado']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
        }
        $conn->close();
        exit();
    } elseif (isset($_POST['edit_cargo']) && isset($_POST['funcionario_id']) && isset($_POST['cargo'])) {
        $funcionario_id = $_POST['funcionario_id'];
        $cargo = $_POST['cargo'];
        $restaurante_id = $_POST['restaurante_id'];

        $sql = "UPDATE funcionarios SET cargo = ? WHERE id_utilizador = ? AND id_restaurante = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sii", $cargo, $funcionario_id, $restaurante_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar cargo: ' . $conn->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro na preparação da query: ' . $conn->error]);
        }
        $conn->close();
        exit();
    }
}

if ($restaurante_id && $tipo_usuario === 'proprietario') {
    $sql = "SELECT u.id, u.nome, u.email, u.telefone, u.nif, f.cargo 
            FROM funcionarios f
            INNER JOIN utilizador u ON f.id_utilizador = u.id
            WHERE f.id_restaurante = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $restaurante_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $funcionarios = $result->fetch_all(MYSQLI_ASSOC);
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
    <title>Gestão de Funcionários - Restomate</title>

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
                    <h3 class="page-title">Gestão de Funcionários</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="funcionarios.php">Funcionários</a></li>
                            <li class="breadcrumb-item" aria-current="page"><strong>Funcionários</strong></li>
                        </ol>
                    </nav>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title">Funcionários</h4>
                            <a href="contratar_funcionario.php" class="btn btn-primary">Contratar Funcionário</a>
                        </div>
                        <?php if ($message): ?>
                            <div class="alert alert-warning"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="order-listing" class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nome</th>
                                                <th>Email</th>
                                                <th>Telefone</th>
                                                <th>NIF</th>
                                                <th>Cargo</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($funcionarios)): ?>
                                                <?php foreach($funcionarios as $row): ?>
                                                <tr>
                                                    <td><?= $row['id'] ?></td>
                                                    <td><?= htmlspecialchars($row['nome']) ?></td>
                                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                                    <td><?= htmlspecialchars($row['telefone']) ?></td>
                                                    <td><?= htmlspecialchars($row['nif']) ?></td>
                                                    <td>
                                                        <?php 
                                                        $badge_class = [
                                                            'Gerente' => 'badge-danger',
                                                            'Chefe de Cozinha' => 'badge-warning',
                                                            'Cozinheiro' => 'badge-success',
                                                            'Ajudante de Cozinha' => 'badge-info',
                                                            'Empregado de Mesa' => 'badge-primary'
                                                        ][$row['cargo']] ?? 'badge-secondary';
                                                        ?>
                                                        <label class="badge <?= $badge_class ?>">
                                                            <?= $row['cargo'] ?>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-outline-primary btn-sm details-btn" 
                                                                data-funcionario-id="<?= $row['id'] ?>">Detalhes</button>
                                                        <button class="btn btn-outline-primary btn-sm edit-btn" 
                                                                data-funcionario-id="<?= $row['id'] ?>" 
                                                                data-funcionario-cargo="<?= htmlspecialchars($row['cargo']) ?>">Editar</button>
                                                        <button class="btn btn-outline-danger btn-sm remove-btn" 
                                                                data-funcionario-id="<?= $row['id'] ?>" 
                                                                data-funcionario-nome="<?= htmlspecialchars($row['nome']) ?>">Excluir</button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td>-</td>
                                                    <td>Nenhum funcionário associado</td>
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
                                <p>Tem certeza que deseja excluir o funcionário <strong id="funcionario-nome"></strong>? Esta ação não pode ser desfeita.</p>
                                <input type="hidden" id="funcionario-id">
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
                                <h5 class="modal-title" id="detailsModalLabel">Detalhes do Funcionário</h5>
                                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered">
                                    <tr><th>Nome</th><td id="detalhe-nome"></td></tr>
                                    <tr><th>Email</th><td id="detalhe-email"></td></tr>
                                    <tr><th>Telefone</th><td id="detalhe-telefone"></td></tr>
                                    <tr><th>NIF</th><td id="detalhe-nif"></td></tr>
                                    <tr><th>Data de Nascimento</th><td id="detalhe-data-nascimento"></td></tr>
                                    <tr><th>Morada</th><td id="detalhe-morada"></td></tr>
                                    <tr><th>Código Postal</th><td id="detalhe-codigo-postal"></td></tr>
                                    <tr><th>Distrito</th><td id="detalhe-distrito"></td></tr>
                                    <tr><th>País</th><td id="detalhe-pais"></td></tr>
                                    <tr><th>Cargo</th><td id="detalhe-cargo"></td></tr>
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
                                <h5 class="modal-title" id="editModalLabel">Editar Cargo do Funcionário</h5>
                                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="editCargoForm">
                                    <div class="form-group">
                                        <label for="edit-cargo">Cargo</label>
                                        <select class="form-control" id="edit-cargo" name="cargo" required>
                                            <option value="Gerente">Gerente</option>
                                            <option value="Chefe de Cozinha">Chefe de Cozinha</option>
                                            <option value="Cozinheiro">Cozinheiro</option>
                                            <option value="Ajudante de Cozinha">Ajudante de Cozinha</option>
                                            <option value="Empregado de Mesa">Empregado de Mesa</option>
                                        </select>
                                    </div>
                                    <input type="hidden" id="edit-funcionario-id">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="../assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
    <script src="../assets/js/off-canvas.js"></script>
    <script src="../assets/js/misc.js"></script>
    <script src="../assets/js/settings.js"></script>
    <script src="../assets/js/data-table.js"></script>

    <script>
        $(document).ready(function() {

            <?php if (!empty($funcionarios)): ?>
                $('#order-listing').DataTable();
            <?php endif; ?>

            $('.remove-btn').on('click', function() {
                var funcionarioId = $(this).data('funcionario-id');
                var funcionarioNome = $(this).data('funcionario-nome');

                $('#funcionario-nome').text(funcionarioNome);
                $('#funcionario-id').val(funcionarioId);
                $('#removeModal').modal('show');
            });

            $('#confirmRemove').on('click', function() {
                var funcionarioId = $('#funcionario-id').val();
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
                        funcionario_id: funcionarioId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#removeModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao remover funcionário: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });

            $('.details-btn').on('click', function() {
                var funcionarioId = $(this).data('funcionario-id');

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        get_details: true,
                        funcionario_id: funcionarioId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            var data = response.data;
                            $('#detalhe-nome').text(data.nome || '-');
                            $('#detalhe-email').text(data.email || '-');
                            $('#detalhe-telefone').text(data.telefone || '-');
                            $('#detalhe-nif').text(data.nif || '-');
                            $('#detalhe-data-nascimento').text(data.data_nascimento || '-');
                            $('#detalhe-morada').text(data.morada || '-');
                            $('#detalhe-codigo-postal').text(data.codigo_postal || '-');
                            $('#detalhe-distrito').text(data.distrito || '-');
                            $('#detalhe-pais').text(data.pais || '-');
                            $('#detalhe-cargo').text(data.cargo || '-');
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
                var funcionarioId = $(this).data('funcionario-id');
                var cargoAtual = $(this).data('funcionario-cargo');

                $('#edit-funcionario-id').val(funcionarioId);
                $('#edit-cargo').val(cargoAtual);
                $('#editModal').modal('show');
            });

            $('#confirmEdit').on('click', function() {
                var funcionarioId = $('#edit-funcionario-id').val();
                var novoCargo = $('#edit-cargo').val();
                var restauranteId = <?php echo json_encode($restaurante_id ?? 'null'); ?>;

                if (restauranteId === null) {
                    alert('Erro: Restaurante não identificado.');
                    return;
                }

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        edit_cargo: true,
                        restaurante_id: restauranteId,
                        funcionario_id: funcionarioId,
                        cargo: novoCargo
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#editModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao atualizar cargo: ' + response.message);
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