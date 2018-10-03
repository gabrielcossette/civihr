<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2018
 *
 * Generated from xml/schema/CRM/HRLeaveAndAbsences/WorkDay.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:ce3eb1bcb9d930cba6dc403fd1c73daf)
 */

/**
 * Database access object for the WorkDay entity.
 */
class CRM_HRLeaveAndAbsences_DAO_WorkDay extends CRM_Core_DAO {

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  static $_tableName = 'civicrm_hrleaveandabsences_work_day';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  static $_log = TRUE;

  /**
   * Unique WorkDay ID
   *
   * @var int unsigned
   */
  public $id;

  /**
   * A number between 1 and 7, following ISO-8601. 1 is Monday and 7 is Sunday
   *
   * @var int unsigned
   */
  public $day_of_the_week;

  /**
   * The type of this day, according to the values on the Work Day Type Option Group
   *
   * @var string
   */
  public $type;

  /**
   * The start time of this work day. This field is a char because CiviCRM can't handle TIME fields.
   *
   * @var string
   */
  public $time_from;

  /**
   * The end time of this work day. This field is a char because CiviCRM can't handle TIME fields.
   *
   * @var string
   */
  public $time_to;

  /**
   * The amount of break time (in hours) allowed for this day.
   *
   * @var float
   */
  public $break;

  /**
   * One of the values of the Leave Days Amount option group
   *
   * @var string
   */
  public $leave_days;

  /**
   * This is the number of hours between time_from and time_to minus break
   *
   * @var float
   */
  public $number_of_hours;

  /**
   * The Work Week this Day belongs to
   *
   * @var int unsigned
   */
  public $week_id;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_hrleaveandabsences_work_day';
    parent::__construct();
  }

  /**
   * Returns foreign keys and entity references.
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  public static function getReferenceColumns() {
    if (!isset(Civi::$statics[__CLASS__]['links'])) {
      Civi::$statics[__CLASS__]['links'] = static ::createReferenceColumns(__CLASS__);
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'week_id', 'civicrm_hrleaveandabsences_work_week', 'id');
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', Civi::$statics[__CLASS__]['links']);
    }
    return Civi::$statics[__CLASS__]['links'];
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => 'Unique WorkDay ID',
          'required' => TRUE,
          'table_name' => 'civicrm_hrleaveandabsences_work_day',
          'entity' => 'WorkDay',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_WorkDay',
          'localizable' => 0,
        ],
        'day_of_the_week' => [
          'name' => 'day_of_the_week',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Day Of The Week'),
          'description' => 'A number between 1 and 7, following ISO-8601. 1 is Monday and 7 is Sunday',
          'required' => TRUE,
          'table_name' => 'civicrm_hrleaveandabsences_work_day',
          'entity' => 'WorkDay',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_WorkDay',
          'localizable' => 0,
        ],
        'type' => [
          'name' => 'type',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Type'),
          'description' => 'The type of this day, according to the values on the Work Day Type Option Group',
          'required' => TRUE,
          'maxlength' => 512,
          'size' => CRM_Utils_Type::HUGE,
          'table_name' => 'civicrm_hrleaveandabsences_work_day',
          'entity' => 'WorkDay',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_WorkDay',
          'localizable' => 0,
          'pseudoconstant' => [
            'optionGroupName' => 'hrleaveandabsences_work_day_type',
            'optionEditPath' => 'civicrm/admin/options/hrleaveandabsences_work_day_type',
          ]
        ],
        'time_from' => [
          'name' => 'time_from',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Time From'),
          'description' => 'The start time of this work day. This field is a char because CiviCRM can\'t handle TIME fields.',
          'maxlength' => 5,
          'size' => CRM_Utils_Type::SIX,
          'table_name' => 'civicrm_hrleaveandabsences_work_day',
          'entity' => 'WorkDay',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_WorkDay',
          'localizable' => 0,
        ],
        'time_to' => [
          'name' => 'time_to',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Time To'),
          'description' => 'The end time of this work day. This field is a char because CiviCRM can\'t handle TIME fields.',
          'maxlength' => 5,
          'size' => CRM_Utils_Type::SIX,
          'table_name' => 'civicrm_hrleaveandabsences_work_day',
          'entity' => 'WorkDay',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_WorkDay',
          'localizable' => 0,
        ],
        'break' => [
          'name' => 'break',
          'type' => CRM_Utils_Type::T_MONEY,
          'title' => ts('Break'),
          'description' => 'The amount of break time (in hours) allowed for this day. ',
          'precision' => [
            20,
            2
          ],
          'table_name' => 'civicrm_hrleaveandabsences_work_day',
          'entity' => 'WorkDay',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_WorkDay',
          'localizable' => 0,
        ],
        'leave_days' => [
          'name' => 'leave_days',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Leave Days'),
          'description' => 'One of the values of the Leave Days Amount option group',
          'maxlength' => 512,
          'size' => CRM_Utils_Type::HUGE,
          'table_name' => 'civicrm_hrleaveandabsences_work_day',
          'entity' => 'WorkDay',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_WorkDay',
          'localizable' => 0,
          'pseudoconstant' => [
            'optionGroupName' => 'hrleaveandabsences_leave_days_amounts',
            'optionEditPath' => 'civicrm/admin/options/hrleaveandabsences_leave_days_amounts',
          ]
        ],
        'number_of_hours' => [
          'name' => 'number_of_hours',
          'type' => CRM_Utils_Type::T_MONEY,
          'title' => ts('Number Of Hours'),
          'description' => 'This is the number of hours between time_from and time_to minus break',
          'precision' => [
            20,
            2
          ],
          'table_name' => 'civicrm_hrleaveandabsences_work_day',
          'entity' => 'WorkDay',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_WorkDay',
          'localizable' => 0,
        ],
        'week_id' => [
          'name' => 'week_id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => 'The Work Week this Day belongs to',
          'required' => TRUE,
          'table_name' => 'civicrm_hrleaveandabsences_work_day',
          'entity' => 'WorkDay',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_WorkDay',
          'localizable' => 0,
          'FKClassName' => 'CRM_HRLeaveAndAbsences_DAO_WorkWeek',
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'hrleaveandabsences_work_day', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'hrleaveandabsences_work_day', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [
      'unique_day_for_week' => [
        'name' => 'unique_day_for_week',
        'field' => [
          0 => 'week_id',
          1 => 'day_of_the_week',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civicrm_hrleaveandabsences_work_day::1::week_id::day_of_the_week',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
