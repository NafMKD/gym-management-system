<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'created_by_user_id')) {
                $table->foreignId('created_by_user_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        Schema::table('memberships', function (Blueprint $table) {
            if (! Schema::hasColumn('memberships', 'created_by_user_id')) {
                $table->foreignId('created_by_user_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            if (Schema::hasColumn('memberships', 'created_by_user_id')) {
                $table->dropConstrainedForeignId('created_by_user_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'created_by_user_id')) {
                $table->dropConstrainedForeignId('created_by_user_id');
            }
        });
    }
};
