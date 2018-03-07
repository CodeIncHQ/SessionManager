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
	const REQ_SRV_ATTR = SessionManager::class;

	/**
	 * @var SessionConfigInterface
	 */
	protected $sessionConfig;

	/**
	 * SessionMiddleware constructor.
	 *
	 * @param SessionConfigInterface $sessionConfig
	 * @param null|MiddlewareInterface $nextMiddleware
	 */
	public function __construct(SessionConfigInterface $sessionConfig, ?MiddlewareInterface $nextMiddleware = null)
	{
		parent::__construct($nextMiddleware);
		$this->sessionConfig = $sessionConfig;
	}

	/**
	 * @inheritdoc
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 * @return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
	{
		// loading and attaching the session service
		$sessionManager = $this->newSessionManager($request);
		$sessionManager->start();

		// processing
		$response = parent::process(
			$sessionManager->attacheToRequest($request),
			$handler
		);

		// attaching the session cookie to the response
		if ($sessionCookie = $sessionManager->getSessionCookie()) {
			$response = $sessionCookie->addToResponse($response);
		}

		return $response;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @return SessionManager
	 */
	protected function newSessionManager(ServerRequestInterface $request):SessionManager
	{
		return new SessionManager($request, $this->sessionConfig);
	}
}