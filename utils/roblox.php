<?php

http_response_code(response_code: 200);

function returnError(string $message, int $code = 400): null
{
    $error = [
        "error" => true,
        "message" => $message,
        "status" => $code,
    ];
    http_response_code(response_code: $code);
    return exit(json_encode(value: $error));
}

function httpPost($url, $data): bool|string
{
    $curl = curl_init(url: $url);
    curl_setopt(handle: $curl, option: CURLOPT_POST, value: true);
    curl_setopt(handle: $curl, option: CURLOPT_POSTFIELDS, value: $data);
    curl_setopt(handle: $curl, option: CURLOPT_RETURNTRANSFER, value: true);
    $response = curl_exec(handle: $curl);
    curl_close(handle: $curl);
    return $response;
}

function getUserId(): int
{
    $userId = null;

    if (isset($_GET['userId'])) {
        $userId = $_GET['userId'];
    } else if (isset($_GET['username'])) {
        $username = $_GET['username'];
        $response = httpPost(
            url: "https://users.roblox.com/v1/usernames/users",
            data: json_encode(value: [
                "usernames" => [$username],
                "excludeBannedUsers" => false
            ])
        );

        echo $response;
        exit();

        // $userId = $userId->data[0]->id ?? null;
    } else {
        returnError(message: "No userId or username provided.");
    }

    $userId = intval(value: $userId);
    if ($userId === 0)
        returnError(message: "Invalid userId or username provided.");

    return $userId;
}

$action = $_GET['action'] ?? null;

if ($action === null)
    returnError(message: "No action provided.");

if ($action === "user-thumbnail") {
    $type = $_GET['type'] ?? null;

    if (
        $type !== "full"
        && $type !== "headshot"
        && $type !== "bust"
    )
        returnError(message: "Invalid type provided, must be 'full', 'headshot' or 'bust'.");

    $userId = getUserId();

    echo $userId;
    exit();
}

returnError(message: "Invalid action provided, must be 'user-thumbnail'.");