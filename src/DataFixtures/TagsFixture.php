<?php

namespace Shared\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Shared\Entity\Tags;
use Shared\Repository\CostumerRepository;

class TagsFixture extends Fixture
{
    public function __construct(private CostumerRepository $costumerRepository)
    {}
    
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        for ($i=0; $i < 20; $i++) { 
            $tag = new Tags();
            $tag->setName('t'.$i);

            // give 0-4 costumers Tags
            for ($j=0; $j < $i%4; $j++) {
                $tag->addCostumer($this->costumerRepository->getRandomCostumer());
            }
            $manager->persist($tag);

        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CostumerFixture::class,
        ];
    }
}
