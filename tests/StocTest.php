<?php

namespace App\Tests;

use App\Entity\Stock;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StocTest extends KernelTestCase
{
    public function testFindRefProduits()
    {
        self::bootKernel();
        $container = self::$container;

        // Récupérer le repository de Stock
        $stockRepository = $container->get('doctrine')->getRepository(Stock::class);

        // Appeler la méthode findRefProduits
        $refProduits = $stockRepository->findAllDistinctMarques();

        // Afficher les résultats
        var_dump($refProduits);
    }
}
