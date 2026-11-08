<?php

namespace App\Helpers;

class AuditLogFormatter
{
    // Dans app/Helpers/AuditLogFormatter.php

protected static array $labels = [
    // ... tes labels existants
    'last_login_at'     => 'Dernière connexion',
    'nom'               => 'Nom', // Si besoin de forcer
    'prenom'            => 'Prénom',
    'telephone'         => 'Téléphone',
    'address'           => 'Adresse',
    'daily_contribution'=> 'Cotisation journalière',
    'target_amount'     => 'Montant cible',
    'desired_article'   => 'Article souhaité',
    'statut'            => 'Statut',
    'collector_id'      => 'ID Collecteur',
    'client_id'         => 'ID Client',
    'id'                => 'ID',
];
    protected static array $excluded = [
    'updated_at',
    'created_at',
    'password',
    'remember_token',
    'model_type',    // Ajouté
    'model_id',      // Ajouté
    'new_values',    // Ajouté (puisque le diff s'en occupe déjà)
    'old_values',    // Ajouté
];

    /**
     * Transforme une donnée (JSON ou Tableau) en tableau ['label' => 'valeur formatée'].
     */
    public static function toArray($data): array
    {
        if (empty($data)) {
            return [];
        }

        // Si c'est une chaîne JSON, on décode. Si c'est déjà un tableau, on garde.
        $data = is_string($data) ? json_decode($data, true) : $data;

        if (!is_array($data)) {
            return [];
        }

        $result = [];
        foreach ($data as $key => $value) {
            if (in_array($key, self::$excluded)) {
                continue;
            }

            $label = self::$labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
            $result[$label] = self::formatValue($value);
        }

        return $result;
    }

    /**
     * Combine old_values et new_values pour afficher "Champ : ancien → nouveau"
     */
    public static function diff($oldData, $newData): array
    {
        $old = self::toArray($oldData);
        $new = self::toArray($newData);

        $lines = [];
        foreach ($new as $label => $newValue) {
            $oldValue = $old[$label] ?? '—';
            // On n'affiche la ligne que si la valeur a vraiment changé
            if ($oldValue !== $newValue) {
                $lines[] = [
                    'label' => $label,
                    'old'   => $oldValue,
                    'new'   => $newValue,
                ];
            }
        }

        return $lines;
    }

    protected static function formatValue($value): string
    {
        if (is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }

        if ($value === '1' || $value === 1) {
            return 'Oui';
        }

        if ($value === '0' || $value === 0) {
            return 'Non';
        }

        if (is_null($value)) {
            return '—';
        }

        return (string) $value;
    }
}
