<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$topic = $_GET['topic'] ?? 'python';
$limit = $_GET['limit'] ?? 10;
$grade = $_GET['grade'] ?? 'college';

// 🎯 AUTO DIFFICULTY
$res = $conn->query("
SELECT AVG(score) as avg 
FROM attempts 
WHERE user_id=$user_id AND topic='$topic'
");

$row = $res->fetch_assoc();
$avgScore = $row['avg'] ?? 50;

if($avgScore < 30){
    $difficulty = "easy";
} elseif($avgScore < 60){
    $difficulty = "medium";
} else {
    $difficulty = "hard";
}

// 🎯 Grade override
if($grade == "school"){
    $difficulty = "easy";
} elseif($grade == "college"){
    $difficulty = "medium";
} else {
    $difficulty = "hard";
}

$mode = $_GET['mode'] ?? 'student';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-gray-100 p-6">

<div class="bg-white p-6 rounded shadow max-w-3xl mx-auto">

<!-- HEADER -->
<div class="flex justify-between items-center mb-4">
  <h2 class="text-2xl font-bold">
    Quiz: <?= htmlspecialchars($topic) ?> (<?= htmlspecialchars($difficulty) ?>)
  </h2>

  <div class="bg-red-100 text-red-700 px-3 py-1 rounded font-semibold">
    ⏱ Time Left: <span id="timer">00:00</span>
  </div>
</div>

<?php if($mode == "teacher"){ ?>
<button class="bg-red-500 text-white px-3 py-1 rounded">Exit</button>
<?php } ?>

<!-- START BUTTON -->
<button id="startBtn" class="bg-green-500 text-white px-4 py-2 rounded mb-4">
    Start Quiz
</button>

<!-- FORM -->
<form action="submit_quiz.php" method="POST" id="quizForm">
    <input type="hidden" name="topic" value="<?= htmlspecialchars($topic) ?>">

    <button type="button" onclick="openFullscreen()" class="bg-black text-white px-3 py-2 mb-3">
        Enter Full Screen
    </button>
</form>

</div>

<script>

// START BUTTON CLICK
document.getElementById("startBtn").onclick = function(){

    this.style.display = "none";

    let form = document.getElementById("quizForm");

    fetch(`get_quiz.php?topic=<?= urlencode($topic) ?>&difficulty=<?= urlencode($difficulty) ?>&limit=<?= $limit ?>`)
    .then(res => res.json())

    .then(data => {

        // fallback to wiki
        if(data.length < <?= $limit ?>){
            return fetch(`wiki_quiz.php?topic=<?= urlencode($topic) ?>&limit=<?= $limit ?>`)
            .then(res => res.json());
        }

        return data;
    })

    .then(data => {

        if(!data || data.length === 0){
            form.innerHTML += "<h3 class='text-red-500'>No questions found!</h3>";
            return;
        }

        data.forEach((q, i) => {
            form.innerHTML += `
                <div class="mb-4">
                    <p class="font-semibold text-lg mb-2">
                        Q${i+1}: ${q.question}
                    </p>

                    <label><input type="radio" name="q${i}" value="${q.option1}" required> ${q.option1}</label><br>
                    <label><input type="radio" name="q${i}" value="${q.option2}"> ${q.option2}</label><br>
                    <label><input type="radio" name="q${i}" value="${q.option3}"> ${q.option3}</label><br>
                    <label><input type="radio" name="q${i}" value="${q.option4}"> ${q.option4}</label><br>

                    <input type="hidden" name="correct${i}" value="${q.correct_answer}">
                    <hr class="my-3">
                </div>
            `;
        });

        form.innerHTML += `
            <input type="hidden" name="total" value="${data.length}">
            <button class="bg-blue-500 text-white px-4 py-2 mt-4 rounded">
                Submit Quiz
            </button>
        `;
    });
};

// FULLSCREEN
function openFullscreen(){
    document.documentElement.requestFullscreen();
}

// PREVENT EXIT
window.onbeforeunload = function(){
    return "You cannot leave the quiz!";
};

// TAB SWITCH DETECTION
document.addEventListener("visibilitychange", function(){
    if(document.hidden){
        alert("You switched tab! Teacher notified.");
        fetch("notify_teacher.php");
    }
});

// TIMER
const totalMinutes = 10;
let timeLeft = totalMinutes * 60;

const timerEl = document.getElementById("timer");
const form = document.getElementById("quizForm");

function formatTime(sec){
    let m = Math.floor(sec / 60);
    let s = sec % 60;
    return `${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
}

const timerInterval = setInterval(() => {
    timeLeft--;
    timerEl.innerText = formatTime(timeLeft);

    if(timeLeft <= 0){
        clearInterval(timerInterval);
        alert("Time is up! Submitting quiz...");
        form.submit();
    }
}, 1000);

timerEl.innerText = formatTime(timeLeft);

</script>

</body>
</html>