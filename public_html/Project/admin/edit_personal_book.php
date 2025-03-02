<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    //exit(header("Location: $BASE_PATH" . "/list_books.php"));
    redirect("personal_books.php");
}
?>

<?php
$id = se($_GET, "id", -1, false);
//TODO handle book fetch
if (isset($_POST["title"]) && isset($_POST["author"])) {
    foreach ($_POST as $k => $v) {
        if (!in_array($k, ["title", "author", "image", "rating", "bookID"])) {
            unset($_POST[$k]);
        }
        $books = $_POST;
        error_log("Cleaned up POST: " . var_export($books, true));
    }
    //insert data
    $db = getDB();
    $query = "UPDATE `Books` SET ";

    $params = [];
    //per record
    foreach ($books as $k => $v) {
        if($k == "bookID"){
            if($v == ''){
                $v = null;
            }
        }
        if ($params) {
            $query .= ",";
        }
        //be sure $k is trusted as this is a source of sql injection
        $query .= "$k=:$k";
        $params[":$k"] = $v;
    }

    $query .= " WHERE id = :id";
    $params[":id"] = $id;
    error_log("Query: " . $query);
    error_log("Params: " . var_export($params, true));
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Updated record ", "success");
    } catch (PDOException $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred", "danger");
    }
}

$book = [];
if ($id > -1) {
    //fetch
    $db = getDB();
    
    $query = "SELECT bookID, title, author, image, rating FROM `Books` WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $book = $r;
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }
} else {
    flash("Invalid id passed", "danger");
    //die(header("Location:" . get_url("admin/list_books.php")));
    redirect("personal_books.php");
}
if ($book) {
    if($book["bookID"] == ''){
        $book["bookID"] = null;
    }
    $form = [
        ["type" => "text", "name" => "title", "placeholder" => "Book Title", "label" => "Book Title", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "author", "placeholder" => "Author Name", "label" => "Author Name", "rules" => ["required" => "required"]],
        ["type" => "url", "name" => "image", "placeholder" => "Image URL", "label" => "Image URL"],
        ["type" => "number", "name" => "rating", "placeholder" => "Book Rating [0-5]", "label" => "Book Rating [0-5]", "rules" => ["step"=>"any", "min"=>"0", "max"=>"5"]],
        ["type" => "hidden", "name" => "bookID", "value" => $book["bookID"]],
    ];
    $keys = array_keys($book);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $book[$v["name"]];
        }
    }
}
//TODO handle manual create stock
?>
<div class="container-fluid">
    <h3>Edit Books</h3>
    <form method="POST">
        <?php foreach ($form as $k => $v) {
            render_input($v);
        } ?>
        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Update"]); ?>
    </form>

</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>