<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../partials/nav.php");

/* if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    exit(header("Location: $BASE_PATH" . "/home.php"));
} */
?>

<?php

//TODO handle book fetch
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $title = se($_POST, "title", "", false);
    $author = se($_POST, "author", "", false);
    $books = [];
    if ((($action === "fetch") && $title) || (($action === "create") && $title && $author)) {
        if ($action === "fetch") {
            $result = fetch_book($title);
            error_log("Data from API" . var_export($result, true));
            if ($result) {
                $books = $result;
            }
        } else if ($action === "create") {
            foreach ($_POST as $k => $v) {
                if (!in_array($k, ["title", "author", "image", "rating", "bookID"])) {
                    unset($_POST[$k]);
                
                }
                $_POST["bookID"] = null;
                $books = [$_POST];
                var_export($books,true);
                error_log("Cleaned up POST: " . var_export($books, true));
            }
        }
    } else {
        flash("You must provide a book title and author name", "warning");
    }
    //insert data
    $db = getDB();
    $query = "INSERT INTO `Books` ";
    $columns = [];
    $params = [];
    //per record
    $index = 0;
    foreach($books as $currBook){
        foreach($currBook as $k => $v) {
            if(!in_array("`$k`", $columns, true)){
                array_push($columns, "`$k`");
            }
            //Sanitize rating before query. Set 0 if empty or negative, or round to 2 decimal places.
            if($k == "rating"){
                if($v == '')
                    $v = 0.00;
                else
                    $v = sprintf("%.2f", $v);
            }
            $params[":$k$index"] = $v;
        }
        $index += 1;
    }
    $query .= "(" . join(",", $columns) . ")";
    //$query .= " VALUES (" . join(",", array_keys($params)) . ")";
    $query .= " VALUES";
    //Each set of values for the query insertion
    $chunks = array_chunk(array_keys($params),5);
    foreach($chunks as $chunk){
        $query .= " (" . join(",", $chunk) . "),";
    }
    $query = rtrim($query,',');
    $query .= " ON DUPLICATE KEY UPDATE `bookID` = VALUES(`bookID`)";
    error_log("Query: " . $query . "<br>");
    error_log("Params: " . var_export($params, true) . "<br>");
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Inserted record " . $db->lastInsertId(), "success");
    } catch (PDOException $e) {
        echo("Something broke with the query" . var_export($e, true));
        flash("An error occurred", "danger");
    }
}

//TODO handle manual create stock
?>
<div class="container-fluid">
    <h3>Create or Fetch Books</h3>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link bg-success" href="#" onclick="switchTab('create')">Fetch</a>
        </li>
        <li class="nav-item">
            <a class="nav-link bg-success" href="#" onclick="switchTab('fetch')">Create</a>
        </li>
    </ul>
    <div id="fetch" class="tab-target">
        <form method="POST">
            <?php render_input(["type" => "search", "name" => "title", "placeholder" => "Book Title", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "hidden", "name" => "action", "value" => "fetch"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit",]); ?>
        </form>
    </div>
    <div id="create" style="display: none;" class="tab-target">
        <form method="POST">
            <?php render_input(["type" => "text", "name" => "title", "placeholder" => "Book Title", "label" => "Book Title", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "author", "placeholder" => "Author Name", "label" => "Author Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "url", "name" => "image", "placeholder" => "Image URL", "label" => "Image URL"]); ?>
            <?php render_input(["type" => "number", "name" => "rating", "placeholder" => "Book Rating [0-5]", "label" => "Book Rating [0-5]", "rules" => ["step"=>"any", "min"=>"0", "max"=>"5"]]); ?>
            <?php render_input(["type" => "hidden", "name" => "action", "value" => "create"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit", "text" => "Create"]); ?>
        </form>
    </div>
</div>
<script>
    function switchTab(tab) {
        let target = document.getElementById(tab);
        if (target) {
            let eles = document.getElementsByClassName("tab-target");
            for (let ele of eles) {
                ele.style.display = (ele.id === tab) ? "none" : "block";
            }
        }
    }
</script>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../partials/flash.php");
?>
