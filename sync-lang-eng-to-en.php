<?php

function mergeMissing($sourceDir, $targetDir)
{
    if (!is_dir($sourceDir)) {
        echo "Source not found: {$sourceDir}\n";
        return;
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    foreach (glob($sourceDir . '/*.php') as $sourceFile) {
        $fileName = basename($sourceFile);
        $targetFile = $targetDir . '/' . $fileName;

        $sourceArray = include $sourceFile;
        $targetArray = file_exists($targetFile) ? include $targetFile : [];

        if (!is_array($sourceArray)) {
            echo "Skip non-array source: {$sourceFile}\n";
            continue;
        }

        if (!is_array($targetArray)) {
            $targetArray = [];
        }

        $added = 0;

        $merge = function ($src, $dst) use (&$merge, &$added) {
            foreach ($src as $key => $value) {
                if (is_array($value)) {
                    $dst[$key] = $merge($value, $dst[$key] ?? []);
                } else {
                    if (!array_key_exists($key, $dst)) {
                        $dst[$key] = $value;
                        $added++;
                    }
                }
            }

            return $dst;
        };

        $merged = $merge($sourceArray, $targetArray);

        file_put_contents($targetFile, "<?php\n\nreturn " . var_export($merged, true) . ";\n");

        echo "{$sourceFile} -> {$targetFile} | Added {$added} keys\n";
    }
}

mergeMissing(__DIR__ . '/lang/eng', __DIR__ . '/lang/en');
mergeMissing(__DIR__ . '/resources/lang/eng', __DIR__ . '/resources/lang/en');

echo "Language sync completed.\n";
