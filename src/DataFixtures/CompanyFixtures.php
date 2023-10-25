<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Role;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CompanyFixtures extends  Fixture
{
    public function load(ObjectManager $manager)
    {
        $campnies = [
            [
                'name'        => 'C&H',
                'logo'        => 'C&H.svg' ,
                'adress'      => '128 RUE LA BOETIE PARIS ',
                'country'     => 'France',
                'zipcode'     => 75008,
                'phoneNumber' => '08 92 97 61 45',
                'fax'         => '',
                'email'       => 'c&h@test.com',
                'site'        => 'c&h.com'
            ],

            [
                'name'        => 'ALTRA CALL',
                'logo'        => 'altra call.svg' ,
                'adress'      => 'rue senghor Sousse',
                'country'     => 'Tunis',
                'zipcode'     => 4000,
                'phoneNumber' => '+21652594153',
                'fax'         => '',
                'email'       => 'altra@test.com',
                'site'        => 'www.altracall.com'
            ],

            [
                'name'        => 'ALTRA PHONE',
                'logo'        => 'altra phone.svg' ,
                'adress'      => '12 AV EMILE AILLAUD  GRIGNY',
                'country'     => 'France',
                'zipcode'     => 91350,
                'phoneNumber' => '+33148843259',
                'fax'         => '',
                'email'       => 'altraphone@test.fr',
                'site'        => 'altraphone.com'
            ],

            [
                'name'        => 'IDS GROUPE',
                'logo'        => 'ids groupe.svg' ,
                'adress'      => '12 AV EMILE AILLAUD  GRIGNY',
                'country'     => 'France',
                'zipcode'     => 91350,
                'phoneNumber' => '+33148843259',
                'fax'         => '',
                'email'       => 'ommercial@ids-groupe.com',
                'site'        => 'https://ids-groupe.com/'
            ],

            [
                'name'        => 'ALTRASYSTEMS',
                'logo'        => 'altra systems.svg' ,
                'adress'      => '12 AV EMILE AILLAUD  GRIGNY',
                'country'     => 'France',
                'zipcode'     => 91350,
                'phoneNumber' => '+33148843259',
                'fax'         => '',
                'email'       => 'info@altra-systems.com',
                'site'        => 'www.altra-systems.com'
            ]

        ];

        foreach ($campnies as $company)
        {
            $entity = new Company();
            $entity->setName($company['name']);
            $entity->setLogo($company['logo']);
            $entity->setAdress($company['adress']);
            $entity->setCountry($company['country']);
            $entity->setZipcode($company['zipcode']);
            $entity->setPhoneNumber($company['phoneNumber']);
            $entity->setFax($company['fax']);
            $entity->setEmail($company['email']);
            $entity->setSite($company['site']);
            $entity->setCreatedAt(new \DateTime('now'));
            $manager->persist($entity);
            $manager->flush();
        }
    }
}