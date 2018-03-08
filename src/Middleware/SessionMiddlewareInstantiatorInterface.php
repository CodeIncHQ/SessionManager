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
// Date:     08/03/2018
// Time:     17:07
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\Session\Middleware;
use CodeInc\Session\Manager\SessionManager;
use Psr\Http\Message\ServerRequestInterface;


/**
 * SessionMiddlewareInstantiatorInterface
 *
 * @package CodeInc\Session
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
interface SessionMiddlewareInstantiatorInterface {
	/**
	 * Intantiates and returns a session manager for a given request.
	 *
	 * @param ServerRequestInterface $request
	 * @return SessionManager
	 */
	public function instantiate(ServerRequestInterface $request):SessionManager;
}