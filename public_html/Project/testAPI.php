<?php
require(__DIR__ . "/../../partials/nav.php");

$result = [];
if (isset($_GET["book"])) {
    $result = fetch_book($_GET["book"]);
}

?>
<div class="container-fluid">
    <h1>Book Info</h1>
    <p>This is merely a quick sample. Well want to cache data in our DB to save on API quota.</p>
    <form>
        <div>
            <label>Book</label>
            <input name="book" />
            <input type="submit" value="Fetch Book" />
        </div>
    </form>
    <div class="row ">
        <?php if (isset($result)) : ?>
            <?php foreach ($result as $book) : ?>
                <pre>
                    <?php var_export($book);?>
                </pre>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php
require(__DIR__ . "/../../partials/flash.php");