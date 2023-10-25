<?php

namespace App\Repository;

use App\Entity\SupportingDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SupportingDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportingDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportingDocument[]    findAll()
 * @method SupportingDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportingDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupportingDocument::class);
    }

    // /**
    //  * @return SupportingDocument[] Returns an array of SupportingDocument objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SupportingDocument
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
