<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->uuid('uuid');
			$table->string('type_id', 64)->nullable();
			$table->nullableMorphs('attachable');
			$table->string('group_id', 64)->nullable();
			$table->string('disk_id');
			$table->string('filepath');
			$table->string('filename');
			$table->unsignedInteger('filesize');
			$table->string('mimetype');
			$table->string('description')->nullable();
			$table->char('token', 36)->nullable();
			$table->json('extra_data')->nullable();
			$table->unsignedInteger('is_referred')->nullable();
            $table->timestamps();
			$table->softDeletes();

			$table->unique('uuid');
			$table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attachments');
    }
}
