<?php
if (isset(Registry::load('settings')->google_analytics_id) && !empty(Registry::load('settings')->google_analytics_id)) {
    $google_analytics_id = Registry::load('settings')->google_analytics_id;
    ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $google_analytics_id ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', '<?php echo $google_analytics_id ?>');
    </script>
    <?php
} ?>