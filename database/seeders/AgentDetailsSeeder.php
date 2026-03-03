<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agent;
use App\Models\AgentDetail;

/**
 * AgentDetailsSeeder - Création des détails de paiement pour les agents
 * 
 * Ce seeder crée les enregistrements dans la table agent_details pour chaque agent,
 * avec des données réalistes pour tester les calculs de paiement.
 */
class AgentDetailsSeeder extends Seeder
{
    /**
     * Exécuter le seeder
     */
    public function run(): void
    {
        echo "🚀 Création des détails de paiement pour les agents...\n";

        // Récupérer tous les agents
        $agents = Agent::all();
        if ($agents->isEmpty()) {
            echo "⚠️  Aucun agent trouvé. Veuillez d'abord exécuter AgentSeeder\n";
            return;
        }

        $detailsCreated = 0;
        echo "📊 Traitement de {$agents->count()} agents...\n";

        foreach ($agents as $agent) {
            try {
                // Vérifier si les détails existent déjà
                $existingDetails = AgentDetail::where('agent_id', $agent->id)->first();
                if ($existingDetails) {
                    echo "⚠️  L'agent {$agent->nom} {$agent->prenom} a déjà des détails\n";
                    continue;
                }

                // Créer les détails selon le rôle de l'agent
                $detailsData = $this->generateDetailsForRole($agent->role);
                
                $agentDetail = AgentDetail::create([
                    'agent_id' => $agent->id,
                    'created_by' => 'AgentDetailsSeeder',
                    'updated_by' => 'AgentDetailsSeeder',
                    ...$detailsData
                ]);

                $detailsCreated++;
                echo "✅ Détails créés pour : {$agent->nom} {$agent->prenom} ({$agent->role})\n";
                echo "   → Taux brut : {$agentDetail->taux_brut} Ar | Taux net : {$agentDetail->taux_net} Ar\n";
            } catch (\Exception $e) {
                echo "❌ Erreur lors de la création des détails pour {$agent->nom}: {$e->getMessage()}\n";
            }
        }

        echo "\n🎉 AgentDetailsSeeder terminé !\n";
        echo "📊 Résumé :\n";
        echo "   - {$detailsCreated} détails créés avec succès\n";
        echo "   - {$agents->count()} agents traités\n";
        echo "\n💡 Les détails utilisent les taux de TauxRoleSeeder\n";
        echo "🔍 Vous pouvez maintenant tester les calculs dans l'interface !\n";
    }

    /**
     * Générer les données de détails selon le rôle
     */
    private function generateDetailsForRole($role)
    {
        // Taux selon le rôle (basé sur TauxRoleSeeder)
        $tauxRates = [
            'PDO' => ['forfaitaire' => 150000],
            'VPDO' => ['forfaitaire' => 150000],
            'CDC' => ['forfaitaire' => 130000],
            'CDCA' => ['forfaitaire' => 130000],
            'Secretaire' => ['journalier' => 8000],
            'SecOrg' => ['journalier' => 8000],
            'Surveillance' => ['journalier' => 9000],
            'Securite' => ['journalier' => 9000],
            'Correcteur' => [
                'base' => 30000,
                'surplus_bep' => 300,
                'surplus_autres' => 200
            ],
            'Korakary' => ['journalier' => 2000] // Rôle personnalisé
        ];

        $rate = $tauxRates[$role] ?? ['journalier' => 5000]; // Valeur par défaut

        // Générer les détails selon le type de taux
        if (isset($rate['forfaitaire'])) {
            // Rôle forfaitaire
            $tauxBrut = $rate['forfaitaire'];
            $irsa = $this->calculateIRSA($tauxBrut);
            $tauxNet = $tauxBrut - $irsa;

            return [
                'taux_forfaitaire' => $rate['forfaitaire'],
                'jours_travaille' => null,
                'taux_journalier' => null,
                'jours_surveillance' => null,
                'jours_encours' => null,
                'jours_ensalles' => null,
                'taux_par_jour' => null,
                'matiere' => null,
                'nombre_copie' => null,
                'taux_par_copie' => null,
                'taux_brut' => $tauxBrut,
                'irsa' => $irsa,
                'taux_net' => $tauxNet
            ];
        } elseif (isset($rate['base'])) {
            // Rôle correcteur
            $nombreCopie = rand(80, 150); // Nombre de copies realistic
            $tauxBase = $rate['base'];
            $surplusCopies = max(0, $nombreCopie - 100);
            
            $tauxBrut = $tauxBase;
            if ($surplusCopies > 0) {
                // Supposons 50% BEP, 50% autres pour l'exemple
                $tauxBrut += ($surplusCopies * 0.5 * $rate['surplus_bep']);
                $tauxBrut += ($surplusCopies * 0.5 * $rate['surplus_autres']);
            }
            
            $irsa = $this->calculateIRSA($tauxBrut);
            $tauxNet = $tauxBrut - $irsa;

            return [
                'taux_forfaitaire' => null,
                'jours_travaille' => null,
                'taux_journalier' => null,
                'jours_surveillance' => null,
                'jours_encours' => null,
                'jours_ensalles' => null,
                'taux_par_jour' => null,
                'matiere' => 'Mathématiques',
                'nombre_copie' => $nombreCopie,
                'taux_par_copie' => $rate['surplus_bep'], // Taux de référence
                'taux_brut' => $tauxBrut,
                'irsa' => $irsa,
                'taux_net' => $tauxNet
            ];
        } else {
            // Rôle journalier
            $joursTravaille = rand(15, 30); // Jours travaillés plus élevés pour IRSA
            $tauxJournalier = $rate['journalier'];
            $tauxBrut = $joursTravaille * $tauxJournalier;
            $irsa = $this->calculateIRSA($tauxBrut);
            $tauxNet = $tauxBrut - $irsa;

            return [
                'taux_forfaitaire' => null,
                'jours_travaille' => $joursTravaille,
                'taux_journalier' => $tauxJournalier,
                'jours_surveillance' => null,
                'jours_encours' => null,
                'jours_ensalles' => null,
                'taux_par_jour' => null,
                'matiere' => null,
                'nombre_copie' => null,
                'taux_par_copie' => null,
                'taux_brut' => $tauxBrut,
                'irsa' => $irsa,
                'taux_net' => $tauxNet
            ];
        }
    }

    /**
     * Calculer l'IRSA (simplifié)
     */
    private function calculateIRSA($salaire)
    {
        // Tranches IRSA Madagascar (simplifié)
        if ($salaire <= 200000) {
            return 0;
        } elseif ($salaire <= 250000) {
            return ($salaire - 200000) * 0.05;
        } elseif ($salaire <= 350000) {
            return 2500 + ($salaire - 250000) * 0.10;
        } elseif ($salaire <= 450000) {
            return 12500 + ($salaire - 350000) * 0.15;
        } else {
            return 27500 + ($salaire - 450000) * 0.20;
        }
    }
}
