<?php
namespace PluginBase;

/**
 * Version trait
 *
 * @package    PluginBase
 * @author     Joel Kuzmarski <leoj3n+PluginBase@gmail.com>
 * @copyright  2013-2014 Roots
 * @since      Class available since Release 1.0.0
 */
trait BaseTrait_Version {

  /**
   * Deactivates plugin if PHP version is less than passed version number
   *
   * @return     true|false|void
   * @param      string $version minimum PHP version
   * @since      Method available since Release 1.0.0
   */
  private static function requirePHP($ver) {
    if (version_compare(PHP_VERSION, $ver, '<')) {
      if (is_admin() && is_null(@constant('DOING_AJAX'))) {
        require_once ABSPATH.'/wp-admin/includes/plugin.php';
        $plugin = get_called_class();
        deactivate_plugins($plugin::root());
        wp_die(
          $plugin::NAME.' '.__('requires PHP', BasePlugin::DOMAIN)." {$ver}. "
          .__('The plugin has now disabled itself.', BasePlugin::DOMAIN)
          .' <a href="javascript:history.go( -1 );">'
          .__('go back', BasePlugin::DOMAIN).' &rarr;</a>'
        );
      } else {
        return false;
      }
    } else {
      return true;
    }
  }
} # BaseTrait_Version
