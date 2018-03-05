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
// Project:  intranet
//
declare(strict_types = 1);
namespace CodeInc\Session;
use HansOtt\PSR7Cookies\SetCookie;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class SessionManager
 *
 * @package CodeInc\AppLib\Services\Session
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class SessionManager implements \IteratorAggregate, \ArrayAccess {
	public const DEFAULT_NAME = "SID";
	public const DEFAULT_EXPIRE = 60;

	public const KEY_IP = "__clientIp";
	public const KEY_LAST_REQ = "__lastRequest";
	private const KEYS = [self::KEY_IP, self::KEY_LAST_REQ];

	/**
	 * Parent request
	 *
	 * @var ServerRequestInterface
	 */
	private $request;

	/**
	 * Session handler
	 *
	 * @var SessionHandlerInterface
	 */
	private $handler;

	/**
	 * Session ID
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Session data
	 *
	 * @var array
	 */
	private $data = [];

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
	 * Verifies if the client IP address needs to be validated.
	 *
	 * @var bool
	 */
	private $validateClientIp = false;

	/**
	 * SessionManager constructor.
	 *
	 * @param ServerRequestInterface $request
	 * @param SessionHandlerInterface $handler
	 * @param null|string $sessionName
	 * @param int|null $sessionExpire
	 */
	public function __construct(ServerRequestInterface $request, SessionHandlerInterface $handler,
		?string $sessionName = null, ?int $sessionExpire = null)
	{
		$this->request = $request;
		$this->handler = $handler;
		$this->name = $sessionName ?? self::DEFAULT_NAME;
		$this->expire = $sessionExpire ?? self::DEFAULT_EXPIRE;
	}

	/**
	 * Destructor. Saves the session data.
	 */
	public function __destruct()
	{
		if ($this->isStarted()) {
			$this->data[self::KEY_LAST_REQ] = time();
			$this->handler->writeData($this->id, $this->data);
		}
	}

	/**
	 * @throws SessionManagerException
	 */
	public function start():void
	{
		try {
			// if the session exists
			if (isset($this->request->getCookieParams()[$this->name])) {
				$this->id = $this->request->getCookieParams()[$this->name];

				// the session is not valid = we delete the previous one and create a new one.
				if (!($this->data = $this->handler->readData($this->id)) || !$this->isValid()) {
					$this->handler->remove($this->id);
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
	 * Generates a new session id.
	 *
	 * @throws SessionManagerException
	 */
	private function newSession():void
	{
		try {
			$this->id = bin2hex(random_bytes(32));
			$this->data = [
				self::KEY_IP => $this->request->getServerParams()["REMOTE_ADDR"] ?? null,
				self::KEY_LAST_REQ => time()
			];
		}
		catch (\Throwable $exception) {
			throw new SessionManagerException("Unable to start a new session",
				$this, null, $exception);
		}
	}

	/**
	 * Verifies if the session is expired
	 *
	 * @return bool
	 */
	private function isValid():bool
	{
		// if the session is expired
		if (($lastReq = $this->getLastRequestTimestamp())
			&& ($lastReq + $this->expire * 60) < time())
		{
			return false;
		}

		// if the client IP changed
		if ($this->validateClientIp
			&& ($clientIp = $this->getClientIp())
			&& isset($this->request->getServerParams()['REMOTE_ADDR'])
			&& $clientIp != $this->request->getServerParams()['REMOTE_ADDR'])
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
		return $this->data[self::KEY_LAST_REQ];
	}

	/**
	 * Returns the IP used to start the current session.
	 *
	 * @return null|string
	 */
	public function getClientIp():?string
	{
		return $this->data[self::KEY_IP] ?? null;
	}

	/**
	 * Verifies and/or sets if the IP of the client address must be validated.
	 *
	 * @param bool|null $validateClientIp
	 * @return bool
	 */
	public function validateClientIp(?bool $validateClientIp = null):bool
	{
		if ($validateClientIp !== null) {
			$this->validateClientIp = $validateClientIp;
		}
		return $this->validateClientIp;
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
			if (!in_array($var, self::KEYS)) {
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
					$this->name,
					$this->getId(),
					(time() + $this->expire * 60),
					$this->cookiePath ?? "/",
					$this->cookieHost ?? $this->request->getUri()->getHost(),
					$this->cookieSecure ?? ($this->request->getUri()->getScheme() == "https"),
					true
				);
			}

			// si la session était lancée mais a été stoppée
			elseif (isset($this->request->getCookieParams()[$this->name])) {
				return SetCookie::thatDeletesCookie($this->name);
			}

			return null;
		}
		catch (\Throwable $exception) {
			throw new SessionManagerException("Error while preparing the session cookie",
				$this, null, $exception);
		}
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
		if (in_array($offset, self::KEYS)) {
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
		unset($this->data[$offset]);
	}
}