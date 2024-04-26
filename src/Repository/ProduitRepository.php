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

    public function findAllDistinctMarques(): array
    {
        $marques = $this->createQueryBuilder('s')
            ->select('s.marque')
            ->distinct()
            ->getQuery()
            ->getResult();

        // Formater les résultats pour avoir un tableau associatif
        $formattedMarques = [];
        foreach ($marques as $marque) {
            $formattedMarques[$marque['marque']] = $marque['marque'];
        }

        return $formattedMarques;
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
            ->select('p.categorie')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findByCategorie(string $categorie): array
    {
        $marques = $this->createQueryBuilder('p')
            ->select('p.marque')
            ->distinct()
            ->leftJoin('p.categorie', 'c')
            ->andWhere('c.nom = :categorie')
            ->setParameter('categorie', $categorie)
            ->getQuery()
            ->getResult();

        return $marques;
    }
    public function  findByMarquesParCategorie(): array
    {
        $marquesParCategorie = [];

        // Récupérer toutes les catégories distinctes
        $categories = $this->findAllDistinctCategories();

        // Pour chaque catégorie, récupérer les marques correspondantes
        foreach ($categories as $categorie) {
            $marquesParCategorie[$categorie] = $this->findByMarquesParCategorie($categorie);
        }

        return $marquesParCategorie;
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
