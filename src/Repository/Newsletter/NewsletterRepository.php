<?php

namespace App\Repository\Newsletter;

use App\Entity\Newsletter\Newsletter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Newsletter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Newsletter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Newsletter[]    findAll()
 * @method Newsletter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsletterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Newsletter::class);
    }

    public function create(): Newsletter
    {
        $newsletter = new Newsletter();
        // $event->setLocale($locale);

        return $newsletter;
    }

    public function remove(int $id): void
    {
        /** @var object $newsletter */
        $newsletter = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($newsletter);
        $this->getEntityManager()->flush();
    }

    public function save(Newsletter $newsletter): void
    {
        $this->getEntityManager()->persist($newsletter);
        $this->getEntityManager()->flush();

        
    }

   /**
    * @return Newsletter|null Returns a Newsletters  object
    */
    public function findByOneCategories($value): array
    {
        return $this->createQueryBuilder('n')
            ->leftJoin('n.categorie','c')
            ->andWhere('c.name = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }
 
    // /**
    //  * @return Newsletters[] Returns an array of Newsletters objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Newsletters
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
