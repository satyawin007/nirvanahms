<?php namespace salaries;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use settings\AppSettingsController;

class LeavesController extends \Controller {

	/**
	 * add a new state.
	 *
	 * @return Response
	 */
	public function addLeave()
	{
		if (\Request::isMethod('post'))
		{
			$values = Input::all();
			$dt = date("Y-m-d",strtotime($values["fromdate"]));
			$dStart = new \DateTime($dt);
			$dt = date("Y-m-d",strtotime($values["todate"]));
		   	$dEnd  = new \DateTime($dt);
		   	$dDiff = $dStart->diff($dEnd);
			$leaves =  $dDiff->days;
			if($values["frommngreve"]=="Afternoon" && $values["tomngreve"]=="Morning"){
				$leaves = $leaves-0.5;
			}
			if($values["frommngreve"]=="Morning" && $values["tomngreve"]=="Afternoon"){
				$leaves = $leaves+0.5;
			}
			$values["leaves"] = $leaves;
			$field_names = array("employeename"=>"empId","fromdate"=>"fromDate", "branch"=>"branchId", "todate"=>"toDate", "frommngreve"=>"fromMrngEve","tomngreve"=>"toMrngEve","substitute"=>"substituteId","leaves"=>"noOfLeaves","leavesTaken"=>"leavesTaken","remarks"=>"remarks");
			$fields = array();
			foreach ($field_names as $key=>$val){
				if(isset($values[$key]) && ($key=="fromdate" || $key=="todate")){
					$fields[$val] = date("Y-m-d",strtotime($values[$key]));
				}
				else if(isset($values[$key])){
					$fields[$val] = $values[$key];
				}				
			}
			$db_functions_ctrl = new DBFunctionsController();
			$table = "Leaves";
			if($db_functions_ctrl->insert($table, $fields)){
				\Session::put("message","Operation completed Successfully");
				return \Redirect::to("leaves");
			}
			else{
				\Session::put("message","Operation Could not be completed, Try Again!");
				return \Redirect::to("leaves");
			}	
		}		
	}
	
