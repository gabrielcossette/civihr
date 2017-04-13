define([
  'common/angular',
  'common/angularBootstrap',
  'common/text-angular',
  'common/directives/loading',
  'common/models/option-group',
  'common/modules/dialog',
  'common/services/angular-date/date-format',
  'leave-absences/shared/ui-router',
  'leave-absences/my-leave/modules/config',
  'leave-absences/my-leave/components/my-leave',
  'leave-absences/my-leave/components/my-leave-calendar',
  'leave-absences/my-leave/components/my-leave-report',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/directives/leave-request-popup',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/models/calendar-model',
  'leave-absences/shared/models/absence-period-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/public-holiday-model',
  'leave-absences/shared/modules/shared-settings',
], function (angular) {
  angular.module('my-leave', [
    'ngResource',
    'ngAnimate',
    'ui.router',
    'ui.bootstrap',
    'textAngular',
    'common.angularDate',
    'common.dialog',
    'common.directives',
    'common.models',
    'my-leave.config',
    'my-leave.components',
    'leave-absences.directives',
    'leave-absences.models',
    'leave-absences.settings'
  ])
  .run(['$log', '$rootScope', 'shared-settings', 'settings', function ($log, $rootScope, sharedSettings, settings) {
    $log.debug('app.run');

    $rootScope.pathTpl = sharedSettings.pathTpl;
    $rootScope.settings = settings;
  }]);

  return angular;
});