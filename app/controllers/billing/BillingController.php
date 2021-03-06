<?php namespace billing;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use billing\DBFunctionsController;
class BillingController extends \Controller {
	public function finalBilling()
	{	
		$values = Input::all();
			
			
		return View::make("billing.finalbill", array("values"=>$values));		
	/**
	 * manage all states.
	 *
	 * @return Response
	 */
	public function manageAddedPurchaseOrders()
	{
		$values = Input::all();
		$values['bredcum'] = "PURCHASE ORDER";
		$values['home_url'] = '#';
		$values['add_url'] = '#';
		$values['form_action'] = '#';
		$values['action_val'] = '#';
		
		$theads = array('name', "type", "remarks", "status", "Actions");
		$values["theads"] = $theads;
				
		$form_info = array();
		$form_info["name"] = "addpurchaseorder";
		$form_info["action"] = "addpurchaseorder";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "#";
		$form_info["bredcum"] = "add inventory lookup value";
		$form_info["addlink"] = "addparent";
		
		$form_fields = array();
		
		$types =  \InventoryLookupValues::where("parentId", "=", 0)->get();
		$types_arr = array();
		foreach ($types as $type){
			$types_arr[$type->id] = $type->name;
		}
		$val = "";
		if(!isset($values["type"])){
			$values["type"] = "-1";
		}
		
		$credit_sup_arr = array();
		$credit_sups = \CreditSupplier::All();
		foreach ($credit_sups as $credit_sup){
			$credit_sup_arr[$credit_sup->id] = $credit_sup->supplierName;
		}
		$emp_arr = array();
		$emps = \Employee::where("roleId","!=","19")->orWhere("roleId","!=","20")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
		
		$warehouse_arr = array();
		$warehouses = \OfficeBranch::where("isWareHouse","=","Yes")->get();
		foreach ($warehouses as $warehouse){
			$warehouse_arr[$warehouse->id] = $warehouse->name;
		}
		
		$form_field = array("name"=>"creditsupplier", "content"=>"credit supplier", "readonly"=>"", "required"=>"required","type"=>"select", "options"=>$credit_sup_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"warehouse", "content"=>"warehouse", "readonly"=>"", "required"=>"required","type"=>"select", "options"=>$warehouse_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"receivedby", "content"=>"received by", "readonly"=>"", "required"=>"required","type"=>"select", "options"=>$emp_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"orderdate", "content"=>"order date", "readonly"=>"", "required"=>"required","type"=>"text", "class"=>"form-control date-picker");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"billnumber", "content"=>"bill number", "readonly"=>"", "required"=>"required","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"amountpaid", "content"=>"amount paid", "readonly"=>"", "required"=>"required","type"=>"select", "options"=>array("Yes"=>"Yes","No"=>"No"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"comments", "content"=>"comments", "readonly"=>"", "required"=>"required","type"=>"textarea", "class"=>"form-control ");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"totalamount", "content"=>"total amount", "readonly"=>"", "required"=>"required","type"=>"text", "class"=>"form-control ");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"jsondata", "value"=>"", "content"=>"", "readonly"=>"", "required"=>"","type"=>"hidden", "class"=>"form-control ");
		$form_fields[] = $form_field;
		
		$form_info["form_fields"] = $form_fields;
		
		$values["form_info"] = $form_info;
		
		$form_info = array();
		
		$form_info["name"] = "edit";
			
		$form_info["action"] = "#";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$items_arr = array();
		$items = \Items::where("status","=","ACTIVE")->get();
		foreach ($items as $item){
			$items_arr[$item->id] = $item->name;
		}
		$item_info_arr = array("1"=>"info1","2"=>"info2");
		$form_fields = array();
		$form_field = array("name"=>"item", "content"=>"item", "readonly"=>"", "required"=>"required","type"=>"select", "options"=>$items_arr, "action"=>array("type"=>"onchange","script"=>"getManufacturers(this.value)"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"iteminfo", "content"=>"manufacturer", "readonly"=>"", "required"=>"required","type"=>"select", "options"=>array(),  "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"quantity", "content"=>"quantity", "readonly"=>"", "required"=>"required","type"=>"text", "class"=>"form-control ");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"unitprice", "content"=>"price of unit", "readonly"=>"", "required"=>"required","type"=>"text", "class"=>"form-control ");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"status", "content"=>"status", "readonly"=>"", "required"=>"required","type"=>"select", "options"=>array("New"=>"New","Old"=>"Old"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;		
		$modals[] = $form_info;
		
		$values["provider"] = "purchasedorder";
		
		$values["modals"] = $modals;
		return View::make('inventory.purchaseorder', array("values"=>$values));
	}
	
	/**
	 * manage all states.
	 *
	 * @return Response
	 */
	public function createBilling()
	{
		$values = Input::all();
		$values['bredcum'] = "BILLING";
		$values['home_url'] = '#';
		$values['add_url'] = '#';
		$values['form_action'] = '#';
		$values['action_val'] = '#';
	
		$theads = array('name', "type", "remarks", "status", "Actions");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "addbilling";
		$form_info["action"] = "addbilling";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "#";
		$form_info["bredcum"] = "add inventory lookup value";
		$form_info["addlink"] = "addparent";
	
		$form_fields = array();
	
		
		$form_field = array("name"=>"orderdate", "content"=>"order date", "readonly"=>"", "required"=>"required","type"=>"text","action"=>array("type"=>"onchange","script"=>"getendreading()"),  "class"=>"form-control date-picker");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"billnumber", "content"=>"bill number", "readonly"=>"", "action"=>array("type"=>"onChange","script"=>"verifyBillNo(this.value)"), "required"=>"","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"amountpaid", "content"=>"amount paid", "readonly"=>"", "required"=>"required","type"=>"select", "action"=>array("type"=>"onChange","script"=>"enablePaymentType(this.value)"), "options"=>array("Yes"=>"Yes","No"=>"No"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"paymenttype", "id"=>"paymenttype", "content"=>"payment type", "readonly"=>"", "required"=>"","type"=>"select", "action"=>array("type"=>"onchange","script"=>"showPaymentFields(this.value)"), "options"=>array("cash"=>"CASH","advance"=>"FROM ADVANCE","cheque_debit"=>"CHEQUE (CREDIT)","cheque_credit"=>"CHEQUE (DEBIT)","ecs"=>"ECS","neft"=>"NEFT","rtgs"=>"RTGS","dd"=>"DD","credit_card"=>"CREDIT CARD","debit_card"=>"DEBIT CARD"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"paymentdate", "content"=>"payment paid date", "readonly"=>"", "required"=>"", "type"=>"text", "class"=>"form-control date-picker");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"comments", "content"=>"comments", "readonly"=>"", "required"=>"","type"=>"textarea", "class"=>"form-control ");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"billfile", "content"=>"upload bill", "readonly"=>"", "required"=>"", "type"=>"file", "class"=>"form-control file");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"totalamount", "content"=>"total amount", "readonly"=>"", "required"=>"required","type"=>"text", "class"=>"form-control ");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"jsondata", "value"=>"", "content"=>"", "readonly"=>"", "required"=>"","type"=>"hidden", "class"=>"form-control ");
		$form_fields[] = $form_field;
		$stocktype = "NON OFFICE";
		if(isset($values["stocktype"]) && $values["stocktype"]=="office"){
			$stocktype = "OFFICE";
		}
		$form_field = array("name"=>"stocktype", "content"=>"", "value"=>$stocktype, "readonly"=>"", "required"=>"","type"=>"hidden", "class"=>"form-control");
		$form_fields[] = $form_field;
	
		$form_info["form_fields"] = $form_fields;
	
		$values["form_info"] = $form_info;
	
		$form_info = array();
	
		$form_info["name"] = "edit";
			
		$form_info["action"] = "#";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$items_arr = array();
		if(isset($values["stocktype"]) && $values["stocktype"]=="office"){
			$items = \Items::where("status","=","ACTIVE")->where("stockType","=","OFFICE")->get();
		}
		else{
			$items = \Items::where("status","=","ACTIVE")->where("stockType","=","NON OFFICE")->get();
		}
		foreach ($items as $item){
			$items_arr[$item->id] = $item->name;
		}
		$item_info_arr = array("1"=>"info1","2"=>"info2");
		$form_fields = array();
		$form_field = array("name"=>"item", "content"=>"drug", "readonly"=>"", "required"=>"required","type"=>"select", "options"=>$items_arr, "action"=>array("type"=>"onchange","script"=>"getManufacturers(this.value)"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"itemtype", "content"=>"drug type", "readonly"=>"", "required"=>"","type"=>"select", "options"=>array(),  "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"iteminfo", "content"=>"manufacturer", "readonly"=>"", "required"=>"required","type"=>"select", "options"=>array(),  "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"quantity", "content"=>"quantity", "readonly"=>"", "required"=>"required","type"=>"text", "class"=>"form-control ");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"unitprice", "content"=>"price of unit", "readonly"=>"", "required"=>"required","type"=>"text", "class"=>"form-control ");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$modals[] = $form_info;
	
		$values["provider"] = "billing";
	
		$values["modals"] = $modals;
		return View::make('billing.editpurchaseorder', array("values"=>$values));
	}
	
	
	public function billing()
	{
		$values = Input::all();
		if (\Request::isMethod('post'))
		{
			//val["sda"];
			$url = "billing?&type=".$values["type"]."&uhid=".$values["uhid"];
			\DB::beginTransaction();
// 			try{
				$db_functions_ctrl = new DBFunctionsController();
				$table = "\PatientTransactions"; 
				$jsonitems = json_decode($values["jsondata"]);
				if($values['type']=="diagnostics"){
					$table::where('type',"=", $values['type'])->where('uhid',"=", $values['uhid'])->update(array("status"=>"DELETED"));
					foreach($jsonitems as $jsonitem){
						$fields = array();
						$fields["uhid"] = $values["uhid"];
						$fields["entityId"] = $jsonitem->i6;
						$fields["type"] = $values["type"];
						$fields["amount"] = $jsonitem->i4;
						$fields["date"] =  date("Y-m-d", strtotime($jsonitem->i3));
						if($jsonitem->i5 == "undefined"){
							$db_functions_ctrl->insert($table, $fields);
						}
						else{
							$data = array("id"=>$jsonitem->i5);
							$fields["status"] = "ACTIVE";
							$db_functions_ctrl->update($table, $fields, $data);
						}
					}
				}
				else if($values['type']=="pharmacy"){
					$table::where('type',"=", $values['type'])->where('uhid',"=", $values['uhid'])->update(array("status"=>"DELETED"));
					foreach($jsonitems as $jsonitem){
						$fields = array();
						$fields["uhid"] = $values["uhid"];
						$fields["entityId"] = $jsonitem->i6;
						$fields["type"] = $values["type"];
						$fields["batchNo"] = $jsonitem->i1;
						$fields["expiryDate"] =  date("Y-m-d", strtotime($jsonitem->i2));
						$fields["qty"] = $jsonitem->i3;
						$fields["amount"] = $jsonitem->i4;
						if($jsonitem->i5 == "undefined"){
							$db_functions_ctrl->insert($table, $fields);
						}
						else{
							$data = array("id"=>$jsonitem->i5);
							$fields["status"] = "ACTIVE";
							$db_functions_ctrl->update($table, $fields, $data);
						}
					}
				}
// 			}
// 			catch(\Exception $ex){
// 				\Session::put("message","Update Purchase2 Item : Operation Could not be completed, Try Again!");
// 				\DB::rollback();
// 				\Redirect::to($url);
// 			}
			\DB::commit();
			\Session::put("message","operation completed successfully!");
			\Redirect::to($url);
		}
		$values['bredcum'] = "ADD DIAGNOSTICS";
		$values['home_url'] = '#';
		$values['add_url'] = '#';
		$values['form_action'] = '#';
		$values['action_val'] = '#';
	
		$theads = array('name', "type", "remarks", "status", "Actions");
		$values["theads"] = $theads;
		
		$values["id"] = 10;
		$form_info = array();
		$form_info["name"] = "billing";
		$form_info["action"] = "billing";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "#";
		$form_info["bredcum"] = "add inventory lookup value";
		$form_info["addlink"] = "addparent";
	
		$form_fields = array();
	
		$types =  \InventoryLookupValues::where("parentId", "=", 0)->get();
		$types_arr = array();
		foreach ($types as $type){
			$types_arr[$type->id] = $type->name;
		}
		$val = "";
		$patient = "";
		if(!isset($values["uhid"])){
			return View::make('billing.billing', array("values"=>$values));
		}
		else if(isset($values["uhid"])){
			$patient = \Patients::where("UHID","=",$values["uhid"])->first();
			if(count($patient)<=0){
				\Session::put("message","UHID is not found, Try Again!");
				return View::make('billing.billing', array("values"=>$values));
			}
		}
	
		if(true){
			$form_field = array("name"=>"uhid", "content"=>"UHID", "value"=>$patient->UHID, "readonly"=>"readonly", "required"=>"","type"=>"text", "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"firstname", "value"=>$patient->firstName, "content"=>"first name", "readonly"=>"readonly",  "required"=>"required", "type"=>"text","class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"lastname", "value"=>$patient->lastName, "content"=>"last name", "readonly"=>"readonly",  "required"=>"required", "type"=>"text","class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"phone", "value"=>$patient->phone, "content"=>"Mobile No", "readonly"=>"readonly",  "required"=>"required", "type"=>"text","class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"gender",  "value"=>$patient->gender, "content"=>"gender", "readonly"=>"readonly",  "required"=>"", "type"=>"text", "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"dob",  "value"=>$patient->dob, "content"=>"date of birth", "readonly"=>"readonly",  "required"=>"required", "type"=>"text","action"=>array("type"=>"onChange", "script"=>"changeAge(this.value);"),  "class"=>"form-control date");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"age", "value"=>$patient->age, "content"=>"Age", "readonly"=>"readonly",  "required"=>"", "type"=>"text","class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"totalamount", "value"=>"", "content"=>"total amount", "readonly"=>"readonly",  "required"=>"", "type"=>"text","class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"jsondata", "value"=>"", "content"=>"", "readonly"=>"readonly",  "required"=>"", "type"=>"hidden","class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"type", "value"=>$values["type"], "content"=>"", "readonly"=>"readonly",  "required"=>"", "type"=>"hidden","class"=>"form-control");
			$form_fields[] = $form_field;
	
			$form_info["form_fields"] = $form_fields;
			$form_info["form_payment_fields"] = array();
			$values["form_info"] = $form_info;
			$form_info = array();
			$form_info["name"] = "edit";
			$form_info["action"] = "#";
			$form_info["method"] = "post";
			$form_info["class"] = "form-horizontal";
			
			if($values["type"]=="diagnostics"){
				$items = \LabTests::where("status","=","ACTIVE")->get();
				foreach ($items as $item){
					$items_arr[$item->id] = $item->name;
				}
				$form_fields = array();
				$form_field = array("name"=>"name", "content"=>"test name", "readonly"=>"", "required"=>"required","type"=>"select", "options"=>$items_arr, "action"=>array("type"=>"onchange","script"=>"getTestDetails(this.value)"), "class"=>"form-control chosen-select");
				$form_fields[] = $form_field;
				$form_field = array("name"=>"code", "content"=>"code", "readonly"=>"readonly", "required"=>"","type"=>"text",  "class"=>"form-control");
				$form_fields[] = $form_field;
				$form_field = array("name"=>"description", "content"=>"description", "readonly"=>"readonly", "required"=>"","type"=>"text",  "class"=>"form-control");
				$form_fields[] = $form_field;
				$form_field = array("name"=>"amount", "content"=>"amount", "readonly"=>"readonly", "required"=>"required","type"=>"text", "class"=>"form-control");
				$form_fields[] = $form_field;
				$form_field = array("name"=>"date", "content"=>"test date", "readonly"=>"", "required"=>"required", "type"=>"text", "class"=>"form-control date-picker");
				$form_fields[] = $form_field;
				$form_info["form_fields"] = $form_fields;
				$modals[] = $form_info;
				$values["provider"] = "purchasedorder";
				$values["modals"] = $modals;
				return View::make('billing.diagnostics', array("values"=>$values));
			}
			else if($values["type"]=="pharmacy"){
				$select_fields = array();
				$select_fields[] = "items.name as name";
				$select_fields[] = "purchased_items.qty as qty";
				$select_fields[] = "purchased_items.batchNo as batchNo";
				$select_fields[] = "purchased_items.id as id";
				$stockitems =  \PurchasedOrders::where("purchased_items.status","=","ACTIVE")->where("purchased_items.qty",">",0)->join("purchased_items","purchased_items.purchasedOrderId","=","purchase_orders.id")->join("items","purchased_items.itemId","=","items.id")->select($select_fields)->get();
				$stockitems_arr = array();
				foreach ($stockitems as $stockitem){
					$stockitems_arr[$stockitem['id']] = $stockitem->name." (".$stockitem->qty.", ".$stockitem->batchNo.")";
				}
				$form_fields = array();
				$form_field = array("name"=>"name", "content"=>"drug name", "readonly"=>"", "required"=>"required","type"=>"select", "options"=>$stockitems_arr,  "class"=>"form-control chosen-select");
				$form_fields[] = $form_field;
				$form_field = array("name"=>"qty", "content"=>"quantity", "readonly"=>"", "required"=>"required", "type"=>"text", "action"=>array("type"=>"onchange","script"=>"getItemInfo(this.value)"), "class"=>"form-control");
				$form_fields[] = $form_field;
				$form_field = array("name"=>"batchno", "content"=>"batch no", "readonly"=>"readonly", "required"=>"required", "type"=>"text",  "class"=>"form-control");
				$form_fields[] = $form_field;
				$form_field = array("name"=>"expirydate", "content"=>"expiry date", "readonly"=>"", "required"=>"required", "type"=>"text", "class"=>"form-control date-picker");
				$form_fields[] = $form_field;
				$form_field = array("name"=>"amount", "content"=>"amount", "readonly"=>"readonly", "required"=>"required","type"=>"text", "class"=>"form-control");
				$form_fields[] = $form_field;
				$form_info["form_fields"] = $form_fields;
				$modals[] = $form_info;
				$values["provider"] = "purchasedorder";
				$values["modals"] = $modals;
				return View::make('billing.pharmacy', array("values"=>$values));
			}
		}
	}
	
	/**
	 * manage all states.
	 *
	 * @return Response
	 */
	public function managePurchaseOrders()
	{
		$values = Input::all();
		$values['bredcum'] = "PURCHASE ORDERS";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'addvehicle';
		$values['form_action'] = 'vehicles';
		$values['action_val'] = '';
	
		$action_val = "";
		$links = array();
		$values['action_val'] = $action_val;
		$values['links'] = $links;
		
		$values['create_link'] = array("href"=>"createpurchaseorder?stocktype=nonoffice","text"=>"CREATE PURCHASE ORDER");
		//$values['create_link1'] = array("href"=>"createpurchaseorder?stocktype=office","text"=>"CREATE OFFICE PURCHASE ORDER");
		
		$theads = array('Credit supplier', "Items (qty)", "order date", "pmt date", "bill number", "amount paid", "payment type", "total amount", "comments", "status",  'created by',  "Actions");
		$values["theads"] = $theads;
	
		//Code to add modal forms
		$modals =  array();
			
		$form_info = array();
		$form_info["name"] = "block";
		$form_info["action"] = "blockvehicle";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
	
		$form_fields = array();
		$form_field = array("name"=>"vehreg", "content"=>"Veh Reg No", "readonly"=>"readonly",  "required"=>"", "type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"blockeddate", "content"=>"blocked date", "readonly"=>"", "required"=>"required","type"=>"text", "class"=>"form-control date-picker");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"id1", "content"=>"", "readonly"=>"readonly",  "required"=>"", "type"=>"hidden", "value"=>"", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"remarks", "readonly"=>"", "content"=>"remarks", "required"=>"", "type"=>"textarea", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$modals[] = $form_info;
			
		$form_info = array();
		$form_info["name"] = "sell";
		$form_info["action"] = "sellvehicle";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
			
		$form_fields = array();
		$form_field = array("name"=>"vehreg1", "content"=>"Veh Reg No", "readonly"=>"readonly",  "required"=>"", "type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"soldto", "content"=>"sold to", "readonly"=>"", "required"=>"required","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"address", "readonly"=>"", "content"=>"address", "required"=>"", "type"=>"textarea", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"totalcost", "content"=>"total cost", "readonly"=>"", "required"=>"required","type"=>"text", "class"=>"form-control number");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"paidamount", "content"=>"paid amount", "readonly"=>"", "required"=>"required","type"=>"text", "class"=>"form-control number");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"solddate", "content"=>"sold date", "readonly"=>"", "required"=>"required","type"=>"text", "class"=>"form-control date-picker");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"id2", "content"=>"", "readonly"=>"readonly",  "required"=>"", "type"=>"hidden", "value"=>"", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"remarks1", "readonly"=>"", "content"=>"remarks", "required"=>"", "type"=>"textarea", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$modals[] = $form_info;
		$values["modals"] = $modals;
	
		$values["provider"] = "purchaseorders";
		return View::make('inventory.datatable', array("values"=>$values));
	}
	
	public function getManufacturers(){
		$values = Input::all();
		$jsondata = array();
		$itemid = $values["itemid"];
		$item = \Items::where("id","=",$itemid)->first();
		
		$jsondata["itemnumberstatus"] = $item->itemNumber;
		
		$mans = "";
		$mans_arr = explode(",",$item->manufactures);
		foreach ($mans_arr as $man){
			if($man != "") {
				$manId = $man;
				$man = \Manufacturers::where("id","=",$man)->get();
				$man = $man[0];
				$man = $man->name;
				$mans = $mans."<option value='".$manId."' >".$man."</option>";
			}
		}
		if($mans == ""){
			$mans = $mans."<option value='' ></option>";
		}
		$jsondata["manufactures"] = $mans;
		

		$types = "";
		$mans_arr = explode(",",$item->itemTypeId);
		foreach ($mans_arr as $man){
			if($man != "") {
				$manId = $man;
				$man = \ItemTypes::where("id","=",$man)->get();
				$man = $man[0];
				$man = $man->name;
				$types = $types."<option value='".$manId."' >".$man."</option>";
			}
		}
		if($types == ""){
			$types = $types."<option value='' ></option>";
		}
		$jsondata["itemtypes"] = $types;
		echo json_encode($jsondata);
	}
	
	public function verifyBillNo(){
		$values = Input::all();
		$recs = \PurchasedOrders::where("creditSupplierId","=", $values["creditsupplier"])
								->where("billNumber","=", $values["billno"])->first();
		if(count($recs)>0){
			echo "yes";
		}
		else{
			echo "no";
		}
	}
	
	public function getTestDetails(){
		$values = Input::all();
		$jsondata = array();
		$test = \LabTests::where("id","=",$values["id"])->where("status","=","ACTIVE")->first();
		$jsondata["code"] = $test->code;
		$jsondata["description"] = $test->description;
		$jsondata["amount"] = $test->amount;
		echo json_encode($jsondata);
	}
	
	public function deletePurchaseOrder(){
		$values = Input::all();
		$itemid = $values["id"];
		$fields = array("status"=>"DELETED");
		$data = array('id'=>$values['id']);
		$db_functions_ctrl = new DBFunctionsController();
		$table = "\PurchasedOrders";
			
		if($db_functions_ctrl->update($table, $fields, $data)){
			echo "success";
		}
		else{
			echo "fail";
		}
	}
}
