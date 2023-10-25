<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }
    public function TrancateUsers() {
        $usertrancated = $this->createQueryBuilder('DELETE  FROM user ' )
            ->getQuery();
        $usertrancated->execute();
    }

    public function findUserByUserName($name)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username= :username')
            ->setParameter('username', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function findUserByDepartement($label)
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.department', 'd')
            ->where('d.label= :label')
            ->setParameter('label', $label)
            ->getQuery()
            ->getResult();
    }
    public function IsAdmin($user)
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.RoleRole', 'r')
            ->where('r.code = :codes')
            ->andWhere('u.id= :user')
            ->setParameter('codes', 'ROLE_ADMIN')
            ->setParameter('user',$user)
            ->getQuery()
            ->getResult();
    }


    public function findUserByCompany($company)
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.department', 'd')
            ->innerJoin('d.company','c')
            ->where('c.id= :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }
    public function getUserAdmin() {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.RoleRole', 'r')
            ->where('r.code = :codes OR r.code= :codes1')
            ->setParameter('codes', 'ROLE_ADMIN')
            ->setParameter('codes1', 'ROLE_DIRECTEUR')
            ->getQuery()
            ->getResult();
    }
    public function getUserAdmins() {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.RoleRole', 'r')
            ->where('r.code = :codes')
            ->setParameter('codes', 'ROLE_ADMIN')

            ->getQuery()
            ->getResult();
    }
    public function getUserNotAdmin() {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.RoleRole', 'r')
            ->where('r.code != :codes')
            ->setParameter('codes', 'ROLE_ADMIN')
            ->getQuery()
            ->getResult();
    }
    public function getUserNotDirecteurAdmin() {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.RoleRole', 'r')
            ->where('r.code  LIKE :codes2 OR r.code  LIKE :codes3 ')
            ->setParameter('codes2', 'ROLE_TO')
            ->setParameter('codes3', 'ROLE_RESPONSABLE')
            ->getQuery()
            ->getResult();
    }
    public function getResp($id) {
        return $this->createQueryBuilder('u')
            ->where('u.id  :=id ')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
}
