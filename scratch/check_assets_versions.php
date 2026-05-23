<?php
$html = file_get_contents('http://localhost:8080/GadgetZone/index.php');
if ($html === false) {
    $html = file_get_contents('http://localhost/GadgetZone/index.php');
}

if ($html) {
    preg_match('/<link rel="stylesheet" href="([^"]+)"/', $html, $css_matches);
    preg_match('/<script src="([^"]+)"/', $html, $js_matches);
    echo "CSS asset URL: " . ($css_matches[1] ?? 'NOT FOUND') . "\n";
    echo "JS asset URL: " . ($js_matches[1] ?? 'NOT FOUND') . "\n";
} else {
    echo "Could not fetch index.php\n";
}
