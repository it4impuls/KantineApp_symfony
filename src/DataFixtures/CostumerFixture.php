<?php

namespace Shared\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Shared\Entity\Costumer;

class CostumerFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i=0; $i < 10; $i++) { 
            $costumer = new Costumer()
                ->setActive(true)
                ->setDepartment(array_rand(Costumer::DEPARTMENTS))
                ->setFirstname('F'.$i)
                ->setLastname('L'.$i);
            $manager->persist($costumer);
        }

        $manager->flush();
    }
}
