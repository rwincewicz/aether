<?php

/**
 * @file
 * Contains theme override functions and preprocess functions for Aether.
 */

// Auto-rebuild the theme registry during theme development.
if (theme_get_setting('clear_registry')) {
  // Rebuild .info data.
  system_rebuild_theme_data();
  // Rebuild theme registry.
  drupal_theme_rebuild();
}

/**
 * Implements hook_preprocess_html().
 */
function aether_preprocess_html(&$variables, $hook) {
  // Add paths needed for html5shim.
  $variables['base_path'] = base_path();
  $variables['path_to_aether'] = drupal_get_path('theme', 'aether');
  $html5_respond_meta = theme_get_setting('aether_html5_respond_meta');
  $variables['add_html5_shim']      = in_array('html5', $html5_respond_meta);
  $variables['add_respond_js']      = in_array('respond', $html5_respond_meta);
  $variables['add_responsive_meta'] = in_array('meta', $html5_respond_meta);
  $variables['add_ios_viewport_bugfix'] = in_array('ioszoombugfix', $html5_respond_meta);
  $variables['add_selectivizr_js']  = in_array('selectivizr', $html5_respond_meta);
  $variables['add_imgsizer_js']  = in_array('imgsizer', $html5_respond_meta);
  $variables['skip_link_anchor'] = theme_get_setting('aether_skip_link_anchor');
  $variables['skip_link_text'] = theme_get_setting('aether_skip_link_text');

  // Attributes for html element.
  $variables['html_attributes_array'] = array(
    'lang' => $variables['language']->language,
    'dir' => $variables['language']->dir,
  );

  // Classes for body element. Allows advanced theming based on context
  // (home page, node of certain type, etc.)
  if (!$variables['is_front'] && $hook == 'html') {
    // Add unique class for each page.
    $path = drupal_get_path_alias($_GET['q']);
    // Add unique class for each website section.
    list($section,) = explode('/', $path, 2);
    $arg = explode('/', $_GET['q']);
    if ($arg[0] == 'node') {
      if ($arg[1] == 'add') {
        $section = 'node-add';
      }
      elseif (isset($arg[2]) && is_numeric($arg[1]) && ($arg[2] == 'edit' || $arg[2] == 'delete')) {
        $section = 'node-' . $arg[2];
      }
    }
    $variables['classes_array'][] = drupal_html_class('section-' . $section);
  }
  // Optionally add wireframes.
  if (theme_get_setting('zen_wireframes')) {
    $variables['classes_array'][] = 'with-wireframes';
  }
  // If media queries are enabled in theme-settings.
  if (in_array('1', theme_get_setting('layout_options'))) {
    $variables['classes_array'][] = 'responsive-all';
  }
  // If header adheres to grid.
  if (in_array('2', theme_get_setting('layout_options'))) {
    $variables['classes_array'][] = 'grid-en-header';
  }
  // If header adheres to grid.
  else {
    $variables['classes_array'][] = 'grid-dis-header';
  }
  // If navigation bar links adheres to grid.
  if (in_array('3', theme_get_setting('layout_options'))) {
    $variables['classes_array'][] = 'grid-en-nav';
  }
  // If header adheres to grid.
  else {
    $variables['classes_array'][] = 'grid-dis-nav';
  }
  // Store the menu item since it has some useful information.
  if ($hook == 'html') {
    $variables['menu_item'] = menu_get_item();
    if ($variables['menu_item']) {
      switch ($variables['menu_item']['page_callback']) {
        case 'views_page':
          // Is this a Views page?
          $variables['classes_array'][] = 'page-views';
          break;

        case 'page_manager_page_execute':
        case 'page_manager_node_view':
        case 'page_manager_contact_site':
          // Is this a Panels page?
          $variables['classes_array'][] = 'page-panels';
          break;
      }
    }
  }

  if (in_array('1', theme_get_setting('layout_options'))) {
    // Then load the media queries.
    drupal_add_css(drupal_get_path('theme', 'aether') . '/css/layout/layout-mediaqueries.css',
      array(
        'group' => CSS_THEME,
        'preprocess' => TRUE,
        'every_page' => TRUE,
        'weight' => '0',
      )
    );
  }

}

/**
 * Override or insert variables into the html templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("html" in this case.)
 */
function aether_process_html(&$variables, $hook) {
  // Flatten out html_attributes.
  $variables['html_attributes'] = drupal_attributes($variables['html_attributes_array']);
}

/**
 * Override or insert variables in the html_tag theme function.
 */
