<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $conversation; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?php echo Registry::load('config')->site_url ?>assets/thirdparty/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo Registry::load('config')->site_url ?>assets/css/messages_table/util.css">
    <link rel="stylesheet" href="<?php echo Registry::load('config')->site_url ?>assets/css/messages_table/style.css">
    <link rel="stylesheet" href="<?php echo Registry::load('config')->site_url ?>assets/css/chat_page/emojis.css">
</head>
<body>

    <div class="limiter">
        <div class="conversation_title">
            <h1><?php echo $conversation; ?></h1>
        </div>

        <div class="container-table100">
            <div class="wrap-table100">
                <div class="table100">
                    <table>
                        <thead>
                            <tr class="table100-head">
                                <th class="column1"><?php echo Registry::load('strings')->id ?></th>
                                <th class="column2"><?php echo Registry::load('strings')->posted_by ?></th>
                                <th class="column3"><?php echo Registry::load('strings')->content ?></th>
                                <th class="column6"><?php echo Registry::load('strings')->timestamp ?></th>
                            </tr>
                        </thead>
                        <tbody>



                            <?php
                            foreach ($messages as $message) {
                                $date = array();
                                $date['date'] = $message['created_on'];
                                $message_attachment = '';
                                $date['auto_format'] = true;
                                $date['include_time'] = true;
                                $date['timezone'] = Registry::load('current_user')->time_zone;
                                $created_on = get_date($date);

                                if ($message['attachment_type'] === 'gif') {
                                    $attachment = json_decode($message['attachments']);
                                    $message_attachment = '<div class="attachment">['.$attachment->gif_url.']</div>';
                                } else if ($message['attachment_type'] === 'sticker') {
                                    $attachment = json_decode($message['attachments']);
                                    $message_attachment = '<div class="attachment">['.basename($attachment->sticker).']</div>';
                                } else if ($message['attachment_type'] === 'audio_message') {
                                    $message_attachment = Registry::load('strings')->audio_message;
                                } else if (!empty($message['attachment_type']) && $message['attachment_type'] !== 'url_meta') {
                                    $attachments = json_decode($message['attachments']);
                                    $message_attachment = '<div class="attachment">';

                                    foreach ($attachments as $attachment) {

                                        if (!isset($attachment->name)) {
                                            $attachment = new stdClass();
                                            $attachment->name = '[Attachment]';
                                        }

                                        $message_attachment .= '['.$attachment->name.'] ';
                                    }
                                    $message_attachment .= '</div>';
                                }

                                ?>

                                <tr id="message_<?php echo $message['message_id']; ?>">



                                    <td class="column1" data-th="<?php echo Registry::load('strings')->message_id ?>"><?php output($message['message_id']); ?></td>
                                    <td class="column2" data-th="<?php echo Registry::load('strings')->posted_by ?>"><?php output($message['display_name']); ?></td>

                                    <?php if (!empty($message['filtered_message'])) {
                                        ?>
                                        <td class="column3" data-th="<?php echo Registry::load('strings')->content ?>">
                                            <div>
                                                <?php echo $message['filtered_message']; ?>
                                            </div>
                                            <?php echo $message_attachment; ?>
                                        </td>
                                        <?php
                                    } else if ($message['attachment_type'] === 'gif') {
                                        ?>
                                        <td class="column3" data-th="<?php echo Registry::load('strings')->content ?>">
                                            <?php echo Registry::load('strings')->gif ?>
                                            <?php echo $message_attachment; ?>
                                        </td>
                                        <?php
                                    } else if ($message['attachment_type'] === 'sticker') {
                                        ?>
                                        <td class="column3" data-th="<?php echo Registry::load('strings')->content ?>">
                                            <?php echo Registry::load('strings')->sticker ?>
                                            <?php echo $message_attachment; ?>
                                        </td>
                                        <?php
                                    } else if ($message['attachment_type'] === 'audio_message') {
                                        ?>
                                        <td class="column3" data-th="<?php echo Registry::load('strings')->content ?>">
                                            <?php echo Registry::load('strings')->audio_message ?>
                                        </td>
                                        <?php
                                    } else {
                                        ?>
                                        <td class="column3" data-th="<?php echo Registry::load('strings')->content ?>">
                                            <?php echo $message_attachment; ?>
                                        </td>
                                        <?php
                                    } ?>



                                    <td class="column6" data-th="<?php echo Registry::load('strings')->timestamp ?>"><?php output($created_on['date'].' - '.$created_on['time']); ?></td>

                                </tr>
                                <?php
                            } ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo Registry::load('config')->site_url ?>assets/thirdparty/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>