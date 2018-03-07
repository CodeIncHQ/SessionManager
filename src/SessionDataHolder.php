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
// Date:     07/03/2018
// Time:     16:23
// Project:  lib-session
//
declare(strict_types = 1);
namespace CodeInc\Session;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class SessionDataHolder
 *
 * @package CodeInc\Session
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class SessionDataHolder implements \ArrayAccess, \IteratorAggregate {
	/**
	 * Session data
	 *
	 * @var array
	 */
	private $data;

	/**
	 * @var string|null
	 */
	private $clientIp;

	/**
	 * @var int
	 */
	private $lastReqTime;

	/**
	 * SessionDataHolder constructor.
	 *
	 * @param array|null $data
	 * @param int|null $lastReqTime
	 */
	public function __construct(?array $data = null, ?int $lastReqTime = null)
	{
		$this->data = $data ?? [];
		$this->lastReqTime = $lastReqTime ?? time();
	}

	/**
	 * Returns a new data holder for a given request.
	 *
	 * @param ServerRequestInterface $request
	 * @return SessionDataHolder
	 * @throws \Exception
	 */
	public static function newFromRequest(ServerRequestInterface $request):self
	{
		$dataHolder = new self();
		$dataHolder->clientIp = $request->getServerParams()["REMOTE_ADDR"] ?? null;
		return $dataHolder;
	}

	/**
	 * Returns the data holder from a serialized string.
	 *
	 * @param string $serializedDataHolder
	 * @return SessionDataHolder
	 * @throws SessionException
	 */
	public static function fromSerialized(string $serializedDataHolder):self
	{
		if (($dataHolder = unserialize($serializedDataHolder)) === false) {
			throw new SessionException("The serialized data is not readable");
		}
		if (!$dataHolder instanceof self) {
			throw new SessionException(sprintf("The serialized data is not an instance of %s",
				self::class));
		}
		return $dataHolder;
	}

	/**
	 * Returns the serialized data holder.
	 *
	 * @return string
	 */
	public function toSerialized():string
	{
		return serialize($this);
	}

	/**
	 * Updates the last request timestamp.
	 */
	public function updateLastRequestTime():void
	{
		$this->lastReqTime = time();
	}

	/**
	 * Returns the last request timestamp.
	 *
	 * @return int
	 */
	public function getLastReqTime():int
	{
		return $this->lastReqTime;
	}

	/**
	 * Returns the clients IP.
	 *
	 * @return null|string
	 */
	public function getClientIp():?string
	{
		return $this->clientIp;
	}

	/**
	 * Unsets all session data.
	 */
	public function unsetData():void
	{
		$this->data = [];
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
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value):void
	{
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
	 * @param mixed $offset
	 */
	public function offsetUnset($offset):void
	{
		unset($this->data[$offset]);
	}
}