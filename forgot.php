<?php
include "db.php";

if(isset($_POST['email'])){
    $email = $_POST['email'];

    $token = bin2hex(random_bytes(16));
    $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $conn->query("
    UPDATE users 
    SET reset_token='$token', reset_expiry='$expiry'
    WHERE email='$email'
    ");

    echo "Reset link:<br>";
    echo "<a href='reset.php?token=$token'>Reset Password</a>";
}
?>

<form method="POST">
    <input name="email" placeholder="Enter email" required>
    <button>Send Reset Link</button>
</form>