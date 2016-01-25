<?php
/**
 * Implements theme_preprocess_html.
 */
function expressbase_preprocess_html(&$vars) {
  // Add web fonts from fonts.com
  $element = array(
    '#tag' => 'link', // The #tag is the html tag - <link />
    '#attributes' => array( // Set up an array of attributes inside the tag
      'href' => '//fast.fonts.net/cssapi/86696b99-fb1a-4964-9676-9233fb4fca8f.css',
      'rel' => 'stylesheet',
      'type' => 'text/css',
    ),
  );
  // Don't include web fonts if variable is false
  if (variable_get('use_fonts', TRUE)) {
    drupal_add_html_head($element, 'web_fonts');
  }
  // Turn off IE Compatibility Mode
  $element = array(
    '#tag' => 'meta',
    '#attributes' => array(
      'http-equiv' => 'X-UA-Compatible',
      'content' => 'IE=edge',
    ),
  );
  drupal_add_html_head($element, 'ie_compatibility_mode');

  // Add campus name to title
  $vars['head_title_array']['slogan'] = 'University of Colorado Boulder';
  $vars['head_title'] = implode(' | ', $vars['head_title_array']);

  // set classes for theme configs
  $headings = theme_get_setting('headings') ? theme_get_setting('headings') : 'headings-bold';
  $vars['classes_array'][]=$headings;
  $page_title_image_background = theme_get_setting('page_title_image_background') ? theme_get_setting('page_title_image_background') : 'page-title-image-background-white';
  $vars['classes_array'][]=$page_title_image_background;
  $icon_color = theme_get_setting('block_icons_color') ? theme_get_setting('block_icons_color') : 'block-icons-inherit';
  $vars['classes_array'][]=$icon_color;

  // Attributes for html element.
  $vars['html_attributes_array'] = array(
    'lang' => $vars['language']->language,
    'dir' => $vars['language']->dir,
  );
  $vars['html_attributes'] = drupal_attributes($vars['html_attributes_array']);

  // Add variables and paths needed for HTML5 and responsive support.
  $vars['base_path'] = base_path();
  $vars['path_to_expressbase'] = drupal_get_path('theme', 'expressbase');

  // Add responsive, max width classes
  if (theme_get_setting('alpha_responsive')) {
    $vars['classes_array'][] = 'layout-responsive';
    $vars['classes_array'][] = 'max-width-1200';
  } else {
    $vars['classes_array'][] = 'layout-fixed';
    $vars['classes_array'][] = 'max-width-960';
  }
  // Add primary sidebar class
  $vars['classes_array'][] = theme_get_setting('primary_sidebar') ? theme_get_setting('primary_sidebar') : 'primary-sidebar-second';

  // Add focus js
  drupal_add_js(drupal_get_path('theme','expressbase') .'/js/track-focus.js', array('scope' => 'footer'));

  // Set skip to link
  $vars['skip_link_anchor'] = 'main';
  $vars['skip_link_text'] = t('Skip to Content');
}

/**
 * Implements theme_page_alter();
 * Force regions to render even if empty
 */
function expressbase_page_alter(&$page) {

  // Remove the wrapper from the main content block.
  if (!empty($page['content']['system_main'])) {
    $page['content']['system_main']['#theme_wrappers'] = array_diff($page['content']['system_main']['#theme_wrappers'], array('block'));
  }
  // Force these regions to print
  $regions = array('branding', 'secondary_menu', 'menu', 'site_info');
  foreach ($regions as $region) {
    if ( !isset($page[$region]) || empty($page[$region])) {
      $page[$region] = array(
        '#region' => $region,
        '#weight' => '-10',
        '#theme_wrappers' => array('region'),
      );
    }
  }
}

/**
 * Implements theme_preprocess_page().
 */
