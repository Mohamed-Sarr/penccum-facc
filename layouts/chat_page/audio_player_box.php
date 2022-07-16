<?php
$audio = fetch(['fetch' => 'current_playing']);
$additional_class = 'no_autoplay';

if (role(['permissions' => ['audio_player' => 'listen_music']])) {

    if (isset(Registry::load('settings')->autoplay_audio_player) && Registry::load('settings')->autoplay_audio_player === 'yes') {
        $additional_class = 'autoplay';
    }

    ?>

    <div class="header<?php echo $audio['audio_player_class'] ?>">

        <div class="info">
            <div class="<?php echo $additional_class; ?> currently_playing" audio_type="<?php echo $audio['audio_type']; ?>" audio_content_id="<?php echo $audio['audio_content_id']; ?>">

                <div class="audio_player_controls">

                    <div>
                        <div class="controls">
                            <span class="previous_audio"><i class="bi bi-skip-start-fill"></i></span>
                            <span class="play_btn play_audio" audio_type="<?php echo $audio['audio_type']; ?>"><i class="bi bi-play-fill"></i></span>
                            <span class="next_audio"><i class="bi bi-skip-end-fill"></i></span>
                        </div>
                    </div>

                    <audio id="audio_player" controls preload="none">
                        <source src="<?php echo $audio['audio_url']; ?>" type="<?php echo $audio['audio_mime_type']; ?>" />
                    </audio>

                </div>

                <span class="now_playing"><?php echo Registry::load('strings')->now_playing; ?></span>
                <span class="title"><?php echo $audio['audio_title']; ?></span>
                <span class="subtitle"><span><?php echo $audio['audio_description']; ?></span></span>
            </div>
            <div class="image">
                <span>
                    <img src="<?php echo $audio['image']; ?>">
                    <span class="disc"></span>
                </span>
            </div>
        </div>


        <div class="audio_player_controls audio_duration">
            <div>

                <div class="seek_bar">
                    <div>
                        <span class="current_timestamp">
                            <span>00:00</span>
                        </span>

                        <div class="control">
                            <div>
                                <input type="range" min="1" max="100" value="1" class="audio_player_seekbar audio_player_range_control">
                            </div>
                        </div>

                        <span class="duration">
                            <span>00:00</span>
                        </span>
                    </div>
                </div>

                <div class="volume">
                    <div class="control">
                        <div>
                            <div>
                                <input type="range" min="1" max="100" value="1" class="audio_player_range_control audio_player_volume_control">
                            </div>
                        </div>
                    </div>
                    <span class="bi bi-volume-down-fill"></span>
                </div>

            </div>
        </div>


    </div>

    <div class="heading">
        <div>
            <div class="title">
                <?php echo(Registry::load('strings')->audio_player) ?>
            </div>
            <div class="button">
                <span><?php echo Registry::load('strings')->refresh ?></span>
            </div>
        </div>
    </div>

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

    <div class="playlist">
        <div>
            <ul></ul>

        </div>

    </div>

    <?php
}
?>