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
// Date:     05/03/2018
// Time:     12:59
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\Session;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Trait SessionControllerTrait
 *
 * @package CodeInc\Session
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
trait SessionControllerTrait {
	/**
	 * @var SessionManager
	 */
	private $sessionManager;

	/**
	 * @param ServerRequestInterface $request
	 * @throws SessionException
	 */
	protected function configureSessionManager(ServerRequestInterface $request):void
	{
		if (($this->sessionManager = SessionMiddleware::detachService($request)) === null) {
			throw new SessionException("The session service is not available in the request attributes.");
		}
	}

	/**
	 * Returns the session service extracted from the request object.
	 *
	 * @return SessionManager
	 */
	protected function getSessionManager():SessionManager
	{
		return $this->sessionManager;
	}
}