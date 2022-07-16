<?php

$all_css_variables = DB::connect()->select("css_variables", ["css_variable", "css_variable_value", "color_scheme"]);
$stored_css_variables = array();

foreach ($all_css_variables as $stored_css_variable) {
    $color_scheme = $stored_css_variable['color_scheme'];
    $variable = $stored_css_variable['css_variable'];
    $stored_css_variables[$color_scheme][$variable] = $stored_css_variable['css_variable_value'];
}


include('fns/global/css_variables.php');

$contents = '';

$contents .= ':root {'."\n";

foreach ($css_variables as $variable_index => $css_variable) {
    foreach ($css_variable as $variable => $value) {

        $css_variable_name = $variable_index.'-'.$variable;

        if (isset($stored_css_variables['light_mode'][$css_variable_name])) {
            $value = $stored_css_variables['light_mode'][$css_variable_name];
        }

        $contents .= '--'.$variable_index.'-'.$variable.': '.$value.';'."\n";
    }
}

$contents .= '}'."\n";

$cachefile = 'assets/css/common/css_variables.css';

if (file_exists($cachefile)) {
    unlink($cachefile);
}

$cachefile = fopen($cachefile, "w");
fwrite($cachefile, $contents);
fclose($cachefile);


include('fns/global/dark_mode_css_variables.php');

$contents = '';

$contents .= ':root {'."\n";

foreach ($css_variables as $variable_index => $css_variable) {
    foreach ($css_variable as $variable => $value) {

        $css_variable_name = $variable_index.'-'.$variable;

        if (isset($stored_css_variables['dark_mode'][$css_variable_name])) {
            $value = $stored_css_variables['dark_mode'][$css_variable_name];
        }

        $contents .= '--'.$variable_index.'-'.$variable.': '.$value.';'."\n";
    }
}

$contents .= '}'."\n";

$cachefile = 'assets/css/common/dark_mode_css_variables.css';

if (file_exists($cachefile)) {
    unlink($cachefile);
}

$cachefile = fopen($cachefile, "w");
fwrite($cachefile, $contents);
fclose($cachefile);

$result = true;