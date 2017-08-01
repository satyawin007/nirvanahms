<?php namespace registrations;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
class OutpatientController extends \Controller {

	/**
	 * add a new city.
	 *
	 * @return Response
	 */
	public function addOutpatients()
	{
		if (\Request::isMethod('post'))
		{
			$values = Input::all();
			$field_names = array("cityname"=>"cityId","officebranchcode"=>"code", "iswarehouse"=>"isWareHouse", "officebranchname"=>"name","statename"=>"stateId");
			$fields = array();
			foreach ($field_names as $key=>$val){
				if(isset($values[$key])){
					$fields[$val] = $values[$key];
				}
			}
			$field_names1 = array(
					"advanceamount"=>"advanceAmount",  "monthlyrent"=>"monthlyRent", "ownername"=>"ownerName", "contactno"=>"ownerContactNo", "occupationdate"=>"occupiedDate", "agreementexpdate"=>"expDate",
					"paymenttype"=>"paymentType", "bankaccount"=>"bankAccount", "paymentexpecteday"=>"paymentExpectedDay", "currentbillpaidbyowner"=>"currentBillPaidByOwner", "muncipaltaxpaidbyowner"=>"muncipalTaxPaidByOwner"
					);
			$fields1 = array();
			foreach ($field_names1 as $key=>$val){
				if(isset($values[$key])){
					if($val == "occupiedDate" || $val == "expDate"){
						$fields1[$val] = date("Y-m-d",strtotime($values[$key]));
					}
					else {
						$fields1[$val] = $values[$key];
					}
				}
			}
			
			$db_functions_ctrl = new DBFunctionsController();
			$table1 = "\RentDetails";
			
			$entity = new \OfficeBranch();
			foreach($fields as $key=>$value){
				$entity[$key] = $value;
			}
			$entity->save();
				
			$branchid = $entity->id;
			$fields1["officeBranchId"] = $branchid;			
			
			if($db_functions_ctrl->insert($table1, $fields1)){
				\Session::put("message","Operation completed Successfully");
				return \Redirect::to("addofficebranch");
			}
			else{
				\Session::put("message","Operation Could not be completed, Try Again!");
				return \Redirect::to("addofficebranch");
			}
		}
		
		$form_info = array();
		$form_info["name"] = "addofficebranch";
		$form_info["action"] = "addofficebranch";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "officebranches";
		$form_info["bredcum"] = "add office branch";
		
		$form_fields = array();
		
		$states =  \State::Where("status","=","ACTIVE")->get();
		$state_arr = array();
		foreach ($states as $state){
			$state_arr[$state['id']] = $state->name; 	
		}
		$parentId = -1;
		$parent = \LookupTypeValues::where("name","=","PAYMENT TYPE")->get();
		if(count($parent)>0){
			$parent = $parent[0];
			$parentId = $parent->id;
		}
		$paymenttypes =  \LookupTypeValues::where("parentId","=",$parentId)->get();
		$pmttype_arr = array();
		foreach ($paymenttypes  as $paymenttype){
			$pmttype_arr[$paymenttype['name']] = $paymenttype->name;
		}
		
		$bank_arr = array();
		
// 		$cities =  \State::Where("status","=","ACTIVE")->get();
// 		$cities_arr = array();
// 		foreach ($cities as $city){
// 			$cities_arr[$city['id']] = $city->name;
// 		}
		
		$tabs = array();
		$form_fields = array();
		$form_field = array("name"=>"newregistration", "content"=>"Registration", "readonly"=>"",  "required"=>"required", "type"=>"radio", "options"=>array("new_registration"=>"new","existing_registration"=>"already registered"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"previousvisitdoctor", "content"=>"previous visit doctor", "readonly"=>"","required"=>"","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"mr_no", "content"=>"MR No", "readonly"=>"",  "required"=>"", "readonly"=>"readonly",  "type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"medicolegal", "content"=>"Medico Legal Case", "readonly"=>"",  "required"=>"required", "type"=>"checkbox", "options"=>array("yes"=>"&nbsp;"),  "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"closepreviousvisit", "content"=>"Close previous Active Visit", "readonly"=>"",  "required"=>"required", "type"=>"checkbox", "options"=>array("yes"=>"&nbsp;"),  "class"=>"form-control");
		$form_fields[] = $form_field;
		$tab = array();
		$tab['form_fields'] = $form_fields;
		$tab['href'] = "tabone";
		$tab['heading'] = strtoupper("Patient Details");
		$tabs[] = $tab;
		
		$form_fields = array();
		$form_field = array("name"=>"fullname", "content"=>"Full name", "readonly"=>"",  "required"=>"required", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"phone", "content"=>"Mobile No", "readonly"=>"",  "required"=>"required", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"age", "content"=>"Age", "readonly"=>"",  "required"=>"required", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"gender",  "content"=>"gender", "readonly"=>"",  "required"=>"required", "type"=>"select", "options"=>array("MALE"=>"MALE","FEMALE"=>"FEMALE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"Next_of_kin_name", "content"=>"Next of kin name", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"addtional_phone", "content"=>"Additional phone", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"Relation", "content"=>"Relation", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"relation_phone", "content"=>"relation phone No", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"patient_category", "content"=>"Patient category", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>$bank_arr,  "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"casefile", "content"=>"Case file", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$tab = array();
		$tab['form_fields'] = $form_fields;
		$tab['href'] = "tabtwo";
		$tab['heading'] = strtoupper("Basic Information");
		$tabs[] = $tab;
		
		$form_fields = array();
		$form_field = array("name"=>"address", "content"=>"ADDRESS", "readonly"=>"",  "required"=>"", "type"=>"textarea","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"area", "content"=>"Area", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"state",  "content"=>"State", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"country",  "content"=>"country", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"city",  "content"=>"City", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"marital_status",  "content"=>"marital status", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"marketing_source",  "content"=>"Marketing Source", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"hospital_visit_type",  "content"=>"Marketing Source", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"bloodgroup",  "content"=>"Blood Group", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"religion",  "content"=>"religion", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"occupation",  "content"=>"occupation", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"patient_origin", "content"=>"patient origin", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"custom_field2", "content"=>"custom field2", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"father_name", "content"=>"father name", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"patient_category_text", "content"=>"patient category", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"patient_Sourcing_category", "content"=>"patient sourcing category", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"uhid", "content"=>"uhid", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"test",  "content"=>"test", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"remarks", "content"=>"remarks", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"email", "content"=>"email id", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"pmore", "content"=>"pmore", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$tab = array();
		$tab['form_fields'] = $form_fields;
		$tab['href'] = "tabthree";
		$tab['heading'] = strtoupper("Addtional Patient Information");
		$tabs[] = $tab;
		
		$form_fields = array();
		$form_field = array("name"=>"sponsors1", "content"=>"primary sponsor", "readonly"=>"",  "required"=>"required", "type"=>"checkbox", "options"=>array("primary_sponsor"=>"primary sponsor"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"sponsor2", "content"=>"secondary sponsor", "readonly"=>"",  "required"=>"required", "type"=>"checkbox", "options"=>array("secondary_sponsor"=>"secondary sponsor"), "class"=>"form-control");
		$form_fields[] = $form_field;
		
		$tab = array();
		$tab['form_fields'] = $form_fields;
		$tab['href'] = "tabfour";
		$tab['heading'] = strtoupper("sponsor information");
		$tabs[] = $tab;
		
		$form_fields = array();
		$form_field = array("name"=>"bill_type", "content"=>"bill type", "readonly"=>"",  "required"=>"required", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"rate_plan", "content"=>"rate plan", "readonly"=>"",  "required"=>"required",  "type"=>"select", "options"=>array("AAROGYARASKHA"=>"AAROGYARASKHA","ESI"=>"ESI","GENERAL"=>"GENERAL","NTRVS"=>"NTRVS","TEST"=>"TEST","VIP"=>"VIP","E.P.D.C.L"=>"E.P.D.C.L","ONGC"=>"ONGC"), "class"=>"form-control");
		$form_fields[] = $form_field;
		
		$tab = array();
		$tab['form_fields'] = $form_fields;
		$tab['href'] = "tabfive";
		$tab['heading'] = strtoupper("payment information");
		$tabs[] = $tab;
		
		$form_fields = array();
		$form_field = array("name"=>"department", "content"=>"department", "readonly"=>"",  "required"=>"required", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"consulting_doctor", "content"=>"consulting doctor", "readonly"=>"",  "required"=>"required", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"visit_type", "content"=>"visit type", "readonly"=>"",  "required"=>"required", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"consultation_type", "content"=>"consultation type", "readonly"=>"",  "required"=>"required", "type"=>"select", "options"=>array("MARRIED"=>"MARRIED","SINGLE"=>"SINGLE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"consultaion_time", "content"=>"consultation time", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control date");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"consultation_remarks", "content"=>"consultation remarks", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"consultaion_fee", "content"=>"consultation fee", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"referredby", "content"=>"referred by", "readonly"=>"",  "required"=>"required", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"complaint", "content"=>"complaint", "readonly"=>"",  "required"=>"required", "type"=>"textarea","class"=>"form-control");
		$form_fields[] = $form_field;
		
		$tab = array();
		$tab['form_fields'] = $form_fields;
		$tab['href'] = "tabsix";
		$tab['heading'] = strtoupper("admission information");
		$tabs[] = $tab;
		$form_info["tabs"] = $tabs;
		return View::make("registrations.addtabbedform",array("form_info"=>$form_info));		
	}
	
	/**
	 * edit a Office Branch.
	 *
	 * @return Response
	 */
	public function editOfficeBranch()
	{
		$values = Input::all();
		$values["Sdf"];
		if (\Request::isMethod('post'))
		{
		$field_names = array("cityname"=>"cityId","officebranchcode"=>"code", "officebranchname"=>"name","statename"=>"stateId","iswarehouse"=>"isWareHouse");
			$fields = array();
			foreach ($field_names as $key=>$val){
				if(isset($values[$key])){
					$fields[$val] = $values[$key];
				}
			}
			$field_names1 = array(
					"advanceamount"=>"advanceAmount",  "monthlyrent"=>"monthlyRent", "ownername"=>"ownerName", "contactno"=>"ownerContactNo", "occupationdate"=>"occupiedDate", "agreementexpdate"=>"expDate",
					"paymenttype"=>"paymentType", "bankaccount"=>"bankAccount", "paymentexpecteday"=>"paymentExpectedDay", "currentbillpaidbyowner"=>"currentBillPaidByOwner", "muncipaltaxpaidbyowner"=>"muncipalTaxPaidByOwner"
					);
			$fields1 = array();
			foreach ($field_names1 as $key=>$val){
				if(isset($values[$key])){
					if($val == "occupiedDate" || $val == "expDate"){
						$fields1[$val] = date("Y-m-d",strtotime($values[$key]));
					}
					else {
						$fields1[$val] = $values[$key];
					}
				}
			}
			$db_functions_ctrl = new DBFunctionsController();
			$table1 = "\RentDetails";
			
			
			$update = \OfficeBranch::where("id","=",$values['id'])->update($fields);	
			$data = array("id"=>$values['id']);
			$id = $values['id'];
			
			$rentid  = \RentDetails::where("officeBranchId","=",$values['id'])->get();
			if(count($rentid)>0){				
				$rentid = $rentid[0]->id;
				$data = array("id"=>$rentid);
				if($db_functions_ctrl->update($table1, $fields1, $data)){
					\Session::put("message","Operation completed Successfully");
					return \Redirect::to("editofficebranch?id=".$id);
				}
				else{
					\Session::put("message","Operation Could not be completed, Try Again!");
					return \Redirect::to("editofficebranch?id=".$id);
				}
			}
			\Session::put("message","Operation Could not be completed, Try Again!");
			return \Redirect::to("editofficebranch?id=".$id);
		}
	
		$form_info = array();
		$form_info["name"] = "editofficebranch?id";
		$form_info["action"] = "editofficebranch?id=".$values['id'];
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "officebranches";
		$form_info["bredcum"] = "edit office branch";
	
		$form_fields = array();
	
		$states =  \State::Where("status","=","ACTIVE")->get();
		$state_arr = array();
		foreach ($states as $state){
			$state_arr[$state['id']] = $state->name;
		}
		$select_args = array();
		$select_args[] = "officebranch.name as name";
		$select_args[] = "officebranch.code as code";
		$select_args[] = "officebranch.stateId as stateId";
		$select_args[] = "officebranch.cityId as cityId";
		$select_args[] = "officebranch.id as id";
		$select_args[] = "officebranch.isWareHouse as isWareHouse";
		$select_args[] = "rentdetails.ownerName as ownerName";
		$select_args[] = "rentdetails.id as id1";
		$select_args[] = "rentdetails.ownerContactNo as ownerContactNo";
		$select_args[] = "rentdetails.occupiedDate as occupiedDate";
		$select_args[] = "rentdetails.bankAccount as bankAccount";
		$select_args[] = "rentdetails.advanceAmount as advanceAmount";
		$select_args[] = "rentdetails.expDate as expDate";
		$select_args[] = "rentdetails.monthlyRent as monthlyRent";
		$select_args[] = "rentdetails.paymentType as paymentType";
		$select_args[] = "rentdetails.paymentExpectedDay as paymentExpectedDay";
		$select_args[] = "rentdetails.currentBillPaidByOwner as currentBillPaidByOwner";
		$select_args[] = "rentdetails.muncipalTaxPaidByOwner as muncipalTaxPaidByOwner";
		
		
		$entity = \OfficeBranch::where("officebranch.id","=", $values['id'])->leftjoin("rentdetails", "rentdetails.officeBranchId", "=", "officebranch.id")->select($select_args)->get();
		
		if(count($entity)){
			$entity = $entity[0];
			$states =  \State::Where("status","=","ACTIVE")->get();
			$state_arr = array();
			foreach ($states as $state){
				$state_arr[$state['id']] = $state->name;
			}
			
			$parentId = -1;
			$parent = \LookupTypeValues::where("name","=","PAYMENT TYPE")->get();
			if(count($parent)>0){
				$parent = $parent[0];
				$parentId = $parent->id;
			}
			$paymenttypes =  \LookupTypeValues::where("parentId","=",$parentId)->get();
			$pmttype_arr = array();
			foreach ($paymenttypes  as $paymenttype){
				$pmttype_arr[$paymenttype['name']] = $paymenttype->name;
			}
			
			$banks =  \BankDetails::all();
			$bank_arr = array();
			foreach ($banks as $bank){
				$bank_arr[$bank['id']] = $bank->bankName."-".$bank->accountNo;
			}
			
			$cities = \City::where("stateId", "=", $entity->stateId)->get();
			$cities_arr = array();
			foreach ($cities as $city){
				$cities_arr[$city['id']] = $city->name;
			}
	
			$tabs = array();
			$form_fields = array();
			$form_field = array("name"=>"statename", "value"=>$entity->stateId,  "content"=>"state name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeState(this.value);"), "class"=>"form-control", "options"=>$state_arr);
			$form_fields[] = $form_field;
			$form_field = array("name"=>"id", "value"=>$entity->id,  "content"=>"", "readonly"=>"",  "required"=>"", "type"=>"hidden", );
			$form_fields[] = $form_field;
			$form_field = array("name"=>"cityname", "value"=>$entity->cityId, "content"=>"city name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control", "options"=>$cities_arr);
			$form_fields[] = $form_field;
			$form_field = array("name"=>"officebranchname", "value"=>$entity->name, "content"=>"office branch name", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"officebranchcode", "value"=>$entity->code, "content"=>"office branch code", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control");
			$form_fields[] = $form_field;
			$tab = array();
			$tab['form_fields'] = $form_fields;
			$tab['href'] = "tabone";
			$tab['heading'] = strtoupper("Branch Information");
			$tabs[] = $tab;
			
			
			
			$form_fields = array();
			$form_field = array("name"=>"advanceamount", "value"=>$entity->advanceAmount, "content"=>"advance amount", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"monthlyrent", "value"=>$entity->monthlyRent, "content"=>"monthly rent", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"ownername", "value"=>$entity->ownerName, "content"=>"owner name", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"contactno", "value"=>$entity->ownerContactNo, "content"=>"contact no", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"occupationdate", "value"=>date("d-m-Y",strtotime($entity->occupiedDate)), "content"=>"occupation date", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control date");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"agreementexpdate", "value"=>date("d-m-Y",strtotime($entity->expDate)), "content"=>"agreement exp date", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control date");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"paymenttype", "id"=>"paymenttype", "value"=>$entity->paymentType,  "content"=>"payment type", "readonly"=>"",  "action"=>array("type"=>"onchange","script"=>"showPaymentFields(this.value)"), "required"=>"", "type"=>"select", "class"=>"form-control select2",  "options"=>array("cash"=>"CASH","advance"=>"FROM ADVANCE","cheque_debit"=>"CHEQUE (CREDIT)","cheque_credit"=>"CHEQUE (DEBIT)","ecs"=>"ECS","neft"=>"NEFT","rtgs"=>"RTGS","dd"=>"DD","credit_card"=>"CREDIT CARD","debit_card"=>"DEBIT CARD"));
			$form_fields[] = $form_field;
// 			$form_field = array("name"=>"bankaccount", "value"=>$entity->bankAccount, "content"=>"bank account", "readonly"=>"",  "required"=>"","type"=>"select", "options"=>$bank_arr, "class"=>"form-control");
// 			$form_fields[] = $form_field;
// 			$form_field = array("name"=>"paymentexpecteday", "value"=>$entity->paymentExpectedDay, "content"=>"payment expected day [1-30]", "readonly"=>"",  "required"=>"","type"=>"text", "class"=>"form-control");
// 			$form_fields[] = $form_field;
			$form_field = array("name"=>"currentbillpaidbyowner", "value"=>$entity->currentBillPaidByOwner, "content"=>"Current Bill paid by Owner", "readonly"=>"",  "required"=>"required", "type"=>"select", "options"=>array("Yes"=>"Yes","No"=>"No"),  "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"muncipaltaxpaidbyowner", "value"=>$entity->muncipalTaxPaidByOwner, "content"=>"muncipal tax paid by owner", "readonly"=>"",  "required"=>"required", "type"=>"select", "options"=>array("Yes"=>"Yes","No"=>"No"), "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"iswarehouse", "value"=>$entity->isWareHouse, "content"=>"is warehouse", "readonly"=>"",  "required"=>"required", "type"=>"select", "options"=>array("Yes"=>"Yes","No"=>"No"), "class"=>"form-control");
			$form_fields[] = $form_field;
			$tab = array();
			$tab['form_fields'] = $form_fields;
			$tab['href'] = "tabtwo";
			$tab['heading'] = strtoupper("Rental information");
			$tabs[] = $tab;
			$form_info["tabs"] = $tabs;
			return View::make("masters.layouts.edittabbedform",array("form_info"=>$form_info));
		}
	}
	
	/**
	 * manage all Office Branches.
	 *
	 * @return Response
	 */
	public function manageOutpatients()
	{
		$values = Input::all();
		$values['bredcum'] = "OUT PATIENTS";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'addoutpatient';
		$values['form_action'] = 'outpatients';
		$values['action_val'] = '';
		$theads = array('Branch Id','Branch Name', "Branch Code", "City", "State", 
				"owner Name", "owner Contact No", "occupied Date", "exp Date", "monthly Rent", "payment Type", "pmt Exp Day", "Actions");
		$values["theads"] = $theads;
			
		$actions = array();
		$action = array("url"=>"editofficebranch?","css"=>"primary", "type"=>"", "text"=>"Edit");
		$actions[] = $action;
		$values["actions"] = $actions;
			
		if(!isset($values['entries'])){
			$values['entries'] = 10;
		}
	
		$values["provider"] = "outpatients";
			
		return View::make('registrations.datatable', array("values"=>$values));
	}
	
}
