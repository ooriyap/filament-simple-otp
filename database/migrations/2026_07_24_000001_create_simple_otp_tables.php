<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Get all target tables to create/update.
     *
     * @return array<int, string>
     */
    protected function getTargetTables(): array
    {
        $tables = [];

        /** @var string $configuredModel */
        $configuredModel = config('filament-simple-otp.admin_model', User::class);

        if (class_exists($configuredModel)) {
            $tables[] = (new $configuredModel)->getTable();
        }

        $tables[] = 'users';

        return array_unique(array_filter($tables));
    }

    public function up(): void
    {
        foreach ($this->getTargetTables() as $table) {
            if (! Schema::hasTable($table)) {
                Schema::create($table, function (Blueprint $t) {
                    $t->id();
                    $t->string('name')->nullable();
                    $t->string('mobile')->unique();
                    $t->string('email')->unique()->nullable();
                    $t->string('avatar')->nullable();
                    $t->string('password')->nullable();
                    $t->boolean('is_active')->default(true);
                    $t->boolean('can_manage_admins')->default(false);
                    $t->rememberToken();
                    $t->timestamps();
                });

                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table) {
                if (! Schema::hasColumn($table, 'mobile')) {
                    $t->string('mobile')->unique()->nullable();
                }
                if (! Schema::hasColumn($table, 'avatar')) {
                    $t->string('avatar')->nullable();
                }
                if (! Schema::hasColumn($table, 'is_active')) {
                    $t->boolean('is_active')->default(true);
                }
                if (! Schema::hasColumn($table, 'can_manage_admins')) {
                    $t->boolean('can_manage_admins')->default(false);
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->getTargetTables() as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    if (Schema::hasColumn($table, 'can_manage_admins')) {
                        $t->dropColumn('can_manage_admins');
                    }
                    if (Schema::hasColumn($table, 'is_active')) {
                        $t->dropColumn('is_active');
                    }
                    if (Schema::hasColumn($table, 'avatar')) {
                        $t->dropColumn('avatar');
                    }
                });
            }
        }
    }
};
