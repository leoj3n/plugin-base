<?php

namespace PluginBase;

/**
 * Exception class
 *
 * @package    PluginBase
 * @author     Joel Kuzmarski <leoj3n+PluginBase@gmail.com>
 * @copyright  2013-2014 Roots
 * @since      Class available since Release 1.0.0
 */
class BaseException extends \Exception {
  //use BaseTrait_ToString_Rudimentary; # @FIXME: too simple

  /**
   * Converts PHP error constants into user-generated error constants
   *
   * @return     void
   * @param      string $templates templates directory for Twig
   * @param      string $cache cache directory for Twig
   * @since      Method available since Release 1.0.0
   */
  public function getUserCode() {
    switch ($this->code) {
      case E_ERROR:
        return E_USER_ERROR;
        break;

      case E_WARNING:
        return E_USER_WARNING;
        break;

      case E_NOTICE:
      default:
        return E_USER_NOTICE;
        break;
    }
  }
}
