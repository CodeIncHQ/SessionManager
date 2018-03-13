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
// Time:     12:37
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\Session\Handlers;


/**
 * Class FileHandler
 *
 * @package CodeInc\Session\Handlers
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class FileHandler implements HandlerInterface
{
    /**
     * @var string
     */
    private $savePath;

    /**
     * FileHandler constructor.
     *
     * @param string $savePath
     */
    public function __construct(string $savePath)
    {
        $this->savePath = $savePath;
    }

    /**
     * Returns the local file path for a given id.
     *
     * @param string $id
     * @return string
     * @throws HandlerException
     */
    private function getIdPath(string $id):string
    {
        if (!preg_match("/^[a-z0-9]$/ui", $id)) {
            throw new HandlerException(
                sprintf("The session id %s is invalid", $id),
                $this
            );
        }
        return $this->savePath."/$id.session";
    }

    /**
     * @inheritdoc
     * @throws HandlerException
     */
    public function read(string $id):?array
    {
        $path = $this->getIdPath($id);
        if (file_exists($path)) {
            if (($data = file_get_contents($path)) === false) {
                throw new HandlerException(
                    sprintf("Unable to read the file for the session %s",
                        $id),
                    $this
                );
            }
            if (($data = unserialize($data)) === false) {
                throw new HandlerException(
                    sprintf("Error while parsing the file for the session %s",
                        $id),
                    $this
                );
            }
            return $data;
        }
        return null;
    }

    /**
     * @inheritdoc
     * @throws HandlerException
     */
    public function write(string $id, array $data):void
    {
        if (file_put_contents($this->getIdPath($id), serialize($data)) === false) {
            throw new HandlerException(
                sprintf("Unable to write the file for the session %s",
                    $id),
                $this
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function cleanup(int $maxlifetime):void
    {
        $expiringTime = time() - $maxlifetime;
        foreach (glob($this->savePath."/*.session") as $sessionFile) {
            if (filemtime($sessionFile) < $expiringTime) {
                unlink($sessionFile);
            }
        }
    }

    /**
     * @inheritdoc
     * @throws HandlerException
     */
    public function destroy(string $id):void
    {
        $path = $this->getIdPath($id);
        if (file_exists($path)) {
            unlink($path);
        }
    }
}