<?php

session_start();
require(__DIR__ . "/../../lib/functions.php");

is_logged_in(true,get_url("login.php"));

$db = getDB();
$query = "DELETE FROM `UserBooks` WHERE userID = :userID";
try {
    $stmt = $db->prepare($query);
    $stmt->execute(["userID" => get_user_id()]);
    flash("Unsaved every personal book", "success");
} catch (Exception $e) {
    error_log("Error unsaving books" . var_export($e, true));
    flash("Error unsaving books", "danger");
}
//exit(header("Location: " . get_url("personal_books.php")));
redirect("personal_books.php");
