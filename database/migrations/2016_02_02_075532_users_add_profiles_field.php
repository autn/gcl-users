<?php

namespace {

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    /**
     * @codeCoverageIgnore
     */
    class UsersAddProfilesField extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::table('users', function (Blueprint $table) {
                // $table->increments('id');
                $table->string('username', 30)->nullable();
                $table->string('location', 100)->nullable();
                $table->string('country', 100)->nullable();
                $table->string('biography', 255)->nullable();
                $table->string('occupation', 255)->nullable();
                $table->string('website', 255)->nullable();
                $table->string('image', 255)->nullable();
                $table->integer('status')->default(0);
                $table->date('birthday')->nullable();
                $table->tinyInteger('gender')->nullable();
                $table->softDeletes();
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::drop('users');
        }
    }
}
