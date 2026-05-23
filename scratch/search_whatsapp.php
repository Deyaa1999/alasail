<?php
function searchDir($dir, $pattern) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if ($file->isDir()) continue;
        $filePath = $file->getPathname();
        
        // Skip scratch, uploads, backups, .git, etc.
        if (strpos($filePath, 'scratch') !== false || 
            strpos($filePath, 'uploads') !== false || 
            strpos($filePath, 'backups') !== false ||
            strpos($filePath, '.git') !== false) {
            continue;
        }
        
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, ['php', 'js', 'css', 'html'])) continue;
        
        $content = file_get_contents($filePath);
        if (stripos($content, $pattern) !== false) {
            echo "Match in: $filePath\n";
            // Print matching lines
            $lines = explode("\n", $content);
            foreach ($lines as $num => $line) {
                if (stripos($line, $pattern) !== false) {
                    echo "  Line " . ($num + 1) . ": " . trim($line) . "\n";
                }
            }
        }
    }
}

echo "Searching for 'whats'...\n";
searchDir(__DIR__ . '/..', 'whats');
echo "Done!\n";
?>
