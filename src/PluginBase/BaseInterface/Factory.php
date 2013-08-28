<?php
namespace PluginBase;

/**
 * Base factory interface
 *
 * @package    PluginBase
 * @author     Joel Kuzmarski <leoj3n+BootstrapShortcodes@gmail.com>
 * @copyright  2013-2014 Roots
 * @since      Class available since Release 1.0.0
 */
interface BaseInterface_Factory {

  /**
   * Build method
   *
   * @param      string $what what to build
   * @return     Object|void
   * @throws     Exception [if class to build does not exist]
   * @since      Method available since Release 1.0.0
   */
  public static function build($what);

} # BaseInterface_Factory
