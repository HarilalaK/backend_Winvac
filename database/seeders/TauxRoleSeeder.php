<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * TauxRoleSeeder - Initialisation des taux par rôle
 * 
 * Ce seeder crée les taux initiaux pour tous les rôles du système.
 * Les taux sont uniformes pour toutes les régions et peuvent être
 * modifiés dynamiquement par les administrateurs.
 */
class TauxRoleSeeder extends Seeder
{
    /**
     * Exécuter le seeder
     * 
     * @return void
     */
    public function run(): void
    {
        // Rôles forfaitaires - Paiement fixe quel que soit le nombre de jours
        $rolesForfaitaires = [
            [
                'role' => 'PDO',           // Président Directeur d'Ordre
                'taux_forfaitaire' => 150000,
                'created_by' => 'System',
                'is_active' => true
            ],
            [
                'role' => 'VPDO',          // Vice-Président Directeur d'Ordre
                'taux_forfaitaire' => 150000,
                'created_by' => 'System',
                'is_active' => true
            ],
            [
                'role' => 'CDC',           // Chef de Centre d'Examen
                'taux_forfaitaire' => 130000,
                'created_by' => 'System',
                'is_active' => true
            ],
            [
                'role' => 'CDCA',          // Chef de Centre d'Examen Adjoint
                'taux_forfaitaire' => 130000,
                'created_by' => 'System',
                'is_active' => true
            ],
        ];

        // Rôles journaliers - Paiement au nombre de jours travaillés
        $rolesJournaliers = [
            [
                'role' => 'Secretaire',    // Secrétaire d'examen
                'taux_journalier' => 8000,
                'created_by' => 'System',
                'is_active' => true
            ],
            [
                'role' => 'SecOrg',        // Secrétaire d'organisation
                'taux_journalier' => 8000,
                'created_by' => 'System',
                'is_active' => true
            ],
            [
                'role' => 'Surveillance',  // Agent de surveillance
                'taux_journalier' => 9000,
                'created_by' => 'System',
                'is_active' => true
            ],
            [
                'role' => 'Securite',      // Agent de sécurité
                'taux_journalier' => 9000,
                'created_by' => 'System',
                'is_active' => true
            ],
        ];

        // Rôle correcteur - Paiement au nombre de copies avec base + surplus
        $rolesCorrecteur = [
            [
                'role' => 'Correcteur',
                'taux_base_correcteur' => 30000,    // Tarif pour les 100 premières copies
                'taux_surplus_bep' => 300,           // Tarif par copie supplémentaire BEP
                'taux_surplus_autres' => 200,        // Tarif par copie supplémentaire autres examens
                'created_by' => 'System',
                'is_active' => true
            ],
        ];

        // Insérer les données avec timestamps automatiques
        $now = now();
        
        // Ajouter les timestamps aux rôles forfaitaires
        foreach ($rolesForfaitaires as &$role) {
            $role['created_at'] = $now;
            $role['updated_at'] = $now;
        }

        // Ajouter les timestamps aux rôles journaliers
        foreach ($rolesJournaliers as &$role) {
            $role['created_at'] = $now;
            $role['updated_at'] = $now;
        }

        // Ajouter les timestamps au rôle correcteur
        foreach ($rolesCorrecteur as &$role) {
            $role['created_at'] = $now;
            $role['updated_at'] = $now;
        }

        // Insérer dans la base de données
        DB::table('taux_roles')->insert($rolesForfaitaires);
        DB::table('taux_roles')->insert($rolesJournaliers);
        DB::table('taux_roles')->insert($rolesCorrecteur);

        $this->command->info('✅ Taux des rôles créés avec succès:');
        $this->command->info('   - ' . count($rolesForfaitaires) . ' rôles forfaitaires');
        $this->command->info('   - ' . count($rolesJournaliers) . ' rôles journaliers');
        $this->command->info('   - ' . count($rolesCorrecteur) . ' rôle correcteur');
        $this->command->info('   - Total: ' . (count($rolesForfaitaires) + count($rolesJournaliers) + count($rolesCorrecteur)) . ' taux créés');
    }
}