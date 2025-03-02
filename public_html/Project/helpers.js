function flash(message = "", color = "info") {
    let flash = document.getElementById("flash");
    //create a div (or whatever wrapper we want)
    let outerDiv = document.createElement("div");
    outerDiv.className = "row justify-content-center";
    let innerDiv = document.createElement("div");

    //apply the CSS (these are bootstrap classes which we'll learn later)
    innerDiv.className = `alert alert-${color}`;
    //set the content
    innerDiv.innerText = message;

    outerDiv.appendChild(innerDiv);
    //add the element to the DOM (if we don't it merely exists in memory)
    flash.appendChild(outerDiv);
}

function sanitize_email(email = "")
{
    //Remove all characters except specified, according to PHP documentation.
    return email.replace(/[^a-zA-Z0-9!#$%&'*+-=?^_`{|}~@.[]]/g,'');
}

function is_valid_email(email = "")
{
    //One or more chars, @, One or more chars, ., One or more chars
    let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(emailRegex.test(email)){
        return true;
    }
    return false;
}

function is_valid_username(username)
{
    let usernameRegex = /^[a-z0-9_-]{3,16}$/
    if(usernameRegex.test(username)){
        return true;
    }
    return false;
}

function is_valid_password(password)
{
    return password.length >= 8;
}