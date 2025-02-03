<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id_cliente = $_SESSION['id'];
$fornecedores = [];

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
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Model</title>
    
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/favicon.png" />
</head>
<body class="sidebar-fixed">
    <div class="container-scroller">
      <?php include 'sidebar.php'?>
      <div class="page-body-wrapper">
        <?php include 'navbar.php'?>
        
        <div class="main-panel">
            <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title">Criar Categoria</h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Gestão</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Criar Categoria</li>
                </ol>
              </nav>
            </div>
            <div class="row grid-margin">
              <div class="col-lg-12">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Categoria</h4>
                    <form class="cmxform" id="commentForm" method="get" action="#">
                      <fieldset>
                        <div class="form-group">
                          <label for="cname">Name: *</label>
                          <input id="cname" class="form-control" name="name" type="text" placeholder="Nome da Categoria ..." required>
                        </div>
                        <div class="form-group">
                          <label for="ccomment">Descrição (Opcional):</label>
                          <textarea id="ccomment" class="form-control" name="comment" placeholder="Descrição..." ></textarea>
                        </div>
                        <input class="btn btn-inverse-primary" type="submit" value="Submit">
                      </fieldset>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            </div>
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>
    <script src="assets/js/data-table.js"></script>
</body>
</html>