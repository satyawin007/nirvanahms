<?php namespace transactions;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use masters\BlockDataEntryController;
use settings\AppSettingsController;
class DataTableController extends \Controller {

	/**
	 * add a new city.
	 *
	 * @return Response
	 */
	private $jobs;
	
	public function getDataTableData()
	{
		$this->jobs = \Session::get("jobs");
		$values = Input::All();
		$start = $values['start'];
		$length = $values['length'];
		$total = 0;
		$data = array();
		
		if(isset($values["name"]) && $values["name"]=="income") {
			$ret_arr = $this->getIncomeTransactions($values, $length, $start);
			$total = $ret_arr["total"];
			$data = $ret_arr["data"];
		}
		else if(isset($values["name"]) && $values["name"]=="fuel") {
			$ret_arr = $this->getFuelTransactions($values, $length, $start);
			$total = $ret_arr["total"];
			$data = $ret_arr["data"];
		}
		else if(isset($values["name"]) && $values["name"]=="expense") {
			$ret_arr = $this->getExpenseTransactions($values, $length, $start);
			$total = $ret_arr["total"];
			$data = $ret_arr["data"];
		}
		else if(isset($values["name"]) && $values["name"]=="vehicle_repairs") {
			$ret_arr = $this->getVehicleRepairs($values, $length, $start);
			$total = $ret_arr["total"];
			$data = $ret_arr["data"];
		}
		else if(isset($values["name"]) && $values["name"]=="getrepairtransactionitems") {
			$ret_arr = $this->getRepairTransactionItems($values, $length, $start);
			$total = $ret_arr["total"];
			$data = $ret_arr["data"];
		}
		
		$json_data = array(
				"draw"            => intval( $_REQUEST['draw'] ),
				"recordsTotal"    => intval( $total ),
				"recordsFiltered" => intval( $total ),
				"data"            => $data
			);
		echo json_encode($json_data);
	}
	
