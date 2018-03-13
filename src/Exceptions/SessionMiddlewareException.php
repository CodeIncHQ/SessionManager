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
// Date:     13/03/2018
// Time:     13:03
// Project:  SessionManager
//
declare(strict_types = 1);
namespace CodeInc\SessionManager\Exceptions;
use CodeInc\SessionManager\SessionMiddleware;
use Throwable;


/**
 * Class SessionMiddlewareException
 *
 * @package CodeInc\Session\Middleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class SessionMiddlewareException extends SessionException
{
    /**
     * @var SessionMiddleware|null
     */
    private $middleware;

    /**
     * SessionMiddlewareException constructor.
     *
     * @param string $message
     * @param SessionMiddleware|null $middleware
     * @param int|null $code
     * @param null|Throwable $previous
     */
    public function __construct(string $message, ?SessionMiddleware $middleware = null,
        ?int $code = null, ?Throwable $previous = null)
    {
        $this->middleware = $middleware;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return SessionMiddleware|null
     */
    public function getMiddleware():?SessionMiddleware
    {
        return $this->middleware;
    }
}