function expressbase_preprocess_page(&$vars) {
  global $base_url;
  // Set site slogan so it can't be overriden
  $vars['site_slogan'] = 'University of Colorado <strong>Boulder</strong>';
  // add print logo
  $vars['print_logo'] = '<img src="' . $base_url . '/' . drupal_get_path('theme','expressbase') . '/images/print-logo.png" alt="University of Colorado Boulder" />';
  // hide title on homepage
  if($vars['is_front'] == TRUE) {
    $vars['title_hidden'] = TRUE;
  }
  // add home icon to footer menu if needed
  expressbase_home_icon($vars, 'footer_menu');
  // add footer menu color config
  $vars['footer_menu_color'] = theme_get_setting('footer_menu_color') ? theme_get_setting('footer_menu_color') : 'footer-menu-gray';
  if ($node = menu_get_object()) {
    $status = ($node->status == 1) ? 'published' : 'unpublished';
    $vars['classes_array'][] = 'node-' . $status;
  }
  // content and sidebar grid classes
  $primary_sidebar = theme_get_setting('primary_sidebar');
  $main_content_classes = array();
  $sidebar_first_classes = array();
  $sidebar_second_classes = array();
  // Two Sidebars
  if (!empty($vars['page']['sidebar_first']) && !empty($vars['page']['sidebar_second'])) {
    // Main content 6 cols on desktop, 8 on tablet, 12 on mobile
    $main_content_classes[] = 'col-lg-6 col-md-6 col-sm-8 col-xs-12 col-xs-push-0';
    // Main content pushed 3 on desktop, pushed 0 on mobile
    $main_content_classes[] = 'col-lg-push-3 col-md-push-3 col-xs-push-0';
    // Sidebar first 3 cols on dekstop, 12 on mobile
    $sidebar_first_classes[] = 'col-lg-3 col-md-3 col-xs-12';
    // Sidebar first 3 cols on dekstop, 12 on mobile
    $sidebar_second_classes[] = 'col-lg-3 col-md-3 col-xs-12';

    // Add additional classes depending on primary sidebar
    if ($primary_sidebar == 'primary-sidebar-first') {
      // Push main content on tablet to make room for left sidebar
      $main_content_classes[] = 'col-sm-push-4';
      // Sidebar first 4 colums on tablet
      $sidebar_first_classes[] = 'col-sm-4';
      // Pull sidebar to be on left
      $sidebar_first_classes[] = 'col-lg-pull-6 col-md-pull-6 col-sm-pull-8';
      // Sidebar second 4 columns on tablet
      $sidebar_second_classes[] = 'col-sm-4';
      // Pull right sidebar to be on left when viewed on a tablet
      $sidebar_second_classes[] = 'col-sm-pull-8';
      // Reset pull of right column for desktop
      $sidebar_second_classes[] = 'col-lg-pull-0 col-md-pull-0';
    } else {
      // Set sidebars to 4 columns on tablet
      $sidebar_first_classes[] = 'col-sm-4';
      $sidebar_second_classes[] = 'col-sm-4';
      // Pull left sidebar for desktop
      $sidebar_first_classes[] = 'col-lg-pull-9 col-md-pull-9';
      // Push right sidebar for desktop
      $sidebar_second_classes[] = 'col-lg-push-3 col-md-push-3';
    }
  }
  // Only first/left sidebar
  elseif (!empty($vars['page']['sidebar_first']) && empty($vars['page']['sidebar_second'])) {
    // Main content 8 columns on desktop, tablet, 12 columns on mobile
    $main_content_classes[] = 'col-lg-8 col-md-8 col-sm-8 col-xs-12 col-xs-push-0';
    // Push main content to make room for left sidebar
    $main_content_classes[] = 'col-lg-push-4 col-md-push-4 col-sm-push-4';
    // Set sidebar to 4 cols on desktop, tablet, 12 on mobile
    $sidebar_first_classes[] = 'col-lg-4 col-md-4 col-sm-4 col-xs-12';
    // Pull sidebar to left column on desktop and tablet
    $sidebar_first_classes[] = 'col-lg-pull-8 col-md-pull-8 col-sm-pull-8';
  }
  // Only second/right sidebar
  elseif (empty($vars['page']['sidebar_first']) && !empty($vars['page']['sidebar_second'])) {
    // Main content 8 columns on desktop, tablet, 12 columns on mobile
    $main_content_classes[] = 'col-lg-8 col-md-8 col-sm-8 col-xs-12 col-xs-push-0';
    // Set sidebar to 4 cols on desktop, tablet, 12 on mobile
    $sidebar_second_classes[] = 'col-lg-4 col-md-4 col-sm-4 col-xs-12';
  }
  // No sidebars
  else {
    // Set main content to 12 columns on desktop, tablet, mobile
    $main_content_classes[] = 'col-lg-12 col-md-12 col-sm-12 col-xs-12';
  }
  $vars['main_content_classes'] = join(' ', $main_content_classes);
  $vars['sidebar_first_classes'] = join(' ', $sidebar_first_classes);
  $vars['sidebar_second_classes'] = join(' ', $sidebar_second_classes);
}

