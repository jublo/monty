<?php

/**
 * monty is a simple database wrapper.
 * Copyright (C) 2011 mynetx.

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.

 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define('MONTY_ERROR_STRING', 1);
define('MONTY_ERROR_ARRAY', 2);
define('MONTY_ERROR_OBJECT', 3);
define('MONTY_ERROR_NUMERIC', 4);

define('MONTY_NEXT_ARRAY', 1);
define('MONTY_NEXT_OBJECT', 2);

/**
 * Monty_Connector
 *
 * @package monty
 * @author mynetx
 * @copyright 2011
 * @access protected
 */
abstract class Monty_Connector {

	protected $_intRows;
	protected $_resQuery;
	protected $_strQuery;

	/**
	 * Monty_Connector::error()
	 *
	 * Get the last operation error.
	 */
	public function error($intType = MONTY_ERROR_STRING);

	/**
	 * Monty_Connector::id()
	 *
	 * Get the last inserted auto-id.
	 */
	public function id();

	/**
	 * Monty_Connector::open()
	 *
	 * Open a database connection.
	 */
	public function open($strHost = null, $strUser = null, $strPassword = null, $strDatabase = null);

	/**
	 * Monty_Connector::query()
	 *
	 * Run a raw database query.
	 */
	public function query($strQuery);

	/**
	 * Monty_Connector::rows()
	 *
	 * Get the number of rows in the result set.
	 */
	public function rows();

	/**
	 * Monty_Connector::seek()
	 *
	 * Seek a certain row in the result set.
	 */
	public function seek($intRow);

	/**
	 * Monty_Connector::walk()
	 *
	 * Walk through the result set.
	 */
	public function next();

	/**
	 * Monty_Connector::walksingle()
	 *
	 * Walk through the result set and get the next field.
	 */
	public function nextfield();
}
