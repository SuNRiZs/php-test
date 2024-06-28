<?php
include "includes/_process_include.php";
include "includes/_header.php";

?>

<form class="form-signin">
    <h2 class="form-signin-heading">Форма входа</h2>
    <label for="inputEmail" class="sr-only">Логин</label>
    <input type="input" id="username" class="form-control" placeholder="Username" required autofocus>
    <label for="inputPassword" class="sr-only">Пароль</label>
    <input type="password" id="password" class="form-control" placeholder="Password" required>
    <div class="checkbox">
        
    </div>
    <input type="hidden" id="nextURL" class="form-control" value=<?=Gallery_Page_Link ?>>
    <button class="btn btn-lg btn-primary btn-block" id="loginbtn" type="button">Авторизация</button>

    <div id="loader text-center"></div>
</form>

<?php
include "includes/_footer.php";
?>