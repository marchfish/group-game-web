<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_role', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_bin';

            $table->increments('id')->comment('主键 id');
            $table->unsignedInteger('user_id')->comment('用户 user.id');
            $table->string('name')->comment('角色名');
            $table->unsignedInteger('hp')->default(100)->comment('血量');
            $table->unsignedInteger('max_hp')->default(100)->comment('血量上限');
            $table->unsignedInteger('mp')->default(100)->comment('蓝量');
            $table->unsignedInteger('max_mp')->default(100)->comment('蓝量上限');
            $table->unsignedInteger('attack')->default(10)->comment('攻击力');
            $table->unsignedInteger('magic')->default(10)->comment('魔力');
            $table->unsignedInteger('crit')->default(0)->comment('暴击');
            $table->unsignedInteger('dodge')->default(0)->comment('闪避');
            $table->unsignedInteger('defense')->default(5)->comment('防御力');
            $table->unsignedInteger('level')->default(1)->comment('等级');
            $table->unsignedInteger('exp')->default(0)->comment('经验');
            $table->unsignedInteger('fame')->default(1)->comment('称号');
            $table->unsignedInteger('map_id')->default(1)->comment('当前位置/位置id');
            $table->unsignedInteger('coin')->default(5000)->comment('金币');
            $table->unsignedTinyInteger('gender')->default(2)->comment('性别: 0-女，1-男，2-人妖');
            $table->unsignedTinyInteger('status')->default(200)->comment('状态：0-禁用，200-正常');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('修改时间');

            $table->unique('name');
        });

        DB::statement("ALTER TABLE `user_role` comment '角色表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_role');
    }
}
