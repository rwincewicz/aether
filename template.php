<?php

/*
 * Here we override the default HTML output of drupal.
 * refer to http://drupal.org/node/550722
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
function aether_preprocess_html(&$vars, $hook) {
  // Add paths needed for html5shim.
  $vars['base_path'] = base_path();
  $vars['path_to_aether'] = drupal_get_path('theme', 'aether');
  $html5_respond_meta = theme_get_setting('aether_html5_respond_meta');
  $vars['add_html5_shim']      = in_array('html5', $html5_respond_meta);
  $vars['add_respond_js']      = in_array('respond', $html5_respond_meta);
  $vars['add_responsive_meta'] = in_array('meta', $html5_respond_meta);
  $vars['add_ios_viewport_bugfix'] = in_array('ioszoombugfix', $html5_respond_meta);
  $vars['add_selectivizr_js']  = in_array('selectivizr', $html5_respond_meta);
  $vars['add_imgsizer_js']  = in_array('imgsizer', $html5_respond_meta);
  $vars['skip_link_anchor'] = theme_get_setting('aether_skip_link_anchor');
  $vars['skip_link_text'] = theme_get_setting('aether_skip_link_text');

  // Attributes for html element.
  $vars['html_attributes_array'] = array(
    'lang' => $vars['language']->language,
    'dir' => $vars['language']->dir,
  );

  if (in_array('1', theme_get_setting('layout_options'))) {
  // then load the media queries
    drupal_add_css(drupal_get_path('theme', 'aether') . '/css/layout/layout-mediaqueries.css', array('group' => CSS_THEME, 'preprocess' => TRUE, 'every_page' => TRUE, 'weight' => '0'));
  }

}

/**
 * Override or insert variables into the html templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("html" in this case.)
 */
function aether_process_html(&$vars, $hook) {
  // Flatten out html_attributes.
  $vars['html_attributes'] = drupal_attributes($vars['html_attributes_array']);
}

/**
 * Override or insert variables in the html_tag theme function.
 */
function aether_process_html_tag(&$vars) {
  $tag = &$vars['element'];

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
 * Uses Drupal block interface and appends any blocks assigned by the Context module.
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
    $grid["name{$media_count}"] = substr(theme_get_setting("theme_grid{$media_count}"), 0, 7);
    $grid["type{$media_count}"] = substr(theme_get_setting("theme_grid{$media_count}"), 7);
    $grid["fixed{$media_count}"] = (substr(theme_get_setting("theme_grid{$media_count}"), 7) != 'fluid') ? TRUE : FALSE;
    $grid["width{$media_count}"] = (int)substr($grid["name{$media_count}"], 4, 2);
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
      $grid['regions'][$region] = array('width' => $region_width, 'style' => $region_style, 'total' => count(aether_block_list($region)), 'count' => 0);
    }

  }
  return $grid;
}

/**
 * Implements hook_preprocess_page().
 */
