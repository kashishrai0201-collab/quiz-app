<?php
include "db.php";

$msg = "";

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // server-side email check
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $msg = "Invalid email format!";
    } else {
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if($check->num_rows > 0){
            $msg = "Email already exists!";
        } else {
            $conn->query("INSERT INTO users(name,email,password) VALUES('$name','$email','$pass')");
            $msg = "Registered successfully!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 p-6">

<div class="bg-white p-6 rounded shadow max-w-md mx-auto">
    <h2 class="text-xl mb-4">Register</h2>

    <?php if($msg): ?>
      <div class="bg-green-100 text-green-800 p-2 mb-3"><?= $msg ?></div>
    <?php endif; ?>

    <form action="auth.php" method="POST">
        <input name="name" placeholder="Name" class="w-full mb-2 p-2 border" required>
        <input name="email" placeholder="Email" class="w-full mb-2 p-2 border" required>
        <input name="password" type="password" placeholder="Password" class="w-full mb-2 p-2 border" required>
        <button name="register" class="bg-green-500 text-white p-2 w-full">Register</button>
    </form>
</div>

</body>
</html>