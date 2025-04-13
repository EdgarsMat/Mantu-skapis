

<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "u547027111_mvg";
$password = "MVGskola1";
$dbname = "u547027111_mvg";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nosaukums = trim(htmlspecialchars($_POST['nosaukums']));
    $apraksts = trim(htmlspecialchars($_POST['apraksts']));
    $user_id = $_SESSION['user_id']; // Saglabā lietotāja ID

    // FTP credentials
    $ftp_server = "77.37.34.2";
    $ftp_username = "u547027111.mvg.lv";  
    $ftp_password = "@MVGskolens1";  
    $ftp_folder = "ev/uploads"; 

    // Check if file was uploaded
    if (!isset($_FILES["attels"]) || $_FILES["attels"]["error"] !== UPLOAD_ERR_OK) {
        die("Error: No file uploaded or upload failed.");
    }

    // Temporary file details
    $local_file = $_FILES["attels"]["tmp_name"];
    $file_name = basename($_FILES["attels"]["name"]);
    $remote_file = $ftp_folder . "/" . $file_name;

    // Establish FTP connection
    $ftp_conn = ftp_connect($ftp_server);
    if (!$ftp_conn) {
        die("Could not connect to FTP server.");
    }

    $login = ftp_login($ftp_conn, $ftp_username, $ftp_password);
    if (!$login) {
        ftp_close($ftp_conn);
        die("Could not log in to FTP server.");
    }

    // Upload file to FTP server
    if (ftp_put($ftp_conn, $remote_file, $local_file, FTP_BINARY)) {
        echo "";
    } else {
        ftp_close($ftp_conn);
        die("Error uploading file.");
    }

    // Close FTP connection
    ftp_close($ftp_conn);

    // Save file path in database with user_id
    $sql = "INSERT INTO ieraksti (nosaukums, apraksts, attels, user_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nosaukums, $apraksts, $remote_file, $user_id);

    if ($stmt->execute()) {
        // Redirect to user's page
        header("Location: mans-page.php");
        exit();
    } else {
        echo "Kļūda: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Pievienot sludinājumu</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel='stylesheet' type='text/css' media='screen' href='add.css'>
</head>
<body> 
    <header></header>
    <div class="container">
        <form action="add.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="nosaukums" placeholder="Nosaukums" required>
            <textarea name="apraksts" placeholder="Apraksts" required></textarea>
            <label for="fileInput" class="file-upload">Pievienot attēlu</label>
            <input type="file" id="fileInput" name="attels" accept="image/*" required>
            <span id="fileName">Nav pievienots neviens attēls</span>
            <button type="submit">TĀLĀK</button>
        </form>
    </div>
</body>
<script>
document.getElementById("fileInput").addEventListener("change", function() {
    let fileName = this.files.length > 0 ? this.files[0].name : "Nav pievienots neviens attēls";
    document.getElementById("fileName").textContent = fileName;
});
</script>
</html>



