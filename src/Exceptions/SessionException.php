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
// Time:     12:35
// Project:  SessionManager
//
declare(strict_types = 1);
namespace CodeInc\SessionManager\Exceptions;
use Throwable;


/**
 * Class SessionExceptyion
 *
 * @package CodeInc\Session\Exceptions
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class SessionException extends \Exception
{
    /**
     * SessionException constructor.
     *
     * @param string $message
     * @param int|null $code
     * @param null|Throwable $previous
     */
    public function __construct(string $message, ?int $code = null,
        ?Throwable $previous = null)
    {
        parent::__construct($message, $code ?? 0, $previous);
    }
}