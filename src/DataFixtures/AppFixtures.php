<?php

namespace App\DataFixtures;

use App\Entity\Tip;
use App\Entity\User;
use App\Entity\Month;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail("user@ecogarden.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $user->setZipcode("04400");
        $manager->persist($user);

        $userAdmin = new User();
        $userAdmin->setEmail("admin@ecogarden.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $userAdmin->setZipcode("76150");
        $manager->persist($userAdmin);

        for ($i=1; $i<13; $i++){
            $month = new Month();
            $month->setMonth($i);
            $manager->persist($month);
            $monthList [] = $month;
        }

        for ($i=0; $i<20; $i++){
            $tip = new Tip();
            $tip->setTitle('tip n°' . $i);
            $tip->setContent('Conseil n°' . $i);
            $manager->persist($tip);
            $tips []= $tip;
        }

        //To define several months to one tip 
        foreach ($tips as $tip) {
            for ($i=0; $i < mt_rand(0,3); $i++){
                $tip->addMonth($monthList[mt_rand(0, count($monthList) -1)]);
            }
        }       
       
        $manager->flush();
    }
}
