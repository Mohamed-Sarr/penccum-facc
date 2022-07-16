<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';

if (role(['permissions' => ['custom_pages' => 'create']])) {

    $result['error_message'] = Registry::load('strings')->invalid_value;
    $result['error_key'] = 'invalid_value';
    $result['error_variables'] = [];

    include 'fns/filters/load.php';
    include 'fns/files/load.php';

    $noerror = true;
    $disabled = 0;
    $clean_html = true;
    $meta_title = $meta_description = '';

    if (!isset($data['page_title']) || empty($data['page_title'])) {
        $result['error_variables'][] = ['page_title'];
        $noerror = false;
    }

    if (!isset($data['slug']) || empty(sanitize_slug($data['slug']))) {
        $result['error_variables'][] = ['slug'];
        $noerror = false;
    } else {
        $data['slug'] = sanitize_slug($data['slug']);
        if (slug_exists($data['slug'])) {
            $result['error_message'] = Registry::load('strings')->slug_already_exists;
            $result['error_key'] = 'slug_already_exists';
            $result['error_variables'][] = ['slug'];
            $noerror = false;
        }
    }

    if ($noerror) {
        $data['page_title'] = htmlspecialchars($data['page_title'], ENT_QUOTES, 'UTF-8');

        if (isset($data['disabled']) && $data['disabled'] === 'yes') {
            $disabled = 1;
        }

        if (isset($data['meta_title']) && !empty($data['meta_title'])) {
            $data['meta_title'] = htmlspecialchars(trim($data['meta_title']), ENT_QUOTES, 'UTF-8');
            if (!empty($data['meta_title'])) {
                $meta_title = $data['meta_title'];
            }
        }


        if (isset($data['meta_description']) && !empty($data['meta_description'])) {
            $data['meta_description'] = htmlspecialchars(trim($data['meta_description']), ENT_QUOTES, 'UTF-8');
            if (!empty($data['meta_description'])) {
                $meta_description = $data['meta_description'];
            }
        }

        if (isset($data['page_content']) && !empty($data['page_content'])) {
            if ($clean_html) {
                include('fns/HTMLPurifier/load.php');
                class HTMLPurifier_Strategy_Null extends HTMLPurifier_Strategy {
                    public function execute($tokens, $config, $context) {
                        return $tokens;
                    }
                }

                class HTMLLinter extends HTMLPurifier {
                    public function __construct($config = null) {
                        parent::__construct($config);
                        $this->strategy = new HTMLPurifier_Strategy_Null();
                    }
                }

                $linter = new HTMLLinter();
                $data['page_content'] = $linter->purify($data['page_content']);
            }
        } else {
            $data['page_content'] = '';
        }

        if (isset($data['who_all_can_view_page'])) {
            $data['who_all_can_view_page'] = array_filter($data['who_all_can_view_page'], 'is_numeric');
            $data["who_all_can_view_page"] = json_encode($data['who_all_can_view_page']);
        } else {
            $data["who_all_can_view_page"] = '';
        }

        DB::connect()->insert("custom_pages", [
            "string_constant" => $data['page_title'],
            "slug" => $data['slug'],
            "meta_title" => $meta_title,
            "meta_description" => $meta_description,
            "who_all_can_view_page" => $data["who_all_can_view_page"],
            "disabled" => $disabled,
            "created_on" => Registry::load('current_user')->time_stamp,
            "updated_on" => Registry::load('current_user')->time_stamp,
        ]);

        if (!DB::connect()->error) {

            $page_id = DB::connect()->id();
            $string_constant = 'custom_page_'.$page_id;
            $content_string_constant = $string_constant.'_content';

            DB::connect()->update("custom_pages", ["string_constant" => $string_constant], ["page_id" => $page_id]);

            language(['add_string' => $string_constant, 'value' => $data['page_title'], 'skip_cache' => true]);
            language(['add_string' => $content_string_constant, 'value' => $data['page_content'], 'skip_cache' => true]);

            if (isset($_FILES['featured_image']['name']) && !empty($_FILES['featured_image']['name'])) {
                if (isImage($_FILES['featured_image']['tmp_name'])) {

                    foreach (glob("assets/files/custom_pages/".$page_id.Registry::load('config')->file_seperator."*.*") as $oldimage) {
                        unlink($oldimage);
                    }

                    $extension = pathinfo($_FILES['featured_image']['name'])['extension'];
                    $filename = $page_id.Registry::load('config')->file_seperator.random_string(['length' => 6]).'.'.$extension;

                    if (files('upload', ['upload' => 'featured_image', 'folder' => 'custom_pages/', 'saveas' => $filename])['result']) {
                        files('resize_img', ['resize' => 'custom_pages/'.$filename, 'width' => 1920, 'height' => 1000, 'crop' => false]);
                    }
                }
            }

            $result = array();
            $result['success'] = true;
            $result['todo'] = 'reload';
            $result['reload'] = 'custom_pages';
        } else {
            $result['error_message'] = Registry::load('strings')->went_wrong;
            $result['error_key'] = 'something_went_wrong';
        }

    }
}
?>