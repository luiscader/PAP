<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';
include 'navbar.php';

$nome_usuario = '';
$tipo_usuario = '';

if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $tipo_usuario = $_SESSION['tipo'];
    $id_restaurante = $_SESSION['id_restaurante'] ?? null;

    $sql = "SELECT nome FROM Utilizador WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($nome);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            $nome_usuario = htmlspecialchars($nome);
        }
        $stmt->close();
    } else {
        $nome_usuario = "Erro ao buscar nome";
    }
}

$sql_categories = "SELECT id, nome, foto_categoria_link FROM tipocozinha ORDER BY nome";
$result_categories = $conn->query($sql_categories);
$categories = [];

if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$distrito = isset($_GET['district']) && $_GET['district'] !== '' ? $_GET['district'] : null;

$sql = "
    SELECT 
        r.id, 
        r.nome_empresa, 
        i.caminho_imagem, 
        tc.nome AS tipo_cozinha,
        r.codigo_postal, 
        r.distrito, 
        r.intervalo_precos, 
        COALESCE(AVG((a.comida + a.servico + a.valor + a.ambiente) / 4), 0) AS media_avaliacoes
    FROM restaurante r
    LEFT JOIN imagem_restaurante i ON r.id = i.id_restaurante
    LEFT JOIN restaurante_tipocozinha rtc ON r.id = rtc.id_restaurante
    LEFT JOIN tipocozinha tc ON rtc.id_tipo_cozinha = tc.id
    LEFT JOIN avaliacoes a ON r.id = a.id_restaurante
    WHERE r.status = 'ativo'
";

if (!empty($searchTerm)) {
    $sql .= " AND (r.nome_empresa LIKE ? OR tc.nome LIKE ?)";
}

if ($distrito) {
    $sql .= " AND r.distrito = ?";
}

$sql .= "
    GROUP BY r.id, r.nome_empresa, i.caminho_imagem, tc.nome, r.codigo_postal, r.distrito, r.intervalo_precos
";

$stmt = $conn->prepare($sql);

if (!empty($searchTerm) && $distrito) {
    $searchParam = '%' . $searchTerm . '%';
    $stmt->bind_param("sss", $searchParam, $searchParam, $distrito);
} elseif (!empty($searchTerm)) {
    $searchParam = '%' . $searchTerm . '%';
    $stmt->bind_param("ss", $searchParam, $searchParam);
} elseif ($distrito) {
    $stmt->bind_param("s", $distrito);
}

$stmt->execute();
$result = $stmt->get_result();

