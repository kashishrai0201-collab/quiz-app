<?php
include "db.php";

$email = $_GET['email'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
<title>Verify OTP</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">
<div class="card">

<h2>Verify Email</h2>

<form method="POST">
    <input type="text" name="otp" placeholder="Enter OTP" required>
    <button>Verify</button>
</form>

<?php
if(isset($_POST['otp'])){
    $otp = $_POST['otp'];

    $res = $conn->query("
        SELECT * FROM users 
        WHERE email='$email' AND otp='$otp'
    ");

    if($res->num_rows > 0){

        $user = $res->fetch_assoc();

        // ⏱ CHECK OTP EXPIRY
        if(strtotime($user['otp_expiry']) < time()){
            echo "<p style='color:orange;'>OTP expired!</p>";
            echo "<a href='resend.php?email=$email'>Resend OTP</a>";
            exit();
        }

        // ✅ VERIFY USER
        $conn->query("UPDATE users SET verified=1 WHERE email='$email'");

        echo "<p style='color:green;'>Verified Successfully!</p>";
        echo "<a href='index.html'>Login Now</a>";

    } else {
        echo "<p style='color:red;'>Invalid OTP</p>";
    }
}
?>

<br>
<a href="resend.php?email=<?= $email ?>">Resend OTP</a>

</div>
</div>

</body>
</html>