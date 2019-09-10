<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserKnapsackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_knapsack', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_bin';

            $table->increments('id')->comment('主键 id');
            $table->unsignedInteger('user_id')->comment('用户 id');
            $table->unsignedInteger('user_role_id')->comment('用户角色 id');
            $table->unsignedInteger('item_id')->default(0)->comment('物品id');
            $table->unsignedInteger('item_num')->default(0)->comment('物品数量');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('修改时间');
        });

        DB::statement("ALTER TABLE `user_knapsack` comment '背包表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_knapsack');
    }
}