/**
 * Implements theme_image_style().
 */
function expressbase_image_style(&$vars) {
  // Determine the dimensions of the styled image.
  $dimensions = array(
    'width' => $vars['width'],
    'height' => $vars['height'],
  );
  image_style_transform_dimensions($vars['style_name'], $dimensions);
  $vars['width'] = $dimensions['width'];
  $vars['height'] = $dimensions['height'];
  // Determine the url for the styled image.
  $vars['path'] = image_style_url($vars['style_name'], $vars['path']);
  $vars['attributes']['class'] = array('image-' . $vars['style_name']);
  return theme('image', $vars);
}

/**
 * Implements theme_breadcrumb().
 */
function expressbase_breadcrumb($vars) {
  $breadcrumb = $vars['breadcrumb'];
  if (!empty($breadcrumb)) {
    // Replace the Home breadcrumb with a Home icon
    //$breadcrumb[0] = str_replace('Home','<i class="fa fa-home"></i> <span class="home-breadcrumb element-invisible">Home</span>',$breadcrumb[0]);
    // Get current page title and add to breadcrumb array
    $breadcrumb[] = drupal_get_title();
    // Provide a navigational heading to give context for breadcrumb links to
    // screen-reader users. Make the heading invisible with .element-invisible.
    $output = '<h2 class="element-invisible">' . t('You are here') . '</h2>';
    $output .= '<div class="breadcrumb">' . implode(' <i class="fa fa-angle-right"></i> ', $breadcrumb) . '</div>';
    return $output;
  }
}

/**
 * Implements theme_preprocess_node().
 */
function expressbase_preprocess_node(&$vars) {
  // Custom display templates will be called node--[type]--[viewmode].tpl.php
  $vars['theme_hook_suggestions'][] = 'node__' . $vars['type'] . '__' . $vars['view_mode'];

  // Making comments appear at the bottom of $content
  $vars['content']['comments']['#weight'] = 1000;
  
  // Don't print link variables
  unset($vars['content']['links']);
}

/**
 * Implements theme_preprocess_region().
 */
