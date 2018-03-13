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
// Time:     17:04
// Project:  lib-doctrinesessionhandler
//
declare(strict_types = 1);
namespace CodeInc\Session\Handlers\Doctrine;
use CodeInc\Session\Handlers\HandlerInterface;
use CodeInc\Session\Exceptions\HandlerException;
use Doctrine\ORM\EntityManager;

/**
 * Class DoctrineHandler
 *
 * @package CodeInc\Session\Handlers\Doctrine
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class DoctrineHandler implements HandlerInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $sessionDataEntityClass;

    /**
     * DoctrineSessionHandler constructor.
     *
     * @param EntityManager $entityManager
     * @param string $sessionDataEntityClass
     * @throws HandlerException
     */
    public function __construct(EntityManager $entityManager,
        string $sessionDataEntityClass)
    {
        if (!is_subclass_of($sessionDataEntityClass, SessionDataEntity::class)) {
            throw new HandlerException(
                sprintf("The class %s is not a valid session data entity. "
                    ."all session data entity must extend %s.",
                    $sessionDataEntityClass, SessionDataEntity::class),
                $this
            );
        }
        $this->sessionDataEntityClass = $sessionDataEntityClass;
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function cleanup(int $maxlifetime):void
    {
        $dateTime = new \DateTime("now");
        $dateTime->sub(new \DateInterval("PT".(int)$maxlifetime."S"));

        $this->entityManager->createQueryBuilder()
            ->delete($this->sessionDataEntityClass, "s")
            ->where("s.lastHit < :t")
            ->setParameter(":t", $dateTime)
            ->getQuery()->execute();
    }

    /**
     * @inheritdoc
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function read(string $id):?array
    {
        if ($sessionData = $this->entityManager->find($this->sessionDataEntityClass, $id)) {
            /** @var SessionDataEntity $sessionData */
            return $sessionData->getData();
        }
        return null;
    }

    /**
     * @inheritdoc
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function write(string $id, array $data):void
    {
        /** @var SessionDataEntity $sessionData */
        if ($sessionData = $this->entityManager->find($this->sessionDataEntityClass, (string)$id)) {
            $sessionData->setData($data);
        }
        else {
            $sessionData = new $this->sessionDataEntityClass($id);
            $sessionData->setData($data);
        }
        $sessionData->updateLastHit();
        $this->entityManager->persist($sessionData);
    }

    /**
     * @inheritdoc
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function destroy(string $id):void
    {
        if ($sessionData = $this->entityManager->find($this->sessionDataEntityClass, (string)$id)) {
            /** @var SessionDataEntity $sessionData */
            $this->entityManager->remove($sessionData);
        }
    }
}