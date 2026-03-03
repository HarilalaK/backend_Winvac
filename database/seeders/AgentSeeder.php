<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Agent;
use App\Models\Centre;

/**
 * AgentSeeder - Création d'agents de base pour tester le système
 * 
 * Ce seeder crée des agents exemples avec différents rôles pour démontrer
 * comment les taux sont appliqués dans le système de calcul.
 */
class AgentSeeder extends Seeder
{
    /**
     * Exécuter le seeder
     */
    public function run(): void
    {
        echo "🚀 Création des agents de base...\n";

        // Récupérer les centres existants
        $centres = Centre::all();
        if ($centres->isEmpty()) {
            echo "⚠️  Aucun centre trouvé. Veuillez d'abord exécuter CentreSeeder\n";
            return;
        }

        $agentsCreated = 0;
        $centresCount = $centres->count();

        echo "📊 Données disponibles : {$centresCount} centres\n";

        // Agents avec des rôles prédéfinis
        $predefinedAgents = [
            [
                'nom' => 'Rakoto',
                'prenom' => 'Jean',
                'role' => 'PDO',
                'im' => '101234567890',
                'cin' => '123456789012',
                'sexe' => 'M',
                'lieu_cin' => 'Antananarivo',
                'date_cin' => '2020-01-15',
                'centre_id' => $centres->random()->id,
                'typeExamen' => 'ConcoursLTP',
                'annee' => 2024,
                'situation' => 'Actif'
            ],
            [
                'nom' => 'Rabe',
                'prenom' => 'Marie',
                'role' => 'VPDO',
                'im' => '101234567891',
                'cin' => '123456789013',
                'sexe' => 'F',
                'lieu_cin' => 'Toamasina',
                'date_cin' => '2020-02-20',
                'centre_id' => $centres->random()->id,
                'typeExamen' => 'ConcoursLTP',
                'annee' => 2024,
                'situation' => 'Actif'
            ],
            [
                'nom' => 'Randria',
                'prenom' => 'Paul',
                'role' => 'CDC',
                'im' => '101234567892',
                'cin' => '123456789014',
                'sexe' => 'M',
                'lieu_cin' => 'Fianarantsoa',
                'date_cin' => '2019-11-10',
                'centre_id' => $centres->random()->id,
                'typeExamen' => 'ConcoursLTP',
                'annee' => 2024,
                'situation' => 'Actif'
            ],
            [
                'nom' => 'Razafy',
                'prenom' => 'Sophie',
                'role' => 'CDCA',
                'im' => '101234567893',
                'cin' => '123456789015',
                'sexe' => 'F',
                'lieu_cin' => 'Mahajanga',
                'date_cin' => '2021-03-25',
                'centre_id' => $centres->random()->id,
                'typeExamen' => 'ConcoursLTP',
                'annee' => 2024,
                'situation' => 'Actif'
            ],
            [
                'nom' => 'Andriama',
                'prenom' => 'Lucie',
                'role' => 'Secretaire',
                'im' => '101234567894',
                'cin' => '123456789016',
                'sexe' => 'F',
                'lieu_cin' => 'Antsirabe',
                'date_cin' => '2020-07-08',
                'centre_id' => $centres->random()->id,
                'typeExamen' => 'ConcoursLTP',
                'annee' => 2024,
                'situation' => 'Actif'
            ],
            [
                'nom' => 'Rakotondrabe',
                'prenom' => 'Marc',
                'role' => 'SecOrg',
                'im' => '101234567895',
                'cin' => '123456789017',
                'sexe' => 'M',
                'lieu_cin' => 'Toliara',
                'date_cin' => '2019-09-12',
                'centre_id' => $centres->random()->id,
                'typeExamen' => 'ConcoursLTP',
                'annee' => 2024,
                'situation' => 'Actif'
            ],
            [
                'nom' => 'Rasolo',
                'prenom' => 'David',
                'role' => 'Surveillance',
                'im' => '101234567896',
                'cin' => '123456789018',
                'sexe' => 'M',
                'lieu_cin' => 'Antsiranana',
                'date_cin' => '2021-01-30',
                'centre_id' => $centres->random()->id,
                'typeExamen' => 'ConcoursLTP',
                'annee' => 2024,
                'situation' => 'Actif'
            ],
            [
                'nom' => 'Ravelo',
                'prenom' => 'Emma',
                'role' => 'Securite',
                'im' => '101234567897',
                'cin' => '123456789019',
                'sexe' => 'F',
                'lieu_cin' => 'Morondava',
                'date_cin' => '2020-12-05',
                'centre_id' => $centres->random()->id,
                'typeExamen' => 'ConcoursLTP',
                'annee' => 2024,
                'situation' => 'Actif'
            ],
            [
                'nom' => 'Razafindrabe',
                'prenom' => 'Pierre',
                'role' => 'Correcteur',
                'im' => '101234567898',
                'cin' => '123456789020',
                'sexe' => 'M',
                'lieu_cin' => 'Ambatondrazaka',
                'date_cin' => '2019-06-18',
                'centre_id' => $centres->random()->id,
                'typeExamen' => 'ConcoursLTP',
                'annee' => 2024,
                'situation' => 'Actif'
            ]
        ];

        // Créer les agents prédéfinis
        foreach ($predefinedAgents as $agentData) {
            try {
                // Vérifier si l'agent existe déjà (par CIN)
                $existingAgent = Agent::where('cin', $agentData['cin'])->first();
                if ($existingAgent) {
                    echo "⚠️  L'agent {$agentData['nom']} {$agentData['prenom']} existe déjà\n";
                    continue;
                }

                // Créer l'agent
                $agent = Agent::create([
                    'nom' => $agentData['nom'],
                    'prenom' => $agentData['prenom'],
                    'role' => $agentData['role'],
                    'im' => $agentData['im'],
                    'cin' => $agentData['cin'],
                    'sexe' => $agentData['sexe'],
                    'lieu_cin' => $agentData['lieu_cin'],
                    'date_cin' => $agentData['date_cin'],
                    'centre_id' => $agentData['centre_id'],
                    'typeExamen' => $agentData['typeExamen'],
                    'annee' => $agentData['annee'],
                    'situation' => $agentData['situation'],
                    'created_by' => 'AgentSeeder',
                    'updated_by' => 'AgentSeeder'
                ]);

                $agentsCreated++;
                echo "✅ Agent créé : {$agent->nom} {$agent->prenom} ({$agent->role})\n";
            } catch (\Exception $e) {
                echo "❌ Erreur lors de la création de l'agent {$agentData['nom']}: {$e->getMessage()}\n";
            }
        }

        // Agents avec des rôles personnalisés (si existent)
        $customAgents = [
            [
                'nom' => 'Rakotomalala',
                'prenom' => 'Antoine',
                'role' => 'Korakary', // Notre rôle personnalisé
                'im' => '101234567899',
                'cin' => '123456789021',
                'sexe' => 'M',
                'lieu_cin' => 'Moramanga',
                'date_cin' => '2021-08-22',
                'centre_id' => $centres->random()->id,
                'typeExamen' => 'ConcoursLTP',
                'annee' => 2024,
                'situation' => 'Actif'
            ]
        ];

        // Créer les agents personnalisés
        foreach ($customAgents as $agentData) {
            try {
                // Vérifier si l'agent existe déjà
                $existingAgent = Agent::where('cin', $agentData['cin'])->first();
                if ($existingAgent) {
                    echo "⚠️  L'agent personnalisé {$agentData['nom']} {$agentData['prenom']} existe déjà\n";
                    continue;
                }

                // Créer l'agent
                $agent = Agent::create([
                    'nom' => $agentData['nom'],
                    'prenom' => $agentData['prenom'],
                    'role' => $agentData['role'],
                    'im' => $agentData['im'],
                    'cin' => $agentData['cin'],
                    'sexe' => $agentData['sexe'],
                    'lieu_cin' => $agentData['lieu_cin'],
                    'date_cin' => $agentData['date_cin'],
                    'centre_id' => $agentData['centre_id'],
                    'typeExamen' => $agentData['typeExamen'],
                    'annee' => $agentData['annee'],
                    'situation' => $agentData['situation'],
                    'created_by' => 'AgentSeeder',
                    'updated_by' => 'AgentSeeder'
                ]);

                $agentsCreated++;
                echo "✅ Agent personnalisé créé : {$agent->nom} {$agent->prenom} ({$agent->role})\n";
            } catch (\Exception $e) {
                echo "❌ Erreur lors de la création de l'agent personnalisé {$agentData['nom']}: {$e->getMessage()}\n";
            }
        }

        echo "\n🎉 AgentSeeder terminé !\n";
        echo "📊 Résumé :\n";
        echo "   - {$agentsCreated} agents créés avec succès\n";
        echo "   - " . count($predefinedAgents) . " rôles prédéfinis\n";
        echo "   - " . count($customAgents) . " rôles personnalisés\n";
        echo "\n💡 Ces agents utilisent les taux définis dans TauxRoleSeeder\n";
        echo "🔍 Vous pouvez maintenant tester les calculs de paiement dans l'interface !\n";
    }
}