function expressbase_preprocess_region(&$vars) {
  global $base_url;
  // add classes to regions and blocks for column configuration
  $columns = array(
    '1' => 'region-one-column',
    '2' => 'region-two-columns',
    '3' => 'region-three-columns',
    '4' => 'region-four-columns',
    '5' => 'region-five-columns',
    '6' => 'region-six-columns',
  );
  // Get column theme settings
  $after_content_columns = theme_get_setting('after_content_columns') ? theme_get_setting('after_content_columns') : '1';
  $lower_columns = theme_get_setting('lower_columns') ? theme_get_setting('lower_columns') : '1';
  $footer_columns = theme_get_setting('footer_columns') ? theme_get_setting('footer_columns') : '1';
  // Add variables to regions
  switch ($vars['region']) {
    case 'branding':
      $vars['logo'] = theme_get_setting('logo');
      $vars['front_page'] = url('<front>');

      if (variable_get('site_name_2', '')) {
        $vars['site_name'] = '<span class="site-name-two-lines">' . variable_get('site_name_1', NULL) . '<br />' . variable_get('site_name_2', NULL) . '</span>';
      } else {
        $vars['site_name'] = variable_get('site_name', NULL);
      }

      $vars['site_slogan'] = 'University of Colorado <strong>Boulder</strong>';
      $vars['print_logo'] = '<img src="' . $base_url . '/' . drupal_get_path('theme','expressbase') . '/images/print-logo.png" alt="University of Colorado Boulder" />';

      break;
    case 'secondary_menu':
      $vars['secondary_menu'] = menu_secondary_menu();
      $vars['secondary_menu_heading'] = theme_get_setting('secondary_menu_label') ? theme_get_setting('secondary_menu_label') : '';
      break;
    case 'menu':
      $vars['main_menu'] = menu_main_menu();
      expressbase_home_icon($vars, 'main_menu');
      $vars['secondary_menu'] = menu_secondary_menu();
      if (theme_get_setting('use_action_menu')) {
        $color = theme_get_setting('action_menu_color') ? theme_get_setting('action_menu_color') : 'action-blue';
        $vars['classes_array'][] = $color;
      }
      break;
    case 'after_content':
      $vars['classes_array'][] = $columns[$after_content_columns];
      $vars['classes_array'][] = 'block-column-container';
      break;
    case 'lower':
      $vars['classes_array'][] = $columns[$lower_columns];
      $vars['classes_array'][] = 'block-column-container';
      break;
    case 'footer':
      $vars['classes_array'][] = $columns[$footer_columns];
      $vars['classes_array'][] = 'block-column-container';
      break;
    case 'site_info':
      $vars['base_url'] = $base_url;
      $vars['beboulder']['color'] = 'white';
      $vars['classes_array'][] = !empty($vars['content']) ? 'footer-2col' : 'footer-1col';
      break;
    case 'sidebar_first':
      //$vars['classes_array'][] = 'sidebar';
      //$vars['classes_array'][] = 'col-lg-4 col-md-4';
      break;
    case 'sidebar_second':
      //$vars['classes_array'][] = 'sidebar';
      //$vars['classes_array'][] = 'col-lg-4 col-md-4';
      break;
    case 'content':
      break;
  }
}

/**
 * Implements hook_preprocess_block).
 */
function expressbase_preprocess_block(&$vars) {
  // Add class for block titles
  $vars['title_attributes_array']['class'][] = 'block-title';
  // If the block is a bean, add bundle as a class
  if ($vars['block']->module == 'bean') {
    // Get the bean elements.
    if (isset($vars['elements']['bean'])) {
      $beans = $vars['elements']['bean'];
      // There is only 1 bean per block.
      $children = element_children($beans);
      $bean = $beans[current(element_children($beans))];
      // Add bean type classes to the parent block.
      $vars['classes_array'][] = drupal_html_class('block-bean-type-' . $bean['#bundle']);
    }
  }
  if(isset($vars['elements']['content']) && isset($vars['elements']['content']['bean'])) {
    $bean = $vars['elements']['content']['bean'];
    $children = array_intersect_key($bean, array_flip(element_children($bean)));
    $the_bean = current($children);
    $bean_type = $the_bean['#bundle'];
    $vars['classes_array'][] = 'bean-type-' . $bean_type;
  }
  // Get region column settings
  if (theme_get_setting('after_content_columns')) {
    $after_content_columns = theme_get_setting('after_content_columns') ? theme_get_setting('after_content_columns') : 1;
  }
  if (theme_get_setting('lower_columns')) {
    $lower_columns = theme_get_setting('lower_columns') ? theme_get_setting('lower_columns') : 1;
  }
  if (theme_get_setting('footer_columns')) {
    $footer_columns = theme_get_setting('footer_columns') ? theme_get_setting('footer_columns') : 1;
  }

  // Add column classes to blocks
  $classes = expressbase_size_column_classes();
  switch ($vars['block']->region) {
    case 'after_content':
      $vars['classes_array'][] = 'col-lg-' . 12/$after_content_columns;
      $vars['classes_array'][] = 'col-md-' . 12/$after_content_columns;
      $vars['classes_array'][] = 'col-sm-' . 12/$after_content_columns;
      $vars['classes_array'][] = 'col-xs-12';
      break;
    case 'lower':
      $vars['classes_array'][] = 'col-lg-' . 12/$lower_columns;
      $vars['classes_array'][] = 'col-md-' . 12/$lower_columns;
      $vars['classes_array'][] = 'col-sm-' . 12/$lower_columns;
      $vars['classes_array'][] = 'col-xs-12';
      break;
    case 'footer':
      //$vars['classes_array'][] = 'col-lg-' . 12/$footer_columns;
      //$vars['classes_array'][] = 'col-md-' . 12/$footer_columns;
      //$vars['classes_array'][] = 'col-sm-' . 12/$footer_columns;
      //$vars['classes_array'][] = 'col-xs-12';

      $vars['classes_array'][] = $classes['xs'][$footer_columns];
      $vars['classes_array'][] = $classes['sm'][$footer_columns];
      $vars['classes_array'][] = $classes['md'][$footer_columns];
      $vars['classes_array'][] = $classes['lg'][$footer_columns];
      break;
  }
}

