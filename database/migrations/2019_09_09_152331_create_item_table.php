<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_bin';

            $table->increments('id')->comment('主键 id');
            $table->string('name')->comment('物品名称');
            $table->string('description')->default('')->comment('描述');
            $table->string('type')->default('')->comment('属性/用途');
            $table->unsignedInteger('level')->default(0)->comment('等级');
            $table->string('content')->default('')->comment('内容信息:json');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('修改时间');
        });

        DB::statement("ALTER TABLE `item` comment '物品表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item');
    }
}
