var realtime_request = null;
var realtime_timeout = null;
var realtime_refresh_rate = $.trim(system_variable('refresh_rate'));
var site_notification_tone = $('.site_sound_notification > div > audio')[0];
var user_typing_log_request = null;
var user_typing_log_timeout = null;
var users_typing_timeout = null;


if (realtime_refresh_rate.length === 0) {
    realtime_refresh_rate = 2000;
}


$(window).on('load', function() {
    realtime();
});


function realtime() {

    if (realtime_timeout !== null) {
        clearTimeout(realtime_timeout);
    }

    var side_navigation = $('.main .side_navigation .menu_items');
    var request_time = new Date($.now());
    var whos_typing_last_logged_user_id = 0;
    var logged_in_user_id = 0;

    realtime_timeout = setTimeout(function() {

        var ajax_data = {
            request_time: request_time,
            realtime: true,
        };

        if ($('.logged_in_user_id').length > 0) {
            logged_in_user_id = $('.logged_in_user_id').text();
        }

        ajax_data['logged_in_user_id'] = logged_in_user_id;

        if (!$('.main .chatbox').hasClass('d-none') && !$('.main .chatbox > .contents > .chat_messages').hasClass('searching')) {

            if ($('.main .chatbox').attr('group_id') !== undefined) {
                ajax_data['group_id'] = $('.main .chatbox').attr('group_id');
            } else if ($('.main .chatbox').attr('user_id') !== undefined) {
                ajax_data['user_id'] = $('.main .chatbox').attr('user_id');
            }
            if ($('.main .chatbox').attr('group_id') !== undefined || $('.main .chatbox').attr('user_id') !== undefined) {
                ajax_data['message_id_greater_than'] = get_message_id('last');
                ajax_data['last_seen_by_recipient'] = get_message_id('last_seen_by_recipient');
            }

            if ($('.main .chatbox > .header > .heading > .whos_typing').attr('last_logged_user_id') !== undefined) {
                whos_typing_last_logged_user_id = $('.main .chatbox > .header > .heading > .whos_typing').attr('last_logged_user_id');
            }

            ajax_data['whos_typing_last_logged_user_id'] = whos_typing_last_logged_user_id;
        }

        if (side_navigation.find('li.realtime_module[module="groups"]').length > 0) {
            $unread_group_messages = 0;

            if (side_navigation.find('li.realtime_module[module="groups"]').attr('unread') != undefined) {
                $unread_group_messages = side_navigation.find('li.realtime_module[module="groups"]').attr('unread');
            }

            ajax_data['unread_group_messages'] = $unread_group_messages;
        }

        if (side_navigation.find('li.realtime_module[module="private_conversations"]').length > 0) {
            $unread_private_chat_messages = 0;

            if (side_navigation.find('li.realtime_module[module="private_conversations"]').attr('unread') != undefined) {
                $unread_private_chat_messages = side_navigation.find('li.realtime_module[module="private_conversations"]').attr('unread');
            }

            ajax_data['unread_private_chat_messages'] = $unread_private_chat_messages;
        }

        if (side_navigation.find('li.realtime_module[module="site_notifications"]').length > 0) {
            $unread_site_notifications = 0;

            if (side_navigation.find('li.realtime_module[module="site_notifications"]').attr('unread') != undefined) {
                $unread_site_notifications = side_navigation.find('li.realtime_module[module="site_notifications"]').attr('unread');
            }

            ajax_data['unread_site_notifications'] = $unread_site_notifications;
        }

        if ($('.main .aside > .site_records > .current_record').attr('load') === 'online') {
            $recent_online_user_id = 0;
            $recent_online_user_online_status = 0;
            $total_online_users = 0;

            if (side_navigation.find('li.realtime_module[module="online_users"]').attr('recent_online_user_id') != undefined) {
                $recent_online_user_id = side_navigation.find('li.realtime_module[module="online_users"]').attr('recent_online_user_id');
            }

            if (side_navigation.find('li.realtime_module[module="online_users"]').attr('recent_online_user_online_status') != undefined) {
                $recent_online_user_online_status = side_navigation.find('li.realtime_module[module="online_users"]').attr('recent_online_user_online_status');
            }

            if (side_navigation.find('li.realtime_module[module="online_users"]').attr('total_online_users') != undefined) {
                $total_online_users = side_navigation.find('li.realtime_module[module="online_users"]').attr('total_online_users');
            }

            ajax_data['recent_online_user_id'] = $recent_online_user_id;
            ajax_data['recent_online_user_online_status'] = $recent_online_user_online_status;
            ajax_data['total_online_users'] = $total_online_users;
        }


        if (side_navigation.find('li.realtime_module[module="complaints"]').length > 0) {
            $unresolved_complaints = 0;

            if (side_navigation.find('li.realtime_module[module="complaints"]').attr('unresolved') != undefined) {
                $unresolved_complaints = side_navigation.find('li.realtime_module[module="complaints"]').attr('unresolved');
            }

            ajax_data['unresolved_complaints'] = $unresolved_complaints;
        }


        $last_realtime_log_id = 0;

        if ($('.main_window').attr('last_realtime_log_id') != undefined) {
            $last_realtime_log_id = $('.main_window').attr('last_realtime_log_id');
        }

        ajax_data['last_realtime_log_id'] = $last_realtime_log_id;

        realtime_request = $.ajax({
            type: 'POST',
            url: api_request_url,
            data: ajax_data,
            async: true,
            beforeSend: function() {
                if (realtime_request !== null) {
                    realtime_request.abort();
                    realtime_request = null;
                }
            },
            success: function(data) {}
        }).done(function(data) {
            if (isJSON(data)) {
                data = $.parseJSON(data);
                var aside_refresh = true;

                if (data.reload_page !== undefined) {
                    location.reload(true);
                }

                if (data.play_sound_notification !== undefined && data.play_sound_notification) {
                    site_notification_tone.currentTime = 0;
                    site_notification_tone.play();
                }

                if ($('.main .aside > .site_records .current_record_search_keyword').val().length > 0) {
                    aside_refresh = false;
                }

                if (!$('.main .aside > .site_records > .tools > .tool.multiple_selection').hasClass('d-none')) {
                    aside_refresh = false;
                }

                if ($('.main .aside > .site_records .current_record_filter').val().length > 1) {
                    aside_refresh = false;
                }

                if ($('.main .aside > .site_records .current_record_sort_by').val().length > 1) {
                    aside_refresh = false;
                }

                if ($('.main .aside > .site_records > .current_record').hasClass('loading')) {
                    aside_refresh = false;
                }

                var aside_scroll_position = $('.main .aside > .site_records > .records > .list').scrollTop();

                if (aside_scroll_position > 150) {
                    aside_refresh = false;
                }

                if (!$('.main .chatbox').hasClass('d-none') && !$('.main .chatbox > .contents > .chat_messages').hasClass('searching')) {
                    if (data.group_messages !== undefined) {
                        data.group_messages.append = true;

                        var scroll_position = $('.main .chatbox > .contents > .chat_messages').scrollTop();
                        scroll_position = Math.abs(scroll_position);
                        var screen_height = ($(window).height())-50;

                        //console.log('scroll_position : ' + scroll_position + 'screen_height : ' + screen_height);

                        if (scroll_position < screen_height) {
                            data.group_messages.scrollToBottom = true;
                        }

                        if (data.group_messages.messages !== undefined) {
                            if (data.group_messages.messages[0] !== undefined) {
                                if (data.group_messages.messages[0].own_message === undefined || !data.group_messages.messages[0].own_message) {
                                    var browser_title = language_string('new_message_notification');
                                    change_browser_title(browser_title, 5000);
                                }
                            }
                        }

                        load_messages(data.group_messages);
                    }

                    if (data.last_seen_by_recipient !== undefined) {
                        if (data.last_seen_by_recipient.user_id !== undefined && $('.main .chatbox').attr('user_id') !== undefined) {
                            if ($('.main .chatbox').attr('user_id') == data.last_seen_by_recipient.user_id) {
                                if (data.last_seen_by_recipient.message_id !== undefined) {

                                    var last_seen_by_recipient_id = parseInt(data.last_seen_by_recipient.message_id);

                                    $('.main .chatbox > .contents > .chat_messages > ul > li').each(function() {
                                        if ($(this).attr('message_id') != undefined && !$(this).hasClass('seen_by_recipient')) {

                                            var this_message_id = parseInt($(this).attr('message_id'));

                                            if (this_message_id <= last_seen_by_recipient_id) {
                                                $(this).addClass('seen_by_recipient');
                                                $(this).find('.read_status').addClass('read');
                                                $(this).find('.read_status').html('<i class="bi bi-eye"></i>');
                                            }
                                        }
                                    });
                                }
                            }
                        }
                    }

                    if (data.private_chat_messages !== undefined) {
                        data.private_chat_messages.append = true;

                        var scroll_position = $('.main .chatbox > .contents > .chat_messages').scrollTop();
                        scroll_position = Math.abs(scroll_position);

                        if (scroll_position < 300) {
                            data.private_chat_messages.scrollToBottom = true;
                        }

                        if (data.private_chat_messages.messages !== undefined) {
                            if (data.private_chat_messages.messages[0] !== undefined) {
                                if (data.private_chat_messages.messages[0].own_message === undefined || !data.private_chat_messages.messages[0].own_message) {
                                    var browser_title = language_string('new_message_notification');
                                    change_browser_title(browser_title, 5000);
                                }
                            }
                        }

                        load_messages(data.private_chat_messages);
                    }

                    if (data.users_typing !== undefined) {

                        if (data.users_typing.last_inserted_user_id !== undefined) {
                            $('.main .chatbox > .header > .heading > .whos_typing').attr('last_logged_user_id', data.users_typing.last_inserted_user_id);
                        }

                        if (data.users_typing.users !== undefined) {
                            if (data.users_typing.group_id !== undefined && data.users_typing.group_id == $(".main .chatbox").attr('group_id')) {
                                whos_typing(data.users_typing.users);
                            } else if (data.users_typing.user_id !== undefined && data.users_typing.user_id == $(".main .chatbox").attr('user_id')) {
                                whos_typing(data.users_typing.users);
                            } else {
                                whos_typing(null);
                            }
                        }

                    }
                }

                if (data.unread_group_messages !== undefined) {

                    if (data.unread_group_messages.length == 0) {
                        data.unread_group_messages = 0;
                    }

                    var unread_text = '';

                    side_navigation.find('li.realtime_module[module="groups"]').attr('unread', data.unread_group_messages);

                    if (data.unread_group_messages !== 0) {
                        unread_text = '<span>'+abbreviateNumber(data.unread_group_messages)+'</span>';

                        if ($('.main .aside > .site_records > .current_record').attr('load') === 'groups') {

                            if (aside_refresh) {
                                $(".main .aside > .site_records > .current_record").removeClass('loading');
                                $('.main .aside > .site_records > .current_record').attr('disable_preloader', true);
                                load_aside($(".main .aside > .site_records > .current_record"));
                                $('.main .aside > .site_records > .current_record').removeAttr('disable_preloader');
                            }

                        }
                    } else {
                        if ($('.main .aside > .site_records > .current_record').attr('load') === 'groups') {
                            $(".main .aside > .site_records > .records > .list > li > div > .center > .title > .unread").addClass('d-none');
                        }
                    }

                    side_navigation.find('li.realtime_module[module="groups"] > .menu_item > .unread').html(unread_text);
                }

                if (data.unread_private_chat_messages !== undefined) {

                    if (data.unread_private_chat_messages.length == 0) {
                        data.unread_private_chat_messages = 0;
                    }


                    var unread_text = '';
                    side_navigation.find('li.realtime_module[module="private_conversations"]').attr('unread', data.unread_private_chat_messages);

                    if (data.unread_private_chat_messages !== 0) {
                        unread_text = '<span>'+abbreviateNumber(data.unread_private_chat_messages)+'</span>';

                        if ($('.main .aside > .site_records > .current_record').attr('load') === 'private_conversations') {

                            if (aside_refresh) {
                                $(".main .aside > .site_records > .current_record").removeClass('loading');
                                $('.main .aside > .site_records > .current_record').attr('disable_preloader', true);
                                load_aside($(".main .aside > .site_records > .current_record"));
                                $('.main .aside > .site_records > .current_record').removeAttr('disable_preloader');
                            }

                        }
                    } else {
                        if ($('.main .aside > .site_records > .current_record').attr('load') === 'private_conversations') {
                            $(".main .aside > .site_records > .records > .list > li > div > .center > .title > .unread").addClass('d-none');
                        }
                    }

                    $('.main .pm_shortcut > .notification_count').html(unread_text);
                    side_navigation.find('li.realtime_module[module="private_conversations"] > .menu_item > .unread').html(unread_text);
                }

                if (data.unread_site_notifications !== undefined) {

                    if (data.unread_site_notifications.length == 0) {
                        data.unread_site_notifications = 0;
                    }

                    var unread_text = '';
                    side_navigation.find('li.realtime_module[module="site_notifications"]').attr('unread', data.unread_site_notifications);

                    if (data.unread_site_notifications !== 0) {
                        unread_text = '<span>'+abbreviateNumber(data.unread_site_notifications)+'</span>';

                        if ($('.main .aside > .site_records > .current_record').attr('load') === 'site_notifications') {

                            if (aside_refresh) {
                                $(".main .aside > .site_records > .current_record").removeClass('loading');
                                $('.main .aside > .site_records > .current_record').attr('disable_preloader', true);
                                load_aside($(".main .aside > .site_records > .current_record"));
                                $('.main .aside > .site_records > .current_record').removeAttr('disable_preloader');
                            }

                        }
                    }
                    side_navigation.find('li.realtime_module[module="site_notifications"] > .menu_item > .unread').html(unread_text);
                }


                if (data.unresolved_complaints !== undefined) {

                    if (data.unresolved_complaints.length == 0) {
                        data.unresolved_complaints = 0;
                    }

                    var unread_text = '';
                    side_navigation.find('li.realtime_module[module="complaints"]').attr('unresolved', data.unresolved_complaints);

                    if (data.unresolved_complaints !== 0) {
                        unread_text = '<span>'+abbreviateNumber(data.unresolved_complaints)+'</span>';

                        if ($('.main .aside > .site_records > .current_record').attr('load') === 'complaints') {

                            if (aside_refresh) {
                                $(".main .aside > .site_records > .current_record").removeClass('loading');
                                $('.main .aside > .site_records > .current_record').attr('disable_preloader', true);
                                load_aside($(".main .aside > .site_records > .current_record"));
                                $('.main .aside > .site_records > .current_record').removeAttr('disable_preloader');
                            }

                        }
                    }
                    side_navigation.find('li.realtime_module[module="complaints"] > .menu_item > .unread').html(unread_text);
                }

                if (data.recent_online_user_id !== undefined) {
                    if ($('.main .aside > .site_records > .current_record').attr('load') === 'online') {

                        if (data.recent_online_user_id.length == 0) {
                            data.recent_online_user_id = 0;
                        }

                        if (data.recent_online_user_online_status.length == 0) {
                            data.recent_online_user_online_status = 0;
                        }

                        if (data.total_online_users.length == 0) {
                            data.total_online_users = 0;
                        }

                        var current_recent_online_user_id = 0;
                        var current_online_user_online_status = 0;
                        var total_online_users = 0;

                        if (isFinite(side_navigation.find('li.realtime_module[module="online_users"]').attr('recent_online_user_id'))) {
                            current_recent_online_user_id = side_navigation.find('li.realtime_module[module="online_users"]').attr('recent_online_user_id');
                        }

                        if (side_navigation.find('li.realtime_module[module="online_users"]').attr('recent_online_user_online_status') !== undefined) {
                            current_online_user_online_status = side_navigation.find('li.realtime_module[module="online_users"]').attr('recent_online_user_online_status');
                        }

                        if (side_navigation.find('li.realtime_module[module="online_users"]').attr('total_online_users') !== undefined) {
                            total_online_users = side_navigation.find('li.realtime_module[module="online_users"]').attr('total_online_users');
                        }

                        side_navigation.find('li.realtime_module[module="online_users"]').attr('recent_online_user_id', data.recent_online_user_id);
                        side_navigation.find('li.realtime_module[module="online_users"]').attr('recent_online_user_online_status', data.recent_online_user_online_status);
                        side_navigation.find('li.realtime_module[module="online_users"]').attr('total_online_users', data.total_online_users);

                        if (data.total_online_users !== total_online_users || data.recent_online_user_id !== current_recent_online_user_id || data.recent_online_user_online_status !== current_online_user_online_status) {

                            if (aside_refresh) {
                                $(".main .aside > .site_records > .current_record").removeClass('loading');
                                $('.main .aside > .site_records > .current_record').attr('disable_preloader', true);
                                load_aside($(".main .aside > .site_records > .current_record"));
                                $('.main .aside > .site_records > .current_record').removeAttr('disable_preloader');
                            }
                        }
                    }
                }

                if (data.unread_realtime_logs !== undefined) {

                    if (data.last_realtime_log_id !== undefined) {
                        $('.main_window').attr('last_realtime_log_id', data.last_realtime_log_id);
                    }
                    realtime_logs(data.unread_realtime_logs);
                }

                var total_unread_notifications = 0;
                var current_total_unread_notifications = 0;

                if ($('.total_unread_notifications').attr('total_unread_notification') !== undefined) {
                    current_total_unread_notifications = parseInt($('.total_unread_notifications').attr('total_unread_notification'));
                }

                if (isFinite(side_navigation.find('li.realtime_module[module="groups"]').attr('unread'))) {
                    total_unread_notifications = parseInt(total_unread_notifications)+parseInt(side_navigation.find('li.realtime_module[module="groups"]').attr('unread'));
                }

                if (isFinite(side_navigation.find('li.realtime_module[module="private_conversations"]').attr('unread'))) {
                    total_unread_notifications = parseInt(total_unread_notifications)+parseInt(side_navigation.find('li.realtime_module[module="private_conversations"]').attr('unread'));
                }
                if (isFinite(side_navigation.find('li.realtime_module[module="site_notifications"]').attr('unread'))) {
                    total_unread_notifications = parseInt(total_unread_notifications)+parseInt(side_navigation.find('li.realtime_module[module="site_notifications"]').attr('unread'));
                }
                if (isFinite(side_navigation.find('li.realtime_module[module="complaints"]').attr('unresolved'))) {
                    total_unread_notifications = parseInt(total_unread_notifications)+parseInt(side_navigation.find('li.realtime_module[module="complaints"]').attr('unresolved'));
                }

                if (current_total_unread_notifications != total_unread_notifications) {
                    if (total_unread_notifications != 0) {
                        $('.total_unread_notifications').html('<span>'+abbreviateNumber(total_unread_notifications)+'</span>');
                    } else {
                        $('.total_unread_notifications').html('');
                    }

                    $('.total_unread_notifications').attr('total_unread_notification', total_unread_notifications);
                }


            } else {
                console.log('ERROR : ' + data);
            }

            realtime_request = null;
            realtime_timeout = null;
            realtime();

        }) .fail(function(qXHR, textStatus, errorThrown) {
            if (qXHR.statusText !== 'abort' && qXHR.statusText !== 'canceled') {
                console.log('ERROR : ' + errorThrown);
            }

            realtime_request = null;
            realtime_timeout = null;
            realtime();

        });
    }, realtime_refresh_rate);
}