/**
 * Override or insert variables into the block templates.
 */
function expressbase_process_block(&$vars) {
  // Drupal 7 should use a $title variable instead of $block->subject.
  $vars['title'] = isset($vars['block']->subject) ? $vars['block']->subject : '';
}

/**
 * Implements theme_menu_link().
 * adds icons, allows hiding of menu text for icon only navigation
 * This is for sidebar navigation
 */
function expressbase_menu_link(array $vars) {
  $element = $vars['element'];
  if (isset($element['#localized_options']['icon'])&& strlen($element['#localized_options']['icon']) > 3 ) {
    $element['#localized_options']['html'] = TRUE;
    $hide = isset($element['#localized_options']['hide_text']) ? $element['#localized_options']['hide_text'] : 0;
    $hide_class = $hide ? 'hide-text' : '';
    $space = $hide ? '' : ' ';
    $element['#title'] = '<i class="fa fa-fw ' . $element['#localized_options']['icon'] . '"></i>' . $space . '<span class="menu-icon-text ' . $hide_class . '">' . $element['#title']  . '</span>';
  }
  $sub_menu = '';
  if ($element['#below']) {
    $sub_menu = drupal_render($element['#below']);
  }
  $output = l($element['#title'], $element['#href'], $element['#localized_options']);
  return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . $sub_menu . "</li>\n";
}
/**
 * Implements theme_links__system_main_menu().
 * Markup/Classes for main menu
 */
function expressbase_links__system_main_menu($vars) {
  $classes = join(' ',$vars['attributes']['class']);
  $html = '  <ul class="' . $classes . '" id="' . $vars['attributes']['id'] . '">';

  // Add first and last classes to first and last list items
  reset($vars['links']);
  $first = key($vars['links']);
  end($vars['links']);
  $last = key($vars['links']);
  reset($vars['links']);

  $vars['links'][$first]['attributes']['class'][] = 'first';
  $vars['links'][$last]['attributes']['class'][] = 'last';
  foreach ($vars['links'] as $link) {
    $classes = '';
    if (!empty($link['attributes']['class'])) {
      $classes = join(' ', $link['attributes']['class']);
    }
    if (isset($link['icon']) && strlen($link['icon']) > 3) {
      $link['html'] = TRUE;
      $hide = isset($link['hide_text']) ? $link['hide_text'] : 0;
      $hide_class = $hide ? 'hide-text' : '';
      $space = $hide ? '' : ' ';
      $title = '<i class="fa fa-fw '. $link['icon'] . '"></i>' . $space . '<span class="' . $hide_class . '">' . $link['title'] . '</span>';
      $html .= '<li class="' . $classes .'">'.l($title, $link['href'], $link).'</li>';
    }
    else if (isset($link['title']) && isset($link['href'])) {
      $html .= '<li class="' . $classes .'">'.l($link['title'], $link['href'], $link).'</li>';
    }
  }
  $html .= "  </ul>";
  return $html;
}
/**
 * Implements theme_links__system_secondary_menu().
 * Markup/Classes for secondary menu
 */
