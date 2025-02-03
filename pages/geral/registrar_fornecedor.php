<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Fornecedor</title>
    <style>
        * {
            margin: 10px;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .register-container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            background-color: #ffffff;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            border-radius: 15px;
            padding: 40px;
        }

        form {
            display: flex;
            width: 100%;
            justify-content: space-between;
            align-items: stretch;
        }

        .form-column {
            width: 100%;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .form-column h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2rem;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-size: 1rem;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: none;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.05);
            color: #333;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            background: rgba(0, 0, 0, 0.1);
            outline: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        button.register-button {
            width: 100%;
            padding: 12px;
            background-color: #ff4b2b;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: auto;
        }

        button.register-button:hover {
            background-color: #ff6b4b;
        }

        p.error {
            color: #f44336;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .links {
            text-align: center;
            margin-top: 10px;
        }

        a {
            color: #6e45e2;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #88d3ce;
        }

        @media (max-width: 768px) {
            form {
                flex-direction: column;
            }

            .form-column {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <form method="post" action="">
            <div class="form-column">
                <h2>Registrar Fornecedor</h2>
                <label for="nome_representante">Nome do Representante:</label>
                <input type="text" id="nome_representante" name="nome_representante" required>

                <label for="telefone_representante">Telefone do Representante:</label>
                <input type="text" id="telefone_representante" name="telefone_representante" required>

                <label for="email_representante">Email do Representante:</label>
                <input type="email" id="email_representante" name="email_representante" required>

                <label for="nif_empresa">NIF da Empresa:</label>
                <input type="text" id="nif_empresa" name="nif_empresa" required>

                <label for="morada_sede">Morada da Sede:</label>
                <input type="text" id="morada_sede" name="morada_sede" required>

                <label for="codigo_postal_fornecedor">Código Postal:</label>
                <input type="text" id="codigo_postal_fornecedor" name="codigo_postal_fornecedor" required>

                <label for="distrito_fornecedor">Distrito:</label>
                <input type="text" id="distrito_fornecedor" name="distrito_fornecedor" required>

                <label for="pais_fornecedor">País:</label>
                <input type="text" id="pais_fornecedor" name="pais_fornecedor" required>

                <label for="iban_fornecedor">IBAN:</label>
                <input type="text" id="iban_fornecedor" name="iban_fornecedor" required>

                <button type="submit" class="register-button" name="registrar_fornecedor">Registrar Fornecedor</button>
            </div>
        </form>
    </div>
</body>
</html>
