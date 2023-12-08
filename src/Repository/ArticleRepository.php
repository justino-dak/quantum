<?php

namespace App\Repository;

use App\Entity\Article;
use App\Data\SearchData;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;    

    public function __construct(ManagerRegistry $registry,PaginatorInterface $paginator)
    {
        parent::__construct($registry, Article::class);
        $this->paginator = $paginator;
    }

    public function add(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function create(): Article
    {
        $article = new Article();
        // $event->setLocale($locale);

        return $article;
    }

    public function remove(int $id): void
    {
        /** @var object $article */
        $article = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($article);
        $this->getEntityManager()->flush();
    }

    public function save(Article $article): void
    {
        $this->getEntityManager()->persist($article);
        $this->getEntityManager()->flush();
    }


   /**
    * @return Article[] Returns an array of Article objects
    */
   public function findByTag($criteria,$limit=null): array
   {
        $query=$this->createQueryBuilder('a');
        $query=$query->join('a.tags','t')
                    ->andWhere('t.name = :criteria')
                    ->setParameter('criteria', $criteria)
                    ->orderBy('a.id', 'DESC');
          if ($limit) {
            $query=$query->setMaxResults($limit);
          }
           
          $query=$query->getQuery()
                        ->getResult()
       ;
       return $query;
   }

   public function findSearch(SearchData $search): PaginationInterface
   {
       $query= $this->createQueryBuilder('a');

       if(!empty($search->q)){
           $query=$query->andWhere('a.titre LIKE :q')
                        ->andWhere('a.description LIKE :q')
                        ->andWhere('a.contenu LIKE :q')
                        ->setParameter('q', "%{$search->q}%");
                        // ->orderBy('a.id', 'DESC');
       }
       return $this->paginator->paginate(
           $query,
           $search->page,
           $search->limit,
           [
            'defaultSortFieldName'      => 'a.id',
            'defaultSortDirection' => 'desc'
        ]
       );

   }   

//    /**
//     * @return Article[] Returns an array of Article objects
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

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
