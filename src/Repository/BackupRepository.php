<?php

namespace App\Repository;

use App\Entity\Backup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Backup>
 *
 * @method Backup|null find($id, $lockMode = null, $lockVersion = null)
 * @method Backup|null findOneBy(array $criteria, array $orderBy = null)
 * @method Backup[]    findAll()
 * @method Backup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BackupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Backup::class);
    }

    public function create(): Backup
    {
        $backup = new Backup();
        // $event->setLocale($locale);

        return $backup;
    }

    public function remove(int $id): void
    {
        /** @var object $backup */
        $backup = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($backup);
        $this->getEntityManager()->flush();
    }

    public function save(Backup $backup): void
    {
        $this->getEntityManager()->persist($backup);
        $this->getEntityManager()->flush();
    }

//    /**
//     * @return Backup[] Returns an array of Backup objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Backup
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
