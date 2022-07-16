<?php
$result = false;
$output_buffer_required = false;
$output_buffer = false;
if (isset($data['download']) && !empty($data['download'])) {
    if (isset($data['real_path']) && $data['real_path']) {
        $download = $data['download'];
    } else {
        $download = 'assets/files/'.$data['download'];
    }

    if (ini_get('output_buffering')) {
        $output_buffer = true;
    }

    if (file_exists($download)) {
        if ($output_buffer_required && !$output_buffer) {
            echo "Output_Buffering is disabled in your server. Enable output_buffering Directive in your server.";
            exit;
        } else {

            $filename = basename($download);

            if (isset($data['download_as']) && !empty($data['download_as'])) {
                $filename = $data['download_as'];
            }

            if ($output_buffer) {
                ob_clean();
                ob_flush();
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-Disposition: attachment; filename*=UTF-8''".rawurlencode($filename));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($download));

            if ($output_buffer) {
                ob_get_clean();
                while (ob_get_level()) {
                    ob_end_clean();
                }
            }

            readfile($download);
            exit;
        }
    }
}
?>