function aether_process_html_tag(&$variables) {
  $tag = &$variables['element'];

  if ($tag['#tag'] == 'style' || $tag['#tag'] == 'script') {
    // Remove redundant type attribute and CDATA comments.
    unset($tag['#attributes']['type'], $tag['#value_prefix'], $tag['#value_suffix']);

    // Remove media="all" but leave others unaffected.
    if (isset($tag['#attributes']['media']) && $tag['#attributes']['media'] === 'all') {
      unset($tag['#attributes']['media']);
    }
  }
}

/**
 * Implements hook_html_head_alter().
 */
function aether_html_head_alter(&$head) {
  // Simplify the meta tag for character encoding.
  $head['system_meta_content_type']['#attributes'] = array(
    'charset' => str_replace('text/html; charset=', '', $head['system_meta_content_type']['#attributes']['content']),
  );
}

/**
 * Implements hook_theme().
 */
function aether_theme() {
  return array(
    'grid_block' => array(
      'variables' => array('content' => NULL, 'id' => NULL),
    ),
  );
}

/**
 * Returns a list of blocks.
 * Uses Drupal block interface,
 * Appends any blocks assigned by the Context module.
 */
function aether_block_list($region) {
  $drupal_list = array();
  if (module_exists('block')) {
    $drupal_list = block_list($region);
  }
  if (module_exists('context') && $context = context_get_plugin('reaction', 'block')) {
    $context_list = $context->block_list($region);
    $drupal_list = array_merge($context_list, $drupal_list);
  }
  return $drupal_list;
}

/**
 * Generate initial grid info
 */
function aether_grid_info() {
  global $theme_key;

  if (!isset($grid)) {
    $grid = array();
    $media_queries = theme_get_setting('media_queries');
  }
  else {
    $media_queries = 1;
  }
  for ($media_count = 1; $media_count <= $media_queries; $media_count++) {
    $grid["prefix{$media_count}"] = substr(theme_get_setting("grid_prefix{$media_count}"), 0);
    $grid["nav_link_width{$media_count}"] = substr(theme_get_setting("nav_link_width{$media_count}"), 0);
    $grid["hgroup_first_width{$media_count}"] = substr(theme_get_setting("hgroup_first_width{$media_count}"), 0);
    $grid["hgroup_third_width{$media_count}"] = substr(theme_get_setting("hgroup_third_width{$media_count}"), 0);
    $grid["footer_first_width{$media_count}"] = substr(theme_get_setting("footer_first_width{$media_count}"), 0);
    $grid["footer_second_width{$media_count}"] = substr(theme_get_setting("footer_second_width{$media_count}"), 0);
    $grid["footer_third_width{$media_count}"] = substr(theme_get_setting("footer_third_width{$media_count}"), 0);
    $grid["footer_fourth_width{$media_count}"] = substr(theme_get_setting("footer_fourth_width{$media_count}"), 0);
    $grid["name{$media_count}"] = substr(theme_get_setting("theme_grid{$media_count}"), 0, 7);
    $grid["type{$media_count}"] = substr(theme_get_setting("theme_grid{$media_count}"), 7);
    $grid["fixed{$media_count}"] = (substr(theme_get_setting("theme_grid{$media_count}"), 7) != 'fluid') ? TRUE : FALSE;
    $grid["width{$media_count}"] = (int) substr($grid["name{$media_count}"], 4, 2);
    $grid["sidebar_first_width{$media_count}"] = (aether_block_list('sidebar_first')) ? theme_get_setting("sidebar_first_width{$media_count}") : 0;
    $grid["sidebar_second_width{$media_count}"] = (aether_block_list('sidebar_second')) ? theme_get_setting("sidebar_second_width{$media_count}") : 0;

    $grid['regions'] = array();
    $regions = array_keys(system_region_list($theme_key, REGIONS_VISIBLE));
    $nested_regions = theme_get_setting('grid_nested_regions');
    $adjusted_regions = theme_get_setting('grid_adjusted_regions');
    foreach ($regions as $region) {
      $region_style = 'full-width';
      $region_width = $grid["width{$media_count}"];
      if ($region == 'sidebar_first' || $region == 'sidebar_second') {
        $region_width = ($region == 'sidebar_first') ? $grid["sidebar_first_width{$media_count}"] : $grid["sidebar_second_width{$media_count}"];
      }
      if ($nested_regions && in_array($region, $nested_regions)) {
        $region_style = 'nested';
        if ($adjusted_regions && in_array($region, array_keys($adjusted_regions))) {
          foreach ($adjusted_regions[$region] as $adjacent_region) {
            $region_width = $region_width - $grid[$adjacent_region . '_width' . $media_count];
          }
        }
      }
      $grid['regions'][$region] = array(
        'width' => $region_width,
        'style' => $region_style,
        'total' => count(aether_block_list($region)),
        'count' => 0,
      );
    }

  }
  return $grid;
}


