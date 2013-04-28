<?php
class Template{
	
	private $array_config = array(
				'sufficx' => '.m',	//模版文件的后缀
				'templete_dir' => 'templates', //设置模版所在文件
				'cache_htm' => false,	//是否需要编译成静态的html文件
				'complie_dir' => 'complie', 	//设置编译后存放的目录
				'suffix_cache' => '.htm', //设置编译文件的后缀
				'cache_time' => 2000,   //多长时间自动更新
				'php_true' => true,  //是否支持原生php代码
				'cache_control' => 'control.dat',
				'debug' => false
			);
	
	public $file;  //文件名
	
	static private $instance = null;
	
	private $value = array();
	
	public $debug = array();
	
	private $compileTool;
	
	private $controlData = array();
	
	public function __construct($array_config = array()){
		$this->array_config =  $array_config + $this->array_config;
		$this->debug['begin'] = microtime(true);
		$this->getPath();
		if(!is_dir($this->array_config['templete_dir'])){
			exit("template dir isn't founs");
		}
		if(!is_dir($this->array_config['complie_dir'])){
			mkdir($this->array_config['complie_dir'],'0770',true);
		}
		
		include 'complie.php';
		//$this->complieTool = new ComplieClass;
	}
	
	/**
	 * 取得模版实力
	 */
	public function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new Template();
		}
		return self::$instance;
	}
	
	/**
	 * 单步设置引擎
	 * @param unknown_type $key
	 * @param unknown_type $value
	 */
	public function setConfig($key,$value = null){
		if(is_array($key)){
			$this->array_config = $key + $this->array_config;
		}else{
			$this->array_config[$key] = $value;
		}
	}
	
	/**
	 * 获取配置信息，调试时使用
	 * @param unknown_type $key
	 */
	public function getConfig($key = null){
		if($key){
			return $this->array_config[$key]; 
		}else{
			return $this->array_config;
		}
	}
	
	/**
	 * 注入单个变量
	 */
	public function assign($key,$value){
		$this->value[$key] = $value;
	}
	
	/**
	 * 注入数组变量
	 */
	
	public function assignArray($array){
		if(is_array($array)){
			foreach($array as $key => $val){
				$this->value[$key] = $val;
			}
		}
	}
	
	/**
	 * 显示模版 
	 */
	public function show($file){
		$this->file = $file;
		if(!is_file($this->path())){
			exit('模版不存在');
		}
		
		$compile_file = $this->array_config['complie_dir'].DS.md5($file).'.php';
		$cache_file = $this->array_config['complie_dir'].DS.md5($file).'.htm';
		
		if($this->reCache($file) === false){
			$this->debug['cached'] = 'false';
			$this->compileTool = new ComplieClass($this->path(),$compile_file,$this->array_config);

			if($this->needCache()){
				ob_start();
			}
			extract($this->value,EXTR_OVERWRITE);
			if(!is_file($compile_file) || filemtime($compile_file) < filemtime($this->path())){

				$this->compileTool->c_var2 = $this->value;
				$this->compileTool->compile();
				include $compile_file;
			}else{
				include $compile_file;
			}
			 //判断是否开启了缓存
			if($this->needCache()){
				$message = ob_get_contents();
				file_put_contents($cache_file,$message);
			}
			
			
		}else{
			readfile($cache_file);
			$this->debug['cached'] = 'true';
		}
		
		$this->debug['spend'] = microtime(true) - $this->debug['begin'];
		$this->debug['count'] = count($this->value);
		$this->debug_info();
		
	}
	
	public function debug_info(){
		if($this->array_config['debug'] == true){
			echo PHP_EOL,'-------debug info--------------',PHP_EOL;
			echo '程序运行日期:',date('Y-m-d h:i:s'),PHP_EOL;
			echo '模版解析耗时:',$this->debug['spend'],'秒',PHP_EOL;
			echo '模版包含标签数:',$this->debug['count'],PHP_EOL;
			echo '是否使用静态缓存:',$this->debug['cached'],PHP_EOL;
			echo '模版引擎实例参数:',var_dump($this->getConfig());
		}
	}
	
	/**
	 * 判断是否开启了缓存
	 */
	public function needCache(){
		return $this->array_config['cache_htm'];	
	}
	
	public function path(){
			
		return $this->array_config['templete_dir'].DS.$this->file.$this->array_config['sufficx'];
	/* 	if(file_exists($path)){
			return true;
		}else{
			return false;
		} */
	}
	
	
	public function reCache($file){
		$flag = false;
		$cacheFile = $this->array_config['complie_dir'].DS.md5($flag).'.htm';
		if($this->array_config['cache_htm'] == true){
			$timeFlag = (time()-@filemtime($cacheFile)) < $this->array_config['cache_time'] ? true : false;
			if(is_file($cacheFile) && filesize($cacheFile) > 1 && $timeFlag){
				$flag = true;
			}else{
				$flag = false;
			}
		}
		return $flag;
	}
	
	/**
	 * 清理缓存的html文件
	 */
	public function clean($path = null){
		if($path == null){
			$path = $this->array_config['complie_dir'];
			$path = glob($path.'*'.$this->array_config['suffix_cache']);
		}else{
			$path = $this->array_config['complie_dir'].md5($path).'.htm';
		}
		foreach((array)$path as $val){
			unlink($val);
		}
	}
	
	public function getPath(){
		$this->array_config['templete_dir'] = ROOT_PATH.$this->array_config['templete_dir'];
		$this->array_config['complie_dir'] = ROOT_PATH.$this->array_config['complie_dir'];
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
}