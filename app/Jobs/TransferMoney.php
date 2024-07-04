<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class TransferMoney implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $recipient;
    protected $amount;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, User $recipient, $amount, $remarks, $balanceBefore, $balanceAfter)
    {
        $this->user = $user;
        $this->recipient = $recipient;
        $this->amount = $amount;
        $this->remarks = $remarks;
        $this->balanceBefore = $balanceBefore;
        $this->balanceAfter = $balanceAfter;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $this->user->balance -= $this->amount;
            $this->user->save();

            $this->recipient->balance += $this->amount;
            $this->recipient->save();

            Transaction::create([
                'user_id' => $this->user->id,
                'amount' => $this->amount,
                'type' => 'transfer',
                'remarks' => $this->remarks,
                'balance_before' => $this->balanceBefore,
                'balance_after' => $this->balanceAfter,
            ]);

            Transaction::create([
                'user_id' => $this->recipient->id,
                'amount' => $this->amount,
                'type' => 'received',
            ]);
        });
    }
}
