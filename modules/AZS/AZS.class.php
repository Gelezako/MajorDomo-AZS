<?php
/**
* AZS 
* @package project
* @author Alex Sokolov <admin@gelezako.com>
* @copyright Alex Sokolov http://www.xa-xa.pp.ua (c)
* @version 0.1 (wizard, 11:02:06 [Feb 06, 2017])
*/
//
//
class AZS extends module {
/**
* AZS
*
* Module class constructor
*
* @access private
*/
  public function AZS() {
  $this->name="AZS";
  $this->title="Цена на топливо";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
 public function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
  public function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
 public function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/

 protected function GetXMLPetrolPriceByRegionID($reg,$type){
      if (!isset($reg)) return null;
      $url="https://privat24.privatbank.ua/p24/accountorder?oper=prp&avias=price&region=&type=&PUREXML=";
      $data = simplexml_load_file($url);
      if(!empty($data)){
	      foreach ($data->xpath('//price') as $price) {
		if($price['regionCode']==$reg and $price['type']==$type){
		if ($price['price']!="0.00") sg("Price.".$type,$price['price']);
		else sg("Price.".$type,"нет данных");
		sg("Price.region",$price['region']);
		break;
		}
    	      }
      }
}

   protected function SetAutoUpdate()
   {
      $code = '//START AZS module
                        include_once(DIR_MODULES . \'AZS/AZS.class.php\');
                        $app_azs = new AZS();
                        $app_asz->GetXMLPetrolPriceByRegionID(gg(\'Price.region\'),"A80");
                        $app_asz->GetXMLPetrolPriceByRegionID(gg(\'Price.region\'),"A95");
                        $app_asz->GetXMLPetrolPriceByRegionID(gg(\'Price.region\'),"A95E");
                        $app_asz->GetXMLPetrolPriceByRegionID(gg(\'Price.region\'),"DT");
                        $app_asz->GetXMLPetrolPriceByRegionID(gg(\'Price.region\'),"GAZ");
               // END AZS module';

      $res = SQLSelectOne("SELECT ID, CODE FROM methods WHERE OBJECT_ID = '0' AND TITLE LIKE 'onNewHour'");

      if (!in_array($code, $res))
      {
         $res["CODE"] = $res["CODE"].$code;
         SQLUpdate('methods', $res);
      }
   }


public function SaveData($region,$type) {
        $call=AZS::GetXMLPetrolPriceByRegionID($region,$type);
        if ($call!=null) $out["notification"] = "Данные успешно получены";
        else $out["notification"] = "Не удалось получить цены";
         
}

public function admin(&$out) {

global $A92,$A95,$A95E,$DT,$GAZ,$region,$notification;
if(isset($region)){
    $mas=[$A80,$A92,$A95,$A95E,$DT,$GAZ];
    foreach($mas as $i){
        if(!empty($i)) 
        $this->SaveData($region,$i);
    }
}

$this->get_settings($out,$region);
}


public function get_settings(&$out,$region)
{
    $out["region"] = $region;
	$out["A80"] = gg("Price.".$A80);
    $out["A92"] = gg("Price.".$A92);
    $out["A95"] = gg("Price.".$A95);
    $out["A95E"] = gg("Price.".$A95E);
    $out["DT"] = gg("Price.".$DT);
    $out["GAZ"] = gg("Price.".$GAZ);
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
 public function usual(&$out) {
 $this->admin($out);
}
/**
* Install
*
* Module installation routine
*
* @access private
*/
 public function install($data='') {
 $className = 'AZS'; //имя класса
 $objectName = array('Price');//имя обьектов
 $objDescription = array('Цена на топливо');
 $rec = SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe($className) . "'");
 
    if (!$rec['ID']) {
        $rec = array();
        $rec['TITLE'] = $className;
        $rec['DESCRIPTION'] = $objDescription;
        $rec['ID'] = SQLInsert('classes', $rec);
    }
    for ($i = 0; $i < count($objectName); $i++) {
        $obj_rec = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($objectName[$i]) . "'");
        if (!$obj_rec['ID']) {
            $obj_rec = array();
            $obj_rec['CLASS_ID'] = $rec['ID'];
            $obj_rec['TITLE'] = $objectName[$i];
            $obj_rec['DESCRIPTION'] = $objDescription[$i];
            $obj_rec['ID'] = SQLInsert('objects', $obj_rec);
        }
    }
	addClassProperty('Price', 'A80', 'include_once(DIR_MODULES."AZS/AZS.class.php");');
	addClassProperty('Price', 'A92', 'include_once(DIR_MODULES."AZS/AZS.class.php");');
	addClassProperty('Price', 'A95', 'include_once(DIR_MODULES."AZS/AZS.class.php");');
	addClassProperty('Price', 'DT', 'include_once(DIR_MODULES."AZS/AZS.class.php");');
	addClassProperty('Price', 'GAZ', 'include_once(DIR_MODULES."AZS/AZS.class.php");');
	addClassProperty('Price', 'region', 'include_once(DIR_MODULES."AZS/AZS.class.php");');
    $this->SetAutoUpdate();
	parent::install();
 }

 public function uninstall()
   {
      SQLExec("delete from pvalues where property_id in (select id FROM properties where object_id in (select id from objects where class_id = (select id from classes where title = 'AZS')))");
      SQLExec("delete from properties where object_id in (select id from objects where class_id = (select id from classes where title = 'AZS'))");
      SQLExec("delete from objects where class_id = (select id from classes where title = 'AZS')");
      SQLExec("delete from classes where title = 'AZS'");
      
      parent::uninstall();
   }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgRmViIDA2LCAyMDE3IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
