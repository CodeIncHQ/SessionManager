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
// Time:     11:28
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\Session\Controller;
use CodeInc\Router\Controller\ControllerInterface;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class SessionController
 *
 * @package CodeInc\Session\Controller
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
abstract class SessionController implements ControllerInterface {
	use SessionControllerTrait;

	/**
	 * @var ServerRequestInterface
	 */
	private $request;

	/**
	 * SessionController constructor.
	 *
	 * @param ServerRequestInterface $request
	 * @throws SessionControllerException
	 */
	public function __construct(ServerRequestInterface $request)
	{
		$this->request = $request;
		$this->configureSessionManager($request);
	}

	/**
	 * @return ServerRequestInterface
	 */
	protected function getRequest():ServerRequestInterface
	{
		return $this->request;
	}
}