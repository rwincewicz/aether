(function ($) {

   /**
   * ImgSizer
   * http://unstoppablerobotninja.com/entry/fluid-images/
   */
  Drupal.behaviors.aetherImgSizer = {
    attach: function(context, settings) {
      $(window).load(function () {
        imgSizer.collate($('img'));
      });
    }
  };

})(jQuery);