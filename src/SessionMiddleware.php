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
// Time:     10:46
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\Session;
use CodeInc\Psr15Middlewares\AbstractRecursiveMiddleware;
use HansOtt\PSR7Cookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


/**
 * Class SessionMiddleware
 *
 * @package CodeInc\Session
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class SessionMiddleware extends AbstractRecursiveMiddleware {
	public const DEFAULT_REQ_ATTR = 'session';
	public const DEFAULT_NAME = "SID";
	public const DEFAULT_EXPIRE = 60;

	/**
	 * Request attribute name.
	 *
	 * @var string
	 */
	private $reqAttrName = self::DEFAULT_REQ_ATTR;

	/**
	 * Session name
	 *
	 * @var string
	 */
	private $sessionName;

	/**
	 * Session lifespan in minutes.
	 *
	 * @var int
	 */
	private $sessionExpire;

	/**
	 * Session cookie host
	 *
	 * @var string|null
	 */
	private $sessionCookieHost;

	/**
	 * Session cookie secure
	 *
	 * @var bool|null
	 */
	private $sessionCookieSecure;

	/**
	 * Session cookie path
	 *
	 * @var string|null
	 */
	private $sessionCookiePath;

	/**
	 * Verifies if the session cookie must be sent HTTP only
	 *
	 * @var bool
	 */
	private $sessionCookieHttpOnly = true;

	/**
	 * Session handler
	 *
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
	 * SessionMiddleware constructor.
	 *
	 * @param \SessionHandlerInterface $handler
	 * @param null|MiddlewareInterface $nextMiddleware
	 * @param null|string $sessionName
	 * @param int|null $sessionExpire
	 */
	public function __construct(\SessionHandlerInterface $handler, ?MiddlewareInterface $nextMiddleware = null,
		?string $sessionName = null, ?int $sessionExpire = null)
	{
		parent::__construct($nextMiddleware);
		$this->handler = $handler;
		$this->sessionName = $sessionName ?? self::DEFAULT_NAME;
		$this->sessionExpire = $sessionExpire ?? self::DEFAULT_EXPIRE;
	}

	/**
	 * @inheritdoc
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 * @return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
	{
		/*
		 * Starting the session through the session handler and cleaning up expired sessions
		 */
		$this->getHandler()->open('', $this->getSessionName());
		if (rand(1, 100) == 50) {
			$this->getHandler()->gc($this->getSessionExpire() * 60);
		}

		/*
		 * Loading the session data holder
		 */
		if (isset($request->getCookieParams()[$this->getSessionName()])) {
			$sessionId = $request->getCookieParams()[$this->getSessionName()];

			// trying to load the session data using the handler
			if (($serializedDataHolder = $this->getHandler()->read($sessionId)) === false) {
				throw new SessionException(
					sprintf("Unable to read the session %s data", $sessionId)
				);
			}
			$dataHolder = SessionDataHolder::fromSerialized($serializedDataHolder);

			// checking the data holder validity
			if (!$this->isDataHolderValid($request, $dataHolder)) {
				// if the data older is invalid, destroying
				$this->getHandler()->destroy($sessionId);
				$sessionId = $this->generateSessionId();
				$dataHolder = SessionDataHolder::newFromRequest($request);
			}
		}
		else {
			$sessionId = $this->generateSessionId();
			$dataHolder = SessionDataHolder::newFromRequest($request);
		}

		/*
		 * Processing
		 */
		$response = parent::process(
			$request->withAttribute($this->getReqAttrName(), $dataHolder),
			$handler
		);

		/*
		 * Saving the session data
		 */
		$dataHolder->updateLastRequestTime();
		$this->getHandler()->write($sessionId, $dataHolder->toSerialized());

		/*
		 * Attaches the session cookie to HTML responses
		 */
		if (($contentType = $response->getHeader("Content-Type")) !== null
			&& preg_match("#^text/html#ui", $contentType)) {
			$response = $this->getSessionCookie($sessionId, $request)->addToResponse($response);
		}

		return $response;
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	private function generateSessionId():string
	{
		return bin2hex(random_bytes(32));
	}

	/**
	 * Verifies if the session is expired
	 *
	 * @param ServerRequestInterface $request
	 * @param SessionDataHolder $dataHolder
	 * @return bool
	 */
	private function isDataHolderValid(ServerRequestInterface $request, SessionDataHolder $dataHolder):bool
	{
		// if the session is expired
		if (($dataHolder->getLastReqTime() + $this->getSessionExpire() * 60) < time())
		{
			return false;
		}

		// if the client IP changed
		if ($this->validateClientIp()
			&& $dataHolder->getClientIp() !== null
			&& isset($request->getServerParams()['REMOTE_ADDR'])
			&& $dataHolder->getClientIp() != $request->getServerParams()['REMOTE_ADDR'])
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns the session cookie.
	 *
	 * @param string $sessionId
	 * @param ServerRequestInterface $request
	 * @return SetCookie|null
	 * @throws SessionException
	 */
	private function getSessionCookie(string $sessionId, ServerRequestInterface $request):?SetCookie
	{
		try {
			return new SetCookie(
				$this->getSessionName(),
				$sessionId,
				(time() + $this->getSessionExpire() * 60),
				$this->getSessionCookiePath() ?? "/",
				$this->getSessionCookieHost() ?? $request->getUri()->getHost(),
				$this->getSessionCookieSecure() ?? ($request->getUri()->getScheme() == "https"),
				$this->isSessionCookieHttpOnly()
			);
		}
		catch (\Throwable $exception) {
			throw new SessionException("Error while preparing the session cookie",
				null, $exception);
		}
	}

	/**
	 * @param string $reqAttrName
	 */
	public function setReqAttrName(string $reqAttrName):void
	{
		$this->reqAttrName = $reqAttrName;
	}

	/**
	 * @return string
	 */
	public function getReqAttrName():string
	{
		return $this->reqAttrName;
	}

	/**
	 * @return \SessionHandlerInterface
	 */
	public function getHandler():\SessionHandlerInterface
	{
		return $this->handler;
	}

	/**
	 * @param string $sessionName
	 */
	public function setSessionName(string $sessionName):void
	{
		$this->sessionName = $sessionName;
	}

	/**
	 * @return string
	 */
	public function getSessionName():string
	{
		return $this->sessionName;
	}


	/**
	 * @param int $sessionExpire
	 */
	public function setSessionExpire(int $sessionExpire):void
	{
		$this->sessionExpire = $sessionExpire;
	}

	/**
	 * @return int
	 */
	public function getSessionExpire():int
	{
		return $this->sessionExpire;
	}

	/**
	 * @param null|string $sessionCookieHost
	 */
	public function setSessionCookieHost(?string $sessionCookieHost):void
	{
		$this->sessionCookieHost = $sessionCookieHost;
	}

	/**
	 * @return null|string
	 */
	public function getSessionCookieHost():?string
	{
		return $this->sessionCookieHost;
	}

	/**
	 * @param bool|null $sessionCookieSecure
	 */
	public function setSessionCookieSecure(?bool $sessionCookieSecure):void
	{
		$this->sessionCookieSecure = $sessionCookieSecure;
	}

	/**
	 * @return bool|null
	 */
	public function getSessionCookieSecure():?bool
	{
		return $this->sessionCookieSecure;
	}

	/**
	 * @param null|string $sessionCookiePath
	 */
	public function setSessionCookiePath(?string $sessionCookiePath):void
	{
		$this->sessionCookiePath = $sessionCookiePath;
	}

	/**
	 * @return null|string
	 */
	public function getSessionCookiePath():?string
	{
		return $this->sessionCookiePath;
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
	public function validateClientIp():bool
	{
		return $this->validateClientIp;
	}

	/**
	 * @param bool $sessionCookieHttpOnly
	 */
	public function setSessionCookieHttpOnly(bool $sessionCookieHttpOnly):void
	{
		$this->sessionCookieHttpOnly = $sessionCookieHttpOnly;
	}

	/**
	 * @return bool
	 */
	public function isSessionCookieHttpOnly():bool
	{
		return $this->sessionCookieHttpOnly;
	}
}