function aether_preprocess_page(&$vars, $hook) {
  if (isset($vars['node_title'])) {
    $vars['title'] = $vars['node_title'];
  }
  // Adding a class to #page in wireframe mode
  if (theme_get_setting('wireframe_mode')) {
    $vars['classes_array'][] = 'wireframe-mode';
  }
  // Adding classes wether #navigation is here or not
  if (!empty($vars['main_menu']) or !empty($vars['sub_menu'])) {
    $vars['classes_array'][] = 'with-navigation';
  }
  if (!empty($vars['secondary_menu'])) {
    $vars['classes_array'][] = 'with-subnav';
  }
  $vars['content_attributes_array']['class'][] = 'content-inner';

  // Set grid width
  $grid = aether_grid_info();
  $media_queries = in_array('1', theme_get_setting('layout_options')) ? theme_get_setting('media_queries') : 1;

  // Define var for later use
  $grid_width = '';

  for ($media_count = 1; $media_count <= $media_queries; $media_count++) {

    // Adjust width variables for nested grid groups
    $grid_adjusted_groups = (theme_get_setting('grid_adjusted_groups')) ? theme_get_setting('grid_adjusted_groups') : array();
    foreach (array_keys($grid_adjusted_groups) as $group) {
      $width = $grid["width{$media_count}"];
      foreach ($grid_adjusted_groups[$group] as $region) {
        $width = $width - $grid['regions'][$region]['width'];
      }
      $vars[$group . '_width'] = $grid["width{$media_count}"] . $width;
    }

    // Add nav to grid option if checked
    if (in_array('2', theme_get_setting('layout_options'))) {
      $base_grid_prefix = $grid["prefix{$media_count}"];
      $nav_link_width = $grid["nav_link_width{$media_count}"];
      foreach ($vars['main_menu'] as $key => $value) {
        $vars['main_menu'][$key]['attributes']['class'][] = $base_grid_prefix . "$nav_link_width ";
      }
    }
    // Create full grid width variable
    $grid_width .= $grid["prefix{$media_count}"] . $grid["width{$media_count}"] . ' ';
    $vars['grid_width'] = $grid_width;

    // Set content classes
    if ($region == 'sidebar_first' || $region == 'sidebar_second') {
      $base_grid_prefix = $grid["prefix{$media_count}"];
      $push_prefix = $base_grid_prefix . "push";
      $pull_prefix = $base_grid_prefix . "pull";
      $offset_prefix = $base_grid_prefix . "o";
      $sidebar_first_width = $grid["sidebar_first_width{$media_count}"];
      $sidebar_second_width = $grid["sidebar_second_width{$media_count}"];
      $content_width = ($grid["width{$media_count}"] - $sidebar_first_width) - $sidebar_second_width;
      $two_sidebar_width = $sidebar_first_width + $sidebar_second_width;;
      $sidebar1_content_width = $grid["sidebar_first_width{$media_count}"] + $content_width;
      $sidebar2_content_width = $grid["sidebar_second_width{$media_count}"] + $content_width;
      if (theme_get_setting("sidebar_layout{$media_count}") === '1') {
        $vars['content_attributes_array']['class'][] = $base_grid_prefix . "$content_width " . $push_prefix . "$sidebar_first_width ";
      }
      if (theme_get_setting("sidebar_layout{$media_count}") === '2') {
        $vars['content_attributes_array']['class'][] = $base_grid_prefix . "$content_width " . $push_prefix . "$two_sidebar_width ";
      }
      if (theme_get_setting("sidebar_layout{$media_count}") === '3') {
        $vars['content_attributes_array']['class'][] = $base_grid_prefix . "$content_width ";
      }
      if (theme_get_setting("sidebar_layout{$media_count}") === '4') {
        $vars['content_attributes_array']['class'][] = $base_grid_prefix . $grid["width{$media_count}"];
      }
    }
  }
}

/**
 * Implements hook_preprocess_node().
 */
function aether_preprocess_node(&$vars) {
  // Add a striping class.
  $vars['classes_array'][] = 'node-' . $vars['zebra'];
  // Add $unpublished variable.
  $vars['unpublished'] = (!$vars['status']) ? TRUE : FALSE;

  // Add pubdate to submitted variable.
  $vars['pubdate'] = '<time pubdate datetime="' . format_date($vars['node']->created, 'custom', 'c') . '">' . $vars['date'] . '</time>';
  if ($vars['display_submitted']) {
    $vars['submitted'] = t('Submitted by !username on !datetime', array('!username' => $vars['name'], '!datetime' => $vars['pubdate']));
  }
}

/**
 * Override or insert variables into the comment templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("comment" in this case.)
 */
