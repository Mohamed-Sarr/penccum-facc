<div class="installation_form d-none">
    <form>

        <div class="error_message d-none">
            <div class="alert alert-danger" role="alert">
                <b>ERROR : </b>
                <span></span>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="mb-2">Purchase Code</label>
            <input type="text" name="purchase_code" class="form-control" placeholder="Envato Purchase Code" />
        </div>

        <div class="form-group mb-3">
            <label class="mb-2">How did you discover Grupo?</label>
            <select class="form-control" name="discover_grupo">
                <option value="">-----------</option>
                <option value="social_media">Social Media</option>
                <option value="search_engine">Search Engine</option>
                <option value="recommendation">Recommended by friend or colleague</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <h5>Database Details</h5>
        </div>

        <div class="form-group mb-3 d-none">
            <label class="mb-2">DB Type</label>
            <select class="form-control" name="database_type">
                <option value="mysql" selected>MySQL</option>
                <option value="mariadb">MariaDB</option>
            </select>
        </div>

        <div class="row mb-3">
            <div class="form-group col-md-6">
                <label class="mb-2">Hostname</label>
                <input type="text" name="database_hostname" class="form-control" placeholder="Database Hostname" value="localhost" />
            </div>
            <div class="form-group col-md-6">
                <label class="mb-2">Database</label>
                <input type="text" name="database_name" class="form-control" placeholder="Database Name" />
            </div>
        </div>

        <div class="row mb-3">
            <div class="form-group col-md-6">
                <label class="mb-2">Username</label>
                <input type="text" name="database_username" class="form-control" placeholder="Database Username" />
            </div>
            <div class="form-group col-md-6">
                <label class="mb-2">Password</label>
                <input type="text" name="database_password" class="form-control" placeholder="Database Password" />
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="mb-2">Port</label>
            <input type="number" name="database_port" class="form-control" placeholder="Database Port" value="3306" />
        </div>

        <div class="form-group">
            <h5>Account Details</h5>
            <p>
                Type in your email address, preferred username and password for your admin account
            </p>
        </div>

        <div class="form-group mb-3">
            <label class="mb-2">Email Address</label>
            <input type="email" name="email_address" class="form-control" placeholder="Email Address" />
        </div>

        <div class="row mb-3">
            <div class="form-group col-md-6">
                <label class="mb-2">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Username" />
            </div>
            <div class="form-group col-md-6">
                <label class="mb-2">Password</label>
                <input type="text" name="password" class="form-control" placeholder="Password" />
            </div>
        </div>

        <div class="d-none">
            <input type="hidden" name="install" value="install" />
        </div>

        <div class="install">
            <span>Install</span>
        </div>
    </form>
</div>