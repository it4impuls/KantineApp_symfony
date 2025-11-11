<?php

namespace App\Repository;

use App\Entity\Costumer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Costumer>
 */
class CostumerRepository extends ServiceEntityRepository
{
    private const MONTHS_INACTIVE_UNTIL_REMOVAL = 6;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Costumer::class);
    }

    private function getOldInactive(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.active = false')
            ->andWhere('c.enddate < :date')
            ->setParameter('date', new \DateTimeImmutable(-self::MONTHS_INACTIVE_UNTIL_REMOVAL . ' months'));
    }

    public function countOldInactive(): int
    {
        return $this->getOldInactive()->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();
    }

    public function deleteOldInactive(): int
    {
        return $this->getOldInactive()->delete()->getQuery()->execute();
    }

    public function findByCode($id): Query
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery();
    }

    public function filterBy(array $filters): Query
    {
        $s = "test";

        $qb = $this->createQueryBuilder('c');
        foreach ($filters as $key => $value) {
            // check if it should be treated as a literal or if it should be treated as a string
            $isLiteral = (ctype_digit($value) && $key == 'id') ||          // numeric id
                filter_var($value, FILTER_VALIDATE_BOOLEAN); // boolean


            // escape unescaped quotes not at beginning/end and stringify if not literal
            if (!$isLiteral) {
                $value = preg_replace("/(?<!\\\\|^)'(?!$)/", '"', $value);
                // already stringified (starts and ends with ' -- " does not work)
                $value = preg_replace("/^(?!')|(?<![^\\\\]')$/", "'", $value);
            }
            $qb->andWhere(sprintf('c.%s = %s', $key, $value));
        }
        return $qb->orderBy('c.id', 'ASC')->getQuery();
    }

    //    /**
    //     * @return Costumer[] Returns an array of Costumer objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Costumer
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
