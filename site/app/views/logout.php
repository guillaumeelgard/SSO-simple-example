<div class="container well shadow-lg p-5" id="logout-form">
    <h3 class="mb-3">Hello <?=htmlspecialchars($_SESSION['user']->login)?>!</h3>
    <button type="submit" class="btn btn-primary">Sign out</button>
</div>