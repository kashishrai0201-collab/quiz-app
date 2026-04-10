<?php
session_start();
include "db.php";

$user_id = $_SESSION['user_id'];
$topic = $_POST['topic'];

$total = $_POST['total'];   // dynamic number of questions
$score = 0;

$score = 0;
$answered = 0;

for($i=0; $i<$total; $i++){

    if(isset($_POST["q$i"])){
        $answered++;

        if($_POST["q$i"] == $_POST["correct$i"]){
            $score++;
        }
    }
}

if($total > 0){
    $percentage = ($score / $total) * 100;
} else {
    $percentage = 0;
}

$topic = $_POST['topic']; 

$check = $conn->query("SELECT * FROM skills WHERE user_id=$user_id AND skill='$topic'");

if($percentage >= 80 && $check->num_rows == 0){
    $conn->query("INSERT INTO skills (user_id,skill) VALUES ($user_id,'$topic')");
}

$status = ($answered == 0) ? "not_attempted" : "attempted";

$conn->query("
INSERT INTO attempts (user_id, topic, score, status) 
VALUES ($user_id, '$topic', $percentage, '$status')
");

$_SESSION['score'] = $percentage;
header("Location: result.php");
exit();
?>