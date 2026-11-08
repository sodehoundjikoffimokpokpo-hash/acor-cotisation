<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Exécute la vérification chaque soir à 18h00
Schedule::command('acor:check-alerts')->dailyAt('18:00');




// 1. Sauvegarde quotidienne (chaque jour à 02:00)
Schedule::command('backup:run')->dailyAt('02:00');

// 2. Sauvegarde hebdomadaire (chaque dimanche à 03:00)
Schedule::command('backup:run')->weeklyOn(0, '03:00');

// 3. Sauvegarde mensuelle (le 1er jour du mois à 04:00)
Schedule::command('backup:run')->monthlyOn(1, '04:00');

// 4. Nettoyage automatique des anciennes sauvegardes (tous les jours à 05:00)
Schedule::command('backup:clean')->dailyAt('05:00');