/**
 * Implements hook_preprocess_page().
 */
function aether_preprocess_page(&$variables, $hook) {
  if (isset($variables['node_title'])) {
    $variables['title'] = $variables['node_title'];
  }
  // Adding a class to #page in wireframe mode.
  if (theme_get_setting('wireframe_mode')) {
    $variables['classes_array'][] = 'wireframe-mode';
  }
  // Adding classes wether #navigation is here or not.
  if (!empty($variables['main_menu']) or !empty($variables['sub_menu'])) {
    $variables['classes_array'][] = 'with-navigation';
  }
  if (!empty($variables['secondary_menu'])) {
    $variables['classes_array'][] = 'with-subnav';
  }
  $variables['content_attributes_array']['class'][] = 'content-inner';

  // Set grid width.
  $grid = aether_grid_info();
  $media_queries = in_array('1', theme_get_setting('layout_options')) ? theme_get_setting('media_queries') : 1;

  for ($media_count = 1; $media_count <= $media_queries; $media_count++) {

    // Adjust width variables for nested grid groups.
    $grid_adjusted_groups = (theme_get_setting('grid_adjusted_groups')) ? theme_get_setting('grid_adjusted_groups') : array();
    foreach (array_keys($grid_adjusted_groups) as $group) {
      $width = $grid["width{$media_count}"];
      foreach ($grid_adjusted_groups[$group] as $region) {
        $width = $width - $grid['regions'][$region]['width'];
      }
      $variables[$group . '_width'] = $grid["width{$media_count}"] . $width;
    }

    // Add nav to grid option if checked.
    if (in_array('3', theme_get_setting('layout_options'))) {
      $base_grid_prefix = $grid["prefix{$media_count}"];
      $nav_link_width = $grid["nav_link_width{$media_count}"];
      foreach ($variables['main_menu'] as $key => $value) {
        $variables['main_menu'][$key]['attributes']['class'][] = $base_grid_prefix . "$nav_link_width ";
      }
    }
    // Create full grid width var and classes.
    $variables['grid_width_array'][] = $grid["prefix{$media_count}"] . $grid["width{$media_count}"] . ' ';
    $variables['grid_width'] = implode(' ', $variables['grid_width_array']);

    // Define variables for class insertion if header is aligned to grid.
    $variables['hgroup_first_width_array'][] = '';
    $variables['hgroup_second_width_array'][] = '';
    $variables['hgroup_third_width_array'][] = '';
    $variables['hgroup_third_classes_array'][] = '';
    $variables['hgroup_first_width'] = implode(' ', $variables['hgroup_first_width_array']);
    $variables['hgroup_second_width'] = implode(' ', $variables['hgroup_second_width_array']);
    $variables['hgroup_third_width'] = implode(' ', $variables['hgroup_third_width_array']);
    $variables['hgroup_third_classes'] = implode(' ', $variables['hgroup_third_classes_array']);

    if (in_array('2', theme_get_setting('layout_options'))) {
      // Set hgroup variables and classes.
      if ($region == 'hgroup_first' || $region == 'hgroup_second' || $region == 'hgroup_third' || (isset($variables['logo'])) || (isset($variables['site_name'])) || (isset($variables['site_slogan'])) || (isset($variables['secondary_menu']))) {
        $base_grid_prefix = $grid["prefix{$media_count}"];
        $logo_width = $grid["hgroup_first_width{$media_count}"];
        $seclinks_width = $grid["hgroup_third_width{$media_count}"];
        $sitename_width = $grid["width{$media_count}"] - ($logo_width + $seclinks_width);
        $sitename_width_logo_left = $grid["width{$media_count}"] - $seclinks_width;
        $logo_seclinks_width = $logo_width + $seclinks_width;
        $logo_sitename_width = $logo_width + $sitename_width;
        $seclinks_sitename_width = $seclinks_width + $sitename_width;
        if (theme_get_setting("hgroup_layout{$media_count}") === '1') {
          if ((!empty($variables['logo']) || $grid['regions']['hgroup_first']['total'] === 1) && (((!empty($variables['site_name'])) || (!empty($variables['site_slogan'])) || $grid['regions']['hgroup_second']['total'] === 1)) && ((!empty($variables['secondary_menu'])) || $grid['regions']['hgroup_third']['total'] === 1)) {
            $variables['hgroup_first_width_array'][] = $base_grid_prefix . $logo_width;
            $variables['hgroup_second_width_array'][] = $base_grid_prefix . $sitename_width;
            $variables['hgroup_third_width_array'][] = $base_grid_prefix . $seclinks_width;
          }
          elseif ((empty($variables['logo']) && $grid['regions']['hgroup_first']['total'] === 0) && (((empty($variables['site_name'])) && (empty($variables['site_slogan'])) && $grid['regions']['hgroup_second']['total'] === 0)) && ((!empty($variables['secondary_menu'])) || $grid['regions']['hgroup_third']['total'] === 1)) {
            $variables['hgroup_third_classes_array'][] = $base_grid_prefix . 'o' . $logo_sitename_width;
            $variables['hgroup_third_width_array'][] = $base_grid_prefix . $seclinks_width;
          }
          elseif ((empty($variables['logo']) && $grid['regions']['hgroup_first']['total'] === 0) && (((!empty($variables['site_name'])) || (!empty($variables['site_slogan'])) || $grid['regions']['hgroup_second']['total'] === 1)) && ((!empty($variables['secondary_menu'])) || $grid['regions']['hgroup_third']['total'] === 1)) {
            $variables['hgroup_second_width_array'][] = $base_grid_prefix . $logo_sitename_width;
            $variables['hgroup_third_width_array'][] = $base_grid_prefix . $seclinks_width;
          }
          elseif ((!empty($variables['logo']) || $grid['regions']['hgroup_first']['total'] === 1) && (((!empty($variables['site_name'])) || (!empty($variables['site_slogan'])) || $grid['regions']['hgroup_second']['total'] === 1)) && ((empty($variables['secondary_menu'])) && $grid['regions']['hgroup_third']['total'] === 0)) {
            $variables['hgroup_first_width_array'][] = $base_grid_prefix . $logo_width;
            $variables['hgroup_second_width_array'][] = $base_grid_prefix . ($grid["width{$media_count}"] - $logo_width);
          }
          elseif ((!empty($variables['logo']) || $grid['regions']['hgroup_first']['total'] === 1) && (((empty($variables['site_name'])) && (empty($variables['site_slogan'])) && $grid['regions']['hgroup_second']['total'] === 0)) && ((!empty($variables['secondary_menu'])) || $grid['regions']['hgroup_third']['total'] === 0)) {
            $variables['hgroup_third_classes_array'][] = $base_grid_prefix . 'o' . $logo_sitename_width;
            $variables['hgroup_first_width_array'][] = $base_grid_prefix . $logo_width;
            $variables['hgroup_third_width_array'][] = $base_grid_prefix . $seclinks_width;
          }
          elseif ((empty($variables['logo']) && $grid['regions']['hgroup_first']['total'] === 0) && (((!empty($variables['site_name'])) || (!empty($variables['site_slogan'])) || $grid['regions']['hgroup_second']['total'] === 1)) && ((empty($variables['secondary_menu'])) && $grid['regions']['hgroup_third']['total'] === 0)) {
            $variables['hgroup_second_width_array'][] = $base_grid_prefix . $grid["width{$media_count}"];
          }
        }
        elseif (theme_get_setting("hgroup_layout{$media_count}") === '2') {
          if ((!empty($variables['logo']) || $grid['regions']['hgroup_first']['total'] === 1) && (((!empty($variables['site_name'])) || (!empty($variables['site_slogan'])) || $grid['regions']['hgroup_second']['total'] === 1)) && ((!empty($variables['secondary_menu'])) || $grid['regions']['hgroup_third']['total'] === 1)) {
            $variables['hgroup_first_width_array'][] = $base_grid_prefix . $logo_width;
            $variables['hgroup_second_width_array'][] = $base_grid_prefix . $seclinks_sitename_width;
            $variables['hgroup_third_width_array'][] = $base_grid_prefix . $grid["width{$media_count}"];
          }
          elseif ((empty($variables['logo']) && $grid['regions']['hgroup_first']['total'] === 0) && (((empty($variables['site_name'])) && (empty($variables['site_slogan'])) && $grid['regions']['hgroup_second']['total'] === 0)) && ((!empty($variables['secondary_menu'])) || $grid['regions']['hgroup_third']['total'] === 1)) {
            $variables['hgroup_third_width_array'][] = $base_grid_prefix . $grid["width{$media_count}"];;
          }
          elseif ((empty($variables['logo']) && $grid['regions']['hgroup_first']['total'] === 0) && (((!empty($variables['site_name'])) || (!empty($variables['site_slogan'])) || $grid['regions']['hgroup_second']['total'] === 1)) && ((!empty($variables['secondary_menu'])) || $grid['regions']['hgroup_third']['total'] === 1)) {
            $variables['hgroup_second_width_array'][] = $base_grid_prefix . $grid["width{$media_count}"];
            $variables['hgroup_third_width_array'][] = $base_grid_prefix . $grid["width{$media_count}"];
          }
          elseif ((!empty($variables['logo']) || $grid['regions']['hgroup_first']['total'] === 1) && (((!empty($variables['site_name'])) || (!empty($variables['site_slogan'])) || $grid['regions']['hgroup_second']['total'] === 1)) && ((empty($variables['secondary_menu'])) && $grid['regions']['hgroup_third']['total'] === 0)) {
            $variables['hgroup_first_width_array'][] = $base_grid_prefix . $logo_width;
            $variables['hgroup_second_width_array'][] = $base_grid_prefix . ($grid["width{$media_count}"] - $logo_width);
          }
          elseif ((!empty($variables['logo']) || $grid['regions']['hgroup_first']['total'] === 1) && (((empty($variables['site_name'])) && (empty($variables['site_slogan'])) && $grid['regions']['hgroup_second']['total'] === 0)) && ((!empty($variables['secondary_menu'])) || $grid['regions']['hgroup_third']['total'] === 0)) {
            $variables['hgroup_third_classes_array'][] = '';
            $variables['hgroup_first_width_array'][] = $base_grid_prefix . $logo_width;
            $variables['hgroup_third_width_array'][] = $base_grid_prefix . $grid["width{$media_count}"];
          }
          elseif ((empty($variables['logo']) && $grid['regions']['hgroup_first']['total'] === 0) && (((!empty($variables['site_name'])) || (!empty($variables['site_slogan'])) || $grid['regions']['hgroup_second']['total'] === 1)) && ((empty($variables['secondary_menu'])) && $grid['regions']['hgroup_third']['total'] === 0)) {
            $variables['hgroup_second_width_array'][] = $base_grid_prefix . $grid["width{$media_count}"];
          }
        }
        else {
          $variables['hgroup_first_width_array'][] = $base_grid_prefix . $grid["width{$media_count}"];
          $variables['hgroup_second_width_array'][] = $base_grid_prefix . $grid["width{$media_count}"];
          $variables['hgroup_third_width_array'][] = $base_grid_prefix . $grid["width{$media_count}"];
        }
          $variables['hgroup_first_width'] = implode(' ', $variables['hgroup_first_width_array']);
          $variables['hgroup_second_width'] = implode(' ', $variables['hgroup_second_width_array']);
          $variables['hgroup_third_width'] = implode(' ', $variables['hgroup_third_width_array']);
          $variables['hgroup_third_classes'] = implode(' ', $variables['hgroup_third_classes_array']);
      }
    }

    // Set content classes.
    if ($region == 'sidebar_first' || $region == 'sidebar_second') {
      $base_grid_prefix = $grid["prefix{$media_count}"];
      $push_prefix = $base_grid_prefix . "push-";
      $pull_prefix = $base_grid_prefix . "pull-";
      $offset_prefix = $base_grid_prefix . "o-";
      $sidebar_first_width = $grid["sidebar_first_width{$media_count}"];
      $sidebar_second_width = $grid["sidebar_second_width{$media_count}"];
      $content_width = ($grid["width{$media_count}"] - $sidebar_first_width) - $sidebar_second_width;
      $content_width_sidebar_right = $grid["width{$media_count}"] - $sidebar_first_width;
      $two_sidebar_width = $sidebar_first_width + $sidebar_second_width;
      $sidebar1_content_width = $grid["sidebar_first_width{$media_count}"] + $content_width;
      $sidebar2_content_width = $grid["sidebar_second_width{$media_count}"] + $content_width;
      if (theme_get_setting("sidebar_layout{$media_count}") === '1') {
        $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$content_width " . $push_prefix . "$sidebar_first_width ";
      }
      if (theme_get_setting("sidebar_layout{$media_count}") === '2') {
        $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$content_width ";
      }
      if (theme_get_setting("sidebar_layout{$media_count}") === '3') {
        $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$content_width " . $push_prefix . "$two_sidebar_width ";
      }
      if (theme_get_setting("sidebar_layout{$media_count}") === '4') {
        $variables['content_attributes_array']['class'][] = $base_grid_prefix . $grid["width{$media_count}"];
      }
      if (theme_get_setting("sidebar_layout{$media_count}") === '5') {
        $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$content_width_sidebar_right ";
      }
    }
  }
}


