<?php
$plainPassword = 'shaktiparvat';
$hashedPassword = '$2y$10$Ii1s4yP4DBWY.EPSM9ZZqOrkvtTstGf9AKTnIUWzj/yXc9e3NNzYO'; // Example hash from your DB

if (password_verify($plainPassword, $hashedPassword)) {
    echo 'Password is valid!';
} else {
    echo 'Invalid password!';
}

?>
