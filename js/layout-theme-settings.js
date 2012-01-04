$ = jQuery.noConflict(); // Make sure jQuery owns $ here
/*
 * Reduce complexity and clutter of form
 */
$(function() {
  // $('#edit-layout .form-radios').each( function() {
  //   if ($(this).find('input.form-radio[value=4]').attr('checked')) {
  //     console.log($(this));
  //     $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar"]').hide();
  //   }
  //   if ($(this).find('input.form-radio[value=5]').attr('checked')) {
  //     $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar-sec"]').hide();
  //   }
  // });


  $('#edit-layout-options-1').click( function() {
    if ($(this).attr('checked')) {
      $("#edit-layout.ui-tabs .ui-tabs-nav li:not(:first-child), .form-item-layout-options-query1").fadeIn();
    } else {
      $("#edit-layout.ui-tabs .ui-tabs-nav li:not(:first-child), .form-item-layout-options-query1").fadeOut();
    }
  });

  $('#edit-layout-options-2').click( function() {
    if ($(this).attr('checked')) {
      $('div[class^="form-item form-type-select form-item-nav-link-width"]').fadeIn();
    } else {
      $('div[class^="form-item form-type-select form-item-nav-link-width"]').fadeOut();
    }
  });


  $('#edit-layout .form-radios').click( function() {
    if ($(this).find('input.form-radio[value=4]').attr('checked')) {
      $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar"]').fadeOut();
    }
    if ($(this).find('input.form-radio[value=5]').attr('checked')) {
      $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar-sec"]').fadeOut();
      $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar-fir"]').fadeIn();
    }
    if ($(this).find('input.form-radio[value|=1]').attr('checked')) {
      $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar"]').fadeIn();
    }
    if ($(this).find('input.form-radio[value|=2]').attr('checked')) {
      $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar"]').fadeIn();
    }
    if ($(this).find('input.form-radio[value|=3]').attr('checked')) {
      $(this).parent().parent().find('div[class^="form-item form-type-select form-item-sidebar"]').fadeIn();
    }
  });


});
