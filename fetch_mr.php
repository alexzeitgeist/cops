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
switch ($type)
{
    case 'tn':
    case 'cover':
        foreach ($config['cops_image_dimensions'][$type] as $dimension)
        {
            if ((isset($height) && $dimension['height'] == $height) && (isset($width) && $dimension['width'] == $width))
            {
                $file = $book->getFilePath('jpg');
                break;
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

switch ($type)
{
    case 'tn':
    case 'cover':
        include_once('resources/Image/Image.php');
        require('resources/Image/vendor/autoload.php');

        $image_cache_file = Image::open($file)->setActualCacheDir($config['cops_image_cache_path'])
                                              ->setCacheDir($config['cops_image_cache_nginx_location'])
                                              ->scaleResize((int) $width, (int) $height, 'black')
                                              ->jpeg(75);

        deliver_asset($image_cache_file, 'image/jpeg', $config['cops_image_client_cache_age']);
        break;

	case 'epub':
        if ($config['cops_provide_kepub'] === "1" && strpos($_SERVER['HTTP_USER_AGENT'], 'Kobo') !== false)
        {
            $type = 'kepub.epub';
        }
        deliver_asset($file, $data->getMimeType(), $config['cops_attachment_client_cache_age'], $config['cops_attachment_basename'] . $book->id . '.' . $type);
        break;

    case 'imp-1200':
        $type = 'imp';
        deliver_asset($file, $data->getMimeType(), $config['cops_attachment_client_cache_age'], $config['cops_attachment_basename'] . $book->id . '.' . $type);
        break;

    default:
        deliver_asset($file, $data->getMimeType(), $config['cops_attachment_client_cache_age'], $config['cops_attachment_basename'] . $book->id . '.' . $type);
        break;
}

send_not_found();

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