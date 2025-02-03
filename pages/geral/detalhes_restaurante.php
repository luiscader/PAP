<?php
include 'C:/wamp64/www/PAP/includes/config.php';
include 'navbar.php';

// Fetch restaurant data dynamically
$queryRestaurants = "SELECT id, nome_empresa FROM restaurante";
$resultRestaurants = $conn->query($queryRestaurants);

// Define default restaurant ID
$restaurantId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Fetch specific restaurant data
$query = "
    SELECT r.nome_empresa, r.morada, r.codigo_postal, r.distrito, r.intervalo_precos,
           GROUP_CONCAT(DISTINCT tc.nome SEPARATOR ', ') AS tipos_cozinha,
           AVG((a.comida + a.servico + a.valor + a.ambiente) / 4) AS avg_total
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

// Fetch all images associated with the restaurant
$queryImages = "SELECT caminho_imagem FROM imagem_restaurante WHERE id_restaurante = ?";
$stmtImages = $conn->prepare($queryImages);
$stmtImages->bind_param('i', $restaurantId);
$stmtImages->execute();
$resultImages = $stmtImages->get_result();
$images = $resultImages->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
    <title><?php echo htmlspecialchars($restaurant['nome_empresa']); ?> - Informação</title>
    <style>
/* Global Reset and Fonts */
body {
    margin: 0;
    font-family: Arial, sans-serif;
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

/* Container Styles */
.restaurant-info {
    overflow: hidden;
    margin-left: 150px;
    margin-right: 150px;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    margin-top: 50px;
}

/* Header Styles */
.restaurant-info h1 {
    font-size: 2rem;
    margin-bottom: 10px;
}

/* Paragraph Styles */
.restaurant-info p, .restaurant-details span {
    margin: 5px 0;
    font-size: 1.1rem;
}

/* Details Styles */
.restaurant-details {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Rating Styles */
.ratings {
    margin: 20px 0;
}
.ratings span {
    font-size: 1.2rem;
}

/* Gallery Styles */
.gallery {
    margin-left: 50px;
    margin-right: 50px;
}
.gallery h2 {
    font-size: 1.8rem;
    margin-bottom: 25px;
    color: #333;
    font-weight: 600;
    margin-left: -10px;
}

/* Image Grid Styles */
.image-grid {
    display: grid;
    grid-template-columns: repeat(4, 180px); /* 4 colunas de 180px cada */
    gap: 8px; /* Espaço entre os quadrados */
    overflow: hidden;
}

/* Image Square Styles */
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

.image-square div{
    width: 180px;
    height: 180px;
    border-radius: 20px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    background-size: cover;
    background-position: center;
    margin: 8px;
    margin-top:10px;
}
.image-square img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

/* View All Button */
.view-all-square {
    background-color:#1a4369;
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
    
}

.view-all-square:hover {
    background: linear-gradient(45deg, #e2e8f0, #cbd5e0);
    transform: translateY(-5px);
}
.view-all-square a {
    color: #2b6cb0;
    font-size: 1.2rem;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
}
.view-all-square a:hover {
    color: #1a4369;
}
.view-all-square a i {
    font-size: 1.4rem;
}

.gallery .image-grid {
    display: block;
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

/* Modal Styles */
.modal {
    backdrop-filter: blur(5px);
    background-color: rgba(0, 0, 0, 0.85);
    padding: 40px;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: none;
    z-index: 1000;
    overflow-y: auto;
}
.modal-content {
    position: relative;
    background: transparent;
    max-width: 1200px;
    margin: 40px auto;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
    max-height: 80vh;
    overflow-y: auto;
}
.modal .image-square {
    background: #fff;
    padding: 10px;
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
}
.close:hover {
    opacity: 1;
    color: #fff;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .image-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    .modal-content {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
    }
}
@media (max-width: 480px) {
    .image-grid {
        grid-template-columns: 1fr;
    }
    .modal {
        padding: 20px;
    }
}

    </style>
</head>
<body>
    <div class="restaurant-info">
        <h1><?php echo htmlspecialchars($restaurant['nome_empresa']); ?></h1>
        <div class="restaurant-details">
            <span><strong>Morada:</strong> <?php echo htmlspecialchars($restaurant['morada']); ?>,</span>
            <span><?php echo htmlspecialchars($restaurant['codigo_postal']); ?>,</span>
            <span><?php echo htmlspecialchars($restaurant['distrito']); ?></span>
        </div>
        <div class="restaurant-details">
            <span><strong>Tipos de Cozinha:</strong> <?php echo htmlspecialchars($restaurant['tipos_cozinha']); ?></span>
        </div>
        <div class="restaurant-details">
            <span><strong>Preço Médio por Refeição:</strong> <?php echo htmlspecialchars($restaurant['intervalo_precos']); ?>€</span>
        </div>
        <div class="ratings">
            <span><strong>Avaliações:</strong> 
                <?php
                if (is_null($restaurant['avg_total'])) {
                    echo "Sem Avaliações";
                } else {
                    echo number_format($restaurant['avg_total'], 1) . "/5";
                }
                ?>
            </span>
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
                             alt="Imagem do restaurante <?php echo htmlspecialchars($restaurant['nome_empresa']); ?>" 
                             loading="lazy">
                    </div>
                <?php 
                    endif;
                endforeach; 
                ?>
                <div class="view-all-square">
                    <a href="#" id="viewAllImages">
                        <i class="fas fa-images"></i>
                        Ver Galeria Completa
                    </a>
                </div>
            </div>
        </div>
        <div id="imageModal" class="modal">
            <span class="close">&times;</span>
            <div class="modal-content">
                <?php foreach ($images as $image): ?>
                    <div class="image-square">
                        <img src="<?php echo htmlspecialchars($image['caminho_imagem']); ?>" 
                             alt="Imagem do restaurante <?php echo htmlspecialchars($restaurant['nome_empresa']); ?>" 
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
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>
