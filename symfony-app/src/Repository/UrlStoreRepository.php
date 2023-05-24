<?php

namespace App\Repository;

use App\Entity\Url_store;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UrlStoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Url_store::class);
    }

    public function add(Url_store $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Url_store $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getCountOfRecordsWithCondition(int $uid): int
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select($qb->expr()->count('r.no'));
        $qb->andWhere('r.uid = :uid')
           ->setParameter('uid', $uid);
    
        return $qb->getQuery()->getSingleScalarResult();
    }
}
