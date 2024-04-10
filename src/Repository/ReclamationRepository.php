<?php

namespace App\Repository;

use App\Entity\Reclamation;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Repository\UtilisateurRepository; // Importation du repository UtilisateurRepository
use Doctrine\Persistence\ManagerRegistry;

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

    public function prepareReclamationFormForUser7(int $userId, UtilisateurRepository $utilisateurRepository)
        {
            // Correction ici : Fetch the user with the provided ID
            $user = $utilisateurRepository->find($userId);

            return $user;
        }
}
