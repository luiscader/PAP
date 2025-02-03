<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id_cliente = $_SESSION['id'];
$message = '';

// Handle image upload
// Fetch restaurant data
$restaurant_data = null;
$sql = "SELECT * FROM restaurante WHERE id_proprietario = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $restaurant_data = $result->fetch_assoc();
    } else {
        $message = "Restaurante não encontrado.";
    }
    $stmt->close();
}

// Handle image upload
if (isset($_FILES['restaurant_image']) && $_FILES['restaurant_image']['error'] != UPLOAD_ERR_NO_FILE) {
    // Define server and web paths
    $targetDirServer = $_SERVER['DOCUMENT_ROOT'] . "/PAP/geral/uploads/";
    $targetDirWeb = "/PAP/geral/uploads/";
    
    if (!file_exists($targetDirServer)) {
        mkdir($targetDirServer, 0777, true);
    }

    $fileName = basename($_FILES["restaurant_image"]["name"]);
    $uniqueName = time() . '_' . $fileName;
    $targetFileServer = $targetDirServer . $uniqueName;
    $targetFileWeb = $targetDirWeb . $uniqueName;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFileServer, PATHINFO_EXTENSION));

    // Check PHP upload errors
    if ($_FILES["restaurant_image"]["error"] !== UPLOAD_ERR_OK) {
        $uploadOk = 0;
        switch ($_FILES["restaurant_image"]["error"]) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $message = "O ficheiro é muito grande. (Apenas até 5MB)";
                break;
            default:
                $message = "Erro ao carregar a imagem.";
        }
    } else {
        // Check if image is valid
        $check = getimagesize($_FILES["restaurant_image"]["tmp_name"]);
        if ($check === false) {
            $message = "O ficheiro não é uma imagem.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["restaurant_image"]["size"] > 5000000) {
            $message = "O ficheiro é muito grande.";
            $uploadOk = 0;
        }

        // Check file format
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $message = "Apenas ficheiros JPG, JPEG & PNG são permitidos.";
            $uploadOk = 0;
        }
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["restaurant_image"]["tmp_name"], $targetFileServer)) {
            // Insert image into database
            if ($restaurant_data) {
                $id_restaurante = $restaurant_data['id'];
                $sql = "INSERT INTO imagem_restaurante (id_restaurante, caminho_imagem) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $id_restaurante, $targetFileWeb);
                if ($stmt->execute()) {
                    $message = "Imagem carregada com sucesso!";
                } else {
                    $message = "Erro ao salvar a imagem no banco de dados: " . $conn->error;
                }
                $stmt->close();
            } else {
                $message = "Restaurante não encontrado.";
            }
        } else {
            $message = "Erro ao carregar a imagem.";
        }
    }
}
// Handle image deletion
if (isset($_POST['delete_image'])) {
    $image_id = $_POST['delete_image'];
    
    // Fetch image path and verify ownership
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
            
            // Delete from database
            $delete_sql = "DELETE FROM imagem_restaurante WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $image_id);
            
            if ($delete_stmt->execute()) {
                // Delete file from server
                if (file_exists($image_path_server)) {
                    unlink($image_path_server);
                }
                $message = "Imagem eliminada com sucesso!";
            } else {
                $message = "Erro ao eliminar a imagem da base de dados.";
            }
            $delete_stmt->close();
        } else {
            $message = "Imagem não encontrada ou não tem permissão.";
        }
        $stmt->close();
    } else {
        $message = "Erro na preparação da consulta.";
    }
}
// Fetch restaurant data
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

// Fetch restaurant images
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

// Fetch user data
$sql = "SELECT id, nome, email, senha, tipo FROM Utilizador WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $nome, $email, $senha, $tipo);
    if ($stmt->num_rows > 0) {
        $stmt->fetch();
    } else {
        echo "Cliente não encontrado.";
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
    
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/owl-carousel-2/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/vendors/owl-carousel-2/owl.theme.default.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css --> 
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">
    
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/favicon.png" />
</head>
<body class="sidebar-fixed">
    <div class="container-scroller">
      <!-- partial:partials/_sidebar.html -->
      <?php include'sidebar.php'?>
      <!-- partial -->
      <div class="page-body-wrapper">
        <!-- partial:partials/_navbar.html -->
        <?php include'navbar.php'?>
        <!-- partial -->

        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title">Informações do Restaurante</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active">Restaurante</li>
                            <li class="breadcrumb-item" aria-current="page"> Atualizar Informações</li>
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
                                                <label class="col-sm-3 col-form-label">Capacidade</label>
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
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Adicionar Nova Imagem</label>
                                                <input type="file" name="restaurant_image" class="form-control" accept="image/*">
                                            </div>
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
                                                        <div class="card-body">
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
                                    <div class="row mt-4">
                                        <div class="col-12 text-center">
                                            <button type="submit" class="btn btn-primary">Atualizar Informações do Restaurante</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <!-- plugins:js -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>
</body>
</html>