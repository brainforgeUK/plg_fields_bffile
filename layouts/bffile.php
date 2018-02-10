<?php
/**
 * @package     Joomla.Site
 * @subpackage  Fields.BFFile
 *
 * @copyright   Copyright (C) 2018 Jonathan Brain. Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$application = JFactory::getApplication();
if (!$application->isAdmin()) {
  $application->enqueueMessage(JText::_('JERROR_LOGIN_DENIED'), 'error');
}
else {
  $suffix_list = $displayData['field']->getParam('bffile_suffix_list');

  extract($displayData);

  /**
   * Layout variables
   * -----------------
   * @var   string   $autocomplete    Autocomplete attribute for the field.
   * @var   boolean  $autofocus       Is autofocus enabled?
   * @var   string   $class           Classes for the input.
   * @var   string   $description     Description of the field.
   * @var   boolean  $disabled        Is this field disabled?
   * @var   string   $group           Group the field belongs to. <fields> section in form XML.
   * @var   boolean  $hidden          Is this field hidden in the form?
   * @var   string   $hint            Placeholder for the field.
   * @var   string   $id              DOM id of the field.
   * @var   string   $label           Label of the field.
   * @var   string   $labelclass      Classes to apply to the label.
   * @var   boolean  $multiple        Does this field support multiple values?
   * @var   string   $name            Name of the input field.
   * @var   string   $onchange        Onchange attribute for the field.
   * @var   string   $onclick         Onclick attribute for the field.
   * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
   * @var   boolean  $readonly        Is this field read only?
   * @var   boolean  $repeat          Allows extensions to duplicate elements.
   * @var   boolean  $required        Is this field required?
   * @var   integer  $size            Size attribute of the input.
   * @var   boolean  $spellcheck      Spellcheck state for the form field.
   * @var   string   $validate        Validation rules to apply.
   * @var   string   $value           Value attribute of the field.
   * @var   array    $checkedOptions  Options that will be set as checked.
   * @var   boolean  $hasValue        Has this field a value assigned?
   * @var   array    $options         Options available for this field.
   * @var   array    $inputType       Options available for this field.
   * @var   array    $spellcheck      Options available for this field.
   * @var   string   $accept          File types that are accepted.
   */
  
  // Including fallback code for HTML5 non supported browsers.
  JHtml::_('jquery.framework');
  JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9'));
  
  $maxSize = JHtml::_('number.bytes', JUtility::getMaxUploadSize());
  $valueEmpty = empty($value);
  $required = $required && $valueEmpty;
  if (!$valueEmpty) {
    $valueObject = json_decode($value);
    if (empty($valueObject)) {
      $corruptMsg = jText::_('PLG_FIELDS_BFFILE_DATA_CORRUPT');
      JFactory::getDocument()->addScriptDeclaration('alert("' . jText::sprintf('PLG_FIELDS_BFFILE_DATA_ALERT', $label, $corruptMsg) . '");');
      echo '<h2 style="color:red;">' . $corruptMsg . '</h2>' .
           '<p>' .
             jText::_('PLG_FIELDS_BFFILE_XML_DESCRIPTION') .
           '</p>';  
    }
  }
  if (!empty($valueObject)) {
    if (!empty($suffix_list) && !in_array(pathinfo($valueObject->filename, PATHINFO_EXTENSION), $suffix_list)) {
      $application->enqueueMessage(jText::sprintf('PLG_FIELDS_BFFILE_SUFFIX_UNSUPPORTED', $label, $valueObject->filename), 'error');
      $required = true;
      $valueEmpty = true;
      $value = null;
      $valueObject = null;
    }
    else {
      echo $valueObject->filename . '<br/>';
    }
  }
  ?>
  <input type="file"
  	name="<?php echo $name; ?>"
  	id="<?php echo $id; ?>"
  	<?php echo !empty($size) ? ' size="' . $size . '"' : ''; ?>
  	<?php echo !empty($accept) ? ' accept="' . $accept . '"' : ''; ?>
  	<?php echo $required && !empty($class) ? ' class="' . $class . '"' : ''; ?>
  	<?php echo !empty($multiple) ? ' multiple' : ''; ?>
  	<?php echo $disabled ? ' disabled' : ''; ?>
  	<?php echo $autofocus ? ' autofocus' : ''; ?>
    <?php echo !empty($suffix_list) ? 'accept=".' . implode(',.', $suffix_list) . '"' : ''; ?>
  	<?php echo !empty($onchange) ? ' onchange="' . $onchange . '"' : ''; ?>
  	<?php echo $required ? ' required aria-required="true"' : ''; ?> /><br/>
  	<?php echo JText::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?>

  <input type="hidden"
  	name="<?php echo str_replace('[com_fields]', '[com_fields][bffile-original]', $name); ?>"
  	value="<?php echo str_replace('"', '&#34;', $value); ?>" />
  <?php
}
?>