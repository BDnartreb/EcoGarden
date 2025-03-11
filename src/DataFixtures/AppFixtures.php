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

            //$month->addTip($listTip[array_rand($listTip)]);
            //$month->setMonth(random_int(1, 12));

        for ($i=0; $i<20; $i++){
            $tip = new Tip();
            $tip->setTitle('tip n°' . $i);
            $tip->setContent('Conseil n°' . $i);
            $manager->persist($tip);
            $tips []= $tip;
        }

        foreach ($tips as $tip) {
            for ($i=0; $i < mt_rand(0,3); $i++){
                $tip->addMonth($monthList[mt_rand(0, count($monthList) -1)]);
            }
        }

        /* pour affecter 1 à 5 tutu à un toto
        foreach ($totos as $toto) {
            for ($i = 0; $i < mt_rand(1, 5); $i++) {
                $toto->addTutu($tutu[mt_rand(0, count($tutu) -1)]);
            }    
        }
        https://www.google.com/search?q=comment+associer+deux+objet+dans+une+relation+ManyToMany+symfony&oq=comment+associer+deux+objet+dans+une+relation+ManyToMany+symfony&gs_lcrp=EgZjaHJvbWUyBggAEEUYOdIBCTE0NDU3ajBqNKgCALACAQ&sourceid=chrome&ie=UTF-8#fpstate=ive&vld=cid:0d0abe55,vid:rUr4PrN-fqo,st:0
        minute 17    
        */

        
       
        $manager->flush();
    }
}
