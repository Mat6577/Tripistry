<?php
    // gemini-api.php

    $GEMINI_API_KEY = "AIzaSyCc9l6rd-PnvIPbtv050FqpU6E6UcaNIbw";

    $inputData = json_decode(file_get_contents("php://input"), true);
    $userPrompt = isset($inputData['prompt']) ? $inputData['prompt'] :'Hello!';

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $GEMINI_API_KEY;

    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $userPrompt]
                ]
            ]
        ]
    ];

    // 6. Initialize and configure cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    // 7. Execute the request and handle errors
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(['error' => curl_error($ch)]);
    } else {
        // Decode JSON response and extract text
        $responseData = json_decode($response, true);
        echo $responseData['candidates'][0]['content']['parts'][0]['text'];

    }

    curl_close($ch);
?>