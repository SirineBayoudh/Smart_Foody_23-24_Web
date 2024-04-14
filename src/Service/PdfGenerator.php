<?php
namespace App\Service;

use TCPDF;
use App\Entity\Produit;
use App\Entity\Objectif;

class PdfGenerator
{
    public function generatePdf(array $products)
    {
        // Créer une instance de TCPDF
        $pdf = new TCPDF();
        
        // Définir les métadonnées du document
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle('Liste des produits');
        $pdf->SetHeaderData('', 0, 'Liste des produits', '');
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Ajouter une page au document
        $pdf->AddPage();

        // Entête du tableau
        $header = array('Marque', 'Catégorie', 'Prix', 'Critère');

        // Style des cellules
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);

        // Largeurs des colonnes
        $columnWidths = array(45, 45, 45, 45);

        // Ajouter le titre au milieu de la page
        $pdf->Cell(0, 10, 'Liste des produits', 0, 1, 'C');

        // Ajouter les données des produits dans le tableau
        foreach ($products as $key => $product) {
            $data = array(
                $product->getMarque(),
                $product->getCategorie(),
                $product->getPrix() . ' DT',
                $this->getCritereLabel($product->getCritere()), 
            );

            $pdf->SetFont('helvetica', '', 10);
            $this->addRow($pdf, $data, $columnWidths, $key === 0); // Passer true si c'est la première ligne
        }

        // Ajouter la date de téléchargement
        $pdf->Cell(0, 10, 'Téléchargé le : ' . date('Y-m-d H:i:s'), 0, 1, 'C');

        // Renvoyer le PDF sous forme de chaîne
        return $pdf->Output('liste_produits.pdf', 'S');
    }

    private function getCritereLabel(?Objectif $critere): string
    {
        if ($critere !== null) {
            $critereId = $critere->getId();
            if (isset($this->critereMapping[$critereId])) {
                return $this->critereMapping[$critereId];
            }
        }
        return 'Critère inconnu';
    }

    private array $critereMapping = [
        75 => 'protein',
        78 => 'sans_glucose',
        81 => 'sans_gluten',
        83 => 'sans_lactose',
        // Ajoutez d'autres correspondances au besoin
    ];

    private function addRow($pdf, $data, $columnWidths, $isFirstRow)
    {
        $nbColumns = count($data);
        $columnWidth = array_sum($columnWidths);

        $rowHeight = 10;
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetFont('helvetica', '', 10);

        // Définir la couleur de remplissage en vert pour la première ligne
        if ($isFirstRow) {
            $pdf->SetFillColor(86, 171, 47); // Vert en code RGB
        } else {
            $pdf->SetFillColor(255, 255, 255); // Blanc pour les autres lignes
        }

        // Dessiner les cellules
        for ($i = 0; $i < $nbColumns; ++$i) {
            $pdf->SetTextColor(0);
            $pdf->SetLineWidth(0.1);
            $pdf->MultiCell($columnWidths[$i], $rowHeight, $data[$i], 1, 'C', 1, 0, '', '', true);
            $pdf->SetXY($x + $columnWidths[$i], $y);
            $x += $columnWidths[$i];
        }

        // Aller à la ligne suivante
        $pdf->SetX($x);
        $pdf->Ln($rowHeight);
    }
}
