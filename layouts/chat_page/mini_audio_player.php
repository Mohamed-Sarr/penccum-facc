<?php
if (Registry::load('settings')->mini_audio_player === 'enable') {
    if (role(['permissions' => ['audio_player' => 'listen_music']])) {

        if (empty($audio['audio_title'])) {
            $columns = $where = null;
            $columns = [
                'audio_player.audio_content_id', 'audio_player.audio_title', 'audio_player.radio_stream_url',
                'audio_player.audio_description', 'audio_player.audio_type',
            ];

            $where["audio_player.disabled[!]"] = 1;
            $where["ORDER"] = ["audio_player.audio_content_id" => "DESC"];
            $where["LIMIT"] = 1;

            $audio_record = DB::connect()->select('audio_player', $columns, $where);

            if (isset($audio_record[0])) {
                $audio['audio_content_id'] = $audio_record[0]['audio_content_id'];
                $audio['audio_type'] = $audio_record[0]['audio_type'];
                $audio['audio_title'] = $audio_record[0]['audio_title'];
                $audio['audio_description'] = $audio_record[0]['audio_description'];
                $audio['image'] = get_image(['from' => 'audio_player/images', 'search' => $audio_record[0]['audio_content_id']]);
            }

        }

        if ((int)$audio['audio_type'] === 1 || $audio['audio_type'] === 'radio_station') {
            $audio['audio_content_id'] = 0;
        }

        if (!empty($audio['audio_title'])) {
            ?>
            <div class="mini_audio_player d-none">
                <div>
                    <div class="left load_audio_player">
                        <div class="image">
                            <img src="<?php echo $audio['image']; ?>" />
                        </div>
                    </div>
                    <div class="center load_audio_player">
                        <div class="title">
                            <?php echo $audio['audio_title']; ?>
                        </div>
                        <div class="description">
                            <span><?php echo $audio['audio_description']; ?></span>
                        </div>
                    </div>

                    <div class="right">
                        <div class="controls" audio_content_id="<?php echo $audio['audio_content_id']; ?>">
                            <span class="prev_track">
                                <i class="bi bi-skip-start-fill"></i>
                            </span>
                            <span class="play_audio">
                                <i class="bi bi-play-fill"></i>
                            </span>
                            <span class="next_track">
                                <i class="bi bi-skip-end-fill"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}
?>