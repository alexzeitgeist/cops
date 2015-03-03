<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require('config_default.php');
if (file_exists(dirname(__FILE__) . '/config_local.php') && (php_sapi_name() !== 'cli')) {
    require('config_local.php');
}
