<?php

namespace App\Repository;

use App\Entity\AppInfos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppInfos>
 *
 * @method AppInfos|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppInfos|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppInfos[]    findAll()
 * @method AppInfos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppInfosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppInfos::class);
    }

//    /**
//     * @return AppInfos[] Returns an array of AppInfos objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AppInfos
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