function expressbase_links__system_secondary_menu($vars) {
  // Prepare label - set by more_menus.module
  $classes = join(' ',$vars['attributes']['class']);
  $html = '  <ul class="' . $classes . '">';
  $label = variable_get('secondary_menu_label') ? '<h2 class="inline secondary-menu-label">' . variable_get('secondary_menu_label') . '</h2>': '';
  if (theme_get_setting('use_action_menu') && !isset($vars['mobile'])) {
    $html = '  <ul id="action-menu" class="' . $classes . '">';
  } else {
    $html = $label . '  <ul class="' . $classes . '">';
  }

  // Add first and last classes to first and last list items
  reset($vars['links']);
  $first = key($vars['links']);
  end($vars['links']);
  $last = key($vars['links']);
  reset($vars['links']);
  $vars['links'][$first]['attributes']['class'][] = 'first';
  $vars['links'][$last]['attributes']['class'][] = 'last';

  foreach ($vars['links'] as $link) {
    $classes = '';
    if (!empty($link['attributes']['class'])) {
      $classes = join(' ', $link['attributes']['class']);
    }
    if (isset($link['icon']) && strlen($link['icon']) > 3) {
      $link['html'] = TRUE;
      $hide = isset($link['hide_text']) ? $link['hide_text'] : 0;
      $hide_class = $hide ? 'hide-text' : '';
      $space = $hide ? '' : ' ';
      $title = '<i class="fa fa-fw '. $link['icon'] . '"></i>' . $space . '<span class="' . $hide_class . '">' . $link['title'] . '</span>';
      $html .= '<li class="' . $classes .'">'.l($title, $link['href'], $link).'</li>';
    }
    else if (isset($link['title']) && isset($link['href'])) {
      $html .= '<li class="' . $classes .'">'.l($link['title'], $link['href'], $link).'</li>';
    }
  }
  $html .= "  </ul>";
  return $html;
}
/**
 * Implements theme_links__footer_menu().
 * Markup/Classes for footer menu
 */
function expressbase_links__footer_menu($vars) {
  $classes = join(' ',$vars['attributes']['class']);
  $html = '<ul id="footer-menu-links" class="' . $classes . '">';

  // Add first and last classes to first and last list items
  reset($vars['links']);
  $first = key($vars['links']);
  end($vars['links']);
  $last = key($vars['links']);
  reset($vars['links']);

  $vars['links'][$first]['attributes']['class'][] = 'first';
  $vars['links'][$last]['attributes']['class'][] = 'last';
  foreach ($vars['links'] as $link) {
    $classes = '';
    if (!empty($link['attributes']['class'])) {
      $classes = join(' ', $link['attributes']['class']);
    }
    if (isset($link['icon']) && strlen($link['icon']) > 3) {
      $link['html'] = TRUE;
      $hide = isset($link['hide_text']) ? $link['hide_text'] : 0;
      $hide_class = $hide ? 'hide-text' : '';
      $space = $hide ? '' : ' ';
      $title = '<i class="fa fa-fw '. $link['icon'] . '"></i>' . $space . '<span class="' . $hide_class . '">' . $link['title'] . '</span>';
      $html .= '<li class="' . $classes .'">'.l($title, $link['href'], $link).'</li>';
    }
    else if (isset($link['title']) && isset($link['href'])) {
      $html .= '<li class="' . $classes .'">'.l($link['title'], $link['href'], $link).'</li>';
    }
  }
  $html .= "  </ul>";
  return $html;
}

/**
 * Overrides theme_menu_local_tasks().
 * Add classes to primary tab links
 */
function expressbase_menu_local_tasks(&$vars) {
  $output = '';
  if (!empty($vars['primary'])) {
    foreach($vars['primary'] as $menu_item_key => $menu_attributes) {
      $vars['primary'][$menu_item_key]['#link']['localized_options']['attributes']['class'][] = strtolower(str_replace(' ','-', $menu_attributes['#link']['title']));
    }
    $vars['primary']['#prefix'] = '<h2 class="element-invisible">' . t('Primary tabs') . '</h2>';
    $vars['primary']['#prefix'] .= '<ul class="primary">';
    $vars['primary']['#suffix'] = '</ul>';
    $output .= drupal_render($vars['primary']);
  }
  return $output;
}