function realtime_logs(realtime_logs) {
    $.each(realtime_logs, function(index, realtime_log) {
        if (realtime_log.log_type !== undefined && realtime_log.related_parameters !== undefined) {

            if (realtime_log.log_type === 'message_reaction') {
                realtime_log.related_parameters = $.parseJSON(realtime_log.related_parameters);

                if (realtime_log.related_parameters.total_reactions !== undefined) {
                    realtime_log.related_parameters.total_reactions = $.parseJSON(realtime_log.related_parameters.total_reactions);
                    update_message_reactions(realtime_log.related_parameters);
                }
            } else if (realtime_log.log_type === 'deleted_message') {
                realtime_log.related_parameters = $.parseJSON(realtime_log.related_parameters);

                if (realtime_log.related_parameters.message_id !== undefined) {
                    remove_messages(realtime_log.related_parameters);
                }
            } else if (realtime_log.log_type === 'removed_all_messages') {
                realtime_log.related_parameters = $.parseJSON(realtime_log.related_parameters);

                if (realtime_log.related_parameters.group_id !== undefined) {
                    if ($('.main .chatbox').attr('group_id') === realtime_log.related_parameters.group_id) {
                        $('.main .chatbox > .contents > .chat_messages > ul').html('');
                    }
                }
            }
        }
    });
}

