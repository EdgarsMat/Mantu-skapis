<?php

// Ieslēdz kļūdu ziņošanu
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Pārbauda savienojumu
if ($conn->connect_error) {
    die("Savienojums neizdevās: " . $conn->connect_error);
}

// Saņem itemID un interesentaID no URL
$itemID = isset($_GET['itemID']) ? intval($_GET['itemID']) : 0;
$interesentaID = isset($_GET['interesentaID']) ? intval($_GET['interesentaID']) : 0;

// Iegūst interesenta epastu
$interesentaEmail = "";
$stmt = $conn->prepare("SELECT email FROM logcilveki WHERE id = ?");
if (!$stmt) {
    die("SQL kļūda (email atlase): " . $conn->error);
}
$stmt->bind_param("i", $interesentaID);
$stmt->execute();
$stmt->bind_result($interesentaEmail);
$stmt->fetch();
$stmt->close();

// Apstiprināšanas loģika
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Atjaunina "interese" tabulu, pievienojot apstiprinājuma datumu
    $updateIntereseStmt = $conn->prepare("UPDATE interese SET datums_apstiprinajums = NOW() WHERE itemID = ? AND interesentaID = ?");
    if (!$updateIntereseStmt) {
        die("SQL kļūda (interese atjaunināšana): " . $conn->error);
    }
    $updateIntereseStmt->bind_param("ii", $itemID, $interesentaID);
    $updateIntereseStmt->execute();
    $updateIntereseStmt->close();

    // 2. Iegūst "interese" datus pirms pārvietošanas uz "vesture_interese"
    $stmt = $conn->prepare("SELECT datums_interese, datums_apstiprinajums, itemID, interesentaID, devejaID, nosaukums FROM interese WHERE itemID = ? AND interesentaID = ?");
    if (!$stmt) {
        die("SQL kļūda (interese atlase): " . $conn->error);
    }
    $stmt->bind_param("ii", $itemID, $interesentaID);
    $stmt->execute();
    $result = $stmt->get_result();
    $intereseData = $result->fetch_assoc();
    $stmt->close();

    // 3. Ja dati eksistē, kopē uz "vesture_interese"
    if ($intereseData) {
        $copyStmt = $conn->prepare("INSERT INTO vesture_interese (datums_interese, datums_apstiprinajums, itemID, interesentaID, devejaID, nosaukums) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$copyStmt) {
            die("SQL kļūda (kopēšana uz vesture_interese): " . $conn->error);
        }
        $copyStmt->bind_param("ssiiis", $intereseData['datums_interese'], $intereseData['datums_apstiprinajums'], $intereseData['itemID'], $intereseData['interesentaID'], $intereseData['devejaID'], $intereseData['nosaukums']);
        $copyStmt->execute();
        $copyStmt->close();
    }

    // 4. Iegūst sludinājuma datus pirms dzēšanas
    $stmt = $conn->prepare("SELECT id, nosaukums, apraksts, attels, user_id FROM ieraksti WHERE id = ?");
    if (!$stmt) {
        die("SQL kļūda (ierakstu atlase): " . $conn->error);
    }
    $stmt->bind_param("i", $itemID);
    $stmt->execute();
    $result = $stmt->get_result();
    $itemData = $result->fetch_assoc();
    $stmt->close();

    // **DEBUG:** Pārbauda, vai dati ir pareizi iegūti
    if (!$itemData) {
        die("Kļūda: Netika atrasts ieraksts ar ID $itemID!");
    }

    // 5. Saglabā dzēsto sludinājumu "ev_vesture"
    $insertStmt = $conn->prepare("INSERT INTO ev_vesture (id, nosaukums, apraksts, attels, datums_dzests, user_id) VALUES (?, ?, ?, ?, NOW(), ?)");
    if (!$insertStmt) {
        die("SQL kļūda (ieraksta saglabāšana vēsturē): " . $conn->error);
    }
    $insertStmt->bind_param("isssi", $itemData['id'], $itemData['nosaukums'], $itemData['apraksts'], $itemData['attels'], $itemData['user_id']);
    $insertStmt->execute();
    $insertStmt->close();

    // 6. Izdzēš sludinājumu no "ieraksti"
    $deleteStmt = $conn->prepare("DELETE FROM ieraksti WHERE id = ?");
    if (!$deleteStmt) {
        die("SQL kļūda (sludinājuma dzēšana): " . $conn->error);
    }
    $deleteStmt->bind_param("i", $itemID);
    if (!$deleteStmt->execute()) {
        die("SQL kļūda (dzēšot sludinājumu): " . $conn->error);
    }
    $deleteStmt->close();

    // Paziņojums lietotājam
    $message = "Sludinājums apstiprināts un pārvietots uz vēsturi!";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apstiprināt Interesi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="zinojumi.css">
</head>
<body>
<div class="container">
    <h1>Apstiprināt Interesi</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label"><strong>Interesenta e-pasts:</strong></label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($interesentaEmail); ?>" disabled>
    </div>

    <form method="POST">
        <button type="submit" name="apstiprinat" class="btn btn-success w-100">Apstiprināt</button>
    </form>

    <a href="tituls.php" class="btn btn-secondary mt-3 w-100">Atpakaļ</a>
</div>
</body>
</html>

<!-- ev_vesture -->