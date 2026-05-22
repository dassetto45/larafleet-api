<?php

namespace App\Jobs;

use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class MaintenanceReminderJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        // Manutenzioni programmate nei prossimi 3 giorni non ancora completate
        $upcoming = Maintenance::query()
            ->whereNull('completed_at')
            ->whereBetween('scheduled_at', [now(), now()->addDays(3)])
            ->with('vehicle')
            ->get();

        if ($upcoming->isEmpty())
            return;

        // Notifica tutti gli admin
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            \Illuminate\Support\Facades\Log::info(
                "Maintenance reminder sent to {$admin->email}: " .
                $upcoming->count() . " upcoming maintenances"
            );
            // Qui in futuro si può inviare una vera email con la lista
        }
    }
}
