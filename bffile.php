<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.BFFile
 *
 * @copyright   Copyright (C) 2018 Jonathan Brain. Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('components.com_fields.libraries.fieldsplugin', JPATH_ADMINISTRATOR);

/**
 * Fields BFFile Plugin
 *
 * @since  3.7.0
 */
class PlgFieldsBffile extends FieldsPlugin
{
	/**
	 * Add enctype to form tag - required for file upload
	 *
	 * @return  void
	 */
  public function onAfterDispatch() {
    if (JFactory::getApplication()->isAdmin()) {
      $doc = JFactory::getDocument();
      $buffer = $doc->getBuffer();
      if (isset($buffer['component'][null][null])) {
        $html = $buffer['component'][null][null];
        $start = strpos($html, '<form');
        if ($start !== false) {
          $end = strpos($html, '>', $start);
          if ($end !== false) {
            $formTag = substr($html, $start, $end-$start);
            if (strpos($formTag, 'enctype=') === false) {
              $doc->setBuffer((($start == 0) ? '' : substr($html, 0, $start)) . $formTag . ' enctype="multipart/form-data"' . substr($html, $end), array('type'=>'component', 'name'=>null, 'title'=>null));
            }
          }
        }
      }
    }
  }
  
	/**
	 * @return  void
	 */
  public function onUserBeforeDataValidation($form, &$data) {
    $bffileIDs = array();  
    if (is_array($_FILES['jform']['name']['com_fields'])) {
      foreach($_FILES['jform']['name']['com_fields'] as $id=>$fileName) {
        if (!empty($_FILES['jform']['tmp_name']['com_fields'][$id]) && @file_exists($_FILES['jform']['tmp_name']['com_fields'][$id])) {
          $field = array();
          $field['filename'] = $fileName;
          $field['size'] = filesize($_FILES['jform']['tmp_name']['com_fields'][$id]);
          $field['data'] = base64_encode(file_get_contents($_FILES['jform']['tmp_name']['com_fields'][$id]));
          if (!isset($data['com_fields'])) {
            $data['com_fields'] = array();
          }
          $data['com_fields'][$id] = json_encode($field);
          $bffileIDs[] = $id;
        }
      }
    }

    if (is_array($data['com_fields']['bffile-original'])) {
      foreach($data['com_fields']['bffile-original'] as $id => $field) {
        if (empty($data['com_fields'][$id])) {
          $data['com_fields'][$id] = str_replace('&#34;', '"', $field);
        }
      }
      unset($data['com_fields']['bffile-original']);
      $bffileIDs[] = $id;
    }

    foreach($form->getFieldSets() as $setName=>$set) {
      if (strpos($setName, 'fields-') === 0) {
        $setFields = $form->getFieldSet($setName);
        foreach (array_unique($bffileIDs) as $id) {
          $formfieldid = 'jform_com_fields_' . $id;
          if (isset($setFields[$formfieldid])) {
            $field = $setFields[$formfieldid];
            $suffix_list = $field->getParam('bffile_suffix_list');
            if (!empty($suffix_list) && !empty($data['com_fields'][$id])) {
              $valueObject = json_decode($data['com_fields'][$id]);
              if (empty($valueObject)) {
                $data['com_fields'][$id] = null;
                JFactory::getApplication()->enqueueMessage(JText::sprintf('PLG_FIELDS_BFFILE_DATA_ALERT', $field->getParam('label'), jText::_('PLG_FIELDS_BFFILE_DATA_CORRUPT')));
              }
              else if (!in_array(pathinfo($valueObject->filename, PATHINFO_EXTENSION), $suffix_list)) {
                $data['com_fields'][$id] = null;
                JFactory::getApplication()->enqueueMessage(jText::sprintf('PLG_FIELDS_BFFILE_SUFFIX_UNSUPPORTED', $field->getParam('label'), $valueObject->filename), 'error');
              }
            }
          }
        }
      }
    }
  }

	/**
	 * Transforms the field into a DOM XML element and appends it as a child on the given parent.
	 *
	 * @param   stdClass    $field   The field.
	 * @param   DOMElement  $parent  The field node parent.
	 * @param   JForm       $form    The form.
	 *
	 * @return  DOMElement
	 *
	 * @since   3.7.0
	 */
	public function onCustomFieldsPrepareDom($field, DOMElement $parent, JForm $form)
	{
		$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

		if (!$fieldNode)
		{
			return $fieldNode;
		}

		$fieldNode->setAttribute('hide_default', 'true');

		return $fieldNode;
	}

	/**
	 * The form event. Load additional parameters when available into the field form.
	 * Only when the type of the form is of interest.
	 *
	 * @param   JForm     $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function onContentPrepareForm(JForm $form, $data)
	{
		// Load framework's fields
		$form->addFieldPath(__DIR__);
		$form->removeField('default_value');

		return parent::onContentPrepareForm($form, $data);
	}
  
	/**
	 * Returns the custom fields types.
	 *
	 * @return  string[][]
	 *
	 * @since   3.7.0
	 */
	public function onCustomFieldsGetTypes() {
    return array('bffile' => array('type' => 'bffile',
                                   'label' => 'Brainforge File',
                                   'path' => __DIR__ . '/fields',
                                   'rules' => null));
  }
}
