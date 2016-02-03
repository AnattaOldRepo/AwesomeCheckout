<?php
/**
 * This file is part of AwesomeCheckout.
 *
 * AwesomeCheckout is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AwesomeCheckout is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AwesomeCheckout.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Awesome Checkout - Magento Extension
 *
 * @package     AwesomeCheckout
 * @category    AnattaDesign
 * @copyright   Copyright 2012 AnattaDesign (http://www.anattadesign.com)
 * @version:    0.0.2
 */
class AnattaDesign_AwesomeCheckout_Model_System_Conf_Backend_File_Logo extends Mage_Core_Model_Config_Data {

	/**
	 * Save uploaded file before saving config value
	 *
	 * @return Mage_Adminhtml_Model_System_Config_Backend_Image
	 */
	protected function _beforeSave() {
		$value = $this->getValue();
		if ( is_array( $value ) && !empty( $value['delete'] ) ) {
			$this->setValue( '' );
		}

		if ( $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'] ) {
			$uploadDir = $this->_getUploadDir();
			try {
				$file = array( );
				$file['tmp_name'] = $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
				$file['name'] = $_FILES['groups']['name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
				$uploader = new Varien_File_Uploader( $file );
				$uploader->setAllowedExtensions( $this->_getAllowedExtensions() );
				$uploader->setAllowRenameFiles( true );
				$uploader->save( $uploadDir );
			} catch ( Exception $e ) {
				Mage::throwException( $e->getMessage() );
				return $this;
			}

			if ( $filename = $uploader->getUploadedFileName() ) {
				if ( $this->_addWhetherScopeInfo() ) {
					$filename = $this->_prependScopeInfo( $filename );
				}
				$this->setValue( $filename );
			}
		}
		return $this;
	}

	/**
	 * Makes a decision about whether to add info about the scope.
	 *
	 * @return boolean
	 */
	protected function _addWhetherScopeInfo() {
		$fieldConfig = $this->getFieldConfig();
		$el = $fieldConfig->descend( 'upload_dir' );
		return (!empty( $el['scope_info'] ));
	}

	/**
	 * Return path to directory for upload file
	 *
	 * @return string
	 * @throw Mage_Core_Exception
	 */
	protected function _getUploadDir() {
		$fieldConfig = $this->getFieldConfig();
		/* @var $fieldConfig Varien_Simplexml_Element */

		if ( empty( $fieldConfig->upload_dir ) ) {
			Mage::throwException(
					Mage::helper( 'catalog' )->__( 'The base directory to upload image file is not specified.' ) );
		}

		$uploadDir = (string) $fieldConfig->upload_dir;

		$el = $fieldConfig->descend( 'upload_dir' );

		/**
		 * Add scope info
		 */
		if ( !empty( $el['scope_info'] ) ) {
			$uploadDir = $this->_appendScopeInfo( $uploadDir );
		}

		/**
		 * Take root from config
		 */
		if ( !empty( $el['config'] ) ) {
			$uploadRoot = $this->_getUploadRoot( (string) $el['config'] );
			$uploadDir = $uploadRoot . '/' . $uploadDir;
		}
		return $uploadDir;
	}

	/**
	 * Return the root part of directory path for uploading
	 *
	 * @var string $token
	 * @return string
	 */
	protected function _getUploadRoot( $token ) {
		$uploadRoot = (string) Mage::getConfig()->getNode( $token, $this->getScope(), $this->getScopeId() );
		$uploadRoot = Mage::getConfig()->substDistroServerVars( $uploadRoot );
		return $uploadRoot;
	}

	/**
	 * Prepend path with scope info
	 *
	 * E.g. 'stores/2/path' , 'websites/3/path', 'default/path'
	 *
	 * @param string $path
	 * @return string
	 */
	protected function _prependScopeInfo( $path ) {
		$scopeInfo = $this->getScope();
		if ( 'default' != $this->getScope() ) {
			$scopeInfo .= '/' . $this->getScopeId();
		}
		return $scopeInfo . '/' . $path;
	}

	/**
	 * Add scope info to path
	 *
	 * E.g. 'path/stores/2' , 'path/websites/3', 'path/default'
	 *
	 * @param string $path
	 * @return string
	 */
	protected function _appendScopeInfo( $path ) {
		$path .= '/' . $this->getScope();
		if ( 'default' != $this->getScope() ) {
			$path .= '/' . $this->getScopeId();
		}
		return $path;
	}

	/**
	 * @return array
	 */
	protected function _getAllowedExtensions() {
		return array( 'jpg', 'jpeg', 'png', 'gif' );
	}

}