/**
 * Implements hook_preprocess_node().
 */
function aether_preprocess_node(&$variables) {
  // Add a striping class.
  $variables['classes_array'][] = 'node-' . $variables['zebra'];
  // Add $unpublished variable.
  $variables['unpublished'] = (!$variables['status']) ? TRUE : FALSE;

  // Add pubdate to submitted variable.
  $variables['pubdate'] = '<time pubdate datetime="' . format_date($variables['node']->created, 'custom', 'c') . '">' . $variables['date'] . '</time>';
  if ($variables['display_submitted']) {
    $variables['submitted'] = t('Submitted by !username on !datetime', array('!username' => $variables['name'], '!datetime' => $variables['pubdate']));
  }
}

/**
 * Override or insert variables into the comment templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("comment" in this case.)
 */
function zen_preprocess_comment(&$variables, $hook) {
  // If comment subjects are disabled, don't display them.
  if (variable_get('comment_subject_field_' . $variables['node']->type, 1) == 0) {
    $variables['title'] = '';
  }

  // Add pubdate to submitted variable.
  $variables['pubdate'] = '<time pubdate datetime="' . format_date($variables['comment']->created, 'custom', 'c') . '">' . $variables['created'] . '</time>';
  $variables['submitted'] = t('!username replied on !datetime', array('!username' => $variables['author'], '!datetime' => $variables['pubdate']));

  // Zebra striping.
  if ($variables['id'] == 1) {
    $variables['classes_array'][] = 'first';
  }
  if ($variables['id'] == $variables['node']->comment_count) {
    $variables['classes_array'][] = 'last';
  }
  $variables['classes_array'][] = $variables['zebra'];

  $variables['title_attributes_array']['class'][] = 'comment-title';
}

