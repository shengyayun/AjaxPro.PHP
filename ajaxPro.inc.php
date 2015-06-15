<?php
class AjaxPro {
	public static $config = array (
			"Name" => "AjaxPro",
			"OpName" => "op"
	);
	
	public static function register($instance, $namespace = null) {
		$_get = array_change_key_case ( $_GET, CASE_LOWER );
		if($namespace!=null){
			$namespace = trim ( $namespace, "." );
			$namespace = preg_replace ( "#[\./]#", "\\", $namespace );
		}
		if (isset ( $_get [self::$config ["OpName"]] )) {
			return self::handle ( $instance, $_get [self::$config ["OpName"]], $namespace );
		} else {
			return self::build ( $instance, $namespace );
		}
	}
	
	public static function build($instance, $namespace) {
		$objName = self::$config ["Name"];
		$script = "if(typeof {$objName}=='undefined') var {$objName}={};";
		$script.="if(typeof {$objName}.Ajax=='undefined') {$objName}.Ajax=function(){var ajax=this;this.xml=null;this.async=true;this.callBack=null;this.GetXmlHttp=function(){try{this.xml=new ActiveXObject('Msxml2.XMLHTTP')}catch(e){try{this.xml=new ActiveXObject('Microsoft.XMLHTTP')}catch(e2){this.xml=false}}if(!this.xml&&typeof XMLHttpRequest!='undefined'){this.xml=new XMLHttpRequest()}};this.GetXmlHttp();this.updatePage=function(){if(this.readyState==4&&this.status==200){if(ajax.callBack!=null&&typeof ajax.callBack=='function'){ajax.callBack(this.responseText)}}};this.toQueryString=function(json){var query='';if(json!=null){for(var param in json){query+=param+'='+encodeURIComponent(json[param])+'&'}}return query};this.invoke=function(opName,params,callback){if(!this.xml){return}var query='';query+=this.toQueryString(params);query=query.substring(0,query.length-1);this.callBack=callback;this.xml.onreadystatechange=this.updatePage;this.xml.open('POST',location.pathname+'?".self::$config ["OpName"]."='+opName,this.async);this.xml.setRequestHeader('Content-type','application/x-www-form-urlencoded');this.xml.send(query)}};";
		
		$reflect = new \ReflectionClass ( $instance );
		// 命名空间
		if ($namespace == null)
			$namespace = $reflect->name;
		$nss = explode ( "\\", $namespace );
		array_unshift ( $nss, $objName );
		
		for($i = 1; $i < count ( $nss ); $i ++) {
			$itemName=($i == 0 ? "{$objName}." : "") . implode ( ".", array_slice ( $nss, 0, $i + 1 ) );
			$script .= "if(typeof {$itemName}=='undefined'){$itemName}={};";
		}

		$opBase = implode ( ".", $nss );
		$methods = $reflect->getMethods ( \ReflectionProperty::IS_PUBLIC );
		foreach ( $methods as $method ) {
			$name = $method->getName ();
			$params = $method->getParameters ();
			$arguments_formal = array ();
			$arguments_actual = array ();
			
			foreach ( $params as $param ) {
				$arguments_formal [] = $param->name;
				$arguments_actual [] = "'{$param->name}':{$param->name}";
			}
			$arguments_formal [] = "async";
			$arguments_formal [] = "callback";
			$opName = "{$opBase}.{$name}";
			
			$arg_f = implode ( ",", $arguments_formal );
			$arg_a = implode ( ",", $arguments_actual );
			
			$script .= "{$itemName}.{$name}=function({$arg_f}){new {$objName}.Ajax().invoke('{$opName}',{{$arg_a}},callback);};";
		}
		return $script = "<script type='text/javascript'>{$script}</script>\n";
	}
	
	
	public static function handle($instance, $opName, $namespace) {
		if ($namespace != null) {
			$prefix=self::$config ["Name"].".".$namespace.".";
			if(stripos($opName,$prefix)!==0) return;
			$funcName=str_replace($prefix,"" ,$opName);
			
			$reflect = new \ReflectionClass ( $instance );
			$methods = $reflect->getMethods ( \ReflectionProperty::IS_PUBLIC );
			foreach($methods as $method){
				if($method->name==$funcName){
					$arg = array ();
					foreach ( $_POST as $value ) {
						$arg [] = $value;
					}
					ob_clean();
					echo $method->invokeArgs(null,$arg);
					exit();
				}
			}		
		}
	}
}