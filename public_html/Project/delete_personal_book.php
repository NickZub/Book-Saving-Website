<?php

session_start();
require(__DIR__ . "/../../lib/functions.php");

is_logged_in(true,get_url("login.php"));

$id = se($_GET, "id", -1, false);
if ($id < 1) {
    flash("Invalid id passed to delete", "danger");
    exit(header("Location: " . get_url("personal_books.php")));
}

$db = getDB();
$query = "DELETE FROM `UserBooks` WHERE bookID = :id AND userID = :userID";
try {
    $stmt = $db->prepare($query);
    $stmt->execute([":id" => $id, "userID" => get_user_id()]);
    flash("Unsaved record with id $id", "success");
} catch (Exception $e) {
    error_log("Error unsaving book $id" . var_export($e, true));
    flash("Error unsaving record", "danger");
}
//exit(header("Location: " . get_url("personal_books.php")));
redirect("personal_books.php");