/**
 * Implements hook_preprocess_block().
 */
function aether_preprocess_block(&$variables, $hook) {
  // Use a template with no wrapper for the page's main content.
  if ($variables['block_html_id'] == 'block-system-main') {
    $variables['theme_hook_suggestions'][] = 'block__no_wrapper';
  }

  // Classes describing the position of the block within the region.
  if ($variables['block_id'] == 1) {
    $variables['classes_array'][] = 'first';
  }
  // The last_in_region property is set in aether_page_alter().
  if (isset($variables['block']->last_in_region)) {
    $variables['classes_array'][] = 'last';
  }
  $variables['title_attributes_array']['class'][] = 'block-title';

  // Add a striping class.
  $variables['classes_array'][] = 'block-' . $variables['zebra'];
  // Add Aria Roles via attributes.
  switch ($variables['block']->module) {
    case 'system':
      switch ($variables['block']->delta) {
        case 'main':
          // Note: the "main" role goes in the page.tpl, not here.
          break;

        case 'help':
        case 'powered-by':
          $variables['attributes_array']['role'] = 'complementary';
          break;

        default:
          // Any other "system" block is a menu block.
          $variables['attributes_array']['role'] = 'navigation';
          break;

      }
      break;

    case 'menu':
    case 'menu_block':
    case 'blog':
    case 'book':
    case 'comment':
    case 'forum':
    case 'shortcut':
    case 'statistics':
      $variables['attributes_array']['role'] = 'navigation';
      break;

    case 'search':
      $variables['attributes_array']['role'] = 'search';
      break;

    case 'help':
    case 'aggregator':
    case 'locale':
    case 'poll':
    case 'profile':
      $variables['attributes_array']['role'] = 'complementary';
      break;

    case 'node':
      switch ($variables['block']->delta) {
        case 'syndicate':
          $variables['attributes_array']['role'] = 'complementary';
          break;

        case 'recent':
          $variables['attributes_array']['role'] = 'navigation';
          break;

      }
      break;

    case 'user':
      switch ($variables['block']->delta) {
        case 'login':
          $variables['attributes_array']['role'] = 'form';
          break;

        case 'new':
        case 'online':
          $variables['attributes_array']['role'] = 'complementary';
          break;

      }
      break;

  }
}

