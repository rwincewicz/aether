<?php

/**
 * @file
 * Theme settings for regions.
 */

/**
 * @todo
 */
function aether_theme_settings_structure(&$form, &$form_state) {
  $theme = aether_get_theme();
  $containers = isset($theme->grids[$theme->settings['grid']]) ? aether_container_options($theme->grids[$theme->settings['grid']]) : array();
  $options = aether_zone_options($theme->zones);
  $columns = $spacing = !empty($containers) ? aether_column_options(max(array_keys($containers))) : array();  
  
  unset($columns[0]);
  array_pop($spacing);
  
  $form['aether_settings']['structure'] = array(
    '#type' => 'fieldset',
    '#title' => t('Zone and region configuration'),
    '#weight' => -8,
  );
  
  $form['aether_settings']['structure']['__unassigned__'] = array(
    '#type' => 'fieldset',
    '#title' => t('Unassigned zones'),
    '#description' => t('There are no unassigned zones.'),
    '#weight' => 100,
    '#attributes' => array(
      'class' => array('aether-unassigned'),
    ),
  );
  
  $form['aether_settings']['structure']['__unassigned__']['__unassigned__']['regions'] = array(
    '#type' => 'fieldset',
    '#title' => t('Unassigned regions'),
    '#description' => t('There are no unassigned regions.'),
    '#weight' => 100,
    '#attributes' => array(
      'class' => array('aether-unassigned'),
    ),
  );
  
  foreach ($theme->sections as $section => $name) {
    $form['aether_settings']['structure'][$section] = array(
      '#type' => 'fieldset',
      '#title' => $name . ' ' . t('Section'),
      '#description' => t('This section is empty.'),
    );
  }
  
  foreach ($theme->zones as $zone => $item) {
    $section = $item['enabled'] ? $item['section'] : '__unassigned__';
    
    unset($form['aether_settings']['structure'][$section]['#description']);
    
    $form['aether_settings']['structure'][$section][$zone] = array(
      '#type' => 'fieldset',
      '#title' => $item['name'] . ' ' . t('Zone'),
      '#weight' => $item['weight'],
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    
    $form['aether_settings']['structure'][$section][$zone]['zone'] = array(
      '#type' => 'fieldset',
      '#title' => t('Configuration'),
      '#weight' => -999,
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#attributes' => array(
        'class' => array('aether-inline'),
      ),
    );
    
    // Provide a full width wrapper around the zone (allowing for design elements outside the grid)
    $form['aether_settings']['structure'][$section][$zone]['zone']['aether_zone_' . $zone . '_wrapper'] = array(
      '#type' => 'checkbox',
      '#title' => t('Provide full width wrapper around this zone'),
      '#description' => t('Enabling this feature will give a &lt;div&gt; wrapper around the zone itself, allowing you to theme in elements that appear outside the 960 pixel container zone.'),
      '#default_value' => $item['wrapper'],
    );
    
    $form['aether_settings']['structure'][$section][$zone]['zone']['aether_zone_' . $zone . '_force'] = array(
      '#type' => 'checkbox',
      '#title' => t('Force this zone to be rendered'),
      '#description' => t('Enabling this will always render this zone, even if it is empty.'),
      '#default_value' => $item['force'],
    );
    
    $form['aether_settings']['structure'][$section][$zone]['zone']['aether_zone_' . $zone . '_order'] = array(
      '#type' => 'checkbox',
      '#title' => t('Customize the region positioning'),
      '#description' => t('This allows you to manipulate the placing of the regions in this zone.'),
      '#default_value' => $item['order'],
      '#element_validate' => array('aether_theme_settings_validate_order'),
      '#zone' => $zone,
    );
    
    $form['aether_settings']['structure'][$section][$zone]['zone']['aether_zone_' . $zone . '_section'] = array(
      '#type' => 'select',
      '#title' => t('Section'),
      '#default_value' => $item['section'],
      '#element_validate' => array('aether_theme_settings_validate_not_empty'),
      '#options' => array('_none' => '- None -') + $theme->sections,
    );
    
    $form['aether_settings']['structure'][$section][$zone]['zone']['aether_zone_' . $zone . '_weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight'),
      '#default_value' => $item['weight'],
    );
          
    // Create a container width selection menu for EACH zone
    $form['aether_settings']['structure'][$section][$zone]['zone']['aether_zone_' . $zone . '_columns'] = array(
      '#type' => 'select',
      '#title' => t('Column count'),
      '#default_value' => $item['columns'],
      '#options' => $containers,
    );
    
    // Decide which region is the primary item.
    // The primary region is the one that will absorb the size of empty regions that are related in the same zone.
    $form['aether_settings']['structure'][$section][$zone]['zone']['aether_zone_' . $zone . '_primary'] = array(
      '#type' => 'select',
      '#title' => t('Primary Region'),
      '#default_value' => $item['primary'],
      '#options' => array('_none' => t('- None -')) + aether_zone_regions($zone, $theme->regions),
      '#element_validate' => array('aether_theme_settings_validate_not_empty', 'aether_theme_settings_validate_primary'),
      '#zone' => $zone,
    );
    
    // Allow for zone classes
    $form['aether_settings']['structure'][$section][$zone]['zone']['aether_zone_' . $zone . '_css'] = array(
      '#type' => 'textfield',
      '#title' => t('Additional zone classes'),
      '#size' => 60,
      '#default_value' => $item['css'],
    );

    // Allow for zone wrapper classes
    $form['aether_settings']['structure'][$section][$zone]['zone']['aether_zone_' . $zone . '_wrapper_css'] = array(
      '#type' => 'textfield',
      '#title' => t('Additional wrapper classes'),
      '#size' => 60,
      '#default_value' => $item['wrapper_css'],
      '#states' => array(
        'visible' => array(
          ':input[name="aether_zone_' . $zone . '_wrapper"]' => array('checked' => TRUE),
        ),
      ),
    );
    
    $form['aether_settings']['structure'][$section][$zone]['regions'] = array(
      '#type' => 'fieldset',
      '#title' => t('Regions'),
      '#description' => t('This zone is empty.'),
      '#weight' => $item['weight'],
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
  }
  
  foreach($theme->regions as $region => $item) {
    $zone = $item['enabled'] ? $item['zone'] : '__unassigned__';
    $section = $item['enabled'] && $theme->zones[$item['zone']]['enabled'] ? $item['section'] : '__unassigned__';
    
    unset($form['aether_settings']['structure'][$section][$zone]['regions']['#description']);

    $form['aether_settings']['structure'][$section][$zone]['regions'][$region] = array(
      '#type' => 'fieldset',
      '#title' => $item['name'],
      '#weight' => $item['weight'],
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#attributes' => array(
        'class' => array('aether-inline'),
      ),
    );
    
    $form['aether_settings']['structure'][$section][$zone]['regions'][$region]['aether_region_' . $region . '_force'] = array(
      '#type' => 'checkbox',
      '#title' => t('Force this region to be rendered'),
      '#description' => t('Enabling this will always render this region, even if it is empty.'),
      '#default_value' => $item['force'],
    );
      
    $form['aether_settings']['structure'][$section][$zone]['regions'][$region]['aether_region_' . $region . '_zone'] = array(
      '#type' => 'select',
      '#title' => t('Zone'),
      '#default_value' => !empty($item['zone']) ? $item['zone'] : array('_none'),
      '#element_validate' => array('aether_theme_settings_validate_not_empty'),
      '#options' => array('_none' => '- None -') + $options,
    );
    
    $form['aether_settings']['structure'][$section][$zone]['regions'][$region]['aether_region_' . $region . '_prefix'] = array(
      '#type' => 'select',
      '#title' => t('Prefix'),
      '#default_value' => $item['prefix'],
      '#options' => $spacing,
    );
    
    $form['aether_settings']['structure'][$section][$zone]['regions'][$region]['aether_region_' . $region . '_columns'] = array(
      '#type' => 'select',
      '#title' => t('Width'),
      '#default_value' => $item['columns'],
      '#options' => $columns,
    );
    
    $form['aether_settings']['structure'][$section][$zone]['regions'][$region]['aether_region_' . $region . '_suffix'] = array(
      '#type' => 'select',
      '#title' => t('Suffix'),
      '#default_value' => $item['suffix'],
      '#options' => $spacing,
    );
    
    $form['aether_settings']['structure'][$section][$zone]['regions'][$region]['aether_region_' . $region . '_weight'] = array(
      '#type' => 'weight',
      '#delta' => 50,
      '#title' => t('Weight'),
      '#default_value' => $item['weight'],
    );
    
    $form['aether_settings']['structure'][$section][$zone]['regions'][$region]['aether_region_' . $region . '_position'] = array(
      '#type' => 'weight',
      '#delta' => 50,
      '#title' => t('Position'),
      '#default_value' => $item['position'],
      '#states' => array(
        'visible' => array(
          ':input[name="aether_zone_' . $zone . '_order"]' => array('checked' => TRUE),
        ),
      ),
    );
    
    $form['aether_settings']['structure'][$section][$zone]['regions'][$region]['aether_region_' . $region . '_css'] = array(
      '#type' => 'textfield',
      '#title' => t('Additional region classes'),
      '#size' => 50,
      '#default_value' => $item['wrapper_css'],
    );
  }
}