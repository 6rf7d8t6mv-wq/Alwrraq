<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('order_files')
            ->where('cd_type', 'printed')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('orders')
                    ->whereColumn('orders.id', 'order_files.order_id')
                    ->where('orders.payment_status', '!=', 'paid');
            })
            ->orderBy('id')
            ->chunkById(100, function ($files): void {
                foreach ($files as $file) {
                    $newCdPrice = max(1, (int) $file->cd_copies) * 10;

                    DB::table('order_files')
                        ->where('id', $file->id)
                        ->update([
                            'cd_price' => $newCdPrice,
                            'total_price' => max(0, (float) $file->total_price - (float) $file->cd_price + $newCdPrice),
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('order_files')
            ->where('cd_type', 'printed')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('orders')
                    ->whereColumn('orders.id', 'order_files.order_id')
                    ->where('orders.payment_status', '!=', 'paid');
            })
            ->orderBy('id')
            ->chunkById(100, function ($files): void {
                foreach ($files as $file) {
                    $oldCdPrice = max(1, (int) $file->cd_copies) * 15;

                    DB::table('order_files')
                        ->where('id', $file->id)
                        ->update([
                            'cd_price' => $oldCdPrice,
                            'total_price' => max(0, (float) $file->total_price - (float) $file->cd_price + $oldCdPrice),
                        ]);
                }
            });
    }
};