/**
 * Implements hook_page_alter().
 *
 * Look for the last block in the region. This is impossible to determine from
 * within a preprocess_block function.
 *
 * @param $page
 *   Nested array of renderable elements that make up the page.
 */
function aether_page_alter(&$page) {
  // Look in each visible region for blocks.
  foreach (system_region_list($GLOBALS['theme'], REGIONS_VISIBLE) as $region => $name) {
    if (!empty($page[$region])) {
      // Find the last block in the region.
      $blocks = array_reverse(element_children($page[$region]));
      while ($blocks && !isset($page[$region][$blocks[0]]['#block'])) {
        array_shift($blocks);
      }
      if ($blocks) {
        $page[$region][$blocks[0]]['#block']->last_in_region = TRUE;
      }
    }
  }
}

/**
 * Preprocess variables for region.tpl.php
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("region" in this case.)
 */
function aether_preprocess_region(&$variables, $hook) {
  static $grid;

  // Initialize grid info once per page.
  if (!isset($grid)) {
    $grid = aether_grid_info();
  }

  // Sidebar regions get some extra classes and a common template suggestion.
  if (strpos($variables['region'], 'sidebar_') === 0) {
    $variables['classes_array'][] = 'column';
    $variables['classes_array'][] = 'sidebar';
    $variables['content_attributes_array']['class'][] = 'sidebar-inner';
    // Allow a region-specific template to override aether's region--sidebar.
    array_unshift($variables['theme_hook_suggestions'], 'region__sidebar');
  }

  // Footer region gets a common template suggestion.
  if (strpos($variables['region'], 'footer') === 0) {
    $variables['content_attributes_array']['class'][] = 'footer-inner';
    // Allow a region-specific template to override aether's region--sidebar.
    array_unshift($variables['theme_hook_suggestions'], 'region__footer');
  }

  // Set region variables.
  $variables['region_style'] = $variables['fluid_width'] = '';
  $variables['region_name'] = str_replace('_', '-', $variables['region']);
  $variables['classes_array'][] = $variables['region_name'];
  if (in_array($variables['region'], array_keys($grid['regions']))) {
    // Set region full-width or nested style.
    $variables['region_style'] = $grid['regions'][$variables['region']]['style'];
    $variables['classes_array'][] = ($variables['region_style'] == 'nested') ? $variables['region_style'] : '';

    if (in_array('1', theme_get_setting('layout_options'))) {
      $media_queries = theme_get_setting('media_queries');
    }
    else {
      $media_queries = 1;
    }

    for ($media_count = 1; $media_count <= $media_queries; $media_count++) {
      // Do we really need to duplicate all of these variables..
      // or can they be set globally in $grid?
      $base_grid_prefix = $grid["prefix{$media_count}"];
      $push_prefix = $base_grid_prefix . "push-";
      $pull_prefix = $base_grid_prefix . "pull-";
      $offset_prefix = $base_grid_prefix . "o-";
      $sidebar_first_width = $grid["sidebar_first_width{$media_count}"];
      $sidebar_second_width = $grid["sidebar_second_width{$media_count}"];
      $content_width = ($grid["width{$media_count}"] - $sidebar_first_width) - $sidebar_second_width;
      $full_width = $grid["width{$media_count}"];
      $two_sidebar_width = $sidebar_first_width + $sidebar_second_width;;
      $sidebar1_content_width = $grid["sidebar_first_width{$media_count}"] + $content_width;
      $sidebar2_content_width = $grid["sidebar_second_width{$media_count}"] + $content_width;

      if (strpos($variables['region'], 'sidebar_first') === 0) {
        if (theme_get_setting("sidebar_layout{$media_count}") === '1') {
          $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$sidebar_first_width " . $pull_prefix . "$content_width ";
        }
        if (theme_get_setting("sidebar_layout{$media_count}") === '2') {
          $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$sidebar_first_width ";
        }
        if (theme_get_setting("sidebar_layout{$media_count}") === '3') {
          $variables['classes_array'][] = $offset_prefix . "$content_width ";
          $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$sidebar_first_width " . $pull_prefix . "$content_width ";
        }
        if (theme_get_setting("sidebar_layout{$media_count}") === '4') {
          $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$full_width ";
        }
        if (theme_get_setting("sidebar_layout{$media_count}") === '5') {
          $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$sidebar_first_width ";
        }
      }

      if (strpos($variables['region'], 'sidebar_second') === 0) {
        $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$sidebar_second_width ";
        if (theme_get_setting("sidebar_layout{$media_count}") === '1') {
          $variables['classes_array'][] = $offset_prefix . "$sidebar1_content_width ";
        }
        if (theme_get_setting("sidebar_layout{$media_count}") === '2') {
          $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$sidebar_second_width ";
        }
        if (theme_get_setting("sidebar_layout{$media_count}") === '3') {
          $variables['classes_array'][] = $offset_prefix . "$sidebar1_content_width ";
          $variables['content_attributes_array']['class'][] = $pull_prefix . "$content_width ";
        }
        if (theme_get_setting("sidebar_layout{$media_count}") === '4') {
          $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$full_width ";
        }
        if (theme_get_setting("sidebar_layout{$media_count}") === '5') {
          $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$full_width ";
        }
      }
      if (strpos($variables['region'], 'footer_first') === 0) {
        $variables['classes_array'][] = $offset_prefix . '';
        $variables['content_attributes_array']['class'][] = $base_grid_prefix . $grid["footer_first_width{$media_count}"];
      }
      if (strpos($variables['region'], 'footer_second') === 0) {
        $variables['classes_array'][] = $offset_prefix . '';
        $variables['content_attributes_array']['class'][] = $base_grid_prefix . $grid["footer_second_width{$media_count}"];
      }
      if (strpos($variables['region'], 'footer_third') === 0) {
        $variables['classes_array'][] = $offset_prefix . '';
        $variables['content_attributes_array']['class'][] = $base_grid_prefix . $grid["footer_third_width{$media_count}"];
      }
      if (strpos($variables['region'], 'footer_fourth') === 0) {
        $variables['classes_array'][] = $offset_prefix . '';
        $variables['content_attributes_array']['class'][] = $base_grid_prefix . $grid["footer_fourth_width{$media_count}"];
      }
      if ($variables['region'] == 'footer') {
        $variables['content_attributes_array']['class'][] = $base_grid_prefix . "$full_width ";
      }
    }
  }
}


