<div class="form-signin-wrapper">
    <form class="form-signin text-center">
        <input type="hidden" id="csrf_token" ng-model="csrf_token"/>
        <img class="mb-4" src="./assets/images/logo.png" alt="" width="72" height="72">
        <h1 class="h3 mb-3 font-weight-normal"><?php echo $page_title; ?></h1>
        <div class="input-group">
            <label for="inputUsername" class="sr-only"><?php echo $username_label; ?></label>
            <div class="input-group-append">
                <div class="input-group-text border-right-0">
                    <span class="fas fa-user"></span>
                </div>
            </div>
            <input type="text" id="inputUsername" class="form-control border-left-0" placeholder="<?php echo $username_label; ?>" ng-model="username" required autofocus>
        </div>

        <div class="input-group">
            <label for="inputPassword" class="sr-only"><?php echo $password_label; ?></label>
            <div class="input-group-append">
                <div class="input-group-text border-right-0">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
            <input type="password" id="inputPassword" class="form-control border-left-0" placeholder="<?php echo $password_label; ?>" ng-model="password" required>
        </div>

        <div class="input-group mb-3 text-left">
            <input class="checkbox" id="rememberme" type="checkbox" value="rememberme" ng-model="rememberme">
            <label for="rememberme"><?php echo $rememberme_label; ?></label>
        </div>
        <button class="btn btn-lg btn-primary btn-block text-white" type="button" ng-click="login(username, password, csrf_token, rememberme)"><?php echo $login_button_label; ?></button>
        <p class="mt-5 mb-3 text-muted">&copy; 2017-2018</p>
    </form>
</div>