<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_bin';

            $table->increments('id');
            $table->string('name')->comment('地图名称');
            $table->unsignedInteger('npc')->default(0)->comment('npc');
            $table->unsignedInteger('enemy')->default(0)->comment('怪物');
            $table->string('description')->default('')->comment('描述');
            $table->unsignedInteger('up')->default(0)->comment('上');
            $table->unsignedInteger('down')->default(0)->comment('下');
            $table->unsignedInteger('left')->default(0)->comment('左');
            $table->unsignedInteger('right')->default(0)->comment('右');
            $table->unsignedInteger('forward')->default(0)->comment('前');
            $table->unsignedInteger('behind')->default(0)->comment('后');
            $table->string('area')->default('')->comment('区域');
            $table->unsignedTinyInteger('is_safe')->default(0)->comment('是否安全区: 0-否，1-是');
            $table->unsignedTinyInteger('is_transfer')->default(0)->comment('是否能传送: 0-否，1-是');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('修改时间');

            $table->unique('name');
        });

        DB::statement("ALTER TABLE `map` COMMENT '地图表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('map');
    }
}
