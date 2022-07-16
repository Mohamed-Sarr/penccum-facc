<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';

$group_id = 0;
$referrer_user_id = Registry::load('current_user')->id;
$usernames = $email_addresses = array();
$invitations = array();

$super_privileges = false;

if (role(['permissions' => ['groups' => 'super_privileges']])) {
    $super_privileges = true;
}

if (isset($data['group_id'])) {
    $data["group_id"] = filter_var($data["group_id"], FILTER_SANITIZE_NUMBER_INT);
}

if (isset($data['group_id']) && !empty($data['group_id'])) {
    $columns = $join = $where = null;
    $columns = [
        'groups.secret_group', 'groups.password', 'group_members.group_role_id',
        'group_members.group_member_id', 'group_roles.group_role_attribute',
    ];

    $join["[>]group_members"] = ["groups.group_id" => "group_id", "AND" => ["user_id" => $referrer_user_id]];
    $join["[>]group_roles"] = ["group_members.group_role_id" => "group_role_id"];

    $where["groups.group_id"] = $data["group_id"];
    $where["LIMIT"] = 1;

    $group = DB::connect()->select('groups', $join, $columns, $where);

    if (isset($group[0])) {

        $group = $group[0];

        if ($super_privileges || isset($group['group_role_id']) && !empty($group['group_role_id'])) {
            if ($super_privileges || role(['permissions' => ['groups' => 'invite_users']]) && $group['group_role_attribute'] === 'administrators') {
                $group_id = $data["group_id"];
            } else if (role(['permissions' => ['groups' => 'invite_users']]) && empty($group['password']) && empty($group['secret_group']) && $group['group_role_attribute'] !== 'banned_users') {
                $group_id = $data["group_id"];
            }
        }
    }


    if (isset($data['email_username']) && !empty($data['email_username'])) {
        $invites = preg_split("/\r\n|\n|\r/", $data['email_username']);
        if (!empty($invites)) {
            $email_addresses = array_filter(array_map('trim', $invites), function ($invite) {
                return (filter_var($invite, FILTER_VALIDATE_EMAIL)) ? true : false;
            });

            $usernames = array_diff($invites, $email_addresses);
        }
    }

    if (empty($email_addresses) && empty($usernames)) {
        $group_id = 0;
        $result['error_variables'][] = ['email_username'];
        $result['error_message'] = Registry::load('strings')->invalid_value;
        $result['error_key'] = 'invalid_value';
    }


    if (!empty($group_id)) {

        $columns = $join = $where = null;
        $columns = [
            'group_members.group_member_id', 'site_users.user_id', 'site_users.email_address',
            'group_invitations.group_invitation_id'
        ];

        $join["[>]group_members"] = ["site_users.user_id" => "user_id", "AND" => ["group_members.group_id" => $group_id]];
        $join["[>]group_invitations"] = ["site_users.user_id" => "user_id", "AND" => ["group_invitations.group_id" => $group_id]];

        if (empty($email_addresses)) {
            $where["site_users.username"] = $usernames;
        } else if (empty($usernames)) {
            $where["site_users.email_address"] = $email_addresses;
        } else {
            $where["OR"] = ["site_users.username" => $usernames, "site_users.email_address" => $email_addresses];
        }

        $site_users = DB::connect()->select('site_users', $join, $columns, $where);

        foreach ($site_users as $site_user) {

            if (($key = array_search($site_user['email_address'], $email_addresses)) !== false) {
                unset($email_addresses[$key]);
            }

            if (!isset($site_user['group_member_id']) || empty($site_user['group_member_id'])) {
                if (!isset($site_user['group_invitation_id']) || empty($site_user['group_invitation_id'])) {
                    $invitations[] = [
                        'user_id' => $site_user['user_id'],
                        'group_id' => $group_id,
                        'referrer_user_id' => $referrer_user_id,
                        'related_email_address' => $site_user['email_address'],
                        'invitation_code' => random_string(['length' => 8]),
                        'created_on' => Registry::load('current_user')->time_stamp,
                        'updated_on' => Registry::load('current_user')->time_stamp
                    ];
                }
            }
        }

        foreach ($email_addresses as $email_address) {
            $invitations[] = [
                'user_id' => null,
                'group_id' => $group_id,
                'referrer_user_id' => $referrer_user_id,
                'related_email_address' => $email_address,
                'invitation_code' => random_string(['length' => 8]),
                'created_on' => Registry::load('current_user')->time_stamp,
                'updated_on' => Registry::load('current_user')->time_stamp
            ];
        }

        if (!empty($invitations)) {
            foreach ($invitations as $invitation) {
                DB::connect()->insert("group_invitations", $invitation);
                if (!DB::connect()->error) {
                    $invitation_id = DB::connect()->id();

                    if (!empty($invitation['user_id'])) {

                        if (isset(Registry::load('settings')->site_notifications->on_group_invitation)) {
                            $related_parameters = ['invitation_id' => $invitation_id, 'invitation_code' => $invitation['invitation_code']];
                            $related_parameters = json_encode($related_parameters);
                            DB::connect()->insert("site_notifications", [
                                "user_id" => $invitation['user_id'],
                                "notification_type" => 'group_invitation',
                                "related_user_id" => $referrer_user_id,
                                "related_group_id" => $group_id,
                                "related_parameters" => $related_parameters,
                                "created_on" => Registry::load('current_user')->time_stamp,
                                "updated_on" => Registry::load('current_user')->time_stamp,
                            ]);
                        }
                    } else if (!empty($invitation['related_email_address'])) {
                        include('fns/mailer/load.php');

                        $invitation_link = Registry::load('config')->site_url.'group/'.$group_id.'/invitation/';
                        $invitation_link .= $invitation_id.'/'.$invitation['invitation_code'].'/';

                        $mail = array();
                        $mail['email_addresses'] = $invitation['related_email_address'];
                        $mail['category'] = 'group_invitation';
                        $mail['parameters'] = ['link' => $invitation_link];
                        $mail['send_now'] = true;
                        mailer('compose', $mail);
                    }
                }
            }
        }

        $result = array();
        $result['success'] = true;
        $result['success'] = true;
        $result['todo'] = 'load_conversation';
        $result['identifier_type'] = 'group_id';
        $result['identifier'] = $group_id;
        $result['reload_aside'] = true;
    }
}

?>