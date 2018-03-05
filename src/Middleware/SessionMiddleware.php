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
// Project:  intranet
//
declare(strict_types = 1);
namespace CodeInc\Session\Middleware;
use CodeInc\Session\SessionManager;
use CodeInc\Psr15Middlewares\AbstractRecursiveMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


/**
 * Class SessionMiddleware
 *
 * @package CodeInc\AppLib\Services\Session\Middleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class SessionMiddleware extends AbstractRecursiveMiddleware {
	const REQ_SRV_ATTR = SessionManager::class;

	/**
	 * @var SessionManager
	 */
	private $session;

	/**
	 * SessionMiddleware constructor.
	 *
	 * @param SessionManager $session
	 * @param null|MiddlewareInterface $nextMiddleware
	 */
	public function __construct(SessionManager $session, ?MiddlewareInterface $nextMiddleware = null)
	{
		parent::__construct($nextMiddleware);
		$this->session = $session;
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
		$this->session->start();

		// processing
		$response = parent::process(
			self::attachService($request, $this->session),
			$handler
		);

		// attaching the session cookie to the response
		if ($sessionCookie = $this->session->getSessionCookie()) {
			$response = $sessionCookie->addToResponse($response);
		}

		return $response;
	}

	/**
	 * Attaches a session service to a requesst.
	 *
	 * @param ServerRequestInterface $request
	 * @param SessionManager $session
	 * @return ServerRequestInterface
	 */
	public static function attachService(ServerRequestInterface $request,
		SessionManager $session):ServerRequestInterface
	{
		return $request->withAttribute(self::REQ_SRV_ATTR, $session);
	}

	/**
	 * Detaches and returns the session service from a request. Returns null if the request does not include any
	 * session service.
	 *
	 * @param ServerRequestInterface $request
	 * @return SessionManager|null
	 */
	public static function detachService(ServerRequestInterface $request):?SessionManager
	{
		return $request->getAttribute(self::REQ_SRV_ATTR);
	}
}