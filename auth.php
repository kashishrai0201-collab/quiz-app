<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';


/* ===========================
   REGISTER
=========================== */
if(isset($_POST['register'])){

    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 1️⃣ Validate email
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "<script>alert('Invalid email format');window.history.back();</script>";
        exit();
    }

    // 2️⃣ Check existing email
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if($check->num_rows > 0){
        echo "<script>alert('Email already exists');window.history.back();</script>";
        exit();
    }

    // 3️⃣ Generate OTP + expiry
    $otp = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    // 4️⃣ Insert user (ONLY ONCE)
    $conn->query("
    INSERT INTO users (name,email,password,otp,otp_expiry,verified) 
    VALUES ('$name','$email','$pass','$otp','$expiry',0)
    ");

    // 5️⃣ Send Email
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // 🔥 PUT YOUR EMAIL HERE
        $mail->Username = 'yourgmail@gmail.com';
        $mail->Password = 'your_app_password';

        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('yourgmail@gmail.com', 'Quiz App');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Your OTP for Quiz App";

        $mail->Body = "
        <h2>Quiz App Verification</h2>
        <p>Your OTP is:</p>
        <h1 style='color:blue;'>$otp</h1>
        <p>This OTP expires in 5 minutes.</p>
        ";

        $mail->send();

        echo "<script>alert('OTP sent to your email'); window.location='verify.php?email=$email';</script>";
        exit();

    } catch (Exception $e) {
        echo "Mail Error: " . $mail->ErrorInfo;
    }
}


/* ===========================
   LOGIN
=========================== */
if(isset($_POST['email']) && isset($_POST['password'])){

    $email = $_POST['email'];
    $pass = $_POST['password'];

    $res = $conn->query("SELECT * FROM users WHERE email='$email'");

    if($res->num_rows > 0){
        $user = $res->fetch_assoc();

        // 🚫 Block unverified users
        if($user['verified'] == 0){
            echo "<script>alert('Please verify your email first');window.location='index.html';</script>";
            exit();
        }

        if(password_verify($pass, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Invalid password";
        }

    } else {
        echo "User not found";
    }
}
?>