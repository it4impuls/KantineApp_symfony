<?php

namespace Zeiterfassung\Repository;

use Shared\Entity\Costumer;
use Zeiterfassung\Entity\TimeEntry;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeEntry>
 */
class TimeEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeEntry::class);
    }

    public function getTimeEntryForUser(Costumer $userEntity): TimeEntry | null {
        $now = new \DateTime();
        $todayStart = (clone $now)->setTime(0, 0, 0);
        $todayEnd = (clone $now)->setTime(23, 59, 59);

        return $this->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.checkinTime BETWEEN :start AND :end')
            ->orderBy('t.checkinTime', 'DESC')
            ->setParameter('user', $userEntity)
            ->setParameter('start', $todayStart)
            ->setParameter('end', $todayEnd)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
}
