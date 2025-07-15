<?php

namespace App\Repository\Process;

use App\Entity\Process\QualityProcess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QualityProcess>
 */
class QualityProcessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QualityProcess::class);
    }

    //    /**
    //     * @return QualityProcess[] Returns an array of QualityProcess objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?QualityProcess
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
