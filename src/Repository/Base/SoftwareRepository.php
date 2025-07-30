<?php

namespace App\Repository\Base;

use App\Entity\Base\Software;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Software>
 */
class SoftwareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Software::class);
    }

    public function clearMainFlagExcept(Software $main) : void
    {
        $qb = $this->createQueryBuilder('s');
        $qb->update()
            ->set('s.isMain', ':false')
            ->where('s.id != :id')
            ->setParameter('false', false)
            ->setParameter('id', $main->getId())
            ->getQuery()
            ->execute();
    }

    //    /**
    //     * @return Software[] Returns an array of Software objects
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

    //    public function findOneBySomeField($value): ?Software
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
