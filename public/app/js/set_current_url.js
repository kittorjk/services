$(document).ready(function() {
  $.post('/set_current_url', { url: window.location.href }, function() {});
});
