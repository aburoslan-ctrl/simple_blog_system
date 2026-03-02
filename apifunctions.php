
<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

 function respondMethodNotAlowed()
    {
        $method = getenv('REQUEST_METHOD');
        $endpoint = getCurrentFileFullURL();
        $errordata = [
            "code" => "", 
            "text" => "The request method used is not valid.",
            "link" => "https://", 
            "hint" => [
                "Ensure you use the method stated in the documentation.",
                "Check your environment variable",
                "Missing or Incorrect Headers"
            ]
        ];
        setTimeZoneForUser('');
        $data = ["status" => false, "text" =>"Method Not Allowed", "data" => [], "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header("HTTP/1.1 405 Method Not allowed");
        http_response_code(405);

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    function respondBadRequest($userErrMessage,$data=[])
    {
        $method = getenv('REQUEST_METHOD');
        $endpoint = getCurrentFileFullURL();

        $errordata = ["code" => '', "text" => "The body request is not valid, missing compulsory parameter or invalid data sent.", "link" => "https://", "hint" => [
            "Ensure you use the request data stated in the documentation.",
            "Check your environment variable",
            "Missing or Incorrect Headers"
        ]];
        setTimeZoneForUser('');
        $data = ["status" => false, "text" => $userErrMessage,"data" => $data, "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header("HTTP/1.1 400 Bad request");
        http_response_code(400);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    function respondUnauthorized()
    {
        $method = getenv('REQUEST_METHOD');
        $endpoint = getCurrentFileFullURL();

        $errordata = ["code" => '', "text" => "Access token invalid or not sent", "link" => "https://", "hint" => [
            "Check your environment variable",
            "Missing or Incorrect Headers",
            "Change access token",
            "Ensure access token is sent and its valid",
            "Follow the format stated in the documentation", "All letters in upper case must be in upper case",
            "Ensure the correct method is used","Ensure authorization is sent with capital A"
        ]];
        setTimeZoneForUser('');
        $data = ["status" => false, "text" => 'Unauthorized', "data" =>[], "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header("HTTP/1.1 401 Unauthorized");
        http_response_code(401);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    function respondInternalError($errorText,$usererr = null) {
        // If user error response is not provided, use the default internal error message
        if ($usererr === null) {
            $usererr = "Internal Server Error";
        }
        $method = getenv('REQUEST_METHOD');
        $endpoint = getCurrentFileFullURL();
 
        $errordata = ["code" => '', "text" => $errorText, "link" => "https://", "hint" => [
            "Check the code",
            "Make sure data type needed is sent",
        ]];
        setTimeZoneForUser('');
        $data = ["status" => false, "text" =>$usererr, "data" =>[], "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        
        
        $log = dirname(__DIR__) . '/logs/' . date('Y-m-d') . '.txt';
        ini_set('error_log', $log);
        if(strpos($errorText,"wwwcardifyaf2104")!==false){
        $message = "Uncaught exception: $errorText";
        //SEND TO ADMIN TG
        system_notify_crash_handler($message,"System");
        
        error_log($message);
            
        }
        
        header("HTTP/1.1 500 Internal Error");
        http_response_code(500);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    function respondOK($maindata, $text)
    {
        $method = getenv('REQUEST_METHOD');
        $endpoint = getCurrentFileFullURL();

        $errordata = [];
        setTimeZoneForUser('');
        $data = ["status" => true, "text" => "$text", "data" => $maindata, "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header("HTTP/1.1 200 OK");
        http_response_code(200);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    function respondTooManyRequest(){
        $method = getenv('REQUEST_METHOD');
        $endpoint = getCurrentFileFullURL();

        $errordata = ["code" => '', "text" => "Too Many Requests", "link" => "https://", "hint" => [
            "Your server is calling the API contineously",
            "Your server is calling the API contineously",
        ]];
        setTimeZoneForUser('');
        $data = ["status" => false, "text" => "Too Many Requests", "data" =>[], "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header("HTTP/1.1 429 Too Many Requests");
        http_response_code(429);
            // 405 Method Not Allowed
        echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        exit;
    }
    function respondNotCompleted(){
        $method = getenv('REQUEST_METHOD');
        $endpoint = getCurrentFileFullURL();

        $errordata = ["code" => '', "text" => "Request did not get completed", "link" => "https://", "hint" => [
            "Check server",
        ]];
        setTimeZoneForUser('');
        $data = ["status" => false, "text" => "Request did not get completed", "data" =>[], "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header('HTTP/1.1 202 Not Completed');
        http_response_code(202);
        echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
            // 202 Accepted Indicates that the request has been received but not completed yet.
        exit;
    }

    function respondURLChanged($data){
        $method = getenv('REQUEST_METHOD');
        $endpoint = getCurrentFileFullURL();

        $errordata = ["code" => '', "text" => "URL changed", "link" => "https://", "hint" => [
            "Check documentation",
            "Invalid URL"
        ]];
        setTimeZoneForUser('');
        $data = ["status" => false, "text" => "URL changed", "data" =>[], "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header('HTTP/1.1 302 URL changed');
        http_response_code(302);
        echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
           // The URL of the requested resource has been changed temporarily
        exit;
    }
    function respondNotFound($data){
        $method = getenv('REQUEST_METHOD');
        $endpoint = getCurrentFileFullURL();

        $errordata = ["code" => '', "text" => "Data not found", "link" => "https://", "hint" => [
            "URL not valid",
            "Check data sent",
        ]];
        setTimeZoneForUser('');
        $data = ["status" => false, "text" => "Data not found", "data" =>[], "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header('HTTP/1.1 404 Not found');
        http_response_code(404);
          //  Not found
        echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        exit;
    }
    function respondForbiddenAuthorized($data){
        $method = getenv('REQUEST_METHOD');
        $endpoint = getCurrentFileFullURL();

        $errordata = ["code" => '', "text" => "Authorization Not allowed", "link" => "https://", "hint" => [
            "Make sure to have the permission",
            "Read documentation",
        ]];
        setTimeZoneForUser('');
        $data = ["status" => false, "text" => "Authorization Not allowed", "data" =>[], "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header("HTTP/1.1 403 Forbidden");
        http_response_code(403);
            // 403 Forbidden
        // Unauthorized request. The client does not have access rights to the content. Unlike 401, the client’s identity is known to the server.
        echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        exit;
    }
    // Generated a unique pub key for all users
    function getTokenToSendAPI($userPubkey)
    {
       
        try {
          
                $companyprivateKey = "PrivatekeyPrivatekeyPrivatekeyPrivatekeyPrivatekeyPrivatekeyPrivatekey";
                $serverName = 'SerServernameServernameServernamevername';
                $minutetoend =60;//how many min
                $issuedAt = new \DateTimeImmutable();
                $expire = $issuedAt->modify("+$minutetoend minutes")->getTimestamp();
                $username = "$userPubkey";
                $data = [
                    'iat' => $issuedAt->getTimestamp(),
                    // Issued at: time when the token was generated
                    'iss' => $serverName,
                    // Issuer
                    'nbf' => $issuedAt->getTimestamp(),
                    // Not before
                    'exp' => $expire,
                    // Expire
                    'usertoken' => $username, // User name
                ];

                // Encode the array to a JWT string.
                //  get token below
                $auttokn = JWT::encode(
                    $data,
                    $companyprivateKey,
                    'HS512'
                );
                return $auttokn;
        } catch (\Exception $e) {
            respondInternalError(get_details_from_exception($e));
        }
    }
    // $whocalled=1 user 2 admin
    function ValidateAPITokenSentIN($whocalled=1)
    {
        try {
             $companyprivateKey = "PrivatekeyPrivatekeyPrivatekeyPrivatekeyPrivatekeyPrivatekeyPrivatekey";
                $serverName = 'SerServernameServernameServernamevername';

            $headerName = 'Authorization';
            $headers = getallheaders();
 
            $signraturHeader = isset($headers[$headerName]) ? $headers[$headerName] : null;
            if ($signraturHeader == null) {
                $signraturHeader = isset($_SERVER['Authorization']) ? $_SERVER['Authorization'] : "";
            } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
                $signraturHeader = trim($_SERVER["HTTP_AUTHORIZATION"]);
            }
       
            if (!preg_match('/Bearer\s(\S+)/', $signraturHeader, $matches)) {
                respondUnauthorized();
            }

            $jwt = $matches[1];

            if (!$jwt) {
                respondUnauthorized();
            }
            $secretKey = $companyprivateKey;
            $token = JWT::decode($jwt, new Key($secretKey,'HS512'));
            $now = new \DateTimeImmutable();

            if ($token->iss !== $serverName || $token->nbf > $now->getTimestamp() || $token->exp < $now->getTimestamp() || input_is_invalid($token->usertoken)) {
                respondUnauthorized();
            }
            $usertoken= $token->usertoken;
            // in 60 min 300 max API call
            // if(userHasCalledAPIToMaxLimit($usertoken, 300 ,  60)){
            //     respondTooManyRequest();
            // }
            return $token;
        } catch (\Exception $e) {
            if(str_contains($e->getMessage(),'Expired')){
                respondUnauthorized();

            }else  if(str_contains($e->getMessage(),'Signature verification failed')){
                respondUnauthorized();

            }else  if(str_contains($e->getMessage(),'Wrong number of segments')){
                respondUnauthorized();

            }else{
                respondInternalError(get_details_from_exception($e));
            }
        }
    }

    function userHasCalledAPIToMaxLimit($userId, $limit = 100, $interval = 3600) {
        try{
                    // preventing too many request at a time
            // Define the maximum number of requests allowed per minute
            $maxRequestsPerMinute =$limit;
            // Create a unique identifier for the client, e.g., based on the client's IP address
            $clientIdentifier = 'rate_limit_' .$userId;
            // Retrieve the current timestamp
            $currentTimestamp = time();
            $folderPath = dirname(__DIR__);
            $filename =  $folderPath . "/logs/cache_call/rate_limit_data.json";
            // Set the path to the rate limit data file
            $rateLimitDataFile =  $filename ;
            // Initialize an empty array for rate limit data
            $requestData = [];
            
            // Check if the rate limit data file exists
            if (file_exists($rateLimitDataFile)) {
                // Load existing data from the file
                $requestData = json_decode(file_get_contents($rateLimitDataFile), true);
            }
            
            // Check if the client identifier exists in the request data
            if (!isset($requestData[$clientIdentifier])) {
                $requestData[$clientIdentifier] = [
                    'timestamp' => $currentTimestamp,
                    'count' => 1,
                ];
            } else {
                $lastTimestamp = $requestData[$clientIdentifier]['timestamp'];
                
                // Check if the time window has elapsed (1 minute in this case)
                if (($currentTimestamp - $lastTimestamp) > $interval) {
                    $requestData[$clientIdentifier] = [
                        'timestamp' => $currentTimestamp,
                        'count' => 1,
                    ];
                } else {
                    // Increment the request count
                    $requestData[$clientIdentifier]['count']++;
                }
            }
            
            // Save the updated request data
            file_put_contents( $filename , json_encode($requestData));
            
            // Check if the client has exceeded the allowed number of requests
            if ($requestData[$clientIdentifier]['count'] > $maxRequestsPerMinute) {
                //reset
                $requestData[$clientIdentifier]['count']=0;
                return true;
            }
         return false;
        } catch (\Exception $e) {
            respondInternalError(get_details_from_exception($e));
        }
    }