<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('league_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->mediumText('description')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_team_id')->constrained('teams');
            $table->foreignId('away_team_id')->constrained('teams');
            $table->foreignId('stage_id')->constrained('league_stages');
            $table->smallInteger('home_team_score')->default(0);
            $table->smallInteger('away_team_score')->default(0);
            $table->unique(['stage_id', 'home_team_id', 'away_team_id']);
            $table->timestamp('played_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams');
            $table->smallInteger('played')->default(0);
            $table->smallInteger('won')->default(0);
            $table->smallInteger('draw')->default(0);
            $table->smallInteger('loss')->default(0);
            $table->smallInteger('points')->default(0);
            $table->float('power')->default(0);
            $table->float('prediction')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('fixtures');
        Schema::drop('league_stages');
        Schema::drop('stats');
        Schema::drop('teams');
    }
};
