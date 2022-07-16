<div class="header">

    <div class="go_back_icon">
        <span class="go_to_previous_page">
            <i class="icon"></i>
        </span>
    </div>

    <div class="message_selection d-none">
        <label class="selector select_all">
            <input type="checkbox" name="select_all_messages" value="1">
            <span class="checkmark"></span>
        </label>
    </div>
    <div class="image get_info" auto_find=true>
        <span class="thumbnail">
            <img class="image" />
        </span>
    </div>
    <div class="heading get_info" auto_find=true>
        <span class="title"></span>
        <span class="subtitle"></span>
        <span class="view_info"><?php echo(Registry::load('strings')->click_to_view_info); ?></span>
        <div class="whos_typing" last_logged_user_id=0>
            <ul></ul>
        </div>
    </div>
    <div class="icons">
        <?php
        if (Registry::load('current_user')->logged_in) {
            ?>
            <span class="d-md-none toggle_side_navigation">
                <i class="iconic_menu"></i>
                <span class="total_unread_notifications"></span>
            </span>
            <?php
        }
        ?>


        <span class="toggle_checkbox"><i class="iconic_selection"></i></span>
        <span class="ask_confirmation delete_multiple_messages d-none" column="second" data-chat_messages=true multi_select="message_id" submit_button="<?php echo(Registry::load('strings')->yes); ?>" cancel_button="<?php echo(Registry::load('strings')->no); ?>" confirmation="<?php echo(Registry::load('strings')->confirm_delete); ?>"><i class="bi bi-x-lg"></i></span>
        <span class="reload_conversation d-none"><i class="bi bi-arrow-clockwise"></i></span>
        <span class="toggle_search_messages"><i class="bi bi-search"></i></span>
    </div>

    <?php
    if (role(['permissions' => ['groups' => 'send_as_another_user']])) {
        ?>
        <div class="switch_user d-none">
            <span class="close_popup toggle_list"><i class="bi bi-x-lg"></i></span>
            <span class="image toggle_list" title="<?php echo Registry::load('strings')->switch_user ?>" data-bs-toggle="tooltip" data-bs-placement="left"></span>
            <span class="user_id d-none"><input type="text" /></span>
            <span class="username d-none"></span>
            <div>
                <div class="search">
                    <div>
                        <i class="bi bi-search"></i>
                        <input type="search" placeholder="<?php echo(Registry::load('strings')->search_here) ?>">
                    </div>
                </div>
                <div class="list">
                    <ul></ul>
                </div>
            </div>
        </div>

        <?php
    }
    ?>

</div>

<div class="search_messages">
    <div>
        <div class="search">
            <div>
                <i class="bi bi-search"></i>
                <input type="search" name="search_messages" placeholder="<?php echo(Registry::load('strings')->search_here) ?>">
            </div>
        </div>
    </div>
</div>

<div class="alert_message">
    <div>
        <div class="message">
            <span></span>
        </div>
    </div>
</div>

<div class="contents" read_more_criteria="<?php echo(Registry::load('settings')->read_more_criteria) ?>">
    <span class="date timestamp">
        <span></span>
    </span>
    <div class="chat_messages">
        <ul></ul>
    </div>

    <div class="loader conversation_loader">
        <ul></ul>
    </div>
    <div class="error_message">
        <div>
            <div>
                <div class="image"></div>
                <div class="text">
                    <span class="title"></span>
                    <span class="subtitle"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="info_box">
    <div>
        <div class="content"></div>
    </div>
</div>

<div class="footer">

    <?php
    if (Registry::load('current_user')->logged_in) {
        ?>

        <div class="grid_list module hidden">
            <div class="gif_module d-none" load="gifs">
                <span class="data_attributes d-none"></span>

                <div class="search">
                    <div>
                        <input type="search" placeholder="<?php echo(Registry::load('strings')->search_here) ?>" />
                    </div>
                </div>

                <div class="subtabs">
                    <ul></ul>
                </div>


                <div class="results">
                    <div>
                        <ul id="grid_list"></ul>
                    </div>
                </div>
            </div>

            <div class="stickers_module d-none" load="stickers">
                <span class="data_attributes d-none"></span>
                <div class="subtabs">
                    <ul></ul>
                </div>

                <div class="results">
                    <div>
                        <ul id="grid_list"></ul>
                    </div>
                </div>
            </div>

            <div class="emojis_module d-none" load="emojis">
                <span class="data_attributes d-none"></span>

                <div class="search">
                    <div>
                        <input type="search" placeholder="<?php echo(Registry::load('strings')->search_here) ?>" />
                    </div>
                </div>

                <div class="subtabs">
                    <ul></ul>
                </div>


                <div class="results">
                    <div>
                        <ul id="grid_list"></ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="attached_message">
            <div class="attached_message_preview">
                <div class="content">
                    <div class="left">
                        <span class="send_by"></span>
                        <span class="text"></span>
                    </div>
                    <div class="right">
                        <span class="thumbnail"></span>
                    </div>
                </div>
                <div class="detach_message">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
            </div>
            <span class="attached_message_id"><input type="hidden" name="attached_msg_id" value="0" /></span>
        </div>


        <div class="editor" min_message_length="<?php echo(Registry::load('settings')->minimum_message_length) ?>" max_message_length="<?php echo(Registry::load('settings')->maximum_message_length) ?>">
            <div>
                <div class="attached_gif d-none">
                    <span class="gif_image">
                        <img src='' />
                        <span class="deattach_gif">
                            <i class="bi bi-x-circle-fill"></i>
                        </span>
                    </span>
                    <span class="gif_url">
                        <input type="text" name="gif_url" value='' />
                    </span>
                </div>
                <div class="message_editor">
                    <div id="message_editor"></div>
                </div>
                <div class="send_message_button">
                    <div>
                        <span class="send_message">
                            <i class="icon"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="attachments module hidden">
            <div>
                <div class="files">
                    <ul></ul>
                </div>
                <div class="attached_files">
                    <form class="attach_files_form" enctype="multipart/form-data">
                    </form>
                </div>
            </div>
        </div>

        <?php
    }
    ?>

    <audio id="audio_message_preview" class="d-none" controls preload="none">
        <source src="" type="" />
    </audio>
</div>

<span class="background_image"></span>