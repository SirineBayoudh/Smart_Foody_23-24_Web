<?php

namespace App\Repository;

use App\Entity\Reclamation;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Repository\UtilisateurRepository; // Importation du repository UtilisateurRepository
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<Reclamation>
 *
 * @method Reclamation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reclamation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reclamation[]    findAll()
 * @method Reclamation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }
   
    // Méthode personnalisée pour trouver les réclamations avec un état spécifique.
    public function findByArchive($val)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.archive = :val')
            ->setParameter('val', $val)
            ->getQuery()
            ->getResult();
    }

    // Méthode pour compter le nombre total des réclamations
    public function countTotalReclamations(): int
    {
        // Sélectionne le nombre total de réclamations en utilisant une requête de comptage SQL
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id_reclamation)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Méthode pour compter le nombre de réclamation par rapport au type
    public function countReclamationsByType(string $type): int
    {
        // Sélectionne le nombre de réclamations pour un type spécifique en utilisant une requête de comptage SQL avec une clause WHERE
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id_reclamation)')
            ->andWhere('r.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Méthode pour calculer la moyenne des réclamations par type
    public function averageReclamationsByType(string $type): float
    {
        // Obtient le nombre total de réclamations de ce type
        $totalReclamationsOfType = $this->countReclamationsByType($type);
        
        // Obtient le nombre total de réclamations
        $totalReclamations = $this->countTotalReclamations();
        
        // Vérifie si le nombre total de réclamations est différent de zéro pour éviter une division par zéro
        if ($totalReclamations === 0) {
            return 0; // Retourne 0 pour éviter une division par zéro
        }

        // Calcule la moyenne des réclamations pour ce type par rapport à l'ensemble des réclamations
        return ($totalReclamationsOfType / $totalReclamations) * 100; // Calcule le pourcentage
    }


      // Méthode pour compter le nombre de réclamations reçues pour chaque mois d'une année
            public function countReclamationsByMonthAndYear(int $month, int $year): int
            {
                // Obtenez la connexion à la base de données
                /** @var Connection $connection */
                $connection = $this->getEntityManager()->getConnection();
            
                // Construire la requête SQL
                $sql = "
                    SELECT COUNT(id_reclamation) AS total
                    FROM reclamation
                    WHERE MONTH(date_reclamation) = :month AND YEAR(date_reclamation) = :year
                ";
            
                // Exécuter la requête
                $result = $connection->executeQuery($sql, ['month' => $month, 'year' => $year]);
            
                // Récupérer le résultat de la requête
                $total = (int) $result->fetchOne();
            
                return $total;
            }




    public function prepareReclamationFormForUser7(int $userId, UtilisateurRepository $utilisateurRepository)
        {
            // Correction ici : Fetch the user with the provided ID
            $user = $utilisateurRepository->find($userId);

            return $user;
        }
}
