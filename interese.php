<?php
session_start();

// Ja lietotājs nav pieslēdzies, novirzām uz login2.php
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_itemID'] = $_GET['itemID'] ?? null;
    $_SESSION['redirect_devejaID'] = $_GET['devejaID'] ?? null;
    header("Location: login2.php");
    exit();
}

// Savienojums ar DB
$servername = "localhost";
$username = "u547027111_mvg";
$password = "MVGskola1";
$dbname = "u547027111_mvg";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Savienojums neizdevās: " . $conn->connect_error);
}

// Saņem datus no URL un sesijas
$itemID = intval($_GET['itemID'] ?? 0);
$devejaID = intval($_GET['devejaID'] ?? 0);
$interesentaID = $_SESSION['user_id'];

// Pārbauda, vai lietotāji eksistē un saņem e-pastus
$checkUserSql = "SELECT id, email FROM logcilveki WHERE id IN (?, ?)";
$checkUserStmt = $conn->prepare($checkUserSql);
$checkUserStmt->bind_param("ii", $interesentaID, $devejaID);
$checkUserStmt->execute();
$checkUserStmt->bind_result($foundID, $foundEmail);
$emails = [];
while ($checkUserStmt->fetch()) {
    $emails[$foundID] = $foundEmail;
}
$checkUserStmt->close();

// Pārbauda, vai sludinājums eksistē
$checkItemSql = "SELECT id, nosaukums, apraksts, attels FROM ieraksti WHERE id = ?";
$checkItemStmt = $conn->prepare($checkItemSql);
$checkItemStmt->bind_param("i", $itemID);
$checkItemStmt->execute();
$result = $checkItemStmt->get_result();
if ($result->num_rows === 0) {
    die("Sludinājums nav atrasts.");
}
$sludinajums = $result->fetch_assoc();
$checkItemStmt->close();

// Ja forma iesniegta — reģistrē interesi un sūta e-pastu
$zinaNosutita = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zina = $conn->real_escape_string($_POST['zina']);
    
    // Reģistrē interesi
    $insertSql = "INSERT INTO interese (datums_interese, itemID, interesentaID, devejaID) VALUES (NOW(), ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("iii", $itemID, $interesentaID, $devejaID);
    $insertStmt->execute();
    $insertStmt->close();
    
    // Apstiprināšanas/noraidīšanas saites
    $baseURL = "https://mvg.lv/ev/";
    $apstiprinatURL = "{$baseURL}apstiprinat.php?itemID=$itemID&interesentaID=$interesentaID";
    $noraiditURL = "{$baseURL}noraidit.php?itemID=$itemID&interesentaID=$interesentaID";
    
    // Sagatavo e-pastu
// Sagatavo e-pastu
$to = $emails[$devejaID];
echo "E-pasts tiks sūtīts uz: " . $to; // Šis parādīs saņēmēja e-pastu

$subject = "Interese par atrasto mantu: " . $sludinajums['nosaukums'];
$message = "Sveiki,\n\n"
    . "Kāds ir izrādījis interesi par jūsu sludinājumu: '" . $sludinajums['nosaukums'] . "'.\n"
    . "Ziņa:\n$zina\n\n"
    . "Sazināties ar interesentu: " . $emails[$interesentaID] . "\n\n"
    . "Lūdzu, apstipriniet vai noraidiet interesi:\n"
    . "✅ Apstiprināt: $apstiprinatURL\n"
    . "❌ Noraidīt: $noraiditURL\n\n"
    . "Ar cieņu,\nMVG Komanda.";

$headers = "From: noreply@mvg.lv";

// Nosūta e-pastu
if (mail($to, $subject, $message, $headers)) {
    $zinaNosutita = true;
} else {
    die("Neizdevās nosūtīt e-pastu.");
}
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Izrādīt interesi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="interese.css">
</head>
<body>
    <div class="container">
        <div class="card p-4">
            <h1 class="mb-4 text-center">Izrādīt interesi</h1>

            <!-- Sludinājuma informācija -->
            <div class="text-center mb-4">
                <h3><?= htmlspecialchars($sludinajums['nosaukums']) ?></h3>
                <div class="image-box">
                    <img src="https://mvg.lv/<?= htmlspecialchars($sludinajums['attels']) ?>" class="img-fluid mt-3" alt="Attēls">
                </div>
                <p class="mt-3"><?= nl2br(htmlspecialchars($sludinajums['apraksts'])) ?></p>
            </div>

            <!-- Ziņas forma -->
            <?php if ($zinaNosutita): ?>
                <p class="success-message text-center">Ziņa veiksmīgi nosūtīta!</p>
                <div class="button-container">
                    <a href="tituls.php" class="button">Atpakaļ</a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="zina">Tava ziņa:</label>
                        <textarea id="zina" name="zina" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="button-container">
                        <button type="submit" class="button edit">Nosūtīt ziņu</button>
                        <a href="tituls.php" class="button delete">Atpakaļ</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>