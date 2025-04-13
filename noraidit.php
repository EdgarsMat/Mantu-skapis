<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login2.php");
    exit();
}

// DB savienojums
$servername = "localhost";
$username = "u547027111_mvg";
$password = "MVGskola1";
$dbname = "u547027111_mvg";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Savienojums neizdevās: " . $conn->connect_error);
}

// Saņem itemID un interesentaID no URL
$itemID = isset($_GET['itemID']) ? intval($_GET['itemID']) : 0;
$interesentaID = isset($_GET['interesentaID']) ? intval($_GET['interesentaID']) : 0;

// Iegūst interesenta epastu
$interesentaEmail = "";
$stmt = $conn->prepare("SELECT email FROM logcilveki WHERE id = ?");
$stmt->bind_param("i", $interesentaID);
$stmt->execute();
$stmt->bind_result($interesentaEmail);
$stmt->fetch();
$stmt->close();

// Noraidīšanas darbība
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = "Ieraksts tika noraidīts!";
    echo "<script>alert('Ieraksts noraidīts!'); window.location.href = 'tituls.php';</script>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Noraidīt ierakstu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="zinojumi.css">
</head>
<body>
<div class="container">
    <h1>Noraidīt ierakstu</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label"><strong>Interesenta e-pasts:</strong></label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($interesentaEmail); ?>" disabled>
    </div>

    <form method="POST">
        <button type="submit" class="btn btn-danger w-100">Noraidīt</button>
    </form>

    
    <a href="tituls.php" class="btn btn-secondary mt-3 w-100">Atpakaļ</a>
    
</div>
</body>
</html>