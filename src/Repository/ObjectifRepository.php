<?php

namespace App\Repository;

use App\Entity\Objectif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Objectif>
 *
 * @method Objectif|null find($id, $lockMode = null, $lockVersion = null)
 * @method Objectif|null findOneBy(array $criteria, array $orderBy = null)
 * @method Objectif[]    findAll()
 * @method Objectif[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ObjectifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Objectif::class);
    }
    /**
     * Récupère toutes les listes de critères distinctes parmi tous les objectifs.
     *
     * @return array
     */
    public function findAllCriteres(): array
    {
        $criteres = $this->createQueryBuilder('o')
        ->select('o.listCritere')
        ->getQuery()
        ->getResult();

    $criteresList = [];
    foreach ($criteres as $critere) {
        $criteresList = array_merge($criteresList, explode(', ', $critere['listCritere']));
    }

    // Supprimer les doublons
    $criteresList = array_unique($criteresList);

    return $criteresList;
    }

    

//    /**
//     * @return Objectif[] Returns an array of Objectif objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Objectif
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
