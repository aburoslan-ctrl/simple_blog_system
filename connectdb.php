<?php
//Database Connection to 
$server= 'localhost';
$username= 'root';
$password= '';
$dbname= 'simple_blog'; 


$connect= mysqli_connect($server,$username,$password,$dbname);
if(!$connect){
    http_response_code(500);
    die(json_encode(["status"=>"error","message"=>"Database connection failed"]));
}
mysqli_set_charset($connect,'utf8mb4');

// SendGrid / password reset configuration
define('SENDGRID_API_KEY',       getenv('SENDGRID_API_KEY') ?: '');
define('SENDGRID_FROM_EMAIL',    'jamiu@aburoslan.site');
define('SENDGRID_FROM_NAME',     'simple_blog');
define('RESET_TOKEN_EXPIRY_MIN', 30);

// ?>