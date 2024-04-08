<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 *
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }


// Dans CommandeRepository.php


public function countLivreCommandes(): int
{
    return $this->createQueryBuilder('c')
        ->select('count(c.id)')
        ->where('c.etat = :etat')
        ->setParameter('etat', 'livré')
        ->getQuery()
        ->getSingleScalarResult();
}

public function countNonLivreCommandes(): int
{
    return $this->createQueryBuilder('c')
        ->select('count(c.id)')
        ->where('c.etat = :etat')
        ->setParameter('etat', 'non livré')
        ->getQuery()
        ->getSingleScalarResult();
}

public function countEnCoursCommandes(): int
{
    return $this->createQueryBuilder('c')
        ->select('count(c.id)')
        ->where('c.etat = :etat')
        ->setParameter('etat', 'en cours')
        ->getQuery()
        ->getSingleScalarResult();
}


    public function countCommandesByClientId(int $clientId): int
{
    return $this->createQueryBuilder('c')
        ->select('COUNT(c.id)')
        // Utilisez 'c.utilisateur' pour accéder à l'entité Utilisateur associée
        // Assurez-vous que la propriété dans Commande pointant vers Utilisateur est nommée 'utilisateur'
        ->andWhere('c.utilisateur = :clientId')
        ->setParameter('clientId', $clientId)
        ->getQuery()
        ->getSingleScalarResult();
}


public function trouverClientsFideles()
{
    $qb = $this->createQueryBuilder('c')
        ->join('c.utilisateur', 'u') // Jointure avec l'entité Utilisateur
        ->select('u.id_utilisateur AS idClient, SUM(c.totaleCommande) as totalCommande, COUNT(c.id) as nombreCommandes')
        ->groupBy('u.id_utilisateur') // Groupe par ID de l'Utilisateur en utilisant 'id_utilisateur'
        ->orderBy('totalCommande', 'DESC') // Ordonne par total des commandes décroissant
        ->addOrderBy('nombreCommandes', 'DESC'); // Ensuite, ordonne par nombre de commandes décroissant
    
    return $qb->getQuery()->getArrayResult();
}
    



//    /**
//     * @return Commande[] Returns an array of Commande objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Commande
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
