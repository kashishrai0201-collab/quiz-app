<?php
include "db.php";

$topic = $_GET['topic'];
$difficulty = $_GET['difficulty'];
$limit = intval($_GET['limit']);

$res = $conn->query("
SELECT * FROM quizzes 
WHERE topic='$topic' AND difficulty='$difficulty'
ORDER BY RAND() LIMIT $limit
");

$questions = [];

while($row = $res->fetch_assoc()){
    $questions[] = $row;
}

// ❌ REMOVE DUPLICATION LOGIC

echo json_encode($questions);
?>