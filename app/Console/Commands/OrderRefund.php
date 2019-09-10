<?php

namespace App\Console\Commands;

use App\Models\SysPay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use InvalidArgumentException;

class OrderRefund extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:refund';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $expireAt = Carbon::now()->subDays(1)->format('Y-m-d H:i:s');

        $orderRows = DB::query()
            ->select([
                'id AS order_id',
            ])
            ->from('order')
            ->where('status', '=', 35)
            ->where('canceled_at', '<', $expireAt)
            ->get()
        ;

        if (!$orderRows->isEmpty()) {
            $order_ids = [];

            foreach ($orderRows as $order) {
                try {
                    DB::beginTransaction();

                    SysPay::refundOrder([
                        'order_id' => $order->order_id,
                    ]);

                    $order_ids[] = $order->order_id;

                    DB::commit();
                } catch (InvalidArgumentException $e) {
                    DB::rollBack();
                }
            }

            Log::info('order-auto-refund:', $order_ids);
        }
    }
}
