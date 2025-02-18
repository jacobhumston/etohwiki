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
    header(header: "Content-Type: application/json");
    return exit(json_encode(value: $error));
}

function httpPost($url, $data, null|callable $preCall): bool|string
{
    $curl = curl_init(url: $url);
    curl_setopt(handle: $curl, option: CURLOPT_POST, value: true);
    curl_setopt(handle: $curl, option: CURLOPT_POSTFIELDS, value: $data);
    curl_setopt(handle: $curl, option: CURLOPT_RETURNTRANSFER, value: true);

    if ($preCall !== null)
        $preCall($curl);

    $response = curl_exec(handle: $curl);
    curl_close(handle: $curl);
    return $response;
}

function httpGet($url, null|callable $preCall): bool|string
{
    $curl = curl_init(url: $url);
    curl_setopt(handle: $curl, option: CURLOPT_HTTPGET, value: true);
    curl_setopt(handle: $curl, option: CURLOPT_RETURNTRANSFER, value: true);

    if ($preCall !== null)
        $preCall($curl);

    $response = curl_exec(handle: $curl);
    curl_close(handle: $curl);
    return $response;
}

$preCallApplicationTypeJSON = function ($curl): void {
    curl_setopt(handle: $curl, option: CURLOPT_HTTPHEADER, value: [
        "Content-Type: application/json"
    ]);
};

function getUserId(): int
{
    global $preCallApplicationTypeJSON;

    $userId = null;

    if (isset($_GET['userId'])) {
        $userId = $_GET['userId'];
    } else if (isset($_GET['username'])) {
        $username = $_GET['username'];

        $data = json_encode(value: [
            "usernames" => [$username],
            "excludeBannedUsers" => false
        ], flags: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $response = httpPost(
            url: "https://users.roblox.com/v1/usernames/users",
            data: $data,
            preCall: $preCallApplicationTypeJSON
        );

        $data = json_decode(json: $response);
        if (json_last_error() !== JSON_ERROR_NONE)
            returnError(message: "Invalid JSON response: " . json_last_error_msg());
        if (!isset($data->data[0]->id))
            returnError(message: "User id not found in the response.");

        return $data->data[0]->id;
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
    $url = [
        "full" => "https://thumbnails.roblox.com/v1/users/avatar?userIds=$userId&size=352x352&format=Webp&isCircular=false",
        "headshot" => "https://thumbnails.roblox.com/v1/users/avatar-headshot?userIds=$userId&size=352x352&format=Webp&isCircular=false",
        "bust" => "https://thumbnails.roblox.com/v1/users/avatar-bust?userIds=$userId&size=352x352&format=Webp&isCircular=false",
    ][$type];

    $response = httpGet(
        url: $url,
        preCall: $preCallApplicationTypeJSON
    );

    $data = json_decode(json: $response);
    if (json_last_error() !== JSON_ERROR_NONE)
        returnError(message: "Invalid JSON response: " . json_last_error_msg());
    if (!isset($data->data[0]->imageUrl))
        returnError(message: "Image URL not found in the response.");

    $imageUrl = $data->data[0]->imageUrl;
    $image = file_get_contents(filename: $imageUrl);

    header(header: "Content-Type: image/webp");
    echo $image;
}

if ($action === 'username') {

}

returnError(message: "Invalid action provided, must be 'user-thumbnail' or 'username'.");