<?php
include "db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$email = $_GET['email'] ?? '';

if(!$email){
    die("Email missing");
}

// generate new OTP
$otp = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

// update DB
$conn->query("UPDATE users SET otp='$otp', otp_expiry='$expiry' WHERE email='$email'");

// send mail
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // 🔥 PUT YOUR EMAIL HERE
        $mail->Username = 'kashishrai0201@gmail.com';
        $mail->Password = 'kflsxmmzvstahxsq';

        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

    $mail->setFrom('yourgmail@gmail.com', 'Quiz App');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Resend OTP - Quiz App";

    $mail->Body = "
    <h2>New OTP</h2>
    <p>Your new OTP is:</p>
    <h1>$otp</h1>
    <p>Expires in 5 minutes</p>
    ";

    $mail->send();

    echo "<script>alert('OTP resent successfully');window.location='verify.php?email=$email';</script>";

} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}
?>