<?php
session_start();

// Function to handle user registration
function registerUser($username, $password, $confirmPassword, $accountType) {
    // Perform validation on the registration data

    // Check if passwords match
    if ($password !== $confirmPassword) {
        return "Passwords do not match";
    }

    // Check if username already exists in the database
    require_once "config.php";
    $checkUserQuery = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $checkUserQuery);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        return "Username already exists";
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Store the user registration data in the database
    $sql = "INSERT INTO users (username, password, account_type) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $username, $hashedPassword, $accountType);
    if (mysqli_stmt_execute($stmt)) {
        // Registration successful, redirect to login page
        header("Location: login.php");
        exit();
    } else {
        // Registration failed, display error message
        echo "Registration Error: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// Function to handle user login
function loginUser($username, $password) {
    // TODO: Authenticate the user against the stored user data in the database or any other storage
    require_once "config.php";

    // Prepare the SQL statement
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Verify the password
        if (password_verify($password, $row['password'])) {
            // Password is correct, set session variables
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $row['id'];

            // Redirect the user to the desired page after successful login
            header("Location: dashboard.php");
            exit();
        } else {
            // Password is incorrect, display error message
            $_SESSION['nao_autenticado'] = true;
            header("Location: login.php");
            exit();
        }
    } else {
        // User does not exist, display error message
        $_SESSION['nao_autenticado'] = true;
        header("Location: login.php");
        exit();
    }

    // Close the statement
    mysqli_stmt_close($stmt);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the form is for registration
    if (isset($_POST['register'])) {
        // Get the registration form data
        $username = $_POST['usuario'];
        $password = $_POST['senha'];
        $confirmPassword = $_POST['confirmasenha'];
        $accountType = $_POST['answer'];

        // Call the registerUser function
        $registrationResult = registerUser($username, $password, $confirmPassword, $accountType);

        // Check the registration result
        if ($registrationResult !== true) {
            // Registration failed, display error message
            echo "Registration Error: " . $registrationResult;
        }
    }

    // Check if the form is for login
    if (isset($_POST['login'])) {
        // Get the login form data
        $username = $_POST['usuario'];
        $password = $_POST['senha'];

        // Call the loginUser function
        loginUser($username, $password);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AppValue</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
    <link rel="stylesheet" href="css/bulma.min.css" />
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".toggle-form").click(function() {
                $(".login-form, .register-form").toggle();
            });
        });
        
    </script>
</head>

<body>
    <section class="hero is-success is-fullheight">
        <div class="hero-body">
            <div class="container has-text-centered">
                <div class="column is-4 is-offset-4">
                    <h3 class="title has-text-black">Faça o Login</h3>
                    <?php
                    if(isset($_SESSION['nao_autenticado'])):
                    ?>
                    <div class="notification is-danger">
                      <p>ERRO: Usuário ou senha inválidos.</p>
                    </div>
                    <?php
                    endif;
                    unset($_SESSION['nao_autenticado']);
                    ?>
                    <div class="box login-form">
                        <form action="" method="POST">
                            <div class="field">
                                <div class="control">
                                    <input name="usuario" name="text" class="input is-large" placeholder="Seu usuário" autofocus="">
                                </div>
                            </div>

                            <div class="field">
                                <div class="control">
                                    <input name="senha" class="input is-large" type="password" placeholder="Sua senha">
                                </div>
                            </div>
                            <button type="submit" name="login" class="button is-block is-link is-large is-fullwidth">Entrar</button>
                            <span class="text1">
                                Não tem uma conta?
                            </span>
                            <a href="#" class="text2 toggle-form">
                                Criar
                            </a>
                        </form>
                    </div>

                    <div class="box register-form" style="display: none;">
                        <form action="" method="POST">
                            <div class="field">
                                <div class="control">
                                    <input name="usuario" name="text" class="input is-large" placeholder="Seu usuário" autofocus="">
                                </div>
                            </div>

                            <div class="field">
                                <div class="control">
                                    <input name="senha" class="input is-large" type="password" placeholder="Sua senha">
                                </div>
                            </div>
                            <div class="field">
                                <div class="control">
                                    <input name="confirmasenha" class="input is-large" type="password" placeholder="Confirmar senha">
                                </div>
                            </div>
                            <button type="submit" name="register" class="button is-block is-link is-large is-fullwidth">Registrar</button>
                            <span class="text1">
                                Já tem uma conta?
                            </span>
                            <a href="#" class="text2 toggle-form">
                                Entrar
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>
