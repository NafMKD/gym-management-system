<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('invoices', 'user_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->string('invoice_source', 32)->default('membership')->after('membership_id');
            });
        }

        $this->dropForeignKeyOnColumn('invoices', 'membership_id');

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('membership_id')->nullable()->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('cascade');
        });

        $this->dropForeignKeyOnColumn('payments', 'membership_id');

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('membership_id')->nullable()->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        $this->dropForeignKeyOnColumn('payments', 'membership_id');

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('membership_id')->nullable(false)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('cascade');
        });

        $this->dropForeignKeyOnColumn('invoices', 'membership_id');

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('membership_id')->nullable(false)->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('cascade');
        });

        if (Schema::hasColumn('invoices', 'user_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn(['invoice_source', 'user_id']);
            });
        }
    }

    /**
     * Drop a foreign key on the given column if it exists (MySQL constraint names vary; legacy tables may have no FK).
     */
    private function dropForeignKeyOnColumn(string $table, string $column): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $dbName = DB::getDatabaseName();
            $rows = DB::select(
                'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
                [$dbName, $table, $column]
            );
            foreach ($rows as $row) {
                $name = $row->CONSTRAINT_NAME;
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$name}`");
            }

            return;
        }

        if (in_array($driver, ['pgsql', 'sqlsrv'], true)) {
            try {
                Schema::table($table, function (Blueprint $blueprint) use ($column) {
                    $blueprint->dropForeign([$column]);
                });
            } catch (\Throwable) {
                //
            }

            return;
        }

        // SQLite: may or may not have a named FK; try Laravel helper.
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropForeign([$column]);
            });
        } catch (\Throwable) {
            //
        }
    }
};
