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
    /**
     * Trouver les marques pour la catégorie donnée
     *
     * @return array
     */
    public function findByCategorie(string $categorie): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.marque')
            ->where('p.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->distinct()
            ->getQuery()
            ->getResult();
    }
    public function findAllDistinctMarques(): array
    {
        $marques = $this->createQueryBuilder('s')
            ->select('s.marque')
            ->distinct()
            ->getQuery()
            ->getResult();
        var_dump($marques);
        // Formater les résultats pour avoir un tableau associatif
        $formattedMarques = [];
        foreach ($marques as $marque) {
            $formattedMarques[$marque['marque']] = $marque['marque'];
        }

        return $formattedMarques;
        var_dump($formattedMarques);
    }
    public function findAllDistinctMarquesByCategorie(string $categorie): array
    {
        $marques = $this->createQueryBuilder('p')
            ->select('DISTINCT p.marque')
            ->where('p.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->getQuery()
            ->getResult();

        // Formater les résultats pour avoir un tableau associatif
        $formattedMarques = [];
        foreach ($marques as $marque) {
            $formattedMarques[$marque['marque']] = $marque['marque'];
        }

        return $formattedMarques;
    }
    public function findAllDistinctCategories(): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT p.categorie')
            ->orderBy('p.categorie', 'ASC')
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
