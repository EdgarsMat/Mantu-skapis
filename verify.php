<?php
session_start();

// Savienojuma dati
$servername = "localhost";
$username = "u547027111_mvg";
$password = "MVGskola1";
$dbname = "u547027111_mvg";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Savienojuma kļūda: " . $conn->connect_error);
}

// Pārbauda, vai GET parametri ir saņemti
if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    // Pārbauda, vai e-pasts un token pastāv datubāzē
    $stmt = $conn->prepare("SELECT id FROM logcilveki WHERE email = ? AND verification_token = ? AND is_verified = 0");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Atzīmē lietotāju kā verificētu
        $stmt = $conn->prepare("UPDATE logcilveki SET is_verified = 1, verification_token = NULL WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            echo "<p style='color:green; text-align:center;'>E-pasts veiksmīgi verificēts! Tagad vari <a href='login.php'>pieslēgties</a>.</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Kļūda verifikācijā!</p>";
        }
    } else {
        echo "<p style='color:red; text-align:center;'>Nederīgs vai jau verificēts e-pasts!</p>";
    }

    $stmt->close();
} else {
    echo "<p style='color:red; text-align:center;'>Trūkst verifikācijas dati!</p>";
}

$conn->close();
?>