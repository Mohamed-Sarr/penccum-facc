<body class="landing_page<?php echo ' '.Registry::load('appearance')->body_class ?>">

    <?php include 'assets/headers_footers/landing_page/body.php'; ?>
    <main>

        <?php
        include('layouts/landing_page/navigation.php');
        ?>

        <?php
        if (isset(Registry::load('config')->load_page) && !empty(Registry::load('config')->load_page)) {
            include('layouts/landing_page/custom_page.php');
        } else {
            include('layouts/landing_page/hero.php');

            if (isset(Registry::load('settings')->groups_section_status) && Registry::load('settings')->groups_section_status === 'enable') {
                include('layouts/landing_page/groups.php');
            }
            if (isset(Registry::load('settings')->faq_section_status) && Registry::load('settings')->faq_section_status === 'enable') {
                include('layouts/landing_page/faq.php');
            }
        }
        ?>

        <?php
        include('layouts/landing_page/bottom.php');
        ?>



    </main>
</body>