<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\TransferMoney;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();

        $balanceBefore = $user->balance;

        $user->balance += $request->amount;
        $user->save();

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'type' => 'top-up',
            'balance_before' => $balanceBefore,
            'balance_after' => $user->balance,
        ]);

        return response()->json([
            'status' => 'SUCCESS',
            'result' => [
                'top_up_id' => $transaction->uuid,
                'amount_top_up' => $request->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->balance,
                'created_date' => $transaction->created_at,
            ]
        ]);
    }

    public function payment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();

        $balanceBefore = $user->balance;

        if ($user->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        $user->balance -= $request->amount;
        $user->save();

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'type' => 'payment',
            'remarks' => $request->remarks,
            'balance_before' => $balanceBefore,
            'balance_after' => $user->balance,
        ]);

        return response()->json([
            'status' => 'SUCCESS',
            'result' => [
                'payment_id' => $transaction->uuid,
                'amount' => $request->amount,
                'remarks' => $request->remarks,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->balance,
                'created_date' => $transaction->created_at,
            ]
        ]);
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'recipient_id' => 'required|exists:users,uuid',
        ]);

        $user = Auth::user();

        $balanceBefore = $user->balance;

        if ($user->balance < $request->amount) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $recipient = User::where('uuid', $request->recipient_id)->first();

        $transaction = TransferMoney::dispatch(
            $user,
            $recipient,
            $request->amount,
            $request->remarks,
            $balanceBefore,
            $user->balance
        );

        return response()->json([
            'status' => 'SUCCESS',
            'result' => [
                'transfer_id' => $transaction->uuid,
                'amount' => $request->amount,
                'remarks' => $request->remarks,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->balance,
                'created_date' => $transaction->created_at,
            ]
        ]);
    }

    public function transactions()
    {
        $user = Auth::user();
        $transactions = Transaction::where('user_id', $user->id)->orderBy('id', 'desc')->get();
        $result = [];

        foreach ($transactions as $transaction) {
            $result[] = [
                'transfer_id' => $transaction->id,
                'status' => 'SUCCESS',
                'user_id' => $transaction->user_id,
                'transaction_type' => $transaction->type,
                'amount' => $transaction->amount,
                'remarks' => $transaction->remarks,
                'balance_before' => $transaction->balance_before,
                'balance_after' => $transaction->balance_after,
                'created_date' => $transaction->created_at,
            ];
        }

        return response()->json([
            'status' => 'SUCCESS',
            'result' => $result
        ]);
    }
}
