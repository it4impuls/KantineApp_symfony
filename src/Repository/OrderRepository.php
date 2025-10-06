<?php

namespace App\Repository;

use App\Entity\Order;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findCostumerOrderAtDate($costumer, ?DateTime $order_date): ?Order
    {
        // copy original date
        $day_start = clone $order_date;
        $day_start->setTime(0, 0);
        $day_end = clone $day_start;
        $day_end->modify('+1 day');
        return $this->createQueryBuilder('p')
            ->where('p.Costumer = :Costumer')
            ->andWhere('p.order_dateTime > :startDate')
            ->andWhere('p.order_dateTime < :endDate')
            ->setParameters(new ArrayCollection([
                new Parameter('startDate', $day_start),
                new Parameter('endDate', $day_end),
                new Parameter('Costumer', $costumer),
            ]))
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
