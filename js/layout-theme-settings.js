$ = jQuery.noConflict(); // Make sure jQuery owns $ here
/*
 * Reduce complexity and clutter of form
 */
// $(function() {
//   $('#edit-layout .form-radios').each( function() {
//     if ($(this).find('input.form-radio[value=4]').attr('checked')) {
//       console.log($(this));
//       $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar"]').hide();
//     }
//     if ($(this).find('input.form-radio[value=5]').attr('checked')) {
//       $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar-sec"]').hide();
//     }
//   });


  $('#edit-responsive-enable').click( function() {
    if ($(this).attr('checked')) {
      $("#edit-layout.ui-tabs .ui-tabs-nav li:not(:last-child), .form-item-layout-query1").fadeIn();
    } else {
      $("#edit-layout.ui-tabs .ui-tabs-nav li:not(:last-child), .form-item-layout-query1").fadeOut();
    }
  });

  // $('#edit-layout .form-radios').click( function() {
  //   if ($(this).find('input.form-radio[value=4]').attr('checked')) {
  //     $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar"]').fadeOut();
  //   }
  //   if ($(this).find('input.form-radio[value=5]').attr('checked')) {
  //     $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar-sec"]').fadeOut();
  //     $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar-fir"]').fadeIn();
  //   }
  //   if ($(this).find('input.form-radio[value|=1]').attr('checked')) {
  //     $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar"]').fadeIn();
  //   }
  //   if ($(this).find('input.form-radio[value|=2]').attr('checked')) {
  //     $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar"]').fadeIn();
  //   }
  //   if ($(this).find('input.form-radio[value|=3]').attr('checked')) {
  //     $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar"]').fadeIn();
  //   }
  // });

  // /**
  //  * Use formUpdated event from Drupal core form.js
  //  */
  // $('input[id^=edit-layout-width]').bind('formUpdated', function() {
  //   var width = $(this).val();
  //   if (/^[0-9]+%$/.test(width)) {
  //     $(this).parent().parent().find('div[class^="form-item form-type-textfield form-item-layout-max-width"]').fadeIn();
  //     console.log('percentage');
  //   } else {
  //     $(this).parent().parent().find('div[class^="form-item form-type-textfield form-item-layout-max-width"]').fadeOut();
  //     console.log('not percentage');
  //   }
  // });
});
