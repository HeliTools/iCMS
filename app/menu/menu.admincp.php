<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
*/

class menuAdmincp{
    public function __construct() {
    	// var_dump(menu::$menu_array['article']['children']);
     //    exit;
    }
    public function do_add(){
    	// $id	= $_GET['id'];
     //    if($id) {
     //        $rs		= iDB::row("SELECT * FROM `#iCMS@__menu` WHERE `id`='$id' LIMIT 1;",ARRAY_A);
     //        $rootid	= $rs['rootid'];
     //    }else{
     //    	$rootid	= $_GET['rootid'];
     //    }
     //    include admincp::view("menu.add");
    }
    public function do_addseparator(){
    	// $rootid	= $_GET['rootid'];
    	// $class	= $rootid?'divider':'divider-vertical';
    	// iDB::query("INSERT INTO `#iCMS@__menu` (`rootid`,`app`,`class`) VALUES($rootid,'separator','$class');");
    	// menu::cache();
    	// iUI::success('添加完成');
    }
    public function do_updateorder(){
  //   	foreach((array)$_POST['sortnum'] as $sortnum=>$id){
  //           iDB::query("UPDATE `#iCMS@__menu` SET `sortnum` = '".intval($sortnum)."' WHERE `id` ='".intval($id)."' LIMIT 1");
  //   	}
		// menu::cache();
    }
    public function do_iCMS(){
    	admincp::$APP_METHOD="domanage";
    	$_GET['tab'] OR $_GET['tab']="tree";
    	$this->do_manage();
    }
    public function do_manage($doType=null) {
        include admincp::view("menu.manage");
    }
    public function power_tree($id=0){
        $li   = '';
        foreach((array)menu::$root_array[$id] AS $root=>$M) {
            $li.= '<li>';
            $li.= $this->power_holder($M);
            if(menu::$child_array[$M['id']]){
                $li.= '<ul>';
                $li.= $this->power_tree($M['id']);
                $li.= '</ul>';
            }
            $li.= '</li>';
        }
        return $li;
    }
    public function power_holder($M) {
        $name ='<span class="add-on">'.$M['caption'].'</span>';
        if($M['app']=='separator'){
            $name ='<span class="add-on tip" title="分隔符权限,仅为UI美观">───分隔符───</span>';
        }
        return '<div class="input-prepend input-append li2">
        <span class="add-on"><input type="checkbox" name="power[]" value="'.$M['id'].'"></span>
        '.$name.'
        </div>';
    }

    public function do_ajaxtree(){
		$expanded = $_GET['expanded']?true:false;
	 	echo $this->tree($_GET["root"],$expanded);
    }

    public function tree($id=null,$expanded=false,$menu_array = null){
        $array      = array();
        $menu_array === null && $menu_array = menu::$menu_array;
        // $id && $menu_array = $menu_array[$id];
        foreach($menu_array AS $key=>$M) {
            $a = array('id'=>$M['id']?$M['id']:md5($M['href']),'data'=>$M);
            unset($a['data']['children']);
            if($M['children']){
            	if($expanded){
                    $a['hasChildren'] = false;
                    $a['expanded']    = true;
                    $a['children']    = $this->tree($key,$expanded,$M['children']);
            	}else{
                    $a['hasChildren'] = true;
            	}
            }
            $a && $array[] = $a;
        }
        if($expanded && $id){
            return $array;
        }

        return $array?json_encode($array):'[]';
    }

    public function do_copy() {
        $id = $_GET['id'];
        $field = '`rootid`, `sortnum`, `app`, `name`, `title`, `href`, `icon`, `class`, `a_class`, `target`, `caret`, `data-toggle`, `data-meta`, `data-target`';
        iDB::query("insert into `#iCMS@__menu` ({$field}) select {$field} from `#iCMS@__menu` where id = '$id'");
        $nid = iDB::$insert_id;
        iUI::success('复制完成,编辑此菜单', 'url:' . APP_URI . '&do=add&id=' . $nid);
    }
    public function do_save(){
        $id          = $_POST['id'];
        $rootid      = $_POST['rootid'];
        $app         = $_POST['app'];
        $name        = $_POST['name'];
        $title       = $_POST['title'];
        $href        = $_POST['href'];
        $a_class     = $_POST['a_class'];
        $icon        = $_POST['icon'];
        $target      = $_POST['target'];
        $data_toggle = $_POST['data-toggle'];
        $sortnum    = $_POST['sortnum'];
        $class       = '';
        $caret       = '';
        $data_meta   = $_POST['data-meta'];
        $data_target = '';

    	if($data_toggle=="dropdown"){
    		$class		= 'dropdown';
    		$a_class	= 'dropdown-toggle';
    		$caret		= '<b class="caret"></b>';
    	}else if($data_toggle=="modal"){
    		$data_meta	OR	$data_meta	= '{"width":"800px","height":"600px"}';
    		$data_target = '#iCMS-MODAL';
    	}
        $fields = array('rootid', 'sortnum', 'app', 'name', 'title', 'href', 'icon', 'class', 'a_class', 'target', 'caret', 'data-toggle', 'data-meta', 'data-target');
        $data   = compact ($fields);
        $data['data-toggle'] = $data_toggle;
        $data['data-meta']   = $data_meta;
        $data['data-target'] = $data_target;

		if($id){
            iDB::update('menu', $data, array('id'=>$id));
    		$msg = "编辑完成!";
    	}else{
            iDB::insert('menu',$data);
			$msg = "添加完成!";
    	}
		menu::cache();
		iUI::success($msg,'url:' . APP_URI . '&do=manage');
    }
    public function do_del(){
        $id		= (int)$_GET['id'];
        if(empty(menu::$root_array[$id])) {
            iDB::query("DELETE FROM `#iCMS@__menu` WHERE `id` = '$id'");
            menu::cache();
            $msg	= '删除成功!';
        }else {
        	$msg	= '请先删除本菜单下的子菜单!';
        }
		iUI::dialog($msg,'js:parent.$("#'.$id.'").remove();');
    }
    public function select($currentid="0",$id="0",$level = 1) {
        foreach((array)menu::$root_array[$id] AS $root=>$M) {
			$t=$level=='1'?"":"├ ";
			$selected=($currentid==$M['id'])?"selected":"";
			if($M['app']=='separator'){
				$M['caption']	= "─────────────";
				$M['id']	= "-1";
			}
			$text	= str_repeat("│　", $level-1).$t.$M['caption'];
			$option.="<option value='{$M['id']}' $selected>{$text}</option>";
			menu::$child_array[$M['id']] && $option.=$this->select($currentid,$M['id'],$level+1);
        }
        return $option;
    }
}
