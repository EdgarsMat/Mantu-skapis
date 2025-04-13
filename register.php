<?php
// Savienojuma dati
$servername = "localhost";
$username = "u547027111_mvg";
$password = "MVGskola1";
$dbname = "u547027111_mvg";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Savienojuma kļūda: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $email_confirm = trim($_POST['email_confirm']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];


    // Pārbaudām, vai e-pasta domēns ir "@marupe.edu.lv"
    if (!preg_match('/@marupe\.edu\.lv$/', $email)) {
        $error_message = "Piekļuve tikai skolas e-pastiem!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Nederīgs e-pasta formāts!";
    } elseif ($email !== $email_confirm) {
        $error_message = "E-pasti nesakrīt!";
    } elseif ($password !== $password_confirm) {
        $error_message = "Paroles nesakrīt!";
    } else {
        // Paroles šifrēšana
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Ģenerējam unikālu verifikācijas tokenu
        $token = bin2hex(random_bytes(16));

        // Saglabājam lietotāja datus ar verifikācijas tokenu
        $stmt = $conn->prepare("INSERT INTO logcilveki (email, password_hash, verification_token, is_verified, created_at) VALUES (?, ?, ?, 0, NOW())");
        if ($stmt === false) {
            die("Kļūda SQL sagatavošanā: " . $conn->error);
        }
        $stmt->bind_param("sss", $email, $password_hash, $token);

        if ($stmt->execute()) {
            // Sagatavo e-pasta verifikācijas saiti
            $verification_link = "https://mvg.lv/ev/verify.php?email=" . urlencode($email) . "&token=" . urlencode($token);

            // Nosūtīt e-pastu (PHP mail funkcija)
            $to = $email;
            $subject = "E-pasta verifikācija";
            $message = "Sveiki,\n\nLūdzu, noklikšķiniet uz šīs saites, lai verificētu savu e-pastu:\n$verification_link\n\nPaldies!";
            
            $headers = "From: noreply@mvg.lv\r\n";
            $headers .= "Reply-To: noreply@mvg.lv\r\n";
            $headers .= "Return-Path: noreply@mvg.lv\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            error_log("Pirms mail() funkcijas...");
            if (mail($to, $subject, $message, $headers)) {
                error_log("E-pasts veiksmīgi nosūtīts uz $email");
                echo "<p style='color:white; text-align:center;'>Reģistrācija veiksmīga! Pārbaudi savu e-pastu, lai verificētu kontu.</p>";
            } else {
                error_log("E-pasta sūtīšana neizdevās uz $email");
                $error_message = "Kļūda: e-pasta sūtīšana neizdevās!";
            }
        } else {
            $error_message = "Tāds konts jau eksistē!";
        }

        $stmt->close();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Reģistrēties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel='stylesheet' type='text/css' href='register.css'>
</head>
<body> 
    <div class="register-wrapper">
        <div class="register-box">
            <h2>REĢISTRĒTIES</h2>
        </div>
        <div class="register-container">
            <?php if (!empty($error_message)): ?>
                <p style="color:red; text-align:center;"> <?php echo $error_message; ?> </p>
            <?php endif; ?>
            <form action="register.php" method="POST">
                <input type="email" name="email" placeholder="E-pasts @marupe.edu.lv" required>
                <input type="email" name="email_confirm" placeholder="Atkārtot e-pastu" required>
                <input type="password" name="password" placeholder="Parole" required>
                <input type="password" name="password_confirm" placeholder="Atkārtot paroli" required>
                <button type="submit" class="register-button">REĢISTRĒTIES</button>
            </form>
            <p>Ir konts? <a href="login.php">PIESLĒDZIES</a></p>
        </div>
    </div>
</body>
</html>