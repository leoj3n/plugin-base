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
  public function __toString() {
    return sprintf(
      "%s: [%d]: %s\n",
      __CLASS__, $this->code, $this->message
    );
  }

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
