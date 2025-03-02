<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../partials/nav.php");

/* if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    exit(header("Location: $BASE_PATH" . "/home.php"));
} */

is_logged_in(true,get_url("login.php"));

?>

<?php
$id = se($_GET, "id", -1, false);

$book = [];
if ($id > -1) {
    //fetch
    $db = getDB();
    $query = "SELECT bookID, title, author, image, rating, created, modified FROM `Books` WHERE id = :id";
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
    //exit(header("Location:" . get_url("list_books.php")));
    redirect("personal_books.php");
}
foreach ($book as $key => &$value) {
    if (is_null($value)) {
        $book[$key] = "N/A";
    }

    if($key == "image"){
        $url_validation_regex = "/^https?:\\/\\/(?:www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b(?:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*)$/";
        if(!preg_match($url_validation_regex,$value)){
            $value = "https://clipartix.com/wp-content/uploads/2016/08/Asking-probing-questions-clipart.jpg";
        }
    }
}
unset($value);
//TODO handle manual create stock
?>
<div class="container-fluid">
    <h3>Book: <?php se($book, "title", "Unknown"); ?></h3>
    <div>
        <a href="<?php echo get_url("personal_books.php"); ?>" class="btn btn-secondary">Back</a>
        <?php if (has_role("Admin")) : ?>
            <a href="<?php echo get_url("admin/edit_book.php"); ?>?id=<?php se($id,""); ?>" class="btn btn-secondary">Edit</a>
        <?php endif; ?>
    </div>
    <!-- https://i.kym-cdn.com/entries/icons/original/000/029/959/Screen_Shot_2019-06-05_at_1.26.32_PM.jpg -->
    <div class="card mx-auto" style="width: 18rem;">
        <img src=<?php se($book, "image"); ?> class="card-img-top" alt="...">
        <div class="card-body">
            <h5 class="card-title"><?php se($book, "title", "Unknown"); ?></h5>
            <div class="card-text">
                <ul class="list-group">
                    <li class="list-group-item">Author Name: <?php se($book, "author", "Unknown"); ?></li>
                    <li class="list-group-item">Rating: <?php se($book, "rating", "Unknown"); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../partials/flash.php");
?>