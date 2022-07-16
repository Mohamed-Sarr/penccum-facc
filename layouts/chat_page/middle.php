<div class="col-md-7 col-lg-9 middle page_column" column="second">

    <div class="video_preview d-none">
        <span class="icons">
            <span class="close_player">
                <i class="bi bi-x-square-fill"></i>
            </span>
        </span>
        <div>
        </div>
    </div>

    <div class="iframe_window d-none">
        <span class="icons">
            <span class="close_iframe_window">
                <i class="bi bi-x-square-fill"></i>
            </span>
        </span>
        <div>
        </div>
    </div>

    <div class="confirm_box d-none animate__animated animate__flipInX">
        <div class="error">
            <span class="message"><?php echo(Registry::load('strings')->error) ?> : <span></span></span>
        </div>
        <div class="content">
            <span class="text"></span>
            <span class="btn cancel" column="second"><span></span></span>
            <span class="btn submit"><span></span></span>
        </div>
    </div>

    <div class="content">

        <div class="welcome_screen">
            <?php include 'layouts/chat_page/welcome_screen.php'; ?>
        </div>

        <div class="statistics d-none">
            <?php include 'layouts/chat_page/statistics.php'; ?>
        </div>

        <div class="custom_page d-none">
            <?php include 'layouts/chat_page/custom_page.php'; ?>
        </div>

        <div class="chatbox d-none boundary">
            <?php include 'layouts/chat_page/chatbox.php'; ?>
        </div>
    </div>


</div>