<?php
include 'C:/wamp64/www/PAP/includes/config.php';
include 'navbar.php';

$id_restaurante = $_GET['id'];

// Consultar dados do restaurante
$sql_restaurante = "SELECT * FROM restaurante WHERE id = ?";
$stmt_restaurante = $conn->prepare($sql_restaurante);
$stmt_restaurante->bind_param("i", $id_restaurante);
$stmt_restaurante->execute();
$result_restaurante = $stmt_restaurante->get_result();
$row_restaurante = $result_restaurante->fetch_assoc();

// Consultar imagens do restaurante
$sql_imagens = "SELECT * FROM imagem_restaurante WHERE id_restaurante = ?";
$stmt_imagens = $conn->prepare($sql_imagens);
$stmt_imagens->bind_param("i", $id_restaurante);
$stmt_imagens->execute();
$result_imagens = $stmt_imagens->get_result();

// Consultar ementa do restaurante
$sql_ementa = "SELECT * FROM pratos WHERE id_restaurante = ?";
$stmt_ementa = $conn->prepare($sql_ementa);
$stmt_ementa->bind_param("i", $id_restaurante);
$stmt_ementa->execute();
$result_ementa = $stmt_ementa->get_result();

// Consultar avaliações médias por critério
$sql_avaliacoes = "
    SELECT 
        AVG(comida) as media_comida, 
        AVG(servico) as media_servico, 
        AVG(valor) as media_valor, 
        AVG(ambiente) as media_ambiente
    FROM avaliacoes 
    WHERE id_restaurante = ?";
$stmt_avaliacoes = $conn->prepare($sql_avaliacoes);
$stmt_avaliacoes->bind_param("i", $id_restaurante);
$stmt_avaliacoes->execute();
$result_avaliacoes = $stmt_avaliacoes->get_result();
$row_avaliacoes = $result_avaliacoes->fetch_assoc();

// Consultar tipos de cozinha (gastronomia)
$sql_gastronomia = "
    SELECT t.nome 
    FROM tipocozinha t
    JOIN restaurante_tipocozinha rt ON t.id = rt.id_tipo_cozinha
    WHERE rt.id_restaurante = ?";
$stmt_gastronomia = $conn->prepare($sql_gastronomia);
$stmt_gastronomia->bind_param("i", $id_restaurante);
$stmt_gastronomia->execute();
$result_gastronomia = $stmt_gastronomia->get_result();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row_restaurante['nome_empresa']); ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/styles.css">
    <style>
        :root {
            --primary: #2D3748;
            --secondary: #4A5568;
            --accent: #ED8936;
            --background: #F7FAFC;
            --white: #FFFFFF;
            --gray-100: #EDF2F7;
            --gray-200: #E2E8F0;
            --gray-300: #CBD5E0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--primary);
            line-height: 1.6;
        }

        /* Header Styles */
        .hero {
            position: relative;
            height: 400px;
            background-color: var(--primary);
            color: var(--white);
            overflow: hidden;
        }

        .hero-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 2rem;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

/* Update Gallery Styles */
.gallery-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    grid-template-rows: 1fr 1fr;
    gap: 1rem;
    margin: 2rem 0;
    height: 600px;
}

.gallery-main {
    grid-column: 1;
    grid-row: 1 / span 2;
    height: 100%;
}

.gallery-side {
    grid-column: 2;
    grid-row: 1 / span 2;
    display: grid;
    grid-template-rows: repeat(2, 1fr);
    gap: 1rem;
}

.gallery-image {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    height: 100%;
    background-color: #000;
}

.gallery-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.9;
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.gallery-image:hover img {
    transform: scale(1.05);
    opacity: 1;
}