function typing_indicator(todo = 'log') {

    if (todo === undefined || todo === 'log') {
        if (!$('.main .chatbox').hasClass('logged_user_typing_status')) {

            $('.main .chatbox').addClass('logged_user_typing_status');

            var ajax_data = {
                update: 'typing_status',
            };

            if ($('.main .chatbox').attr('group_id') !== undefined) {
                ajax_data['group_id'] = $('.main .chatbox').attr('group_id');
            } else if ($('.main .chatbox').attr('user_id') !== undefined) {
                ajax_data['user_id'] = $('.main .chatbox').attr('user_id');
            }

            if ($('.main .chatbox > .header > .switch_user > .user_id > input').length > 0) {
                var send_as_user_id = $('.main .chatbox > .header > .switch_user > .user_id > input').val();

                if (send_as_user_id.length > 0 && send_as_user_id !== '0') {
                    ajax_data['send_as_user_id'] = send_as_user_id;
                }
            }

            user_typing_log_request = $.ajax({
                type: 'POST',
                url: api_request_url,
                data: ajax_data,
                async: true,
                beforeSend: function() {
                    if (user_typing_log_request !== null) {
                        user_typing_log_request.abort();
                        user_typing_log_request = null;
                    }
                },
                success: function(data) {}
            }).done(function(data) {
                $('.main .chatbox').addClass('logged_user_typing_status');
            }).fail(function(qXHR, textStatus, errorThrown) {
                $('.main .chatbox').removeClass('logged_user_typing_status');
            });

            if ($('.main .chatbox').hasClass('logged_user_typing_status')) {
                if (user_typing_log_timeout !== null) {
                    clearTimeout(user_typing_log_timeout);
                }

                user_typing_log_timeout = setTimeout(function() {
                    $('.main .chatbox').removeClass('logged_user_typing_status');
                    user_typing_log_timeout = null;
                }, 10000);

            }
        }
    } else if (todo === 'reset') {

        if (user_typing_log_timeout !== null) {
            clearTimeout(user_typing_log_timeout);
            user_typing_log_timeout = null;
        }

        whos_typing(null)

        $('.main .chatbox').removeClass('logged_user_typing_status');
    }
}


