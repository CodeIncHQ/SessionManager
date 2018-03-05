<?php
//
// +---------------------------------------------------------------------+
// | CODE INC. SOURCE CODE                                               |
// +---------------------------------------------------------------------+
// | Copyright (c) 2017 - Code Inc. SAS - All Rights Reserved.           |
// | Visit https://www.codeinc.fr for more information about licensing.  |
// +---------------------------------------------------------------------+
// | NOTICE:  All information contained herein is, and remains the       |
// | property of Code Inc. SAS. The intellectual and technical concepts  |
// | contained herein are proprietary to Code Inc. SAS are protected by  |
// | trade secret or copyright law. Dissemination of this information or |
// | reproduction of this material  is strictly forbidden unless prior   |
// | written permission is obtained from Code Inc. SAS.                  |
// +---------------------------------------------------------------------+
//
// Author:   Joan Fabrégat <joan@codeinc.fr>
// Date:     04/03/2018
// Time:     09:52
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\Session;


/**
 * Interface SessionHandlerInterface
 *
 * @package CodeInc\Session
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
interface SessionHandlerInterface {
	/**
	 * Reads and Retruns the session data from the storage system.
	 *
	 * @param string $sessionId
	 * @return array
	 */
	public function readData(string $sessionId):array;

	/**
	 * Write the session data in the storage system.
	 *
	 * @param string $sessionId
	 * @param array $data
	 */
	public function writeData(string $sessionId, array $data):void;

	/**
	 * Removes the session data from the storage system.
	 *
	 * @param string $sessionId
	 */
	public function remove(string $sessionId):void;
}