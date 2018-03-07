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
// Date:     02/03/2018
// Time:     10:45
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\Session;
use HansOtt\PSR7Cookies\SetCookie;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class SessionManager
 *
 * @package CodeInc\Session
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class SessionManager implements \IteratorAggregate, \ArrayAccess {
	public const HEADER_IP = "__clientIp";
	public const HEADER_LAST_REQ = "__lastRequest";
	public const HEADER_CLEAN_UP = "__cleanUp";
	private const ALL_HEADERS = [self::HEADER_IP, self::HEADER_LAST_REQ, self::HEADER_CLEAN_UP];

	public const DEFAULT_NAME = "SID";
	public const DEFAULT_EXPIRE = 60;

	/**
	 * Session ID
	 *
	 * @var string
	 */
	protected $id;

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
	 * Verifies if the session cookie must be HTTP only.
	 *
	 * @var bool
	 */
	private $cookieHttpOnly = true;

	/**
	 * @var \SessionHandlerInterface
	 */
	private $handler;

	/**
	 * Verifies if the client IP address needs to be validated.
	 *
	 * @var bool
	 */
	private $validateClientIp = false;

	/**
	 * Parent request
	 *
	 * @var ServerRequestInterface
	 */
	private $request;

	/**
	 * Session data
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * SessionManager constructor.
	 *
	 * @param ServerRequestInterface $request
	 * @param \SessionHandlerInterface $handler
	 * @param null|string $name
	 * @param int|null $expire
	 */
	public function __construct(ServerRequestInterface $request, \SessionHandlerInterface $handler,
		?string $name = null, ?int $expire = null)
	{
		$this->request = $request;
		$this->handler = $handler;
		$this->name = $name ?? self::DEFAULT_NAME;
		$this->expire = $expire ?? self::DEFAULT_EXPIRE;
	}

	/**
	 * Destructor. Saves the session data.
	 */
	public function __destruct()
	{
		if ($this->isStarted()) {
			$this->data[self::HEADER_LAST_REQ] = time();
			if (!isset($this->data[self::HEADER_CLEAN_UP])) {
				$this->getHandler()->gc($this->getExpire() * 60);
				$this->data[self::HEADER_CLEAN_UP] = true;
			}
			$this->getHandler()->write($this->id, serialize($this->data));
		}
	}

	/**
	 * @return ServerRequestInterface
	 */
	public function getRequest():ServerRequestInterface
	{
		return $this->request;
	}

	/**
	 * @return \SessionHandlerInterface
	 */
	public function getHandler():\SessionHandlerInterface
	{
		return $this->handler;
	}

	/**
	 * @throws SessionManagerException
	 */
	public function start():void
	{
		try {
			// loading the session id
			$this->id = $this->getRequest()->getCookieParams()[$this->getName()]
				?? bin2hex(random_bytes(32));

			// starting the handler
			$this->getHandler()->open('', $this->id);

			// if the session exists
			if (isset($this->getRequest()->getCookieParams()[$this->getName()])) {
				// the session is not valid = we delete the previous one and create a new one.
				if (!($data = $this->getHandler()->read($this->id))
					|| !($this->data = unserialize($data))
					|| !is_array($this->data)
					|| !$this->isValid())
				{
					$this->getHandler()->destroy($this->id);
					$this->newSession();
				}
			}

			// creating the session
			else {
				$this->newSession();
			}
		}
		catch (\Throwable $exception) {
			throw new SessionManagerException("Error while starting the session",
				$this, null, $exception);
		}
	}

	/**
	 * Stops the session. All session data are destroyed in memory and on the storage unit through the handler.
	 */
	public function stop():void
	{
		if ($this->isStarted()) {
			$this->getHandler()->destroy($this->id);
			$this->getHandler()->close();
		}
		$this->id = null;
		$this->data = [];
	}

	/**
	 * Generates a new session.
	 */
	private function newSession():void
	{
		$this->data = [
			self::HEADER_IP => $this->getRequest()->getServerParams()["REMOTE_ADDR"] ?? null,
			self::HEADER_LAST_REQ => time()
		];
	}

	/**
	 * Verifies if the session is expired
	 *
	 * @return bool
	 */
	protected function isValid():bool
	{
		// if the session is expired
		if (($lastReq = $this->getLastRequestTimestamp())
			&& ($lastReq + $this->getExpire() * 60) < time())
		{
			return false;
		}

		// if the client IP changed
		if ($this->getValidateClientIp()
			&& ($clientIp = $this->getClientIp())
			&& isset($this->getRequest()->getServerParams()['REMOTE_ADDR'])
			&& $clientIp != $this->getRequest()->getServerParams()['REMOTE_ADDR'])
		{
			return false;
		}

		return true;
	}

	/**
	 * Return the timestmap of the last request.
	 *
	 * @return int|null
	 */
	public function getLastRequestTimestamp():?int
	{
		return $this->data[self::HEADER_LAST_REQ];
	}

	/**
	 * Returns the IP used to start the current session.
	 *
	 * @return null|string
	 */
	public function getClientIp():?string
	{
		return $this->data[self::HEADER_IP] ?? null;
	}

	/**
	 * Returns the session id.
	 *
	 * @return string
	 */
	public function getId():string
	{
		return $this->id;
	}

	/**
	 * Verifies if the session is started.
	 *
	 * @return bool
	 */
	public function isStarted():bool
	{
		return $this->id !== null;
	}

	/**
	 * Returns the session data.
	 *
	 * @return array
	 */
	public function getData():array
	{
		return $this->data;
	}

	/**
	 * Unsets all session data.
	 */
	public function unsetData():void
	{
		foreach (array_keys($this->data) as $var) {
			if (!in_array($var, self::ALL_HEADERS)) {
				unset($this->data[$var]);
			}
		}
	}

	/**
	 * Returns the session cookie.
	 *
	 * @return SetCookie|null
	 * @throws SessionManagerException
	 */
	public function getSessionCookie():?SetCookie
	{
		try {
			// envoie du cookie de session
			if ($this->isStarted()) {
				return new SetCookie(
					$this->getName(),
					$this->getId(),
					(time() + $this->getExpire() * 60),
					$this->getCookiePath() ?? "/",
					$this->getCookieHost() ?? $this->getRequest()->getUri()->getHost(),
					$this->getCookieSecure() ?? ($this->getRequest()->getUri()->getScheme() == "https"),
					$this->isCookieHttpOnly()
				);
			}

			// si la session était lancée mais a été stoppée
			elseif (isset($this->getRequest()->getCookieParams()[$this->getName()])) {
				return SetCookie::thatDeletesCookie($this->getName());
			}

			return null;
		}
		catch (\Throwable $exception) {
			throw new SessionManagerException("Error while preparing the session cookie",
				$this, null, $exception);
		}
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
	 * @param bool $cookieHttpOnly
	 */
	public function setCookieHttpOnly(bool $cookieHttpOnly):void
	{
		$this->cookieHttpOnly = $cookieHttpOnly;
	}

	/**
	 * @return bool
	 */
	public function isCookieHttpOnly():bool
	{
		return $this->cookieHttpOnly;
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

	/**
	 * @inheritdoc
	 * @return \ArrayIterator
	 */
	public function getIterator():\ArrayIterator
	{
		return new \ArrayIterator($this->data);
	}

	/**
	 * @inheritdoc
	 * @param mixed $offset
	 * @param mixed $value
	 * @throws SessionManagerException
	 */
	public function offsetSet($offset, $value):void
	{
		if (in_array($offset, self::ALL_HEADERS)) {
			throw new SessionManagerException(
				sprintf("Unable to write the offset %s, this offset is reserved for the session manager",
					$offset),
				$this
			);
		}
		$this->data[$offset] = $value;
	}

	/**
	 * @inheritdoc
	 * @param mixed $offset
	 * @return mixed|null
	 */
	public function offsetGet($offset)
	{
		return $this->data[$offset] ?? null;
	}

	/**
	 * @inheritdoc
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset):bool
	{
		return isset($this->data[$offset]);
	}

	/**
	 * @inheritdoc
	 * @param mixed $offset
	 */
	public function offsetUnset($offset):void
	{
		if (in_array($offset, self::ALL_HEADERS)) {
			throw new SessionManagerException(
				sprintf("Unable to unset the offset %s, this offset is reserved for the session manager",
					$offset),
				$this
			);
		}
		unset($this->data[$offset]);
	}
}