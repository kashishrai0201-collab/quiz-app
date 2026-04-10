<?php
session_start();
$score = $_SESSION['score'] ?? 0;

echo "<h2>Your Score: $score%</h2>";

if($score >= 80){
    echo "<h3>🎉 Skill Unlocked!</h3>";
}
?>

<head>
    <title>Quiz Result</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>