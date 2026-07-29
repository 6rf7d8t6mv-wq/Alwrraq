<?php

namespace App\Console\Commands;

use App\Services\Payments\MoyasarPaymentService;
use Illuminate\Console\Command;
use Throwable;

class ReconcileMoyasarPayments extends Command
{
    protected $signature = 'payments:reconcile-moyasar';

    protected $description = 'Reconcile pending local payment attempts with paid Moyasar transactions';

    public function handle(MoyasarPaymentService $moyasar): int
    {
        try {
            $completed = $moyasar->reconcilePendingAttempts();
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Moyasar payment reconciliation failed.');

            return self::FAILURE;
        }

        $this->info("Reconciled {$completed} paid payment attempt(s).");

        return self::SUCCESS;
    }
}
