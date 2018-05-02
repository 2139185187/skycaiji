<?php
/*
 |--------------------------------------------------------------------------
 | SkyCaiji (蓝天采集器)
 |--------------------------------------------------------------------------
 | Copyright (c) 2018 http://www.skycaiji.com All rights reserved.
 |--------------------------------------------------------------------------
 | 使用协议  http://www.skycaiji.com/licenses
 |--------------------------------------------------------------------------
 */

namespace Install\Event; use Think\Controller; use Admin\Model\ConfigModel; if(!defined('IN_SKYCAIJI')) { exit('NOT IN SKYCAIJI'); } class UpgradeDbEvent extends Controller{ public function success($message='',$jumpUrl='',$ajax=false){ parent::success($message,$jumpUrl,$ajax); exit(); } public function run(){ load_data_config(); $result=$this->run_upgrade(); if($result['success']){ $mconfig=new ConfigModel(); $mconfig->setVersion($this->get_skycaiji_version()); } return $result; } public function get_skycaiji_version(){ $newProgramConfig=file_get_contents(C('ROOTPATH').'/SkycaijiApp/Common/Common/function.php'); if(preg_match('/[\'\"]SKYCAIJI_VERSION[\'\"]\s*,\s*[\'\"](?P<v>[\d\.]+?)[\'\"]/i', $newProgramConfig,$programVersion)){ $programVersion=$programVersion['v']; }else{ $programVersion=''; } return $programVersion; } public function run_upgrade(){ $mconfig=new ConfigModel(); $dbVersion=$mconfig->where("`cname`='version'")->find(); if(!empty($dbVersion)){ $dbVersion=$mconfig->convertData($dbVersion); $dbVersion=$dbVersion['data']; } $fileVersion=$this->get_skycaiji_version(); if(empty($dbVersion)){ return array('success'=>false,'msg'=>'未获取到数据库中的版本号'); } if(empty($fileVersion)){ return array('success'=>false,'msg'=>'未获取到项目文件的版本号'); } if(version_compare($dbVersion,$fileVersion)>=0){ return array('success'=>true,'msg'=>'数据库已是最新版本，无需更新'); } $methods=get_class_methods($this); $upgradeDbMethods=array(); foreach ($methods as $method){ if(preg_match('/^upgrade_db_to(?P<ver>(\_\d+)+)$/',$method,$toVer)){ $toVer=str_replace('_', '.', trim($toVer['ver'],'_')); if(version_compare($toVer,$dbVersion)>=1){ if(version_compare($toVer,$fileVersion)<=0){ $upgradeDbMethods[$toVer]=$method; } } } } if(empty($upgradeDbMethods)){ return array('success'=>true,'msg'=>'暂无更新'); } ksort($upgradeDbMethods); foreach ($upgradeDbMethods as $newVer=>$upMethod){ try { $this->$upMethod(); $mconfig->setVersion($newVer); }catch (\Exception $ex){ return array('success'=>false,'msg'=>$ex->getMessage()); } } clear_dir(C('APPPATH').'/Runtime'); return array('success'=>true,'msg'=>'升级完毕'); } public function upgrade_db_to_1_0_2(){ rename(C('ROOTPATH').'/SkycaijiApp/Admin/View/CPattern', C('ROOTPATH').'/SkycaijiApp/Admin/View/Cpattern'); rename(C('ROOTPATH').'/SkycaijiApp/Admin/View/MyStore', C('ROOTPATH').'/SkycaijiApp/Admin/View/Mystore'); } } ?>