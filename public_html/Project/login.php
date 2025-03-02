<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<div class="container-fluid">
    <form onsubmit="return validate(this)" method="POST">
        <?php render_input(["type"=>"text", "id"=>"email", "name"=>"email", "label"=>"Email/Username", "rules"=>["required"=>true]]);?>
        <?php render_input(["type"=>"password", "id"=>"password", "name"=>"password", "label"=>"Password", "rules"=>["required"=>true, "minlength"=>8]]);?>
        <?php render_button(["text"=>"Login", "type"=>"submit"]);?>
    </form>
</div>
<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success
        let input = form.email.value;
        let password = form.password.value;
        let hasError = false;
        if(input.length === 0){
            flash("[Client] Input must not be empty", "warning");
            hasError = true;
        }
        if(input.includes('@')){
            input = sanitize_email(input);
            if(!is_valid_email(input)){
                flash("[Client] Invalid email address", "warning");
                hasError = true;
            }
        }
        else{
            if(!is_valid_username(input)){
                flash("[Client] Invalid username", "warning");
                hasError = true;
            }
        }
        if(password.length === 0){
            flash("[Client] Password must not be empty", "warning");
            hasError = true;
        }
        if(!is_valid_password(password)){
            flash("[Client] Password too short", "warning");
            hasError = true;
        }
        return !hasError;
    }
</script>
<?php
//TODO 2: add PHP Code
if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);

    //TODO 3
    $hasError = false;
    if (empty($email)) {
        flash("Email must not be empty", "warning");
        $hasError = true;
    }
    if (str_contains($email, "@")) {
        //sanitize
        $email = sanitize_email($email);
        //validate
        if (!is_valid_email($email)) {
            flash("Invalid email address", "warning");
            $hasError = true;
        }
    } else {
        if (!is_valid_username($email)) {
            flash("Invalid username", "warning");
            $hasError = true;
        }
    }
    if (empty($password)) {
        flash("Password must not be empty", "warning");
        $hasError = true;
    }
    if (!is_valid_password($password)) {
        flash("Password too short", "warning");
        $hasError = true;
    }
    if (!$hasError) {
        //flash("Welcome, $email");
        //TODO 4
        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, username, password from Users 
        where email = :email or username = :email");
        try {
            $r = $stmt->execute([":email" => $email]);
            if ($r) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $hash = $user["password"];
                    unset($user["password"]);
                    if (password_verify($password, $hash)) {
                        //flash("Weclome $email");
                        $_SESSION["user"] = $user; //sets our session data from db
                        //lookup potential roles
                        $stmt = $db->prepare("SELECT Roles.name FROM Roles 
                        JOIN UserRoles on Roles.id = UserRoles.role_id 
                        where UserRoles.user_id = :user_id and Roles.is_active = 1 and UserRoles.is_active = 1");
                        $stmt->execute([":user_id" => $user["id"]]);
                        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC); //fetch all since we'll want multiple
                        //save roles or empty array
                        if ($roles) {
                            $_SESSION["user"]["roles"] = $roles; //at least 1 role
                        } else {
                            $_SESSION["user"]["roles"] = []; //no roles
                        }
                        flash("Welcome, " . get_username());
                        exit(header("Location: home.php"));
                    } else {
                        flash("Invalid password", "warning");
                    }
                } else {
                    flash("Email or Username not found", "danger");
                }
            }
        } catch (Exception $e) {
            flash("<pre>" . var_export($e, true) . "</pre>");
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
