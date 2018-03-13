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
// Time:     13:12
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\SessionManager\Exceptions;
use CodeInc\SessionManager\SessionManager;
use Throwable;


/**
 * Class SessionManagerException
 *
 * @package CodeInc\Session\Exceptions
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class SessionManagerException extends SessionException
{
	/**
	 * @var SessionManager|null
	 */
	private $sessionManager;

	/**
	 * SessionException constructor.
	 *
	 * @param string $message
	 * @param SessionManager|null $sessionManager
	 * @param int|null $code
	 * @param null|Throwable $previous
	 */
	public function __construct(
		string $message,
		?SessionManager $sessionManager = null,
		?int $code = null,
		?Throwable $previous = null
	) {
		$this->sessionManager = $sessionManager;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return SessionManager|null
	 */
	public function getSessionManager():?SessionManager
	{
		return $this->sessionManager;
	}
}