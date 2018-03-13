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
namespace CodeInc\Session;
use CodeInc\Psr15Middlewares\AbstractRecursiveMiddleware;
use CodeInc\Session\Exceptions\SessionMiddlewareException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


/**
 * Class SessionMiddleware
 *
 * @package CodeInc\Session\Middleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class SessionMiddleware extends AbstractRecursiveMiddleware
{
    public const REQ_ATTR = '__sessionManager';

    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * SessionMiddleware constructor.
     *
     * @param SessionManager $sessionManager
     * @param null|MiddlewareInterface $nextMiddleware
     */
    public function __construct(SessionManager $sessionManager,
        ?MiddlewareInterface $nextMiddleware = null)
    {
        parent::__construct($nextMiddleware);
        $this->sessionManager = $sessionManager;
    }

    /**
     * @return SessionManager
     */
    public function getSessionManager():SessionManager
    {
        return $this->sessionManager;
    }

    /**
     * @inheritdoc
     * @throws \CodeInc\Session\Exceptions\SessionManagerException
     */
    public function process(ServerRequestInterface $request,
        RequestHandlerInterface $handler):ResponseInterface
    {
        // get the session manager and starts the session if not started
        $session = $this->sessionManager->start($request);

        // processes the response
        $response = parent::process(
            $request->withAttribute(static::REQ_ATTR, $session),
            $handler
        );

        // if the response is a HTML page, attaches the cookie
        if (preg_match("#^text/html#ui", $response->getHeaderLine("Content-Type"))) {
            $response = $this->sessionManager->getSessionCookie()->addToResponse($response);
        }

        return $response;
    }

    /**
     * Returns the session object attached to a request.
     *
     * @param ServerRequestInterface $request
     * @return Session
     * @throws SessionMiddlewareException
     */
    public static function getSession(ServerRequestInterface $request):Session
    {
        $session = $request->getAttribute(static::REQ_ATTR);
        if (!$session instanceof Session) {
            throw new SessionMiddlewareException(
                "No session object is available in the request attributes"
            );
        }
        return $session;
    }
}