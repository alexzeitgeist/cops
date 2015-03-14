<?php
/**
 * COPS (Calibre OPDS PHP Server)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

use Gregwar\Image\Image;

if (empty($_REQUEST['id']) AND empty($_REQUEST['data']))
{
    send_not_found();
}

require('config.php');
require('book.php');

global $config;

$bookId = getURLParam('id', NULL);
$type = getURLParam('type', 'jpg');
$idData = getURLParam('data', NULL);
$height = getURLParam('height', NULL);
$width = getURLParam('width', NULL);

$height = $height === NULL ? NULL : (int) $height;
$width = $width === NULL ? NULL : (int) $width;

if ($bookId === null)
{
    $book = Book::getBookByDataId($idData);
}
else
{
    $book = Book::getBookById($bookId);
}

if ($book === null OR !($book instanceof Book))
{
    send_not_found();
}

$file = NULL;
$is_image = false;

switch ($type)
{
    case 'tn':
    case 'cover':
        foreach ($config['cops_images'] as $locations)
        {
            foreach ($locations[$type] as $dimension)
            {
                if (($height === NULL || $dimension['height'] === $height) && ($width === NULL || $dimension['width'] === $width))
                {
                    $file = $book->getFilePath('jpg');
                    $is_image = true;
                    break;
                }
            }
        }
        break;

    default:
        $data = $book->getDataById($idData);
        if (strtolower($data->format) === $type)
        {
            $file = $book->getFilePath($type, $idData);
        }
        break;
}

if ($file === null || !file_exists ($file))
{
    send_not_found();
}

if ($is_image)
{
    include_once('resources/Image/Image.php');
    require('resources/Image/vendor/autoload.php');
}

switch ($type)
{
    case 'tn':
        $image_cache_file = Image::open($file)->setActualCacheDir($config['cops_image_cache_path'])
                                              ->setCacheDir($config['cops_image_cache_nginx_location'])
                                              ->scaleResize($width, $height)
                                              ->jpeg(75);
        break;

    case 'cover':
        $image_cache_file = Image::open($file)->setActualCacheDir($config['cops_image_cache_path'])
                                              ->setCacheDir($config['cops_image_cache_nginx_location'])
                                              ->cropResize($width, $height)
                                              ->jpeg(75);
        break;

    case 'epub':
        if ($config['cops_provide_kepub'] === "1" && strpos($_SERVER['HTTP_USER_AGENT'], 'Kobo') !== false)
        {
            $type = 'kepub.epub';
        }
        break;

    case 'imp-1200':
        $type = 'imp';
        break;

    default:
        break;
}

if ($is_image)
{
    deliver_asset($image_cache_file, 'image/jpeg', $config['cops_image_client_cache_age']);
}
else
{
    deliver_asset($file, $data->getMimeType(), $config['cops_attachment_client_cache_age'], $config['cops_attachment_basename'] . $book->id . '.' . $type);
}

function deliver_asset($file, $mime, $age, $attachment = NULL)
{
    header("Content-type: $mime");
    header("Cache-Control: public, max-age=$age");
    if ($attachment !== NULL)
    {
        header("Content-Disposition:attachment; filename=$attachment");
    }
    header('Expires: ' . gmdate("D, d M Y H:i:s", time() + $age) . ' GMT');
    header("X-Accel-Redirect: $file");
    exit();
}

function send_not_found()
{
    header('HTTP/1.1 404 Not Found');
    exit();
}