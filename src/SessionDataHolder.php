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
// Time:     11:31
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\Session;
use CodeInc\Session\Exceptions\SessionReservedOffsetException;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class SessionDataHolder
 *
 * @package CodeInc\Session
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class SessionDataHolder implements \IteratorAggregate, \ArrayAccess
{
    // session data headers
    public const HEADER_IP = "__clientIp";
    public const HEADER_LAST_REQ = "__lastRequest";
    public const HEADER_CLEAN_UP = "__cleanUp";
    private const ALL_HEADERS = [
        self::HEADER_IP,
        self::HEADER_LAST_REQ,
        self::HEADER_CLEAN_UP
    ];

    /**
     * Parent session manager.
     *
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * Session ID
     *
     * @var string
     */
    protected $id;

    /**
     * Session data
     *
     * @var array
     */
    protected $data;

    /**
     * Session constructor.
     *
     * @param SessionManager $sessionManager
     * @param string $id
     * @param array|null $data
     * @throws \Exception
     */
    public function __construct(SessionManager $sessionManager, string $id,
        ?array $data = null)
    {
        $this->sessionManager = $sessionManager;
        $this->id = $id;
        $this->data = $data ?? [];
    }

    /**
     * @param SessionManager $sessionManager
     * @param ServerRequestInterface $request
     * @return SessionDataHolder
     * @throws \Exception
     */
    public static function factory(SessionManager $sessionManager, ServerRequestInterface $request):SessionDataHolder
    {
        return new SessionDataHolder($sessionManager, bin2hex(random_bytes(32)), [
            self::HEADER_IP => $request->getServerParams()["REMOTE_ADDR"] ?? null,
            self::HEADER_LAST_REQ => time()
        ]);
    }

    /**
     * @return SessionManager
     */
    public function getSessionManager():SessionManager
    {
        return $this->sessionManager;
    }

    /**
     * Returns the session id.
     *
     * @return string
     */
    public function getId():string
    {
        return $this->id;
    }

    /**
     * Returns the session data.
     *
     * @return array
     */
    public function getData():array
    {
        return $this->data;
    }

    /**
     * Unsets all session data.
     */
    public function unsetData():void
    {
        foreach (array_keys($this->data) as $var) {
            if (!in_array($var, self::ALL_HEADERS)) {
                unset($this->data[$var]);
            }
        }
    }

    /**
     * Return the timestmap of the last request.
     *
     * @return int|null
     */
    public function getLastRequestTime():?int
    {
        return $this->data[self::HEADER_LAST_REQ];
    }

    /**
     * Updates the last request timestamp.
     */
    public function updateLastRequestTime():void
    {
        $this->data[self::HEADER_LAST_REQ] = time();
    }

    /**
     * Returns the IP used to start the current session.
     *
     * @return null|string
     */
    public function getClientIp():?string
    {
        return $this->data[self::HEADER_IP] ?? null;
    }

    /**
     * @inheritdoc
     * @return \ArrayIterator
     */
    public function getIterator():\ArrayIterator
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * @inheritdoc
     * @throws SessionReservedOffsetException
     */
    public function offsetSet($offset, $value):void
    {
        if (in_array($offset, self::ALL_HEADERS)) {
            throw new SessionReservedOffsetException($offset, $this);
        }
        $this->data[$offset] = $value;
    }

    /**
     * @inheritdoc
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * @inheritdoc
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset):bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @inheritdoc
     * @throws SessionReservedOffsetException
     */
    public function offsetUnset($offset):void
    {
        if (in_array($offset, self::ALL_HEADERS)) {
            throw new SessionReservedOffsetException($offset, $this);
        }
        unset($this->data[$offset]);
    }
}