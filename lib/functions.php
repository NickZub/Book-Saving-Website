<?php
//TODO 1: require db.php
require_once(__DIR__ . "/db.php");
//This is going to be a helper for redirecting to our base project path since it's nested in another folder
//This MUST match the folder name exactly
$BASE_PATH = '/Project';
//TODO 4: Flash Message Helpers
require(__DIR__ . "/flash_messages.php");

//require safer_echo.php
require(__DIR__ . "/safer_echo.php");
//TODO 2: filter helpers
require(__DIR__ . "/sanitizers.php");

//TODO 3: User helpers
require(__DIR__ . "/user_helpers.php");


//duplicate email/username
require(__DIR__ . "/duplicate_user_details.php");
//reset session
require(__DIR__ . "/reset_session.php");
//return relative or absolute path of file
require(__DIR__ . "/get_url.php");
//dyanmic component generation
require(__DIR__ . "/render_functions.php");
//api curl request
require(__DIR__ . "/api_helper.php");
//pass needed data to api wrapper
require(__DIR__ . "/book_api.php");
//session save, load, and delete
require(__DIR__ . "/session_store.php");
//redirect using javascript and html
require(__DIR__ . "/redirect.php");
?>