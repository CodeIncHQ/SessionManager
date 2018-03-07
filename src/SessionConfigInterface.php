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
// Date:     07/03/2018
// Time:     02:58
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\Session;


/**
 * Interface SessionConfigInterface
 *
 * @package CodeInc\Session
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
interface SessionConfigInterface {
	/**
	 * @return SessionHandlerInterface
	 */
	public function getHandler():SessionHandlerInterface;

	/**
	 * @return string
	 */
	public function getName():string;
	/**
	 * @return int
	 */
	public function getExpire():int;

	/**
	 * @return null|string
	 */
	public function getCookieHost():?string;
	/**
	 * @return bool|null
	 */
	public function getCookieSecure():?bool;

	/**
	 * @return null|string
	 */
	public function getCookiePath():?string;
	/**
	 * @return bool
	 */
	public function getValidateClientIp():bool;
}