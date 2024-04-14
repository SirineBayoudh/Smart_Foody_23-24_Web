<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpClient\HttpClient;

/**
 * @extends ServiceEntityRepository<Stock>
 *
 * @method Stock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stock[]    findAll()
 * @method Stock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }
    public function findRefProduits(): array
    {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->distinct(true)
            ->getQuery()
            ->getResult();
    }



    //    /**
    //     * @return Stock[] Returns an array of Stock objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Stock
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findAllDistinctMarques(): array
    {
        $marques = $this->createQueryBuilder('s')
            ->select('s.marque')
            ->distinct()
            ->getQuery()
            ->getResult();
        var_dump($marques);
        // Formater les résultats pour avoir un tableau associatif
        $formattedMarques = [];
        foreach ($marques as $marque) {
            $formattedMarques[$marque['marque']] = $marque['marque'];
        }

        return $formattedMarques;
        var_dump($formattedMarques);
    }

    public function findFutureStocks(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.date_arrivage > :currentDate')
            ->setParameter('currentDate', new \DateTime())
            ->getQuery()
            ->getResult();
    }
    public function findExistantStocks(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.date_arrivage < :currentDate')
            ->setParameter('currentDate', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function convertDinarToEuro($amount)
    {
        // Appel de l'API pour obtenir le taux de change
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', 'https://api.exchangerate-api.com/v4/latest/TND');
        $data = $response->toArray();

        // Vérifier si la requête a réussi
        if ($response->getStatusCode() === 200) {
            // Obtenez le taux de change pour l'euro
            $eurExchangeRate = $data['rates']['EUR'];

            // Convertir le montant du dinar en euro
            $amountInEuro = $amount / $eurExchangeRate;

            return $amountInEuro;
        } else {
            // En cas d'échec de la requête API, vous pouvez gérer l'erreur ici
            throw new \Exception('Failed to fetch exchange rate from API.');
        }
    }
    public function searchStocksByName($searchQuery)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.nom LIKE :query')
            ->setParameter('query', '%' . $searchQuery . '%')
            ->getQuery()
            ->getResult();
    }
}
