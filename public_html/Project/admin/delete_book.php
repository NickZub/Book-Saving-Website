<?php

session_start();
require(__DIR__ . "/../../../lib/functions.php");
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    //exit(header("Location: $BASE_PATH" . "/list_books.php"));
    redirect("list_books.php");
}

$id = se($_GET, "id", -1, false);
if ($id < 1) {
    flash("Invalid id passed to delete", "danger");
    exit(header("Location: " . get_url("admin/list_books.php")));
}

$db = getDB();
$query1 = "DELETE FROM `UserBooks` WHERE bookID = :bookID";
$query2 = "DELETE FROM `Books` WHERE id = :id";
try {
    $db->beginTransaction();
    $stmt1 = $db->prepare($query1);
    $stmt1->execute([":bookID" => $id]);
    flash("Deleted UserBook with id $id", "success");
    $stmt2 = $db->prepare($query2);
    $stmt2->execute([":id" => $id]);
    flash("Deleted Book with id $id", "success");
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    error_log("Error deleting book $id" . var_export($e, true));
    flash("Error deleting record", "danger");
}
//exit(header("Location: " . get_url("list_books.php")));
redirect("list_books.php");
