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
// Time:     13:49
// Project:  SessionManager
//
declare(strict_types = 1);
namespace CodeInc\SessionManager\Exceptions;
use CodeInc\SessionManager\SessionManager;
use Throwable;


/**
 * Class NoSessionStartedException
 *
 * @package CodeInc\Session\Exceptions
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class NoSessionStartedException extends SessionManagerException
{
    /**
     * NoSessionStartedException constructor.
     *
     * @param SessionManager|null $sessionManager
     * @param int|null $code
     * @param null|Throwable $previous
     */
    public function __construct(?SessionManager $sessionManager = null,
        ?int $code = null, ?Throwable $previous = null)
    {
        parent::__construct(
            "No session is started, unable to return the session data holder.",
            $sessionManager, $code, $previous
        );
    }
}