	/**
	 * Edit a state.
	 *
	 * @return Response
	 */
	public function editLeave()
	{
		$values = Input::all();
		if (\Request::isMethod('post'))
		{
			$values = Input::all();
			$dt = date("Y-m-d",strtotime($values["fromdate"]));
			$dStart = new \DateTime($dt);
			$dt = date("Y-m-d",strtotime($values["todate"]));
		   	$dEnd  = new \DateTime($dt);
		   	$dDiff = $dStart->diff($dEnd);
			$leaves =  $dDiff->days;
			if($values["frommngreve"]=="Afternoon" && $values["tomngreve"]=="Morning"){
				$leaves = $leaves-0.5;
			}
			if($values["frommngreve"]=="Morning" && $values["tomngreve"]=="Afternoon"){
				$leaves = $leaves+0.5;
			}
			$values["leaves"] = $leaves;
			$field_names = array("employeename"=>"empId","fromdate"=>"fromDate","todate"=>"toDate","frommngreve"=>"fromMrngEve","tomngreve"=>"toMrngEve","substitute"=>"substituteId","leaves"=>"noOfLeaves","leavesTaken"=>"leavesTaken","remarks"=>"remarks");
			$fields = array();
			foreach ($field_names as $key=>$val){
				if(isset($values[$key]) && ($key=="fromdate" || $key=="todate")){
					$fields[$val] = date("Y-m-d",strtotime($values[$key]));
				}
				else if(isset($values[$key])){
					$fields[$val] = $values[$key];
				}				
			}
			$db_functions_ctrl = new DBFunctionsController();
			$data = array('id'=>$values['id']);			
			$table = "\Leaves";
			if($db_functions_ctrl->update($table, $fields, $data)){
				\Session::put("message","Operation completed Successfully");
				return \Redirect::to("editleave?id=".$values['id']);
			}
			else{
				\Session::put("message","Operation Could not be completed, Try Again!");
				return \Redirect::to("editleave?id=".$values['id']);
			}
		}
		$form_info = array();
		$form_info["name"] = "editleave";
		$form_info["action"] = "editleave";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "leaves";
		$form_info["bredcum"] = "edit leave";
	
		$entity = \Leaves::where("id","=",$values['id'])->get();
		if(count($entity)){
			$entity = $entity[0];
			$emps = \Employee::whereRaw(" status='ACTIVE' and (roleId!=20 and roleId!=19) ")->get();
			$emp_arr = array();
			foreach ($emps as $emp){
				$emp_arr[$emp->id] = $emp->fullName." - ".$emp->empCode;
			}
			$form_fields = array();	
			$empid = "";
			if(isset($emp_arr[$entity->empId])){
				$empid = $emp_arr[$entity->empId];
			}
			$form_field = array("name"=>"employeename1", "id"=>"employeename1", "value"=>$empid, "content"=>"employee name", "readonly"=>"readonly", "required"=>"required","type"=>"text", "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"fromdate", "id"=>"fromdate", "value"=>date("d-m-Y",strtotime($entity->fromDate)), "content"=>"from Date", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control date-picker");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"frommngreve", "id"=>"frommngreve", "value"=>$entity->fromMrngEve, "content"=>"from Mor/Eve", "readonly"=>"",  "required"=>"required","type"=>"radio", "options"=>array("Morning"=>"Morning","Afternoon"=>"Afternoon"), "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"todate", "id"=>"todate", "value"=>date("d-m-Y",strtotime($entity->toDate)), "content"=>"to Date", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control date-picker");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"tomngreve", "id"=>"tomngreve", "value"=>$entity->toMrngEve, "content"=>"to Mor/Eve", "readonly"=>"",  "required"=>"required","type"=>"radio", "options"=>array("Morning"=>"Morning","Afternoon"=>"Afternoon"), "class"=>"form-control");
			$form_fields[] = $form_field;
// 			$form_field = array("name"=>"substitute", "id"=>"substitute", "value"=>$entity->substituteId, "content"=>"substitute employee", "readonly"=>"", "required"=>"","type"=>"select", "options"=>$emp_arr, "class"=>"form-control chosen-select");
// 			$form_fields[] = $form_field;
			$form_field = array("name"=>"remarks", "id"=>"remarks", "value"=>$entity->remarks, "content"=>"remarks", "readonly"=>"", "required"=>"","type"=>"textarea", "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"leavesTaken", "value"=>$entity->leavesTaken, "content"=>"LEAVES ALREADY AVAILED THIS MONTH", "readonly"=>"", "required"=>"required", "type"=>"text",  "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"employeename", "id"=>"employeename", "value"=>$entity->empId, "content"=>"", "readonly"=>"readonly", "required"=>"required", "type"=>"hidden", "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"id", "value"=>$values["id"], "content"=>"", "readonly"=>"", "required"=>"","type"=>"hidden", "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_info['form_payment_fields'] = array();
			$form_info["form_fields"] = $form_fields;
			return View::make("transactions.edit2colmodalform",array("form_info"=>$form_info));
		}
	}
	
		
	
	/**
	 * manage all states.
	 *
	 * @return Response
	 */
	public function manageLeaves()
	{
		$values = Input::all();
		$values['bredcum'] = "EMPLOYEE LEAVES";
		$values['home_url'] = '#';
		$values['add_url'] = 'addleave';
		$values['form_action'] = 'leaves';
		$values['action_val'] = '#';
		$theads = array('Emp Id','Emp Name', "branch", "From", "Mor/Eve", "To", "Mor/Eve", "Leaves", "Leaves Taken", "status", "remarks", "Actions");
		$values["theads"] = $theads;
			
		$actions = array();
		$action = array("url"=>"editleave?","css"=>"primary", "type"=>"", "text"=>"Edit");
		$actions[] = $action;
		$values["actions"] = $actions;
			
		$form_info = array();
		$form_info["name"] = "addleave";
		$form_info["action"] = "addleave";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "leaves";
		$form_info["bredcum"] = "add leave";
		
		$emps = AppSettingsController::getEmpBranches();
		$emp_arr = array();
		foreach ($emps as $emp){
			$emp_arr[$emp["id"]] = $emp["name"];
		}
		$empcode = \Auth::user()->empCode;
		$empname = \Auth::user()->fullName." (".$empcode.")";
		$form_fields = array();
		$form_field = array("name"=>"datetime", "content"=>"date & time", "readonly"=>"readonly", "required"=>"required","type"=>"text",  "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"branch", "content"=>"branch", "readonly"=>"", "required"=>"required", "type"=>"select", "options"=>$emp_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"employeename1", "value"=>$empname, "content"=>"employee name", "readonly"=>"readonly", "required"=>"required","type"=>"text",  "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"fromdate", "content"=>"from Date", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control date-picker");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"todate", "content"=>"to Date", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control date-picker");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"frommngreve", "content"=>"from Mor/Eve", "readonly"=>"",  "required"=>"required","type"=>"radio", "options"=>array("Morning"=>"Morning","Afternoon"=>"Afternoon"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"tomngreve", "content"=>"to Mor/Eve", "readonly"=>"",  "required"=>"required","type"=>"radio", "options"=>array("Morning"=>"Morning","Afternoon"=>"Afternoon"), "class"=>"form-control");
		$form_fields[] = $form_field;
		//$form_field = array("name"=>"substitute", "content"=>"substitute employee", "readonly"=>"", "required"=>"","type"=>"select", "options"=>$emp_arr, "class"=>"form-control chosen-select");
		//$form_fields[] = $form_field;
		$form_field = array("name"=>"remarks", "content"=>"remarks", "readonly"=>"", "required"=>"required", "type"=>"textarea", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"leavesTaken", "content"=>"LEAVES ALREADY AVAILED THIS MONTH", "readonly"=>"", "required"=>"required", "type"=>"text",  "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"employeename", "id"=>"employeename", "value"=>\Auth::user()->id, "content"=>"", "readonly"=>"readonly", "required"=>"", "type"=>"hidden", "class"=>"form-control");
		$form_fields[] = $form_field;
				
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
		$modals = array();
		$values["modals"] = $modals;
		
		$values['provider'] = "leaves";

		return View::make('salaries.lookupdatatable', array("values"=>$values));
	}	
	
	public function approveLeave(){
		$values = Input::all();
		$db_functions_ctrl = new DBFunctionsController();
		$data = array('id'=>$values['id']);
		$table = "\Leaves";
		$fields = array("status"=>"Approved");
		if($db_functions_ctrl->update($table, $fields, $data)){
			echo "success";
			return;
		}
		echo "fail";
	}
	
	public function rejectLeave(){
		$values = Input::all();
		$db_functions_ctrl = new DBFunctionsController();
		$data = array('id'=>$values['id']);
		$table = "\Leaves";
		$fields = array("status"=>"Rejected");
		if($db_functions_ctrl->update($table, $fields, $data)){
			echo "success";
			return;
		}
		echo "fail";
	}
	
	public function getEmployeesByOffice(){
		$values = Input::all();
		if(isset($values["date"])){
			$entities = \Employee::whereRaw(" roleId!=20 and roleId!=19 and FIND_IN_SET('".$values["officebranch"]."',employee.officeBranchIds)")->get();
			$options_str = "<option value=''>--select employee--</option>";
			foreach ($entities as $entity){
				if($entity->terminationDate =="" || $entity->terminationDate =="0000-00-00" || $entity->terminationDate =="1970-01-01"){
					$options_str = $options_str."<option value='".$entity->id."'>".$entity->fullName."(".$entity->empCode.")</option>";
				}
				else if(isset($values["date"])){
					$date1 = strtotime(date("Y-m-d",strtotime($entity->terminationDate)));
					$date2 = strtotime(date("Y-m-d",strtotime($values["date"])));
					if($date1<$date2){
						continue;
					}
					else{
						$options_str = $options_str."<option value='".$entity->id."'>".$entity->fullName."(".$entity->empCode.")</option>";
					}
				}
			}
			echo $options_str;
		}
		else{
			$entities = \Employee::whereRaw(" status='ACTIVE' and (roleId!=20 and roleId!=19) and FIND_IN_SET('".$values["branch"]."',employee.officeBranchIds)")->get();
			$options_str = "<option value=''>--select employee--</option>";
			foreach ($entities as $entity){
				$options_str = $options_str."<option value='".$entity->id."'>".$entity->fullName."(".$entity->empCode.")</option>";
			}
			echo $options_str;
		}
	}
	
	public function getEmployeesByDepot(){
		$values = Input::all();
		if(isset($values["date"])){
			if($values["depot"]=="0"){
				DB::statement(DB::raw("CALL contract_driver_helper_all_att_sal('".$values["clientname"]."');"));
			}
			else {
				DB::statement(DB::raw("CALL contract_driver_helper_att_sal('".$values["depot"]."', '".$values["clientname"]."');"));
			}
			$entities = DB::select( DB::raw("select * from temp_contract_drivers_helpers group by id"));
			$options_str = "<option value=''>--select employee--</option>";
			foreach ($entities as $entity){
				if($entity->terminationDate =="" || $entity->terminationDate =="0000-00-00" || $entity->terminationDate =="1970-01-01"){
					$options_str = $options_str."<option value='".$entity->id."'>".$entity->fullName."(".$entity->empCode.")</option>";
				}
				else if(isset($values["date"])){
					$date1 = strtotime(date("Y-m-d",strtotime($entity->terminationDate)));
					$date2 = strtotime(date("Y-m-d",strtotime($values["date"])));
					if($date1<$date2){
						continue;
					}
					else{
						$options_str = $options_str."<option value='".$entity->id."'>".$entity->fullName."(".$entity->empCode.")</option>";
					}
				}
			}
			echo $options_str;
			
		}
		else{ 
				if($values["depot"]=="0"){
					DB::statement(DB::raw("CALL contract_driver_helper_all('".$values["clientname"]."');"));
				}
				else {
					DB::statement(DB::raw("CALL contract_driver_helper('".$values["depot"]."', '".$values["clientname"]."');"));
				}			
				$entities = DB::select( DB::raw("select * from temp_contract_drivers_helpers group by id"));
				$options_str = "<option value=''>--select employee--</option>";
				foreach ($entities as $entity){
					$options_str = $options_str."<option value='".$entity->id."'>".$entity->fullName."(".$entity->empCode.")</option>";
				}
				echo $options_str;
		}
	}
	
	public function leaveDetails(){
		$values = Input::all();
		$empid = $values["eid"];
		$salaryMonth = $values["dt"];
		$noOfDays = date("t", strtotime($salaryMonth)) -1;
		$startDate = $salaryMonth;
		$endDate =  date('Y-m-d', strtotime($salaryMonth.'+ '.$noOfDays.' days'));
		$jsondata = array();
	
		$recs = DB::select( DB::raw("SELECT * from leaves where (fromDate BETWEEN '".$startDate."' and '".$endDate."' or toDate BETWEEN '".$startDate."' and '".$endDate."') and empId=".$empid." and deleted='No'"));
		$data = "";
		foreach ($recs as $rec){
			$data = $data."<tr>";
			$data = $data."<td>".date("d-m-Y",strtotime($rec->fromDate))."</td>";
			$data = $data."<td>".$rec->fromMrngEve."</td>";
			$data = $data."<td>".date("d-m-Y",strtotime($rec->toDate))."</td>";
			$data = $data."<td>".$rec->toMrngEve."</td>";
			$data = $data."<td>".$rec->noOfLeaves."</td>";
			$data = $data."<td>".$rec->remarks."</td>";
			$data = $data."<td>".$rec->status."</td>";
		}
		$jsondata["tbody"] = $data;
		echo json_encode($jsondata);
	}
}
