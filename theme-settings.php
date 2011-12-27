<?php
// Form override fo theme settings
function aether_form_system_theme_settings_alter(&$form, $form_state) {

  global $base_path;
  $subject_theme = arg(count(arg()) - 1);
  $aether_theme_path = drupal_get_path('theme', 'aether') . '/';
  $theme_path = drupal_get_path('theme', $subject_theme) . '/';

  drupal_add_css('themes/seven/vertical-tabs.css', array('group' => CSS_THEME, 'weight' => 9));

  $base_theme_version = 'v0.5alpha.4';

  $header  = '<div class="themesettings-header">';
  $header .= '  <h3>Aether Configuration</h3>';
  $header .= '  <a href="http://www.github.com/krisbulman/aether" title="Hosted on GitHub"><img class="configurator-logo" src="' . $base_path . drupal_get_path('theme', 'aether') . "/" . 'logo.png" /></a>';
  $header .= '  <h2>' . arg(count(arg()) - 1) . '</h2>';
  $header .= '  <h4>' . $base_theme_version . '</h4>';
  $header .= '</div>';

  $form['aether_settings'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => 0,
    '#prefix' => $header,
  );

  $form['aether_settings']['polyfills'] = array(
    '#title' => t('Polyfills'),
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#weight' => 10,
  );

  $form['aether_settings']['polyfills']['aether_html5_respond_meta'] = array(
    '#type'          => 'checkboxes',
    '#title'         => t('Add HTML5 and responsive scripts and meta tags to every page.'),
    '#default_value' => theme_get_setting('aether_html5_respond_meta'),
    '#options'       => array(
                          'respond' => t('Add Respond.js JavaScript to add basic CSS3 media query support to IE 6-8.'),
                          'html5' => t('Add HTML5 shim JavaScript to add support to IE 6-8.'),
                          'meta' => t('Add meta tags to support responsive design on mobile devices.'),
                          'selectivizr' => t('Add pseudo class support to IE6-8.'),
                          'imgsizer' => t('Add imgsizer fluid image support to IE6-8.'),
                        ),
    '#description'   => t('IE 6-8 require a JavaScript polyfill solution to add basic support of HTML5 and CSS3 media queries. If you prefer to use another polyfill solution, such as <a href="!link">Modernizr</a>, you can disable these options. Mobile devices require a few meta tags for responsive designs.', array('!link' => 'http://www.modernizr.com/')),
  );

  $form['aether_settings']['drupal'] = array(
    '#title' => t('Drupal core options / styles'),
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#weight' => 11,
  );

  // $form['aether_settings']['drupal']['theme_settings'] = $form['theme_settings'];
  // $form['aether_settings']['drupal']['logo'] = $form['logo'];
  // $form['aether_settings']['drupal']['favicon'] = $form['favicon'];
  // unset($form['theme_settings']);
  // unset($form['logo']);
  // unset($form['favicon']);

  $form['aether_settings']['drupal']['aether_breadcrumb'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Breadcrumb settings'),
    '#attributes'    => array('id' => 'aether-breadcrumb'),
  );
  $form['aether_settings']['drupal']['aether_breadcrumb']['aether_breadcrumb'] = array(
    '#type'          => 'select',
    '#title'         => t('Display breadcrumb'),
    '#default_value' => theme_get_setting('aether_breadcrumb'),
    '#options'       => array(
                          'yes'   => t('Yes'),
                          'admin' => t('Only in admin section'),
                          'no'    => t('No'),
                        ),
  );
  $form['aether_settings']['drupal']['aether_breadcrumb']['aether_breadcrumb_separator'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Breadcrumb separator'),
    '#description'   => t('Text only. Don’t forget to include spaces.'),
    '#default_value' => theme_get_setting('aether_breadcrumb_separator'),
    '#size'          => 5,
    '#maxlength'     => 10,
    '#prefix'        => '<div id="div-aether-breadcrumb-collapse">', // jquery hook to show/hide optional widgets
  );
  $form['aether_settings']['drupal']['aether_breadcrumb']['aether_breadcrumb_home'] = array(
    '#type'          => 'checkbox',
    '#title'         => t('Show home page link in breadcrumb'),
    '#default_value' => theme_get_setting('aether_breadcrumb_home'),
  );
  $form['aether_settings']['drupal']['aether_breadcrumb']['aether_breadcrumb_trailing'] = array(
    '#type'          => 'checkbox',
    '#title'         => t('Append a separator to the end of the breadcrumb'),
    '#default_value' => theme_get_setting('aether_breadcrumb_trailing'),
    '#description'   => t('Useful when the breadcrumb is placed just before the title.'),
  );
  $form['aether_settings']['drupal']['aether_breadcrumb']['aether_breadcrumb_title'] = array(
    '#type'          => 'checkbox',
    '#title'         => t('Append the content title to the end of the breadcrumb'),
    '#default_value' => theme_get_setting('aether_breadcrumb_title'),
    '#description'   => t('Useful when the breadcrumb is not placed just before the title.'),
    '#suffix'        => '</div>', // #div-aether-breadcrumb
  );

 $form['aether_settings']['themedev'] = array(
    '#title' => t('Debugging'),
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#weight' => 12,
  );

  $form['aether_settings']['themedev']['aether_skip_link_anchor'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Anchor ID for the “skip link”'),
    '#default_value' => theme_get_setting('aether_skip_link_anchor'),
    '#field_prefix'  => '#',
    '#description'   => t('Specify the HTML ID of the element that the accessible-but-hidden “skip link” should link to. (<a href="!link">Read more about skip links</a>.)', array('!link' => 'http://drupal.org/node/467976')),
  );
  $form['aether_settings']['themedev']['wireframe_mode'] = array(
    '#type' => 'checkbox',
    '#title' =>  t('Wireframe Mode - Display borders around main layout elements'),
    '#description'   => t('<a href="!link">Wireframes</a> are useful when prototyping a website.', array('!link' => 'http://www.boxesandarrows.com/view/html_wireframes_and_prototypes_all_gain_and_no_pain')),
    '#default_value' => theme_get_setting('wireframe_mode'),
  );
  $form['aether_settings']['themedev']['clear_registry'] = array(
    '#type' => 'checkbox',
    '#title' =>  t('Rebuild theme registry on every page.'),
    '#description'   =>t('During theme development, it can be very useful to continuously <a href="!link">rebuild the theme registry</a>. WARNING: this is a huge performance penalty and must be turned off on production websites.', array('!link' => 'http://drupal.org/node/173880#theme-registry')),
    '#default_value' => theme_get_setting('clear_registry'),
  );

}
