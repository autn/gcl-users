<?php

namespace {

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;

    /**
     * @codeCoverageIgnore
     */
    class RolesSetupTables extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return  void
         */
        public function up()
        {
            // Create table for storing roles
            Schema::create('roles', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->string('display_name')->nullable();
                $table->string('description')->nullable();
                $table->integer('active')->unsigned()->nullable();
                $table->timestamps();
            });

            // Create table for associating roles to users (Many-to-Many)
            Schema::create('role_user', function (Blueprint $table) {
                $table->integer('user_id')->unsigned();
                $table->integer('role_id')->unsigned();

                $table->foreign('user_id')->references('id')->on('users')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')
                    ->onUpdate('cascade')->onDelete('cascade');

                $table->primary(['user_id', 'role_id']);
            });

            // Create table for storing permissions
            Schema::create('permissions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('display_name')->nullable();
                $table->string('description')->nullable();
                $table->integer('parent_id')->nullable()->index();
                $table->integer('lft')->nullable()->index();
                $table->integer('rgt')->nullable()->index();
                $table->integer('depth')->nullable();
                $table->timestamps();
            });

            // Create table for storing routes of permissions (Many-to-Many)
            Schema::create('permission_route', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('permission_id')->unsigned();
                $table->string('route_name');
                $table->string('route_method');

                $table->foreign('permission_id')->references('id')->on('permissions')
                    ->onUpdate('cascade')->onDelete('cascade');
            });

            // Create table for associating permissions to roles (Many-to-Many)
            Schema::create('permission_role', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('permission_id')->unsigned();
                $table->integer('role_id')->unsigned();
                $table->integer('status')->unsigned();

                $table->foreign('permission_id')->references('id')->on('permissions')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')
                    ->onUpdate('cascade')->onDelete('cascade');

                $table->primary(['permission_id', 'role_id']);
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return  void
         */
        public function down()
        {
            Schema::drop('permission_role');
            Schema::drop('permissions');
            Schema::drop('permission_route');
            Schema::drop('role_user');
            Schema::drop('roles');
        }
    }
}
