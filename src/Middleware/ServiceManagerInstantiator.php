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
// Date:     12/03/2018
// Time:     18:40
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\Session\Middleware;
use CodeInc\ServiceManager\ServiceInterface;
use CodeInc\ServiceManager\ServiceManager;
use CodeInc\Session\SessionManager;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class ServiceManagerInstantiator
 *
 * @package CodeInc\Session\Middleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class ServiceManagerInstantiator implements InstantiatorInterface, ServiceInterface
{
    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * ServiceManagerInstantiator constructor.
     *
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @return SessionManager
     * @throws \CodeInc\ServiceManager\Exceptions\ClassNotFoundException
     * @throws \CodeInc\ServiceManager\Exceptions\InterfaceWithoutAliasException
     * @throws \CodeInc\ServiceManager\Exceptions\NotAnObjectException
     * @throws \CodeInc\ServiceManager\Exceptions\ServiceManagerException
     * @throws \ReflectionException
     */
    public function instantiate(ServerRequestInterface $request):SessionManager
    {
        if (!$this->serviceManager->hasInstance(ServerRequestInterface::class)) {
            $this->serviceManager->addService($request);
        }
        return $this->serviceManager->getInstance(SessionManager::class);
    }
}