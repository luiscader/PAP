<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../geral/login.php");
    exit();
}

$id_cliente = $_SESSION['id'];


$sql = "SELECT tipo FROM Utilizador WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $stmt->bind_result($tipo);
    $stmt->fetch();
    $stmt->close();
    
    if ($tipo !== 'proprietario') {
        header("Location: ../../geral/index.php");
        exit();
    }
} else {
    die("Erro na verificação do tipo de usuário: " . $conn->error);
}

$message = '';

$restaurant_data = null;
$sql = "SELECT * FROM restaurante WHERE id_proprietario = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $restaurant_data = $result->fetch_assoc();
    } else {
        $_SESSION['message'] = "Restaurante não encontrado.";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['delete_image'])) {
    $nome_empresa = $_POST['nome_empresa'];
    $nif = $_POST['nif'];
    $designacao_legal = $_POST['designacao_legal'];
    $capacidade = $_POST['capacidade'];
    $telefone = $_POST['telefone'];
    $email_contato = $_POST['email_contato'];
    $numero_contato = $_POST['numero_contato'];
    $intervalo_precos = $_POST['intervalo_precos'];
    $morada = $_POST['morada'];
    $codigo_postal = $_POST['codigo_postal'];
    $distrito = $_POST['distrito'];
    $pais = $_POST['pais'];
    $nome_banco = $_POST['nome_banco'];
    $iban = $_POST['iban'];
    $titular_conta = $_POST['titular_conta'];

    $sql = "UPDATE restaurante SET 
                nome_empresa = ?, 
                nif = ?, 
                designacao_legal = ?, 
                capacidade = ?, 
                telefone = ?, 
                email_contato = ?, 
                numero_contato = ?, 
                intervalo_precos = ?, 
                morada = ?, 
                codigo_postal = ?, 
                distrito = ?, 
                pais = ?, 
                nome_banco = ?, 
                iban = ?, 
                titular_conta = ? 
            WHERE id_proprietario = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssssssssssssi", 
            $nome_empresa, 
            $nif, 
            $designacao_legal, 
            $capacidade, 
            $telefone, 
            $email_contato, 
            $numero_contato, 
            $intervalo_precos, 
            $morada, 
            $codigo_postal, 
            $distrito, 
            $pais, 
            $nome_banco, 
            $iban, 
            $titular_conta, 
            $id_cliente
        );

        if ($stmt->execute()) {
            $_SESSION['message'] = "Informações do restaurante atualizadas com sucesso!";
        } else {
            $_SESSION['message'] = "Erro ao atualizar as informações do restaurante: " . $conn->error;
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Erro na preparação da consulta.";
    }

    if (isset($_FILES['restaurant_image']) && !empty($_FILES['restaurant_image']['name'][0])) {
        $targetDirServer = $_SERVER['DOCUMENT_ROOT'] . "/PAP/geral/uploads/";
        $targetDirWeb = "/PAP/geral/uploads/";
        
        if (!file_exists($targetDirServer)) {
            mkdir($targetDirServer, 0777, true);
        }
    
        $totalFiles = count($_FILES['restaurant_image']['name']);
        $successCount = 0;
        $errorMessages = [];
    
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($_FILES['restaurant_image']['error'][$i] == UPLOAD_ERR_NO_FILE) {
                continue; 
            }
    
            $fileName = basename($_FILES["restaurant_image"]["name"][$i]);
            $uniqueName = time() . '_' . $i . '_' . $fileName;
            $targetFileServer = $targetDirServer . $uniqueName;
            $targetFileWeb = $targetDirWeb . $uniqueName;
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($targetFileServer, PATHINFO_EXTENSION));
    
            
            error_log("Processing file $i: " . print_r($_FILES['restaurant_image'], true));
    
            if ($_FILES["restaurant_image"]["error"][$i] !== UPLOAD_ERR_OK) {
                $uploadOk = 0;
                switch ($_FILES["restaurant_image"]["error"][$i]) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorMessages[] = "O ficheiro '$fileName' é muito grande. (Apenas até 5MB)";
                        break;
                    default:
                        $errorMessages[] = "Erro ao carregar o ficheiro '$fileName': " . $_FILES["restaurant_image"]["error"][$i];
                }
            } else {
                $check = getimagesize($_FILES["restaurant_image"]["tmp_name"][$i]);
                if ($check === false) {
                    $errorMessages[] = "O ficheiro '$fileName' não é uma imagem.";
                    $uploadOk = 0;
                }
    
                if ($_FILES["restaurant_image"]["size"][$i] > 5000000) {
                    $errorMessages[] = "O ficheiro '$fileName' é muito grande.";
                    $uploadOk = 0;
                }
    
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                    $errorMessages[] = "O ficheiro '$fileName' apenas suporta JPG, JPEG & PNG.";
                    $uploadOk = 0;
                }
            }
    
            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["restaurant_image"]["tmp_name"][$i], $targetFileServer)) {
                    if ($restaurant_data) {
                        $id_restaurante = $restaurant_data['id'];
                        $sql = "INSERT INTO imagem_restaurante (id_restaurante, caminho_imagem) VALUES (?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("is", $id_restaurante, $targetFileWeb);
                        if ($stmt->execute()) {
                            $successCount++;
                        } else {
                            $errorMessages[] = "Erro ao salvar o ficheiro '$fileName' no banco de dados: " . $conn->error;
                        }
                        $stmt->close();
                    } else {
                        $errorMessages[] = "Restaurante não encontrado.";
                    }
                } else {
                    $errorMessages[] = "Erro ao mover o ficheiro '$fileName' para o servidor.";
                    error_log("Failed to move uploaded file to: " . $targetFileServer);
                }
            }
        }
    
        if ($successCount > 0) {
            $_SESSION['message'] = "Foram carregadas $successCount imagem(ns) com sucesso!";
        }
        if (!empty($errorMessages)) {
            $_SESSION['message'] = ($_SESSION['message'] ?? '') . " " . implode(" ", $errorMessages);
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['delete_image'])) {
    $image_id = $_POST['delete_image'];
    
    $sql = "SELECT caminho_imagem FROM imagem_restaurante 
            INNER JOIN restaurante ON imagem_restaurante.id_restaurante = restaurante.id 
            WHERE imagem_restaurante.id = ? AND restaurante.id_proprietario = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $image_id, $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $image_data = $result->fetch_assoc();
            $image_path_server = $_SERVER['DOCUMENT_ROOT'] . $image_data['caminho_imagem'];
            
            $delete_sql = "DELETE FROM imagem_restaurante WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $image_id);
            
            if ($delete_stmt->execute()) {
                if (file_exists($image_path_server)) {
                    unlink($image_path_server);
                }
                $_SESSION['message'] = "Imagem eliminada com sucesso!";
            } else {
                $_SESSION['message'] = "Erro ao eliminar a imagem da base de dados.";
            }
            $delete_stmt->close();
        } else {
            $_SESSION['message'] = "Imagem não encontrada ou não tem permissão.";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Erro na preparação da consulta.";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$restaurant_data = null;
$sql = "SELECT * FROM restaurante WHERE id_proprietario = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $restaurant_data = $result->fetch_assoc();
    }
    $stmt->close();
}

