<?php

namespace Zeiterfassung\Repository;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zeiterfassung\Entity\ScannerClient;

/**
 * @extends ServiceEntityRepository<ScannerClient>
 */
class ScannerClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScannerClient::class);
    }

    public function getOrCreateByName(string $scanner_name): ScannerClient
    {
        $scanner_entity = $this->findOneBy(["uname" => $scanner_name]);
        if (!$scanner_entity){
            $scanner_entity = new ScannerClient()
            ->setUname($scanner_name)
            ->setLastOnline(new DateTime());
            $this->getEntityManager()->persist($scanner_entity);
            $this->getEntityManager()->flush();
        }
        return $scanner_entity;
    }

    //    /**
    //     * @return ScannerClient[] Returns an array of ScannerClient objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ScannerClient
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
