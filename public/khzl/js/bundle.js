(function () {
	'use strict';

	(function(window){
		var Laya=window.Laya;
		var Animation=Laya.Animation,AtlasInfoManager=Laya.AtlasInfoManager,BoxCollider=Laya.BoxCollider,Browser=Laya.Browser;
		var CircleCollider=Laya.CircleCollider,ClassUtils=Laya.ClassUtils,Event=Laya.Event,Handler=Laya.Handler,Label=Laya.Label;
		var MouseManager=Laya.MouseManager,Pool=Laya.Pool,Prefab=Laya.Prefab,ResourceVersion=Laya.ResourceVersion;
		var RigidBody=Laya.RigidBody,Scene=Laya.Scene,Script=Laya.Script,SoundManager=Laya.SoundManager,Sprite=Laya.Sprite;
		var Stat=Laya.Stat,Text=Laya.Text,URL=Laya.URL,Utils=Laya.Utils;


    // var ws = new WebSocket("ws://10.0.0.131:8282");
    //
    // ws.onmessage = function(e) {
    //   var res = JSON.parse(e.data);
    //
    //   var type = res.type || '';
    //
    //   switch(type){
    //     case 'init':
    //       axios.get('/admin/test',{
    //         params: {
    //           client_id: res.client_id,
    //         }
    //       }).then(function(res){
    //         console.log(res.data);
    //       });
    //       break;
    //     default :
    //       console.log(res.message);
    //   }
    // };

    /**
		*游戏初始化配置
		*/
		class GameConfig{
			constructor(){}
			static init (){
				var reg=ClassUtils.regClass;
				reg("script.GameUI",GameUI);
				reg("laya.physics.BoxCollider",BoxCollider);
				reg("laya.physics.RigidBody",RigidBody);
				reg("script.GameControl",GameControl);
				reg("laya.physics.CircleCollider",CircleCollider);
				reg("script.Bullet",Bullet);
				reg("laya.display.Text",Text);
				reg("script.DropBox",DropBox);
			}
		}

		Laya.GameConfig=GameConfig;
		GameConfig.width=640;
		GameConfig.height=1136;
		GameConfig.scaleMode="fixedwidth";
		GameConfig.screenMode="none";
		GameConfig.alignV="top";
		GameConfig.alignH="left";
		GameConfig.startScene="test/TestScene.scene";
		GameConfig.sceneRoot="";
		GameConfig.debug=false;
		GameConfig.stat=false;
		GameConfig.physicsDebug=false;
		GameConfig.exportSceneToJson=true;
		GameConfig.__init$=function(){
			GameConfig.init();
		};
		
		class Main{
			constructor (){
				if (window["Laya3D"])window["Laya3D"].init(GameConfig.width,GameConfig.height);
				else Laya.init(GameConfig.width,GameConfig.height,Laya["WebGL"]);
				Laya["Physics"] && Laya["Physics"].enable();
				Laya["DebugPanel"] && Laya["DebugPanel"].enable();
				Laya.stage.scaleMode=GameConfig.scaleMode;
				Laya.stage.screenMode=GameConfig.screenMode;
				Laya.stage.alignV=GameConfig.alignV;
				Laya.stage.alignH=GameConfig.alignH;
				URL.exportSceneToJson=GameConfig.exportSceneToJson;
				if (GameConfig.debug || Utils.getQueryString("debug")=="true")Laya.enableDebugPanel();
				if (GameConfig.physicsDebug && Laya["PhysicsDebugDraw"])Laya["PhysicsDebugDraw"].enable();
				if (GameConfig.stat)Stat.show();
				Laya.alertGlobalError=true;
				ResourceVersion.enable("version.json",Handler.create(this,this.onVersionLoaded),ResourceVersion.FILENAME_VERSION);
			}
			onVersionLoaded(){
				AtlasInfoManager.enable("fileconfig.json",Handler.create(this,this.onConfigLoaded));
				}onConfigLoaded(){
				GameConfig.startScene && Scene.open(GameConfig.startScene);
			}
		}

		Laya.Main=Main;	
		window.script={};

		/**
		*子弹脚本，实现子弹飞行逻辑及对象池回收机制
		*/
		class Bullet extends Script{
			constructor(){
				super();
			}
			onEnable(){
				var rig=this.owner.getComponent(RigidBody);
				rig.setVelocity({x:0,y:-10});
				}onTriggerEnter(other,self,contact){
				this.owner.removeSelf();
				}onUpdate(){
				if ((this.owner).y <-10){
					this.owner.removeSelf();
				}
				}onDisable(){
				Pool.recover("bullet",this.owner);
			}
		}

		script.Bullet=Laya.Bullet=Bullet;	
		

		/**
		*掉落盒子脚本，实现盒子碰撞及回收流程
		*/
		class DropBox extends Script{
			constructor (){
				super();
				/**盒子等级 */
				this.level=1;
				/**等级文本对象引用 */
				//this._text=null;
				/**刚体对象引用 */
				//this._rig=null;
			}
			onEnable(){
				this._rig=this.owner.getComponent(RigidBody);
				this.level=Math.round(Math.random()*5)+1;
				this._text=this.owner.getChildByName("levelTxt");
				this._text.text=this.level+"";
				}onUpdate(){
				(this.owner).rotation++;
				}onTriggerEnter(other,self,contact){
				var owner=this.owner;
				if (other.label==="buttle"){
					if (this.level > 1){
						this.level--;
						this._text.changeText(this.level+"");
						owner.getComponent(RigidBody).setVelocity({x:0,y:-10});
						SoundManager.playSound("sound/hit.wav");
						}else {
						if (owner.parent){
							var effect=Pool.getItemByCreateFun("effect",this.createEffect,this);
							effect.pos(owner.x,owner.y);
							owner.parent.addChild(effect);
							effect.play(0,true);
							owner.removeSelf();
							SoundManager.playSound("sound/destroy.wav");
						}
					}
					GameUI.instance.addScore(1);
					}else if (other.label==="ground"){
					owner.removeSelf();
					GameUI.instance.stopGame();
				}
			}
			/**使用对象池创建爆炸动画 */
			createEffect(){
				var ani=new Animation();
				ani.loadAnimation("test/TestAni.ani");
				ani.on(Event.COMPLETE,null,recover);
				function recover (){
					ani.removeSelf();
					Pool.recover("effect",ani);
				}
				return ani;
				}onDisable(){
				Pool.recover("dropBox",this.owner);
			}
		}

		script.DropBox=Laya.DropBox=DropBox;	
		

		/**
		*游戏控制脚本。定义了几个dropBox，bullet，createBoxInterval等变量，能够在IDE显示及设置该变量
		*更多类型定义，请参考官方文档
		*/
		class GameControl extends Script{
			constructor (){
				super();
				/**@prop {name:dropBox,tips:"掉落容器预制体对象",type:Prefab}*/
				//this.dropBox=null;
				/**@prop {name:bullet,tips:"子弹预制体对象",type:Prefab}*/
				//this.bullet=null;
				/**@prop {name:createBoxInterval,tips:"间隔多少毫秒创建一个下跌的容器",type:int,default=1000}*/
				this.createBoxInterval=1000;
				/**开始时间*/
				this._time=0;
				/**是否已经开始游戏 */
				this._started=false;
				/**子弹和盒子所在的容器对象 */
				//this._gameBox=null;
			}
			onEnable(){
				this._time=Browser.now();
				this._gameBox=this.owner.getChildByName("gameBox");
				}onUpdate(){
				var now=Browser.now();
				if (now-this._time > this.createBoxInterval&&this._started){
					this._time=now;
					this.createBox();
				}
				}createBox(){
				var box=Pool.getItemByCreateFun("dropBox",this.dropBox.create,this.dropBox);
				box.pos(Math.random()*(Laya.stage.width-100),-100);
				this._gameBox.addChild(box);
				}onStageClick(e){
				e.stopPropagation();
				var flyer=Pool.getItemByCreateFun("bullet",this.bullet.create,this.bullet);
				flyer.pos(Laya.stage.mouseX,Laya.stage.mouseY);
				this._gameBox.addChild(flyer);
			}
			/**开始游戏，通过激活本脚本方式开始游戏*/
			startGame(){
				if (!this._started){
					this._started=true;
					this.enabled=true;
				}
			}
			/**结束游戏，通过非激活本脚本停止游戏 */
			stopGame(){
				this._started=false;
				this.enabled=false;
				this.createBoxInterval=1000;
				this._gameBox.removeChildren();
			}
		}

		script.GameControl=Laya.GameControl=GameControl;	
		window.ui={};ui.test={};

		class TestSceneUI extends Scene{
			constructor (){
				super();
				//this.scoreLbl=null;
				//this.tipLbll=null;
			}
			createChildren(){
				super.createChildren();
				this.loadScene("test/TestScene");
			}
		}

		ui.test.TestSceneUI=Laya.TestSceneUI=TestSceneUI;	
		

		/**
		*本示例采用非脚本的方式实现，而使用继承页面基类，实现页面逻辑。在IDE里面设置场景的Runtime属性即可和场景进行关联
		*相比脚本方式，继承式页面类，可以直接使用页面定义的属性（通过IDE内var属性定义），比如this.tipLbll，this.scoreLbl，具有代码提示效果
		*建议：如果是页面级的逻辑，需要频繁访问页面内多个元素，使用继承式写法，如果是独立小模块，功能单一，建议用脚本方
		*/
		class GameUI extends TestSceneUI{
			constructor (){
				super();
				/**当前游戏积分字段 */
				//this._score=NaN;
				/**游戏控制脚本引用，避免每次获取组件带来不必要的性能开销 */
				//this._control=null;
				script.GameUI.instance=this;
				MouseManager.multiTouchEnabled=false;
			}
			onEnable(){
				this._control=this.getComponent(GameControl);
				this.tipLbll.on(Event.CLICK,this,this.onTipClick);
				}onTipClick(e){
				this.tipLbll.visible=false;
				this._score=0;
				this.scoreLbl.text="";
				this._control.startGame();
			}
			/**增加分数 */
			addScore(value=1){
				this._score+=value;
				this.scoreLbl.changeText("分数："+this._score);
				if (this._control.createBoxInterval > 600 && this._score % 20==0)this._control.createBoxInterval-=20;
			}
			/**停止游戏 */
			stopGame(){
				this.tipLbll.visible=true;
				this.tipLbll.text="游戏结束了，点击屏幕重新开始";
				this._control.stopGame();
			}
		}

		script.GameUI=Laya.GameUI=GameUI;
		GameUI.instance=null;
		Laya.__init([GameConfig]);

		/**LayaGameStart**/
		new Main();

		return Laya;
	}(window));

}());