/**
 * Return a themed breadcrumb trail.
 *
 * @param $breadcrumb
 *   An array containing the breadcrumb links.
 *
 * @return
 *   A string containing the breadcrumb output.
 */
function aether_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];
  $output = '';

  // Determine if we are to display the breadcrumb.
  $show_breadcrumb = theme_get_setting('aether_breadcrumb');
  if ($show_breadcrumb == 'yes' || $show_breadcrumb == 'admin' && arg(0) == 'admin') {

    // Optionally get rid of the homepage link.
    $show_breadcrumb_home = theme_get_setting('aether_breadcrumb_home');
    if (!$show_breadcrumb_home) {
      array_shift($breadcrumb);
    }

    // Return the breadcrumb with separators.
    if (!empty($breadcrumb)) {
      $breadcrumb_separator = theme_get_setting('aether_breadcrumb_separator');
      $trailing_separator = $title = '';
      if (theme_get_setting('aether_breadcrumb_title')) {
        $item = menu_get_item();
        if (!empty($item['tab_parent'])) {
          // If we are on a non-default tab, use the tab's title.
          $breadcrumb[] = check_plain($item['title']);
        }
        else {
          $breadcrumb[] = drupal_get_title();
        }
      }
      elseif (theme_get_setting('aether_breadcrumb_trailing')) {
        $trailing_separator = $breadcrumb_separator;
      }

      // Provide a navigational heading to give context for breadcrumb links to
      // screen-reader users.
      if (empty($variables['title'])) {
        $variables['title'] = t('You are here');
      }
      // Unless overridden by a preprocess function, make the heading invisible.
      if (!isset($variables['title_attributes_array']['class'])) {
        $variables['title_attributes_array']['class'][] = 'element-invisible';
      }

      // Build the breadcrumb trail.
      $output = '<nav class="breadcrumb" role="navigation">';
      $output .= '<h2' . drupal_attributes($variables['title_attributes_array']) . '>' . $variables['title'] . '</h2>';
      $output .= '<ul><li>' . implode($breadcrumb_separator . '</li><li>', $breadcrumb) . $trailing_separator . '</li></ul>';
      $output .= '</nav>';
    }
  }

  return $output;
}

