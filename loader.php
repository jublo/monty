<?php

/**
 * monty is a simple database wrapper.
 *
 * @package monty
 * @version 2.1.3100
 * @author J.M. <me@mynetx.net>
 * @copyright 2011-2013 J.M. <me@mynetx.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// load classes

$strDirectory = dirname(__FILE__) . '/classes/';

require_once $strDirectory . 'monty.class.php';
require_once $strDirectory . 'connector.class.php';
require_once $strDirectory . 'connector.mysql.class.php';
require_once $strDirectory . 'connector.mysql.easy.class.php';
require_once $strDirectory . 'connector.mysqli.class.php';
require_once $strDirectory . 'connector.mysqli.easy.class.php';
