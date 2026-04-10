<?php
$topic = $_POST['topic'] ?? '';
?>

<form method="POST">
    <input name="topic" placeholder="Enter topic" required>
    <button>Create Quiz</button>
</form>

<?php if($topic): 
$link = "quiz.php?topic=".$topic."&difficulty=medium&limit=20&mode=teacher";
echo "<h3>Share this link:</h3>";
echo "<a href='$link' target='_blank'>$link</a>";
endif; ?>