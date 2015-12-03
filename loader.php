<?php

/**
 * A simple MySQL/MariaDB database wrapper in PHP.
 *
 * @package   Monty
 * @version   2.4.0
 * @author    Jublo Solutions <support@jublo.net>
 * @copyright 2011-2015 Jublo Solutions <support@jublo.net>
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @link      https://github.com/jublonet/monty
 */

// load classes

$strDirectory = dirname(__FILE__) . '/classes/';

require_once $strDirectory . 'monty.class.php';
require_once $strDirectory . 'connector.class.php';
require_once $strDirectory . 'connector.mysqli.class.php';
require_once $strDirectory . 'connector.mysqli.easy.class.php';
