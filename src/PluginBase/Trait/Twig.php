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
   * @return     void
   * @param      string $templates templates directory for Twig
   * @param      string $cache cache directory for Twig
   * @since      Method available since Release 1.0.0
   */
  public static function bootstrapTwig(
    $templates = 'templates', $cache = 'cache'
  ) {
    // sets the twig variable
    self::$twig = new \Twig_Environment(
      new \Twig_Loader_Filesystem(self::root($templates)),
      array('cache' => self::root($cache), 'debug' => @constant('WP_DEBUG'))
    );

    // loads the debug extension (only used if 'debug' is true above)
    self::$twig->addExtension(new \Twig_Extension_Debug());
  }
}
