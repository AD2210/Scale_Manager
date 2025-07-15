<?php

namespace App\Repository\Process;

use App\Entity\Process\FinishProcess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FinishProcess>
 */
class FinishProcessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinishProcess::class);
    }

    //    /**
    //     * @return FinishProcess[] Returns an array of FinishProcess objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FinishProcess
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
