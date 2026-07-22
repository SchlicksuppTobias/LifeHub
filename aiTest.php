<?php

$url = "http://localhost:11434/api/generate";


$data = [
    "model" => "mistral:7b",
    "prompt" => "Welche Zutaten beim kochen kann man unter 'Eierteigwaren' finden? Wie zum Beispiel Nudeln",
    "stream" => false
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "Fehler: " . curl_error($ch);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);

print_r($result);