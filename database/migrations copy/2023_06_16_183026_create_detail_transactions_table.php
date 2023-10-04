<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('montant_transaction')->defaul(0);
            $table->text('description_transaction')->nullable();
            $table->bigInteger('id_user_created')->default(0);
            $table->bigInteger('id_user_updated')->default(0);
            $table->bigInteger('id_user_deleted')->default(0);
            $table->boolean('is_delete')->default(FALSE);
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
        Schema::dropIfExists('detail_transactions');
    }
}
