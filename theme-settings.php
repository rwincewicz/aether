<?php

// Form override fo theme settings
function aether_form_system_theme_settings_alter(&$form, $form_state) {

  global $base_path;
  /**
   * @ code
   * a bug in D7 causes the theme to load twice, if this file is loaded a
   * second time we return immediately to prevent further complications.
   */
  global $aether_altered, $base_path;
  if ($aether_altered) return;
  $aether_altered = TRUE;

  // Get theme name from url (admin/.../theme_name)
  $theme_name = arg(count(arg()) - 1);

  // Get default theme settings from .info file
  $theme_data = list_themes();   // get data for all themes
  $defaults = ($theme_name && isset($theme_data[$theme_name]->info['settings'])) ? $theme_data[$theme_name]->info['settings'] : array();

  $subject_theme = arg(count(arg()) - 1);
  $aether_theme_path = drupal_get_path('theme', 'aether') . '/';
  $theme_path = drupal_get_path('theme', $subject_theme) . '/';

  drupal_add_library('system', 'ui.tabs');
  drupal_add_js('jQuery(function () {jQuery("#edit-layout").fieldset_tabs();});', 'inline');
  drupal_add_js($aether_theme_path . "js/jquery.autotabs.js", 'file');
  drupal_add_js($aether_theme_path . "js/layout-theme-settings.js", 'file');
  drupal_add_css($aether_theme_path . 'css/layout/layout-config.css', array('group' => CSS_THEME, 'weight' => 10));
  drupal_add_css('themes/seven/vertical-tabs.css', array('group' => CSS_THEME, 'weight' => 9));
  drupal_add_css('themes/seven/jquery.ui.theme.css', array('group' => CSS_THEME, 'weight' => 9));

  $base_theme_version = 'v0.5alpha.6';

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

  $form['aether_settings']['layout'] = array(
    '#title' => t('Aether Layout'),
    '#type' => 'fieldset',
  );

  $form['aether_settings']['layout']['theme_grid_config']['layout_options'] = array(
    '#type'          => 'checkboxes',
    '#title'         => t('Layout options'),
    '#default_value' => theme_get_setting('layout_options'),
    '#options'       => array(
      '1' => t("Enable additional device media queries that aid in making your design !responsive. If you wish to use a fixed width desktop layout, uncheck this option. WARNING: if you disable media queries, you will need to also disable the responsive meta and polyfills.", array('!responsive' => l(t('responsive'), 'http://www.alistapart.com/articles/responsive-web-design/'))),
      '2' => t("Enable navigation to grid. This will align each link in the navigation bar to the current grid."),
    ),
    '#description'   => t('Enable or disable various grid and responsive layout options'),
  );

  if (in_array('1', theme_get_setting('layout_options'))) {
    $media = array();
    $media_queries = theme_get_setting('media_queries');
    if ($media_queries && is_numeric($media_queries)) {
      for ($i = 1; $i <= $media_queries; $i++) {
        $media[] = 'Media' . $i;
      }
    }
  }
  else {
    $media = array(t('Default'));
    $media_queries = 1;
  }

  for ($media_count = 1; $media_count <= $media_queries; $media_count++) {
    $medium = $media[$media_count-1];

    $form['aether_settings']['layout']["media{$media_count}"] = array(
      '#title' => t('@media', array('@media' => $medium)),
      '#type' => 'fieldset',
    );

    // Sidebar layout
    $form['aether_settings']['layout']["media{$media_count}"]["sidebar_layout{$media_count}"] = array(
      '#type'          => 'radios',
      '#title'         => t('@media Sidebar layout', array('@media' => $medium)),
      '#default_value' => (theme_get_setting("sidebar_layout{$media_count}")) ? theme_get_setting("sidebar_layout{$media_count}") : theme_get_setting("sidebar_layout{$media_count}"),
      '#options'       => array(
        1 => t('Split sidebars'),
        2 => t('Both sidebars last'),
        3 => t('Both sidebars first'),
        4 => t('Full width'),
        5 => t('One Sidebar right, one bottom'),
      ),
    );

    $form['aether_settings']['layout']["media{$media_count}"]["sidebar_layout{$media_count}"]['#options'][$defaults["sidebar_layout{$media_count}"]] .= t(' - Theme Default');

    // Grid type
    // Generate grid type options
    $grid_options = array();
    if (isset($defaults["theme_grid_options{$media_count}"])) {
      foreach ($defaults["theme_grid_options{$media_count}"] as $grid_option) {
        $grid_type = t('grid') . ' [' . substr($grid_option, 7) . 'px]';
        $grid_options[$grid_option] = (int)substr($grid_option, 4, 2) . t(' column ') . $grid_type;
      }
    }
    $form['aether_settings']['layout']["media{$media_count}"]["theme_grid{$media_count}"] = array(
      '#type'          => 'select',
      '#title'         => t('Select a grid layout for your theme'),
      '#default_value' => (theme_get_setting("theme_grid{$media_count}")) ? theme_get_setting("theme_grid{$media_count}") : theme_get_setting("theme_grid{$media_count}"),
      '#options'       => $grid_options,
    );
    $form['aether_settings']['layout']["media{$media_count}"]["theme_grid{$media_count}"]['#options'][$defaults["theme_grid{$media_count}"]] .= t(' - Theme Default');


    // Header layout
    $form['aether_settings']['layout']["media{$media_count}"]["hgroup_layout{$media_count}"] = array(
      '#type'          => 'radios',
      '#title'         => t('@media Header layout options', array('@media' => $medium)),
      '#default_value' => (theme_get_setting("hgroup_layout{$media_count}")) ? theme_get_setting("hgroup_layout{$media_count}") : theme_get_setting("hgroup_layout{$media_count}"),
      '#options'       => array(
        1 => t('Logo, sitename and secondary links inline'),
        2 => t('logo and sitename inline, secondary links underneath'),
        3 => t('Full width'),
      ),
    );

    // Calculate Header and nav link width options
    $grid_width = (int)substr(theme_get_setting("theme_grid{$media_count}"), 4, 2);
    $grid_type = substr(theme_get_setting("theme_grid{$media_count}"), 7);
    $header_width_options = array();
    for ($i = 1; $i <= floor($grid_width - 2); $i++) {
      $grid_units = $i . (($i == 1) ? t(' grid unit: ') : t(' grid units: '));
      $header_width_options[$i] = $grid_units . (($i * ((int)$grid_type / $grid_width)) . 'px');
    }

    // Header group first width
    $form['aether_settings']['layout']["media{$media_count}"]["hgroup_first_width{$media_count}"] = array(
      '#type'          => 'select',
      '#title'         => t('Select the number of columns for the 1st group in the header region (contains logo)'),
      '#default_value' => (theme_get_setting("hgroup_first{$media_count}")) ? theme_get_setting("hgroup_first_width{$media_count}") : theme_get_setting("hgroup_first_width{$media_count}"),
      '#options'       => $header_width_options,
    );
    $form['aether_settings']['layout']["media{$media_count}"]["hgroup_first_width{$media_count}"]['#options'][$defaults["hgroup_first_width{$media_count}"]] .= t(' - Theme Default');
    // Header group second width
    $form['aether_settings']['layout']["media{$media_count}"]["hgroup_third_width{$media_count}"] = array(
      '#type'          => 'select',
      '#title'         => t('Select the number of columns for the 2nd group in the header region (contains secondary links)'),
      '#default_value' => (theme_get_setting("hgroup_third_width{$media_count}")) ? theme_get_setting("hgroup_third_width{$media_count}") : theme_get_setting("hgroup_third_width{$media_count}"),
      '#options'       => $header_width_options,
    );
    $form['aether_settings']['layout']["media{$media_count}"]["hgroup_third_width{$media_count}"]['#options'][$defaults["hgroup_third_width{$media_count}"]] .= t(' - Theme Default');
    // Navigation link width
    $form['aether_settings']['layout']["media{$media_count}"]["nav_link_width{$media_count}"] = array(
      '#type'          => 'select',
      '#title'         => t('Select the number of columns for each link in the navigation bar'),
      '#default_value' => (theme_get_setting("nav_link_width{$media_count}")) ? theme_get_setting("nav_link_width{$media_count}") : theme_get_setting("nav_link_width{$media_count}"),
      '#options'       => $header_width_options,
    );
    $form['aether_settings']['layout']["media{$media_count}"]["nav_link_width{$media_count}"]['#options'][$defaults["nav_link_width{$media_count}"]] .= t(' - Theme Default');

    // Calculate sidebar width options
    $width_options = array();
    for ($i = 1; $i <= floor($grid_width / 2); $i++) {
      $grid_units = $i . (($i == 1) ? t(' grid unit: ') : t(' grid units: '));
      $width_options[$i] = $grid_units . (($i * ((int)$grid_type / $grid_width)) . 'px');
    }

    // Sidebar first width
    $form['aether_settings']['layout']["media{$media_count}"]["sidebar_first_width{$media_count}"] = array(
      '#type'          => 'select',
      '#title'         => t('Select a different width for your first sidebar'),
      '#default_value' => (theme_get_setting("sidebar_first_width{$media_count}")) ? theme_get_setting("sidebar_first_width{$media_count}") : theme_get_setting("sidebar_first_width{$media_count}"),
      '#options'       => $width_options,
    );
    $form['aether_settings']['layout']["media{$media_count}"]["sidebar_first_width{$media_count}"]['#options'][$defaults["sidebar_first_width{$media_count}"]] .= t(' - Theme Default');
    // Sidebar last width
    $form['aether_settings']['layout']["media{$media_count}"]["sidebar_second_width{$media_count}"] = array(
      '#type'          => 'select',
      '#title'         => t('Select a different width for your second sidebar'),
      '#default_value' => (theme_get_setting("sidebar_second_width{$media_count}")) ? theme_get_setting("sidebar_second_width{$media_count}") : theme_get_setting("sidebar_second_width{$media_count}"),
      '#options'       => $width_options,
    );
    $form['aether_settings']['layout']["media{$media_count}"]["sidebar_second_width{$media_count}"]['#options'][$defaults["sidebar_second_width{$media_count}"]] .= t(' - Theme Default');
  }

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
      'html5' => t('Add HTML5 shim JavaScript to add support to IE 6-8.'),
      'respond' => t('Add Respond.js JavaScript to add basic CSS3 media query support to IE 6-8.'),
      'ioszoombugfix' => t('Fix zoom bug on ios landscape rotation (only useful for responsive design)'),
      'meta' => t('Add meta tags to support responsive design on mobile devices.'),
      'selectivizr' => t('Add pseudo class support to IE6-8.'),
      'imgsizer' => t('Add imgsizer fluid image support to IE6-8.'),
    ),
    '#description'   => t('IE 6-8 require a JavaScript polyfill solution to add basic support of HTML5 and CSS3 media queries. If you prefer to use another polyfill solution, such as <a href="!link">Modernizr</a>, you can disable these options. Mobile devices require a few meta tags for responsive designs.', array('!link' => 'http://www.modernizr.com/')),
  );

  $form['aether_settings']['drupal'] = array(
    '#title' => t('Drupal core options'),
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#weight' => 11,
  );

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
