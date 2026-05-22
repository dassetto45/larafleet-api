<?php

use App\Jobs\MaintenanceReminderJob;
use App\Jobs\ReleaseExpiredBookingsJob;
use Illuminate\Support\Facades\Schedule;

// Libera i veicoli con prenotazioni scadute ogni ora
Schedule::job(new ReleaseExpiredBookingsJob)->hourly();

// Controlla manutenzioni imminenti ogni giorno alle 8:00
Schedule::job(new MaintenanceReminderJob)->dailyAt('08:00');
