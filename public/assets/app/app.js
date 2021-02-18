
$(document).tooltip()

// JQuery fix for empty body on JSON response
$.ajaxSetup({
  converters: {
    'text json' : function(response) {
      return (response === '') ? null : JSON.parse(response);
    },
  },
});


$.fn.extend({
  setMsg: function (message, field, result = true) {
    if (message === '') {
      return
    }

    let state = (result === true ? 'highlight' : 'error')
    let icon = (result === true ? 'info' : 'alert')

    this.html(
      '<div class="ui-state-' + state + ' ui-corner-all" style="padding: 10px; margin-top: 20px; margin-bottom: 20px;">' +
      '<p><span class="ui-icon ui-icon-' + icon + '" style="float: left; margin-right: .3em;"></span> ' +
      '<b>' + field + '</b>&nbsp;&nbsp; ' + message + '</p>' +
      '</div>')
  }
})