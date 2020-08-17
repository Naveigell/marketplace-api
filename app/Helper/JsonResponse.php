<?php
function responseStatus(){
    return [
        "200" => "OK",
        "204" => "No Content",
        "401" => "Unauthorized",
        "404" => "Not Found",
        "409" => "Conflict",
        "422" => "Unprocessable Entity",
        "500" => "Internal Server Error"
    ];
}

function error($details, $messages, $code = 500, $status = "Internal Server Error"){
    $response = new Illuminate\Support\Facades\Response;

    $contents = [
        "status"        => empty(responseStatus()[$code]) ? $status : responseStatus()[$code],
        "code"          => $code,
        "client"        => [
            "type"      => \App\Helper\UserAgent::agentType(),
            "name"      => \App\Helper\UserAgent::agentName()
        ],
        "response_time" => microtime(true) - LARAVEL_START,
        "body"          => null,
        "errors"        => [
           "messages"   => $messages
        ],
        "message"       => "Failed"
    ];

    if ($details != null) {
        $contents["errors"]["details"] = $details;
    }

    return \Illuminate\Support\Facades\Response::json($contents, $code, [
        "Content-Type" => "application/json"
    ]);
}

function json($body = [], $errors = null, $code = 200, $status = "OK"){
    $response = new Illuminate\Support\Facades\Response;

    $contents = [
        "status"        => empty(responseStatus()[$code]) ? $status : responseStatus()[$code],
        "code"          => $code,
        "client"        => [
            "type"      => \App\Helper\UserAgent::agentType(),
            "name"      => \App\Helper\UserAgent::agentName()
        ],
        "response_time" => microtime(true) - LARAVEL_START,
        "body"          => $body,
        "errors"        => $errors == null ? null : ["details" => $errors],
        "message"       => $code == 200 || $code == 201 || $code == 202 || $code == 203 || $code == 204 ? "Success" : "Failed"
    ];

    return \Illuminate\Support\Facades\Response::json($contents, $code, [
        "Content-Type" => "application/json"
    ]);
}

function error401($errors = null, $errorKey = null, $errorValue = null, $details = null){

    $errors[$errorKey == null ? "auth" : $errorKey] = [$errorValue == null ? "User belum login" : $errorValue];

    return error($details, $errors, 401);
}

function error404($errors = null, $errorKey = null, $errorValue = null, $details = null){

    $errors[$errorKey == null ? "halaman" : $errorKey] = [$errorValue == null ? "Halaman tidak ditemukan" : $errorValue];

    return error($details, $errors, 404);
}

function error422($errors = null, $errorKey = null, $errorValue = null, $details = null) {

    $errors[$errorKey == null ? "input" : $errorKey] = [$errorValue == null ? "Input yang anda masukkan tidak tepat" : $errorValue];

    return error($details, $errors, 422);
}

function error500($errors = null, $errorKey = null, $errorValue = null, $details = null) {

    $errors[$errorKey == null ? "server" : $errorKey] = [$errorValue == null ? "Terjadi masalah pada server" : $errorValue];

    return error($details, $errors, 500);
}
