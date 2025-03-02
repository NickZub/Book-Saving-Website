<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    //exit(header("Location: $BASE_PATH" . "/home.php"));
    redirect("home.php");
}
//attempt to apply
if (isset($_POST["users"]) && isset($_POST["books"])) {
    $user_ids = $_POST["users"]; //se() doesn't like arrays so we'll just do this
    $book_ids = $_POST["books"]; //se() doesn't like arrays so we'll just do this
    if (empty($user_ids) || empty($book_ids)) {
        flash("Both users and books need to be selected", "warning");
    } else {
        //for sake of simplicity, this will be a tad inefficient
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO UserBooks (userID, bookID) VALUES (:uid, :bid) ON DUPLICATE KEY UPDATE is_active = !is_active");
        foreach ($user_ids as $uid) {
            foreach ($book_ids as $bid) {
                try {
                    $stmt->execute([":uid" => $uid, ":bid" => $bid]);
                    flash("Updated book", "success");
                } catch (PDOException $e) {
                    flash(var_export($e->errorInfo, true), "danger");
                }
            }
        }
    }
}

//search for user by username
$users = [];
$username = "";
if (isset($_POST["username"])) {
    //Username given
    $username = se($_POST, "username", "", false);
    if (!empty($username)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT Users.id, username from Users WHERE username like :username LIMIT 25");
        try {
            $stmt->execute([":username" => "%$username%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $users = $results;
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    } else {
        //Empty username
        //flash("Username must not be empty", "warning");
        $db = getDB();
        $stmt = $db->prepare("SELECT Users.id, username from Users LIMIT 25");
        try {
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $users = $results;
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    }
}
//search for books
$books = [];
$book = "";
if (isset($_POST["book"])) {
    //Book title given
    $book = se($_POST, "book", "", false);
    if (!empty($book)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, title, author FROM Books WHERE title LIKE :title LIMIT 25");
        try {
            $stmt->execute([":title" => "%$book%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $books = $results;
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    } else {
        //Empty book title
        $db = getDB();
        $stmt = $db->prepare("SELECT id, title, author FROM Books LIMIT 25");
        try {
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $books = $results;
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    }
}

?>
<div class="container-fluid">
    <h1>Assign Books</h1>
    <form method="POST">
        <?php render_input(["type" => "search", "name" => "username", "placeholder" => "Username Search", "value" => $username]);/*lazy value to check if form submitted, not ideal*/ ?>
        <?php render_input(["type" => "search", "name" => "book", "placeholder" => "Book Search", "value" => $book]);/*lazy value to check if form submitted, not ideal*/ ?>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <form method="POST">
        <?php render_button(["text" => "Toggle Books", "type" => "submit", "color" => "secondary"]); ?>
        <br><br>
        <?php if (isset($username) && !empty($username)) : ?>
            <input type="hidden" name="username" value="<?php se($username, false); ?>" />
        <?php endif; ?>
        <?php if (isset($book) && !empty($book)) : ?>
            <input type="hidden" name="book" value="<?php se($book, false); ?>" />
        <?php endif; ?>
        <table class="table">
            <thead>
                <th>Users</th>
                <th>Books to Assign</th>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <table class="table">
                            <?php foreach ($users as $user) : ?>
                                <tr>
                                    <td>
                                        <label for="user_<?php se($user, 'id'); ?>"><?php se($user, "username"); ?></label>
                                        <input id="user_<?php se($user, 'id'); ?>" type="checkbox" name="users[]" value="<?php se($user, 'id'); ?>" />
                                    </td>
                                    <!-- <td><?php se($user, "roles", "No Roles"); ?></td> -->
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <td>
                        <?php foreach ($books as $book) : ?>
                            <div>
                                <label for="role_<?php se($book, 'id'); ?>"><?php se($book, "title"); ?></label>
                                <input id="role_<?php se($book, 'id'); ?>" type="checkbox" name="books[]" value="<?php se($book, 'id'); ?>" />
                                <br>
                                <?php se($book, "author") ?>
                                <hr>
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>