<?php
include "db.php";

$token = $_GET['token'];

if(isset($_POST['password'])){
    $new = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $res = $conn->query("
    SELECT * FROM users 
    WHERE reset_token='$token'
    ");

    if($res->num_rows > 0){
        $user = $res->fetch_assoc();

        if(strtotime($user['reset_expiry']) < time()){
            echo "Link expired";
            exit();
        }

        $conn->query("
        UPDATE users 
        SET password='$new', reset_token=NULL 
        WHERE id={$user['id']}
        ");

        echo "Password updated! <a href='index.html'>Login</a>";
    }
}
?>

<form method="POST">
    <input name="password" placeholder="New password" required>
    <button>Reset</button>
</form>