<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$topic = $_GET['topic'] ?? 'General';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;

// 🔑 PUT YOUR API KEY HERE
$api_key = "AIzaSyB60PuBdK171ib6cMVfUqBFEn4lgk4suYQ";

$prompt = "Generate $limit DIFFERENT and UNIQUE multiple-choice questions on '$topic'.

Rules:
- DO NOT repeat the same question
- DO NOT use 'What is $topic' more than once
- Include different types:
   • definition
   • concept-based
   • application-based
   • feature-based
- Each question must be clearly different

Each question must have:
- 1 correct answer
- 3 realistic wrong options

Return ONLY valid JSON array like:
[
 {\"question\":\"...\",\"option1\":\"...\",\"option2\":\"...\",\"option3\":\"...\",\"option4\":\"...\",\"correct_answer\":\"...\"}
]";

// API request
$data = [
    "model" => "gpt-4o-mini",
    "messages" => [
        ["role" => "user", "content" => $prompt]
    ],
    "temperature" => 0.9
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $api_key"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

$questions = json_decode($json, true);

// ✅ FIX: if decoding fails → use fallback
if(!$questions || !is_array($questions)){
    $questions = fallback($topic, $limit);
}

// ❌ If API fails → fallback

if(!isset($result['choices'][0]['message']['content'])){
    $unique = [];
$filtered = [];

if(!isset($questions) || !is_array($questions)){
    $questions = fallback($topic, $limit);
}

foreach($questions as $q){
    if(!in_array($q['question'], $unique)){
        $unique[] = $q['question'];
        $filtered[] = $q;
    }
}

echo json_encode($filtered);

    echo json_encode(fallback($topic, $limit));
    exit;
}

$content = $result['choices'][0]['message']['content'];

// clean
$content = trim($content);
$content = preg_replace('/```json|```/', '', $content);

// extract JSON
$start = strpos($content, '[');
$end = strrpos($content, ']');

if($start !== false && $end !== false){
    $json = substr($content, $start, $end - $start + 1);
} else {
    echo json_encode(fallback($topic, $limit));
    exit;
}

$questions = json_decode($json, true);

if(!$questions || !is_array($questions)){
    echo json_encode(fallback($topic, $limit));
    exit;
}

echo json_encode(array_values($questions));

// fallback
function fallback($topic, $limit){
    $q = [];
    for($i=1; $i<=$limit; $i++){
        $q[] = [
            "question" => "What is $topic?",
            "option1" => "Programming Language",
            "option2" => "Database",
            "option3" => "Operating System",
            "option4" => "Software",
            "correct_answer" => "Programming Language"
        ];
    }
    return $q;
}
?>