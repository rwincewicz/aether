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

function aether_preprocess_html(&$variables, $hook) {
  // Add paths needed for html5shim.
  $variables['base_path'] = base_path();
  $variables['path_to_aether'] = drupal_get_path('theme', 'aether');
  $html5_respond_meta = theme_get_setting('aether_html5_respond_meta');
  $variables['add_respond_js']      = in_array('respond', $html5_respond_meta);
  $variables['add_html5_shim']      = in_array('html5', $html5_respond_meta);
  $variables['add_responsive_meta'] = in_array('meta', $html5_respond_meta);
  $variables['add_selectivizr_js']  = in_array('selectivizr', $html5_respond_meta);
  $variables['add_imgsizer_js']  = in_array('imgsizer', $html5_respond_meta);
  $variables['skip_link_anchor'] = theme_get_setting('aether_skip_link_anchor');
  $variables['skip_link_text'] = theme_get_setting('aether_skip_link_text');

  // Attributes for html element.
  $variables['html_attributes_array'] = array(
    'lang' => $variables['language']->language,
    'dir' => $variables['language']->dir,
  );
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
 * Implement hook_html_head_alter().
 */
function aether_html_head_alter(&$head) {
  // Simplify the meta tag for character encoding.
  $head['system_meta_content_type']['#attributes'] = array('charset' => str_replace('text/html; charset=', '', $head['system_meta_content_type']['#attributes']['content']));
}

/**
 * Custom theme functions
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
  static $grid;

  if (!isset($grid)) {
    $grid = array();
    $grid['prefix_h'] = substr(theme_get_setting('grid_handheld_prefix'), 0);
    $grid['prefix_hl'] = substr(theme_get_setting('grid_handheld_landscape_prefix'), 0);
    $grid['prefix_t'] = substr(theme_get_setting('grid_tablet_prefix'), 0);
    $grid['prefix_tl'] = substr(theme_get_setting('grid_tablet_landscape_prefix'), 0);
    $grid['prefix_d'] = substr(theme_get_setting('grid_desktop_prefix'), 0);
    $grid['name'] = substr(theme_get_setting('theme_grid'), 0, 7);
    $grid['type'] = substr(theme_get_setting('theme_grid'), 7);
    $grid['fixed'] = (substr(theme_get_setting('theme_grid'), 7) != 'fluid') ? TRUE : FALSE;
    $grid['width'] = (int)substr($grid['name'], 4, 2);
    $grid['sidebar_first_width'] = (aether_block_list('sidebar_first')) ? theme_get_setting('sidebar_first_width') : 0;
    $grid['sidebar_second_width'] = (aether_block_list('sidebar_second')) ? theme_get_setting('sidebar_second_width') : 0;
    $grid['regions'] = array();
    $regions = array_keys(system_region_list($theme_key, REGIONS_VISIBLE));
    $nested_regions = theme_get_setting('grid_nested_regions');
    $adjusted_regions = theme_get_setting('grid_adjusted_regions');
    foreach ($regions as $region) {
      $region_style = 'full-width';
      $region_width = $grid['width'];
      if ($region == 'sidebar_first' || $region == 'sidebar_second') {
        $region_width = ($region == 'sidebar_first') ? $grid['sidebar_first_width'] : $grid['sidebar_second_width'];
      }
      if ($nested_regions && in_array($region, $nested_regions)) {
        $region_style = 'nested';
        if ($adjusted_regions && in_array($region, array_keys($adjusted_regions))) {
          foreach ($adjusted_regions[$region] as $adjacent_region) {
            $region_width = $region_width - $grid[$adjacent_region . '_width'];
          }
        }
      }
      $grid['regions'][$region] = array('width' => $region_width, 'style' => $region_style, 'total' => count(aether_block_list($region)), 'count' => 0);
    }
  }
  return $grid;
}

function aether_preprocess_page(&$variables, $hook) {
  if (isset($variables['node_title'])) {
    $variables['title'] = $variables['node_title'];
  }
  // Adding a class to #page in wireframe mode
  if (theme_get_setting('wireframe_mode')) {
    $variables['classes_array'][] = 'wireframe-mode';
  }
  // Adding classes wether #navigation is here or not
  if (!empty($variables['main_menu']) or !empty($variables['sub_menu'])) {
    $variables['classes_array'][] = 'with-navigation';
  }
  if (!empty($variables['secondary_menu'])) {
    $variables['classes_array'][] = 'with-subnav';
  }
    // $content_width_d = theme_get_setting('content_width_d');
    // $variables['content_attributes_array']['class'][] = 'content-inner ' . 'g-d-'. $content_width_d;

  // Set grid width
  $grid = aether_grid_info();
  $variables['grid_width'] = $grid['prefix_d'] . $grid['width'];

  // Adjust width variables for nested grid groups
  $grid_adjusted_groups = (theme_get_setting('grid_adjusted_groups')) ? theme_get_setting('grid_adjusted_groups') : array();
  foreach (array_keys($grid_adjusted_groups) as $group) {
    $width = $grid['width'];
    foreach ($grid_adjusted_groups[$group] as $region) {
      $width = $width - $grid['regions'][$region]['width'];
    }
    // if (!$grid['fixed'] && isset($grid['fluid_adjustments'][$group])) {
    //   $variables[$group . '_width'] = '" style="width:' . $grid['fluid_adjustments'][$group] . '%"';
    // }
    // else {
      $variables[$group . '_width'] = $grid['prefix_d'] . $width;
    // }
  }

}

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
function aether_preprocess_comment(&$variables, $hook) {
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

  // Initialize grid info once per page
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


  // Set region variables
  $variables['region_style'] = $variables['fluid_width'] = '';
  $variables['region_name'] = str_replace('_', '-', $variables['region']);
  $variables['classes_array'][] = $variables['region_name'];
  if (in_array($variables['region'], array_keys($grid['regions']))) {
    // Set region full-width or nested style
    $variables['region_style'] = $grid['regions'][$variables['region']]['style'];
    $variables['classes_array'][] = ($variables['region_style'] == 'nested') ? $variables['region_style'] : '';
    $variables['content_attributes_array']['class'][] = $grid['prefix_d'] . $grid['regions'][$variables['region']]['width'];
    // Adjust & set region width
    if (!$grid['fixed'] && isset($grid['fluid_adjustments'][$variables['region']])) {
      $variables['fluid_width'] = ' style="width:' . $grid['fluid_adjustments'][$variables['region']] . '%"';
    }
  }
  // Sidebar regions receive common class, "sidebar".
  $sidebar_regions = array('sidebar_first', 'sidebar_second');
  if (in_array($variables['region'], $sidebar_regions)) {
    $variables['classes_array'][] = 'sidebar';
  }


  // if (strpos($variables['region'], 'sidebar_first') === 0) {
  //   $sidebar_first_width_d = theme_get_setting('sidebar_first_width_d');
  //   $variables['content_attributes_array']['class'][] = 'g-d-'. $sidebar_first_width_d;
  // }
  // if (strpos($variables['region'], 'sidebar_second') === 0) {
  //   $sidebar_second_width_d = theme_get_setting('sidebar_second_width_d');
  //   $variables['content_attributes_array']['class'][] = 'g-d-'. $sidebar_second_width_d;
  // }
}


/**
 * Return a themed breadcrumb trail.
 *
 * @param $breadcrumb
 *   An array containing the breadcrumb links.
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
 * @param $variables
 *   An associative array containing:
 *   - element: Structured array data for a menu link.
 *
 * @return
 *   A themed HTML string.
 *
 * @ingroup themeable
 */

function aether_menu_link(array $variables) {
  $element = $variables['element'];
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
function aether_preprocess_menu_local_task(&$variables) {
  $link =& $variables['element']['#link'];

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
 * Make drupal core generated images responsive i.e. flexible in width
 */
function aether_image($variables) {
  $attributes = $variables['attributes'];
  $attributes['src'] = file_create_url($variables['path']);

  // remove width and height attributes
  foreach (array('alt', 'title') as $key) {

    if (isset($variables[$key])) {
      $attributes[$key] = $variables[$key];
    }
  }

  return '<img' . drupal_attributes($attributes) . ' />';
}