	private function getLookupValues($values, $length, $start, $typeId){
		$total = 0;
		$data = array();
		$select_args = array('name', "parentId", "remarks", "modules", "fields", "enabled", "status", "id");
	
		$actions = array();
		$action = array("url"=>"#edit", "type"=>"modal", "css"=>"primary", "js"=>"modalEditLookupValue(", "jsdata"=>array("id","name","remarks","modules","fields","enabled","status"), "text"=>"EDIT");
		$actions[] = $action;
		$values["actions"] = $actions;
	
		$search = $_REQUEST["search"];
		$search = $search['value'];
		if($search != ""){			
			$entities = \LookupTypeValues::where("name", "like", "%$search%")->select($select_args)->limit($length)->offset($start)->get();
			$parentName = \LookupTypeValues::where("id","=",$values["type"])->get();
			if(count($parentName)>0){
				$parentName = $parentName[0];
				$parentName = $parentName->name;
				foreach ($entities as $entity){
					$entity->parentId = $parentName;
				}
			}
			$total = \LookupTypeValues::where("name", "like", "%$search%")->count();
		}
		else{
			$entities = \LookupTypeValues::where("parentId", "=",$typeId)->select($select_args)->limit($length)->offset($start)->get();
			$parentName = \LookupTypeValues::where("id","=",$values["type"])->get();
			if(count($parentName)>0){
				$parentName = $parentName[0];
				$parentName = $parentName->name;
				foreach ($entities as $entity){
					$entity->parentId = $parentName;
				}
			}
			$total = \LookupTypeValues::where("parentId", "=",$typeId)->count();
		}
	
		$entities = $entities->toArray();
		foreach($entities as $entity){
			$data_values = array_values($entity);
			$actions = $values['actions'];
			$action_data = "";
			foreach($actions as $action){
				if($action["type"] == "modal"){
					$jsfields = $action["jsdata"];
					$jsdata = "";
					$i=0;
					for($i=0; $i<(count($jsfields)-1); $i++){
						$jsdata = $jsdata." '".$entity[$jsfields[$i]]."', ";
					}
					$jsdata = $jsdata." '".$entity[$jsfields[$i]];
					$action_data = $action_data. "<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."' data-toggle='modal' onClick=\"".$action['js'].$jsdata."')\">".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
				}
				else {
					$action_data = $action_data."<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."&id=".$entity['id']."'>".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
				}
			}
			if(isset($entity["workFlowStatus"]) && $entity["workFlowStatus"]=="Approved"){
				$action_data = "";
			}
			$data_values[7] = $action_data;
			$data[] = $data_values;
		}
		return array("total"=>$total, "data"=>$data);
	}
	
	private function getIncomeTransactions($values, $length, $start){
		$total = 0;
		$data = array();
		$select_args = array();
		$select_args[] = "incometransactions.transactionId as id";
		if(isset($values["contracts"]) && $values["contracts"]=="true"){
			$select_args[] = "clients.name as clientname";
		}
		else{
			$select_args[] = "officebranch.name as branchId";
		}
		$select_args[] = "lookuptypevalues.name as name";
		$select_args[] = "incometransactions.date as date";
		$select_args[] = "incometransactions.amount as amount";
		$select_args[] = "incometransactions.paymentType as paymentType";
		$select_args[] = "incometransactions.billNo as billNo";
		$select_args[] = "incometransactions.remarks as remarks";
		$select_args[] = "employee2.fullName as createdBy";
		$select_args[] = "incometransactions.workFlowStatus as workFlowStatus";
		$select_args[] = "employee3.fullName as updatedBy";
		$select_args[] = "incometransactions.workFlowRemarks as workFlowRemarks";
		$select_args[] = "incometransactions.transactionId as id";
		$select_args[] = "incometransactions.lookupValueId as lookupValueId";
		$select_args[] = "incometransactions.branchId as branch";
		$select_args[] = "incometransactions.filePath as filePath";
		$select_args[] = "vehicle.veh_reg as veh_reg";
		$select_args[] = "incometransactions.chequeNumber as chequeNumber";
		$select_args[] = "incometransactions.bankAccount as bankAccount";
		$select_args[] = "incometransactions.createdBy as createdById";
		if(isset($values["contracts"]) && $values["contracts"]=="true"){
			$select_args[] = "depots.name as depotname";
		}
		if(!isset($values["daterange"])){
			return array("total"=>0, "data"=>array());
		}
		
		$actions = array();
		if(in_array(302, $this->jobs)){
			$action = array("url"=>"#edit", "type"=>"modal", "css"=>"primary", "js"=>"modalEditTransaction(", "jsdata"=>array("id"), "text"=>"EDIT");
			$actions[] = $action;
			$action = array("url"=>"#delete", "type"=>"modal", "css"=>"danger", "js"=>"deleteTransaction(", "jsdata"=>array("id"), "text"=>"DELETE");
			$actions[] = $action;
		}
		$values["actions"] = $actions;
	
		$search = $_REQUEST["search"];
		$search = $search['value'];
		if($search != ""){
			$entities = \IncomeTransaction::where("incometransactions.status","=","ACTIVE")
							->where("transactionId", "like", "%$search%")
							->where("branchId","=",$values["branch1"])
							->leftjoin("officebranch", "officebranch.id","=","incometransactions.branchId")
							->leftjoin("vehicle", "vehicle.id","=","incometransactions.vehicleId")
							->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","incometransactions.lookupValueId")
							->select($select_args)->limit($length)->offset($start)->get();
			$total = \IncomeTransaction::where("incometransactions.status","=","ACTIVE")->where("transactionId", "like", "%$search%")->count();
			foreach ($entities as $entity){
				$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			}
		}
		else if(isset($values["contracts"]) && $values["contracts"]=="true" && isset($values["depot"])){
			$dtrange = $values["daterange"];
			$dtrange = explode(" - ", $dtrange);
			$startdt = date("Y-m-d",strtotime($dtrange[0]));
			$enddt = date("Y-m-d",strtotime($dtrange[1]));
		
			$entities = \IncomeTransaction::where("incometransactions.status","=","ACTIVE")
							->where("contractId",">",0)
							->where("depots.id","=",$values["depot"])
							->whereBetween("date",array($startdt,$enddt))
							->leftjoin("vehicle", "vehicle.id","=","incometransactions.vehicleId")
							->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","incometransactions.lookupValueId")
							->leftjoin("contracts", "contracts.id","=","incometransactions.contractId")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")
							->leftjoin("employee as employee2", "employee2.id","=","incometransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","incometransactions.updatedBy")
							
			->select($select_args)->limit($length)->offset($start)->get();
		
			$total = \IncomeTransaction::where("incometransactions.status","=","ACTIVE")
							->where("contractId",">",0)
							->where("depots.id","=",$values["depot"])
							->whereBetween("date",array($startdt,$enddt))
							->leftjoin("vehicle", "vehicle.id","=","incometransactions.vehicleId")
							->leftjoin("contracts", "contracts.id","=","incometransactions.contractId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")
							->where("contractId",">",0)->count();
			foreach ($entities as $entity){
				$entity["clientname"] = $entity["depotname"]." (".$entity["clientname"].")";
				$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			}
		}
		else{
			$dtrange = $values["daterange"];
			$dtrange = explode(" - ", $dtrange);
			$startdt = date("Y-m-d",strtotime($dtrange[0]));
			$enddt = date("Y-m-d",strtotime($dtrange[1]));
			$entities = \IncomeTransaction::where("incometransactions.status","=","ACTIVE")
							->where("branchId","=",$values["branch1"])
							->where("contractId","=",0)
							->whereBetween("date",array($startdt,$enddt))
							->leftjoin("officebranch", "officebranch.id","=","incometransactions.branchId")
							->leftjoin("employee as employee2", "employee2.id","=","incometransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","incometransactions.updatedBy")
							->leftjoin("vehicle", "vehicle.id","=","incometransactions.vehicleId")
							->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","incometransactions.lookupValueId")
							->select($select_args)->limit($length)->offset($start)->get();
			$total = \IncomeTransaction::where("incometransactions.status","=","ACTIVE")
							->where("contractId","=",0)
							->where("branchId","=",$values["branch1"])
							->whereBetween("date",array($startdt,$enddt))->count();
			foreach ($entities as $entity){
				$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			}
		}
	
		$entities = $entities->toArray();
		foreach($entities as $entity){
			if($entity["billNo"] != ""){
				if($entity["filePath"]==""){
					$entity["billNo"] = "<span style='color:red; font-weight:bold;'>".$entity["billNo"]."</span>";
				}
				else{
					$entity["billNo"] = "<a href='../app/storage/uploads/".$entity["filePath"]."' target='_blank'>".$entity["billNo"]."</a>";
				}
			}
			if($entity["lookupValueId"]>900 && $entity["lookupValueId"]<1000){
				$expenses_arr = array();
				$expenses_arr["998"] = "CREDIT SUPPLIER PAYMENT";
				$expenses_arr["997"] = "FUEL STATION PAYMENT";
				$expenses_arr["996"] = "LOAN PAYMENT";
				$expenses_arr["995"] = "RENT";
				$expenses_arr["994"] = "INCHARGE ACCOUNT CREDIT";
				$expenses_arr["993"] = "PREPAID RECHARGE";
				$expenses_arr["992"] = "ONLINE OPERATORS";
				$expenses_arr["999"] = "PREPAID RECHARGE";
				$entity["name"] = "";
				if(isset($expenses_arr[$entity["lookupValueId"]])){
					$entity["name"] = $expenses_arr[$entity["lookupValueId"]];
				}
			}
			if($entity["veh_reg"] != ""){
				$entity["name"] = $entity["name"]." (".$entity["veh_reg"].")";
			}
			//ecs neft rtgs
			if($entity["paymentType"] != "cash"){
				if($entity["paymentType"] == "ecs" || $entity["paymentType"] == "neft" || $entity["paymentType"] == "rtgs" || $entity["paymentType"] == "cheque_debit" || $entity["paymentType"] == "cheque_credit"){
					$entity["paymentType"] = "Payment Type : ".$entity["paymentType"]."<br/>";
					$bank_dt = \BankDetails::where("id","=",$entity["bankAccount"])->first();
					if(count($bank_dt)>0){
						$entity["paymentType"] = $entity["paymentType"]."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
					}
					$entity["paymentType"] = $entity["paymentType"]."Ref No : ".$entity["chequeNumber"];
				}
				if($entity["paymentType"] == "credit_card" || $entity["paymentType"] == "debit_card"){
					$entity["paymentType"] = "Payment Type : ".$entity["paymentType"]."<br/>";
					$bank_dt = \Cards::where("id","=",$entity["bankAccount"])->first();
					if(count($bank_dt)>0){
						$entity["paymentType"] = $entity["paymentType"]."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
					}
					$entity["paymentType"] = $entity["paymentType"]."Ref No : ".$entity["chequeNumber"];
				}
				if($entity["paymentType"] == "dd"){
					$entity["paymentType"] = "Payment Type : ".$entity["paymentType"]."<br/>";
					$entity["paymentType"] = $entity["paymentType"]."Ref No : ".$entity["chequeNumber"];
				}
			}
			$data_values = array_values($entity);
			$actions = $values['actions'];
			$action_data = "";
			$bde = new BlockDataEntryController();
			$values1 = array("branch"=>$entity["branch"],"date"=>$entity["date"]);
			$valid = $bde->verifyTransactionDateandBranchLocally($values1);
			foreach($actions as $action){
				if($action["type"] == "modal"){
					$jsfields = $action["jsdata"];
					$jsdata = "";
					$i=0;
					for($i=0; $i<(count($jsfields)-1); $i++){
						$jsdata = $jsdata." '".$entity[$jsfields[$i]]."', ";
					}
					$jsdata = $jsdata." '".$entity[$jsfields[$i]];
					
					if($valid=="YES"){
						$action_data = $action_data. "<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."' data-toggle='modal' onClick=\"".$action['js'].$jsdata."')\">".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
					}
				}
				else {
					if($valid=="YES"){
						$action_data = $action_data."<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."&id=".$entity['id']."'>".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
					}
				}
			}
			$login_user = \Auth::user()->fullName;
			$assignedemps = \Auth::user()->assignedEmpIds;
			$assignedemps_arr = explode(",", $assignedemps);
			if(isset($entity["createdById"]) && in_array($entity["createdById"], $assignedemps_arr)){
				//$action_data = "";
			}
			else if(isset($entity["createdBy"]) && $entity["createdBy"]!=$login_user){
				$action_data = "";
			}
			if(isset($entity["workFlowStatus"]) && $entity["workFlowStatus"]=="Approved"){
				$action_data = "";
			}
			$data_values[12] = $action_data;
			$data[] = $data_values;
		}
		return array("total"=>$total, "data"=>$data);
	}
	
	private function getVehicleRepairs($values, $length, $start){
		$total = 0;
		$data = array();
		$select_args = array();
		if(isset($values["type"]) && $values["type"]=="contracts"){
			$select_args[] = "clients.name as clientname";
		}
		else{
			$select_args[] = "officebranch.name as branchId";
		}
		$select_args[] = "creditsuppliers.supplierName as creditSupplierId";
		$select_args[] = "creditsuppliertransactions.date as date";
		$select_args[] = "creditsuppliertransactions.billNumber as billNumber";
		$select_args[] = "creditsuppliertransactions.paymentPaid as paymentPaid";
		$select_args[] = "employee4.fullName as inchargeId ";
		$select_args[] = "creditsuppliertransactions.paymentType as paymentType";
		$select_args[] = "creditsuppliertransactions.amount as amount";
		$select_args[] = "creditsuppliertransactions.comments as comments";
		$select_args[] = "creditsuppliertransdetails.vehicleIds as vehicleIds";
		$select_args[] = "creditsuppliertransactions.status as status";
		$select_args[] = "employee2.fullName as createdBy";
		$select_args[] = "creditsuppliertransactions.workFlowStatus as workFlowStatus";
		$select_args[] = "employee3.fullName as updatedBy";
		$select_args[] = "creditsuppliertransactions.workFlowRemarks as workFlowRemarks";
		$select_args[] = "creditsuppliertransactions.labourCharges as labourCharges";
		$select_args[] = "creditsuppliertransactions.electricianCharges as electricianCharges";
		$select_args[] = "creditsuppliertransactions.batta as batta";
		$select_args[] = "creditsuppliertransactions.id as id";
		$select_args[] = "creditsuppliertransactions.branchId as branch";
		$select_args[] = "creditsuppliertransactions.filePath as filePath";
		$select_args[] = "creditsuppliertransactions.createdBy as createdById";
		if(isset($values["type"]) && $values["type"]=="contracts"){
			$select_args[] = "depots.name as depotname";
		}
		$actions = array();
		if(in_array(308, $this->jobs)){
			$action = array("url"=>"editrepairtransaction?", "type"=>"", "css"=>"primary", "js"=>"modalEditRepairTransaction(", "jsdata"=>array("id"), "text"=>"EDIT");
			$actions[] = $action;
			$action = array("url"=>"#","css"=>"danger", "id"=>"deleteRepairTransaction", "type"=>"", "text"=>"DELETE");
			$actions[] = $action;
		}
		$values["actions"] = $actions;
		
		$fromdt = date("Y-m-d",strtotime($values["fromdate"]));
		$todt = date("Y-m-d",strtotime($values["todate"]));
			
		$search = $_REQUEST["search"];
		$search = $search['value'];
		if($search != ""){
			if(isset($values["type"]) && $values["type"]=="contracts"){
					$emp_depots = \Auth::user()->contractIds;
					$emp_depots_arr = array();
					if($emp_depots==""){
						$depots = \Depot::All();
						foreach ($depots as $depot){
							$emp_depots_arr[] = $depot->id; 
						}
					}
					else{
						$emp_depots_arr = explode(",",$emp_depots);
					}
					
					$entities = \Vehicle::where("veh_reg", "like", "%$search%")
										->where("vehicle.status","=","ACTIVE")->get();
					$veh_arr_str = "0,";
					foreach ($entities as $entity){
						$veh_arr_str = $veh_arr_str.$entity->id.",";
					}
					$veh_arr_str = substr($veh_arr_str, 0, strlen($veh_arr_str)-1);
					
					$supids_arr = array();
					$supids_arr_str = "";
					$suppliers = \CreditSupplier::where("supplierName","like","%$search%")->get();
					foreach ($suppliers as $supplier){
						$supids_arr[] = $supplier->id;
						$supids_arr_str = $supids_arr_str.$supplier->id.",";
					}
					$supids_arr_str = $supids_arr_str."0";
					
					$branchids_arr = array();
					$branchids_arr_str = "";
					$branches = \OfficeBranch::where("name","like","%$search%")->get();
					foreach ($branches as $branch){
						$branchids_arr[] = $branch->id;
						$branchids_arr_str = $branchids_arr_str.$branch->id.",";
					}
					$branchids_arr_str = $branchids_arr_str."0";
					
					$bills_arr = array();
					$bills_arr_str = "";
					$bills = \CreditSupplierTransactions::where("billNumber","like","%$search%")->get();
					foreach($bills as $bill){
						$bills_arr[] = $bill->billNumber;
						$bills_arr_str = $bills_arr_str."'".$bill->billNumber."',";
					}
					$bills_arr_str = substr($bills_arr_str, 0,strlen($bills_arr_str)-1);
					if($bills_arr_str==""){
						$bills_arr_str = "'-1'";
					}
					
					$entities = \CreditSupplierTransactions::whereRaw('(creditsuppliertransactions.branchId in('.$branchids_arr_str.') or creditsuppliertransactions.creditSupplierId in('.$supids_arr_str.') or creditsuppliertransdetails.vehicleIds in('.$veh_arr_str.') or creditsuppliertransactions.billNumber in('.$bills_arr_str.')) ')
														//->where("creditsuppliertransactions.branchId","=",$branchId)
														->whereIn("depots.id",$emp_depots_arr)
														->where("creditsuppliertransdetails.contractIds","!=","")
														->where("creditsuppliertransactions.deleted","=","No")
														->where("creditsuppliertransdetails.status","=","ACTIVE")
														->join("creditsuppliertransdetails", "creditsuppliertransdetails.creditSupplierTransId","=","creditsuppliertransactions.id")
														->leftjoin("contracts", "contracts.id","=","creditsuppliertransactions.contractId")
														->leftjoin("clients", "clients.id","=","contracts.clientId")
														->leftjoin("depots", "depots.id","=","contracts.depotId")
														->leftjoin("vehicle", "vehicle.id","=","creditsuppliertransactions.vehicleId")
														->leftjoin("employee as employee2", "employee2.id","=","creditsuppliertransactions.createdBy")
														->leftjoin("employee as employee3", "employee3.id","=","creditsuppliertransactions.updatedBy")
														->leftjoin("employee as employee4", "employee4.id","=","creditsuppliertransactions.inchargeId")
														->leftjoin("officebranch", "officebranch.id","=","creditsuppliertransactions.branchId")
														->leftjoin("creditsuppliers", "creditsuppliers.id","=","creditsuppliertransactions.creditSupplierId")
														->whereBetween("creditsuppliertransactions.date",array($fromdt,$todt))
														->select($select_args)->limit($length)->groupBy("creditsuppliertransactions.id")->offset($start)->get();
					$total = \CreditSupplierTransactions::whereRaw('(creditsuppliertransactions.branchId in('.$branchids_arr_str.') or creditsuppliertransactions.creditSupplierId in('.$supids_arr_str.') or creditsuppliertransdetails.vehicleIds in('.$veh_arr_str.') or creditsuppliertransactions.billNumber in('.$bills_arr_str.') ) ')
														->whereBetween("creditsuppliertransactions.date",array($fromdt,$todt))
														->where("creditsuppliertransactions.deleted","=","No")
														->whereIn("depots.id",$emp_depots_arr)
														//->where("creditsuppliertransactions.branchId","=",$branchId)
														->where("creditsuppliertransdetails.contractIds","!=","")
														->where("creditsuppliertransdetails.status","=","ACTIVE")
														->join("creditsuppliertransdetails", "creditsuppliertransdetails.creditSupplierTransId","=","creditsuppliertransactions.id")
														->leftjoin("contracts", "contracts.id","=","creditsuppliertransactions.contractId")
														->leftjoin("clients", "clients.id","=","contracts.clientId")
														->leftjoin("depots", "depots.id","=","contracts.depotId")
														->leftjoin("vehicle", "vehicle.id","=","creditsuppliertransactions.vehicleId")
														->leftjoin("employee as employee2", "employee2.id","=","creditsuppliertransactions.createdBy")
														->leftjoin("employee as employee3", "employee3.id","=","creditsuppliertransactions.updatedBy")
														->leftjoin("employee as employee4", "employee4.id","=","creditsuppliertransactions.inchargeId")
														->leftjoin("officebranch", "officebranch.id","=","creditsuppliertransactions.branchId")
														->leftjoin("creditsuppliers", "creditsuppliers.id","=","creditsuppliertransactions.creditSupplierId")
														->select($select_args)->groupBy("creditsuppliertransactions.id")->get();
					$total=count($total);
				}
			}
			else if(isset($values["type"]) && $values["type"]=="contracts"){
			$emp_depots = \Auth::user()->contractIds;
			$emp_depots_arr = array();
			if($emp_depots==""){
				$depots = \Depot::All();
				foreach ($depots as $depot){
					$emp_depots_arr[] = $depot->id; 
				}
			}
			else{
				$emp_depots_arr = explode(",",$emp_depots);
			}
			$entities = \CreditSupplierTransactions::where("creditsuppliertransactions.deleted","=","No")
							->where("creditsuppliertransdetails.contractIds","!=","")
							->where("depots.id",$values["depot"])
							->where("creditsuppliertransdetails.status","=","ACTIVE")
							->whereBetween("creditsuppliertransactions.date",array($fromdt,$todt))
							//->leftjoin("officebranch", "officebranch.id","=","creditsuppliertransactions.branchId")
							->join("creditsuppliertransdetails", "creditsuppliertransdetails.creditSupplierTransId","=","creditsuppliertransactions.id")
							->leftjoin("creditsuppliers", "creditsuppliers.id","=","creditsuppliertransactions.creditSupplierId")
							->leftjoin("contracts", "contracts.id","=","creditsuppliertransactions.contractId")
							->leftjoin("employee as employee2", "employee2.id","=","creditsuppliertransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","creditsuppliertransactions.updatedBy")
							->leftjoin("employee as employee4", "employee4.id","=","creditsuppliertransactions.inchargeId")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")
							->select($select_args)->groupBy("creditsuppliertransactions.id")->limit($length)->offset($start)->get();
			$total = \CreditSupplierTransactions::where("creditsuppliertransactions.deleted","=","No")
							->where("creditsuppliertransdetails.contractIds","!=","")
							->where("depots.id",$values["depot"])
							->where("creditsuppliertransdetails.status","=","ACTIVE")
							->whereBetween("creditsuppliertransactions.date",array($fromdt,$todt))
							//->leftjoin("officebranch", "officebranch.id","=","creditsuppliertransactions.branchId")
							->join("creditsuppliertransdetails", "creditsuppliertransdetails.creditSupplierTransId","=","creditsuppliertransactions.id")
							->leftjoin("creditsuppliers", "creditsuppliers.id","=","creditsuppliertransactions.creditSupplierId")
							->leftjoin("contracts", "contracts.id","=","creditsuppliertransactions.contractId")
							->leftjoin("employee as employee2", "employee2.id","=","creditsuppliertransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","creditsuppliertransactions.updatedBy")
							->leftjoin("employee as employee4", "employee4.id","=","creditsuppliertransactions.inchargeId")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")
							->select($select_args)->groupBy("creditsuppliertransactions.id")->get();
			$total = count($total);
			foreach ($entities as $entity){
				$entity["clientname"] = $entity["depotname"]." (".$entity["clientname"].")";
				$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			}
		}
		else{
			$entities = \CreditSupplierTransactions::where("creditsuppliertransactions.deleted","=","No")
							->where("creditsuppliertransdetails.contractIds","=","")
							->where("creditsuppliertransdetails.status","=","ACTIVE")
							->where("creditsuppliertransactions.branchId","=",$values["branch1"])
							->whereBetween("creditsuppliertransactions.date",array($fromdt,$todt))
							->leftjoin("creditsuppliertransdetails", "creditsuppliertransdetails.creditSupplierTransId","=","creditsuppliertransactions.id")
							->leftjoin("officebranch", "officebranch.id","=","creditsuppliertransactions.branchId")
							->leftjoin("employee as employee2", "employee2.id","=","creditsuppliertransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","creditsuppliertransactions.updatedBy")
							->leftjoin("employee as employee4", "employee4.id","=","creditsuppliertransactions.inchargeId")
							->leftjoin("creditsuppliers", "creditsuppliers.id","=","creditsuppliertransactions.creditSupplierId")
							->select($select_args)->groupBy("creditsuppliertransactions.id")->limit($length)->offset($start)->get();
			$total = \CreditSupplierTransactions::where("creditsuppliertransactions.deleted","=","No")
							->where("creditsuppliertransactions.branchId","=",$values["branch1"])
							->whereBetween("creditsuppliertransactions.date",array($fromdt,$todt))
							->leftjoin("creditsuppliertransdetails", "creditsuppliertransdetails.creditSupplierTransId","=","creditsuppliertransactions.id")
							->where("creditsuppliertransdetails.contractIds","=","")->groupBy("creditsuppliertransactions.id")->count();
				foreach ($entities as $entity){
					$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
				}
		}
		$entities = $entities->toArray();
		$vehs_arr = array();
		$vehicles = \Vehicle::All();
		foreach ($vehicles  as $vehicle){
			$vehs_arr[$vehicle->id] = $vehicle->veh_reg;
		}
		//print_r($entities);die();
		$select_args = array();
		$select_args[] = "creditsuppliertransdetails.vehicleIds as vehicleIds";
		$select_args[] = "lookuptypevalues.name as itemname";
		$select_args[] = "creditsuppliertransdetails.meeterReading as meeterReading";
		foreach($entities as $entity){
			//$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			$trans_items = \CreditSupplierTransDetails::where("creditSupplierTransId","=",$entity["id"])
								->where("creditsuppliertransdetails.status","=","ACTIVE")
								->leftjoin("lookuptypevalues","lookuptypevalues.id","=","creditsuppliertransdetails.repairedItem")				
								->select($select_args)->get();
			
			$entity["vehicleIds"] = "";
			foreach($trans_items as $trans_item){
				$vehs_arr_str = "";
				$veh_ids_arr = explode(",", $trans_item->vehicleIds);
				foreach ($veh_ids_arr  as $veh_id){
					if(isset($vehs_arr[$veh_id])){
						if($veh_id != ""){
							$vehs_arr_str = $vehs_arr_str.$vehs_arr[$veh_id].",";
						}
					}
				}
				$entity["vehicleIds"] = $entity["vehicleIds"]."<span style='color:red;' >VEHICLES : ".$vehs_arr_str."(".$trans_item->meeterReading.")</span><br/>";
				$entity["vehicleIds"] = $entity["vehicleIds"]."<span style='color:green;' >REPAIRED ITEM : ".$trans_item->itemname."</span><br/>";
			}
			if($entity["billNumber"] != ""){
				if($entity["filePath"]==""){
					$entity["billNumber"] = "<span style='color:red; font-weight:bold;'>".$entity["billNumber"]."</span>";
				}
				else{
					$entity["billNumber"] = "<a href='../app/storage/uploads/".$entity["filePath"]."' target='_blank'>".$entity["billNumber"]."</a>";
				}
			}
			$entity["vehicleIds"] = $entity["vehicleIds"]."Labour Charges : ".$entity["labourCharges"]."<br/>";
			$entity["vehicleIds"] = $entity["vehicleIds"]."Electricial Charges : ".$entity["electricianCharges"]."<br/>";
			$entity["vehicleIds"] = $entity["vehicleIds"]."Batta : ".$entity["batta"]."<br/>";
			$data_values = array_values($entity);
			$actions = $values['actions'];
			$action_data = "";
			
			$bde = new BlockDataEntryController();
			$values1 = array("branch"=>$entity["branch"],"date"=>$entity["date"]);
			$valid = $bde->verifyTransactionDateandBranchLocally($values1);
			foreach($actions as $action){
				if($action["type"] == "modal"){
					$jsfields = $action["jsdata"];
					$jsdata = "";
					$i=0;
					for($i=0; $i<(count($jsfields)-1); $i++){
						$jsdata = $jsdata." '".$entity[$jsfields[$i]]."', ";
					}
					$jsdata = $jsdata." '".$entity[$jsfields[$i]];
					
					if($valid=="YES"){
						$action_data = $action_data. "<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."' data-toggle='modal' onClick=\"".$action['js'].$jsdata."')\">".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
					}
				}
				else if($action["url"]=="#") {
					if($valid=="YES"){
						$action_data = $action_data."<a class='btn btn-minier btn-".$action["css"]."' onclick='".$action['id']."(".$entity['id'].")'>".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
					}
				}
				else {
					if($valid=="YES"){
						$action_data = $action_data."<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."&id=".$entity['id']."'>".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
					}
				}
			}
			$login_user = \Auth::user()->fullName;
			$assignedemps = \Auth::user()->assignedEmpIds;
			$assignedemps_arr = explode(",", $assignedemps);
			if(isset($entity["createdById"]) && in_array($entity["createdById"], $assignedemps_arr)){
				//$action_data = "";
			}
			else if(isset($entity["createdBy"]) && $entity["createdBy"]!=$login_user){
				$action_data = "";
			}
			if(isset($entity["workFlowStatus"]) && $entity["workFlowStatus"]=="Approved"){
				$action_data = "";
			}
			$data_values[15] = $action_data;
			$data[] = $data_values;
		}
		return array("total"=>$total, "data"=>$data);
	}
	
	
	private function getFuelTransactions($values, $length, $start){
		$total = 0;
		$data = array();
		$select_args = array();
		
		if(isset($values["contracts"]) && $values["contracts"]=="true"){
			$select_args[] = "clients.name as clientname";
		}
		else{
			$select_args[] = "officebranch.name as branchId";
		}
		$select_args[] = "fuelstationdetails.name as fuelStationName";
		$select_args[] = "vehicle.veh_reg as vehicleId";
		$select_args[] = "fueltransactions.startReading as startReading";
		$select_args[] = "fueltransactions.litres as litres";
		$select_args[] = "fueltransactions.fullTank as fullTank";
		$select_args[] = "fueltransactions.mileage as mileage";
		$select_args[] = "employee4.fullName as inchargeId";
		$select_args[] = "fueltransactions.filledDate as date";
		$select_args[] = "fueltransactions.amount as amount";
		$select_args[] = "fueltransactions.billNo as billNo";
		$select_args[] = "fueltransactions.paymentType as paymentType";
		$select_args[] = "fueltransactions.remarks as remarks";
		$select_args[] = "employee2.fullName as createdBy";
		$select_args[] = "fueltransactions.workFlowStatus as workFlowStatus";
		$select_args[] = "employee3.fullName as updatedBy";
		$select_args[] = "fueltransactions.workFlowRemarks as workFlowRemarks";
		$select_args[] = "fueltransactions.id as id";
		$select_args[] = "fueltransactions.branchId as branch";
		$select_args[] = "fueltransactions.filePath as filePath";
		$select_args[] = "fueltransactions.chequeNumber as chequeNumber";
		$select_args[] = "fueltransactions.bankAccountId as bankAccount";
		$select_args[] = "fueltransactions.createdBy as createdById";
		
		$select_args[] = "vehicle.veh_reg as veh_reg";
		if(isset($values["contracts"]) && $values["contracts"]=="true"){
			$select_args[] = "depots.name as depotname";
		}
		
		$actions = array();
		if(in_array(306, $this->jobs)){
			$action = array("url"=>"#edit", "type"=>"modal", "css"=>"primary", "js"=>"modalEditTransaction(", "jsdata"=>array("id"), "text"=>"EDIT");
			$actions[] = $action;
			$action = array("url"=>"#delete", "type"=>"modal", "css"=>"danger", "js"=>"deleteTransaction(", "jsdata"=>array("id"), "text"=>"DELETE");
			$actions[] = $action;
		}
		$values["actions"] = $actions;
	
		$search = $_REQUEST["search"];
		$search = $search['value'];
		$entities = \Vehicle::where("id","=",0)->get();
		if($search != ""){
			if(isset($values["contracts"]) && $values["contracts"]=="true" && isset($values["depot"])){
				$dtrange = $values["daterange"];
				$dtrange = explode(" - ", $dtrange);
				$startdt = date("Y-m-d",strtotime($dtrange[0]));
				$enddt = date("Y-m-d",strtotime($dtrange[1]));
				
				$entities = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")
							->where("contractId",">",0)
							->where("depots.id","=",$values["depot"])
							->where("clients.id","=",$values["client"])
							->whereBetween("filledDate",array($startdt,$enddt))
							->where("veh_reg","like","%$search%")
							->leftjoin("vehicle", "vehicle.id","=","fueltransactions.vehicleId")
							->leftjoin("fuelstationdetails", "fuelstationdetails.id","=","fueltransactions.fuelStationId")
							->leftjoin("contracts", "contracts.id","=","fueltransactions.contractId")
							->leftjoin("employee as employee2", "employee2.id","=","fueltransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","fueltransactions.updatedBy")
							->leftjoin("employee as employee4", "employee4.id","=","fueltransactions.inchargeId")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")
							->select($select_args)->orderBy("fueltransactions.vehicleId")->orderBy("filledDate","desc")->limit($length+1)->offset($start)->get();
					
				$total = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")
							->where("contractId",">",0)
							->where("depots.id","=",$values["depot"])
							->where("clients.id","=",$values["client"])
							->whereBetween("filledDate",array($startdt,$enddt))
							->where("veh_reg","like","%$search%")
							->leftjoin("vehicle", "vehicle.id","=","fueltransactions.vehicleId")
							->leftjoin("fuelstationdetails", "fuelstationdetails.id","=","fueltransactions.fuelStationId")
							->leftjoin("contracts", "contracts.id","=","fueltransactions.contractId")
							->leftjoin("employee as employee2", "employee2.id","=","fueltransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","fueltransactions.updatedBy")
							->leftjoin("employee as employee4", "employee4.id","=","fueltransactions.inchargeId")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")
							->select($select_args)->count();
				foreach ($entities as $entity){
					$entity["clientname"] = $entity["depotname"]." (".$entity["clientname"].")";
					$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
				}
			}
		}
		else if(isset($values["tripid"])){
			$entities = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")->where("tripId","=",$values["tripid"])->leftjoin("officebranch", "officebranch.id","=","fueltransactions.branchId")
										->leftjoin("vehicle", "vehicle.id","=","fueltransactions.vehicleId")
										->leftjoin("fuelstationdetails", "fuelstationdetails.id","=","fueltransactions.fuelStationId")
										->select($select_args)->orderBy("filledDate")->orderBy("vehicle.id")->limit($length+1)->offset($start)->get();
			$total =  \FuelTransaction::where("fueltransactions.status","=","ACTIVE")->where("tripId","=",$values["tripid"])->leftjoin("officebranch", "officebranch.id","=","fueltransactions.branchId")
										->leftjoin("vehicle", "vehicle.id","=","fueltransactions.vehicleId")
										->leftjoin("fuelstationdetails", "fuelstationdetails.id","=","fueltransactions.fuelStationId")
										->select($select_args)->count();
			foreach ($entities as $entity){
				$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			}
		}
		else if(isset($values["contracts"]) && $values["contracts"]=="true" && isset($values["depot"])){
			$dtrange = $values["daterange"];
			$dtrange = explode(" - ", $dtrange);
			$startdt = date("Y-m-d",strtotime($dtrange[0]));
			$enddt = date("Y-m-d",strtotime($dtrange[1]));
			
			$entities = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")
							->where("contractId",">",0)
							->where("depots.id","=",$values["depot"])
							->where("clients.id","=",$values["client"])
							->whereBetween("filledDate",array($startdt,$enddt))
							->leftjoin("vehicle", "vehicle.id","=","fueltransactions.vehicleId")
							->leftjoin("fuelstationdetails", "fuelstationdetails.id","=","fueltransactions.fuelStationId")
							->leftjoin("contracts", "contracts.id","=","fueltransactions.contractId")
							->leftjoin("employee as employee2", "employee2.id","=","fueltransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","fueltransactions.updatedBy")
							->leftjoin("employee as employee4", "employee4.id","=","fueltransactions.inchargeId")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")
							->select($select_args)->orderBy("fueltransactions.vehicleId")->orderBy("filledDate","desc")->limit($length+1)->offset($start)->get();
			
			$total = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")
							->where("contractId",">",0)
							->where("depots.id","=",$values["depot"])
							->where("clients.id","=",$values["client"])
							->whereBetween("filledDate",array($startdt,$enddt))
							->leftjoin("vehicle", "vehicle.id","=","fueltransactions.vehicleId")
							->leftjoin("fuelstationdetails", "fuelstationdetails.id","=","fueltransactions.fuelStationId")
							->leftjoin("contracts", "contracts.id","=","fueltransactions.contractId")
							->leftjoin("employee as employee2", "employee2.id","=","fueltransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","fueltransactions.updatedBy")
							->leftjoin("employee as employee4", "employee4.id","=","fueltransactions.inchargeId")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")
							->select($select_args)->count();
			foreach ($entities as $entity){
				$entity["clientname"] = $entity["depotname"]." (".$entity["clientname"].")";
				$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			}
		}
		else if(isset($values["branch1"])){
			$dtrange = $values["daterange"];
			$dtrange = explode(" - ", $dtrange);
			$startdt = date("Y-m-d",strtotime($dtrange[0]));
			$enddt = date("Y-m-d",strtotime($dtrange[1]));
				
			$entities = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")
							->where("branchId","=",$values["branch1"])
							->whereBetween("filledDate",array($startdt,$enddt))
							->leftjoin("officebranch", "officebranch.id","=","fueltransactions.branchId")
							->leftjoin("vehicle", "vehicle.id","=","fueltransactions.vehicleId")
							->leftjoin("employee as employee2", "employee2.id","=","fueltransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","fueltransactions.updatedBy")
							->leftjoin("employee as employee4", "employee4.id","=","fueltransactions.inchargeId")
							->leftjoin("fuelstationdetails", "fuelstationdetails.id","=","fueltransactions.fuelStationId")
							->select($select_args)->orderBy("fueltransactions.vehicleId")->orderBy("filledDate","desc")->limit($length+1)->offset($start)->get();
			$total = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")
							->where("branchId","=",$values["branch1"])
							->whereBetween("filledDate",array($startdt,$enddt))
							->leftjoin("officebranch", "officebranch.id","=","fueltransactions.branchId")
							->leftjoin("vehicle", "vehicle.id","=","fueltransactions.vehicleId")
							->leftjoin("employee as employee2", "employee2.id","=","fueltransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","fueltransactions.updatedBy")
							->leftjoin("employee as employee4", "employee4.id","=","fueltransactions.inchargeId")
							->leftjoin("fuelstationdetails", "fuelstationdetails.id","=","fueltransactions.fuelStationId")
							->select($select_args)->count();
		}
		$entities = $entities->toArray();
		$k=0;
		$r_cnt = 0;
		foreach($entities as $entity){
			if($r_cnt==$length){
				continue;	
			}
			$r_cnt++;
			$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			if($entity["billNo"] != ""){				
				if($entity["filePath"]==""){
					$entity["billNo"] = "<span style='color:red; font-weight:bold;'>".$entity["billNo"]."</span>";
				}
				else{
					$entity["billNo"] = "<a href='../app/storage/uploads/".$entity["filePath"]."' target='_blank'>".$entity["billNo"]."</a>";
				}
			}
			//$entity["mileage"] = "0";
			if($entity["fullTank"]=="YES" && $entity["mileage"] == "0.00"){
				$j = $k+1;
				$ltrs = 0;
				while($j<count($entities))
				{
					if($entities[$j]["vehicleId"]==$entity["vehicleId"] && $entities[$j]["fullTank"]=="YES"){
						$ltrs = $ltrs+$entity["litres"];
						$entity["mileage"] = round((($entity["startReading"]-$entities[$j]["startReading"])/$ltrs), 2);
						//echo $entities[$i]["startReading"].", ";
						$frec = \FuelTransaction::where("id","=",$entity["id"])->get();
						if(count($frec)>0){
							$frec = $frec[0];
							$frec->mileage = $entity["mileage"]; 
							$frec->update();
						}
						break;
					}
					if($j<count($entities) && $entities[$j]["fullTank"]=="NO"){
						$ltrs = $ltrs+$entities[$j]["litres"];
					}
					$j++;
				}
			}
			$k++;
			//ecs neft rtgs
			if($entity["paymentType"] != "cash"){
				if($entity["paymentType"] == "ecs" || $entity["paymentType"] == "neft" || $entity["paymentType"] == "rtgs" || $entity["paymentType"] == "cheque_debit" || $entity["paymentType"] == "cheque_credit"){
					$entity["paymentType"] = "Payment Type : ".$entity["paymentType"]."<br/>";
					$bank_dt = \BankDetails::where("id","=",$entity["bankAccount"])->first();
					if(count($bank_dt)>0){
						$entity["paymentType"] = $entity["paymentType"]."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
					}
					$entity["paymentType"] = $entity["paymentType"]."Ref No : ".$entity["chequeNumber"];
				}
				if($entity["paymentType"] == "credit_card" || $entity["paymentType"] == "debit_card" || $entity["paymentType"] == "hp_card"){
					$pmttype = "";
					if($entity["paymentType"] == "credit_card"){$pmttype = "CREDIT CARD";}
					if($entity["paymentType"] == "debit_card"){$pmttype = "DEBIT CARD";}
					if($entity["paymentType"] == "hp_card"){$pmttype = "HP CARD";}
					$entity["paymentType"] = "Payment Type : ".$pmttype."<br/>";
					$bank_dt = \Cards::where("id","=",$entity["bankAccount"])->first();
					if(count($bank_dt)>0){
						$entity["paymentType"] = $entity["paymentType"]."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
					}
					$entity["paymentType"] = $entity["paymentType"]."<br/>Ref No : ".$entity["chequeNumber"];
				}
				if($entity["paymentType"] == "dd"){
					$entity["paymentType"] = "Payment Type : ".$entity["paymentType"]."<br/>";
					$entity["paymentType"] = $entity["paymentType"]."<br/>Ref No : ".$entity["chequeNumber"];
				}
			}
			
			if($entity["createdBy"]=="")
			{
				$entity["createdBy"]="updatedby".$entity["updatedBy"];
			}
			$data_values = array_values($entity);
			$actions = $values['actions'];
			$action_data = "";
			$bde = new BlockDataEntryController();
			$values1 = array("branch"=>$entity["branch"],"date"=>$entity["date"]);
			$valid = $bde->verifyTransactionDateandBranchLocally($values1);
			foreach($actions as $action){
				if($action["type"] == "modal"){
					$jsfields = $action["jsdata"];
					$jsdata = "";
					$i=0;
					for($i=0; $i<(count($jsfields)-1); $i++){
						$jsdata = $jsdata." '".$entity[$jsfields[$i]]."', ";
					}
					$jsdata = $jsdata." '".$entity[$jsfields[$i]];
					
					if($valid=="YES"){
						$action_data = $action_data. "<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."' data-toggle='modal' onClick=\"".$action['js'].$jsdata."')\">".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
					}
				}
				else {
					if($valid=="YES"){
						$action_data = $action_data."<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."&id=".$entity['id']."'>".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
					}
				}
			}
			$login_user = \Auth::user()->fullName;
			$assignedemps = \Auth::user()->assignedEmpIds;
			$assignedemps_arr = explode(",", $assignedemps);
			if(isset($entity["createdById"]) && in_array($entity["createdById"], $assignedemps_arr)){
				//$action_data = "";
			}
			else if(isset($entity["createdBy"]) && $entity["createdBy"]!=$login_user){
				$action_data = "";
			}			
			if(isset($entity["workFlowStatus"]) && $entity["workFlowStatus"]=="Approved"){
				$action_data = "";
			}
			$data_values[17] = $action_data;
			$data[] = $data_values;
		}
		return array("total"=>$total, "data"=>$data);
	}
	
	private function getExpenseTransactions($values, $length, $start){
		$total = 0;
		$data = array();
		$select_args = array();
		$select_args[] = "expensetransactions.transactionId as id";
		
		if(isset($values["contracts"]) && $values["contracts"]=="true"){
			$select_args[] = "clients.name as clientname";
		}
		else{
			$select_args[] = "officebranch.name as branchId";
		}
		$select_args[] = "lookuptypevalues.name as name";
		$select_args[] = "expensetransactions.date as date";
		$select_args[] = "expensetransactions.amount as amount";
		$select_args[] = "expensetransactions.paymentType as paymentType";
		$select_args[] = "expensetransactions.billNo as billNo";
		$select_args[] = "expensetransactions.remarks as remarks";
		$select_args[] = "employee2.fullName as createdBy";
		$select_args[] = "expensetransactions.workFlowStatus as workFlowStatus";
		$select_args[] = "employee3.fullName as updateBy";
		$select_args[] = "expensetransactions.workFlowRemarks as workFlowRemarks";
		$select_args[] = "expensetransactions.transactionId as id";
		$select_args[] = "expensetransactions.lookupValueId as lookupValueId";
		$select_args[] = "expensetransactions.branchId as branch";
		$select_args[] = "expensetransactions.filePath as filePath";
		$select_args[] = "expensetransactions.entity as entity";
		$select_args[] = "expensetransactions.entityValue as entityValue";
		$select_args[] = "expensetransactions.vehicleIds as vehicleIds";
		$select_args[] = "expensetransactions.inchargeId as inchargeId";
		$select_args[] = "employee4.fullName as inchargeName";
		$select_args[] = "expensetransactions.chequeNumber as chequeNumber";
		$select_args[] = "expensetransactions.bankAccount as bankAccount";
		$select_args[] = "expensetransactions.createdBy as createdById";
		
		if(isset($values["contracts"]) && $values["contracts"]=="true"){
			$select_args[] = "depots.name as depotname";
		}
		
		$search = $_REQUEST["search"];
		$search = $search['value'];
	
		if(!isset($values["daterange"])&& $search == ""){
			return array("total"=>0, "data"=>array());
		}
		
		$actions = array();
		if(in_array(304, $this->jobs)){
			$action = array("url"=>"#edit", "type"=>"modal", "css"=>"primary", "js"=>"modalEditTransaction(", "jsdata"=>array("id"), "text"=>"EDIT");
			$actions[] = $action;
			$action = array("url"=>"#delete", "type"=>"modal", "css"=>"danger", "js"=>"deleteTransaction(", "jsdata"=>array("id"), "text"=>"DELETE");
			$actions[] = $action;
		}
		$values["actions"] = $actions;
	
		if($search != ""){
			//echo $search." search";
			$entities = \Vehicle::where("veh_reg", "like", "%$search%")
							->get();
			$veh_arr_str = "";
			foreach ($entities as $entity){
				//$lookupvalue_arr[] = $lookupvalue->id;
				$veh_arr_str = $veh_arr_str.$entity->id.",";
			}
			$veh_arr_str = $veh_arr_str."0";
			
			$lookupvalue_arr = array();
			$lookupvalue_arr_str="";
			$lookupvalues = \LookupTypeValues::where("name","like","%$search%")->get();
			foreach($lookupvalues as $lookupvalue){
				$lookupvalue_arr[] = $lookupvalue->id;
				$lookupvalue_arr_str = $lookupvalue_arr_str.$lookupvalue->id.",";
			}
			$lookupvalue_arr_str = $lookupvalue_arr_str."0";
			
			
			if(isset($values["contracttype"]) && $values["contracttype"]=="contracts"){
				$clients =  AppSettingsController::getEmpClients();
				$clients_arr = array();
				//$clients_arr_str = "";
				foreach ($clients as $client){
					$clients_arr[] = $client['id'];
					//$clients_arr_str = $clients_arr_str.$client['id'].",";
				}
				//$clients_arr_str=$clients_arr_str."0";
				$entities = \ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
							->whereRaw('(expensetransactions.vehicleIds in('.$veh_arr_str.') or expensetransactions.lookupValueId in('.$lookupvalue_arr_str.'))')
							->whereIn("contracts.depotId",$clients_arr)
							//->whereIn("lookupValueId", $lookupvalue_arr)
							->where("contractId",">",0)
							//->leftjoin("vehicle", "vehicle.id","=","expensetransactions.vehicleIds")
							->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","expensetransactions.lookupValueId")
							->leftjoin("contracts", "contracts.id","=","expensetransactions.contractId")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")
							->leftjoin("employee as employee2", "employee2.id","=","expensetransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","expensetransactions.updatedBy")
							->leftjoin("employee as employee4", "employee4.id","=","expensetransactions.inchargeId")
							->select($select_args)->limit($length)->offset($start)->get();
				
	$total = \ExpenseTransaction::whereRaw('(expensetransactions.vehicleIds in('.$veh_arr_str.') or expensetransactions.lookupValueId in('.$lookupvalue_arr_str.'))')
							->leftjoin("contracts", "contracts.id","=","expensetransactions.contractId")
							->where("expensetransactions.status","=","ACTIVE")
							//->whereIn("lookupValueId", $lookupvalue_arr)
							->where("contractId",">",0)
							->whereIn("contracts.depotId",$clients_arr)
							->count();
			}
			else
			{
				$branches =  AppSettingsController::getEmpBranches();
				$branches_arr = array();
				foreach ($branches as $branch){
					$branches_arr[] = $branch["id"];
				}
				$entities = \ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
							->whereIn("lookupValueId", $lookupvalue_arr)
							->whereIn("branchId",$branches_arr)
							->leftjoin("officebranch", "officebranch.id","=","expensetransactions.branchId")
							->leftjoin("employee as employee2", "employee2.id","=","expensetransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","expensetransactions.updatedBy")
							->leftjoin("employee as employee4", "employee4.id","=","expensetransactions.inchargeId")
							//->leftjoin("vehicle", "vehicle.id","=","expensetransactions.vehicleId")
							->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","expensetransactions.lookupValueId")
							->select($select_args)->limit($length)->offset($start)->get();
				$total = \ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
							->whereIn("lookupValueId", $lookupvalue_arr)
							->whereIn("branchId",$branches_arr)
							->count();
			}
			foreach ($entities as $entity){
				$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			}
		}
		else if(isset($values["contracts"]) && $values["contracts"]=="true" && isset($values["depot"])){
			$dtrange = $values["daterange"];
			$dtrange = explode(" - ", $dtrange);
			$startdt = date("Y-m-d",strtotime($dtrange[0]));
			$enddt = date("Y-m-d",strtotime($dtrange[1]));
				
			$sql = \ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
							//->where("contractId",">",0)
							->where("depots.id","=",$values["depot"])
							->whereBetween("date",array($startdt,$enddt));
							if(isset($values["expensestype1"]) && $values["expensestype1"]!=0){
								$sql->where("lookupValueId","=",$values["expensestype1"]);
							}
							//$sql->leftjoin("vehicle", "vehicle.id","=","expensetransactions.vehicleIds")
							$sql->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","expensetransactions.lookupValueId")
							->leftjoin("contracts", "contracts.id","=","expensetransactions.contractId")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")
							->leftjoin("employee as employee2", "employee2.id","=","expensetransactions.createdBy")
							->leftjoin("employee as employee3", "employee3.id","=","expensetransactions.updatedBy")
							->leftjoin("employee as employee4", "employee4.id","=","expensetransactions.inchargeId");
			$entities = $sql->select($select_args)->limit($length)->offset($start)->get();
				
			$sql = \ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
							//->where("contractId",">",0)
							->where("depots.id","=",$values["depot"])
							->whereBetween("date",array($startdt,$enddt));
							if(isset($values["expensestype1"]) && $values["expensestype1"]!=0){
								$sql->where("lookupValueId","=",$values["expensestype1"]);
							}
							//$sql->leftjoin("vehicle", "vehicle.id","=","expensetransactions.vehicleIds")
							$sql->leftjoin("contracts", "contracts.id","=","expensetransactions.contractId")
							->leftjoin("depots", "depots.id","=","contracts.depotId");
			$total =  $sql->where("contractId",">",0)->count();
			foreach ($entities as $entity){
				$entity["clientname"] = $entity["depotname"]." (".$entity["clientname"].")";
				$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			}
		}
		else{
			$dtrange = $values["daterange"];
			$dtrange = explode(" - ", $dtrange);
			$startdt = date("Y-m-d",strtotime($dtrange[0]));
			$enddt = date("Y-m-d",strtotime($dtrange[1]));
			$sql = \ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
								->where("branchId","=",$values["branch1"])
								->where("contractId","=",0)
								->whereBetween("date",array($startdt,$enddt));
								if(isset($values["expensestype1"]) && $values["expensestype1"]!=0){
									$sql->where("lookupValueId","=",$values["expensestype1"]);
								}
								$sql->leftjoin("officebranch", "officebranch.id","=","expensetransactions.branchId")
								->leftjoin("employee as employee2", "employee2.id","=","expensetransactions.createdBy")
								->leftjoin("employee as employee3", "employee3.id","=","expensetransactions.updatedBy")
								->leftjoin("employee as employee4", "employee4.id","=","expensetransactions.inchargeId")
								//->leftjoin("vehicle", "vehicle.id","=","expensetransactions.vehicleIds")
								->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","expensetransactions.lookupValueId");
			$entities =  $sql->select($select_args)->limit($length)->offset($start)->get();
			$sql = \ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
								->where("branchId","=",$values["branch1"])
								->where("contractId","=",0);
								if(isset($values["expensestype1"]) && $values["expensestype1"]!=0){
									$sql->where("lookupValueId","=",$values["expensestype1"]);
								}
			$total = $sql->whereBetween("date",array($startdt,$enddt))->count();
			foreach ($entities as $entity){
				$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			}
		}
		
		$vehicles =  \Vehicle::All();
		$veh_arr = array();
		foreach ($vehicles as $vehicle){
			$veh_arr[$vehicle->id] = $vehicle->veh_reg;
		}
	
		$entities = $entities->toArray();
		foreach($entities as $entity){
			if($entity["lookupValueId"]>900 &&$entity["lookupValueId"]<1000){
				$expenses_arr = array();
				$expenses_arr["998"] = "CREDIT SUPPLIER PAYMENT";
				$expenses_arr["997"] = "FUEL STATION PAYMENT";
				$expenses_arr["996"] = "LOAN PAYMENT";
				$expenses_arr["995"] = "RENT";
				$expenses_arr["994"] = "INCHARGE ACCOUNT CREDIT";
				$expenses_arr["993"] = "PREPAID RECHARGE";
				$expenses_arr["992"] = "ONLINE OPERATORS";
				$expenses_arr["991"] = "DAILY FINANCE PAYMENT";
				$expenses_arr["989"] = "VEHICLE RENEWAL";
				$entity["name"] = "";
				$entity["name"] = $expenses_arr[$entity["lookupValueId"]];
			}
			
			if($entity["lookupValueId"]==999){
				if($entity["entityValue"]>0){
					$prepaidName = \LookupTypeValues::where("id","=",$entity["entityValue"])->first();
					$prepaidName = $prepaidName->name;
					$entity["name"] = $entity["name"]." - ".strtoupper($entity["entity"]);
				}
			}
			else if($entity["lookupValueId"]==124){
				if($entity["entityValue"]>0){
					$creditsupplier = \CreditSupplier::where("id","=",$entity["entityValue"])->first();
					$creditsupplier = $creditsupplier->supplierName;
					$entity["name"] = $entity["name"]." - ".$creditsupplier;
				}
			}
			else if($entity["lookupValueId"]==134){
				if($entity["entityValue"]>0){
					$fuelstation = \FuelStation::where("id","=",$entity["entityValue"])->first();
					$fuelstation = $fuelstation->name;
					$entity["name"] = $entity["name"]." - ".$fuelstation;
				}
			}
			else if($entity["lookupValueId"]==996 || $entity["lookupValueId"]==147){
				if($entity["entityValue"]>0){
					$loan = \Loan::where("id","=",$entity["entityValue"])->first();
					$dfid = $loan->financeCompanyId;
					$finanacecompany = \FinanceCompany::where("id","=",$dfid)->first();
					$finanacecompany = $finanacecompany->name;
					$entity["name"] = $entity["name"]." - ".$loan->loanNo." (".$finanacecompany.")";
				}
			}
			else if($entity["lookupValueId"]==121){
				if($entity["entityValue"]>0){
					$dfid = \DailyFinance::where("id","=",$entity["entityValue"])->first();
					$dfid = $dfid->financeCompanyId;
					$finanacecompany = \FinanceCompany::where("id","=",$dfid)->first();
					$finanacecompany = $finanacecompany->name;
					$entity["name"] = $entity["name"]." - ".$finanacecompany;
				}
			}
			else if($entity["lookupValueId"]==283){
				if($entity["entityValue"]>0){
					$card = \Cards::where("id","=",$entity["entityValue"])->first();
					$lookupvalue = $card->cardNumber." (".$card->cardHolderName.")";
					$entity["name"] = $entity["name"]." - ".$lookupvalue;
				}
			}
				
			else if($entity["lookupValueId"]==84){
				$bankdetails = \ExpenseTransaction::where("transactionId","=",$entity["id"])->leftjoin("bankdetails","bankdetails.id","=","expensetransactions.bankId")->first();
				$bankdetails = $bankdetails->bankName." - ".$bankdetails->accountNo;
				$entity["name"] = $entity["name"]." - ".$bankdetails;
			}
			else if($entity["lookupValueId"]==63){
				$lookupvalue = \LookupTypeValues::where("id","=",$entity["lookupValueId"])->first();
				$lookupvalue = $lookupvalue->name;
				$row["employee"] = "";
			}
			if($entity["vehicleIds"] != ""){
				$vehicleIds = explode(",", $entity["vehicleIds"]);
				$vehicleIds_str  = "";
				foreach ($vehicleIds as $vehicleId){
					if(isset($veh_arr[$vehicleId])){
						$vehicleIds_str = $vehicleIds_str.",".$veh_arr[$vehicleId];
					}
				}
				$vehicleIds_str = substr($vehicleIds_str, 1);
				$entity["name"] = $entity["name"]." (".$vehicleIds_str.")";
			}
						
			if($entity["billNo"] != ""){				
				if($entity["filePath"]==""){
					$entity["billNo"] = "<span style='color:red; font-weight:bold;'>".$entity["billNo"]."</span>";
				}
				else{
					$entity["billNo"] = "<a href='../app/storage/uploads/".$entity["filePath"]."' target='_blank'>".$entity["billNo"]."</a>";
				}
			}
			if($entity["inchargeId"]>0){
				$entity["name"] = $entity["name"]." (Incharge : ".$entity["inchargeName"].")";
			}
			//ecs neft rtgs
			if($entity["paymentType"] != "cash"){
				if($entity["paymentType"] == "ecs" || $entity["paymentType"] == "neft" || $entity["paymentType"] == "rtgs" || $entity["paymentType"] == "cheque_debit" || $entity["paymentType"] == "cheque_credit"){
					$entity["paymentType"] = "Payment Type : ".$entity["paymentType"]."<br/>";
					$bank_dt = \BankDetails::where("id","=",$entity["bankAccount"])->first();
					if(count($bank_dt)>0){
						$entity["paymentType"] = $entity["paymentType"]."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
					}
					$entity["paymentType"] = $entity["paymentType"]."Ref No : ".$entity["chequeNumber"];
				}
				if($entity["paymentType"] == "credit_card" || $entity["paymentType"] == "debit_card" ||$entity["paymentType"] == "hp_card"){
					$entity["paymentType"] = "Payment Type : ".$entity["paymentType"]."<br/>";
					$bank_dt = \Cards::where("id","=",$entity["bankAccount"])->first();
					if(count($bank_dt)>0){
						$entity["paymentType"] = $entity["paymentType"]."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
					}
					$entity["paymentType"] = $entity["paymentType"]."Ref No : ".$entity["chequeNumber"];
				}
				if($entity["paymentType"] == "dd"){
					$entity["paymentType"] = "Payment Type : ".$entity["paymentType"]."<br/>";
					$entity["paymentType"] = $entity["paymentType"]."Ref No : ".$entity["chequeNumber"];
				}
			}
			$data_values = array_values($entity);
			$actions = $values['actions'];
			$action_data = "";
			$bde = new BlockDataEntryController();
			$values1 = array("branch"=>$entity["branch"],"date"=>$entity["date"]);
			$valid = $bde->verifyTransactionDateandBranchLocally($values1);
			foreach($actions as $action){
				if($action["type"] == "modal"){
					$jsfields = $action["jsdata"];
					$jsdata = "";
					$i=0;
					for($i=0; $i<(count($jsfields)-1); $i++){
						$jsdata = $jsdata." '".$entity[$jsfields[$i]]."', ";
					}
					$jsdata = $jsdata." '".$entity[$jsfields[$i]];
					
					if($valid=="YES"){
						$action_data = $action_data. "<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."' data-toggle='modal' onClick=\"".$action['js'].$jsdata."')\">".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
					}
				}
				else {
					if($valid=="YES"){
						$action_data = $action_data."<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."&id=".$entity['id']."'>".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
					}
				}
			}
			$login_user = \Auth::user()->fullName;
			$assignedemps = \Auth::user()->assignedEmpIds;
			//print_r($assignedemps);die();
			$assignedemps_arr = explode(",", $assignedemps);
			if(isset($entity["createdById"]) && in_array($entity["createdById"], $assignedemps_arr)){
				//$action_data = "";
			}
			else if(isset($entity["createdBy"]) && $entity["createdBy"]!=$login_user){
				$action_data = "";
			}			
			if(isset($entity["workFlowStatus"]) && $entity["workFlowStatus"]=="Approved"){
				$action_data = "";
			}
			$data_values[12] = $action_data;
			$data[] = $data_values;
		}
		return array("total"=>$total, "data"=>$data);
	}
	
	private function getRepairTransactionItems($values, $length, $start){
		$total = 0;
		$data = array();
		$select_args = array();
		$select_args[] = "lookuptypevalues.name as repairedItem";
		$select_args[] = "creditsuppliertransdetails.quantity as quantity";
		$select_args[] = "creditsuppliertransdetails.amount as amount";
		$select_args[] = "creditsuppliertransdetails.comments as comments";
		$select_args[] = "creditsuppliertransdetails.status as status";
		$select_args[] = "creditsuppliertransdetails.id as id";
		$select_args[] = "creditsuppliertransdetails.createdBy as createdById";
		$select_args[] = "employee2.fullName as createdBy";
		
		
		
		$actions = array();
		$action = array("url"=>"#edit", "type"=>"modal", "css"=>"primary", "js"=>"modalEditPurchaseOrderItem(", "jsdata"=>array("id","repairedItem","quantity", "amount", "comments", "status"), "text"=>"EDIT");
		$actions[] = $action;
		$values["actions"] = $actions;
	
		$search = $_REQUEST["search"];
		$search = $search['value'];
		if($search != ""){
			$entities = \PurchasedOrders::where("name", "like", "%$search%")->join("inventorylookupvalues","inventorylookupvalues.id","=","items.unitsOfMeasure")->join("item_types","item_types.id","=","items.itemTypeId")->select($select_args)->limit($length)->offset($start)->get();
			$total = count($entities);
		}
		else{
			$entities = \CreditSupplierTransDetails::where("creditSupplierTransId","=",$values["id"])
							->join("lookuptypevalues","lookuptypevalues.id","=","creditsuppliertransdetails.repairedItem")
							->leftjoin("employee as employee2", "employee2.id","=","creditsuppliertransdetails.createdBy")
							->select($select_args)->limit($length)->offset($start)->get();
			$total = \CreditSupplierTransDetails::where("creditSupplierTransId","=",$values["id"])->count();
		}
	
		$entities = $entities->toArray();
		foreach($entities as $entity){
			$data_values = array_values($entity);
			$actions = $values['actions'];
			$action_data = "";
			foreach($actions as $action){
				if($action["type"] == "modal"){
					$jsfields = $action["jsdata"];
					$jsdata = "";
					$i=0;
					for($i=0; $i<(count($jsfields)-1); $i++){
						$jsdata = $jsdata." '".$entity[$jsfields[$i]]."', ";
					}
					$jsdata = $jsdata." '".$entity[$jsfields[$i]];
					$action_data = $action_data. "<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."' data-toggle='modal' onClick=\"".$action['js'].$jsdata."')\">".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
				}
				else {
					$action_data = $action_data."<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."&id=".$entity['id']."'>".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
				}
			}
			$login_user = \Auth::user()->fullName;
			$assignedemps = \Auth::user()->assignedEmpIds;
			$assignedemps_arr = explode(",", $assignedemps);
			if(isset($entity["createdById"]) && in_array($entity["createdById"], $assignedemps_arr)){
				//$action_data = "";
			}
			else if(isset($entity["createdBy"]) && $entity["createdBy"]!=$login_user){
				$action_data = "";
			}			
			if(isset($entity["workFlowStatus"]) && $entity["workFlowStatus"]=="Approved"){
				$action_data = "";
			}
			$data_values[5] = $action_data;
			$data[] = $data_values;
		}
		return array("total"=>$total, "data"=>$data);
	}
}


