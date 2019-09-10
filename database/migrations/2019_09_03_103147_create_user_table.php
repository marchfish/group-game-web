<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_bin';

            $table->increments('id')->comment('主键 id');
            $table->string('username')->comment('用户名');
            $table->string('password')->comment('密码');
            $table->string('nickname')->default('')->comment('昵称');
            $table->string('qq')->default('')->comment('QQ');
            $table->string('email')->default('')->comment('邮箱');
            $table->unsignedTinyInteger('gender')->default(2)->comment('性别: 0-女，1-男，2-人妖');
            $table->unsignedTinyInteger('status')->default(200)->comment('状态：0-禁用，200-正常');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('修改时间');

            $table->unique('username');
            $table->unique('email');
        });

        DB::statement("ALTER TABLE `user` comment '用户表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
