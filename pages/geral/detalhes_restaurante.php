<?php
include 'C:/wamp64/www/PAP/includes/config.php';
include 'navbar.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reserva'])) {
    $restaurant_id = (int)$_POST['restaurant_id'];
    $nome_completo = $_POST['nome_completo'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $preferencia_contato = $_POST['preferencia_contato'];
    $data_reserva = $_POST['data_reserva'];
    $hora_reserva = $_POST['hora_reserva'];
    $num_pessoas = (int)$_POST['num_pessoas'];

    if (!preg_match('/^[0-9]{9,15}$/', $telefone)) {
        echo "<script>alert('Telefone inválido.'); window.location.href='detalhes_restaurante.php?id=$restaurant_id';</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Email inválido.'); window.location.href='detalhes_restaurante.php?id=$restaurant_id';</script>";
        exit;
    }

    $query = "INSERT INTO reserva (nome_completo, telefone, email, preferencia_contato, data_reserva, hora_reserva, num_pessoas, id_restaurante) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssssii', $nome_completo, $telefone, $email, $preferencia_contato, $data_reserva, $hora_reserva, $num_pessoas, $restaurant_id);

    if ($stmt->execute()) {
        echo "<script>alert('Reserva realizada com sucesso!'); window.location.href='detalhes_restaurante.php?id=$restaurant_id';</script>";
    } else {
        echo "<script>alert('Erro ao realizar a reserva.'); window.location.href='detalhes_restaurante.php?id=$restaurant_id';</script>";
    }

    $stmt->close();
    $conn->close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_avaliacao'])) {

    if (!isset($_SESSION['id'])) {
        echo "<script>alert('Por favor, faça login para enviar uma avaliação.'); window.location.href='detalhes_restaurante.php?id=$restaurantId';</script>";
        exit;
    }

    $restaurant_id = (int)$_POST['restaurant_id'];
    $user_id = (int)$_SESSION['id'];
    $comida = (int)$_POST['comida'];
    $servico = (int)$_POST['servico'];
    $valor = (int)$_POST['valor'];
    $ambiente = (int)$_POST['ambiente'];
    $comentario = trim($_POST['comentario']);

    if ($comida < 1 || $comida > 5 || $servico < 1 || $servico > 5 || 
        $valor < 1 || $valor > 5 || $ambiente < 1 || $ambiente > 5) {
        echo "<script>alert('As avaliações devem estar entre 1 e 5.'); window.location.href='detalhes_restaurante.php?id=$restaurant_id';</script>";
        exit;
    }

    if (empty($comentario)) {
        echo "<script>alert('Por favor, escreva um comentário.'); window.location.href='detalhes_restaurante.php?id=$restaurant_id';</script>";
        exit;
    }

    $queryCheckRestaurant = "SELECT id FROM restaurante WHERE id = ?";
    $stmtCheckRestaurant = $conn->prepare($queryCheckRestaurant);
    $stmtCheckRestaurant->bind_param('i', $restaurant_id);
    $stmtCheckRestaurant->execute();
    $resultCheckRestaurant = $stmtCheckRestaurant->get_result();
    if ($resultCheckRestaurant->num_rows == 0) {
        echo "<script>alert('Restaurante não encontrado.'); window.location.href='detalhes_restaurante.php?id=$restaurant_id';</script>";
        $stmtCheckRestaurant->close();
        exit;
    }
    $stmtCheckRestaurant->close();

    $queryCheckReservation = "SELECT id FROM reserva WHERE id_restaurante = ? AND nome_completo = (SELECT nome FROM Utilizador WHERE id = ?)";
    $stmtCheckReservation = $conn->prepare($queryCheckReservation);
    $stmtCheckReservation->bind_param('ii', $restaurant_id, $user_id);
    $stmtCheckReservation->execute();
    $resultCheckReservation = $stmtCheckReservation->get_result();

    if ($resultCheckReservation->num_rows == 0) {
        echo "<script>alert('Você precisa ter uma reserva neste restaurante para enviar uma avaliação.'); window.location.href='detalhes_restaurante.php?id=$restaurant_id';</script>";
        $stmtCheckReservation->close();
        exit;
    }
    $stmtCheckReservation->close();

    $queryCheckReview = "SELECT id FROM avaliacoes WHERE id_restaurante = ? AND id_utilizador = ?";
    $stmtCheckReview = $conn->prepare($queryCheckReview);
    $stmtCheckReview->bind_param('ii', $restaurant_id, $user_id);
    $stmtCheckReview->execute();
    $resultCheckReview = $stmtCheckReview->get_result();

    if ($resultCheckReview->num_rows > 0) {
        echo "<script>alert('Você já avaliou este restaurante.'); window.location.href='detalhes_restaurante.php?id=$restaurant_id';</script>";
        $stmtCheckReview->close();
        exit;
    }
    $stmtCheckReview->close();

    $query = "INSERT INTO avaliacoes (id_restaurante, id_utilizador, comida, servico, valor, ambiente, comentario, data_avaliacao) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiiiiss', $restaurant_id, $user_id, $comida, $servico, $valor, $ambiente, $comentario);

    if ($stmt->execute()) {
        echo "<script>alert('Avaliação enviada com sucesso!'); window.location.href='detalhes_restaurante.php?id=$restaurant_id';</script>";
    } else {
        echo "<script>alert('Erro ao enviar a avaliação.'); window.location.href='detalhes_restaurante.php?id=$restaurant_id';</script>";
    }

    $stmt->close();
    exit;
}

$queryRestaurants = "SELECT id, nome_empresa FROM restaurante";
$resultRestaurants = $conn->query($queryRestaurants);

$restaurantId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$query = "
    SELECT r.nome_empresa, r.morada, r.codigo_postal, r.distrito, r.intervalo_precos,
           r.numero_contato,  -- Certificando que está incluído
           GROUP_CONCAT(DISTINCT tc.nome SEPARATOR ', ') AS tipos_cozinha,
           AVG((a.comida + a.servico + a.valor + a.ambiente) / 4) AS avg_total,
           COUNT(DISTINCT a.id) AS total_avaliacoes
    FROM restaurante r
    LEFT JOIN avaliacoes a ON r.id = a.id_restaurante
    LEFT JOIN restaurante_tipocozinha rtc ON r.id = rtc.id_restaurante
    LEFT JOIN tipocozinha tc ON rtc.id_tipo_cozinha = tc.id
    WHERE r.id = ?
    GROUP BY r.id
";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $restaurantId);
$stmt->execute();
$result = $stmt->get_result();
$restaurant = $result->fetch_assoc();

if ($restaurant === null) {
    echo "<div class='restaurant-info'><h1>Erro</h1><p>Restaurante não encontrado. Por favor, verifique o ID do restaurante ou entre em contato com o suporte.</p></div>";
    include 'footer.php';
    exit;
}

$queryImages = "SELECT caminho_imagem FROM imagem_restaurante WHERE id_restaurante = ?";
$stmtImages = $conn->prepare($queryImages);
$stmtImages->bind_param('i', $restaurantId);
$stmtImages->execute();
$resultImages = $stmtImages->get_result();
$images = $resultImages->fetch_all(MYSQLI_ASSOC);

$queryComments = "
    SELECT a.comida, a.servico, a.valor, a.ambiente, a.comentario, a.data_avaliacao, u.nome AS reviewer_name
    FROM avaliacoes a
    LEFT JOIN Utilizador u ON a.id_utilizador = u.id
    WHERE a.id_restaurante = ?
    ORDER BY a.data_avaliacao DESC
";
$stmtComments = $conn->prepare($queryComments);
$stmtComments->bind_param('i', $restaurantId);
$stmtComments->execute();
$resultComments = $stmtComments->get_result();
$comments = $resultComments->fetch_all(MYSQLI_ASSOC);

$queryCategories = "
    SELECT c.id, c.nome 
    FROM categoria c
    WHERE c.id_restaurante = ?
    ORDER BY c.nome ASC
";
$stmtCategories = $conn->prepare($queryCategories);
$stmtCategories->bind_param('i', $restaurantId);
$stmtCategories->execute();
$resultCategories = $stmtCategories->get_result();
$categories = $resultCategories->fetch_all(MYSQLI_ASSOC);

$queryCategorylessItems = "
    SELECT COUNT(*) as count 
    FROM pratos 
    WHERE id_restaurante = ?
";
$stmtCategorylessItems = $conn->prepare($queryCategorylessItems);
$stmtCategorylessItems->bind_param('i', $restaurantId);
$stmtCategorylessItems->execute();
$resultCategorylessItems = $stmtCategorylessItems->get_result();
$categorylessItemsCount = $resultCategorylessItems->fetch_assoc()['count'];

if ($categorylessItemsCount > 0 || count($categories) == 0) {
    $categories[] = ['id' => 0, 'nome' => 'Menu Principal'];
}

$menuByCategory = [];

foreach ($categories as $category) {
    $categoryId = $category['id'];

    if ($categoryId == 0) {
        $queryMenu = "
            SELECT id, nome, descricao, preco 
            FROM pratos 
            WHERE id_restaurante = ? 
            AND (id_categoria IS NULL OR id_categoria = 0)
            ORDER BY nome ASC
        ";
        $stmtMenu = $conn->prepare($queryMenu);
        $stmtMenu->bind_param('i', $restaurantId);
    } else {
        $queryMenu = "
            SELECT id, nome, descricao, preco 
            FROM pratos 
            WHERE id_restaurante = ? AND id_categoria = ?
            ORDER BY nome ASC
        ";
        $stmtMenu = $conn->prepare($queryMenu);
        $stmtMenu->bind_param('ii', $restaurantId, $categoryId);
    }

    $stmtMenu->execute();
    $resultMenu = $stmtMenu->get_result();
    $menuItems = $resultMenu->fetch_all(MYSQLI_ASSOC);    
    if (!empty($menuItems)) {
        $menuByCategory[$category['nome']] = $menuItems;
    }
}

$ingredientsByDish = [];
foreach ($menuByCategory as $categoryName => $dishes) {
    foreach ($dishes as $dish) {
        $dishId = $dish['id'];

        $queryIngredients = "
            SELECT p.nome, ip.quantidade_necessaria, ip.unidade_medida
            FROM ingrediente_prato ip
            JOIN produtos p ON ip.id_produto = p.id
            WHERE ip.id_prato = ?
            ORDER BY p.nome ASC
        ";
        $stmtIngredients = $conn->prepare($queryIngredients);
        $stmtIngredients->bind_param('i', $dishId);
        $stmtIngredients->execute();
        $resultIngredients = $stmtIngredients->get_result();
        $ingredients = $resultIngredients->fetch_all(MYSQLI_ASSOC);

        $ingredientsByDish[$dishId] = $ingredients;
    }
}

$currentDate = date('Y-m-d');
$queryReservations = "
    SELECT COUNT(*) as total_reservations 
    FROM reserva 
    WHERE id_restaurante = ? AND DATE(data_reserva) = ?
";
$stmtReservations = $conn->prepare($queryReservations);
$stmtReservations->bind_param('is', $restaurantId, $currentDate);
$stmtReservations->execute();
$resultReservations = $stmtReservations->get_result();
$totalReservations = $resultReservations->fetch_assoc()['total_reservations'];

$capacity = $restaurant['capacidade'] ?? 100;
$occupationData = [];
$startDate = new DateTime();
$endDate = (clone $startDate)->modify('+1 month');
$interval = new DateInterval('P1D');
$period = new DatePeriod($startDate, $interval, $endDate);

foreach ($period as $date) {
    $day = $date->format('Y-m-d');
    $queryDayReservations = "
        SELECT SUM(num_pessoas) as total_people 
        FROM reserva 
        WHERE id_restaurante = ? AND DATE(data_reserva) = ?
    ";
    $stmtDayReservations = $conn->prepare($queryDayReservations);
    $stmtDayReservations->bind_param('is', $restaurantId, $day);
    $stmtDayReservations->execute();
    $resultDayReservations = $stmtDayReservations->get_result();
    $totalPeople = $resultDayReservations->fetch_assoc()['total_people'] ?? 0;

    $occupationData[$day] = $capacity > 0 ? min(100, ($totalPeople / $capacity) * 100) : 0;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="shortcut icon" href="../../geral/assets/images/favicon.png" />
    <title><?php echo htmlspecialchars($restaurant['nome_empresa'] ?? 'Restaurante'); ?> - Reserva</title>
    <style>
body {
    margin: 0;
    font-family: Montserrat, Arial, sans-serif;
    background-color: var(--background-color);
    overflow-x: hidden;
}

img {
    max-width: 100%;
    height: auto;
}

ul, li {
    list-style: none;
    margin: 0;
    padding: 0;
}

.restaurant-info {
    overflow: hidden;
    margin-left: 230px;
    margin-right: 150px;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    margin-top: 20px;
}

.restaurant-info h1 {
    font-size: 2rem;
    margin-bottom: 10px;
}

.restaurant-info p, .restaurant-details span {
    margin: 5px 0;
    font-size: 1.1rem;
    font-family: Montserrat, Arial, sans-serif;
}

.restaurant-details {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.ratings {
    margin: 3px 0;
    margin-bottom: 20px;
}

.ratings span {
    font-size: 1.2rem;
}

.rating-star {
    font-size: 1.3rem;
    color: #e2e8f0;
    cursor: pointer;
    transition: color 0.2s ease;
}

.rating-star.filled {
    color: #f97316;
}

.comments-section {
    margin-top: 20px;
}

.comment-form {
    background: #f9fafb;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.rating-group {
    display: flex;
    flex-direction: column; 
    gap: 15px; 
    margin-bottom: 20px;
}

.rating-item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.rating-item label {
    margin-bottom: 8px;
    color: #2d3748;
    font-weight: 600;
    font-size: 0.95rem;
}

.rating-item label i {
    margin-right: 5px;
    color: #ff5722;
}

.rating-item .stars {
    display: flex;
    gap: 4px; 
}

.rating-item input[type="hidden"] {
    display: none;
}

.rating-item .stars:hover .rating-star,
.rating-item .stars .rating-star:hover ~ .rating-star {
    color: #e2e8f0;
}

.rating-item .stars .rating-star:hover,
.rating-item .stars .rating-star:hover ~ .rating-star.filled {
    color: #f97316;
}

.comment-form textarea {
    width: 100%;
    min-height: 120px;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    resize: vertical;
    font-family: Montserrat, Arial, sans-serif;
    font-size: 1rem;
    transition: border-color 0.2s ease;
}

.comment-form textarea:focus {
    border-color: #ff5722;
    outline: none;
}

.comment-form button {
    padding: 12px 24px;
    background-color: #ff5722;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
    margin-top: 10px;
}

.comment-form button:hover {
    background-color: #e64a19;
}

.comment-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 20px;
}

.comment-item {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.comment-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.comment-reviewer {
    font-weight: 600;
    color: #2d3748;
    font-size: 1.1rem;
}

.comment-date {
    color: #718096;
    font-size: 0.9rem;
}

.comment-ratings {
    display: flex;
    flex-direction: column; 
    gap: 8px; 
    margin-bottom: 15px;
    color: #2d3748;
}

.comment-ratings span {
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.comment-ratings span i {
    color: #ff5722;
}

.comment-ratings .stars {
    display: flex; 
    gap: 4px; 
    font-size: 1.3rem; 
}

.comment-ratings .stars .rating-star {
    color: #e2e8f0; 
}

.comment-ratings .stars .rating-star.filled {
    color: #f97316; 
}

.comment-content {
    color: #4a5568;
    line-height: 1.6;
    font-size: 1rem;
    background: #f9fafb;
    padding: 15px;
    border-radius: 8px;
}

.no-comments {
    color: #718096;
    text-align: center;
    padding: 30px 0;
    font-size: 1.1rem;
    background: #f9fafb;
    border-radius: 10px;
}

.gallery {
    margin-left: 0px;
    margin-right: 0px;
}

.gallery h2 {
    font-size: 1.8rem;
    margin-bottom: 25px;
    color: #333;
    font-weight: 600;
    margin-left: -10px;
}

.image-grid {
    display: grid;
    grid-template-columns: repeat(4, 200px);
    gap: 8px;
    overflow: hidden;
    margin-bottom: 10px;
}

.image-square {
    width: 180px;
    height: 180px;
    border-radius: 20px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    background-size: cover;
    background-position: center;
    margin: 8px;
}

.image-square div {
    width: 180px;
    height: 180px;
    border-radius: 20px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    background-size: cover;
    background-position: center;
    margin: 8px;
    margin-top: 10px;
}

.image-square img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.view-all-square {
    background-color: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    width: 180px;
    height: 120px;
    border-radius: 20px;
    transition: background-color 0.3s ease, transform 0.2s ease;
    cursor: pointer;
    margin: 8px;
    margin-left: 4px;
}

.view-all-square:hover {
    background: linear-gradient(45deg, #e2e8f0, #cbd5e0);
    transform: translateY(-5px);
}

.view-all-square a {
    color: rgb(0, 0, 0);
    font-size: 1.2rem;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 10px;
}

.view-all-square a i {
    font-size: 1.4rem;
}

.gallery .image-grid {
    display: inline;
}

.gallery .image-grid .image-square:nth-child(1) {
    width: 420px;
    height: 280px;
    float: left;
}

.gallery .image-grid .image-square:nth-child(2) {
    width: 220px;
    height: 280px;
    float: left;
}

.gallery .image-grid .image-square:nth-child(3) {
    width: 360px;
    height: 150px;
    float: left;
    margin-bottom: 15px;
}

.gallery .image-grid .image-square:nth-child(4) {
    width: 180px;
    height: 120px;
    float: left;
    margin-top: 1px;
}

.gallery .image-grid .image-square:nth-child(5) {
    width: 180px;
    height: 120px;
    float: left;
}

.modal-content {
    position: relative;
    backdrop-filter: blur(5px);
    background-color: rgba(224, 224, 224, 0.62);
    max-width: 80vw;
    max-height: 80vh;
    padding: 1%;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    border-radius: 30px;
    overflow-y: auto;
    width: 75%;
    height: 75%;
    margin-top: 100px;
    margin-left: 170px;
    margin-right: 100px;
    margin-bottom: 100px;
}

.modal .image-square {
    width: 320px;
    height: 320px;
    border-radius: 1rem;
}

.content-container {
    display: flex;
    flex-direction: row;
    margin-top: 10px;
    padding: 0 20px;
    margin-left: -30px;
    margin-right: 30px;
    margin-left: 0;
    gap: 20px;
}

.left-content {
    display: flex;
    flex-direction: column;
    flex: 2;
    margin-top: 30px;
    padding: 10px;
    margin-left: -20px;
}

.nav-bar {
    background: #fff;
    border-radius: 8px;
    padding: 5px 10px;
    width: fit-content;
    align-self: flex-start;
}

.nav-menu {
    display: flex;
    justify-content: flex-start;
    gap: 10px;
    list-style: none;
    padding: 5px;
}

.nav-menu li {
    margin: 0;
}

.nav-menu a {
    text-decoration: none;
    color: #2d3748;
    font-size: 1.3rem;
    font-weight: 500;
    padding: 5px 10px;
    border-radius: 6px;
    transition: color 0.2s ease;
    position: relative;
}

.nav-menu a:hover {
    color: #ff5722;
}

.nav-menu a.active {
    color: #ff5722;
    background-color: transparent;
}

.nav-menu a.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: #ff5722;
}

.content-sections {
    margin-top: 15px;
}

.content-section {
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: none;
}

.content-section.active {
    display: block;
}

.content-section h2 {
    font-size: 1.8rem;
    color: #333;
    margin-bottom: 15px;
}

.menu-category {
    margin-bottom: 30px;
}

.menu-category h3 {
    font-size: 1.5rem;
    color: #2d3748;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e2e8f0;
}

.menu-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.menu-item {
    padding: 15px;
    border-radius: 10px;
    background-color: white;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.menu-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.menu-item h4 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 1.3rem;
    color: #1a202c;
}

.menu-item p {
    color: #4a5568;
    margin-bottom: 15px;
    font-size: 1rem;
    line-height: 1.5;
}

.menu-item .price {
    font-weight: 700;
    font-size: 1.2rem;
    color: #2d3748;
    margin-bottom: 10px;
}

.ingredients-list {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed #e2e8f0;
    font-size: 0.9rem;
    color: #718096;
}

.ingredients-list span {
    display: inline-block;
    margin-right: 5px;
    margin-bottom: 5px;
    padding: 3px 8px;
    background-color: #f3f4f6;
    border-radius: 12px;
}

.empty-menu {
    text-align: center;
    padding: 40px 0;
    color: #718096;
    font-size: 1.1rem;
}

.reservation-section {
    flex: 1;
    min-width: 300px;
    max-width: 400px;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    align-self: flex-start;
    margin-top: 30px;
}

.reservation-section h2 {
    margin-left: 0;
    font-size: 1.8rem;
    color: #333;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    white-space: nowrap;
}

.reservation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0;
}

.popularity-indicator {
    background: #fff3e0;
    color: #f97316;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 10px;
    margin-bottom: 15px;
    margin-left: auto;
}

.popularity-indicator i {
    font-size: 1rem;
}

.reservation-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 20px;
    margin-top: 10px;
}

.form-group {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.form-group label {
    margin-bottom: 8px;
    font-weight: 500;
    color: #2d3748;
}

.form-group input,
.form-group select {
    padding: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 1rem;
    background: white;
    width: 100%;
    box-sizing: border-box;
}

.input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: white;
    width: 100%;
}

.input-with-icon i {
    position: absolute;
    left: 10px;
    color: #666;
}

.input-with-icon select,
.input-with-icon input {
    padding-left: 35px;
    border: none;
    width: 100%;
    height: 40px;
    background: transparent;
    box-sizing: border-box;
}

.calendar-container {
    position: relative;
    width: 100%;
}

.calendar {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    width: 100%;
    box-sizing: border-box;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.calendar-header h4 {
    margin: 0;
    font-size: 1.1rem;
    text-transform: uppercase;
}

.calendar-header button {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 5px;
    text-align: center;
}

.calendar-grid .day-header {
    font-weight: bold;
    color: #666;
    font-size: 0.9rem;
}

.calendar-grid .day {
    padding: 8px;
    border-radius: 5px;
    cursor: pointer;
    position: relative;
}

.calendar-grid .day:hover {
    background: #f0f0f0;
}

.calendar-grid .day.disabled {
    color: #ccc;
    cursor: not-allowed;
}

.calendar-grid .day .occupation {
    font-size: 0.8rem;
    color: #f97316;
    display: block;
}

.occupation-note {
    font-size: 0.9rem;
    color: #666;
    margin-top: 10px;
}

.reserve-button {
    padding: 12px 24px;
    background-color: #ff5722;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
    margin-top: 20px;
    width: 100%;
}

.reserve-button:hover {
    background-color: #e64a19;
}

.info-table {
    width: 100%;
    max-width: 400px;
    margin: 20px 0;
    border-collapse: collapse;
    font-size: 1rem;
    color: #2d3748;
}

.info-table tr {
    border-bottom: 1px solid #e2e8f0;
}

.info-table td {
    padding: 12px 0;
    vertical-align: middle;
}

.info-table td:first-child {
    font-weight: 600;
    width: 40%;
}

.info-table td i {
    margin-right: 8px;
    color: #ff5722;
}

.contact-methods {
    margin-top: 20px;
}

.contact-methods h3 {
    font-size: 1.5rem;
    color: #2d3748;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e2e8f0;
}

.contact-methods ul {
    list-style: none;
    padding: 0;
}

.contact-methods li {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    font-size: 1rem;
    color: #4a5568;
}

.contact-methods li i {
    margin-right: 10px;
    color: #ff5722;
    font-size: 1.2rem;
}

.contact-methods li a {
    color: #ff5722;
    text-decoration: none;
    transition: color 0.2s ease;
}

.contact-methods li a:hover {
    color: #e64a19;
}

@media (max-width: 1024px) {
    .modal-content {
        grid-template-columns: repeat(3, 1fr);
    }
    .menu-list {
        grid-template-columns: repeat(2, 1fr);
    }
    .content-container {
        flex-direction: column;
    }
    .reservation-section {
        max-width: 100%;
    }
}

@media (max-width: 768px) {
    .modal-content {
        grid-template-columns: repeat(2, 1fr);
    }
    .menu-list {
        grid-template-columns: 1fr;
    }
    .restaurant-info {
        margin-left: 20px;
        margin-right: 20px;
    }
    .image-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    .nav-menu {
        gap: 5px;
    }
    .nav-menu a {
        font-size: 0.8rem;
        padding: 4px 8px;
    }
    .rating-group {
        flex-direction: column; 
    }
    .comment-ratings {
        flex-direction: column; 
    }
    .comment-form {
        padding: 15px;
    }
    .comment-item {
        padding: 15px;
    }
}

@media (max-width: 480px) {
    .modal-content {
        grid-template-columns: repeat(1, 1fr);
    }
    .image-grid {
        grid-template-columns: 1fr;
    }
    .rating-group {
        flex-direction: column; 
    }
    .comment-form button {
        width: 100%;
    }
    .comment-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}

.close {
    position: fixed;
    top: 20px;
    right: 30px;
    color: #fff;
    font-size: 2.5rem;
    opacity: 0.8;
    transition: opacity 0.3s ease;
    z-index: 1010;
    cursor: pointer;
}

.close:hover {
    opacity: 1;
    color: #fff;
}
</style>
</head>
<body>
    <div class="restaurant-info">
        <h1><?php echo htmlspecialchars($restaurant['nome_empresa'] ?? ''); ?></h1>
        <div class="restaurant-details">
            <p><strong><i class='fas fa-map-marker-alt'></i></strong> <?php echo htmlspecialchars($restaurant['morada'] ?? ''); ?>,</p>
            <p><?php echo htmlspecialchars($restaurant['codigo_postal'] ?? ''); ?>,</p>
            <p><?php echo htmlspecialchars($restaurant['distrito'] ?? ''); ?></p>
        </div>
        <div class="restaurant-details">
            <p><strong><i class='fas fa-utensils'></i></strong> <?php echo htmlspecialchars($restaurant['tipos_cozinha'] ?? ''); ?></p>
            <p>- Intervalo de preço: <?php echo htmlspecialchars($restaurant['intervalo_precos'] ?? ''); ?>€</p>
        </div>
        <div class="ratings">
            <p><strong><i class='far fa-star'></i></strong>
                <?php
                if (is_null($restaurant['avg_total']) || $restaurant['total_avaliacoes'] == 0) {
                    echo "Sem avaliações";
                } else {
                    echo number_format($restaurant['avg_total'], 1) . "/5 (" . 
                        $restaurant['total_avaliacoes'] . " " . 
                        ($restaurant['total_avaliacoes'] == 1 ? "avaliação" : "avaliações") . ")";
                }
                ?>
            </p>
        </div>

        <div class="gallery">
            <div class="image-grid">
                <?php 
                $count = 0;
                foreach ($images as $image): 
                    $count++;
                    if ($count <= 4):
                ?>
                    <div class="image-square" onclick="openModal(<?php echo $count - 1; ?>)">
                        <img src="<?php echo htmlspecialchars($image['caminho_imagem']); ?>" 
                            alt="Imagem do restaurante <?php echo htmlspecialchars($restaurant['nome_empresa'] ?? ''); ?>" 
                            loading="lazy">
                    </div>
                <?php 
                    endif;
                endforeach; 

                if (count($images) > 4):
                ?>
                    <div class="view-all-square">
                        <a href="#" id="viewAllImages">
                            <i class="fas fa-images"></i>
                            Ver Galeria Completa <br>(<?php echo count($images); ?> fotos)
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="content-container">
            <div class="left-content">
                <div class="nav-bar">
                    <ul class="nav-menu">
                        <li><a href="#" class="active" data-section="sobre-section" onclick="return showSection(event, 'sobre-section')"><strong>Sobre</strong></a></li>
                        <li><a href="#" data-section="menu-section" onclick="return showSection(event, 'menu-section')"><Strong>Ementa</Strong></a></li>
                        <li><a href="#" data-section="avaliacoes-section" onclick="return showSection(event, 'avaliacoes-section')"><Strong>Avaliações</Strong></a></li>
                    </ul>
                </div>

                <div class="content-sections">
                <div class="content-section sobre-section active" id="sobre-section">

                    <table class="info-table">
                        <tr>
                            <td><i class="fas fa-utensils"></i> Tipo de cozinha</td>
                            <td><?php echo htmlspecialchars($restaurant['tipos_cozinha'] ?? 'Não especificado'); ?></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-euro-sign"></i> Preço médio</td>
                            <td><?php echo htmlspecialchars($restaurant['intervalo_precos'] ?? 'Não especificado'); ?>€</td>
                        </tr>
                    </table>

                    <div class="contact-methods">
                        <h3>Meios de Contacto</h3>
                        <ul>
                            <?php if (!empty($restaurant['numero_contato'])): ?>
                                <li>
                                    <i class="fas fa-phone"></i>
                                    <span><?php echo htmlspecialchars($restaurant['numero_contato']); ?></span>
                                </li>
                            <?php else: ?>
                                <li>
                                    <i class="fas fa-phone"></i>
                                    <span>Número de contato não disponível</span>
                                </li>
                            <?php endif; ?>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:contato@<?php echo strtolower(str_replace(' ', '', htmlspecialchars($restaurant['nome_empresa'] ?? 'restaurante'))); ?>.com">
                                    contato@<?php echo strtolower(str_replace(' ', '', htmlspecialchars($restaurant['nome_empresa'] ?? 'restaurante'))); ?>.com
                                </a>
                            </li>

                        </ul>
                    </div>
                </div>

                    <div class="content-section menu-section" id="menu-section">
                        <?php 
                        $totalMenuItems = 0;
                        foreach ($menuByCategory as $dishes) {
                            $totalMenuItems += count($dishes);
                        }

                        if ($totalMenuItems == 0): 
                        ?>
                            <div class="empty-menu">
                                <p>Este restaurante ainda não disponibilizou a sua ementa.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($menuByCategory as $categoryName => $dishes): ?>
                                <?php if (!empty($dishes)): ?>
                                    <div class="menu-category">
                                        <h3><?php echo htmlspecialchars($categoryName); ?></h3>
                                        <div class="menu-list">
                                            <?php foreach ($dishes as $dish): ?>
                                                <div class="menu-item">
                                                    <h4><?php echo htmlspecialchars($dish['nome']); ?></h4>
                                                    <?php if (!empty($dish['descricao'])): ?>
                                                        <p><?php echo htmlspecialchars($dish['descricao']); ?></p>
                                                    <?php endif; ?>
                                                    <div class="price"><?php echo number_format($dish['preco'], 2); ?>€</div>

                                                    <?php 
                                                    if (isset($ingredientsByDish[$dish['id']]) && !empty($ingredientsByDish[$dish['id']])): 
                                                    ?>
                                                        <div class="ingredients-list">
                                                            <strong>Ingredientes:</strong><br>
                                                            <?php foreach ($ingredientsByDish[$dish['id']] as $ingredient): ?>
                                                                <span><?php echo htmlspecialchars($ingredient['nome']); ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="content-section avaliacoes-section" id="avaliacoes-section">
                        <h2>Avaliações e Comentários</h2>

                        <?php if (is_null($restaurant['avg_total']) || $restaurant['total_avaliacoes'] == 0): ?>
                            <p>Sem avaliações ainda.</p>
                        <?php else: ?>
                            <p>Média: <?php echo number_format($restaurant['avg_total'], 1); ?>/5 (baseado em <?php echo $restaurant['total_avaliacoes']; ?> avaliações)</p>
                        <?php endif; ?>

                        <div class="comments-section">
                            <?php if (isset($_SESSION['id'])): ?>
                                <form action="detalhes_restaurante.php?id=<?php echo $restaurantId; ?>" method="POST" class="comment-form">
                                    <input type="hidden" name="submit_avaliacao" value="1">
                                    <input type="hidden" name="restaurant_id" value="<?php echo $restaurantId; ?>">

                                    <div class="rating-group">
                                        <div class="rating-item">
                                            <label>Comida</label>
                                            <div class="stars" data-rating="0">
                                                <input type="hidden" name="comida" class="rating-input" value="0">
                                                <span class="rating-star" data-value="1">★</span>
                                                <span class="rating-star" data-value="2">★</span>
                                                <span class="rating-star" data-value="3">★</span>
                                                <span class="rating-star" data-value="4">★</span>
                                                <span class="rating-star" data-value="5">★</span>
                                            </div>
                                        </div>
                                        <div class="rating-item">
                                            <label>Serviço</label>
                                            <div class="stars" data-rating="0">
                                                <input type="hidden" name="servico" class="rating-input" value="0">
                                                <span class="rating-star" data-value="1">★</span>
                                                <span class="rating-star" data-value="2">★</span>
                                                <span class="rating-star" data-value="3">★</span>
                                                <span class="rating-star" data-value="4">★</span>
                                                <span class="rating-star" data-value="5">★</span>
                                            </div>
                                        </div>
                                        <div class="rating-item">
                                            <label>Valor</label>
                                            <div class="stars" data-rating="0">
                                                <input type="hidden" name="valor" class="rating-input" value="0">
                                                <span class="rating-star" data-value="1">★</span>
                                                <span class="rating-star" data-value="2">★</span>
                                                <span class="rating-star" data-value="3">★</span>
                                                <span class="rating-star" data-value="4">★</span>
                                                <span class="rating-star" data-value="5">★</span>
                                            </div>
                                        </div>
                                        <div class="rating-item">
                                            <label>Ambiente</label>
                                            <div class="stars" data-rating="0">
                                                <input type="hidden" name="ambiente" class="rating-input" value="0">
                                                <span class="rating-star" data-value="1">★</span>
                                                <span class="rating-star" data-value="2">★</span>
                                                <span class="rating-star" data-value="3">★</span>
                                                <span class="rating-star" data-value="4">★</span>
                                                <span class="rating-star" data-value="5">★</span>
                                            </div>
                                        </div>
                                    </div>

                                    <textarea name="comentario" placeholder="Escreva seu comentário aqui..." required></textarea>
                                    <button type="submit">Enviar Avaliação</button>
                                </form>
                            <?php else: ?>
                                <p>Faça login para enviar uma avaliação.</p>
                            <?php endif; ?>

                            <div class="comment-list">
                                <?php if (empty($comments)): ?>
                                    <p class="no-comments">Ainda não há avaliações. Seja o primeiro a avaliar!</p>
                                <?php else: ?>
                                    <?php foreach ($comments as $comment): ?>
                                        <div class="comment-item">
                                            <div class="comment-header">
                                                <div class="comment-reviewer">
                                                    <strong><?php echo htmlspecialchars($comment['reviewer_name'] ?? 'Anonymous'); ?></strong>
                                                </div>
                                                <span class="comment-date">
                                                    <?php echo date('d/m/Y H:i', strtotime($comment['data_avaliacao'])); ?>
                                                </span>
                                            </div>
                                            <div class="comment-ratings">
                                                <span>Comida: <span class="stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <span class="rating-star <?php echo $i <= $comment['comida'] ? 'filled' : ''; ?>">★</span>
                                                    <?php endfor; ?>
                                                </span></span>
                                                <span>Serviço: <span class="stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <span class="rating-star <?php echo $i <= $comment['servico'] ? 'filled' : ''; ?>">★</span>
                                                    <?php endfor; ?>
                                                </span></span>
                                                <span>Valor: <span class="stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <span class="rating-star <?php echo $i <= $comment['valor'] ? 'filled' : ''; ?>">★</span>
                                                    <?php endfor; ?>
                                                </span></span>
                                                <span>Ambiente: <span class="stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <span class="rating-star <?php echo $i <= $comment['ambiente'] ? 'filled' : ''; ?>">★</span>
                                                    <?php endfor; ?>
                                                </span></span>
                                            </div>
                                            <div class="comment-content">
                                                <?php echo nl2br(htmlspecialchars($comment['comentario'])); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="reservation-section">
                <div class="reservation-header">
                    <h2>Reservar Mesa</h2>
                </div>
                <?php if ($totalReservations > 0): ?>
                    <span class="popularity-indicator">
                        <i class="fas fa-fire"></i>
                        Já tem <?php echo $totalReservations; ?> reservas hoje
                    </span>
                <?php endif; ?>
                <form action="detalhes_restaurante.php?id=<?php echo $restaurantId; ?>" method="POST" class="reservation-form">
                    <input type="hidden" name="submit_reserva" value="1">
                    <input type="hidden" name="restaurant_id" value="<?php echo $restaurantId; ?>">

                    <div class="form-group">
                        <label>Nome Completo</label>
                        <input type="text" name="nome_completo" required placeholder="Seu nome">
                    </div>

                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="tel" name="telefone" required placeholder="Seu telefone" pattern="[0-9]{9,15}">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required placeholder="Seu email">
                    </div>

                    <div class="form-group">
                        <label>Preferência de Contato</label>
                        <select name="preferencia_contato" required>
                            <option value="telefone">Telefone</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="email">Email</option>
                        </select>
                    </div>

                    <div class="form-group calendar-container">
                        <div class="input-with-icon">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="text" id="date-display" readonly value="<?php echo date('d/m/Y'); ?>">
                            <input type="hidden" name="data_reserva" id="data_reserva" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="calendar" id="calendar">
                            <div class="calendar-header">
                                <button type="button" id="prev-month"><</button>
                                <h4 id="month-year"></h4>
                                <button type="button" id="next-month">></button>
                            </div>
                            <div class="calendar-grid" id="calendar-grid">
                                <div class="day-header">S</div>
                                <div class="day-header">T</div>
                                <div class="day-header">Q</div>
                                <div class="day-header">Q</div>
                                <div class="day-header">S</div>
                                <div class="day-header">S</div>
                                <div class="day-header">D</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-with-icon">
                            <i class="fas fa-clock"></i>
                            <select name="hora_reserva" required>
                                <?php
                                for ($hour = 12; $hour <= 23; $hour++) {
                                    foreach (['00', '30'] as $minute) {
                                        $time = sprintf("%02d:%s:00", $hour, $minute);
                                        echo "<option value='$time'>$time</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-with-icon">
                            <i class="fas fa-users"></i>
                            <select name="num_pessoas" required>
                                <?php
                                for ($i = 1; $i <= 10; $i++) {
                                    echo "<option value='$i'>$i " . ($i == 1 ? "pessoa" : "pessoas") . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="reserve-button">Reservar Agora</button>
                </form>
            </div>
        </div>

        <div id="imageModal" class="modal">
            <span class="close">×</span>
            <div class="modal-content">
                <?php foreach ($images as $image): ?>
                    <div class="image-square">
                        <img src="<?php echo htmlspecialchars($image['caminho_imagem']); ?>" 
                            alt="Imagem do restaurante <?php echo htmlspecialchars($restaurant['nome_empresa'] ?? ''); ?>" 
                            loading="lazy">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        const viewAllImages = document.getElementById('viewAllImages');
        const modal = document.getElementById('imageModal');
        const closeModal = document.getElementsByClassName('close')[0];

        function openModal(index = 0) {
            modal.style.display = 'block';
            document.body.style.overflow = 'auto'; 
        }

        function closeModalFn() {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto'; 
        }

        viewAllImages.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });

        closeModal.addEventListener('click', closeModalFn);

        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModalFn();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.style.display === 'block') {
                closeModalFn();
            }
        });

        document.querySelector('.reservation-form').addEventListener('submit', function(e) {
            const dateInput = this.querySelector('#data_reserva');
            const selectedDate = new Date(dateInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const telefone = this.querySelector('input[name="telefone"]').value;
            if (!/^[0-9]{9,15}$/.test(telefone)) {
                e.preventDefault();
                alert('Por favor, insira um número de telefone válido (9 a 15 dígitos).');
                return;
            }

            if (selectedDate < today) {
                e.preventDefault();
                alert('Por favor, selecione uma data válida (a partir de hoje).');
            }
        });

        document.querySelector('.comment-form').addEventListener('submit', function(e) {
            const comentario = this.querySelector('textarea[name="comentario"]').value.trim();
            if (!comentario) {
                e.preventDefault();
                alert('Por favor, escreva um comentário.');
            }
        });

        document.querySelectorAll('.rating-item .stars').forEach(starsContainer => {
            const stars = starsContainer.querySelectorAll('.rating-star');
            const ratingInput = starsContainer.querySelector('.rating-input');

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = parseInt(this.dataset.value);
                    ratingInput.value = rating;

                    stars.forEach(s => {
                        if (parseInt(s.dataset.value) <= rating) {
                            s.classList.add('filled');
                        } else {
                            s.classList.remove('filled');
                        }
                    });
                });

                star.addEventListener('mouseover', function() {
                    const rating = parseInt(this.dataset.value);
                    stars.forEach(s => {
                        if (parseInt(s.dataset.value) <= rating) {
                            s.classList.add('filled');
                        } else {
                            s.classList.remove('filled');
                        }
                    });
                });

                starsContainer.addEventListener('mouseout', function() {
                    const currentRating = parseInt(ratingInput.value);
                    stars.forEach(s => {
                        if (parseInt(s.dataset.value) <= currentRating) {
                            s.classList.add('filled');
                        } else {
                            s.classList.remove('filled');
                        }
                    });
                });
            });
        });

        const dateDisplay = document.getElementById('date-display');
        const calendar = document.getElementById('calendar');
        const calendarGrid = document.getElementById('calendar-grid');
        const monthYear = document.getElementById('month-year');
        const prevMonth = document.getElementById('prev-month');
        const nextMonth = document.getElementById('next-month');
        const dataReservaInput = document.getElementById('data_reserva');

        let currentDate = new Date();
        let selectedDate = new Date();

        const occupationData = <?php echo json_encode($occupationData); ?>;

        function renderCalendar() {
            calendarGrid.innerHTML = `
                <div class="day-header">S</div>
                <div class="day-header">T</div>
                <div class="day-header">Q</div>
                <div class="day-header">Q</div>
                <div class="day-header">S</div>
                <div class="day-header">S</div>
                <div class="day-header">D</div>
            `;

            const firstDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            const lastDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            monthYear.textContent = firstDayOfMonth.toLocaleString('pt-PT', { month: 'long', year: 'numeric' }).toUpperCase();

            for (let i = 0; i < (firstDayOfMonth.getDay() + 6) % 7; i++) {
                calendarGrid.innerHTML += `<div class="day disabled"></div>`;
            }

            for (let day = 1; day <= lastDayOfMonth.getDate(); day++) {
                const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
                const dateString = date.toISOString().split('T')[0];
                const isDisabled = date < today;
                const occupation = occupationData[dateString] || 0;

                calendarGrid.innerHTML += `
                    <div class="day ${isDisabled ? 'disabled' : ''}" data-date="${dateString}">
                        ${day}
                        ${occupation >= 30 ? `<span class="occupation">-${Math.round(occupation)}%</span>` : ''}
                    </div>
                `;
            }

            document.querySelectorAll('.calendar-grid .day:not(.disabled)').forEach(day => {
                day.addEventListener('click', function() {
                    selectedDate = new Date(this.dataset.date);
                    dateDisplay.value = selectedDate.toLocaleDateString('pt-PT');
                    dataReservaInput.value = this.dataset.date;
                    calendar.style.display = 'none';
                });
            });
        }

        dateDisplay.addEventListener('click', () => {
            calendar.style.display = calendar.style.display === 'block' ? 'none' : 'block';
            renderCalendar();
        });

        prevMonth.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });

        nextMonth.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });

        document.addEventListener('click', (e) => {
            if (!calendar.contains(e.target) && e.target !== dateDisplay) {
                calendar.style.display = 'none';
            }
        });

        renderCalendar();

        function showSection(event, sectionId) {
            event.preventDefault();
            
            const navLinks = document.querySelectorAll('.nav-menu a');
            const sections = document.querySelectorAll('.content-section');
            const reservationSection = document.querySelector('.reservation-section');
            
            navLinks.forEach(l => l.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));
            
            document.querySelector(`.nav-menu a[data-section="${sectionId}"]`).classList.add('active');
            document.getElementById(sectionId).classList.add('active');
            
            // Manter a seção de reserva sempre visível, independentemente da seção ativa
            reservationSection.style.display = 'block';
            
            return false;
        }
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>