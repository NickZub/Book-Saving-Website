<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

/* if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
} */

is_logged_in(true,get_url("login.php"));

//build search form
$form = [
    ["type" => "text", "name" => "book_title", "placeholder" => "Book Title", "label" => "Book Title", "include_margin" => false],
    ["type" => "text", "name" => "author_name", "placeholder" => "Author Name", "label" => "Author Name", "include_margin" => false],
    ["type" => "number", "name" => "rating_low", "placeholder" => "Rating Low", "label" => "Rating Low", "rules" => ["step"=>"any", "min"=>"0", "max"=>"5"],"include_margin" => false],
    ["type" => "number", "name" => "rating_high", "placeholder" => "Rating High", "label" => "Rating High", "rules" => ["step"=>"any", "min"=>"0", "max"=>"5"], "include_margin" => false],
    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["rating" => "Rating", "created" => "Created"], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => ["asc" => "+", "desc" => "-"], "include_margin" => false],
    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "10", "rules" => ["min"=>"1", "max"=>"100"], "include_margin" => false],
];

$query = "SELECT id, bookID, title, author, image, rating FROM `Books` WHERE id NOT IN 
(SELECT bookID FROM `UserBooks`)";//ORDER BY created DESC LIMIT 25

$params = [];
$session_key = $_SERVER["SCRIPT_NAME"];
$is_clear = isset($_GET["clear"]);
if ($is_clear) {
    session_delete($session_key);
    unset($_GET["clear"]);
    //exit(header("Location: " . $session_key));
    redirect($session_key);
} else {
    $session_data = session_load($session_key);
}

if (count($_GET) == 0 && isset($session_data) && count($session_data) > 0) {
    if ($session_data) {
        $_GET = $session_data;
    }
}

if (count($_GET) > 0) {
    session_save($session_key, $_GET);
    $keys = array_keys($_GET);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $_GET[$v["name"]];
        }
    }
    //book title
    $book_title = se($_GET, "book_title", "", false);
    if (!empty($book_title)) {
        $query .= " AND title like :title";
        $params[":title"] = "%$book_title%";
    }
    //author name
    $author_name = se($_GET, "author_name", "", false);
    if (!empty($author_name)) {
        $query .= " AND author like :author";
        $params[":author"] = "%$author_name%";
    }
    //rating low
    $rating_low = se($_GET, "rating_low", "-1", false);
    if (!empty($rating_low) && $rating_low > -1) {
        $query .= " AND rating >= :rating_low";
        $params[":rating_low"] = $rating_low;
    }
    //rating high
    $rating_high = se($_GET, "rating_high", "-1", false);
    if (!empty($rating_high) && $rating_high > -1) {
        $query .= " AND rating <= :rating_high";
        $params[":rating_high"] = $rating_high;
    }
    //sort and order
    $sort = se($_GET, "sort", "created", false);
    if (!in_array($sort, ["rating", "created"])) {
        $sort = "created";
    }
    $order = se($_GET, "order", "desc", false);
    if (!in_array($order, ["asc", "desc"])) {
        $order = "desc";
    }
    //IMPORTANT make sure you fully validate/trust $sort and $order (sql injection possibility)
    $query .= " ORDER BY $sort $order";
    //limit
    try {
        $limit = (int)se($_GET, "limit", "10", false);
    } catch (Exception $e) {
        $limit = 10;
    }
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
    }
    //IMPORTANT make sure you fully validate/trust $limit (sql injection possibility)
    $query .= " LIMIT $limit";
}
else {
    $query .= " LIMIT 10";
}

$db = getDB();
$stmt1 = $db->prepare($query);
$stmt2 = $db->prepare("SELECT id, bookID, title, author, image, rating FROM `Books` WHERE id NOT IN (SELECT bookID FROM `UserBooks`)");
$results = [];
$count = 0;
$total = 0;
try {
    $stmt1->execute($params);
    $r1 = $stmt1->fetchAll();
    if ($r1) {
        $results = $r1;
        $count = count($results);
    }
    $stmt2->execute();
    $r2 = $stmt2->fetchAll();
    if ($r2) {
        $total = count($r2);
    }
} catch (PDOException $e) {
    error_log("Error fetching stocks " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

$table = ["data" => $results, "title" => "Unsaved Books", "ignored_columns" => ["id", "bookID"], "edit_url" => get_url("admin/edit_book.php"), "view_url" => get_url("admin/view_unsaved_book.php")];
?>
<div class="container-fluid">
    <h3>Unassociated Books</h3>
    <form method="GET">
        <div class="row mb-3" style="align-items: flex-end;">

            <?php foreach ($form as $k => $v) : ?>
                <div class="col">
                    <?php render_input($v); ?>
                </div>
            <?php endforeach; ?>

        </div>
        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Filter"]); ?>
        <a href="?clear" class="btn btn-secondary">Clear</a>
    </form>
    <p>Number of results: <?php se($count); ?>/<?php se($total); ?></p>
    <?php render_table($table); ?>
</div>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>