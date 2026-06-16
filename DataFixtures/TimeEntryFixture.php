<?php

namespace DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DataFixtures\CostumerFixture;
use DateTime;
use Shared\Repository\CostumerRepository;
use Zeiterfassung\Entity\TimeEntry;

// #[When(env: 'test')]
class TimeEntryFixture extends Fixture  implements DependentFixtureInterface
{
    public function __construct(private CostumerRepository $costumerRepository)
    {}

    public function load(ObjectManager $manager): void
    {
        
        for ($i=0; $i < 1000; $i++) {
            $timeStamp = rand(mktime(0,0,0,1,1,2022), new DateTime()->getTimestamp());
            $checkIn = new DateTime()->setTimestamp($timeStamp);
            $entry = new TimeEntry();
            // echo intval(date('H', $timeStamp))."\n";
            if( intval(date('H', $timeStamp))<20){
                $checkOut = new DateTime()->setTimestamp($timeStamp)->modify('+4 hours');
                $entry->setCheckoutTime($checkOut);
            }
            $entry->setCheckinTime($checkIn)
                    ->setUser($this->costumerRepository->getRandomCostumer())
                ;
            $manager->persist($entry);
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
