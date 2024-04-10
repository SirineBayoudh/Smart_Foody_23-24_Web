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
