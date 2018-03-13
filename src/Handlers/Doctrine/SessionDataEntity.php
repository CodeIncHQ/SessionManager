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
// Time:     17:43
// Project:  lib-doctrinesessionhandler
//
declare(strict_types = 1);
namespace CodeInc\Session\Handlers\Doctrine;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class SessionDataEntity
 *
 * @ORM\MappedSuperclass()
 * @package CodeInc\DoctrineSessionHandler
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class SessionDataEntity {
	/**
	 * @ORM\Id()
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $id;

	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	private $lastHit;

	/**
	 * @ORM\Column(type="array")
	 * @var array
	 */
	private $data;

    /**
     * SessionDataEntity constructor.
     *
     * @param string $id
     */
	public function __construct(string $id)
    {
        $this->id = $id;
        $this->updateLastHit();
    }

    /**
     * @param string $id
     */
    public function setId(string $id):void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId():string
    {
        return $this->id;
    }

    /**
     * @param array $data
     */
    public function setData(array $data):void
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData():array
    {
        return $this->data;
    }

    /**
     * Updates the last hit timestmap.
     */
	public function updateLastHit():void
	{
		$this->lastHit = new \DateTime('now');
	}
}