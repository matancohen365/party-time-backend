<?php

use App\Track;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTracksTable extends Migration
{
    protected $table = 'tracks';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            $this->table,
            function (Blueprint $table) {

                $table->increments('id');

                $table->string('url')->nullable();
                $table->string('title')->nullable();
                $table->string('artist')->nullable();
                $table->string('album')->nullable();
                $table->integer('year', false, true)->nullable();
                $table->string('genre')->nullable()->default('Other');
                $table->string('track')->nullable()->default('1/1'); // can be integer "1" or a string "1/1"
                $table->string('cover')->nullable();
                $table->text('lyrics')->nullable();
                $table->string('status')->nullable();
                $table->string('file')->nullable();

                $table->unique('url');

                $table->timestamps();

            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table);
    }
}
