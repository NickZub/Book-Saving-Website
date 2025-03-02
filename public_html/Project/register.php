<?php
    require(__DIR__ . "/../../partials/nav.php");
?>
<div class="container-fluid">
    <form onsubmit="return validate(this)" method="POST">
        <?php render_input(["type"=>"email", "id"=>"email", "name"=>"email", "label"=>"Email", "rules"=>["required"=>true]]);?>
        <?php render_input(["type"=>"text", "id"=>"username", "name"=>"username", "label"=>"Username", "rules"=>["required"=>true, "maxlength"=>30]]);?>
        <?php render_input(["type"=>"password", "id"=>"password", "name"=>"password", "label"=>"Password", "rules"=>["required"=>true, "minlength"=>8]]);?>
        <?php render_input(["type"=>"password", "id"=>"confirm", "name"=>"confirm", "label"=>"Confirm Password", "rules"=>["required"=>true,"minlength"=>8]]);?>
        <?php render_button(["text"=>"Register", "type"=>"submit"]);?>
    </form>
</div>
<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success
        let email = form.email.value;
        let username = form.username.value;
        let password = form.password.value;
        let confirm = form.confirm.value;
        let hasError = false;

        if(email.length === 0){
            flash("[Client] Email must not be empty", "warning");
            hasError = true;
        }
        email = sanitize_email(email);
        if(!is_valid_email(email)){
            flash("[Client] Invalid email address", "warning");
            hasError = true;
        }
        if(username.length === 0){
            flash("[Client] Username must not be empty", "warning");
            hasError = true;
        }
        if(!is_valid_username(username)){
            flash("[Client] Invalid username", "warning");
            hasError = true;
        }
        if(password.length === 0){
            flash("[Client] Password must not be empty", "warning");
            hasError = true;
        }
        if(!is_valid_password(password)){
            flash("[Client] Password too short", "warning");
            hasError = true;
        }
        if(confirm.length === 0){
            flash("[Client] Confirm password must not be empty", "warning");
            hasError = true;
        }
        if(!is_valid_password(confirm)){
            flash("[Client] Confirm password too short", "warning");
            hasError = true;
        }
        if(password !== confirm){
            flash("[Client] Password and Confirm Password do not match", "warning");
            hasError = true;
        }
        return !hasError;
    }
</script>
<?php
 //TODO 2: add PHP Code
 if(isset($_POST["email"]) && isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["confirm"])){
    $email = se($_POST, "email", "", false);
    $username = se($_POST, "username", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST, "confirm", "", false);
    //TODO 3: validate/use
    $hasError = false;
    if(empty($email)){
        flash("Email must not be empty", "warning");
        $hasError = true;
    }
    $email = sanitize_email($email);
    if(!is_valid_email($email)){
        flash("Invalid email address", "warning");
        $hasError = true;
    }
    if(!is_valid_username($username)) {
        flash("Username must only be alphanumeric and can only contain - or _", "warning");
        $hasError = true;
    }
    if(empty($password)){
        flash("Password must not be empty", "warning");
        $hasError = true;
    }
    if(empty($confirm)){
        flash("Confirm password must not be empty", "warning");
        $hasError = true;
    }
    if(!is_valid_password($password)){
        flash("Password is too short", "warning");
        $hasError = true;
    }
    if($password){
        if(strlen($password) > 0 && $password !== $confirm){
            flash("Passwords must match", "warning");
            $hasError = true;
        }
    }
    if(!$hasError){
        //TODO 4: Send information to database
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Users(email, username, password) VALUES (:email, :username, :password)");
        try{
            $r = $stmt->execute([":email" => $email, ":username" => $username, ":password" => $hash]);
            flash("Successfully registered!", "success");
        }
        catch(PDOException $e){
            //flash("There was an error registering", "danger");
            //flash("<pre>" . var_export($e, true) . "</pre>");
            users_check_duplicate($e->errorInfo);
        }
    }
 }
    require(__DIR__ . "/../../partials/flash.php");
?>