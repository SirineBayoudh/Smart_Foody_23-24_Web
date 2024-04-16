<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\ORM\Query;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
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
    public function findCommandesForAutocomplete(string $query)
    {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.dateCommande, u.nom AS utilisateurNom')
            ->join('c.utilisateur', 'u')
            ->where('c.dateCommande LIKE :query OR u.nom LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }
    

    // Compte le nombre de commandes livrées
    public function countLivreCommandes(): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.etat = :etat')
            ->setParameter('etat', 'livré') // Assurez-vous que l'état 'livrée' correspond à ce qui est dans votre base de données
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Compte le nombre de commandes non livrées
    public function countNonLivreCommandes(): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.etat = :etat')
            ->setParameter('etat', 'non livrée') // Assurez-vous que l'état 'non livré' correspond à ce qui est dans votre base de données
            ->getQuery()
            ->getSingleScalarResult();
    }
    //recherche 
    public function searchCommandes($query)
    {
        return $this->createQueryBuilder('c')
            ->join('c.utilisateur', 'u')
            ->andWhere('u.nom LIKE :query OR u.prenom LIKE :query OR c.dateCommande LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->getQuery()
            ->getResult();
    }

    // Compte le nombre de commandes en cours
    public function countEnCoursCommandes(): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.etat = :etat')
            ->setParameter('etat', 'en cours') // Assurez-vous que l'état 'en cours' correspond à ce qui est dans votre base de données
            ->getQuery()
            ->getSingleScalarResult();
    }
/**
 * Trouve la dernière commande en cours pour un utilisateur donné.
 *
 * @param int $idUtilisateur L'identifiant de l'utilisateur.
 * @return Commande|null La dernière commande en cours si trouvée, sinon null.
 */
public function findCommandesByQuery(string $query): Query
{
    return $this->createQueryBuilder('c')
        ->leftJoin('c.utilisateur', 'u')  // Ensuring to join the Utilisateur entity
        ->where('c.dateCommande LIKE :query')  // Searching by date
        ->orWhere('u.nom LIKE :query')         // Searching by last name
        ->orWhere('u.prenom LIKE :query')      // Searching by first name
        ->setParameter('query', '%' . $query . '%')
        ->getQuery();
}

public function findDerniereCommandeEnCoursParUtilisateur(int $idUtilisateur): ?Commande
{
    return $this->createQueryBuilder('c')
        ->where('c.utilisateur = :idUtilisateur')
        ->andWhere('c.etat = :etat')
        ->setParameter('idUtilisateur', $idUtilisateur)
        ->setParameter('etat', 'non validé')
        ->orderBy('c.dateCommande', 'DESC') // Assure le tri des commandes par date, la plus récente d'abord
        ->setMaxResults(1) // Limite à la commande la plus récente
        ->getQuery()
        ->getOneOrNullResult();
}
    // Compte le nombre de commandes par ID client
    public function countCommandesByClientId(int $clientId): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.utilisateur = :clientId')
            ->setParameter('clientId', $clientId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Trouve les clients fidèles basés sur le total des commandes et le nombre de commandes
    public function trouverClientsFideles()
    {
        return $this->createQueryBuilder('c')
            ->join('c.utilisateur', 'u')
            ->select('u.id_utilisateur  AS idClient, u.nom AS nomClient, u.prenom AS prenomClient, SUM(c.totaleCommande) as totalCommande, COUNT(c.id) as nombreCommandes')
            ->groupBy('u.id_utilisateur')
            ->addGroupBy('u.nom')
            ->addGroupBy('u.prenom')
            ->orderBy('totalCommande', 'DESC')
            ->addOrderBy('nombreCommandes', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }
    
    
}
