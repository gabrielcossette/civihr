<?php
/*
 +--------------------------------------------------------------------+
 | CiviHR version 1.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2014                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

/**
 * class to parse csv files
 */
class CRM_Hrjobcontract_Import_Parser_BaseClass extends CRM_Hrjobcontract_Import_Parser {
  protected $_mapperKeys;

  /**
   * Array of select lists options in job roles page
   *
   * @var array
   */

  private $_optionsList;


  private $_contactIdIndex;

  /**
   * Array of successfully imported entity id's
   *
   * @array
   */
  protected $_newEntities = array();

  /**
   * class constructor
   */
  function __construct(&$mapperKeys, $mapperLocType = NULL, $mapperPhoneType = NULL) {
    parent::__construct();
    $this->_mapperKeys = &$mapperKeys;
    $this->_mapperLocType = &$mapperLocType;
  }

  /**
   * the initializer code, called before the processing
   *
   * @return void
   * @access public
   */
  function init() {
    $this->setFields();
    $fields = $this->_fields;
    $this->_fields = array();

    foreach ($fields as $name => $field) {
      $field['type'] = CRM_Utils_Array::value('type', $field, CRM_Utils_Type::T_INT);
      $field['dataPattern'] = CRM_Utils_Array::value('dataPattern', $field, '//');
      $field['headerPattern'] = CRM_Utils_Array::value('headerPattern', $field, '//');
      $this->addField($name, CRM_Utils_Array::value('title', $field, $name), $field['type'], $field['headerPattern'], $field['dataPattern']);
    }
    $this->setActiveFields($this->_mapperKeys);
    $this->setActiveFieldLocationTypes($this->_mapperLocType);

    // Fetch select list options from the database and cache them
    $this->_optionsList['HRJobHour-hours_unit'] = CRM_Hrjobcontract_SelectValues::commonUnit();
    $this->_optionsList['HRJobHour-hours_type'] = CRM_Hrjobcontract_SelectValues::buildDbOptions('hrjc_hours_type');
    $this->_optionsList['HRJobHour-location_standard_hours'] = CRM_Hrjobcontract_SelectValues::buildHourLocations();
    $this->_optionsList['HRJobPension-pension_type'] = CRM_Hrjobcontract_SelectValues::buildDbOptions('hrjc_pension_type');
    $this->_optionsList['HRJobHealth-plan_type'] = CRM_Hrjobcontract_SelectValues::planType();
    $this->_optionsList['HRJobHealth-plan_type_life_insurance'] = CRM_Hrjobcontract_SelectValues::planTypeLifeInsurance();
    $this->_optionsList['HRJobPay-pay_cycle'] = CRM_Hrjobcontract_SelectValues::buildDbOptions('hrjc_pay_cycle');
    $this->_optionsList['HRJobPay-pay_scale'] = CRM_Hrjobcontract_SelectValues::buildPayScales();
    $this->_optionsList['HRJobPay-pay_currency'] = CRM_Hrjobcontract_SelectValues::buildCurrency();
    $this->_optionsList['HRJobPay-pay_unit'] = CRM_Hrjobcontract_SelectValues::payUnit();
    $this->_optionsList['HRJobDetails-contract_type'] = CRM_Hrjobcontract_SelectValues::buildDbOptions('hrjc_contract_type');
    $this->_optionsList['HRJobDetails-location'] = CRM_Hrjobcontract_SelectValues::buildDbOptions('hrjc_location');
    $this->_optionsList['HRJobDetails-notice_unit_employee'] = $this->_optionsList['HRJobHour-hours_unit'];
    $this->_optionsList['HRJobDetails-notice_unit'] = $this->_optionsList['HRJobHour-hours_unit'];
    $this->_optionsList['HRJobDetails-end_reason'] = CRM_Hrjobcontract_SelectValues::buildDbOptions('hrjc_contract_end_reason');
    $this->_optionsList['HRJobContractRevision-change_reason'] = CRM_Hrjobcontract_SelectValues::buildDbOptions('hrjc_revision_change_reason');
  }
  /**
   * Set fields to an array of importable fields
   */
  function setFields() {
   $this->_fields = array();
  }
  /**
   * handle the values in mapField mode
   *
   * @param array $values the array of values belonging to this line
   *
   * @return boolean
   * @access public
   */
  function mapField(&$values) {
    return CRM_Import_Parser::VALID;
  }

  /**
   * handle the values in preview mode
   *
   * @param array $values the array of values belonging to this line
   *
   * @return boolean      the result of this processing
   * @access public
   */
  function preview(&$values) {
    return $this->summary($values);
  }

  /**
   * handle the values in summary mode
   *
   * @param array $values the array of values belonging to this line
   *
   * @return boolean      the result of this processing
   * @access public
   */
  function summary(&$values) {
  }

  /**
   * handle the values in import mode
   *
   * @param int $onDuplicate the code for what action to take on duplicates
   * @param array $values the array of values belonging to this line
   *
   * @return boolean      the result of this processing
   * @access public
   */
  function import($onDuplicate, &$values) {
  }

  /**
   * Get the array of successfully imported Participation ids
   *
   * @return array
   * @access public
   */
  function &getImportedParticipations() {
    return $this->_newEntities;
  }

  /**
   * the initializer code, called before the processing
   *
   * @return void
   * @access public
   */
  function fini() {}

  /**
   * confirm and get option database ID or label given its ID or label
   * @param String|Integer $option
   * @param String|Integer $value
   * @param String $returnField
   * @return Integer|String
   * @access private
   */
  protected function getOptionKey($option, $value, $returnField = 'id')  {
    $search_field = 'label';
    if (is_numeric ($value)) {
      $search_field = 'id';
    }
    $index = array_search(strtolower($value), array_map('strtolower', array_column($this->_optionsList[$option], $search_field)) );
    if ($index !== FALSE)  {
      return $this->_optionsList[$option][$index][$returnField];
    }
    return 0;
  }

  /**
   * get static option ID given its Key
   * @param String|Integer $option
   * @param String|Integer $value
   * @return Integer
   * @access private
   */
  protected function getStaticOptionKey($option, $value)  {
    $key = array_search(strtolower($value), array_map('strtolower', $this->_optionsList[$option]));
    if ($key !== FALSE)  {
      return $key;
    }
    return FALSE;
  }

}
