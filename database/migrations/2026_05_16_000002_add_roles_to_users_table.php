<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('roles')->nullable()->after('password');
        });

        DB::table('users')->get()->each(function ($user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['roles' => json_encode([$user->role ?? 'user'])]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password');
        });

        DB::table('users')->get()->each(function ($user) {
            $roles = json_decode($user->roles, true) ?: ['user'];
            DB::table('users')
                ->where('id', $user->id)
                ->update(['role' => $roles[0] ?? 'user']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('roles');
        });
    }
};
