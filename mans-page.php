<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "u547027111_mvg";
$password = "MVGskola1";
$dbname = "u547027111_mvg";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Savienojuma kļūda: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Pārbauda, vai lietotājs ir admins
$sql = "SELECT is_admin FROM logcilveki WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$is_admin = $user['is_admin'];
$stmt->close();

// Apstrādā POST pieprasījumus (labot/dzēst)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
        // Admins var dzēst visus, parastais tikai savus
        $sql = $is_admin ? "DELETE FROM ieraksti WHERE id = ?" : "DELETE FROM ieraksti WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($is_admin) {
            $stmt->bind_param("i", $delete_id);
        } else {
            $stmt->bind_param("ii", $delete_id, $user_id);
        }
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['edit_id'])) {
        $edit_id = $_POST['edit_id'];
        $nosaukums = $_POST['nosaukums'];
        $apraksts = $_POST['apraksts'];

        $sql = $is_admin ? "UPDATE ieraksti SET nosaukums = ?, apraksts = ? WHERE id = ?" 
                         : "UPDATE ieraksti SET nosaukums = ?, apraksts = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($is_admin) {
            $stmt->bind_param("ssi", $nosaukums, $apraksts, $edit_id);
        } else {
            $stmt->bind_param("ssii", $nosaukums, $apraksts, $edit_id, $user_id);
        }
        $stmt->execute();
        $stmt->close();
    }
}

// Ielādē sludinājumus - admins redz visus
$sql = $is_admin ? "SELECT id, nosaukums, apraksts, attels FROM ieraksti"
                 : "SELECT id, nosaukums, apraksts, attels FROM ieraksti WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if (!$is_admin) {
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <title>Mani sludinājumi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mans-page.css">
</head>
<body>
    <div class="container">
        <h1><?php echo $is_admin ? "VISI SLUDINĀJUMI (ADMINA REŽĪMS)" : "REĢISTRĒTĀS MANTAS"; ?></h1>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="container">
                    <div class="form-group">
                        <label for="name">NOSAUKUMS:</label>
                        <input type="text" name="nosaukums" value="<?php echo htmlspecialchars($row['nosaukums']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="desc">APRAKSTS:</label>
                        <input type="text" name="apraksts" value="<?php echo htmlspecialchars($row['apraksts']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="image">ATTĒLS:</label>
                        <img src="<?php echo 'https://mvg.lv/' . ltrim(htmlspecialchars($row['attels']), '/'); ?>" alt="Attēls" style="max-width: 200px;">
                    </div>

                    <div class="buttons">
                        <form action="mans-page.php" method="POST" style="display:grid;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="button delete">DZĒST</button>
                        </form>

                        <button class="button edit" style="display: grid;" onclick="showEditForm(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nosaukums']); ?>', '<?php echo htmlspecialchars($row['apraksts']); ?>')">LABOT</button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>

        <?php endif; ?>

        <div class="button-container">
            <form action="add.php">
                <button class="button add">PIEVIENOT</button>
            </form>
            <form action="tituls.php">
                <button type="submit" class="button home">UZ SĀKUMU</button>
            </form>
            
            <!-- Poga uz vesture.php, redzama tikai adminam -->
            <?php if ($is_admin): ?>
                <form action="vesture.php">
                    <button type="submit" class="button history">VĒSTURE</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Labot sludinājumu forma -->
        <div id="editForm" style="display:none;">
            <h2>Labot sludinājumu</h2>
            <form action="mans-page.php" method="POST">
                <input type="hidden" name="edit_id" id="edit_id">
                <label for="edit_nosaukums">Nosaukums:</label>
                <input type="text" name="nosaukums" id="edit_nosaukums" required>
                <label for="edit_apraksts">Apraksts:</label>
                <textarea name="apraksts" id="edit_apraksts" required></textarea>
                <button type="submit">Saglabāt</button>
            </form>
        </div>
    </div>

    <script>
        function showEditForm(id, nosaukums, apraksts) {
            document.getElementById("edit_id").value = id;
            document.getElementById("edit_nosaukums").value = nosaukums;
            document.getElementById("edit_apraksts").value = apraksts;
            document.getElementById("editForm").style.display = "block";
        }
    </script>
</body>
</html>