function whos_typing(user_data) {

    if (user_data !== undefined) {

        if (users_typing_timeout !== null) {
            clearTimeout(users_typing_timeout);
            users_typing_timeout = null;
        }

        if (user_data === null || user_data === '') {
            $('.main .chatbox > .header > .heading > .whos_typing').attr('last_logged_user_id', 0);
            $('.main .chatbox > .header > .heading > .whos_typing > ul').html('');
        } else {
            var users_typing = '';

            $.each(user_data, function(key, user) {
                users_typing += '<li>'+user+' '+language_string('is_typing')+'</li>';
            });

            $('.main .chatbox > .header > .heading > .whos_typing > ul').html(users_typing);
        }
    }

    if ($('.main .chatbox > .header > .heading > .whos_typing > ul').length > 0) {
        if ($('.main .chatbox > .header > .heading > .whos_typing > ul > li.active').length === 0) {
            $('.main .chatbox > .header > .heading > .whos_typing > ul > li:first-child').addClass('active');
        } else {

            var $active = $('.main .chatbox > .header > .heading > .whos_typing > ul > li.active');

            if ($active.next().length > 0) {
                var $next = $active.next();
            } else {
                var $next = $('.main .chatbox > .header > .heading > .whos_typing > ul > li:first-child');
            }

            $next.addClass('active');

            if ($('.main .chatbox > .header > .heading > .whos_typing > ul > li').length > 1) {
                $active.removeClass('active');
            }

        }

        if (users_typing_timeout !== null) {
            clearTimeout(users_typing_timeout);
        }

        if ($('.main .chatbox > .header > .heading > .whos_typing > ul > li').length > 1) {
            users_typing_timeout = setTimeout(function() {
                whos_typing();
                users_typing_timeout = null;
            }, 2000);
        }
    }
}