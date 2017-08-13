<?php namespace registrations;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
class OutpatientController extends \Controller {

	/**
	 * add a new city.
	 *
	 * @return Response
	 */
	public function registration()
	{
		if (\Request::isMethod('post'))
		{
			$values = Input::all();
			$field_names = array("firstname"=>"firstName","lastname"=>"lastName", "phone"=>"mobile", "gender"=>"gender","dob"=>"dob","age"=>"age","additional_phone"=>"additional_phone","address"=>"address","area"=>"area","state"=>"state","city"=>"city",
					"marital_status"=>"marital_status","bloodgroup"=>"blood_group","religion"=>"religion","occupation"=>"occupation","email"=>"email_id","billing"=>"billing_details",
					"consulting_doctor"=>"consulting_doctor","department"=>"doct_department","consultaion_time"=>"consult_time","consultaion_fee"=>"consultaion_fee",
					"referredby"=>"referredby","complaint"=>"complaint"
			);
			$fields = array();
			foreach ($field_names as $key=>$val){
				if(isset($values[$key])){
					if($val == "dob"){
						$fields[$val] = date("Y-m-d",strtotime($values[$key]));
					}
					elseif($val == "billing_details"){
						$fields[$val] = "YES";
					}
					else {
						$fields[$val] = $values[$key];
					}
				}
			}
			$mrno = \Patients::orderBy('id', 'desc')->first();
			$mrno = $mrno->UHID;
			$mrno = "HMS".(substr($mrno, 3)+1);
			$fields['UHID'] = $mrno;
			$db_functions_ctrl = new DBFunctionsController();
			$table = "\Patients";
			
			if($db_functions_ctrl->insert($table, $fields)){
				\Session::put("message","Operation completed Successfully");
				return \Redirect::to("register");
			}
			else{
				\Session::put("message","Operation Could not be completed, Try Again!");
				return \Redirect::to("register");
			}
		}
		
		$form_info = array();
		$form_info["name"] = "register";
		$form_info["action"] = "register";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "";
		$form_info["bredcum"] = "Registration Form";
		
		$form_fields = array();
		
		$states =  \State::Where("id","!=",0)->get();
		$state_arr = array();
		foreach ($states as $state){
			$state_arr[$state['id']] = $state->state_name; 	
		}
		
		
		$doctors =  \Doctors::Where("status","=","ACTIVE")->get();
		$doctor_arr = array();
		foreach ($doctors as $doctor){
			$doctor_arr[$doctor['id']] = $doctor->name;
		}
		$tabs = array();
		$form_fields = array();
		$form_field = array("name"=>"firstname", "content"=>"first name", "readonly"=>"",  "required"=>"required", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"lastname", "content"=>"last name", "readonly"=>"",  "required"=>"required", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"phone", "content"=>"Mobile No", "readonly"=>"",  "required"=>"required", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"gender",  "content"=>"gender", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>array("MALE"=>"MALE","FEMALE"=>"FEMALE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"dob",  "content"=>"date of birth", "readonly"=>"",  "required"=>"required", "type"=>"text","action"=>array("type"=>"onChange", "script"=>"changeAge(this.value);"),  "class"=>"form-control date");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"age", "content"=>"Age", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"additional_phone", "content"=>"Additional phone", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
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
		$form_field = array("name"=>"state",  "content"=>"State", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>$state_arr, "action"=>array("type"=>"onChange", "script"=>"changeCity(this.value);"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"city",  "content"=>"City", "readonly"=>"",  "required"=>"", "type"=>"select", "options"=>array(), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"marital_status",  "content"=>"marital status", "readonly"=>"",  "required"=>"", "type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"bloodgroup",  "content"=>"Blood Group", "readonly"=>"",  "required"=>"", "type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"religion",  "content"=>"religion", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"occupation",  "content"=>"occupation", "readonly"=>"",  "required"=>"", "type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"email", "content"=>"email id", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$tab = array();
		$tab['form_fields'] = $form_fields;
		$tab['href'] = "tabthree";
		$tab['heading'] = strtoupper("Addtional Patient Information");
		$tabs[] = $tab;
		
		$form_fields = array();
		$form_field = array("name"=>"billing", "content"=>"Billing Details", "readonly"=>"",  "required"=>"", "type"=>"checkbox", "options"=>array("for_payment"=>"  For payment"), "class"=>"form-control");
		$form_fields[] = $form_field;
		
		$tab = array();
		$tab['form_fields'] = $form_fields;
		$tab['href'] = "tabfour";
		$tab['heading'] = strtoupper("sponsor information");
		$tabs[] = $tab;
		
		
		$form_fields = array();
		$form_field = array("name"=>"consulting_doctor", "content"=>"consulting doctor", "readonly"=>"",  "required"=>"required", "type"=>"select", "options"=>$doctor_arr,"action"=>array("type"=>"onChange", "script"=>"doctorInformation(this.value);"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"department", "content"=>"department", "readonly"=>"readonly",  "required"=>"required", "type"=>"text",  "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"consultaion_time", "content"=>"date & time", "value"=>date("d-m-Y h:i A"), "readonly"=>"readonly",  "required"=>"required","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"consultaion_fee", "content"=>"consultation fee", "readonly"=>"readonly",  "required"=>"","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"referredby", "content"=>"referred by", "readonly"=>"",  "required"=>"", "type"=>"text","class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"complaint", "content"=>"complaint", "readonly"=>"",  "required"=>"", "type"=>"textarea","class"=>"form-control");
		$form_fields[] = $form_field;
		$tab = array();
		$tab['form_fields'] = $form_fields;
		$tab['href'] = "tabsix";
		$tab['heading'] = strtoupper("admission information");
		$tabs[] = $tab;
		$form_info["tabs"] = $tabs;
		return View::make("registrations.registrationform",array("form_info"=>$form_info));		
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
	
	public function patientRegister()
	{
		if (\Request::isMethod('post'))
		{
			//$values["test"];
			$values = Input::all();
			$field_names = array("MRNO"=>"MRNO","consulting_doctor"=>"consulting_doctor","fullname"=>"full_name","mobile_number"=>"mobile_number","age"=>"age","gender"=>"gender","visit_type"=>"visit_type","identity_proof_type"=>"identity_proof_type");
			$fields = array();
			foreach ($field_names as $key=>$val){
				if(isset($values[$key])){
						$fields[$val] = $values[$key];
				}
			}
			if (isset($values["identity_proof"]) && Input::hasFile('identity_proof') && Input::file('identity_proof')->isValid()) {
				$destinationPath = storage_path().'/uploads/'; // upload path
				$extension = Input::file('identity_proof')->getClientOriginalExtension(); // getting image extension
				$fileName = uniqid().'.'.$extension; // renameing image
				Input::file('identity_proof')->move($destinationPath, $fileName); // upl1oading file to given path
				$fields["identity_proof"] = $fileName;
			}
			$db_functions_ctrl = new DBFunctionsController();
			$table = "Registration";
				
			if($db_functions_ctrl->insert($table, $fields)){
				\Session::put("message","Operation completed Successfully");
				return \Redirect::to("register");
			}
			else{
				\Session::put("message","Operation Could not be completed, Try Again!");
				return \Redirect::to("register");
			}
		}
		$mrno = \Registration::orderBy('id', 'desc')->first();
		$mrno = $mrno->MRNO;
		$mrno = "HMS".(substr($mrno, 3)+1);
		//$fields["empCode"] = $empCode;
		
		$form_info = array();
		$form_info["name"] = "register";
		$form_info["action"] = "register";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "";
		$form_info["bredcum"] = "";
	
		
		$doctors =  \Doctors::Where("status","=","ACTIVE")->get();
		$doctor_arr = array();
		foreach ($doctors as $doctor){
			$doctor_arr[$doctor['id']] = $doctor->name;
		}
		
		$proof_arr = array();
		$proofs =  \LookupTypeValues::Where("name","=","identity proof type")->first();
		if(count($proofs)>0){
			$proofs =  \LookupTypeValues::Where("parentId","=",$proofs->id)->get();
			foreach ($proofs as $proof){
				$proof_arr[$proof['id']] = $proof->name;
			}
		}
		
		
		$form_fields = array();
		$form_field = array("name"=>"MRNO", "content"=>"MRNO", "readonly"=>"readonly", "value"=>$mrno,"required"=>"","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"fullname", "content"=>"full name", "readonly"=>"",  "required"=>"required", "type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"mobile_number", "content"=>"mobile number", "readonly"=>"",  "required"=>"required", "type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"age", "content"=>"age", "readonly"=>"",  "required"=>"required", "type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"gender",  "content"=>"gender", "readonly"=>"",  "required"=>"required", "type"=>"select", "options"=>array("MALE"=>"MALE","FEMALE"=>"FEMALE"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"visit_type", "content"=>"visit type", "readonly"=>"",  "required"=>"", "type"=>"radio", "options"=>array("free"=>"free","pay"=>"pay"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"identity_proof_type", "content"=>"identity proof type", "readonly"=>"",  "required"=>"","type"=>"select", "class"=>"form-control chosen-select","options"=>$proof_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"consulting_doctor", "content"=>"consulting doctor", "readonly"=>"",  "required"=>"","type"=>"select", "class"=>"form-control chosen-select","options"=>$doctor_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"identity_proof", "content"=>"identity proof", "readonly"=>"",  "required"=>"", "type"=>"file", "class"=>"form-control");
		$form_fields[] = $form_field;
		
		$form_info["form_fields"] = $form_fields;
		return View::make("registrations.patientregistration",array("form_info"=>$form_info));
	}
	
	public function getpatientdetails(){
		$values = Input::all();
		$doctors =  \Doctors::Where("status","=","ACTIVE")->get();
		$doctor_arr = array();
		foreach ($doctors as $doctor){
			$doctor_arr[$doctor['id']] = $doctor->name;
		}
		$entity = \Registration::where("MRNO","=",$values["MRNO"])->first();
		$json_resp = array();
		if(count($entity)>0){
			$json_resp["consulting_doctor"] = $doctor_arr[$entity->consulting_doctor];
			$json_resp["full_name"] = $entity->full_name;
			$json_resp["mobile_number"] = $entity->mobile_number;
			$json_resp["age"] = $entity->age;
			$json_resp["gender"] = $entity->gender;
		}
		echo json_encode($json_resp);
	}
	public function getDoctorDetails(){
		$values = Input::all();
		$doctors =  \Doctors::Where("doctors.status","=","ACTIVE")
							->Where("doctors.id","=",$values["id"])
							->leftjoin("departments","departments.id","=","doctors.depart_id")
							->select("departments.name")
							->first();
		echo json_encode($doctors);
	}
	
	public function getCitiesbyStateId()
	{
		$values = Input::all();
		$entities = \City::where("stateId","=",$values['id'])->get();
		$response = "<option value=''> --select city-- </option>";
		foreach ($entities as $entity){
			$response = $response."<option value='".$entity->id."'>".$entity->name."</option>";
		}
		echo $response;
	}
	
	public function getAge(){
		$values = Input::all();
		$dob= date('Y',strtotime($values['date']));
		$today =date('Y');
		$diff = $today-$dob;
		echo json_encode($diff);
	}
	
}