function aether_preprocess_comment(&$vars, $hook) {
  // If comment subjects are disabled, don't display them.
  if (variable_get('comment_subject_field_' . $vars['node']->type, 1) == 0) {
    $vars['title'] = '';
  }

  // Add pubdate to submitted variable.
  $vars['pubdate'] = '<time pubdate datetime="' . format_date($vars['comment']->created, 'custom', 'c') . '">' . $vars['created'] . '</time>';
  $vars['submitted'] = t('!username replied on !datetime',
    array('!username' => $vars['author'], '!datetime' => $vars['pubdate']));

  // Zebra striping.
  if ($vars['id'] == 1) {
    $vars['classes_array'][] = 'first';
  }
  if ($vars['id'] == $vars['node']->comment_count) {
    $vars['classes_array'][] = 'last';
  }
  $vars['classes_array'][] = $vars['zebra'];
  $vars['title_attributes_array']['class'][] = 'comment-title';
}

/**
 * Implements hook_preprocess_block().
 */
function aether_preprocess_block(&$vars, $hook) {
  // Use a template with no wrapper for the page's main content.
  if ($vars['block_html_id'] == 'block-system-main') {
    $vars['theme_hook_suggestions'][] = 'block__no_wrapper';
  }

  // Classes describing the position of the block within the region.
  if ($vars['block_id'] == 1) {
    $vars['classes_array'][] = 'first';
  }
  // The last_in_region property is set in aether_page_alter().
  if (isset($vars['block']->last_in_region)) {
    $vars['classes_array'][] = 'last';
  }
  $vars['title_attributes_array']['class'][] = 'block-title';

  // Add a striping class.
  $vars['classes_array'][] = 'block-' . $vars['zebra'];
  // Add Aria Roles via attributes.
  switch ($vars['block']->module) {
    case 'system':
      switch ($vars['block']->delta) {
        case 'main':
          // Note: the "main" role goes in the page.tpl, not here.
          break;
        case 'help':
        case 'powered-by':
          $vars['attributes_array']['role'] = 'complementary';
          break;
        default:
          // Any other "system" block is a menu block.
          $vars['attributes_array']['role'] = 'navigation';
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
      $vars['attributes_array']['role'] = 'navigation';
      break;
    case 'search':
      $vars['attributes_array']['role'] = 'search';
      break;
    case 'help':
    case 'aggregator':
    case 'locale':
    case 'poll':
    case 'profile':
      $vars['attributes_array']['role'] = 'complementary';
      break;
    case 'node':
      switch ($vars['block']->delta) {
        case 'syndicate':
          $vars['attributes_array']['role'] = 'complementary';
          break;
        case 'recent':
          $vars['attributes_array']['role'] = 'navigation';
          break;
      }
      break;
    case 'user':
      switch ($vars['block']->delta) {
        case 'login':
          $vars['attributes_array']['role'] = 'form';
          break;
        case 'new':
        case 'online':
          $vars['attributes_array']['role'] = 'complementary';
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
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("region" in this case.)
 */
function aether_preprocess_region(&$vars, $hook) {
  static $grid;

  // Initialize grid info once per page
  if (!isset($grid)) {
    $grid = aether_grid_info();
  }

  // Sidebar regions get some extra classes and a common template suggestion.
  if (strpos($vars['region'], 'sidebar_') === 0) {
    $vars['classes_array'][] = 'column';
    $vars['classes_array'][] = 'sidebar';
    $vars['content_attributes_array']['class'][] = 'sidebar-inner';
    // Allow a region-specific template to override aether's region--sidebar.
    array_unshift($vars['theme_hook_suggestions'], 'region__sidebar');
  }

  // Footer region gets a common template suggestion.
  if (strpos($vars['region'], 'footer') === 0) {
    $vars['content_attributes_array']['class'][] = 'footer-inner';
    // Allow a region-specific template to override aether's region--sidebar.
    array_unshift($vars['theme_hook_suggestions'], 'region__footer');
  }

  // Set region variables
  $vars['region_style'] = $vars['fluid_width'] = '';
  $vars['region_name'] = str_replace('_', '-', $vars['region']);
  $vars['classes_array'][] = $vars['region_name'];
  if (in_array($vars['region'], array_keys($grid['regions']))) {
    // Set region full-width or nested style
    $vars['region_style'] = $grid['regions'][$vars['region']]['style'];
    $vars['classes_array'][] = ($vars['region_style'] == 'nested') ? $vars['region_style'] : '';

  if (in_array('1', theme_get_setting('layout_options'))) {
    $media_queries = theme_get_setting('media_queries');
  }
  else {
    $media_queries = 1;
  }

  for ($media_count = 1; $media_count <= $media_queries; $media_count++) {
    // Do we really need to duplicate all of these vars.. or can they be set globally in $grid
    $base_grid_prefix = $grid["prefix{$media_count}"];
    $push_prefix = $base_grid_prefix . "push";
    $pull_prefix = $base_grid_prefix . "pull";
    $offset_prefix = $base_grid_prefix . "o";
    $sidebar_first_width = $grid["sidebar_first_width{$media_count}"];
    $sidebar_second_width = $grid["sidebar_second_width{$media_count}"];
    $content_width = ($grid["width{$media_count}"] - $sidebar_first_width) - $sidebar_second_width;
    $full_width = $grid["width{$media_count}"];
    $two_sidebar_width = $sidebar_first_width + $sidebar_second_width;;
    $sidebar1_content_width = $grid["sidebar_first_width{$media_count}"] + $content_width;
    $sidebar2_content_width = $grid["sidebar_second_width{$media_count}"] + $content_width;

    if (strpos($vars['region'], 'sidebar_first') === 0) {
      if (theme_get_setting("sidebar_layout{$media_count}") === '1') {
        $vars['content_attributes_array']['class'][] = $base_grid_prefix . "$sidebar_first_width " . $pull_prefix . "$content_width ";
      }
      if (theme_get_setting("sidebar_layout{$media_count}") === '2') {
        $vars['classes_array'][] = $offset_prefix . "$content_width ";
        $vars['content_attributes_array']['class'][] = $base_grid_prefix . "$sidebar_first_width " . $pull_prefix . "$content_width ";
      }
      if (theme_get_setting("sidebar_layout{$media_count}") === '3') {
        $vars['content_attributes_array']['class'][] = $base_grid_prefix . "$sidebar_first_width ";
      }
      if (theme_get_setting("sidebar_layout{$media_count}") === '4') {
        $vars['content_attributes_array']['class'][] = $base_grid_prefix . "$full_width ";
      }
    }

    if (strpos($vars['region'], 'sidebar_second') === 0) {
      $vars['content_attributes_array']['class'][] = $base_grid_prefix . "$sidebar_second_width ";
      if (theme_get_setting("sidebar_layout{$media_count}") === '1') {
        $vars['classes_array'][] = $offset_prefix . "$sidebar1_content_width ";
      }
      if (theme_get_setting("sidebar_layout{$media_count}") === '2') {
        $vars['classes_array'][] = $offset_prefix . "$sidebar1_content_width ";
        $vars['content_attributes_array']['class'][] = $pull_prefix . "$content_width ";
      }
      if (theme_get_setting("sidebar_layout{$media_count}") === '3') {
        $vars['content_attributes_array']['class'][] = $base_grid_prefix . "$sidebar_second_width ";
      }
      if (theme_get_setting("sidebar_layout{$media_count}") === '4') {
        $vars['content_attributes_array']['class'][] = $base_grid_prefix . "$full_width ";
      }
    }

    if (strpos($vars['region'], 'footer') === 0) {
      $vars['content_attributes_array']['class'][] = $base_grid_prefix . "$full_width ";
    }
  }
}

}


/**
 * Return a themed breadcrumb trail.
 *
 * @param $breadcrumb
 *   An array containing the breadcrumb links.
 * @return
 *   A string containing the breadcrumb output.
 */
function aether_breadcrumb($vars) {
  $breadcrumb = $vars['breadcrumb'];
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
      if (empty($vars['title'])) {
        $vars['title'] = t('You are here');
      }
      // Unless overridden by a preprocess function, make the heading invisible.
      if (!isset($vars['title_attributes_array']['class'])) {
        $vars['title_attributes_array']['class'][] = 'element-invisible';
      }

      // Build the breadcrumb trail.
      $output = '<nav class="breadcrumb" role="navigation">';
      $output .= '<h2' . drupal_attributes($vars['title_attributes_array']) . '>' . $vars['title'] . '</h2>';
      $output .= '<ul><li>' . implode($breadcrumb_separator . '</li><li>', $breadcrumb) . $trailing_separator . '</li></ul>';
      $output .= '</nav>';
    }
  }

  return $output;
}

/*
 * 	Converts a string to a suitable html ID attribute.
 *
 * 	 http://www.w3.org/TR/html4/struct/global.html#h-7.5.2 specifies what makes a
 * 	 valid ID attribute in HTML. This function:
 *
 * 	- Ensure an ID starts with an alpha character by optionally adding an 'n'.
 * 	- Replaces any character except A-Z, numbers, and underscores with dashes.
 * 	- Converts entire string to lowercase.
 *
 * 	@param $string
 * 	  The string
 * 	@return
 * 	  The converted string
 */
function aether_id_safe($string) {
  // Replace with dashes anything that isn't A-Z, numbers, dashes, or underscores.
  $string = strtolower(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $string));
  // If the first character is not a-z, add 'n' in front.
  if (!ctype_lower($string{0})) { // Don't use ctype_alpha since its locale aware.
    $string = 'id'. $string;
  }
  return $string;
}

/**
 * Generate the HTML output for a menu link and submenu.
 *
 * @param $vars
 *   An associative array containing:
 *   - element: Structured array data for a menu link.
 *
 * @return
 *   A themed HTML string.
 *
 * @ingroup themeable
 */
function aether_menu_link(array $vars) {
  $element = $vars['element'];
  $sub_menu = '';

  if ($element['#below']) {
    $sub_menu = drupal_render($element['#below']);
  }
  $output = l($element['#title'], $element['#href'], $element['#localized_options']);
  // Adding a class depending on the TITLE of the link (not constant)
  $element['#attributes']['class'][] = aether_id_safe($element['#title']);
  // Adding a class depending on the ID of the link (constant)
  $element['#attributes']['class'][] = 'mid-' . $element['#original_link']['mlid'];
  return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . $sub_menu . "</li>\n";
}

/**
 * Override or insert variables into theme_menu_local_task().
 */
function aether_preprocess_menu_local_task(&$vars) {
  $link =& $vars['element']['#link'];

  // If the link does not contain HTML already, check_plain() it now.
  // After we set 'html'=TRUE the link will not be sanitized by l().
  if (empty($link['localized_options']['html'])) {
    $link['title'] = check_plain($link['title']);
  }
  $link['localized_options']['html'] = TRUE;
  $link['title'] = '<span class="tab">' . $link['title'] . '</span>';
}

/*
 *  Duplicate of theme_menu_local_tasks() but adds clearfix to tabs.
 */
function aether_menu_local_tasks(&$vars) {
  $output = '';

  if (!empty($vars['primary'])) {
    $vars['primary']['#prefix'] = '<h2 class="element-invisible">' . t('Primary tabs') . '</h2>';
    $vars['primary']['#prefix'] .= '<ul class="tabs primary clearfix">';
    $vars['primary']['#suffix'] = '</ul>';
    $output .= drupal_render($vars['primary']);
  }
  if (!empty($vars['secondary'])) {
    $vars['secondary']['#prefix'] = '<h2 class="element-invisible">' . t('Secondary tabs') . '</h2>';
    $vars['secondary']['#prefix'] .= '<ul class="tabs secondary clearfix">';
    $vars['secondary']['#suffix'] = '</ul>';
    $output .= drupal_render($vars['secondary']);
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
function aether_image($vars) {
  $attributes = $vars['attributes'];
  $attributes['src'] = file_create_url($vars['path']);

  // remove width and height attributes
  foreach (array('alt', 'title') as $key) {
    if (isset($vars[$key])) {
      $attributes[$key] = $vars[$key];
    }
  }
  return '<img' . drupal_attributes($attributes) . ' />';
}