/* Responsive adjustments */
@media (max-width: 768px) {
    .gallery-grid {
        height: auto;
        grid-template-columns: 1fr;
        grid-template-rows: auto auto auto;
    }
    
    .gallery-main {
        grid-column: 1;
        grid-row: 1;
        min-height: 300px;
    }
    
    .gallery-side {
        grid-column: 1;
        grid-row: 2;
        grid-template-columns: repeat(2, 1fr);
        grid-template-rows: 1fr;
    }
}

        /* Menu Section */
        .menu-section {
            background: var(--white);
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: var(--shadow);
        }

        .menu-item {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            transition: background-color 0.2s ease;
        }

        .menu-item:last-child {
            border-bottom: none;
        }

        .menu-item:hover {
            background-color: var(--gray-100);
        }

        .menu-item h3 {
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .menu-price {
            color: var(--accent);
            font-weight: 600;
        }

        .menu-ingredients {
            color: var(--secondary);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        /* Info Cards */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        .info-card {
            background: var(--white);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: transform 0.2s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }

        .info-card h2 {
            color: var(--primary);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent);
        }

        .rating-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
        }

        .rating-value {
            font-weight: 600;
            color: var(--accent);
        }

        /* Back Button */
        .back-button {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: var(--accent);
            color: var(--white);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            margin-top: 2rem;
        }

        .back-button:hover {
            background-color: #DD6B20;
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero {
                height: 300px;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
            }

            .gallery-main, .gallery-side {
                grid-column: span 12;
            }

            .gallery-main {
                height: 300px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 1rem;
            }

            .menu-section, .info-card {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    
    <div class="hero">
        <div class="hero-content">
            <h1><?php echo htmlspecialchars($row_restaurante['nome_empresa']); ?></h1>

        <!-- Morada, Código Postal e Distrito -->
        <p><strong>Morada:</strong> <?php echo htmlspecialchars($row_restaurante['morada']); ?></p>
        <p><strong>Código Postal:</strong> <?php echo htmlspecialchars($row_restaurante['codigo_postal']); ?></p>
        <p><strong>Distrito:</strong> <?php echo htmlspecialchars($row_restaurante['distrito']); ?></p>

        <!-- Tipo de Cozinha e Preço Médio -->
        <p>
            <strong>Tipo de Cozinha:</strong> 
            <?php
            if ($result_gastronomia->num_rows > 0) {
                $gastronomia = [];
                while ($row_gastronomia = $result_gastronomia->fetch_assoc()) {
                    $gastronomia[] = htmlspecialchars($row_gastronomia['nome']);
                }
                echo implode(", ", $gastronomia);
            } else {
                echo "Não disponível";
            }
            ?>
        </p>
        <p><strong>Preço Médio:</strong> 
            <?php echo isset($row_restaurante['intervalo_precos']) ? htmlspecialchars($row_restaurante['intervalo_precos']) : 'Não disponível'; ?>€
        </p>

        <!-- Média das Avaliações -->
        <h2>Média das Avaliações</h2>
        <ul>
            <li><strong>Comida:</strong> <?php echo isset($row_avaliacoes['media_comida']) ? round($row_avaliacoes['media_comida'], 1) : 'N/A'; ?> / 5</li>
            <li><strong>Serviço:</strong> <?php echo isset($row_avaliacoes['media_servico']) ? round($row_avaliacoes['media_servico'], 1) : 'N/A'; ?> / 5</li>
            <li><strong>Valor:</strong> <?php echo isset($row_avaliacoes['media_valor']) ? round($row_avaliacoes['media_valor'], 1) : 'N/A'; ?> / 5</li>
            <li><strong>Ambiente:</strong> <?php echo isset($row_avaliacoes['media_ambiente']) ? round($row_avaliacoes['media_ambiente'], 1) : 'N/A'; ?> / 5</li>
        </ul>
        </div>
    </div>

    <div class="container">
        <div class="gallery-grid">
        <?php
        $total_imagens = $result_imagens->num_rows;
        $contador = 0;

        if ($total_imagens > 0) {
            while ($row_imagem = $result_imagens->fetch_assoc()) {
                $contador++;
                if ($contador == 1) {
                    // Main large image
                    echo "<div class='gallery-main'>
                            <div class='gallery-image'>
                                <img src='" . htmlspecialchars($row_imagem['caminho_imagem']) . "' 
                                    alt='Imagem Principal do Restaurante'>
                            </div>
                        </div>";
                    echo "<div class='gallery-side'>";
                } elseif ($contador <= 3) {
                    // Side images
                    echo "<div class='gallery-image'>
                            <img src='" . htmlspecialchars($row_imagem['caminho_imagem']) . "' 
                                alt='Imagem do Restaurante'>
                        </div>";
                }
                if ($contador == 3) {
                    echo "</div>";
                }
            }
        } else {
            echo "<p>Nenhuma imagem disponível.</p>";
        }
        ?>
    </div>

        <div class="menu-section">
            <h2>Nossa Ementa</h2>
            <?php
            if ($result_ementa->num_rows > 0) {
                while($row_ementa = $result_ementa->fetch_assoc()) {
                    echo "<div class='menu-item'>
                            <h3>" . htmlspecialchars($row_ementa['nome']) . 
                            "<span class='menu-price'> " . number_format($row_ementa['preco'], 2) . "€</span></h3>";
                    if (!empty($row_ementa['ingredientes'])) {
                        echo "<div class='menu-ingredients'>" . htmlspecialchars($row_ementa['ingredientes']) . "</div>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<p>Ementa não disponível.</p>";
            }
            ?>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h2>Avaliações</h2>
                <div class="rating-item">
                    <span>Comida</span>
                    <span class="rating-value"><?php echo isset($row_avaliacoes['media_comida']) ? round($row_avaliacoes['media_comida'], 1) : 'N/A'; ?> / 5</span>
                </div>
                <div class="rating-item">
                    <span>Serviço</span>
                    <span class="rating-value"><?php echo isset($row_avaliacoes['media_servico']) ? round($row_avaliacoes['media_servico'], 1) : 'N/A'; ?> / 5</span>
                </div>
                <div class="rating-item">
                    <span>Valor</span>
                    <span class="rating-value"><?php echo isset($row_avaliacoes['media_valor']) ? round($row_avaliacoes['media_valor'], 1) : 'N/A'; ?> / 5</span>
                </div>
                <div class="rating-item">
                    <span>Ambiente</span>
                    <span class="rating-value"><?php echo isset($row_avaliacoes['media_ambiente']) ? round($row_avaliacoes['media_ambiente'], 1) : 'N/A'; ?> / 5</span>
                </div>
            </div>

            <div class="info-card">
                <h2>Sobre o Restaurante</h2>
                <p><strong>Intervalo de Preços:</strong> <?php echo isset($row_restaurante['intervalo_precos']) ? htmlspecialchars($row_restaurante['intervalo_precos']) : 'Não disponível'; ?>€</p>
                <p><strong>Tipos de Cozinha:</strong> 
                    <?php
                    if ($result_gastronomia->num_rows > 0) {
                        $gastronomia = [];
                        while ($row_gastronomia = $result_gastronomia->fetch_assoc()) {
                            $gastronomia[] = htmlspecialchars($row_gastronomia['nome']);
                        }
                        echo implode(", ", $gastronomia);
                    } else {
                        echo "Não disponível";
                    }
                    ?>
                </p>
            </div>

            <div class="info-card">
                <h2>Contato</h2>
                <p><strong>Email:</strong> <?php echo isset($row_restaurante['email_contato']) ? htmlspecialchars($row_restaurante['email_contato']) : 'Não disponível'; ?></p>
                <p><strong>Telefone:</strong> <?php echo isset($row_restaurante['numero_contato']) ? htmlspecialchars($row_restaurante['numero_contato']) : 'Não disponível'; ?></p>
            </div>
        </div>

        <a href="index.php" class="back-button">Voltar</a>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>