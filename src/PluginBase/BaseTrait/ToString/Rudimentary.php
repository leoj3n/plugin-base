<?php

namespace PluginBase;

/**
 * Rudimentary __toString() trait, for basic exception messages
 *
 * @package    PluginBase
 * @author     Joel Kuzmarski <leoj3n+PluginBase@gmail.com>
 * @copyright  2013-2014 Roots
 * @since      Class available since Release 1.0.0
 */
trait BaseTrait_ToString_Rudimentary {

  /**
   * Returns a custom exception message string
   *
   * @return     string
   * @since      Method available since Release 1.0.0
   */
  public function __toString() {
    return sprintf(
      "%s: [%d]: %s\n",
      get_called_class(), $this->code, $this->message
    );
  }
} # BaseTrait_ToString_Rudimentary
