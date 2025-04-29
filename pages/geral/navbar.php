<nav class="navbar">
    <div class="logo">
        <a href="index.php">
            <img src="assets/images/logo.png" alt="Logo">
            <span>Restomate</span>
        </a>
    </div>

    <form class="search-bar" action="index.php" method="GET">
        <div class="location-select">
            <i class="fas fa-location-arrow"></i>
            <select name="district" id="district">
                <option value="">Distritos</option>
                <?php
                $sql_enum = "SHOW COLUMNS FROM restaurante WHERE Field = 'distrito'";
                $result_enum = $conn->query($sql_enum);
                if ($result_enum && $row_enum = $result_enum->fetch_assoc()) {
                    $enum_str = $row_enum['Type'];
                    preg_match("/^enum\((.*)\)$/", $enum_str, $matches);
                    $enum_values = str_getcsv($matches[1], ',', "'");
                    foreach ($enum_values as $value) {
                        $selected = (isset($_GET['district']) && $_GET['district'] === $value) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($value) . "' $selected>" . htmlspecialchars($value) . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <input type="text" placeholder="Cozinha, nome do restaurante..." name="search" value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <button type="submit">PESQUISA</button>
    </form>

    <div class="user-actions">
        <?php if (isset($_SESSION['id'])): ?>
            <a href="perfil.php" class="profile-link">
                <img src="assets/images/profile.png" alt="Profile" class="profile-img">
            </a>
        <?php else: ?>
            <a href="login.php" class="btn login">Login</a>
            <a href="signup.php" class="btn signup">Sign up</a>
        <?php endif; ?>
    </div>
</nav>