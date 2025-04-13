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

// Pārbaudām, vai lietotājs ir admins
$user_id = $_SESSION['user_id'];
$admin_check = $conn->prepare("SELECT is_admin FROM logcilveki WHERE id = ?");
$admin_check->bind_param("i", $user_id);
$admin_check->execute();
$admin_check->bind_result($is_admin);
$admin_check->fetch();
$admin_check->close();

if (!$is_admin) {
    echo "Jums nav piekļuves tiesību šai lapai!";
    exit();
}

// Datu vaicājumi
$logcilveki = $conn->query("SELECT * FROM logcilveki ORDER BY id DESC");
$ieraksti = $conn->query("SELECT * FROM ieraksti ORDER BY id DESC");
$interese = $conn->query("SELECT * FROM interese ORDER BY id DESC");
$vesture = $conn->query("SELECT * FROM ev_vesture ORDER BY datums_dzests DESC");
$vesture_interese = $conn->query("SELECT * FROM vesture_interese ORDER BY datums_apstiprinajums DESC"); // Dzēstās intereses

$conn->close();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Vēsture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f4f9;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .table-container {
            margin-bottom: 50px;
        }
        .table {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        th {
            background: #343a40;
            color: white;
            text-align: center;
        }
        .btn-back {
            display: block;
            margin: 30px auto;
            width: 200px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Vēsture</h1>

    <!-- Logcilveki -->
    <div class="table-container">
        <h3>Logcilvēki</h3>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>E-pasts</th>
                    <th>Izveidots</th>
                    <th>Admins</th>
                    <th>Verificēts</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $logcilveki->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td><?= $row['is_admin'] ? 'Jā' : 'Nē' ?></td>
                        <td><?= $row['is_verified'] ? 'Jā' : 'Nē' ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Pašreizējie sludinājumi -->
    <div class="table-container">
        <h3>Pašreizējie sludinājumi</h3>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nosaukums</th>
                    <th>Apraksts</th>
                    <th>Attēls</th>
                    <th>Datums</th>
                    <th>Lietotāja ID</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $ieraksti->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nosaukums']) ?></td>
                        <td><?= htmlspecialchars($row['apraksts']) ?></td>
                        <td>
                            <?php if ($row['attels']): ?>
                                <img src="<?= 'https://mvg.lv/' . ltrim(htmlspecialchars($row['attels']), '/'); ?>" alt="Attēls" width="50">
                            <?php else: ?>
                                Nav attēla
                            <?php endif; ?>
                        </td>
                        <td><?= $row['datums'] ?></td>
                        <td><?= $row['user_id'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Dzēstie sludinājumi -->
    <div class="table-container">
        <h3>Dzēstie sludinājumi</h3>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nosaukums</th>
                    <th>Apraksts</th>
                    <th>Attēls</th>
                    <th>Datums Dzēsts</th>
                    <th>Lietotāja ID</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $vesture->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nosaukums']) ?></td>
                        <td><?= htmlspecialchars($row['apraksts']) ?></td>
                        <td>
                            <?php if ($row['attels']): ?>
                                <img src="<?= 'https://mvg.lv/' . ltrim(htmlspecialchars($row['attels']), '/'); ?>" alt="Attēls" width="50">
                            <?php else: ?>
                                Nav attēla
                            <?php endif; ?>
                        </td>
                        <td><?= $row['datums_dzests'] ?></td>
                        <td><?= $row['user_id'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="table-container">
    <h3>Pašreizējās intereses</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nosaukums</th>
                <th>Datums Interese</th>
                <th>Datums Apstiprinājums</th>
                <th>Item ID</th>
                <th>Interesenta ID</th>
                <th>Devēja ID</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $interese->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['nosaukums']) ?></td>
                    <td><?= $row['datums_interese'] ?></td>
                    <td><?= $row['datums_apstiprinajums'] ?: 'Nav apstiprināts' ?></td>
                    <td><?= $row['itemID'] ?></td>
                    <td><?= $row['interesentaID'] ?></td>
                    <td><?= $row['devejaID'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


    <!-- Dzēstās intereses -->
    <!-- Dzēstās intereses -->
<div class="table-container">
    <h3>Dzēstās intereses</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nosaukums</th>
                <th>Datums Interese</th>
                <th>Datums Apstiprinājums</th>
                <th>Item ID</th>
                <th>Interesenta ID</th>
                <th>Devēja ID</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $vesture_interese->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['nosaukums']) ?></td>
                    <td><?= $row['datums_interese'] ?></td>
                    <td><?= $row['datums_apstiprinajums'] ?: 'Nav apstiprināts' ?></td>
                    <td><?= $row['itemID'] ?></td>
                    <td><?= $row['interesentaID'] ?></td>
                    <td><?= $row['devejaID'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

    <a href="tituls.php" class="btn btn-secondary btn-back">Atpakaļ</a>
</div>

</body>
</html>