/**
 * Theme function to output tablinks for classic Quicktabs style tabs.
 */
function expressbase_qt_quicktabs_tabset($vars) {
  $variables = array(
    'attributes' => array(
      'class' => 'clearfix quicktabs-tabs quicktabs-style-' . $vars['tabset']['#options']['style'],
    ),
    'items' => array(),
  );
  foreach (element_children($vars['tabset']['tablinks']) as $key) {
    $item = array();
    if (is_array($vars['tabset']['tablinks'][$key])) {
      $tab = $vars['tabset']['tablinks'][$key];
      if ($key == $vars['tabset']['#options']['active']) {
        $item['class'] = array('active');
      }
      $item['data'] = drupal_render($tab);
      $variables['items'][] = $item;
    }
  }
  return theme('item_list', $variables);
}

/**
 * Function to add home icon to menu items set to <front>.
 */
function expressbase_home_icon(&$vars, $menu) {
  if (isset($vars[$menu])) {
    foreach ($vars[$menu] as $key => $value) {
      if($vars[$menu][$key]['href'] == '<front>') {
        $vars[$menu][$key]['html'] = TRUE;
        $vars[$menu][$key]['title'] = '<i class="fa fa-home"></i><span class="element-invisible">Home</span>';
        $vars[$menu][$key]['attributes']['id'] = 'home-link';
      }
    }
  }
}
/**
 * Function to provide column classes where key is the number of columns that.
 * Use this for set columns that will be the same regardless of device.
 */
function expressbase_column_size_classes() {
  $classes = array();
  $classes[1]= array(
    'xs' => 'col-xs-12',
    'sm' => 'col-sm-12',
    'md' => 'col-md-12',
    'lg' => 'col-lg-12',
  );
  $classes[2]= array(
    'xs' => 'col-xs-6',
    'sm' => 'col-sm-6',
    'md' => 'col-md-6',
    'lg' => 'col-lg-6',
  );
  $classes[3]= array(
    'xs' => 'col-xs-4',
    'sm' => 'col-sm-4',
    'md' => 'col-md-4',
    'lg' => 'col-lg-4',
  );
  $classes[4]= array(
    'xs' => 'col-xs-3',
    'sm' => 'col-sm-3',
    'md' => 'col-md-3',
    'lg' => 'col-lg-3',
  );
  $classes[5]= array(
    'xs' => 'col-xs-5c',
    'sm' => 'col-sm-5c',
    'md' => 'col-md-5c',
    'lg' => 'col-lg-5c',
  );
  $classes[6]= array(
    'xs' => 'col-xs-2',
    'sm' => 'col-sm-2',
    'md' => 'col-md-2',
    'lg' => 'col-lg-2',
  );
  return $classes;
}

/**
 * Function to provide column classes.
 * Use this when amount of columns changes depending on display.
 * 4 columns on desktop collapses to 2 columns on tablet, 1 column on mobile.
 */
function expressbase_size_column_classes() {
  $classes = array();
  $classes['xs'] = array(
    1 => 'col-xs-12',
    2 => 'col-xs-12',
    3 => 'col-xs-12',
    4 => 'col-xs-12',
    6 => 'col-xs-12'
  );
  $classes['sm'] = array(
    1 => 'col-sm-12',
    2 => 'col-sm-6',
    3 => 'col-sm-4',
    4 => 'col-sm-6',
    6 => 'col-sm-4'
  );
  $classes['md'] = array(
    1 => 'col-md-12',
    2 => 'col-md-6',
    3 => 'col-md-4',
    4 => 'col-md-3',
    6 => 'col-md-2'
  );
  $classes['lg'] = array(
    1 => 'col-lg-12',
    2 => 'col-lg-6',
    3 => 'col-lg-4',
    4 => 'col-lg-3',
    6 => 'col-lg-2'
  );
  return $classes;
}
