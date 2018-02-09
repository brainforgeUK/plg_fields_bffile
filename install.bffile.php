<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.BFFile
 *
 * @copyright   Copyright (C) 2018 Jonathan Brain. Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted Access');

class plgfieldsBffileInstallerScript {

	private function executeSQL($sql) {
		$db	= JFactory::getDBO();
		$db->setQuery($sql);
		$db->query();
		if ($db->getErrorNum()) echo '<p>' . $db->getErrorMsg() . '</p>';
	}

	private function sqlChanges() {
		return $this->executeSQL('ALTER TABLE `#__fields_values` CHANGE `value` `value` LONGTEXT;');
	}

	function install($parent) {
		$this->sqlChanges($parent);
	}

	function uninstall($parent) {
	}

	function update($parent) {
		$this->sqlChanges($parent);
	}

	function preflight($type, $parent) {
	}

	function postflight($type, $parent) {
	}
}
?>
