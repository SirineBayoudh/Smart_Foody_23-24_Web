<?php

namespace App\Repository;

use App\Entity\Conseil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conseil>
 *
 * @method Conseil|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conseil|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conseil[]    findAll()
 * @method Conseil[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConseilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conseil::class);
    }

    public function countConseilsForUserPerDay(int $userId)
    {
        $today = new \DateTime('today');
        return $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->andWhere('c.id_client = :userId')
            ->andWhere('c.date_conseil >= :today')
            ->setParameter('userId', $userId)
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAverageRating(): float
    {
        return $this->createQueryBuilder('c')
            ->select('AVG(c.note) as averageRating')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalConseils(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id_conseil)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    public function getCountByStatut(string $statut): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->where('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getNotesCount(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.note, COUNT(c.id_conseil) as count')
            ->groupBy('c.note')
            ->getQuery();

        $results = $qb->getResult();
        $notesCount = [];

        foreach ($results as $result) {
            $notesCount[$result['note']] = $result['count'];
        }

        return $notesCount;
    }

//    /**
//     * @return Conseil[] Returns an array of Conseil objects
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

//    public function findOneBySomeField($value): ?Conseil
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
