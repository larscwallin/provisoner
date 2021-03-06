<?php
/**
 * Provisoner files evolution component
 *
 * @category  Provisioning
 * @author    S. Hamblett <steve.hamblett@linux.com>
 * @copyright 2010 S. Hamblett
 * @license   GPLv3 http://www.gnu.org/licenses/gpl.html
 * @link      none
 *
 * @package provisioner
 *
 */

/* Protection */
if(REVO_GATEWAY_OPEN != "true") die("Revo Gateway API error - Invalid access");

/* Get sanitized base path and current path */
$db = connectToDb();

$sql = "SELECT setting_value FROM " . $table_prefix . "system_settings "
          . "WHERE `setting_name` = 'rb_base_dir'";

$result = mysql_query($sql, $db);
$item = mysql_fetch_assoc($result);
$root = $item['setting_value'];

$sql = "SELECT setting_value FROM " . $table_prefix . "system_settings "
          . "WHERE `setting_name` = 'rb_base_url'";

$result = mysql_query($sql, $db);
$item = mysql_fetch_assoc($result);
$rbBaseUrl = $item['setting_value'];

$sql = "SELECT setting_value FROM " . $table_prefix . "system_settings "
          . "WHERE `setting_name` = 'base_url'";

$result = mysql_query($sql, $db);
$item = mysql_fetch_assoc($result);
$baseUrl = $item['setting_value'];

$dir = !isset($scriptProperties['dir']) || $scriptProperties['dir'] == 'root' ? '' : $scriptProperties['dir'];
$fullpath = $root.$dir;

$imagesExts = array('jpg','jpeg','png','gif');

$files = array();

/* Iterate through directories */
$odir = dir($fullpath);
while(false !== ($name = $odir->read())) {

    if (in_array($name,array('.','..','.svn','_notes'))) continue;
    $fullname = $fullpath.'/'.$name;
     if(!is_readable($fullname)) continue;

    $fileName = $name;
    $filePathName = $fullname;

    if(!is_dir($fullname)) {

        $fileExtension = pathinfo($filePathName,PATHINFO_EXTENSION);
	$filesize = @filesize($filePathName);
        /* calculate url */
	if (!empty($scriptProperties['prependUrl'])) {
            $url = $scriptProperties['prependUrl'].$dir.'/'.$name;
        } else {
            $url = $rbBaseUrl.'/'.$dir.'/'.$name;
        }

        /* get thumbnail */
        $thumb = str_replace('//','/',$baseUrl.$url);
        $thumbWidth = 80;
        $thumbHeight = 60;
        if (in_array($fileExtension,$imagesExts)) {    
            $size = @getimagesize($filePathName);
            if (is_array($size)) {
                $thumbWidth = $size[0];
                $thumbHeight = $size[1];
            }
        } 
           
        $files[] = array(
            'id' => $filePathName,
            'name' => utf8_encode($fileName),
            'cls' => 'icon-'.$fileExtension,
            'image' => $thumb,
            'image_width' => $thumbWidth,
            'image_height' => $thumbHeight,
            'url' => str_replace('//','/',$baseUrl.$url),
            'relativeUrl' => $url,
            'ext' => $fileExtension,
            'pathname' => str_replace('//','/',$filePathName),
            'lastmod' => '',
            'disabled' => false,
            'perms' => '',
            'leaf' => true,
            'size' => $filesize,
            'menu' => '',
                
            );
    }
}

mysql_close($db);

$response = outputArray($files);
echo $response;
