<?php
include "db.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Viewer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-gray-100 p-6">

<div class="max-w-6xl mx-auto">

<h1 class="text-3xl font-bold mb-6 text-center">Database Viewer</h1>

<!-- USERS -->
<div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="text-xl font-semibold mb-3">Users</h2>
    <table class="w-full border">
        <tr class="bg-gray-200">
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
        </tr>

        <?php
        $res = $conn->query("SELECT * FROM users");
        while($row = $res->fetch_assoc()){
            echo "<tr class='border'>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>

<!-- QUIZZES -->
<div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="text-xl font-semibold mb-3">Quizzes</h2>
    <table class="w-full border text-sm">
        <tr class="bg-gray-200">
            <th>ID</th>
            <th>Topic</th>
            <th>Difficulty</th>
            <th>Question</th>
        </tr>

        <?php
        $res = $conn->query("SELECT * FROM quizzes");
        while($row = $res->fetch_assoc()){
            echo "<tr class='border'>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['topic']}</td>";
            echo "<td>{$row['difficulty']}</td>";
            echo "<td>{$row['question']}</td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>

<!-- ATTEMPTS -->
<div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="text-xl font-semibold mb-3">Attempts</h2>
    <table class="w-full border">
        <tr class="bg-gray-200">
            <th>ID</th>
            <th>User ID</th>
            <th>Topic</th>
            <th>Score (%)</th>
        </tr>

        <?php
        $res = $conn->query("SELECT * FROM attempts");
        while($row = $res->fetch_assoc()){
            echo "<tr class='border'>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['user_id']}</td>";
            echo "<td>{$row['topic']}</td>";
            echo "<td>{$row['score']}</td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>

<!-- SKILLS -->
<div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="text-xl font-semibold mb-3">Skills</h2>
    <table class="w-full border">
        <tr class="bg-gray-200">
            <th>ID</th>
            <th>User ID</th>
            <th>Skill</th>
        </tr>

        <?php
        $res = $conn->query("SELECT * FROM skills");
        while($row = $res->fetch_assoc()){
            echo "<tr class='border'>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['user_id']}</td>";
            echo "<td>{$row['skill']}</td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>

</div>

</body>
</html>