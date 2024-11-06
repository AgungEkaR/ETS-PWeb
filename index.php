<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mahasiswa_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (isset($_COOKIE['username']) && !isset($_SESSION['username'])) {
    $_SESSION['username'] = $_COOKIE['username'];
    header("Location: home.php");
    exit;        
}

$error_message = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username;

            setcookie("username", $username, time() + 86400, "/");

            $stmt = $conn->prepare("UPDATE users SET cookie = ? WHERE username = ?");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();

            header("Location: home.php");
            exit;
        } else {
            $error_message = "Wrong password";
        }
    } else {
        $error_message = "Username not found";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <i class="fas fa-user user-icon"></i>
            <h2>Lecturer Login</h2>
            <div class="error-message" style="color: red;"><?php echo $error_message; ?></div>
            <form action="index.php" method="post">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Sign in</button>
                <p>Don't have an account? <a href="register.php">Sign up here!</a></p>
            </form>
        </div>
    </div>
</body>
</html>
