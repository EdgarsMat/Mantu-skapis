<?php
$to = "edgarsmatiss.vingris@marupe.edu.lv";
$subject = "Tests";
$message = "Šis ir tests!";
$headers = "From: no-reply@mvg.lv";

if (mail($to, $subject, $message, $headers)) {
    echo "E-pasts nosūtīts!";
} else {
    echo "Neizdevās nosūtīt e-pastu.";
}
?>