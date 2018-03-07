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
	private const HEADERS = [self::HEADER_IP, self::HEADER_LAST_REQ];

	/**
	 * Parent request
	 *
	 * @var ServerRequestInterface
	 */
	private $request;

	/**
	 * @var SessionConfigInterface
	 */
	private $config;

	/**
	 * Session ID
	 *
	 * @var string
	 */
	protected $id;

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
	 * @param SessionConfigInterface|null $sessionConfig
	 */
	public function __construct(ServerRequestInterface $request, SessionConfigInterface $sessionConfig)
	{
		$this->request = $request;
		$this->config = $sessionConfig;
	}

	/**
	 * Destructor. Saves the session data.
	 */
	public function __destruct()
	{
		if ($this->isStarted()) {
			$this->data[self::HEADER_LAST_REQ] = time();
			$this->config->getHandler()->writeData($this->id, $this->data);
		}
	}

	/**
	 * @return SessionConfigInterface
	 */
	public function getConfig():SessionConfigInterface
	{
		return $this->config;
	}

	/**
	 * @return ServerRequestInterface
	 */
	public function getRequest():ServerRequestInterface
	{
		return $this->request;
	}

	/**
	 * Detaches and returns the session manager from a request.
	 *
	 * @param ServerRequestInterface $request
	 * @param bool $force
	 * @return static|null
	 * @throws SessionManagerException
	 */
	public static function fromRequest(ServerRequestInterface $request, bool $force = false):?SessionManager
	{
		if ($sessionManager = $request->getAttribute(self::class)) {
			if (!$force) {
				throw new SessionManagerException(
					"The session manager is not available in the request attributes"
				);
			}
			return null;
		}
		return $sessionManager;
	}

	/**
	 * Attaches the session manager to a requesst.
	 *
	 * @param ServerRequestInterface $request
	 * @return ServerRequestInterface
	 */
	public function attacheToRequest(ServerRequestInterface $request):ServerRequestInterface
	{
		return $request->withAttribute(self::class, $this);
	}

	/**
	 * @throws SessionManagerException
	 */
	public function start():void
	{
		try {
			// if the session exists
			if (isset($this->request->getCookieParams()[$this->config->getName()])) {
				$this->id = $this->request->getCookieParams()[$this->config->getName()];

				// the session is not valid = we delete the previous one and create a new one.
				if (!($this->data = $this->config->getHandler()->readData($this->id)) || !$this->isValid()) {
					$this->config->getHandler()->remove($this->id);
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
			$this->config->getHandler()->remove($this->id);
		}
		$this->id = null;
		$this->data = [];
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
				self::HEADER_IP => $this->request->getServerParams()["REMOTE_ADDR"] ?? null,
				self::HEADER_LAST_REQ => time()
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
	protected function isValid():bool
	{
		// if the session is expired
		if (($lastReq = $this->getLastRequestTimestamp())
			&& ($lastReq + $this->config->getExpire() * 60) < time())
		{
			return false;
		}

		// if the client IP changed
		if ($this->config->getValidateClientIp()
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
			if (!in_array($var, self::HEADERS)) {
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
					$this->config->getName(),
					$this->getId(),
					(time() + $this->config->getExpire() * 60),
					$this->config->getCookiePath() ?? "/",
					$this->config->getCookieHost() ?? $this->request->getUri()->getHost(),
					$this->config->getCookieSecure() ?? ($this->request->getUri()->getScheme() == "https"),
					true
				);
			}

			// si la session était lancée mais a été stoppée
			elseif (isset($this->request->getCookieParams()[$this->config->getName()])) {
				return SetCookie::thatDeletesCookie($this->config->getName());
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
		if (in_array($offset, self::HEADERS)) {
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
		if (in_array($offset, self::HEADERS)) {
			throw new SessionManagerException(
				sprintf("Unable to unset the offset %s, this offset is reserved for the session manager",
					$offset),
				$this
			);
		}
		unset($this->data[$offset]);
	}
}