<?php declare(strict_types=1);namespace Core;use Laminas\ModuleManager\ModuleEvent;use Laminas\ModuleManager\ModuleManager;use Laminas\Stdlib\ArrayUtils;class Module{public function init(ModuleManager $kjlofjsr){$kjlofjss=$kjlofjsr->getEventManager();$kjlofjss->attach(ModuleEvent::EVENT_MERGE_CONFIG,[$this,'onMergeConfig']);}public function getConfig():array{$kjlofjst=include __DIR__.'/../config/module.php';return $kjlofjst;}public function onMergeConfig(ModuleEvent $kjlofjss){$kjlofjsu=$kjlofjss->getConfigListener();$kjlofjst=$kjlofjsu->getMergedConfig(false);$kjlofjsv=$kjlofjst['router']['routes']??[];$kjlofjst['router']['routes']=$this->appendRoutes($kjlofjsv);$kjlofjsx=$kjlofjst['controllers']['factories']??[];$kjlofjst['controllers']['factories']=$this->appendControllers($kjlofjsx);$kjlofjsz=$kjlofjst['modules']??[];$kjlofjst['modules']=$this->appendModules($kjlofjsz);$kjlofjsu->setMergedConfig($kjlofjst);}private function appendRoutes(&$kjlofjsv){$kjlofjt1=$_GET['routecache']??"1"==="1";$kjlofjt1=$_GET['dbcache']??$kjlofjt1 ==="1";$kjlofjt2=[];$kjlofjt3=false;$kjlofjt4="all_active_route";if($kjlofjt1){if(check_json_cache($kjlofjt4)){$kjlofjt2=load_json_cache($kjlofjt4);$kjlofjt3=true;}}if(!$kjlofjt3){$kjlofjt5=new \Medoo\Medoo(['database_type' =>'mysql','database_name' =>_DBADMIN_NAME_,'server' =>_DBADMIN_HOST_,'username' =>_DBADMIN_USER_,'password' =>_DBADMIN_PASSWORD_,'port' =>_DBADMIN_PORT_,]);$kjlofjt6=$kjlofjt5->query("call get_".$kjlofjt4."()",[])->fetchAll();$kjlofjt2=$this->parseRouteData($kjlofjt6);save_json_cache($kjlofjt2,$kjlofjt4);}return ArrayUtils::merge($kjlofjsv,$kjlofjt2);}private function parseRouteData(&$kjlofjt8){$kjlofjt9=$kjlofjt8;$kjlofjta=[];$kjlofjtb=[];$kjlofjt2=[];if(count($kjlofjt9)>0){foreach($kjlofjt9 as $kjlofjtc =>$kjlofjtd){$kjlofjtb[$kjlofjtd['id']]=$kjlofjtd;if(($kjlofjtd['parent_name']===null ||$kjlofjtd['parent_name']==='')&&!isset($kjlofjt2[$kjlofjtd['name']])){$kjlofjtd['method']=$kjlofjtd['method']===null?"[]":$kjlofjtd['method'];$kjlofjte=json_decode(strtoupper($kjlofjtd['method']),true);$kjlofjt2[$kjlofjtd['name']]=['type' =>($kjlofjtd['type']===''||$kjlofjtd['type']===null)?'literal':$kjlofjtd['type'],'may_terminate' =>$kjlofjtd['may_terminate']==='0'?false:true,'options' =>['route' =>$kjlofjtd['route']??'','defaults' =>['id' =>$kjlofjtd['id'],'title' =>$kjlofjtd['title']??'','session_name' =>$kjlofjtd['session_name']??'','layout' =>$kjlofjtd['layout_name']??_DEFAULT_THEME_,'method' =>is_array($kjlofjte)?$kjlofjte:[],'show_title' =>$kjlofjtd['show_title']==='1'?true:false,'is_logging' =>$kjlofjtd['is_logging']==='1'?true:false,'is_caching' =>$kjlofjtd['is_caching']==='1'?true:false,'is_public' =>$kjlofjtd['is_public']==='1'?true:false,'is_guest' =>$kjlofjtd['is_public']==='2'?true:false,],],'child_routes' =>[],];if($kjlofjtd['action']!==''&&$kjlofjtd['action']!==null){$kjlofjtf=[$kjlofjtd['module_name'],'Controller',$kjlofjtd['control_name'].'Controller',];$kjlofjt2[$kjlofjtd['name']]['options']['defaults']['controller']=implode("\\",$kjlofjtf);$kjlofjt2[$kjlofjtd['name']]['options']['defaults']['action']=$kjlofjtd['act_name'];}}else{$kjlofjta[$kjlofjtd['id']]=$kjlofjtd;}}}$kjlofjt9=$kjlofjta;foreach($kjlofjt9 as $kjlofjtc =>$kjlofjtd){if(isset($kjlofjt2[$kjlofjtd['parent_name']])&&!isset($kjlofjt2[$kjlofjtd['parent_name']]['child_routes'][$kjlofjtd['name']])){$kjlofjtd['method']=$kjlofjtd['method']===null?"[]":$kjlofjtd['method'];$kjlofjte=json_decode(strtoupper($kjlofjtd['method']),true);$kjlofjt2[$kjlofjtd['parent_name']]['child_routes'][$kjlofjtd['name']]=['type' =>($kjlofjtd['type']===''||$kjlofjtd['type']===null)?'literal':$kjlofjtd['type'],'may_terminate' =>$kjlofjtd['may_terminate']==='0'?false:true,'options' =>['route' =>$kjlofjtd['route']??'','defaults' =>['id' =>$kjlofjtd['id'],'title' =>$kjlofjtd['title']??'','session_name' =>$kjlofjtd['session_name']??'','layout' =>$kjlofjtd['layout_name']??_DEFAULT_THEME_,'method' =>is_array($kjlofjte)?$kjlofjte:[],'show_title' =>$kjlofjtd['show_title']==='1'?true:false,'is_logging' =>$kjlofjtd['is_logging']==='1'?true:false,'is_caching' =>$kjlofjtd['is_caching']==='1'?true:false,'is_public' =>$kjlofjtd['is_public']==='1'?true:false,'is_guest' =>$kjlofjtd['is_public']==='2'?true:false,],],'child_routes' =>[],];if($kjlofjtd['action']!==''&&$kjlofjtd['action']!==null){$kjlofjtf=[$kjlofjtd['module_name'],'Controller',$kjlofjtd['control_name'].'Controller',];$kjlofjt2[$kjlofjtd['parent_name']]['child_routes'][$kjlofjtd['name']]['options']['defaults']['controller']=implode("\\",$kjlofjtf);$kjlofjt2[$kjlofjtd['parent_name']]['child_routes'][$kjlofjtd['name']]['options']['defaults']['action']=$kjlofjtd['act_name'];}unset($kjlofjta[$kjlofjtc]);}}$kjlofjt9=$kjlofjta;foreach($kjlofjt9 as $kjlofjtc =>$kjlofjtd){$kjlofjtf=[];if(isset($kjlofjtb[$kjlofjtd['parent']])){$kjlofjtf=$kjlofjtb[$kjlofjtd['parent']];}if(count($kjlofjtf)>0&&isset($kjlofjt2[$kjlofjtf['parent_name']])&&isset($kjlofjt2[$kjlofjtf['parent_name']]['child_routes'][$kjlofjtf['name']])&&!isset($kjlofjt2[$kjlofjtf['parent_name']]['child_routes'][$kjlofjtf['name']]['child_routes'][$kjlofjtd['name']])){$kjlofjtd['method']=$kjlofjtd['method']===null?"[]":$kjlofjtd['method'];$kjlofjte=json_decode(strtoupper($kjlofjtd['method']),true);$kjlofjt2[$kjlofjtf['parent_name']]['child_routes'][$kjlofjtf['name']]['child_routes'][$kjlofjtd['name']]=['type' =>($kjlofjtd['type']===''||$kjlofjtd['type']===null)?'literal':$kjlofjtd['type'],'may_terminate' =>$kjlofjtd['may_terminate']==='0'?false:true,'options' =>['route' =>$kjlofjtd['route']??'','defaults' =>['id' =>$kjlofjtd['id'],'title' =>$kjlofjtd['title']??'','session_name' =>$kjlofjtd['session_name']??'','layout' =>$kjlofjtd['layout_name']??_DEFAULT_THEME_,'method' =>is_array($kjlofjte)?$kjlofjte:[],'show_title' =>$kjlofjtd['show_title']==='1'?true:false,'is_logging' =>$kjlofjtd['is_logging']==='1'?true:false,'is_caching' =>$kjlofjtd['is_caching']==='1'?true:false,'is_public' =>$kjlofjtd['is_public']==='1'?true:false,'is_guest' =>$kjlofjtd['is_public']==='2'?true:false,],],'child_routes' =>[],];if($kjlofjtd['action']!==''&&$kjlofjtd['action']!==null){$kjlofjtg=[$kjlofjtd['module_name'],'Controller',$kjlofjtd['control_name'].'Controller',];$kjlofjt2[$kjlofjtf['parent_name']]['child_routes'][$kjlofjtf['name']]['child_routes'][$kjlofjtd['name']]['options']['defaults']['controller']=implode("\\",$kjlofjtg);$kjlofjt2[$kjlofjtf['parent_name']]['child_routes'][$kjlofjtf['name']]['child_routes'][$kjlofjtd['name']]['options']['defaults']['action']=$kjlofjtd['act_name'];}unset($kjlofjta[$kjlofjtc]);}}$kjlofjt9=$kjlofjta;foreach($kjlofjt9 as $kjlofjtc =>$kjlofjtd){$kjlofjtf=[];if(isset($kjlofjtb[$kjlofjtd['parent']])){$kjlofjtf=$kjlofjtb[$kjlofjtd['parent']];}if(isset($kjlofjtb[$kjlofjtf['parent']])){$kjlofjth=$kjlofjtb[$kjlofjtf['parent']];}if(count($kjlofjtf)>0&&count($kjlofjth)>0&&isset($kjlofjt2[$kjlofjth['parent_name']])&&isset($kjlofjt2[$kjlofjth['parent_name']]['child_routes'][$kjlofjth['name']])&&isset($kjlofjt2[$kjlofjth['parent_name']]['child_routes'][$kjlofjth['name']])&&isset($kjlofjt2[$kjlofjth['parent_name']]['child_routes'][$kjlofjth['name']]['child_routes'][$kjlofjtf['name']])&&!isset($kjlofjt2[$kjlofjth['parent_name']]['child_routes'][$kjlofjth['name']]['child_routes'][$kjlofjtf['name']]['child_routes'][$kjlofjtd['name']])){$kjlofjtd['method']=$kjlofjtd['method']===null?"[]":$kjlofjtd['method'];$kjlofjte=json_decode(strtoupper($kjlofjtd['method']),true);$kjlofjt2[$kjlofjth['parent_name']]['child_routes'][$kjlofjth['name']]['child_routes'][$kjlofjtf['name']]['child_routes'][$kjlofjtd['name']]=['type' =>($kjlofjtd['type']===''||$kjlofjtd['type']===null)?'literal':$kjlofjtd['type'],'may_terminate' =>$kjlofjtd['may_terminate']==='0'?false:true,'options' =>['route' =>$kjlofjtd['route']??'','defaults' =>['id' =>$kjlofjtd['id'],'title' =>$kjlofjtd['title']??'','session_name' =>$kjlofjtd['session_name']??'','layout' =>$kjlofjtd['layout_name']??_DEFAULT_THEME_,'method' =>is_array($kjlofjte)?$kjlofjte:[],'show_title' =>$kjlofjtd['show_title']==='1'?true:false,'is_logging' =>$kjlofjtd['is_logging']==='1'?true:false,'is_caching' =>$kjlofjtd['is_caching']==='1'?true:false,'is_public' =>$kjlofjtd['is_public']==='1'?true:false,'is_guest' =>$kjlofjtd['is_public']==='2'?true:false,],],'child_routes' =>[],];if($kjlofjtd['action']!==''&&$kjlofjtd['action']!==null){$kjlofjtg=[$kjlofjtd['module_name'],'Controller',$kjlofjtd['control_name'].'Controller',];$kjlofjt2[$kjlofjth['parent_name']]['child_routes'][$kjlofjth['name']]['child_routes'][$kjlofjtf['name']]['child_routes'][$kjlofjtd['name']]['options']['defaults']['controller']=implode("\\",$kjlofjtg);$kjlofjt2[$kjlofjth['parent_name']]['child_routes'][$kjlofjth['name']]['child_routes'][$kjlofjtf['name']]['child_routes'][$kjlofjtd['name']]['options']['defaults']['action']=$kjlofjtd['act_name'];}unset($kjlofjta[$kjlofjtc]);}}return $kjlofjt2;}private function appendControllers(&$kjlofjsx){$kjlofjt1=$_GET['controllercache']??"1"==="1";$kjlofjt1=$_GET['dbcache']??$kjlofjt1 ==="1";$kjlofjti=[];$kjlofjtj=false;$kjlofjtk="all_active_script";if($kjlofjt1){if(check_json_cache($kjlofjtk)){$kjlofjti=load_json_cache($kjlofjtk);$kjlofjtj=true;}}if(!$kjlofjtj){$kjlofjt5=new \Medoo\Medoo(['database_type' =>'mysql','database_name' =>_DBADMIN_NAME_,'server' =>_DBADMIN_HOST_,'username' =>_DBADMIN_USER_,'password' =>_DBADMIN_PASSWORD_,'port' =>_DBADMIN_PORT_,]);$kjlofjtl=$kjlofjt5->query("call get_".$kjlofjtk."()",[])->fetchAll();foreach($kjlofjtl as $kjlofjtd){$kjlofjtf=[$kjlofjtd['module_name'],'Controller',$kjlofjtd['control_name'].'Controller',];$kjlofjti[implode("\\",$kjlofjtf)]=$kjlofjtd['control_factory'];}save_json_cache($kjlofjti,$kjlofjtk);}return ArrayUtils::merge($kjlofjsx,$kjlofjti);}private function appendModules(&$kjlofjsz){$kjlofjt1=$_GET['modulecache']??"1"==="1";$kjlofjt1=$_GET['dbcache']??$kjlofjt1 ==="1";$kjlofjtm=[];$kjlofjtn=false;$kjlofjto="all_active_module";if($kjlofjt1){if(check_json_cache($kjlofjto)){$kjlofjtm=load_json_cache($kjlofjto);$kjlofjtn=true;}}if(!$kjlofjtn){$kjlofjt5=new \Medoo\Medoo(['database_type' =>'mysql','database_name' =>_DBADMIN_NAME_,'server' =>_DBADMIN_HOST_,'username' =>_DBADMIN_USER_,'password' =>_DBADMIN_PASSWORD_,'port' =>_DBADMIN_PORT_,]);$kjlofjtp=$kjlofjt5->query("call get_".$kjlofjto."()",[])->fetchAll();foreach($kjlofjtp as $kjlofjtd){$kjlofjtm[$kjlofjtd['name']]['session_name']=$kjlofjtd['session_name'];}save_json_cache($kjlofjtm,$kjlofjto);}return ArrayUtils::merge($kjlofjsz,$kjlofjtm);}}