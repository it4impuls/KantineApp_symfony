<?php

namespace Shared\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Shared\Entity\SonataUserUser;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new SonataUserUser();
        $user->setEmail("admin@admin.admin");
        $user->setEnabled(True);
        $user->setUsername("admin");
        $user->setPlainPassword("admin");
        $user->setRoles(["ROLE_ADMIN"]);


        $manager->persist($user);
        $manager->flush();
    }
}
