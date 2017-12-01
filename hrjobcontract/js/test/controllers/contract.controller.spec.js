/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'mocks/data/contract',
  'common/angularMocks',
  'job-contract/modules/job-contract.module'
], function (_, moment, contractMock) {
  'use strict';

  describe('ContractController', function () {
    var $controller, $httpBackend, $modal, $q, $rootScope, $scope, $window,
      AbsenceType, contractService, utilsService;
    var calculationUnitsMock = [{ value: 1, name: 'days' }, { value: 2, name: 'hours' }];

    // Populate contract mock leaves with values
    contractMock.contractEntity.leave = contractMock.contractLeaves.values;

    beforeEach(module('job-contract', 'job-contract.templates', function ($provide) {
      $window = { location: jasmine.createSpyObj('location', ['assign']) };

      $provide.value('$window', $window);
    }));

    beforeEach(inject(function (_$controller_, _$rootScope_, _$uibModal_, _$q_,
    _$httpBackend_, _$window_, _AbsenceType_, _contractService_, _utilsService_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $q = _$q_;
      $httpBackend = _$httpBackend_;
      $modal = _$uibModal_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      $window = _$window_;
      AbsenceType = _AbsenceType_;
      contractService = _contractService_;
      utilsService = _utilsService_;

      mockBackendCalls();
      spyOn(AbsenceType, 'all').and.callThrough();
      spyOn(AbsenceType, 'loadCalculationUnits').and.callThrough();
      spyOn(contractService, 'fullDetails').and.callThrough();
      makeController();
    }));

    describe('when loads', function () {
      beforeEach(function () {
        $httpBackend.flush();
        $rootScope.$digest();
      });

      it('retrieves contract full details', function () {
        expect(contractService.fullDetails).toHaveBeenCalled();
      });
    });

    describe('Update contract based on new end date', function () {
      describe('When end date is past', function () {
        beforeEach(function () {
          var date = moment().day(-7); // Seven days ago
          createModalSpy(date);
          $scope.modalContract('edit');
          $rootScope.$digest();
        });

        it('Marks the contract as past', function () {
          expect($scope.$parent.contract.is_current).toBe('0');
        });
      });

      describe('When end date is today', function () {
        beforeEach(function () {
          var date = moment();
          createModalSpy(date);
          $scope.modalContract('edit');
          $rootScope.$digest();
        });

        it('Marks the contract as current', function () {
          expect($scope.$parent.contract.is_current).toBe('1');
        });
      });

      describe('When date is future', function () {
        beforeEach(function () {
          var date = moment().day(7); // Seven days from now
          createModalSpy(date);
          $scope.modalContract('edit');
          $rootScope.$digest();
        });

        it('Marks the contract as current', function () {
          expect($scope.$parent.contract.is_current).toBe('1');
        });
      });

      describe('When end date is empty', function () {
        beforeEach(function () {
          var date = ''; //  end date empty
          createModalSpy(date);
          $scope.modalContract('edit');
          $rootScope.$digest();
        });

        it('Marks the contract as current', function () {
          expect($scope.$parent.contract.is_current).toBe('1');
        });
      });

      describe('after updating the contract', function () {
        var url;

        beforeEach(function () {
          url = utilsService.getManageEntitlementsPageURL($scope.contract.contact_id);

          createModalSpy();
          $scope.modalContract('edit');
          $rootScope.$digest();
        });

        it('changes the window location to the Manage Entitlements for the contact', function () {
          expect($window.location.assign).toHaveBeenCalledWith(url);
        });
      });
    });

    function makeController () {
      $scope = $rootScope.$new();

      $scope.contract = {
        id: '1',
        contact_id: '04',
        deleted: '0',
        is_current: '1',
        is_primary: '1'
      };
      $scope.details = {};
      $scope.pay = {};
      $scope.hour = {};
      $scope.health = {};
      $scope.leave = [];
      $scope.$parent.contract = {
        id: '1',
        contact_id: '84',
        deleted: '0',
        is_current: '0',
        is_primary: '1'
      };
      $scope.pension = {};
      $scope.$parent.contractCurrent = [];
      $scope.$parent.contractPast = [];

      $controller('ContractController', {
        $scope: $scope,
        $modal: $modal
      });
    }

    function createModalSpy (newEndDate) {
      spyOn($modal, 'open').and.callFake(function () {
        return {
          result: $q.resolve({
            'files': false,
            'health': {},
            'contract': {
              'id': '48',
              'contact_id': '84',
              'is_primary': '1',
              'deleted': '0'
            },
            'pay': {},
            'hour': {},
            'leave': [],
            'details': {
              'period_end_date': newEndDate
            },
            'pension': {}
          })
        };
      });
    }

    /**
     * Mocks back-end API calls
     */
    function mockBackendCalls () {
      $httpBackend.whenGET(/action=getfulldetails&entity=HRJobContract/).respond(contractMock.contractEntity);
      $httpBackend.whenGET(/action=getcurrentcontract&entity=HRJobContract/).respond({ 'values': [] });
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(contractMock.contract);
      $httpBackend.whenGET(/action=getsingle&entity=HRJobContractRevision/).respond({ 'values': [] });
      $httpBackend.whenGET(/hrjobcontract\/file\/list/).respond({ 'values': [] });
      // @NOTE This is a temporary solution until we can import mocks
      // from other extensions such as Leave and Absence extension
      $httpBackend.whenGET(/action=get&entity=AbsenceType/).respond({ 'values':
        _.map(contractMock.contractEntity.leave, function (leave, index) {
          return { id: leave.leave_type, calculation_unit: _.sample(calculationUnitsMock).value };
        })
      });
      $httpBackend.whenGET(/action=get&entity=OptionValue/).respond({ 'values': calculationUnitsMock });
    }
  });
});
