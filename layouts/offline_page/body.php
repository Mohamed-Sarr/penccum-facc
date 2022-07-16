<body class="d-flex h-100 text-center">

    <div class="offline error_page_container d-flex w-100 h-100 p-3 mx-auto flex-column">

        <main class="px-3 mt-auto mb-auto">
            <div class="expression">
                <span><?php echo Registry::load('strings')->offline_page_expression; ?></span>
            </div>
            <h1 class="title"><?php echo Registry::load('strings')->offline_page_title; ?></h1>
            <div class="description">
                <p>
                    <?php echo Registry::load('strings')->offline_page_description; ?>
                </p>
            </div>
            <div class="button">
                <a href="<?php echo Registry::load('config')->site_url ?>"><?php echo Registry::load('strings')->offline_page_button; ?></a>
            </div>
        </main>
    </div>
</body>