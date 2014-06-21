<?php

/**
 * monty is a simple database wrapper.
 *
 * PHP version 5
 *
 * @category  Database
 * @package   Monty
 * @author    Jublo Solutions <support@jublo.net>
 * @copyright 2011-2014 Jublo Solutions <support@jublo.net>
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @version   2.3.2
 * @link      https://github.com/jublonet/monty
 */

// load classes

$strDirectory = dirname(__FILE__) . '/classes/';

require_once $strDirectory . 'monty.class.php';
require_once $strDirectory . 'connector.class.php';
require_once $strDirectory . 'connector.mysql.class.php';
require_once $strDirectory . 'connector.mysql.easy.class.php';
require_once $strDirectory . 'connector.mysqli.class.php';
require_once $strDirectory . 'connector.mysqli.easy.class.php';
