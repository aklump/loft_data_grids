(function ($) {
  $(document).ready(function () {

    // Arrow controls.
    var links = []
    links[37] = $('a.prev').first();
    links[39] = $('a.next').first();
    // links[38] = $('a.index').first();

    $(document).keyup(function (event) {
      var keyCode = event.keyCode;
      var href = links[keyCode] && links[keyCode].attr('href');
      if (href) {
        window.location = href;
      }
    });

  });

})(jQuery);
