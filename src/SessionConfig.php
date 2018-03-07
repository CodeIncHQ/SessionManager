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
// Time:     02:47
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\Session;


/**
 * Class SessionConfig
 *
 * @package CodeInc\Session
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class SessionConfig {
	public const DEFAULT_NAME = "SID";
	public const DEFAULT_EXPIRE = 60;

	/**
	 * Session name
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Session lifespan in minutes.
	 *
	 * @var int
	 */
	private $expire;

	/**
	 * Session cookie host
	 *
	 * @var string|null
	 */
	private $cookieHost;

	/**
	 * Session cookie secure
	 *
	 * @var bool|null
	 */
	private $cookieSecure;

	/**
	 * Session cookie path
	 *
	 * @var string|null
	 */
	private $cookiePath;

	/**
	 * @var SessionHandlerInterface
	 */
	private $handler;

	/**
	 * Verifies if the client IP address needs to be validated.
	 *
	 * @var bool
	 */
	private $validateClientIp = false;

	/**
	 * SessionConfig constructor.
	 *
	 * @param SessionHandlerInterface $handler
	 * @param string $name
	 * @param int $expire
	 */
	public function __construct(SessionHandlerInterface $handler, string $name = self::DEFAULT_NAME,
		int $expire = self::DEFAULT_EXPIRE)
	{
		$this->handler = $handler;
		$this->name = $name;
		$this->expire = $expire;
	}

	/**
	 * @return SessionHandlerInterface
	 */
	public function getHandler():SessionHandlerInterface
	{
		return $this->handler;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name):void
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName():string
	{
		return $this->name;
	}

	/**
	 * @param int $expire
	 */
	public function setExpire(int $expire):void
	{
		$this->expire = $expire;
	}

	/**
	 * @return int
	 */
	public function getExpire():int
	{
		return $this->expire;
	}

	/**
	 * @param null|string $cookieHost
	 */
	public function setCookieHost(?string $cookieHost):void
	{
		$this->cookieHost = $cookieHost;
	}

	/**
	 * @return null|string
	 */
	public function getCookieHost():?string
	{
		return $this->cookieHost;
	}

	/**
	 * @param bool|null $cookieSecure
	 */
	public function setCookieSecure(?bool $cookieSecure):void
	{
		$this->cookieSecure = $cookieSecure;
	}

	/**
	 * @return bool|null
	 */
	public function getCookieSecure():?bool
	{
		return $this->cookieSecure;
	}

	/**
	 * @param null|string $cookiePath
	 */
	public function setCookiePath(?string $cookiePath):void
	{
		$this->cookiePath = $cookiePath;
	}

	/**
	 * @return null|string
	 */
	public function getCookiePath():?string
	{
		return $this->cookiePath;
	}

	/**
	 * @param bool $validateClientIp
	 */
	public function setValidateClientIp(bool $validateClientIp):void
	{
		$this->validateClientIp = $validateClientIp;
	}

	/**
	 * @return bool
	 */
	public function getValidateClientIp():bool
	{
		return $this->validateClientIp;
	}
}