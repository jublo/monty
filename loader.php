<?php

/**
 * monty is a simple database wrapper.
 *
 * PHP version 5
 *
 * @category  Database
 * @package   Monty
 * @author    J.M. <me@mynetx.net>
 * @copyright 2011-2013 J.M. <me@mynetx.net>
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @version   2.3.0
 * @link      https://github.com/mynetx/monty/
 */

// load classes

$strDirectory = dirname(__FILE__) . '/classes/';

require_once $strDirectory . 'monty.class.php';
require_once $strDirectory . 'connector.class.php';
require_once $strDirectory . 'connector.mysql.class.php';
require_once $strDirectory . 'connector.mysql.easy.class.php';
require_once $strDirectory . 'connector.mysqli.class.php';
require_once $strDirectory . 'connector.mysqli.easy.class.php';
