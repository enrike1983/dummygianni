<?php

require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));

// Our web handlers

$app->get('/{desired_image_width}/{desired_image_height}', function($desired_image_width, $desired_image_height) use($app) {

    $source_path = 'public/1.jpg';
    /*
     * Add file validation code here
     */
    list($source_width, $source_height, $source_type) = getimagesize($source_path);

    //fix in caso di immagine troppo grande
    if($source_width < $desired_image_width || $source_height < $desired_image_height) {
        $desired_image_width = $source_width;
        $desired_image_height = $source_height;
    }

    switch ($source_type) {
        case IMAGETYPE_GIF:
            $source_gdim = imagecreatefromgif($source_path);
            break;
        case IMAGETYPE_JPEG:
            $source_gdim = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source_gdim = imagecreatefrompng($source_path);
            break;
    }

    $source_aspect_ratio = $source_width / $source_height;
    $desired_aspect_ratio = $desired_image_width / $desired_image_height;

    if ($source_aspect_ratio > $desired_aspect_ratio) {
        /*
         * Triggered when source image is wider
         */
        $temp_height = $desired_image_height;
        $temp_width = ( int ) ($desired_image_height * $source_aspect_ratio);
    } else {
        /*
         * Triggered otherwise (i.e. source image is similar or taller)
         */
        $temp_width = $desired_image_width;
        $temp_height = ( int ) ($desired_image_width / $source_aspect_ratio);
    }

    /*
     * Resize the image into a temporary GD image
     */

    $temp_gdim = imagecreatetruecolor($temp_width, $temp_height);
    imagecopyresampled(
        $temp_gdim,
        $source_gdim,
        0, 0,
        0, 0,
        $temp_width, $temp_height,
        $source_width, $source_height
    );

    /*
     * Copy cropped region from temporary image into the desired GD image
     */

    $x0 = ($temp_width - $desired_image_width) / 2;
    $y0 = ($temp_height - $desired_image_height) / 2;
    $desired_gdim = imagecreatetruecolor($desired_image_width, $desired_image_height);
    imagecopy(
        $desired_gdim,
        $temp_gdim,
        0, 0,
        $x0, $y0,
        $desired_image_width, $desired_image_height
    );

    /*
     * Render the image
     * Alternatively, you can save the image in file-system or database
     */

    header('Content-type: image/jpeg');
    imagejpeg($desired_gdim);
die();
});



$app->run();
