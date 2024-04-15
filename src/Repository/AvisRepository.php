<?php

namespace App\Repository;

use App\Entity\Avis;
use App\Entity\Utilisateur;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avis>
 *
 * @method Avis|null find($id, $lockMode = null, $lockVersion = null)
 * @method Avis|null findOneBy(array $criteria, array $orderBy = null)
 * @method Avis[]    findAll()
 * @method Avis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

     // Implémentation de la méthode findAll
        public function findAll(): array
        {
            return parent::findAll();
        }

    public function prepareReclamationFormForUser7(int $userId, UtilisateurRepository $utilisateurRepository)
    {
        // Fetch the user with the provided ID
        $user = $utilisateurRepository->find($userId);
    
        return $user;
    }

    public function prepareReclamationFormProduit(int $refProduit, ProduitRepository $produitRepository)
    {
        // Fetch the user with the provided ID
        $ref = $produitRepository->find($refProduit);
    
        return $ref;
    }

    // Méthode personnalisée pour trouver les avis avec pour un produit spécifique.
        public function findByproduit($val)
        {
            return $this->createQueryBuilder('r')
                ->andWhere('r.ref_produit = :val')
                ->setParameter('val', $val)
                ->orderBy('r.date_avis', 'DESC')
                ->setMaxResults(4)
                ->getQuery()
                ->getResult();
        }

        public function countAvisByProductAndRating(int $productId, int $rating): int
        {
            return $this->createQueryBuilder('a')
                ->select('COUNT(a.id_avis)')
                ->andWhere('a.ref_produit = :productId')
                ->andWhere('a.nb_etoiles = :rating')
                ->setParameter('productId', $productId)
                ->setParameter('rating', $rating)
                ->getQuery()
                ->getSingleScalarResult();
        }

         
        public function findAllProducts()
        {
            return $this->createQueryBuilder('a') // 'a' est un alias pour l'entité Avis
                ->select('DISTINCT p') // Sélectionne toutes les données de l'entité Produit
                ->leftJoin(Produit::class, 'p', 'WITH', 'a.ref_produit = p.ref') // Joindre avec l'entité Produit
                ->getQuery()
                ->getResult();
        }

        // Fonction pour compter le nombre d'avis par produit
        public function countAvisByProduit(): array
        {
            // Sélectionner le nombre d'avis par produit
            return $this->createQueryBuilder('a')
                ->select('COUNT(a.id) as nombre_avis, p.ref as ref_produit')
                ->leftJoin('a.ref_produit', 'p')
                ->groupBy('a.ref_produit')
                ->getQuery()
                ->getResult();
        }
    
//    /**
//     * @return Avis[] Returns an array of Avis objects
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

//    public function findOneBySomeField($value): ?Avis
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
