<?php
namespace PluginBase;

/**
 * Twig trait
 *
 * @package    PluginBase
 * @author     Joel Kuzmarski <leoj3n+PluginBase@gmail.com>
 * @copyright  2013-2014 Roots
 * @since      Class available since Release 1.0.0
 */
trait Trait_Twig {
  /**
   * Twig templating engine instance
   *
   * @staticvar Twig_Environment
   */
  public static $twig;

  /**
   * "Bootstraps" the Twig environment and filesystem
   *
   * @param      string $cache cache directory for Twig
   * @return     \Twig_Environment
   * @since      Method available since Release 1.0.0
   */
  public static function bootstrapTwig($cache) {
    return new \Twig_Environment(
      new \Twig_Loader_Filesystem(self::root('templates')),
      array('cache' => self::root($cache), 'debug' => @constant(WP_DEBUG))
    );
  }
}
