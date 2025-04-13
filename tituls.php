<?php
// Database connection
$servername = "localhost";
$username = "u547027111_mvg";
$password = "MVGskola1";
$dbname = "u547027111_mvg";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all submitted records
$sql = "SELECT id, nosaukums, apraksts, attels, user_id FROM ieraksti ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Atrastās Mantas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='main.css'>

    <style>
        /* Konteiners nosaukumam un ikonai */
        .title-container {
            position: relative;
            text-align: center;
            width: 100%;
            padding-right: 20px; /* Lai nodrošinātu vietu ikonai labajā pusē */
        }

        /* Ikona tiek nostādīta labajā malā */
        .email-icon {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<header></header>
<div class="lapa">
    <div class="augsa">
        <div class="logo">   
            <img src="images/logomvg.png" alt="Mārupe Logo">
        </div>
    </div>
 
    <h1>ATRASTO MANTU SKAPIS</h1>

    <main>

    <section class="pie-apaksa">
        <div class="teksts">
            <p>LAI PIEVIENOTU</p>
        </div>
        <form action="https://mvg.lv/ev/register.php">
            <button type="submit" class="next-button">REĢISTRĒJIES</button>
        </form>
        <form action="https://mvg.lv/ev/login.php">
            <button type="submit" class="next-button">PIESLĒDZIES</button>
        </form>
    </section>

    <section class="atrastasL">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="item-container">';

            // Attēls kreisajā pusē
            echo '<div class="image-box">';
            echo '<img src="https://mvg.lv/' . htmlspecialchars($row["attels"]) . '" alt="Atrasta manta">';
            echo '</div>';

            // Nosaukums un apraksts pa labi
            echo '<div class="text-box">';
            echo '<div class="item title-container">';
            
            // Nosaukums centrā
            echo '<h3>' . htmlspecialchars($row["nosaukums"]) . '</h3>';

            // Saziņas poga labajā malā
            echo '<a href="interese.php?itemID=' . $row["id"] . '&devejaID=' . $row["user_id"] . '" title="Sazināties">';
            echo '<img src="https://mvg.lv/ev/images/email.png" alt="Sazināties" class="email-icon">';
            echo '</a>';

            echo '</div>'; // title-container beigas
            echo '<div class="item"><p>' . htmlspecialchars($row["apraksts"]) . '</p></div>';
            echo '</div>';

            echo '</div>'; // beidzas .item-container
        }
    } else {
        
    }
    $conn->close();
    ?>
    </section>

    </main>
</div>         
</body>
</html>
