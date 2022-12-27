(function (_ref, $) {
  Drupal.behaviors.myHeaderMenu = {
    attach: function (context, settings) {
      this.ButtonToTop();
    },ButtonToTop: function () {
      jQuery("document").ready(function($){

        let link = $('.button-to-top');

        $(window).scroll(function () {
          if ($(this).scrollTop() >= 600) {
            link.addClass("active");
          } else {
            link.removeClass("active");
          }
        });

      });
    }
  };
})(Drupal, jQuery);
