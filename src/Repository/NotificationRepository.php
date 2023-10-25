<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    // /**
    //  * @return Notification[] Returns an array of Notification objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Notification
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function notif_number(User $user) {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.demand', 'd')
            ->innerJoin('d.user', 'u')
            ->where('u.id = :user')
            ->andWhere('n.isRead = :isread')
            ->andWhere('n.message = :msg OR n.message = :msg2 OR n.message = :msg3 OR n.message = :msg4 OR n.message = :msg5')
            ->setParameter('isread', false)
            ->setParameter('user', $user->getId())
            ->setParameter('msg', '3')
            ->setParameter('msg2', '4')
            ->setParameter('msg3', '5')
            ->setParameter('msg4', '7')
            ->setParameter('msg5', '9')
            ->getQuery()
            ->getResult();
    }
    public function notif_number1(User $user) {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.administrativeDemand', 'd')
            ->innerJoin('d.user', 'u')
            ->where('u.id = :user')
            ->andWhere('n.isRead = :isread')
            ->andWhere('n.message = :msg OR n.message = :msg2 OR n.message = :msg3 OR n.message = :msg4 OR n.message = :msg5')
            ->setParameter('isread', false)
            ->setParameter('user', $user->getId())
            ->setParameter('msg', '3')
            ->setParameter('msg2', '4')
            ->setParameter('msg3', '5')
            ->setParameter('msg4', '7')
            ->setParameter('msg5', '9')
            ->getQuery()
            ->getResult();
    }
    public function notif_number_admin() {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.demand', 'd')
            ->innerJoin('d.user','u')
            ->innerJoin('u.RoleRole', 'r')
            ->Where('r.code= :code1 OR r.code= :code2')
            ->andwhere('n.message = :msg OR n.message = :msg2 OR n.message = :msg3')
            ->andWhere('n.isRead = :isread')
            ->setParameter('code1', 'ROLE_TO')
            ->setParameter('code2', 'ROLE_RESPONSABLE')
            ->setParameter('msg', '1')
            ->setParameter('msg2', '6')
            ->setParameter('msg3', '8')
            ->setParameter('isread', false)
            ->getQuery()
            ->getResult();
    }

    public function notif_number_super_admin() {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.demand', 'd')
            ->innerJoin('d.user','u')
            ->innerJoin('u.RoleRole', 'r')
            ->where('n.message = :msg OR n.message = :msg2 OR n.message = :msg3')
            ->andWhere('n.isRead = :isread')
            ->andWhere('r.code= :code')
            ->setParameter('msg', '1')
            ->setParameter('msg2', '6')
            ->setParameter('msg3', '8')
            //->setParameter('msg2', '2')
            ->setParameter('isread', false)
            ->setParameter('code', 'ROLE_ADMIN')
            ->getQuery()
            ->getResult();
    }
    public function update_notif($idNotif){
        $notifUpdated= $this->createQueryBuilder('n')
            ->update(Notification::class, 'n')
            ->set('n.isRead', '1')
            ->set('n.readAt', '?2')
            ->where('n.id = :val')
            ->setParameter('val', $idNotif)
            ->setParameter('2', new \DateTime())
            ->getQuery();
        $notifUpdated->execute();

    }
    public function removeNotif($id = null)
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
