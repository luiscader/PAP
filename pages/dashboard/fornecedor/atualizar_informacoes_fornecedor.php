<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();


if (!isset($_SESSION['id'])) {
    header("Location: ../../geral/login.php");
    exit();
}


$id_usuario = $_SESSION['id'];
$message = '';


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

if (!$id_fornecedor) {
    $_SESSION['message'] = "Este usuário não está associado a um fornecedor.";
    $fornecedor_data = null;
} else {
    $fornecedor_data = null;
    $sql = "SELECT * FROM fornecedor WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_fornecedor);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $fornecedor_data = $result->fetch_assoc();
        } else {
            $_SESSION['message'] = "Fornecedor não encontrado.";
        }
        $stmt->close();
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


if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id_fornecedor) {
    $empresa = $_POST['empresa'];
    $email_empresa = $_POST['email_empresa'];
    $telefone_empresa = $_POST['telefone_empresa'];
    $nif_empresa = $_POST['nif_empresa'];
    $morada_sede = $_POST['morada_sede'];
    $codigo_postal = $_POST['codigo_postal'];
    $distrito = $_POST['distrito'];
    $pais = $_POST['pais'];
    $iban = $_POST['iban'];

    $sql = "UPDATE fornecedor SET 
                empresa = ?, 
                email_empresa = ?, 
                telefone_empresa = ?, 
                nif_empresa = ?, 
                morada_sede = ?, 
                codigo_postal = ?, 
                distrito = ?, 
                pais = ?, 
                iban = ? 
            WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssssssi", 
            $empresa, 
            $email_empresa, 
            $telefone_empresa, 
            $nif_empresa, 
            $morada_sede, 
            $codigo_postal, 
            $distrito, 
            $pais, 
            $iban, 
            $id_fornecedor
        );

        if ($stmt->execute()) {
            $_SESSION['message'] = "Informações do fornecedor atualizadas com sucesso!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['message'] = "Erro ao atualizar as informações do fornecedor: " . $conn->error;
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Erro na preparação da consulta.";
    }
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>restomate Admin</title>
    <link rel="stylesheet" href="../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
    
</head>
<body class="sidebar-fixed">
    <div class="container-scroller">
        <?php include 'sidebar.php' ?>
        <div class="page-body-wrapper">
            <?php include 'navbar.php' ?>
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title">Informações do Fornecedor</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active">Fornecedor</li>
                                <li class="breadcrumb-item" aria-current="page"><strong>Atualizar Informações</strong></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="row">
                        <div class="col-12 grid-margin">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Detalhes do Fornecedor</h4>
                                    <?php if ($fornecedor_data): ?>
                                        <form class="form-sample" method="POST">
                                            <p class="card-description">Informações do Fornecedor</p>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Nome da Empresa</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" name="empresa" value="<?php echo htmlspecialchars($fornecedor_data['empresa']); ?>" required />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">NIF</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" name="nif_empresa" value="<?php echo htmlspecialchars($fornecedor_data['nif_empresa']); ?>" required />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="card-description">Informações de Contato</p>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Telefone</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" name="telefone_empresa" value="<?php echo htmlspecialchars($fornecedor_data['telefone_empresa'] ?? ''); ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Email</label>
                                                        <div class="col-sm-9">
                                                            <input type="email" class="form-control" name="email_empresa" value="<?php echo htmlspecialchars($fornecedor_data['email_empresa'] ?? ''); ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="card-description">Morada</p>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Morada da Sede</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" name="morada_sede" value="<?php echo htmlspecialchars($fornecedor_data['morada_sede'] ?? ''); ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Código Postal</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" name="codigo_postal" value="<?php echo htmlspecialchars($fornecedor_data['codigo_postal'] ?? ''); ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Distrito</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" name="distrito" value="<?php echo htmlspecialchars($fornecedor_data['distrito'] ?? ''); ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">País</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" name="pais" value="<?php echo htmlspecialchars($fornecedor_data['pais'] ?? ''); ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="card-description">Informações Bancárias</p>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">IBAN</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" name="iban" value="<?php echo htmlspecialchars($fornecedor_data['iban'] ?? ''); ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if (!empty($message)): ?>
                                                <div class="alert <?php echo strpos($message, 'sucesso') !== false ? 'alert-success' : 'alert-danger'; ?>">
                                                    <?php echo $message; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="row mt-4">
                                                <div class="col-12 text-center">
                                                    <button type="submit" class="btn btn-primary">Atualizar Informações</button>
                                                </div>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <div class="alert alert-danger">
                                            <?php echo $message ?: "Nenhum fornecedor associado a este usuário."; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include '../footer.php'; ?>
            </div>
        </div>
    </div>

    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../assets/js/off-canvas.js"></script>
    <script src="../assets/js/misc.js"></script>
    <script src="../assets/js/settings.js"></script>
    <script src="../assets/js/todolist.js"></script>
    <script>
        window.setTimeout(function() {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>