$restaurant_images = [];
if ($restaurant_data) {
    $sql = "SELECT * FROM imagem_restaurante WHERE id_restaurante = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $restaurant_data['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $restaurant_images[] = $row;
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
    } else {
        echo "Utilizador não encontrado.";
        exit();
    }
    $stmt->close();
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
    <link rel="stylesheet" href="../assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="../assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="../assets/vendors/owl-carousel-2/owl.carousel.min.css">
    <link rel="stylesheet" href="../assets/vendors/owl-carousel-2/owl.theme.default.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
    <link rel="stylesheet" href="../assets/vendors/mdi/css/materialdesignicons.css">
    <link rel="stylesheet" href="../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
</head>
<body class="sidebar-fixed">
    <div class="container-scroller">
      <?php include'sidebar.php'?>
      <div class="page-body-wrapper">
        <?php include'navbar.php'?>

        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title">Informações do Restaurante</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active">Restaurante</li>
                            <li class="breadcrumb-item" aria-current="page"><strong>Atualizar Informações</strong></li>
                        </ol>
                    </nav>
                </div>
                <div class="row">
                    <div class="col-12 grid-margin">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Detalhes do Restaurante</h4>
                                <form class="form-sample" method="POST" enctype="multipart/form-data">
                                    <p class="card-description">Informações do Restaurante</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Nome do Restaurante</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="nome_empresa" value="<?php echo htmlspecialchars($restaurant_data['nome_empresa'] ?? ''); ?>" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">NIF</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="nif" value="<?php echo htmlspecialchars($restaurant_data['nif'] ?? ''); ?>" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Nome Legal</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="designacao_legal" value="<?php echo htmlspecialchars($restaurant_data['designacao_legal'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Numero de Mesas</label>
                                                <div class="col-sm-9">
                                                    <input type="number" class="form-control" name="capacidade" value="<?php echo htmlspecialchars($restaurant_data['capacidade'] ?? ''); ?>" required />
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
                                                    <input type="text" class="form-control" name="telefone" value="<?php echo htmlspecialchars($restaurant_data['telefone'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Email de Contacto</label>
                                                <div class="col-sm-9">
                                                    <input type="email" class="form-control" name="email_contato" value="<?php echo htmlspecialchars($restaurant_data['email_contato'] ?? ''); ?>" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Numero de Contacto</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="numero_contato" value="<?php echo htmlspecialchars($restaurant_data['numero_contato'] ?? ''); ?>" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Intervalo de Preço</label>
                                                <div class="col-sm-9">
                                                    <input type="number" class="form-control" name="intervalo_precos" value="<?php echo htmlspecialchars($restaurant_data['intervalo_precos'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="card-description">Morada</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Morada</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="morada" value="<?php echo htmlspecialchars($restaurant_data['morada'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Codigo Postal</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="codigo_postal" value="<?php echo htmlspecialchars($restaurant_data['codigo_postal'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Destrito</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="distrito" value="<?php echo htmlspecialchars($restaurant_data['distrito'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">País</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="pais" value="<?php echo htmlspecialchars($restaurant_data['pais'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="card-description">Informações Bancarias</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Nome do Banco</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="nome_banco" value="<?php echo htmlspecialchars($restaurant_data['nome_banco'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">IBAN</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="iban" value="<?php echo htmlspecialchars($restaurant_data['iban'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Prorpietario da Conta</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="titular_conta" value="<?php echo htmlspecialchars($restaurant_data['titular_conta'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="card-description">Imagens do Restaurante</p>
                                    <?php if (!empty($message)): ?>
                                        <div class="alert <?php echo strpos($message, 'sucesso') !== false ? 'alert-success' : 'alert-danger'; ?>">
                                            <?php echo $message; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($message)): ?>
                                            <div class="alert <?= strpos($message, 'sucesso') !== false ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show">
                                                <?= $message ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>

                                    <div class="form-group">
                                        <label>Adicionar Imagens</label>
                                        <input type="file" id="restaurant_image" name="restaurant_image[]" class="file-upload-default" accept="image/*" multiple>
                                        <div class="input-group col-xs-12 d-flex align-items-center">
                                            <input type="text" class="form-control file-upload-info" disabled placeholder="Adicione Ficheiros de Imagens">
                                            <span class="input-group-append ms-2">
                                                <button id="upload-button" class="file-upload-browse btn btn-primary" type="button">Adicionar</button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-12 text-center">
                                            <button type="submit" class="btn btn-primary">Atualizar Informações</button>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <h4>Imagens Atuais</h4>
                                            <div class="row">
                                                <?php foreach ($restaurant_images as $image): ?>
                                                <div class="col-md-4 mb-4">
                                                    <div class="card">
                                                        <img src="<?php echo htmlspecialchars($image['caminho_imagem']); ?>" 
                                                            class="card-img-top" 
                                                            alt="Restaurant Image"
                                                            style="height: 200px; object-fit: cover; ">
                                                        <div class="card-body text-center">
                                                        <button type="submit" 
                                                                name="delete_image" 
                                                                value="<?php echo $image['id']; ?>" 
                                                                class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Tem certeza que deseja eliminar esta imagem?');">
                                                            Eliminar Imagem
                                                        </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include '../footer.php'; ?>
        </div>
    </div>

    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../assets/js/off-canvas.js"></script>
    <script src="../assets/js/misc.js"></script>
    <script src="../assets/js/settings.js"></script>
    <script src="../assets/js/todolist.js"></script>
    <script src="../assets/js/file-upload.js"></script>
    <script>
    document.getElementById('upload-button').addEventListener('click', function() {
        document.getElementById('restaurant_image').click();
    });

    document.getElementById('restaurant_image').addEventListener('change', function() {
        var files = this.files;
        var fileNames = Array.from(files).map(file => file.name).join(', ');
        document.querySelector('.file-upload-info').value = fileNames || 'Nenhum arquivo selecionado';
        document.querySelector('form.form-sample').submit();
    });

    window.setTimeout(function() {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.display = 'none';
        });
    }, 5000);
</script>
    <script>

</script>
</body>
</html>