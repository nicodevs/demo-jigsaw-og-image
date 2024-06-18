<?php

use TightenCo\Jigsaw\Jigsaw;
use Illuminate\Filesystem\Filesystem;

$events->afterBuild(function (Jigsaw $jigsaw) {
    $filesystem = new Filesystem();
    $source = $jigsaw->getSourcePath() . '/assets/og-images';
    $destination = $jigsaw->getDestinationPath() . '/assets/og-images';

    // Make sure the destination directory exists
    $filesystem->ensureDirectoryExists($destination, 0755, true);

    // Copy the OG image files to their final destination
    foreach ($filesystem->allFiles($source) as $file) {
        $filesystem->copy($file->getPathname(), $destination . '/' . $file->getFilename());
    }
});
