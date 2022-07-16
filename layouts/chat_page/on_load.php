<?php

if (!Registry::load('current_user')->logged_in && Registry::load('settings')->view_groups_without_login != 'enable') {

    $entry_page = 'entry/';

    if (!empty(Registry::load('config')->url_path)) {
        $entry_page .= '?redirect='.urlencode(Registry::load('config')->url_path);
    }

    redirect($entry_page);
}

if (Registry::load('settings')->chat_page_boxed_layout === 'enable') {
    Registry::load('appearance')->body_class = Registry::load('appearance')->body_class.' boxed_layout';
}

?>