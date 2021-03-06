<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException as InvalidAbsenceTypeException;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Queue_PublicHolidayLeaveRequestUpdates as PublicHolidayLeaveRequestUpdatesQueue;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_AbsenceTypeTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_AbsenceTypeTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;

  private $calculationUnitOptions;

  public function setUp() {
    // We delete everything two avoid problems with the default absence types
    // created during the extension installation
    $tableName = AbsenceType::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$tableName}");
    // Delete default absence periods created during the extension installation
    $absencePeriodTable = AbsencePeriod::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$absencePeriodTable}");

    $this->calculationUnitOptions = array_flip(AbsenceType::buildOptions('calculation_unit', 'validate'));
  }

  public function testTypeTitlesShouldBeUnique() {
    AbsenceTypeFabricator::fabricate(['title' => 'Type 1']);

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException',
      'Absence Type with same title already exists!'
    );
    AbsenceTypeFabricator::fabricate(['title' => 'Type 1']);
  }

  public function testTypeTitleUniquenessValidationRespectsTrailingSpaces() {
    AbsenceTypeFabricator::fabricate(['title' => 'Type 1']);

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException',
      'Absence Type with same title already exists!'
    );
    AbsenceTypeFabricator::fabricate(['title' => ' Type 1 ']);
  }

  public function testTypeTitleShouldNotBeEmpty() {
    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException',
      'The title is not provided'
    );
    AbsenceTypeFabricator::fabricate(['title' => ''], FALSE);
  }

  public function testTypeTitleShouldNotContainSpacesOnly() {
    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException',
      'The title is not provided'
    );
    AbsenceTypeFabricator::fabricate(['title' => '   '], FALSE);
  }

  public function testTypeTitleCanBeZero() {
    $absenceType = AbsenceTypeFabricator::fabricate(['title' => '0'], FALSE);
    $this->assertEquals('0', $absenceType->title);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage There is already one Absence Type where public holidays should be added to it
   */
  public function testThereShouldBeOnlyOneTypeWithAddPublicHolidayToEntitlementOnCreate() {
    $basicEntity = AbsenceTypeFabricator::fabricate(['add_public_holiday_to_entitlement' => TRUE]);
    $entity1 = AbsenceType::findById($basicEntity->id);
    $this->assertEquals(1, $entity1->add_public_holiday_to_entitlement);

    AbsenceTypeFabricator::fabricate(['add_public_holiday_to_entitlement' => TRUE]);
  }

  public function testTypeCanBeEnabledDirectly() {
    $entity = AbsenceTypeFabricator::fabricate();
    AbsenceType::create(['id' => $entity->id, 'is_active' => 1]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage The title is not provided
   */
  public function testTypeTitleCannotBeAnEmptyStringOnUpdate() {
    $entity = AbsenceTypeFabricator::fabricate();
    AbsenceType::create(['id' => $entity->id, 'title' => '']);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage There is already one Absence Type where public holidays should be added to it
   */
  public function testThereShouldBeOnlyOneTypeWithAddPublicHolidayToEntitlementOnUpdate() {
    $basicEntity1 = AbsenceTypeFabricator::fabricate(['add_public_holiday_to_entitlement' => TRUE]);
    $basicEntity2 = AbsenceTypeFabricator::fabricate();
    $entity1 = AbsenceType::findById($basicEntity1->id);
    $entity2 = AbsenceType::findById($basicEntity2->id);
    $this->assertEquals(1, $entity1->add_public_holiday_to_entitlement);
    $this->assertEquals(0, $entity2->add_public_holiday_to_entitlement);

    $this->updateBasicType($basicEntity2->id, ['add_public_holiday_to_entitlement' => TRUE]);
  }

  public function testUpdatingATypeWithAddPublicHolidayToEntitlementShouldNotTriggerErrorAboutHavingAnotherTypeWithItSelected() {
    $basicEntity = AbsenceTypeFabricator::fabricate(['add_public_holiday_to_entitlement' => TRUE]);
    $entity1 = AbsenceType::findById($basicEntity->id);
    $this->assertEquals(1, $entity1->add_public_holiday_to_entitlement);

    $this->updateBasicType($entity1->id, ['add_public_holiday_to_entitlement' => TRUE]);
  }

  public function testThereCanBeMoreThanOneTypeWithMustTakePublicHolidayAsLeaveOnCreate() {
    $basicEntity = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => TRUE]);
    $entity1 = AbsenceType::findById($basicEntity->id);
    $this->assertEquals(1, $entity1->must_take_public_holiday_as_leave);

    AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => TRUE]);
  }

  public function testThereCanBeMoreThanOneTypeWithMustTakePublicHolidayAsLeaveOnUpdate() {
    $basicEntity1 = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => TRUE]);
    $basicEntity2 = AbsenceTypeFabricator::fabricate();
    $entity1 = AbsenceType::findById($basicEntity1->id);
    $entity2 = AbsenceType::findById($basicEntity2->id);
    $this->assertEquals(1, $entity1->must_take_public_holiday_as_leave);
    $this->assertEquals(0, $entity2->must_take_public_holiday_as_leave);

    $this->updateBasicType($basicEntity2->id, ['must_take_public_holiday_as_leave' => TRUE]);
  }

  public function testUpdatingATypeWithMustTakePublicHolidayAsLeaveShouldNotTriggerErrorAboutHavingAnotherTypeWithItSelected() {
    $basicEntity = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => TRUE]);
    $entity1 = AbsenceType::findById($basicEntity->id);
    $this->assertEquals(1, $entity1->must_take_public_holiday_as_leave);

    $this->updateBasicType($entity1->id, ['must_take_public_holiday_as_leave' => TRUE]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage To set maximum amount of leave that can be accrued you must allow staff to accrue additional days
   */
  public function testAllowAccrualsRequestShouldBeTrueIfMaxLeaveAccrualIsNotEmpty() {
    AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => FALSE,
      'max_leave_accrual' => 1
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage To allow accrue in the past you must allow staff to accrue additional days
   */
  public function testAllowAccrualsRequestShouldBeTrueIfAllowAccrueInThePast() {
    AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => FALSE,
      'allow_accrue_in_the_past' => 1
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage To set the accrual expiry duration you must allow staff to accrue additional days
   */
  public function testAllowAccrualsRequestShouldBeTrueIfAllowAccrualDurationAndUnitAreNotEmpty() {
    AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => FALSE,
      'accrual_expiration_duration' => 1,
      'accrual_expiration_unit' => AbsenceType::EXPIRATION_UNIT_DAYS
    ]);
  }

  /**
   * @dataProvider expirationUnitDataProvider
   */
  public function testShouldNotAllowInvalidAccrualExpirationUnit($expirationUnit, $throwsException) {
    if($throwsException) {
      $this->setExpectedException(
        InvalidAbsenceTypeException::class,
        'Invalid Accrual Expiration Unit'
      );
    }

    AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => TRUE,
      'accrual_expiration_duration' => 1,
      'accrual_expiration_unit' => $expirationUnit
    ]);
  }

  /**
   * @dataProvider accrualExpirationUnitAndDurationDataProvider
   */
  public function testShouldNotAllowAccrualExpirationUnitWithoutDurationAndViceVersa($unit, $duration, $throwsException) {
    if($throwsException) {
      $this->setExpectedException(
        InvalidAbsenceTypeException::class,
        'Invalid Accrual Expiration. It should have both Unit and Duration'
      );
    }

    AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => TRUE,
      'accrual_expiration_unit' => $unit,
      'accrual_expiration_duration' => $duration,
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage To set the Max Number of Days to Carry Forward you must allow Carry Forward
   */
  public function testAllowCarryForwardShouldBeTrueIfMaxNumberOfDaysToCarryForwardIsNotEmpty() {
    AbsenceTypeFabricator::fabricate([
      'allow_carry_forward' => FALSE,
      'max_number_of_days_to_carry_forward' => 1
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage To set the carry forward expiry duration you must allow Carry Forward
   */
  public function testAllowCarryForwardShouldBeTrueIfCarryForwardExpirationDurationAndUnitAreNotEmpty() {
    AbsenceTypeFabricator::fabricate([
      'allow_carry_forward' => FALSE,
      'carry_forward_expiration_duration' => 1,
      'carry_forward_expiration_unit' => AbsenceType::EXPIRATION_UNIT_DAYS
    ]);
  }

  /**
   * @dataProvider accrualExpirationUnitAndDurationDataProvider
   */
  public function testShouldNotAllowCarryForwardExpirationUnitWithoutDurationAndViceVersa($unit, $duration, $throwsException) {
    if($throwsException) {
      $this->setExpectedException(
        InvalidAbsenceTypeException::class,
        'Invalid Carry Forward Expiration. It should have both Unit and Duration'
      );
    }

    AbsenceTypeFabricator::fabricate([
      'allow_carry_forward' => TRUE,
      'carry_forward_expiration_unit' => $unit,
      'carry_forward_expiration_duration' => $duration,
    ]);
  }

  /**
   * @dataProvider expirationUnitDataProvider
   */
  public function testShouldNotAllowInvalidCarryForwardExpirationUnit($expirationUnit, $throwsException) {
    if($throwsException) {
      $this->setExpectedException(
        InvalidAbsenceTypeException::class,
        'Invalid Carry Forward Expiration Unit'
      );
    }

    AbsenceTypeFabricator::fabricate([
      'allow_carry_forward' => TRUE,
      'carry_forward_expiration_duration' => 1,
      'carry_forward_expiration_unit' => $expirationUnit
    ]);
  }

  /**
   * @dataProvider allowRequestCancelationDataProvider
   */
  public function testShouldNotAllowInvalidRequestCancelationOptions($requestCancelationOption, $throwsException) {
    if($throwsException) {
      $this->setExpectedException(
        InvalidAbsenceTypeException::class,
        'Invalid Request Cancelation Option'
      );
    }

    AbsenceTypeFabricator::fabricate(['allow_request_cancelation' => $requestCancelationOption]);
  }

  public function testWeightShouldAlwaysBeMaxWeightPlus1OnCreate() {
    $firstEntity = AbsenceTypeFabricator::fabricate();
    $this->assertNotEmpty($firstEntity->weight);

    $secondEntity = AbsenceTypeFabricator::fabricate();
    $this->assertNotEmpty($secondEntity->weight);
    $this->assertEquals($firstEntity->weight + 1, $secondEntity->weight);
  }

  public function testIsReservedCannotBeSetOnCreate() {
    $entity = AbsenceTypeFabricator::fabricate(['is_reserved' => 1]);
    $this->assertEquals(0, $entity->is_reserved);
  }

  public function testIsReservedCannotBeSetOnUpdate() {
    $entity = AbsenceTypeFabricator::fabricate();
    $this->assertEquals(0, $entity->is_reserved);
    $entity = $this->updateBasicType($entity->id, ['is_reserved' => 1]);
    $this->assertEquals(0, $entity->is_reserved);
  }

  public function testDeleteCanDeleteAbsenceType() {
    $entity = AbsenceTypeFabricator::fabricate();
    $this->assertNotNull($entity->id);
    AbsenceType::del($entity->id);

    $this->setExpectedException(
      Exception::class,
      "Unable to find a CRM_HRLeaveAndAbsences_BAO_AbsenceType with id {$entity->id}"
    );
    AbsenceType::findById($entity->id);
  }

  public function testGetValuesArrayShouldReturnAbsenceTypeValues() {
    $params = [
      'title' => 'Title 1',
      'color' => '#000101',
      'default_entitlement' => 10.6,
      'allow_request_cancelation' => 1,
      'is_active' => 1,
      'allow_carry_forward' => 1,
      'max_number_of_days_to_carry_forward' => 5.5,
    ];
    $entity = AbsenceTypeFabricator::fabricate($params);
    $values = AbsenceType::getValuesArray($entity->id);
    foreach ($params as $field => $value) {
      $this->assertEquals($value, $values[$field]);
    }
  }

  public function testHasExpirationDuration() {
    $absenceType1 = AbsenceTypeFabricator::fabricate([
      'allow_carry_forward' => TRUE
    ]);

    $absenceType2 = AbsenceTypeFabricator::fabricate([
      'allow_carry_forward' => TRUE,
      'carry_forward_expiration_duration' => 3,
      'carry_forward_expiration_unit' => AbsenceType::EXPIRATION_UNIT_DAYS,
    ]);

    $this->assertFalse($absenceType1->hasExpirationDuration());
    $this->assertTrue($absenceType2->hasExpirationDuration());
  }

  public function testCarryForwardNeverExpiresShouldReturnTrueIfTypeHasNoExpirationDuration() {
    $absenceType = AbsenceTypeFabricator::fabricate(['allow_carry_forward' => TRUE]);
    $this->assertTrue($absenceType->carryForwardNeverExpires());
  }

  public function testCarryForwardNeverExpiresShouldReturnFalseIfTypeHasExpirationDuration() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_carry_forward' => TRUE,
      'carry_forward_expiration_duration' => 4,
      'carry_forward_expiration_unit' => AbsenceType::EXPIRATION_UNIT_MONTHS
    ]);
    $this->assertFalse($absenceType->carryForwardNeverExpires());
  }

  public function testCarryForwardNeverExpiresShouldBeNullIfTypeDoesAllowCarryForward() {
    $absenceType = AbsenceTypeFabricator::fabricate(['allow_carry_forward' => FALSE]);
    $this->assertNull($absenceType->carryForwardNeverExpires());
  }

  public function testGetEnabledAbsenceTypesShouldReturnAListOfEnabledAbsenceTypesOrderedByWeight() {
    $absenceType1 = AbsenceTypeFabricator::fabricate();
    $absenceType2 = AbsenceTypeFabricator::fabricate();
    $absenceType3 = AbsenceTypeFabricator::fabricate();

    // Let's change the types order
    $absenceType2 = $this->updateBasicType($absenceType2->id, ['weight' => 1]);
    $absenceType3 = $this->updateBasicType($absenceType3->id, ['weight' => 2]);
    $absenceType1 = $this->updateBasicType($absenceType1->id, ['weight' => 3]);

    $absenceTypes = AbsenceType::getEnabledAbsenceTypes();
    $this->assertCount(3, $absenceTypes);

    $this->assertEquals($absenceType2->id, $absenceTypes[0]->id);
    $this->assertEquals($absenceType2->title, $absenceTypes[0]->title);

    $this->assertEquals($absenceType3->id, $absenceTypes[1]->id);
    $this->assertEquals($absenceType3->title, $absenceTypes[1]->title);

    $this->assertEquals($absenceType1->id, $absenceTypes[2]->id);
    $this->assertEquals($absenceType1->title, $absenceTypes[2]->title);
  }

  public function testGetEnabledAbsenceTypesShouldNotIncludeDisabledTypes() {
    AbsenceTypeFabricator::fabricate(['is_active' => 1]);
    AbsenceTypeFabricator::fabricate(['is_active' => 0]);

    $absenceTypes = AbsenceType::getEnabledAbsenceTypes();
    $this->assertCount(1, $absenceTypes);
  }

  public function testGetAllWithMustTakePublicHolidayAsLeaveRequestShouldNotReturnDisabledAbsenceTypes() {
    $absenceType1 = AbsenceTypeFabricator::fabricate([
      'must_take_public_holiday_as_leave' => TRUE,
      'is_active' => FALSE
    ]);
    $absenceType2 = AbsenceTypeFabricator::fabricate([
      'must_take_public_holiday_as_leave' => TRUE,
      'is_active' => TRUE
    ]);
    $absenceType3 = AbsenceTypeFabricator::fabricate([
      'must_take_public_holiday_as_leave' => TRUE,
      'is_active' => TRUE
    ]);

    $absenceTypes = AbsenceType::getAllWithMustTakePublicHolidayAsLeaveRequest();
    $this->assertCount(2, $absenceTypes);
    $this->assertEquals($absenceTypes[0]->id, $absenceType2->id);
    $this->assertEquals($absenceTypes[1]->id, $absenceType3->id);
  }

  public function testGetAllWithMustTakePublicHolidayAsLeaveRequestShouldReturnEmptyIfThereIsNoSuchAbsenceType() {
    AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => FALSE]);

    $absenceTypes = AbsenceType::getAllWithMustTakePublicHolidayAsLeaveRequest();

    $this->assertEmpty($absenceTypes);
  }

  public function testItEnqueueAnUpdateWhenCreatingAnAbsenceTypeWithMustTakePublicHolidayAsLeave() {
    AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => TRUE]);

    $queue = PublicHolidayLeaveRequestUpdatesQueue::getQueue();
    $this->assertEquals(1, $queue->numberOfItems());

    $item = $queue->claimItem();
    $this->assertEquals(
      'CRM_HRLeaveAndAbsences_Queue_Task_UpdateAllPublicHolidayLeaveRequests',
      $item->data->callback[0]
    );
  }

  public function testItDoesntEnqueueAnUpdateWhenCreatingAnAbsenceTypeWithoutMustTakePublicHolidayAsLeave() {
    AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => FALSE]);

    $queue = PublicHolidayLeaveRequestUpdatesQueue::getQueue();
    $this->assertEquals(0, $queue->numberOfItems());
  }

  public function testItEnqueueAnUpdateWhenChangingTheMustTakePublicHolidayAsLeaveValueForAnAbsenceTypeFromFalseToTrue() {
    $absenceType = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => FALSE]);

    $this->updateBasicType($absenceType->id, ['must_take_public_holiday_as_leave' => TRUE]);

    $queue = PublicHolidayLeaveRequestUpdatesQueue::getQueue();
    $this->assertEquals(1, $queue->numberOfItems());
  }

  public function testItEnqueueAnUpdateWhenChangingTheMustTakePublicHolidayAsLeaveValueForAnAbsenceTypeFromTrueToFalse() {
    $absenceType = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => TRUE]);

    $queue = PublicHolidayLeaveRequestUpdatesQueue::getQueue();
    $this->assertEquals(1, $queue->numberOfItems());

    $this->updateBasicType($absenceType->id, ['must_take_public_holiday_as_leave' => FALSE]);

    $queue = PublicHolidayLeaveRequestUpdatesQueue::getQueue();
    $this->assertEquals(2, $queue->numberOfItems());
  }

  public function testItDoesntEnqueueAnUpdateWhenUpdatingAnAbsenceTypeWithoutChangingMustTakePublicHolidayAsLeave() {
    $absenceType = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => TRUE]);

    $queue = PublicHolidayLeaveRequestUpdatesQueue::getQueue();
    $this->assertEquals(1, $queue->numberOfItems());

    $this->updateBasicType($absenceType->id, ['title' => 'Other title']);

    $queue = PublicHolidayLeaveRequestUpdatesQueue::getQueue();
    $this->assertEquals(1, $queue->numberOfItems());
  }

  public function testItDoesntEnqueueAnUpdateWhenDeletingAnAbsenceTypeWithoutMustTakePublicHolidayAsLeave() {
    $absenceType = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => FALSE]);
    AbsenceType::del($absenceType->id);

    $queue = PublicHolidayLeaveRequestUpdatesQueue::getQueue();
    $this->assertEquals(0, $queue->numberOfItems());
  }

  public function testItShouldEnqueueAnUpdateWhenDeletingAnAbsenceTypeWithMustTakePublicHolidayAsLeave() {
    $absenceType = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => TRUE]);
    AbsenceType::del($absenceType->id);

    $queue = PublicHolidayLeaveRequestUpdatesQueue::getQueue();
    // The number is two because another update was added
    // when the absence type was created
    $this->assertEquals(2, $queue->numberOfItems());
  }

  private function updateBasicType($id, $params) {
    $params['id'] = $id;
    return AbsenceTypeFabricator::fabricate($params);
  }

  public function expirationUnitDataProvider() {
    $data = [
      [rand(3, PHP_INT_MAX), TRUE],
      [rand(3, PHP_INT_MAX), TRUE],
    ];
    $validOptions = array_keys(AbsenceType::getExpirationUnitOptions());
    foreach($validOptions as $option) {
      $data[] = [$option, FALSE];
    }
    return $data;
  }

  public function accrualExpirationUnitAndDurationDataProvider() {
    return [
      [AbsenceType::EXPIRATION_UNIT_DAYS, NULL, TRUE],
      [NULL, 10, TRUE],
      [AbsenceType::EXPIRATION_UNIT_MONTHS, 5, FALSE],
    ];
  }

  public function allowRequestCancelationDataProvider() {
    $data = [
      [rand(3, PHP_INT_MAX), TRUE],
      [rand(3, PHP_INT_MAX), TRUE],
    ];
    $validOptions = array_keys(AbsenceType::getRequestCancelationOptions());
    foreach($validOptions as $option) {
      $data[] = [$option, FALSE];
    }
    return $data;
  }

  public function carryForwardExpirationDateDataProvider() {
    return [
      [12, 12, FALSE],
      [1, 2, FALSE],
      [31, 1, FALSE],
      [30, 2, TRUE],
      [31, 4, TRUE],
      [77, 9, TRUE],
      [12, 31, TRUE],
    ];
  }

  public function testAbsenceTypeHasIsSickFlagAsFalseByDefault() {
    $params = [
      'title' => 'Title 1',
      'color' => '#000101',
      'default_entitlement' => 21,
      'allow_request_cancelation' => 1,
      'is_active' => 1,
      'calculation_unit' => $this->calculationUnitOptions['days']
    ];
    $absenceType = AbsenceTypeFabricator::fabricate($params);
    $this->assertEquals(0, $absenceType->is_sick);
  }

  public function testSetIsSickFlagForAbsenceType() {
    $params = [
      'title' => 'Title 1',
      'color' => '#000101',
      'default_entitlement' => 21,
      'allow_request_cancelation' => 1,
      'is_active' => 1,
      'is_sick' => 1,
      'calculation_unit' => $this->calculationUnitOptions['days'],
    ];
    $absenceType = AbsenceTypeFabricator::fabricate($params);
    $this->assertEquals(1, $absenceType->is_sick);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage This Absence Type does not allow Accruals Request
   */
  public function testCalculateToilExpiryDateWhenAbsenceTypeDoesNotAllowAccrualsRequest() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => FALSE,
      'is_active' => 1,
    ]);
    // date to calculate TOIL expiry for
    $date = new DateTime('2016-11-10');
    $absenceType->calculateToilExpiryDate($date);
  }

  public function testCalculateToilExpiryDateWhenAbsenceTypeAllowsAccrualsRequestAndNeverExpires() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => TRUE,
      'accrual_expiration_unit' => NULL,
      'accrual_expiration_duration' => NULL,
      'is_active' => 1,
    ]);
    // date to calculate TOIL expiry for
    $date = new DateTime('2016-11-10');
    $expiry = $absenceType->calculateToilExpiryDate($date);
    $this->assertNull($expiry);
  }

  public function testCalculateToilExpiryDateWhenAbsenceTypeAllowsAccrualsRequestAndExpiryDurationSet() {
    // Duration set in days
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => TRUE,
      'accrual_expiration_duration' => 10,
      'accrual_expiration_unit' => AbsenceType::EXPIRATION_UNIT_DAYS,
      'is_active' => 1,
    ]);
    // date to calculate TOIL expiry for
    $date = new DateTime('2016-11-10');
    $expiry = $absenceType->calculateToilExpiryDate($date);
    $this->assertEquals('2016-11-20', $expiry->format('Y-m-d'));

    // Duration set in months
    $absenceType2 = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 2',
      'allow_accruals_request' => TRUE,
      'accrual_expiration_duration' => 10,
      'accrual_expiration_unit' => AbsenceType::EXPIRATION_UNIT_MONTHS,
      'is_active' => 1,
    ]);
    // date to calculate TOIL expiry for
    $date = new DateTime('2016-11-10');
    $expiry = $absenceType2->calculateToilExpiryDate($date);
    $this->assertEquals('2017-09-10', $expiry->format('Y-m-d'));
  }

  public function testNonExpiredToilRequestsAreDeletedAndExpiredToilRequestsNotDeletedWhenToilIsDisabledForAbsenceType() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-1 days'),
      'end_date' => CRM_Utils_Date::processDate('+ 300days'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => TRUE,
      'max_leave_accrual' => 1,
    ]);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+3 days'),
      'to_date' => CRM_Utils_Date::processDate('+3 days'),
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+100 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], TRUE);

    $toilRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+1 day'),
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'toil_expiry_date' => CRM_Utils_Date::processDate('2016-12-10'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], TRUE);

    // assert the records exist first before updating absence type
    $balanceChange = new LeaveBalanceChange();
    $balanceChange->find();
    $this->assertEquals($balanceChange->N, 2);

    $leaveRequest = new LeaveRequest();
    $leaveRequest->find();
    $this->assertEquals($leaveRequest->N, 2);

    $leaveRequestDate = new LeaveRequestDate();
    $leaveRequestDate->find();
    $this->assertEquals($leaveRequestDate->N, 2);

    // disable TOIL
    AbsenceType::create([
      'id' => $absenceType->id,
      'title' => $absenceType->title,
      'allow_accruals_request' => FALSE,
      'color' => '#000000'
    ]);

    // confirm the balance change for the expired TOIL balance was not deleted
    $balanceChange = new LeaveBalanceChange();
    $balanceChange->find(TRUE);
    $this->assertEquals(1, $balanceChange->N);
    $toilRequestBalanceChange = $this->findToilRequestMainBalanceChange($toilRequest2->id);
    $this->assertEquals($toilRequestBalanceChange->id, $balanceChange->id);

    // confirm the leave request for the expired TOIL balance was not deleted
    $leaveRequest = new LeaveRequest();
    $leaveRequest->find();
    $this->assertEquals($leaveRequest->N, 1);
    $leaveRequest->fetch();
    $this->assertEquals($leaveRequest->id, $toilRequest2->id);

    // confirm the leave request dates for the expired TOIL balance was not deleted
    $leaveRequestDate = new LeaveRequestDate();
    $leaveRequestDate->find();
    $this->assertEquals($leaveRequestDate->N, 1);
    $leaveRequestDate->fetch();
    $date = date('Y-m-d', strtotime('+1 day'));
    $this->assertEquals($leaveRequestDate->date, $date);
  }

  public function testGetEnabledSicknessAbsenceTypes() {
    $absenceType1 = AbsenceTypeFabricator::fabricate(['is_sick' => 1]);
    $absenceType2 = AbsenceTypeFabricator::fabricate();
    $absenceType3 = AbsenceTypeFabricator::fabricate();

    $absenceTypes = AbsenceType::getEnabledSicknessAbsenceTypes();
    $this->assertCount(1, $absenceTypes);

    $this->assertEquals($absenceType1->id, $absenceTypes[0]->id);
    $this->assertEquals($absenceType1->title, $absenceTypes[0]->title);
  }

  public function testGetEnabledSicknessAbsenceTypesReturnsOnlyEnabledTypes() {
    $absenceType1 = AbsenceTypeFabricator::fabricate(['is_sick' => 1]);
    $absenceType2 = AbsenceTypeFabricator::fabricate(['is_sick' => 1, 'is_active' => 0]);
    $absenceType3 = AbsenceTypeFabricator::fabricate(['is_sick' => 1]);

    $absenceTypes = AbsenceType::getEnabledSicknessAbsenceTypes();
    $this->assertCount(2, $absenceTypes);

    $this->assertEquals($absenceType1->id, $absenceTypes[0]->id);
    $this->assertEquals($absenceType1->title, $absenceTypes[0]->title);

    $this->assertEquals($absenceType3->id, $absenceTypes[1]->id);
    $this->assertEquals($absenceType3->title, $absenceTypes[1]->title);
  }

  public function testNoExceptionIsThrownWhenUpdatingAnAbsenceTypeWithoutChangingTheTitle() {
    $params = ['title' => 'Type 1'];
    $absenceType = AbsenceTypeFabricator::fabricate($params);

    // update the absence type
    $params['id'] = $absenceType->id;
    $params['default_entitlement'] = 50;

    try{
      $absenceType = AbsenceTypeFabricator::fabricate($params);
      $this->assertEquals($absenceType->default_entitlement, $params['default_entitlement']);
    } catch(CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException $e) {
      $this->fail($e->getMessage());
    }
  }

  public function testExceptionIsThrownWhenUpdatingAnAbsenceTypeWithTitleOfAnotherExistingAbsenceType() {
    $params1 = ['title' => 'Type 1'];
    $absenceType1 = AbsenceTypeFabricator::fabricate($params1);

    $params2 = ['title' => 'Type 2'];
    $absenceType2 = AbsenceTypeFabricator::fabricate($params2);

    // update the second absence type with the title of the first type
    $params['id'] = $absenceType2->id;
    $params['title'] = $params1['title'];

    $this->setExpectedException(
      CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException::class,
      'Absence Type with same title already exists!'
    );

    AbsenceTypeFabricator::fabricate($params);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage You cannot add public holiday to entitlement when Absence Type calculation unit is in Hours
   */
  public function testPublicHolidayEntitlementCannotBeAddedWhenLeaveIsCalculatedInHours() {
    AbsenceTypeFabricator::fabricate([
      'add_public_holiday_to_entitlement' => TRUE,
      'calculation_unit' => $this->calculationUnitOptions['hours']
    ]);
  }

  public function testCalculationUnitCannotBeChangedWhenAbsenceTypeIsInUse() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'calculation_unit' => $this->calculationUnitOptions['hours']
    ]);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-02'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-02'),
    ]);

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException',
      'The Calculation unit cannot be changed because the Absence Type is In Use!'
    );

    AbsenceTypeFabricator::fabricate([
      'id' => $absenceType->id,
      'calculation_unit' => $this->calculationUnitOptions['days']
    ]);
  }

  public function testCalculationUnitCanBeChangedWhenAbsenceTypeIsInNotInUse() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'calculation_unit' => $this->calculationUnitOptions['hours']
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'id' => $absenceType->id,
      'calculation_unit' => $this->calculationUnitOptions['days']
    ]);

    $this->assertEquals($absenceType->calculation_unit, $this->calculationUnitOptions['days']);
  }

  public function testIsCalculationUnitInHoursReturnsTrueWhenCalculationUnitIsInHours() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'calculation_unit' => $this->calculationUnitOptions['hours']
    ]);

    $this->assertTrue($absenceType->isCalculationUnitInHours());
  }

  public function testIsCalculationUnitInHoursReturnsFalseWhenCalculationUnitIsNotInHours() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'calculation_unit' => $this->calculationUnitOptions['days']
    ]);

    $this->assertFalse($absenceType->isCalculationUnitInHours());
  }

  public function testGetCategories() {
    $categoryOptions = [
      '1' => 'Leave',
      '2' => 'Sickness',
      '3' => 'TOIL',
      '4' => 'Custom'
    ];
    $categories = AbsenceType::getCategories();

    $this->assertEquals($categories, $categoryOptions);
  }
}
