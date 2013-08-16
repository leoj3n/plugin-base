<?php
/**
 * WordPress plugin base
 *
 * Extend plugin classes from this class.
 *
 * The MIT License (MIT)
 *
 * Copyright (C) 2013  Roots
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    PluginBase
 * @author     Joel Kuzmarski <leoj3n+PluginBase@gmail.com>
 * @copyright  2013-2014 Roots
 * @license    http://opensource.org/licenses/mit-license.php
 *             The MIT License (MIT)
 * @since      File available since Release 1.0.0
 */

namespace PluginBase;

/**
 * Main plugin class
 *
 * @package    PluginBase
 * @author     Joel Kuzmarski <leoj3n+PluginBase@gmail.com>
 * @copyright  2013-2014 Roots
 * @since      Class available since Release 1.0.0
 */
abstract class Base {

  /**
   * Plugin name
   */
  const NAME = 'Base Plugin';

  /**
   * Plugin root directory
   * @staticvar string
   */
  protected static $root;

  /**
   * Stack of unsuspended/resumed error handlers
   * @staticvar array
   */
  protected static $handlers = array();

  /**
   * Initializes the plugin
   * This method adds an <code>init</code> action hook.
   * It should be called once from an file included by the WordPress.
   * @param      string $root plugin root directory
   * @param      string $cache cache directory for Twig
   * @return     void
   * @since      Method available since Release 1.0.0
   */
  public static function init($root) {
    self::$root = trailingslashit($root);
  }

  /**
   * Gets filepath relative to root directory
   * @param      string $path filepath to try
   * @return     string|void filepath relative to plugin root if found
   * @throws     Exception [if not found]
   * @since      Method available since Release 1.0.0
   */
  public static function root($path) {
    if (file_exists($try = self::$root.ltrim($path, '/'))) {
      return $try;
    } else {
      throw new Exception("Cannot locate root relative path {$try}");
    }
  }

  /**
   * Resumes handling of trigger_error()
   * Proceeding trigger_error() calls will be handled by self::errorHandler().
   * @return     void
   * @see        self::errorHandler()
   * @uses       get_called_class() gets the namespaced classname
   * @since      Method available since Release 1.0.0
   */
  public static function resumeErrorHandling($h = 'errorHandler') {
    $handler = array(get_called_class(), $h);

    if (current(self::$handlers) !== $handler) {
      array_unshift(
        self::$handlers,
        set_error_handler(
          $handler,
          E_USER_NOTICE | E_USER_ERROR | E_USER_WARNING | E_USER_DEPRECATED
        )
      );
    }
  }

  /**
   * Suspends handling of trigger_error()
   * This method closes error handling resumed earlier in execution.
   * @return     mixed
   * @see        self::errorHandler()
   * @uses       restore_error_handler() restores the previous error handler
   * @since      Method available since Release 1.0.0
   */
  public static function suspendErrorHandling($h = 'errorHandler') {
    if (current(self::$handlers) === array(get_called_class(), $h)) {
      restore_error_handler();
      return array_shift(self::$handlers);
    } else {
      return current(self::$handlers);
    }
  }

  /**
   * Handles triggered errors
   *
   * This method should only be executed by calls to {@link trigger_error()}.
   *
   * Trap triggered errors like this in this file:
   *
   * <code>
   * self::resumeErrorHandling();
   * trigger_error('...', E_USER_NOTICE);
   * self::suspendErrorHandling();
   * </code>
   *
   * Or like this from another file:
   *
   * <code>
   * Plugin::resumeErrorHandling();
   * trigger_error('...', E_USER_NOTICE);
   * Plugin::suspendErrorHandling();
   * </code>
   *
   * Or even like this from, for example, another WordPress plugin:
   *
   * <code>
   * namespace\Plugin::resumeErrorHandling();
   * trigger_error('...', E_USER_NOTICE);
   * namespace\Plugin::suspendErrorHandling();
   * </code>
   *
   * This method should end up being called as a result of the following flow,
   * where an percent sign represents two possibilities, and an asterisk
   * represents an absolute end point:
   *
   * <code>
   *      +-*[throw] # ((an Exception, possibly nested, is thrown))
   *      |     |
   *      |     |
   *      |     %--*[PHP Exception Handler] # we didn't catch the exception
   *      |     |
   *      |     |
   *      |  [catch] # ((catch exception))
   *      |     |
   *      |     |
   *      \-----% # ((throw nested exception from catch))
   *            |
   *            |
   *            %--*[...] # we didn't throw/call an exception/trigger_error
   *            |
   *            |
   *    [trigger_error()] # ((call trigger_error from catch))
   *            |
   *            |
   *            %--*[PHP Error Handler] # we did not handle the error
   *            |             *
   *            |             |
   * [Plugin::errorHandler()] |
   *            |             |
   *            |             |
   *            %---[false]---/ # we did not recognize the error
   *            |
   *            |
   *            *
   *          [true] # we recognized the error and handled it accordingly
   * </code>
   *
   * If the error code is equivelant to E_USER_ERROR, the script dies.
   *
   * @param      mixed  $c the error code
   * @param      string $m the error message
   * @param      string $f file the error occured in
   * @param      int    $l line in file error occured on
   * @return     true
   * @see        set_error_handler(), restore_error_handler()
   * @uses       error_reporting()
   * @since      Method available since Release 1.0.0
   */
  public static function errorHandler($c, $m, $f, $l) {
    // ensure reporting for code is enabled
    if (!(error_reporting() & $c)) {
      return; // silence is golden
    }

    $e = array($m);

    switch ($c) {
      case E_USER_NOTICE:
        array_unshift($e, "NOTICE");
        break;

      case E_USER_ERROR:
        array_unshift($e, "ERROR");
        break;

      case E_USER_WARNING:
        array_unshift($e, "WARNING");
        break;

      case E_USER_DEPRECATED:
        array_unshift($e, "DEPRECATED");
        break;

      default:
        return false; // execute PHP internal error handler
        break;
    }

    array_unshift($e, static::NAME);

    $e[0] = "<b>{$e[0]}";
    $e[1] = "{$e[1]}:</b>";

    $e = implode(' ', $e);

    if ($c === E_USER_ERROR) {
      die($e);
    } else {
      echo $e;
    }

    return true;
  }

} // PluginBase
