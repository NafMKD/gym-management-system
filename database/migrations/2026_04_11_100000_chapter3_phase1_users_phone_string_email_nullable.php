<?php

use App\Support\PhoneNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['email']);
            });
        } catch (\Throwable) {
            //
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_new', 10)->nullable()->after('email');
        });

        $rows = DB::table('users')->select('id', 'phone')->get();

        foreach ($rows as $row) {
            $phoneNew = null;
            try {
                $phoneNew = PhoneNumber::fromLegacyDatabaseValue($row->phone);
            } catch (\Throwable $e) {
                Log::warning('chapter3_phone_migration_fallback', [
                    'user_id' => $row->id,
                    'raw' => $row->phone,
                    'message' => $e->getMessage(),
                ]);
                $phoneNew = PhoneNumber::placeholderForUserId((int) $row->id);
            }

            DB::table('users')->where('id', $row->id)->update(['phone_new' => $phoneNew]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('phone_new', 'phone');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 10)->nullable(false)->change();
            $table->unique('phone');
        });
    }

    public function down(): void
    {
        throw new \RuntimeException('chapter3_phase1_users migration cannot be safely reversed; restore the database from backup if needed.');
    }
};
