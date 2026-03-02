<?php
// send some CORS headers so the API can be called from anywhere
header("Access-Control-Allow-Origin: *"); // allow from anywhere, you can also specify a domain, e.g. http://example.com
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: $method"); // OPTIONS,GET,POST,PUT,DELETE
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization");
Header("Cache-Control: $cache");
header("Access-Control-Max-Age: 3600");//3600 seconds
// 1)private,max-age=60 (browser is only allowed to cache) 2)no-store(public),max-age=60 (all intermidiary can cache, not browser alone)  3)no-cache (no ceaching at all)

//Include JWT library
require_once(__DIR__ . "/vendor/autoload.php");
//include "../../middleware/jwt_middleware.php";
//$user = verifyJWT();
// now $user has id, username, email, role

include "functions.php";
include "apifunctions.php";


?>