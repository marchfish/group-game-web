<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnemyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enemy', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_bin';

            $table->increments('id')->comment('主键 id');
            $table->string('name')->comment('怪物名');
            $table->unsignedInteger('hp')->default(100)->comment('血量');
            $table->unsignedInteger('mp')->default(100)->comment('蓝量');
            $table->unsignedInteger('attack')->default(10)->comment('攻击力');
            $table->unsignedInteger('magic')->default(10)->comment('魔力');
            $table->unsignedInteger('crit')->default(0)->comment('暴击');
            $table->unsignedInteger('dodge')->default(0)->comment('闪避');
            $table->unsignedInteger('defense')->default(5)->comment('防御力');
            $table->unsignedInteger('level')->default(1)->comment('等级');
            $table->unsignedInteger('exp')->default(0)->comment('经验');
            $table->unsignedInteger('coin')->default(0)->comment('金币');
            $table->string('items')->default('')->comment('掉落物品:json');
            $table->string('certain_items')->default('')->comment('必掉物品:json');
            $table->unsignedInteger('probability')->default(0)->comment('掉落几率');
            $table->string('description')->default('')->comment('描述');
            $table->string('type')->default('')->comment('属性');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('修改时间');
        });

        DB::statement("ALTER TABLE `enemy` comment '怪物表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enemy');
    }
}
