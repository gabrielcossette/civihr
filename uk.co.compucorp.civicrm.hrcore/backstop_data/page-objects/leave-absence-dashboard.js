var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = page.extend({
  /**
   * Opens one of the leave absence dashboard tabs
   *
   * @param  {String} tabId
   * @return {Object} resolves with the tab page object
   */
  openTab: function (tabId) {
    var chromy = this.chromy;
    var tab = require('./tabs/' + tabId).init(chromy, false);

    return new Promise(function (resolve) {
      chromy.click('[ui-sref="' + tab.tabUiSref + '"]');
      chromy.waitUntilVisible(tab.readySelector);
      chromy.wait(500);

      resolve(tab);
    });
  }
});
