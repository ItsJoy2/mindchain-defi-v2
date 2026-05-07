<?php

namespace App\Http\Controllers;

use App\Jobs\ImportTransactionsJob;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationController extends Controller
{
    public function migrateTestUsersToUsers()
    {
        // try {

        //     DB::statement('SET FOREIGN_KEY_CHECKS=0');

        //     $source = 'test_users';
        //     $destination = 'users';

        //     // only destination table columns
        //     $validColumns = Schema::getColumnListing($destination);

        //     DB::table($source)->orderBy('id')->chunk(500, function ($rows) use ($destination, $validColumns) {

        //         $insertData = [];

        //         foreach ($rows as $row) {

        //             $data = [];

        //             foreach ($row as $key => $value) {

        //                 // ONLY allow columns that exist in users table
        //                 if (in_array($key, $validColumns)) {
        //                     $data[$key] = $value;
        //                 }
        //             }

        //             // ensure ID included
        //             if (isset($row->id) && in_array('id', $validColumns)) {
        //                 $data['id'] = $row->id;
        //             }

        //             $data['created_at'] = $row->created_at ?? now();
        //             $data['updated_at'] = $row->updated_at ?? now();

        //             $insertData[] = $data;
        //         }

        //         DB::table($destination)->insert($insertData);
        //     });

        //     DB::statement('SET FOREIGN_KEY_CHECKS=1');

        //     return response()->json([
        //         'status' => true,
        //         'message' => '🎉 Migration completed successfully (no mapping, auto field match)!'
        //     ]);

        // } catch (\Exception $e) {

        //     DB::statement('SET FOREIGN_KEY_CHECKS=1');

        //     return response()->json([
        //         'status' => false,
        //         'message' => '❌ Migration failed',
        //         'error' => $e->getMessage()
        //     ], 500);
        // }




    //         try {

    //     $oldDB = 'mindchain_defi_v2';
    //     $newDB = 'mindchain_defi_v2_new';

    //     $oldUsers = DB::connection('old_mysql')
    //         ->table('users')
    //         ->select('id', 'created_at', 'updated_at')
    //         ->get();

    //     foreach ($oldUsers as $user) {

    //         DB::connection('mysql')
    //             ->table('users')
    //             ->where('id', $user->id)
    //             ->update([
    //                 'created_at' => $user->created_at,
    //                 'updated_at' => $user->updated_at,
    //             ]);
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'message' => '🎉 Timestamps synced successfully!'
    //     ]);

    // } catch (\Exception $e) {

    //     return response()->json([
    //         'status' => false,
    //         'message' => '❌ Sync failed',
    //         'error' => $e->getMessage()
    //     ], 500);
    // }

    // DB::connection('old_mysql')
    //     ->table('mind_wallets')
    //     ->orderBy('id')
    //     ->chunk(1000, function ($rows) {

    //         $insertData = [];

    //         foreach ($rows as $row) {

    //             $insertData[] = [
    //                 'user_id' => (int) $row->user_id,

    //                 // remove negative values
    //                 'amount' => abs((float) $row->amount),

    //                 // wallet fixed (as per your design)
    //                 'wallet' => $row->wallet ?? 'MIND',

    //                 // type logic from amount
    //                 'type' => ((float) $row->amount < 0) ? 'Debit' : 'Credit',

    //                 'method' => $row->method ?? null,
    //                 'description' => $row->description ?? null,

    //                 'txn_id' => $row->txn_id ?? uniqid('txn_'),

    //                 'confirmation_code' => $row->confirmation_code ?? null,

    //                 'kids_username' => $row->kids_username ?? null,

    //                 'status' => $row->status ?? 'Pending',

    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ];
    //         }

    //         // insert into NEW DB
    //         DB::connection('mysql')
    //             ->table('transactions')
    //             ->insert($insertData);
    //     });

    // return response()->json([
    //     'status' => true,
    //     'message' => 'Migration completed successfully 🚀'
    // ]);

DB::connection('old_mysql')
    ->table('bonus_wallets')
    ->orderBy('id')
    ->chunk(1000, function ($rows) {

        $insertData = [];

        // txn_id collect for duplicate check
        $txnIds = collect($rows)
            ->pluck('txn_id')
            ->filter()
            ->toArray();

        // already existing txn_id (NEW DB)
        $existingTxnIds = DB::connection('mysql')
            ->table('transactions')
            ->whereIn('txn_id', $txnIds)
            ->pluck('txn_id')
            ->toArray();

        foreach ($rows as $row) {

            // skip duplicate
            if ($row->txn_id && in_array($row->txn_id, $existingTxnIds)) {
                continue;
            }

            $insertData[] = [
                'user_id' => (int) $row->user_id,

                // negative remove
                'amount' => abs((float) $row->amount),

                // wallet fixed
                'wallet' => $row->wallet ?? 'MIND',

                // type detect
                'type' => ((float) $row->amount < 0) ? 'Debit' : 'Credit',

                'method' => $row->method ?? null,
                'description' => $row->description ?? null,

                // ensure unique txn_id
                'txn_id' => $row->txn_id ?? uniqid('txn_'),

                'confirmation_code' => $row->confirmation_code ?? null,
                'kids_username' => $row->kids_username ?? null,

                // status mapping
                'status' => $row->status ?? 'Pending',

                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ];
        }

        // insert safely (no duplicate crash)
        if (!empty($insertData)) {
            DB::connection('mysql')
                ->table('transactions')
                ->insertOrIgnore($insertData);
        }
    });

return response()->json([
    'status' => true,
    'message' => 'Migration running in chunks 🚀'
]);









    }
}