/**
 *  Duplicate of theme_menu_local_tasks() but adds clearfix to tabs.
 */
function aether_menu_local_tasks(&$variables) {
  $output = '';

  if (!empty($variables['primary'])) {
    $variables['primary']['#prefix'] = '<h2 class="element-invisible">' . t('Primary tabs') . '</h2>';
    $variables['primary']['#prefix'] .= '<ul class="tabs primary clearfix">';
    $variables['primary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['primary']);
  }
  if (!empty($variables['secondary'])) {
    $variables['secondary']['#prefix'] = '<h2 class="element-invisible">' . t('Secondary tabs') . '</h2>';
    $variables['secondary']['#prefix'] .= '<ul class="tabs secondary clearfix">';
    $variables['secondary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['secondary']);
  }

  return $output;
}

/**
 * Implements hook_form_BASE_FORM_ID_alter().
 *
 * Prevent user-facing field styling from screwing up node edit forms by
 * renaming the classes on the node edit form's field wrappers.
 */
function aether_form_node_form_alter(&$form, &$form_state, $form_id) {
  // Remove if #1245218 is backported to D7 core.
  foreach (array_keys($form) as $item) {
    if (strpos($item, 'field_') === 0) {
      if (!empty($form[$item]['#attributes']['class'])) {
        foreach ($form[$item]['#attributes']['class'] as &$class) {
          if (strpos($class, 'field-type-') === 0 || strpos($class, 'field-name-') === 0) {
            // Make the class different from that used in theme_field().
            $class = 'form-' . $class;
          }
        }
      }
    }
  }
}

/**
 * Make Drupal core generated images responsive i.e. flexible in width.
 */
function aether_image($variables) {
  $attributes = $variables['attributes'];
  $attributes['src'] = file_create_url($variables['path']);

  // Remove width and height attributes.
  foreach (array('alt', 'title') as $key) {
    if (isset($variables[$key])) {
      $attributes[$key] = $variables[$key];
    }
  }
  return '<img' . drupal_attributes($attributes) . ' />';
}
