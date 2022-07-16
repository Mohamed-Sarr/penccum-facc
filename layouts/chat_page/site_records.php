<div class="search">
    <i class="bi bi-search"></i>
    <input type="search" placeholder='<?php echo(Registry::load('strings')->search_here) ?>' />
</div>

<div class="current_record d-none">

    <label class="selector selected select_all d-none">
        <input type="checkbox" name="select_all" value="all" />
        <span class="checkmark"></span>
    </label>

    <div class="title">
        <div>
            <span class="text"></span>
            <span class="filter"></span>
            <div class="dropdown_list">
                <ul></ul>
            </div>
        </div>
    </div>

    <div class="options">

        <div class="toggle_checkbox">
            <i class="iconic_selection"></i>
        </div>

        <div class="sort dropdown_button d-none">
            <span>
                <span></span>
                <i class="iconic_arrow-down"></i>
            </span>
            <div class="sort_by_list">
                <div class="dropdown_list">
                    <ul></ul>
                </div>
            </div>
        </div>
    </div>

    <div class="record_info d-none">
        <div class="offset">
            <textarea name="current_record_offset" class="current_record_offset"></textarea>
        </div>
        <div class="filter">
            <input type="text" name="current_record_filter" value='' class="current_record_filter" />
        </div>
        <div class="sort_by">
            <input type="text" name="current_record_sort_by" value='' class="current_record_sort_by" />
        </div>
        <div class="search_keyword">
            <input type="text" name="current_record_search_keyword" value='' class="current_record_search_keyword" />
        </div>
        <div class="data_attributes"></div>
        <div class="refresh_current_record"></div>
    </div>

</div>
<div class="confirm_box d-none animate__animated animate__flipInX">
    <div class="error">
        <span class="message"><?php echo(Registry::load('strings')->error) ?> : <span></span></span>
    </div>
    <div class="content">
        <span class="text"></span>
        <span class="btn cancel"><span></span></span>
        <span class="btn submit"><span></span></span>
    </div>
</div>

<div class="records">

    <div class="zero_results d-none">
        <div>
            <div class="image">
                <img src="<?php echo Registry::load('config')->site_url ?>assets/files/defaults/no_results_found.png" />
            </div>
            <div class="text">
                <span class="title"><?php echo(Registry::load('strings')->no_results_found) ?></span>
                <span class="subtitle"><?php echo(Registry::load('strings')->no_results_found_subtitle) ?></span>
            </div>
        </div>
    </div>

    <div class="on_error d-none">
        <div class="content">
            <div>
                <span class="error_image">
                    <img src="<?php echo Registry::load('config')->site_url ?>assets/files/defaults/error_image.png" />
                </span>
                <span class="text">
                    <span class="title"><?php echo(Registry::load('strings')->error) ?></span>
                    <span class="subtitle"><?php echo Registry::load('strings')->error_message ?></span>
                </span>
            </div>
        </div>
    </div>

    <div class="loader aside_loader">
        <ul></ul>
    </div>
    <div class='dragfile dragupload'>
        <div>
            <div>
                <div class="icon"></div>
            </div>
        </div>
    </div>

    <ul class='list'>

    </ul>
</div>

<div class="tools">
    <div class="tool multiple_selection d-none">
        <span></span>
    </div>
    <div class="tool todo d-none">
        <span class="animate__animated animate__flipInY"></span>
    </div>
</div>