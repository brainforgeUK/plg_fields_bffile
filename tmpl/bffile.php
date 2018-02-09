<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.BFFile
 *
 * @copyright   Copyright (C) 2018 Jonathan Brain. Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

if ($field->value == '')
{
	return;
}

$valueObject = json_decode($field->value);
if (empty($valueObject)) {
  echo '<span style="color:red;">' . jText::_('PLG_FIELDS_BFFILE_DATA_CORRUPT') . '</span>';
  return;  
}

if (JFactory::getApplication()->input->get('bffile', '', 'raw') != $valueObject->filename) {
  $current = JURI::getInstance()->toString();
  $current .= ((strpos($current, '?') === false) ? '?' : '&') . 'bffile=' . urlencode($valueObject->filename);
  
  $class = $fieldParams->get('bffile_download_class');
  if ($class) {
  	echo '<span class="' . htmlentities($class, ENT_COMPAT, 'UTF-8', true) . '">';
  }
  else {
    echo '<span>';
  }
  echo '<a href="' . $current . '"><button class="button">' .
         $valueObject->filename .
       '</button></a>';
  
  echo '</span>';
  return;
}

ob_clean();

$header = true;
$ext = pathinfo($valueObject->filename, PATHINFO_EXTENSION);
switch($ext) {
  case 'doc';
    header("Content-type: application/msword");
    break;
  case 'html';
    $header = false;
    header("Content-type: text/html");
    break;
  case 'mp3';
    header("Content-type: audio/mpeg3");
    break;
  case 'pdf';
    header("Content-type: application/" . $ext);
    $header = false;
    break;
  case 'txt';
    $header = false;
    header("Content-type: text/plain");
    break;
  case 'zip';
    header("Content-type: application/" . $ext);
    break;
  default:
    header("Content-type: application/octet-stream");
    break; 
}

if ($header) {
  header("Content-Disposition: attachment; filename=" . $valueObject->filename); 
  header("Content-length: " . $valueObject->size);
  header("Expires: 0");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache"); 
}

echo base64_decode($valueObject->data);
exit(0);