$restaurantes = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_restaurante = $row['id'];
        $restaurantes[$id_restaurante] = [
            'nome_empresa' => $row['nome_empresa'],
            'imagem' => !empty($row['caminho_imagem']) ? $row['caminho_imagem'] : null,
            'tipo_cozinha' => $row['tipo_cozinha'] ?? 'Não especificado',
            'media_avaliacoes' => round($row['media_avaliacoes'], 1),
            'codigo_postal' => $row['codigo_postal'] ?? 'N/A',
            'distrito' => $row['distrito'] ?? 'N/A',
            'intervalo_precos' => $row['intervalo_precos'] ?? 'N/A',
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restomate - Bem-vindo</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../geral/assets/images/favicon.png" />
</head>
<body>


    <div class="categories-carousel">
        <div class="carousel-container">
            <button class="carousel-button prev">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="carousel-track">
                <?php foreach ($categories as $category): ?>
                    <div class="category-item">
                        <a href="?search=<?php echo urlencode($category['nome']); ?>" class="category-link">
                            <div class="category-image">
                                <?php if (!empty($category['foto_categoria_link'])): ?>
                                    <img src="<?php echo htmlspecialchars($category['foto_categoria_link']); ?>" alt="<?php echo htmlspecialchars($category['nome']); ?>">
                                <?php else: ?>
                                    <img src="assets/images/default-category.jpg" alt="<?php echo htmlspecialchars($category['nome']); ?>">
                                <?php endif; ?>
                            </div>
                            <span class="category-name"><?php echo htmlspecialchars($category['nome']); ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-button next">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <?php if (empty($searchTerm) && empty($distrito)): ?>
        <h2>Restaurantes Populares</h2>
    <?php endif; ?>

    <?php if (!empty($searchTerm) || !empty($distrito)): ?>
        <div class="search-results">
            <h2>Resultados para: 
                <?php 
                if (!empty($searchTerm) && !empty($distrito)) {
                    echo "\"".htmlspecialchars($searchTerm)."\" em ".htmlspecialchars($distrito);
                } elseif (!empty($searchTerm)) {
                    echo "\"".htmlspecialchars($searchTerm)."\"";
                } elseif (!empty($distrito)) {
                    echo "Distrito: ".htmlspecialchars($distrito);
                }
                ?>
            </h2>
        </div>
    <?php endif; ?>

    <main class="main-content">
        <?php
        if (!empty($restaurantes)) {
            foreach ($restaurantes as $id => $restaurante) {
                echo "<a href='detalhes_restaurante.php?id=" . $id . "' class='restaurant-card'>";
                echo "<div class='image-container'>";
                echo !empty($restaurante['imagem'])
                    ? "<img src='" . htmlspecialchars($restaurante['imagem']) . "' alt='Imagem do restaurante'>"
                    : "<img src='assets/images/default-image.jpg' alt='Imagem não disponível'>";
                echo "</div>";
                echo "<div class='info'>";
                echo "<div class='header'>";
                echo "<h2 class='restaurant-name'>" . htmlspecialchars($restaurante['nome_empresa']) . "</h2>";
                echo "<div class='rating'>";
                echo "<img src='assets/images/star.png' alt='Estrela' class='star-icon'>";
                echo "<span class='rating-value'>" . $restaurante['media_avaliacoes'] . " / 5</span>";
                echo "</div>";
                echo "</div>";
                echo "<p class='location'>" . htmlspecialchars($restaurante['codigo_postal']) . ", " . htmlspecialchars($restaurante['distrito']) . "</p>";
                echo "<p class='price-range'>Preço médio: " . htmlspecialchars($restaurante['intervalo_precos']) . " €</p>";
                echo "<p><span class='category'>" . htmlspecialchars($restaurante['tipo_cozinha']) . "</span></p>";
                echo "</div>";
                echo "</a>";
            }
        } else {
            echo "<p>Nenhum restaurante encontrado";
            if (!empty($searchTerm)) {
                echo " para o termo '<strong>" . htmlspecialchars($searchTerm) . "</strong>'";
            }
            if (!empty($distrito)) {
                echo " no distrito '<strong>" . htmlspecialchars($distrito) . "</strong>'";
            }
            echo ".</p>";
        }
        ?>
    </main>

    <?php include 'footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const track = document.querySelector('.carousel-track');
        const prevButton = document.querySelector('.carousel-button.prev');
        const nextButton = document.querySelector('.carousel-button.next');
        let position = 0;

        function updateButtons() {
            prevButton.style.display = position === 0 ? 'none' : 'flex';
            nextButton.style.display = Math.abs(position) >= track.scrollWidth - track.parentElement.clientWidth ? 'none' : 'flex';
        }

        prevButton.addEventListener('click', () => {
            const itemWidth = track.querySelector('.category-item').offsetWidth;
            const containerWidth = track.parentElement.clientWidth;
            const visibleItems = Math.floor(containerWidth / itemWidth);
            position += visibleItems * itemWidth;
            position = Math.min(position, 0);
            track.style.transform = `translateX(${position}px)`;
            updateButtons();
        });

        nextButton.addEventListener('click', () => {
            const itemWidth = track.querySelector('.category-item').offsetWidth;
            const containerWidth = track.parentElement.clientWidth;
            const visibleItems = Math.floor(containerWidth / itemWidth);

            const maxScroll = -(track.scrollWidth - containerWidth);
            position -= visibleItems * itemWidth;

            if (Math.abs(position) > Math.abs(maxScroll)) {
                position = maxScroll;
            }

            track.style.transform = `translateX(${position}px)`;
            updateButtons();
        });

        updateButtons();
        window.addEventListener('resize', updateButtons);
    });
    </script>
</body>
</html>