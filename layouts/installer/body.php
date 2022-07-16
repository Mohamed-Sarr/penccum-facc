<body class="installation_page">

    <div class="d-lg-flex half wrapper">
        <div class="bg order-1 order-md-1 background_image">
            <div>
                <img src="<?php echo Registry::load('config')->site_url.'assets/files/backgrounds/installation_page_bg.jpg' ?>" />
            </div>
        </div>
        <div class="installer_box contents order-2 order-md-2">
            <div class="container">
                <div class="row align-items-center justify-content-center">
                    <div class="col-md-9 box_contents">
                        <h3>Installer</h3>
                        <p class="mb-4">
                            Thanks for purchasing our script.
                            Please feel free to reach us via hello@baevox.com if you have any questions, comments, or concerns.
                        </p>

                        <?php include('layouts/installer/system_requirements.php'); ?>
                        <?php include('layouts/installer/form.php'); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>