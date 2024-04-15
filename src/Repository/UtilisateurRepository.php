<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 *
 * @method Utilisateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Utilisateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Utilisateur[]    findAll()
 * @method Utilisateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function getCountByGender(string $genre): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('u.genre = :genre')
            ->setParameter('genre', $genre)
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function getCountByObjectif(string $objectif): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('u.objectif = :objectif')
            ->setParameter('objectif', $objectif)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCountByRole(string $role): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('u.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAdminImage(): ?string
    {
        $query = $this->createQueryBuilder('u')
            ->select('u.photo')
            ->andWhere('u.idUtilisateur = :id')
            ->setParameter('id', 32)
            ->getQuery();

        $result = $query->getOneOrNullResult();

        return $result['photo'] ?? null;
    }

    public function findByEmail($email)
    {
        return $this->findOneBy(['email' => $email]);
    }

    //    /**
    //     * @return Utilisateur[] Returns an array of Utilisateur objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Utilisateur
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
