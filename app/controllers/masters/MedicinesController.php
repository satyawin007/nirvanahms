<?php namespace masters;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
class MedicinesController extends \Controller {

	/**
	 * add a new state.
	 *
	 * @return Response
	 */
	public function addMedicines()
	{
		if (\Request::isMethod('post'))
		{
			$values = Input::all();
			$field_names = array("name"=>"name","generic_name"=>"generic_name","manafacturer"=>"manafacturer_id");
			$fields = array();
			foreach ($field_names as $key=>$val){
				if(isset($values[$key])){
					$fields[$val] = $values[$key];
				}
			}
			$db_functions_ctrl = new DBFunctionsController();
			$table = "Medicines";
			$values = array();
			if($db_functions_ctrl->insert($table, $fields)){
				\Session::put("message","Operation completed Successfully");
				return \Redirect::to("medicines");
			}
			else{
				\Session::put("message","Operation Could not be completed, Try Again!");
				return \Redirect::to("medicines");
			}	
		}		
	}
	
	/**
	 * Edit a state.
	 *
	 * @return Response
	 */
	public function editMedicines()
	{
		$values = Input::all();
		if (\Request::isMethod('post'))
		{
			$field_names = array("name1"=>"name","generic_name1"=>"generic_name","manafacturer1"=>"manafacturer_id","status1"=>"status");
			$fields = array();
			foreach ($field_names as $key=>$val){
				if(isset($values[$key])){
					$fields[$val] = $values[$key];
				}
			}
			$data = array('id'=>$values['id1']);			
			$db_functions_ctrl = new DBFunctionsController();
			$table = "\Medicines";
			$values = array();
			if($db_functions_ctrl->update($table, $fields, $data)){
				\Session::put("message","Operation completed Successfully");
				return \Redirect::to("medicines");
			}
			else{
				\Session::put("message","Operation Could not be completed, Try Again!");
				return \Redirect::to("medicines");
			}
		}
	}
	
		
	
	/**
	 * manage all states.
	 *
	 * @return Response
	 */
	public function manageMedicines()
	{
		$values = Input::all();
		$values['bredcum'] = "DEPARTMENTS";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'addmedicines';
		$values['form_action'] = 'medicines';
		$values['action_val'] = '#';
		$theads = array('medicineId','medicine Name',"generic name","manafacturers", "description", "status", "Actions");
		$values["theads"] = $theads;
			
		$form_info = array();
		$form_info["name"] = "addmedicines";
		$form_info["action"] = "addmedicines";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "medicines";
		$form_info["bredcum"] = "add medicines";
		
		$manafacturers =  \Manufacturers::Where("status","=","ACTIVE")->get();
		$manafacturers_arr = array();
		foreach ($manafacturers as $manafacturer){
			$manafacturers_arr[$manafacturer['id']] = $manafacturer['name'];
		}
		$form_fields = array();
		$form_field = array("name"=>"name", "content"=>"doctor name", "readonly"=>"","required"=>"required","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"generic_name", "content"=>"generic name", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"manafacturer", "content"=>"department name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>$manafacturers_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"description", "content"=>"description", "readonly"=>"","required"=>"required","type"=>"textarea", "class"=>"form-control");
		$form_fields[] = $form_field;
				
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
		
		$form_info = array();
		$form_info["name"] = "edit";
		$form_info["action"] = "editmedicines";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "medicines";
		$form_info["bredcum"] = "edit medicines";
		
		$modals = array();
		$form_fields = array();
		$form_field = array("name"=>"name1", "content"=>"doctor name", "readonly"=>"","required"=>"required","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"generic_name1", "content"=>"generic name", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"manafacturer1", "content"=>"department name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>$manafacturers_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"description1", "content"=>"description", "readonly"=>"","required"=>"required","type"=>"textarea", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"id1", "value"=>"", "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"status1", "value"=>"", "content"=>"status", "readonly"=>"", "value"=>"", "required"=>"", "type"=>"select", "options"=>array("ACTIVE"=>"ACTIVE","INACTIVE"=>"INACTIVE"), "class"=>"form-control");
		$form_fields[] = $form_field;	
		
		$form_info["form_fields"] = $form_fields;
		$modals[] = $form_info;
		$values["modals"] = $modals;
		
		$values['provider'] = "medicines";

		return View::make('masters.layouts.lookupdatatable', array("values"=>$values));
	}	
}
