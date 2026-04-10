<?php
header('Content-Type: application/json');

$topic = $_GET['topic'] ?? '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;

// Map topics
$map = [
    "python" => "Python_(programming_language)",
    "ai" => "Artificial_intelligence",
    "sql" => "SQL",
    "dbms" => "Database",
    "java" => "Java_(programming_language)",
    "c" => "C_(programming_language)",
    "javascript" => "JavaScript",
    "c++" => "C++_(programming_language)",
    "html" => "HTML",
    "css" => "CSS",
    "ml" => "Machine_learning",
    "dl" => "Deep_learning",
    "ruby" => "Ruby_(programming_language)"
];

$key = strtolower($topic);
if(isset($map[$key])){
    $topic = $map[$key];
}

$topic = str_replace(" ", "_", $topic);

// Fetch FULL content (not just summary)
$url = "https://en.wikipedia.org/api/rest_v1/page/html/" . urlencode($topic);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "QuizApp/1.0");

$html = curl_exec($ch);
curl_close($ch);

$text = strip_tags($html);

// Break into sentences
$sentences = preg_split('/[.?!]\s+/', $text);
$sentences = array_unique($sentences);
shuffle($sentences);

// Extract keywords
function extract_keywords($sentence){
    $words = explode(" ", strtolower($sentence));
    $stop = ["is","a","the","of","and","to","in","for","with","on"];
    return array_values(array_diff($words, $stop));
}

$questions = [];
$used_keywords = [];

foreach($sentences as $sentence){

    if(count($questions) >= $limit) break;

    if(strlen($sentence) < 40) continue;

    $words = extract_keywords($sentence);

if(count($words) == 0) continue;

// choose a meaningful keyword (not always first word)
$keyword = ucfirst($words[rand(0, min(3, count($words)-1))]);

// skip weak keywords
if(strlen($keyword) < 4) continue;

// avoid duplicates
if(in_array($keyword, $used_keywords)) continue;

$used_keywords[] = $keyword;

    // Question types
    $templates = [
        "What is $keyword?",
        "Which statement best describes $keyword?",
        "In $topic, what does $keyword refer to?",
        "Identify the correct description of $keyword."
    ];

    $question = $templates[array_rand($templates)];

    // Options
    $options = [
        "Programming Language",
        "Database System",
        "Operating System",
        "Software Tool",
        "Web Framework",
        "Concept"
    ];

    shuffle($options);

    $questions[] = [
        "question" => $question,
        "option1" => $options[0],
        "option2" => $options[1],
        "option3" => $options[2],
        "option4" => $options[3],
        "correct_answer" => $options[0]
    ];
}

// fallback
if(count($questions) == 0){
    $questions[] = [
        "question" => "No data found",
        "option1" => "OK",
        "option2" => "OK",
        "option3" => "OK",
        "option4" => "OK",
        "correct_answer" => "OK"
    ];
}

include "db.php";

foreach($questions as $q){

    $question = $conn->real_escape_string($q['question']);

    $check = $conn->query("SELECT id FROM quizzes WHERE question='$question'");

    if($check->num_rows == 0){
        $conn->query("
            INSERT INTO quizzes (topic, difficulty, question, option1, option2, option3, option4, correct_answer)
            VALUES ('$topic','medium',
            '{$q['question']}',
            '{$q['option1']}',
            '{$q['option2']}',
            '{$q['option3']}',
            '{$q['option4']}',
            '{$q['correct_answer']}')
        ");
    }
}

echo json_encode($questions);

?>