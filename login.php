<?php
session_start();

// Savienojuma dati
$servername = "localhost";
$username = "u547027111_mvg";
$password = "MVGskola1";
$dbname = "u547027111_mvg";

// Savienojums ar MySQL
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Savienojuma kļūda: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Pārbaudām, vai lietotājs ir verificēts
    $stmt = $conn->prepare("SELECT id, password_hash, is_verified FROM logcilveki WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password_hash, $is_verified);
        $stmt->fetch();

        if ($is_verified == 0) {
            $error_message = "Šis e-pasts vēl nav verificēts! Pārbaudi savu pastkasti.";
        } elseif (password_verify($password, $password_hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['email'] = $email;
            header("Location: mans-page.php");
            exit();
        } else {
            $error_message = "Nepareiza parole!";
        }
    } else {
        $error_message = "Lietotājs ar šo e-pastu nav reģistrēts!";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pieteikšanās</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login2-box">
            <h2>PIESLĒGTIES</h2>
        </div>
        <div class="login-box">
            <?php if (!empty($error_message)): ?>
                <p style="color:red; text-align:center;"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <input type="email" name="email" placeholder="E-pasts" required>
                <input type="password" name="password" placeholder="Parole" required>
                <button type="submit" class="login-button">PIESLĒGTIES</button>
            </form>
            <p>Nav konta? <a href="register.php">REĢISTRĒJIES</a></p>
        </div>
    </div>
</body>
</html>