<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }
    public function findAllCriteres(): array
{
    $qb = $this->createQueryBuilder('p')
        ->select('o.listCritere')
        ->distinct(true)
        ->leftJoin('p.critere', 'o');

    $query = $qb->getQuery();

    $results = $query->getResult();

    $criteresList = [];
    foreach ($results as $result) {
        if ($result['listCritere']) {
            $criteresList[] = $result['listCritere'];
        }
    }

    return $criteresList;
}
public function countProductsByCategory($category)
{
    return $this->createQueryBuilder('p')
        ->select('COUNT(p)')
        ->andWhere('p.categorie = :category')
        ->setParameter('category', $category)
        ->getQuery()
        ->getSingleScalarResult();
}
public function countAll(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.ref)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalProducts()
{
    return $this->createQueryBuilder('p')
        ->select('COUNT(p.ref)')
        ->getQuery()
        ->getSingleScalarResult();
}

public function getTotalPrices()
{
    return $this->createQueryBuilder('p')
        ->select('SUM(p.prix)')
        ->getQuery()
        ->getSingleScalarResult();
}
public function findByCategory($category)
{
    return $this->createQueryBuilder('p')
        ->andWhere('p.categorie = :category')
        ->setParameter('category', $category)
        ->getQuery()
        ->getResult();
}




//    /**
//     * @return Produit[] Returns an array of Produit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Produit
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
