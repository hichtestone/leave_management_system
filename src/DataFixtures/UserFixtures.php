<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    private $repository;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, UserRepository $repository)
    {
        $this->encoder = $passwordEncoder;
        $this->repository = $repository;
    }

    public function load(ObjectManager $manager)
    {

        //super Admin
        $user6 = new User();
        $encodedPassword = $this->encoder->encodePassword($user6, 'altra2021');
        $user6->setEmail('superadmin@altra-call.com');
        $user6->setPassword($encodedPassword);
        $user6->setFirstName('christophe');
        $user6->setLastName('SARAGOSTI');
        $user6->setAdress('paris');
        $user6->setPassword('+33 1 48 84 32 59');
        $user6->setSolde(20);
        $user6->setCivility(1);
        $user6->setFilename('man.png');
        $user6->setRoles(['ROLE_SUPER_ADMIN']);
        $user6->setUsername('Csaragosti');
        $user6->setIsSuperAdmin(true);
        $user6->setUser(null);
        $manager->persist($user6);


        //Admin Nathalie
        $user5 = new User();
        $encodedPassword = $this->encoder->encodePassword($user5, 'altra2021');
        $user5->setEmail('admin1@altra-call.com');
        $user5->setPassword($encodedPassword);
        $user5->setFirstName('Nathalie');
        $user5->setLastName('BERTOT');
        $user5->setSolde(20);
        $user5->setCivility(2);
        $user5->setFilename('woman.png');

        $user5->setRoles(['ROLE_ADMIN']);
        $user5->setUser($user6);
        $user5->setUsername('Nbertot');
        $manager->persist($user5);

        //Admin Sabri
        $user = new User();
        $encodedPassword = $this->encoder->encodePassword($user, 'altra2021');
        $user->setEmail('admin@altra-call.com');
        $user->setPassword($encodedPassword);
        $user->setFirstName('Sabri');
        $user->setLastName('SELLAM');
        $user->setFilename('man.png');
        $user->setSolde(15);
        $user->setCivility(1);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setUser($user6);
        $user->setUsername('Ssallem');
        $manager->persist($user);

        //Directeur
        $user2 = new User();
        $encodedPassword = $this->encoder->encodePassword($user2, 'altra2021');
        $user2->setEmail('directeur@altra-call.com');
        $user2->setPassword($encodedPassword);
        $user2->setCivility(1);
        $user2->setFirstName('Adel');
        $user2->setLastName('YOUSSEF');
        $user2->setFilename('man.png');
        $user2->setSolde(15);
        $user2->setRoles(['ROLE_DIRECTEUR']);
        $user2->setUser($user);
        $user2->setUsername('Ayoussef');
        $manager->persist($user2);

        //Responsable Hich
        $user7 = new User();
        $encodedPassword = $this->encoder->encodePassword($user, 'altra2021');
        $user7->setEmail('responsable-dev@altra-call.com');
        $user7->setPassword($encodedPassword);
        $user7->setFirstName('Hicham');
        $user7->setLastName('BEN MESSAOUD');
        $user7->setSolde(15);
        $user7->setCivility(1);
        $user7->setFilename('man.png');
        $user7->setRoles(['ROLE_RESPONSABLE']);
        $user7->setUser($user);
        $user7->setUsername('Hbenmessaoud');
        $manager->persist($user7);

        //Mohamed
        $user1 = new User();
        $encodedPassword = $this->encoder->encodePassword($user1, 'altra2021');
        $user1->setEmail('user@altra-call.com');
        $user1->setPassword($encodedPassword);
        $user1->setCivility(1);
        $user1->setSolde(15);
        $user1->setFirstName('Mohamed');
        $user1->setLastName('HAMMAMI');
        $user1->setFilename('man.png');

        //$user->setFilename('minion.jpg');
        $user1->setRoles(['ROLE_USER']);
        $user1->setUser($user7);
        $user1->setUsername('Mhammami');
        $manager->persist($user1);

        //Rania
        $user4 = new User();
        $encodedPassword = $this->encoder->encodePassword($user4, 'altra2021');
        $user4->setEmail('user1@altra-call.com');
        $user4->setPassword($encodedPassword);
        $user4->setCivility(2);
        $user4->setSolde(15);
        $user4->setFirstName('Rania');
        $user4->setLastName('MTAALLAH');
        $user4->setUsername('Rmtaallah');
        $user4->setFilename('woman.png');
        $user4->setRoles(['ROLE_USER']);
        $user4->setUser($user7);
        $manager->persist($user4);

        //responsable Rim
        $user3 = new User();
        $encodedPassword = $this->encoder->encodePassword($user3, 'altra2021');
        $user3->setEmail('responsable-to@altra-call.com');
        $user3->setPassword($encodedPassword);
        $user3->setRoles(['ROLE_RESPONSABLE']);
        $user3->setFirstName('Rim');
        $user3->setLastName('ALAYA');
        $user3->setUsername('Ralaya');
        $user3->setFilename('woman.png');
        $user3->setSolde(15);
        $user3->setUser($user2);
        $user3->setCivility(2);
        $manager->persist($user3);

        //Role admin
        $role = new Role();
        $role->setCode('ROLE_ADMIN');
        $role->addUser($user);
        $role->addUser($user5);
        $manager->persist($role);

        //Role To
        $role1 = new Role();
        $role1->setCode('ROLE_TO');
        $role1->addUser($user1);
        $role1->addUser($user4);
        $manager->persist($role1);

        //Role Directeur
        $role2 = new Role();
        $role2->setCode('ROLE_DIRECTEUR');
        $role2->addUser($user2);
        $manager->persist($role2);

        //Role super Admin
        $role3 = new Role();
        $role3->setCode('ROLE_SUPER_ADMIN');
        $role3->addUser($user6);
        $manager->persist($role3);

        //Role Responsable
        $role4 = new Role();
        $role4->setCode('ROLE_RESPONSABLE');
        $role4->addUser($user4);
        $role4->addUser($user3);
        $manager->persist($role4);

       /* $roles = [
            [
                'code' => 'ROLE_ADMIN',
                'user' => $user
            ],
            [
                'code' => 'ROLE_ADMIN',
                'user' => $user5
            ],
            [
                'code' => 'ROLE_TO',
                'user' => $user1
            ],

            [
                'code' => 'ROLE_TO',
                'user' => $user4
            ],

            [
                'code' => 'ROLE_DIRECTEUR',
                'user' => $user2
            ],
            [
                'code' => 'ROLE_RESPONSABLE',
                'user' => $user3
            ],
            [
                'code' => 'ROLE_SUPER_ADMIN',
                'user' => $user6
            ]

        ];
        foreach ($roles as $role) {
            $entity = new Role();
            $entity->setCode($role['code']);
            $entity->addUser($role['user']);
            $manager->persist($entity);
        }*/

        $manager->flush();


    }


}
