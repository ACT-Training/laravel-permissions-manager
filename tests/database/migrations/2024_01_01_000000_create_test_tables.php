<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The UUID-keyed Spatie Permission schema the package expects, plus a users table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->string('model_type');
            $table->uuid('model_uuid');
            $table->foreign('permission_id')->references('uuid')->on('permissions')->onDelete('cascade');
            $table->primary(['permission_id', 'model_uuid', 'model_type']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->uuid('role_id');
            $table->string('model_type');
            $table->uuid('model_uuid');
            $table->foreign('role_id')->references('uuid')->on('roles')->onDelete('cascade');
            $table->primary(['role_id', 'model_uuid', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->uuid('role_id');
            $table->foreign('permission_id')->references('uuid')->on('permissions')->onDelete('cascade');
            $table->foreign('role_id')->references('uuid')->on('roles')->onDelete('cascade');
            $table->primary(['permission_id', 'role_id']);
        });
    }
};
