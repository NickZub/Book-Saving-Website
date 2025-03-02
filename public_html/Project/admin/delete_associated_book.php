<?php

session_start();
require(__DIR__ . "/../../../lib/functions.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    //exit(header("Location: $BASE_PATH" . "/home.php"));
    redirect("home.php");
}

$id = (int)se($_GET, "id", -1, false);
$username = se($_GET, "username", "", false);
if ($id < 1) {
    flash("Invalid id passed to delete", "danger");
    //exit(header("Location: " . get_url("personal_books.php")));
    redirect("admin/all_associations.php");
}
if ($username == "") {
    flash("Username cannot be empty", "danger");
    //exit(header("Location: " . get_url("personal_books.php")));
    redirect("admin/all_associations.php");
}

$db = getDB();
$query = "DELETE ub FROM `UserBooks` AS ub JOIN Users ON Users.id = ub.userID WHERE ub.bookID = :id AND Users.username = :username";
try {
    $stmt = $db->prepare($query);
    $stmt->execute([":id" => $id, ":username" => $username]);
    flash("Unsaved record with id $id for $username", "success");
} catch (Exception $e) {
    error_log("Error unsaving book $id for $username" . var_export($e, true));
    flash("Error unsaving book $id for $username", "danger");
}
//exit(header("Location: " . get_url("personal_books.php")));
redirect("admin/all_associations.php");
