
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


let MyLibrary = {}
MyLibrary.withTooltips = function () {
  $(document).tooltip()
}
MyLibrary.import = {
  actions: [],
  addAction: function(button, action) {
    this.actions.push({button: button, action: action})
  },
  render: function(element) {
    let $result = $('<div>')
    this.actions.forEach(function(action) {
      let $button = $(`<button>${action.button.title}</button>`)
      $button.attr('title', action.button.description)
      $button.button()
      $button.click(function(event) {
          action.action(event, $result)
        });
      $(element).append($button)
      $(element).append('<br>').append('<br>')
    })
    $(element).append($result)
  }
}