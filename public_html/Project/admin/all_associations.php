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
    ["type" => "text", "name" => "username", "placeholder" => "Username", "label" => "Username", "include_margin" => false],
    ["type" => "text", "name" => "book_title", "placeholder" => "Book Title", "label" => "Book Title", "include_margin" => false],
    ["type" => "text", "name" => "author_name", "placeholder" => "Author Name", "label" => "Author Name", "include_margin" => false],
    ["type" => "number", "name" => "rating_low", "placeholder" => "Rating Low", "label" => "Rating Low", "rules" => ["step"=>"any", "min"=>"0", "max"=>"5"],"include_margin" => false],
    ["type" => "number", "name" => "rating_high", "placeholder" => "Rating High", "label" => "Rating High", "rules" => ["step"=>"any", "min"=>"0", "max"=>"5"], "include_margin" => false],
    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["b.rating" => "Rating", "b.created" => "Created"], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => ["asc" => "+", "desc" => "-"], "include_margin" => false],
    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "10", "rules" => ["min"=>"1", "max"=>"100"], "include_margin" => false],
];

$query = "SELECT b.id, b.bookID, b.title, b.author, b.image, b.rating, b.created, GROUP_CONCAT(u.username SEPARATOR ',') AS Users FROM `Books` b 
    JOIN `UserBooks` ub ON b.id = ub.bookID
    JOIN `Users` u ON ub.userID = u.id WHERE is_active=1";//ORDER BY created DESC LIMIT 25

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
$username = "";
if (count($_GET) > 0) {
    session_save($session_key, $_GET);
    $keys = array_keys($_GET);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $_GET[$v["name"]];
        }
    }
    //username
    $username = se($_GET, "username", "", false);
    if (!empty($username)) {
        $query .= " AND u.username like :username";
        $params[":username"] = "%$username%";
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
    $sort = se($_GET, "sort", "b.created", false);
    if (!in_array($sort, ["rating", "created"])) {
        $sort = "b.created";
    }
    $order = se($_GET, "order", "desc", false);
    if (!in_array($order, ["asc", "desc"])) {
        $order = "desc";
    }
    //IMPORTANT make sure you fully validate/trust $sort and $order (sql injection possibility)
    $query .= " GROUP BY b.id, b.bookID, b.title, b.author, b.image, b.rating, b.created ORDER BY $sort $order";
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
else{
    $query .= " GROUP BY b.id, b.bookID, b.title, b.author, b.image, b.rating, b.created LIMIT 10";
}

$db = getDB();
$stmt1 = $db->prepare($query);
$stmt2 = $db->prepare("SELECT b.id, b.bookID, b.title, b.author, b.image, b.rating, b.created, GROUP_CONCAT(u.username SEPARATOR ',') AS Users FROM `Books` b JOIN `UserBooks` ub ON b.id = ub.bookID JOIN `Users` u ON ub.userID = u.id WHERE is_active=1 GROUP BY b.id, b.bookID, b.title, b.author, b.image, b.rating, b.created");
$results = [];
$count = 0;
$total = 0;
try {
    //Get query data and filtered count
    $stmt1->execute($params);
    $r1 = $stmt1->fetchAll();
    if ($r1) {
        $results = $r1;
        $count = count($results);
    }
    //Get total count before filtering results
    $stmt2->execute();
    $r2 = $stmt2->fetchAll();
    if ($r2) {
        $total = count($r2);
    }
} catch (PDOException $e) {
    error_log("Error fetching stocks " . var_export($e, true));
    flash("Unhandled error occurred $query", "danger");
}

//$table = ["data" => $results, "title" => "Saved Books", "ignored_columns" => ["id", "bookID", "image"], "edit_url" => get_url("admin/edit_personal_book.php"), "view_url" => get_url("view_personal_book.php")];
$_ignored_columns = ["id", "bookID", "image", "created"];
?>
<div class="container-fluid">
    <h3>Associated Books</h3>
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
        <?php if ($username != "") : ?>
            <a href="view_associated_book.php?" class="btn btn-danger">Delete All Associations</a>
        <?php endif; ?>
    </form>
    <!-- ?php render_table($table); ?> RENDER THE TABLE BELOW -->
    <p>Number of results: <?php se($count); ?>/<?php se($total); ?></p>
    <table class="table">
        <th>Title</th>
        <th>Author</th>
        <th>Rating</th>
        <th>Users</th>
        <th></th>
        <th>Actions</th>
        <tbody>
            <?php if (is_array($results) && count($results) > 0) : ?>
                <?php foreach ($results as $row) : ?>
                    <tr>
                        <?php foreach ($row as $k => $v) : ?>
                            <?php if (!in_array($k, $_ignored_columns)) : ?>
                                <?php if ($k != "Users") : ?>
                                    <td><?php se($v); ?></td>
                                <!-- If we are iterating username, add the view and delete association options -->
                                <?php else : ?>
                                    <?php $eachUser = explode(',', $v);?>
                                    <td>
                                    <?php foreach ($eachUser as $currUser) : ?>
                                        <div><a href="profile.php?username=<?php se($currUser); ?>"><?php se($currUser); ?></a> <a href="delete_associated_book.php?username=<?php se($currUser); ?>&id=<?php se($row,"id"); ?>">Delete</a></div>
                                    <?php endforeach; ?>
                                    <td>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <td>
                            <a href="view_associated_book.php?id=<?php se($row, "id"); ?>" class="btn btn-primary">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="100%">No Saved Books</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>