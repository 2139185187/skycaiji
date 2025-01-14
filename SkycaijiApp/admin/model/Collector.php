<?php
/*
 |--------------------------------------------------------------------------
 | SkyCaiji (蓝天采集器)
 |--------------------------------------------------------------------------
 | Copyright (c) 2018 https://www.skycaiji.com All rights reserved.
 |--------------------------------------------------------------------------
 | 使用协议  https://www.skycaiji.com/licenses
 |--------------------------------------------------------------------------
 */

namespace skycaiji\admin\model;

class Collector extends \skycaiji\common\model\BaseModel{
	
	public function add_new($data){
	    $data['addtime']=time();
	    $data['uptime']=time();
		$this->isUpdate(false)->allowField(true)->save($data);
		return $this->id;
	}
	
	public function edit_by_id($id,$data){
		unset($data['addtime']);
		$data['uptime']=time();
		
		$this->strict(false)->where(array('id'=>$id))->update($data);
	}
	/*遵守robots协议*/
	public function abide_by_robots($url){
		static $robotsList=array();
		$domain=null;
		if(preg_match('/^(\w+\:\/\/[^\/\\\]+)(.*)$/i',$url,$domain)){
			$url='/'.ltrim($domain[2],'\/\\');
			$domain=rtrim($domain[1],'\/\\');
		}
		if(empty($domain)){
			
			return true;
		}
		
		$robots=array();
		if(isset($robotsList[$domain])){
			$robots=$robotsList[$domain];
		}else{
			$robotsTxt=get_html($domain.'/robots.txt');
			
			if(!empty($robotsTxt)){
				
				$robotsTxt=preg_replace('/\#[^\r\n]*$/m', '', $robotsTxt);
				
				$rule=null;
				if(preg_match('/\bUser-agent\s*:\s*skycaiji\s+(?P<rule>[\s\S]+?)(?=((\bUser-agent\s*\:)|\s*$))/i',$robotsTxt,$rule)){
					
					$rule=$rule['rule'];
				}elseif(preg_match('/\bUser-agent\s*:\s*\*\s+(?P<rule>[\s\S]+?)(?=((\bUser-agent\s*\:)|\s*$))/i',$robotsTxt,$rule)){
					
					$rule=$rule['rule'];
				}else{
					$rule=null;
				}
				if(!empty($rule)){
					
					
					static $replace=array('\\','/','.','*','?','~','!','@','#','%','&','(',')','[',']','{','}','+','=','|',':',',');
					static $replaceTo=array('\\\\','\/','\.','.*','\?','\~','\!','\@','\#','\%','\&','\(','\)','\[','\]','\{','\}','\+','\=','\|','\:','\,');
					
					$allow=array();
					$disallow=array();
					
					if(preg_match_all('/\bAllow\s*:([^\r\n]+)/i',$rule,$allow)){
						$allow=array_unique($allow[1]);
					}else{
						$allow=array();
					}
					if(preg_match_all('/\bDisallow\s*:([^\r\n]+)/i',$rule,$disallow)){
						$disallow=array_unique($disallow[1]);
					}else{
						$disallow=array();
					}
					
					$robots=array(
						'allow'=>$allow,
						'disallow'=>$disallow
					);
					
					foreach ($robots as $k=>$v){
						foreach ($v as $vk=>$vv){
							$vv=trim($vv);
							if(empty($vv)||$vv=='/'){
								
								unset($v[$vk]);
							}else{
								$vv=str_replace($replace, $replaceTo, $vv);
								if(strpos($vv,'\/')===0){
									
									$vv='^'.$vv;
								}
								$v[$vk]=$vv;
							}
						}
						$robots[$k]=$v;
					}
				}
			}
			$robotsList[$domain]=$robots;
		}
		if(empty($robots)){
			
			return true;
		}
		if(!empty($robots['allow'])){
			foreach ($robots['allow'] as $v){
				if(preg_match('/'.$v.'/', $url)){
					
					return true;
					break;
				}
			}
		}

		if(!empty($robots['disallow'])){
			foreach ($robots['disallow'] as $v){
				if(preg_match('/'.$v.'/', $url)){
					
					return false;
					break;
				}
			}
		}
		return true;
	}
	
	public function compatible_config($config){
	    if(!is_array($config)){
	        $config=array();
	    }
	    
	    if(!isset($config['area'])){
	        
	        if(!empty($config['area_start'])||!empty($config['area_end'])) {
	            
	            $config['area']=$config['area_start'] . (!empty($config['area_end']) ? '(?<nr>[\s\S]+?)' : '(?<nr>[\s\S]+)') . $config['area_end'];
	        }
	    }
	    
	    if(!isset($config['url_web'])){
	        
	        if(!empty($config['url_post'])&&isset($config['url_posts'])){
	            
	            \util\Funcs::filter_key_val_list($config['url_posts']['names'], $config['url_posts']['vals']);
	            
	            $config['url_web']=array('open'=>1,'form_method'=>'post','form_names'=>$config['url_posts']['names'],'form_vals'=>$config['url_posts']['vals']);
	            
	            if(is_array($config['level_urls'])){
	                foreach ($config['level_urls'] as $k=>$v){
	                    $v['url_web']=$config['url_web'];
	                    $config['level_urls'][$k]=$v;
	                }
	            }
	        }
	    }
	    return $config;
	}
	
	public static function echo_msg_end_js(){
	    return '<script type="text/javascript" data-echo-msg-is-end="1">window.parent.window.collectorEchoMsg("end");</script>';
	}
	
	public static function echo_msg_log_filename($logid){
	    $logid=md5($logid);
	    $filename=RUNTIME_PATH.'echo_msg/'.substr($logid,0,2).'/'.substr($logid,2);
	    return $filename;
	}
}

?>