<script src="<?php echo Registry::load('config')->site_url.'assets/js/combined_js_landing_page.js'.$cache_timestamp; ?>"></script>
<?php
if (Registry::load('settings')->progressive_web_application === 'enable') {
    ?>
    <script type="module">
        import 'https://cdn.jsdelivr.net/npm/@pwabuilder/pwainstall';
        const el = document.createElement('pwa-update');
        document.body.appendChild(el);
    </script>
    <script>
        $(window).on('load', function() {
            if ("serviceWorker" in navigator) {
                navigator.serviceWorker.register(baseurl+"pwa-sw.js");
            }
        });
    </script>

    <?php

}
?>

<?php
include 'assets/headers_footers/landing_page/footer.php';
include 'layouts/landing_page/google_analytics.php';
?>
</html>