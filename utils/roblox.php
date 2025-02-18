<?php 

    http_response_code(response_code: 200);

    function returnError(string $message, int $code = 400): null {
        $error = array(
            "error" => true,
            "message" => $message,
            "status" => $code,
        );
        http_response_code(response_code: $code);
        return exit(json_encode(value: $error));
    }

    
