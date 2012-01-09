<?php
/**
 * @file
 * Exposes a number of core Drupal elements as blocks.
 */

/**
 * Implements hook_theme().
 */
function aether_theme() {
  return array(
    'grid_block' => array(
      'vars' => array('content' => NULL, 'id' => NULL),
    ),
    'logo' => array(
      'vars' => array('logo_path' => NULL),
    ),
    'site_name' => array(
      'vars' => array('site_name' => NULL),
    ),
    'site_slogan' => array(
      'vars' => array('site_slogan' => NULL),
    ),
  );
}
/**
 * Implements hook_block_info().
 */
function aether_block_info() {
  $block_list = _aether_get_blocks();
  foreach ($block_list as $delta => $name) {
    if (_aether_is_enabled($delta)) {
      $blocks[$delta] = array(
        'info' => $name,
        'cache' => DRUPAL_CACHE_GLOBAL,
      );
    }
  }

  if (empty($blocks)) {
    $blocks = array();
  }
  return $blocks;
}

/**
 * Implements hook_block_view().
 */
function aether_block_view($delta = '') {
  $blocks = aether_get_blocks();
  foreach ($blocks as $block_delta => $block_name) {
    if ($delta == $block_delta && aether_is_enabled($block_delta)) {
      return array(
        'subject' => '',
        'content' => aether_get_content($delta),
      );
    }
  }
}

/**
 * Provides individual block content.
 */
function aether_get_content($delta) {
  $vars = array();

  switch ($delta) {
    case 'aether-logo':
      $vars['logo_path'] = theme_get_setting('logo');
      return theme('logo', $vars);

    case 'aether-site-name':
      $vars['site_name'] = filter_xss_admin(variable_get('site_name', 'Drupal'));
      return theme('site_name', $vars);

    case 'aether-site-slogan':
      $vars['site_slogan'] = filter_xss_admin(variable_get('site_slogan', ''));
      return theme('site_slogan', $vars);
  }
}

/**
 * Returns a list of aether blocks.
 */
function aether_get_blocks($enabled_blocks_only = FALSE) {
  return array(
    'logo' => t('Logo'),
    'site-name' => t('Site name'),
    'site-slogan' => t('Site slogan'),
  );
}

/**
 * Verify if a given aether block is enabled in the aether admin settings.
 */
function aether_is_enabled($delta) {
  $blocks = variable_get('aether_blocks', array());
  if (!empty($blocks[$delta])) {
    return TRUE;
  }
  return FALSE;
}

/**
 * Implements hook_menu_contextual_links_alter().
 */
function aether_menu_contextual_links_alter(&$links, $router_item, $root_path) {
  $block_id = array_pop($router_item['map']);

  $site_information_pages = array(
    'site-slogan',
    'site-name',
  );

  if (in_array($block_id, $site_information_pages)) {
    $links['site-information'] = array(
      'title' => t('Site information'),
      'href' => 'admin/config/system/site-information',
      'localized_options' => array(),
    );
  }

  if ($block_id == 'aether-logo') {
    $links['logo-settings'] = array(
      'title' => t('Logo settings'),
      'href' => 'admin/appearance/settings/' . $GLOBALS['theme'],
      'localized_options' => array('fragment' => 'edit-logo'),
    );
  }
}
