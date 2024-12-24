<?php

namespace Database\Seeders;

use App\Models\Matiere;
use Illuminate\Database\Seeder;

class MatiereSeeder extends Seeder
{
    public function run(): void
    {
        $matieres = [
            $this->createMatiere(1, 'AMET', 'Avant Metré'),
            $this->createMatiere(2, 'ANFAB', 'Analyse de Fabrication'),
            $this->createMatiere(3, 'ANG G1', 'Anglais G1'),
            $this->createMatiere(4, 'BROD', 'Broderie'),
            $this->createMatiere(5, 'CHMAT', 'Chimie des Matériaux'),
            $this->createMatiere(6, 'COFISC', 'Comptabilité et Fiscalité des Entreprises'),
            $this->createMatiere(7, 'COMGENE', 'Comptabilité Générale'),
            $this->createMatiere(8, 'COMPRO', 'Communication Professionnelle Emp Bur'),
            $this->createMatiere(9, 'COMPTA', 'Comptabilité'),
            $this->createMatiere(10, 'CONSRUR/AGR', 'Construction Rurale Agricole'),
            // ... continuer avec toutes les matières de la liste
        ];

        foreach ($matieres as $matiere) {
            Matiere::create($matiere);
        }
    }

    private function createMatiere($num_matiere, $code, $designation)
    {
        // Par défaut, on met toutes les matières disponibles pour tous les examens
        // Vous pourrez ajuster cela selon vos besoins
        return [
            'num_matiere' => $num_matiere,
            'code' => $code,
            'designation' => $designation,
            'BEP' => true,
            'CAP' => true,
            'CFA' => true,
            'ConcoursLTP' => true,
            'ConcoursCFP' => true,
        ];
    }
} 