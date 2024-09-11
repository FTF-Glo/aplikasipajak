<div style="height: calc(90vh - var(--navbar-height));" class="d-flex justify-content-center align-items-center">
    <div class="m-20 ww-full w-350">
        <h3>Halaman Masuk</h3>
        <form action="<?= base_url('?action=login') ?>" method="POST">
            <div class="form-group">
                <label for="username" class="required">Username</label>
                <input type="text" class="form-control" name="username" id="username">
            </div>
            <div class="form-group">
                <label for="password" class="required">Password</label>
                <input type="password" class="form-control" name="password" id="password">
            </div>
            <button class="btn btn-primary">Login</button>
        </form>
    </div>
</div>