<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Les commandes Artisan de votre application.
     * (Laravel les détecte automatiquement dans App\Console\Commands,
     *  mais vous pouvez les lister ici explicitement si besoin)
     */
    protected $commands = [
        \App\Console\Commands\ImportAchatsAuto::class,
    ];

    /**
     * Planification des tâches automatiques.
     *
     * Cette méthode est appelée par le scheduler Laravel.
     * Pour que ça fonctionne sous Windows, vous devez configurer
     * le Planificateur de tâches Windows (voir instructions ci-dessous).
     */
    protected function schedule(Schedule $schedule): void
    {
        // ✅ Lancer l'import automatique chaque jour à 08h00
        // Adaptez l'heure selon vos besoins (ex: '07:00', '09:00')
        $schedule->command('import:achats-auto')
            ->dailyAt('08:00')
            ->withoutOverlapping()       // Évite les doublons si la commande tourne déjà
            ->appendOutputTo(storage_path('logs/import-achats.log')); // Log dédié
    }

    /**
     * Enregistrement des commandes de l'application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
