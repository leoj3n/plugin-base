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

use PluginBase\BaseException as Exception;

/**
 * Main plugin class
 *
 * @package    PluginBase
 * @author     Joel Kuzmarski <leoj3n+PluginBase@gmail.com>
 * @copyright  2013-2014 Roots
 * @since      Class available since Release 1.0.0
 */
abstract class BasePlugin {

  /**
   * Plugin name
   */
  const NAME = 'Base Plugin';

  /**
   * Plugin root directory
   *
   * @staticvar string
   */
  protected static $root;

  /**
   * Error handling bitmask
   *
   * Subclasses can override with values of their own.
   */
  protected static $bitmask;

  /**
   * History of error handling bitmasks
   *
   * Allows the original error_reporting() bitmask to be restored like so:
   *
   * <code>
   * error_reporting(E_ALL ^ E_NOTICE);   // disable reporting notices
   *
   * self::resumeErrorHandling();         // resume handling
   *
   * error_reporting(E_NOTICE);           // enable reporting notices
   *
   * trigger_error('...', E_USER_NOTICE); // reported
   *
   * self::suspendErrorHandling();        // suspend handling
   *
   * trigger_error('...', E_USER_NOTICE); // not reported
   * </code>
   *
   */
  protected static $bitmaskHistory = array();

  /**
   * Initializes the plugin
   *
   * Call this method once from an file included by the WordPress.
   *
   * @param      string $root plugin root directory
   * @param      string $cache cache directory for Twig
   * @return     void
   * @since      Method available since Release 1.0.0
   */
  public static function init($root) {

    self::$root
     = @is_file($root)
     ? plugin_dir_path($roots)
     : trailingslashit($root);

    self::$bitmask = E_USER_NOTICE
                   | E_USER_ERROR
                   | E_USER_WARNING
                   | E_USER_DEPRECATED;
  }

  /**
   * Gets filepath relative to root directory
   *
   * @param      string $path filepath to try
   * @return     string|void filepath relative to plugin root if found
   * @throws     Exception [if not found]
   * @since      Method available since Release 1.0.0
   */
  public static function root($path = 'plugin.php') {
    if (file_exists($try = self::$root.ltrim($path, '/'))) {
      return $try;
    } else {
      throw self::newCascadingClass(
        'Exception',
        "Cannot locate root relative path '{$try}'"
      );
    }
  }

  /**
   * Searches for a namespaced class in order of subclass->class->global
   *
   * @param      array $args first argument MUST be the classname
   * @return     class returns the class if found
   * @throws     Exception [if not found]
   * @since      Method available since Release 1.0.0
   */
  protected static function newCascadingClass() {
    // stores passed arguments as an array
    $args = func_get_args();

    // shifts classname off front of arguments array
    $cn = array_shift($args);

    // gets calling class namespace
    $ccns = (new \ReflectionClass(get_called_class()))->getNamespaceName();

    // assembles fully qualified class name
    $fqcn = "{$ccns}\\{$cn}";

    switch (true) {
      // trys the calling class namespace
      case class_exists($fqcn):
        $found = $fqcn;
        break;

      // trys the called class namespace
      case class_exists($cn):
        $found = $cn;
        break;

      // trys the global namespace
      case class_exists($gcn = "\\{$classname}"):
        $found = $gcn;
        break;

      default:
        throw new Exception("Unable to locate cascading class '{$classname}'");
        break;
    }

    // returns a new instance of the first found class
    return (new \ReflectionClass($found))->newInstanceArgs($args);
  }

  /**
   * Resumes handling of trigger_error()
   *
   * Proceeding trigger_error() calls will be handled by self::errorHandler().
   *
   * @return     void
   * @see        self::errorHandler()
   * @uses       get_called_class() gets the namespaced classname
   * @since      Method available since Release 1.0.0
   */
  public static function resumeErrorHandling($h = 'errorHandler') {
    // pushes new handler onto PHPs internal stack
    set_error_handler(array(get_called_class(), $h), self::$bitmask);

    // records previous bitmask so it can be restored
    array_unshift(self::$bitmaskHistory, error_reporting());
  }

  /**
   * Suspends handling of trigger_error()
   *
   * This method closes error handling resumed earlier in execution.
   *
   * @return     mixed the current handler
   * @see        self::errorHandler()
   * @uses       restore_error_handler() restores the previous error handler
   * @since      Method available since Release 1.0.0
   */
  public static function suspendErrorHandling() {
    // pops newest handler off PHPs internal stack
    restore_error_handler();

    // resets previous bitmask
    error_reporting(current(self::$bitmaskHistory));

    // returns discarded bitmask
    return array_shift(self::$bitmaskHistory);
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

    $e = array(static::NAME);

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

    $e[0] = "<b>{$e[0]}";
    $e[1] = "{$e[1]}:</b>";

    array_push($e, $m);

    $e = implode(' ', $e);

    if ($c === E_USER_ERROR) {
      die($e);
    } else {
      echo trim($e).".\n";
    }

    return true;
  }

} // PluginBase
