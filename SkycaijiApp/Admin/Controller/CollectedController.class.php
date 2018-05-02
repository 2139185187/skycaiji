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

namespace Admin\Controller; use Think\Controller; use Admin\Model\CollectedModel; if(!defined('IN_SKYCAIJI')) { exit('NOT IN SKYCAIJI'); } class CollectedController extends BaseController { public function listAction(){ $taskName=I('task_name'); $page=I('p',1,'intval'); $page=max(1,$page); $mcollected=new CollectedModel(); $mtask=D('Task'); $cond=array(); $search=array(); $null_task=false; if(!empty($taskName)){ $search['task_name']=$taskName; $searchTasks=$mtask->field('`id`,`name`')->where(array('name'=>array('like',"%{$taskName}%")))->select(array('index'=>'id,name')); if(!empty($searchTasks)){ $cond['task_id']=array('in',array_keys($searchTasks)); }else{ $null_task=true; } } $search['num']=I('num',50,'intval'); $search['url']=I('url'); if(!empty($search['url'])){ $cond['url']=array('like','%'.addslashes($search['url']).'%'); } $search['release']=I('release'); if(!empty($search['release'])){ $cond['release']=$search['release']; } $search['status']=I('status'); if(!empty($search['status'])){ if($search['status']==1){ $cond['target']=array('EXP',"!=''"); }elseif($search['status']==2){ $cond['error']=array('EXP',"!=''"); } } if(!$null_task){ $count=$mcollected->where($cond)->count(); $limit=$search['num']; if($count>0){ $dataList=$mcollected->where($cond)->order('id desc')->limit($limit)->page($page)->select(); if($count>$limit){ $pageCount=ceil($count/$limit); $cpage = new \Think\Page($count,$limit); if(!empty($search)){ $cpage->parameter=array_merge($cpage->parameter,$search); } $pagenav = bootstrap_pages($cpage->show()); $this->assign('pagenav',$pagenav); } $taskIds=array(); foreach ($dataList as $itemK=>$item){ $taskIds[$item['task_id']]=$item['task_id']; if(preg_match('/^\w+\:\/\//', $item['target'])){ $dataList[$itemK]['target']='<a href="'.$item['target'].'" target="_blank">'.$item['target'].'</a>'; } } if(!empty($taskIds)){ $taskList=D('Task')->where(array('id'=>array('in',$taskIds)))->select(array('index'=>'id,name')); } $this->assign('dataList',$dataList); $this->assign('taskList',$taskList); } $GLOBALS['content_header']=L('collected_list'); $GLOBALS['breadcrumb']=breadcrumb(array(array('url'=>U('Collected/list'),'title'=>L('collected_list')))); } $this->assign('search',$search); $this->display(); } public function opAction(){ $id=I('id',0,'intval'); $op=I('get.op'); if(empty($op)){ $op=I('post.op'); } $ops=array('item'=>array('delete'),'list'=>array('deleteall')); if(!in_array($op,$ops['item'])&&!in_array($op,$ops['list'])){ $this->error(L('invalid_op')); } $mcollected=D('Collected'); if(in_array($op,$ops['item'])){ $collectedData=$mcollected->getById($id); if(empty($collectedData)){ $this->error(L('empty_data')); } } if($op=='delete'){ $mcollected->where(array('id'=>$id))->delete(); $this->success(L('delete_success')); }elseif($op=='deleteall'){ $ids=I('ids'); if(is_array($ids)&&count($ids)>0){ $mcollected->where(array('id'=>array('in',$ids)))->delete(); } $this->success(L('op_success'),U('list')); } } }