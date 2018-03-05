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
// Date:     04/03/2018
// Time:     13:14
// Project:  intranet
//
declare(strict_types = 1);
namespace CodeInc\Session\Controller;
use CodeInc\Session\SessionException;
use Throwable;


/**
 * Class SessionControllerException
 *
 * @package CodeInc\AppLib\Services\Session\Controller
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class SessionControllerException extends SessionException {
	/**
	 * @var SessionController
	 */
	private $sessionController;

	/**
	 * SessionControllerException constructor.
	 *
	 * @param string $message
	 * @param SessionController $sessionController
	 * @param int|null $code
	 * @param null|Throwable $previous
	 */
	public function __construct(string $message, SessionController $sessionController,
		?int $code = null, ?Throwable $previous = null)
	{
		$this->sessionController = $sessionController;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return SessionController
	 */
	public function getSessionController():SessionController
	{
		return $this->sessionController;
	}
}