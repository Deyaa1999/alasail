<?php
$css = file_get_contents(__DIR__ . '/../assets/css/style.css');
$lines = explode("\n", $css);

$searchKeys = ['text-align: left', 'margin-right', 'margin-left', 'padding-left', 'padding-right', 'float: left', 'float: right'];

$results = [];
foreach ($lines as $i => $line) {
    foreach ($searchKeys as $key) {
        if (stripos($line, $key) !== false) {
            $results[] = "Line " . ($i + 1) . ": " . trim($line);
            break;
        }
    }
}

echo "Total matching lines: " . count($results) . "\n";
// Display a sample of them
foreach (array_slice($results, 0, 50) as $res) {
    echo $res . "\n";
}
?>
