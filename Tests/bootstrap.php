
<?php
/**
 * Created by PhpStorm.
 * User: tveron
 * Date: 02/08/2016
 * Time: 12:16
 */

if (file_exists($file = __DIR__.'/autoload.php')) {
    require_once $file;
} else {
    require_once __DIR__.'/autoload.php.dist';
}