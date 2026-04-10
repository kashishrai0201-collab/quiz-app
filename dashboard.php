<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: index.html");
    exit();
}
include "db.php";
$user_id = $_SESSION['user_id'];

// Total attempts
$total = $conn->query("SELECT COUNT(*) as total FROM attempts WHERE user_id=$user_id")->fetch_assoc()['total'];

// Attempted
$attempted = $conn->query("SELECT COUNT(*) as total FROM attempts WHERE user_id=$user_id AND status='attempted'")->fetch_assoc()['total'];

// Not attempted
$not_attempted = $conn->query("SELECT COUNT(*) as total FROM attempts WHERE user_id=$user_id AND status='not_attempted'")->fetch_assoc()['total'];

// Average score
$avg = $conn->query("SELECT AVG(score) as avg FROM attempts WHERE user_id=$user_id")->fetch_assoc()['avg'];
$avg = round($avg, 2);

$user_id = $_SESSION['user_id'];

$skills = $conn->query("SELECT * FROM skills WHERE user_id=$user_id");
$attempts = $conn->query("SELECT * FROM attempts WHERE user_id=$user_id");
?>
<head>
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>
<h2>Dashboard</h2>

<h3>Your Skills:</h3>
<ul>
<?php while($row = $skills->fetch_assoc()){ ?>
    <li><?= $row['skill'] ?></li>
<?php } ?>
</ul>

<h3>Start Quiz</h3>

<div class=" grid-cols-2 gap-4 mb-6">

    <div class="bg-white p-4 shadow rounded">
        <h3 class="text-lg font-bold">Total Attempts</h3>
        <p class="text-xl"><?= $total ?></p>
    </div>

    <div class="bg-white p-4 shadow rounded">
        <h3 class="text-lg font-bold">Attempted</h3>
        <p class="text-xl"><?= $attempted ?></p>
    </div>

    <div class="bg-white p-4 shadow rounded">
        <h3 class="text-lg font-bold">Not Attempted</h3>
        <p class="text-xl"><?= $not_attempted ?></p>
    </div>

    <div class="bg-white p-4 shadow rounded">
        <h3 class="text-lg font-bold">Average Score</h3>
        <p class="text-xl"><?= $avg ?>%</p>
    </div>

</div>


<form action="quiz.php" method="GET">
    
    <input name="topic" placeholder="Enter topic (python, sql, ai)" class="border p-2 mb-2 w-full" required>

    <select name="grade" class="border p-2 mb-2 w-full">
    <option value="school">School</option>
    <option value="college">College</option>
    <option value="pro">Professional</option>
    </select>

    <?php
include "db.php";

$user_id = $_SESSION['user_id'] ?? 0;
$topic = $_GET['topic'] ?? 'python';

// get average score
$res = $conn->query("
SELECT AVG(score) as avg 
FROM attempts 
WHERE user_id=$user_id AND topic='$topic'
");

$row = $res->fetch_assoc();
$avgScore = $row['avg'] ?? 50;

// adaptive difficulty logic
if($avgScore < 30){
    $autoDifficulty = "easy";
} elseif($avgScore < 60){
    $autoDifficulty = "medium";
} else {
    $autoDifficulty = "hard";
}
?>

   <input type="hidden" name="difficulty" value="<?= $autoDifficulty ?>">
    <p class="text-sm text-gray-600">
    Auto Difficulty: <b><?= $autoDifficulty ?></b>
    </p>

    <!-- NEW INPUT -->
    <input type="number" name="limit" value="20" min="20" max="50" required>

    <button type="submit">Start Quiz</button>

</form>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <canvas id="progressChart" width="400" height="200"></canvas>

    <?php
$scores = [];
while($row = $attempts->fetch_assoc()){
    $scores[] = $row['score'];
}
?>

<script>

const scores = <?= json_encode($scores) ?>;

// Topic-wise data
const topicData = {};
<?php
$res = $conn->query("SELECT topic, score FROM attempts WHERE user_id=$user_id");
while($row = $res->fetch_assoc()){
    echo "topicData['{$row['topic']}'] = topicData['{$row['topic']}'] || [];\n";
    echo "topicData['{$row['topic']}'].push({$row['score']});\n";
}
?>

const datasets = [];

// Overall progress
datasets.push({
    label: "Overall Progress",
    data: scores,
    borderWidth: 2,
    fill: false
});

// Topic-wise
for (let topic in topicData) {
    datasets.push({
        label: topic,
        data: topicData[topic],
        borderDash: [5,5],
        fill: false
    });
}

new Chart(document.getElementById("progressChart"), {
    type: "line",
    data: {
        labels: scores.map((_, i) => "Attempt " + (i+1)),
        datasets: datasets
    }
});

</script>
<h3 class="text-xl mt-6 mb-3">Topic Performance</h3>

<?php
$res = $conn->query("
SELECT topic, AVG(score) as avg_score 
FROM attempts 
WHERE user_id=$user_id 
GROUP BY topic
");

while($row = $res->fetch_assoc()){
    echo "<div class='bg-white p-3 mb-2 shadow rounded'>";
    echo "<b>{$row['topic']}</b> - Avg Score: ".round($row['avg_score'],2)."%";
    echo "</div>";
}
?>

<h3 class="text-xl mt-6 mb-3">Leaderboard</h3>

<?php
$res = $conn->query("
SELECT users.name, AVG(attempts.score) as avg_score 
FROM attempts 
JOIN users ON attempts.user_id = users.id
GROUP BY users.id
ORDER BY avg_score DESC
LIMIT 5
");

$rank = 1;

while($row = $res->fetch_assoc()){
    echo "<div class='bg-yellow-100 p-2 mb-2 rounded'>";
    echo "#$rank {$row['name']} - ".round($row['avg_score'],2)."%";
    echo "</div>";
    $rank++;
}
?>

<h3 class="text-xl mt-6 mb-3">Analytics</h3>

<?php
$max = $conn->query("SELECT MAX(score) as max FROM attempts WHERE user_id=$user_id")->fetch_assoc()['max'];
$min = $conn->query("SELECT MIN(score) as min FROM attempts WHERE user_id=$user_id")->fetch_assoc()['min'];

echo "<div class='bg-blue-100 p-3 mb-2 rounded'>Highest Score: $max%</div>";
echo "<div class='bg-red-100 p-3 mb-2 rounded'>Lowest Score: $min%</div>";
?>