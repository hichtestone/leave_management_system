<?php

namespace App\Repository;

use App\Entity\AdministrativeDemand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AdministrativeDemand|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdministrativeDemand|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdministrativeDemand[]    findAll()
 * @method AdministrativeDemand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdministrativeDemandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdministrativeDemand::class);
    }


    public function findallDemandAdministartifNotAdmin($users) {

        return $this->createQueryBuilder('d')
            ->innerJoin('d.user', 'u')
            ->where('d.status = :code')
            ->andWhere('u.id IN (:users)')
            ->setParameter('code', 1)
            ->setParameter('users', $users)
            ->getQuery()->getResult();
    }

    public function findLastByuser($user)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :val')
            ->setParameter('val', $user)
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
            ;
    }
}
