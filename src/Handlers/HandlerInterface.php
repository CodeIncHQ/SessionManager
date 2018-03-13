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
// Time:     12:28
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\Session\Handlers;


/**
 * Interface HandlerInterface
 *
 * @package CodeInc\Session\Handlers
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
interface HandlerInterface
{
    /**
     * Writes data.
     *
     * @param string $id
     * @param array $data
     * @return mixed
     */
    public function write(string $id, array $data):void;

    /**
     * Read data. Returns an array or null if no data is found for the given id.
     *
     * @param string $id
     * @return array|null
     */
    public function read(string $id):?array;

    /**
     * Destroy a session data.
     *
     * @param string $id
     */
    public function destroy(string $id):void;

    /**
     * Cleanup session older than
     *
     * @param int $maxlifetime Max session lifetime in seconds
     */
    public function cleanup(int $maxlifetime):void;
}