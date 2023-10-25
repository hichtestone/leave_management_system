<?php

namespace App\Repository;

use App\Entity\Demand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Demand|null find($id, $lockMode = null, $lockVersion = null)
 * @method Demand|null findOneBy(array $criteria, array $orderBy = null)
 * @method Demand[]    findAll()
 * @method Demand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demand::class);
    }


    public function demandewithcompanyStatus($company,$status)
    {
        return $this->createQueryBuilder('d')
            ->innerJoin('d.user','u')
            ->innerJoin('u.department','dep')
            ->innerJoin('dep.company','c')
            ->where('c.id= :company')
            ->andwhere('d.status = :status')
            ->setParameter('company', $company)
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
    }
    public function demandewithcompany($company)
    {
        return $this->createQueryBuilder('d')
            ->innerJoin('d.user','u')
            ->innerJoin('u.RoleRole', 'r')
            ->innerJoin('u.department','dep')
            ->innerJoin('dep.company','c')
            ->where('c.id= :company')
            ->andwhere('d.status = :code2 OR d.status= :code')
            ->andWhere('d.isCancled = :cancled')
            ->andWhere('r.code!= :codes')
            ->setParameter('codes', 'ROLE_ADMIN')
            ->setParameter('company', $company)
            ->setParameter('code2', 1)
            ->setParameter('code', 2)
            ->setParameter('cancled', false)
            ->getQuery()
            ->getResult();
    }

    public function findallDemandUserNotAdmin($users) {

        return $this->createQueryBuilder('d')
            ->innerJoin('d.user', 'u')
            //->innerJoin('u.RoleRole', 'r')
            ->where('d.status = :code2')
            ->orwhere('d.status = :code')
            ->andWhere('d.isCancled = :cancled')
            ->andWhere('u.id IN (:users)')
            ->setParameter('code', 1)
            ->setParameter('code2', 2)
            ->setParameter('cancled', false)
           ->setParameter('users', $users)
            ->getQuery()->getResult();
    }

    public function findallDemandUserNot($users) {

        return $this->createQueryBuilder('d')
            ->innerJoin('d.user', 'u')
            ->andWhere('d.isCancled = :cancled')
            ->andWhere('u.id IN (:users)')
            ->setParameter('cancled', false)
            ->setParameter('users', $users)
            ->getQuery()->getResult();
    }
    public function findNotNew($status)
    {
        return $this->createQueryBuilder('d')
            ->where('d.status!= :status')
            ->setParameter('status', $status)

            ->getQuery()
            ->getResult();

    }
    public function findCongesByuser($user,$status)
    {
        return $this->createQueryBuilder('d')
            ->where('d.user= :user')
            ->andWhere('d.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)

            ->getQuery()
            ->getResult();

    }
    public function analyse($date1, $date2)
    {
        return $this->createQueryBuilder('n')
            ->where('n.startDate BETWEEN :start AND :end OR n.endDate BETWEEN :start AND :end')
            ->andWhere('n.status = :val')
            ->setParameter('start', $date1)
            ->setParameter('end', $date2)
            ->setParameter('val' , '3')
            ->getQuery()
            ->getResult();

    }
    public function analyse1($date1, $date2,$company)
    {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.user', 'u')
            ->innerJoin('u.department', 'dep')
            ->innerJoin('dep.company','c')
            ->where('n.startDate BETWEEN :start AND :end OR n.endDate BETWEEN :start AND :end')
            ->andwhere('c.id= :company')
            ->andWhere('n.status = :val')
            ->setParameter('start', $date1)
            ->setParameter('end', $date2)
            ->setParameter('company', $company)
            ->setParameter('val' , '3')
            ->getQuery()
            ->getResult();

    }
    public  function  findAllBCampany($company) {
        return $this->createQueryBuilder('d')
            ->innerJoin('d.user', 'u')
            ->innerJoin('u.department', 'dep')
            ->innerJoin('dep.company', 'c')
            ->where('c.id= :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    public function findCongesByDepartement($departement,$status)
    {
        return $this->createQueryBuilder('d')
            ->innerJoin('d.user', 'u')
            ->where('u.department= :departement')
            ->andWhere('d.status = :status')
            ->orderBy('u.department')
            ->setParameter('departement', $departement)
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
    }

    public function update_demand_progress($id){
        $demandUpdated= $this->createQueryBuilder('d')
            ->update(Demand::class, 'd')
            ->set('d.status', '2')
            ->where('d.id = :val')
            ->setParameter('val', $id)
            ->getQuery();
        $demandUpdated->execute();

    }

    public function getdemandBy($id) {
        return $this->createQueryBuilder('d')
            ->innerJoin('d.user', 'u')
            ->innerJoin('u.user', 'uu')
            ->where('uu.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    public function getdemByResp($id) {
        return $this->createQueryBuilder('d')
            ->innerJoin('d.user', 'u')
            ->where('u.user = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
    public function remove($id = null)
    {
        $q = $this->createQueryBuilder('d')
            ->delete()
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();

        return $q;
    }
}
