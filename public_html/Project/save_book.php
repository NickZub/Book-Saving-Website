<?php

session_start();
require(__DIR__ . "/../../lib/functions.php");
/* if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    exit(header("Location: $BASE_PATH" . "/home.php"));
} */

$id = se($_GET, "id", -1, false);
if ($id < 1) {
    flash("Invalid id passed to save", "danger");
    //exit(header("Location: " . get_url("list_books.php")));
    redirect("list_books.php");
}

$db = getDB();
try {
    $stmt = $db->prepare("INSERT INTO `UserBooks` (`userID`, `bookID`) VALUES(:userID, :bookID)");
    $stmt->execute([":userID" => get_user_id(), ":bookID" => $id]);
    flash("Saved record with id $id", "success");
} catch (PDOException $e) {
    if ($e->errorInfo[1] === 1062) {
        flash("You already saved this book, please try another", "warning");
    } else {
        error_log("Error saving book $id" . var_export($e, true));
        flash("Error saving book", "danger");
    }
}
        
//exit(header("Location: " . get_url("list_books.php")));
redirect("list_books.php");