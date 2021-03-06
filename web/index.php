<?php

require('../vendor/autoload.php');

use Symfony\Component\Finder\Finder;

$app = new Silex\Application();
$app['debug'] = true;

// Register primary services

//Monolog
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));

//Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->get('/', function () use ($app) {
    return $app['twig']->render('pages/home.twig');
});

$app->get('/{desired_image_width}/{desired_image_height}/{custom_value}', function($desired_image_width, $desired_image_height, $custom_value) use($app) {

    $public_folder = 'public/dummy-img';
    $finder = new Finder();

    switch($custom_value) {
        case 'mani':
            $finder->name('*_mani.jpg');
            break;
        case 'merda':
            $finder->name('*_merda.jpg');
            break;
    }



    $finder->files()->in($public_folder);

    $rand_key = rand(0, $finder->count()-1);

    $i = 0;
    $element = null;
    foreach($finder as $file) {
        if($i == $rand_key) {
            $element = $file;
        }
        $i++;
    }

    $source_path = $element->getRealpath();

    /*
     * Add file validation code here
     */
    list($source_width, $source_height, $source_type) = getimagesize($source_path);

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
})->value('custom_value', false);



$app->run();
