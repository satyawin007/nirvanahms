<?php namespace workflow;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use masters\BlockDataEntryController;
use settings\AppSettingsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
		else if(isset($values["name"]) && $values["name"]=="fueltransactions") {
			$ret_arr = $this->getFuelTransactions($values, $length, $start);
			$total = $ret_arr["total"];
			$data = $ret_arr["data"];
		}
		else if(isset($values["name"]) && $values["name"]=="expensetransactions") {
			$ret_arr = $this->getExpenseTransactions($values, $length, $start);
			$total = $ret_arr["total"];
			$data = $ret_arr["data"];
		}
		else if(isset($values["name"]) && $values["name"]=="vehicle_repairs") {
			$ret_arr = $this->getVehicleRepairs($values, $length, $start);
			$total = $ret_arr["total"];
			$data = $ret_arr["data"];
		}
		else if(isset($values["name"]) && $values["name"]=="inchargetransactions") {
			$ret_arr = $this->getInchargeTransactions($values, $length, $start);
			$total = $ret_arr["total"];
			$data = $ret_arr["data"];
		}
		else if(isset($values["name"]) && $values["name"]=="purchaseorders") {
			$ret_arr = $this->getPurchaseOrders($values, $length, $start);
			$total = $ret_arr["total"];
			$data = $ret_arr["data"];
		}
		else if(isset($values["name"]) && $values["name"]=="employeeleaves") {
			$ret_arr = $this->getEmployeeLeaves($values, $length, $start);
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
		$select_args[] = "officebranch.name as branchId";
		$select_args[] = "lookuptypevalues.name as name";
		$select_args[] = "incometransactions.date as date";
		$select_args[] = "incometransactions.amount as amount";
		$select_args[] = "incometransactions.paymentType as paymentType";
		$select_args[] = "incometransactions.remarks as remarks";
		$select_args[] = "incometransactions.transactionId as id";
		$select_args[] = "incometransactions.lookupValueId as lookupValueId";
		$select_args[] = "incometransactions.branchId as branch";
		$select_args[] = "employee2.fullName as createdBy";
		$select_args[] = "incometransactions.createdBy as createdById";
		
		
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
								->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","incometransactions.lookupValueId")
								->select($select_args)->limit($length)->offset($start)->get();
			$total = \IncomeTransaction::where("incometransactions.status","=","ACTIVE")->where("transactionId", "like", "%$search%")->count();
			foreach ($entities as $entity){
				$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			}
		}
		else{
			$dtrange = $values["daterange"];
			$dtrange = explode(" - ", $dtrange);
			$startdt = date("Y-m-d",strtotime($dtrange[0]));
			$enddt = date("Y-m-d",strtotime($dtrange[1]));
			$logstatus_arr = array();
			if($values["logstatus"] != "All"){
				//$qry->where("purchase_orders.workFlowStatus","=",$values["logstatus"]);
				$logstatus_arr [] =  $values["logstatus"];
			}
			else{
				$logstatus_arr = array("Requested","Sent for Approval","Approved","Rejected","Hold");
			}
			$entities = \IncomeTransaction::where("incometransactions.status","=","ACTIVE")
								->where("branchId","=",$values["branch1"])
								>where("incometransactions.workFlowStatus",$logstatus_arr)
								->whereBetween("date",array($startdt,$enddt))
								->leftjoin("officebranch", "officebranch.id","=","incometransactions.branchId")
								->leftjoin("employee as employee2", "employee2.id","=","incometransactions.createdBy")
								->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","incometransactions.lookupValueId")
								->select($select_args)->limit($length)->offset($start)->get();
			$total = \IncomeTransaction::where("incometransactions.status","=","ACTIVE")
								->where("branchId","=",$values["branch1"])
								->whereBetween("date",array($startdt,$enddt))->count();
			foreach ($entities as $entity){
				$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			}
		}
	
		$entities = $entities->toArray();
		foreach($entities as $entity){
			if($entity["lookupValueId"]>900){
				$expenses_arr = array();
				$expenses_arr["998"] = "CREDIT SUPPLIER PAYMENT";
				$expenses_arr["997"] = "FUEL STATION PAYMENT";
				$expenses_arr["996"] = "LOAN PAYMENT";
				$expenses_arr["995"] = "RENT";
				$expenses_arr["994"] = "INCHARGE ACCOUNT CREDIT";
				$expenses_arr["993"] = "PREPAID RECHARGE";
				$expenses_arr["992"] = "ONLINE OPERATORS";
				$expenses_arr["999"] = "PREPAID RECHARGE";
				$entity["name"] = $expenses_arr[$entity["lookupValueId"]];
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
			$data_values[7] = $action_data;
			$data[] = $data_values;
		}
		return array("total"=>$total, "data"=>$data);
	}
	
	private function getEmployeeLeaves($values, $length, $start){
		$dtrange = $values["daterange"];
		$dtrange = explode(" - ", $dtrange);
		$frmDt = date("Y-m-d",strtotime($dtrange[0]));
		$toDt = date("Y-m-d",strtotime($dtrange[1]));
		$total = 0;
		$data = array();
		$select_args = array();
		$select_args[] = "employee.fullName as empId";		
		$select_args[] = "officebranch.name as branchId";
		$select_args[] = "leaves.fromDate as fromDate";
		$select_args[] = "leaves.fromMrngEve as fromMrngEve";
		$select_args[] = "leaves.toDate as toDate";
		$select_args[] = "leaves.toMrngEve as toMrngEve";
		$select_args[] = "leaves.noOfLeaves as noOfLeaves";
		$select_args[] = "leaves.leavesTaken as leavesTaken";
		$select_args[] = "leaves.remarks as remarks";
		$select_args[] = "leaves.rejectReason as rejectReason";
		$select_args[] = "employee1.fullName as createdBy";
		$select_args[] = "leaves.workFlowStatus as workFlowStatus1";
		$select_args[] = "leaves.workFlowRemarks as workFlowRemarks";
		$select_args[] = "leaves.workFlowStatus as workFlowStatus";		
		$select_args[] = "employee.empCode as empcode";
		$select_args[] = "leaves.id as id";
		$select_args[] = "leaves.createdBy as createdById";
		
		
	
		$actions = array();
		$values["actions"] = $actions;
	
		$search = $_REQUEST["search"];
		$search = $search['value'];
		$entities = \Vehicle::where("id","=",0)->get();
		
		$assingedBranches = AppSettingsController::getEmpBranches();
		$emp_branches_str = "";
		foreach ($assingedBranches as $assingedBranch){
			$emp_branches_str = $emp_branches_str.$assingedBranch["id"].",";
		}
		$emp_branches_str = substr($emp_branches_str, 0, strlen($emp_branches_str)-1);
		$emp_contracts = \Auth::user()->contractIds;
		$emp_contracts_str = "";
		if($emp_contracts=="" || $emp_contracts==0){
			$clients = \Contract::All();
			foreach ($clients as $client){
				$emp_contracts_str = $emp_contracts_str.$client->id.",";
			}
		}
		else{
			$emp_contracts = explode(",", $emp_contracts);
			$depots = \Depot::whereIn("depots.id",$emp_contracts)
							->join("contracts", "depots.id", "=","contracts.depotId")
							->select(array("contracts.id as id"))->get();
			foreach ($depots as $depot){
				$emp_contracts_str = $emp_contracts_str.$depot->id.",";
			}
		}
		$emp_contracts_str = substr($emp_contracts_str, 0, strlen($emp_contracts_str)-1);
		
		$createdby_arr = array();
		if(in_array(507, $this->jobs)){
			$emps = \Employee::All();
			$createdby_arr[] = 0;
			foreach ($emps as $emp){
				$createdby_arr[] = $emp->id;
			}
		}
		else{
			$createdby_arr[] = Auth::user()->id;
			$assignedemps = \Auth::user()->assignedEmpIds;
			//print_r($assignedemps);die();
			$assignedemps_arr = explode(",", $assignedemps);
			foreach ($assignedemps_arr as $assignedemp){
				$createdby_arr[] = $assignedemp;
			}
		}
		$entities = \Employee::where("fullName", "like", "%$search%")->get();
		$emp_arr_str= "-1,";
		foreach ($entities as $entity){
			$emp_arr_str= $emp_arr_str. $entity->id.",";
		}
		$emp_arr_str = substr($emp_arr_str, 0, strlen($emp_arr_str)-1);
			
		if($search != ""){
			$qry = \Leaves::whereRaw('(leaves.empId in('.$emp_arr_str.'))');
			if($values["logstatus"] != "All"){
				$qry->where("leaves.workFlowStatus","=",$values["logstatus"])
				->whereBetween("leaves.fromDate",array($frmDt,$toDt));
			}
			$qry->leftjoin("officebranch", "officebranch.id","=","leaves.branchId")
				->leftjoin("employee as employee", "employee.id","=","leaves.empId")
				->leftjoin("employee as employee1", "employee1.id","=","leaves.createdBy");
			$entities = $qry->select($select_args)->limit($length)->offset($start)->get();
			
			$qry = \Leaves::whereRaw('(leaves.empId in('.$emp_arr_str.'))');
						if($values["logstatus"] != "All"){
							$qry->where("leaves.workFlowStatus","=",$values["logstatus"])
							->whereBetween("leaves.fromDate",array($frmDt,$toDt));
						}	
						$qry->leftjoin("officebranch", "officebranch.id","=","leaves.branchId")
							->leftjoin("employee as employee", "employee.id","=","leaves.empId")
							->leftjoin("employee as employee1", "employee1.id","=","leaves.createdBy");
						$total = $qry->count();
		}
		else {
			$qry = \Leaves::whereRaw('(leaves.branchId in('.$emp_branches_str.')) ')->whereIn("leaves.createdBy",$createdby_arr);
						if($values["logstatus"] != "All"){
							$qry->where("leaves.workFlowStatus","=",$values["logstatus"])
							->whereBetween("leaves.fromDate",array($frmDt,$toDt));
						}
					$qry->leftjoin("officebranch", "officebranch.id","=","leaves.branchId")
						->leftjoin("employee as employee", "employee.id","=","leaves.empId")
						->leftjoin("employee as employee1", "employee1.id","=","leaves.createdBy");
			$entities = $qry->select($select_args)->limit($length)->offset($start)->get();
			
			$qry = \Leaves::whereRaw('(leaves.branchId in('.$emp_branches_str.')) ')->whereIn("leaves.createdBy",$createdby_arr);
						if($values["logstatus"] != "All"){
							$qry->where("leaves.workFlowStatus","=",$values["logstatus"])
							->whereBetween("leaves.fromDate",array($frmDt,$toDt));
						}
						$qry->leftjoin("officebranch", "officebranch.id","=","leaves.branchId")
							->leftjoin("employee as employee", "employee.id","=","leaves.empId")
							->leftjoin("employee as employee1", "employee1.id","=","leaves.createdBy");
			$total = $qry->count();
		}
		$entities = $entities->toArray();
		$i = 0;
		foreach($entities as $entity){
			$entity["fromDate"] = date("d-m-Y",strtotime($entity["fromDate"]));
			$entity["toDate"] = date("d-m-Y",strtotime($entity["toDate"]));
			if($entity["workFlowStatus"] == "Sent for Approval"){
				$entity["workFlowRemarks"] = '<label> <input name="remarks[]" type="text" class=""></label>';
			}
			else{
				$entity["workFlowRemarks"] = $entity["workFlowRemarks"].'<input name="remarks[]" type="hidden" class="">';
			}
			if($entity["workFlowStatus1"] == "Requested"){
				$entity["workFlowStatus1"] = 'pending for approval';
			}
			$data_values = array_values($entity);
			$action_data = "";
			if($entity["workFlowStatus"] != "Approved"){
			$login_user = \Auth::user()->fullName;
				if((isset($entity["createdBy"]) && in_array($entity["createdById"],$createdby_arr)) || (isset($entity["createdBy"]) && in_array(507, $this->jobs))){
					$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="checkbox" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
				}
				else{
					$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
				}
			}
			else{
				$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
			}
			$data_values[13] = $action_data;
			$data[] = $data_values;
			$i++;
		}
		return array("total"=>$total, "data"=>$data);
	}
	
	private function getVehicleRepairs($values, $length, $start){
		$dtrange = $values["daterange"];
		$dtrange = explode(" - ", $dtrange);
		$frmDt = date("Y-m-d",strtotime($dtrange[0]));
		$toDt = date("Y-m-d",strtotime($dtrange[1]));
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
		$select_args[] = "creditsuppliertransactions.billNumber as billNo";
		$select_args[] = "employee4.fullName as inchargeId ";
		$select_args[] = "creditsuppliertransactions.paymentPaid as paymentPaid";
		$select_args[] = "creditsuppliertransactions.paymentType as paymentType";
		$select_args[] = "creditsuppliertransactions.amount as amount";
		$select_args[] = "creditsuppliertransactions.comments as comments";
		$select_args[] = "creditsuppliertransdetails.vehicleIds as vehicleIds";
		$select_args[] = "employee2.fullName as createdBy";
		$select_args[] = "creditsuppliertransactions.workFlowStatus as workFlowStatus";
		$select_args[] = "creditsuppliertransactions.workFlowRemarks as workFlowRemarks";
		$select_args[] = "creditsuppliertransactions.status as status";
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
		
		$assingedBranches = AppSettingsController::getEmpBranches();
		$emp_branches_str = "";
		foreach ($assingedBranches as $assingedBranch){
			$emp_branches_str = $emp_branches_str.$assingedBranch["id"].",";
		}
		$emp_branches_str = substr($emp_branches_str, 0, strlen($emp_branches_str)-1);
		$emp_contracts = \Auth::user()->contractIds;
		$emp_contracts_str = "";
		if($emp_contracts=="" || $emp_contracts==0){
			$clients = \Contract::All();
			foreach ($clients as $client){
				$emp_contracts_str = $emp_contracts_str.$client->id.",";
			}
		}
		else{
			$emp_contracts = explode(",", $emp_contracts);
			$depots = \Depot::whereIn("depots.id",$emp_contracts)
							->join("contracts", "depots.id", "=","contracts.depotId")
							->select(array("contracts.id as id"))->get();
			foreach ($depots as $depot){
				$emp_contracts_str = $emp_contracts_str.$depot->id.",";
			}
		}
		$emp_contracts_str = substr($emp_contracts_str, 0, strlen($emp_contracts_str-1));
	
		$search = $_REQUEST["search"];
		$search = $search['value'];
		
		$createdby_arr = array();
		if(in_array(507, $this->jobs)){
			$emps = \Employee::All();
			$createdby_arr[] = 0;
			foreach ($emps as $emp){
				$createdby_arr[] = $emp->id;
			}
		}
		else{
			$createdby_arr[] = Auth::user()->id;
			$assignedemps = \Auth::user()->assignedEmpIds;
			$assignedemps_arr = explode(",", $assignedemps);
			foreach ($assignedemps_arr as $assignedemp){
				$createdby_arr[] = $assignedemp;
			}
		}
		
		if($search != ""){
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
			$qry = \CreditSupplierTransactions::where("creditsuppliertransactions.deleted","=","No");
						if($values["logstatus"] != "All"){
							$qry->where("creditsuppliertransactions.workFlowStatus","=",$values["logstatus"]);
						}
						//print_r($qry);
						
						$qry->whereRaw('(creditsuppliertransactions.branchId in('.$branchids_arr_str.') or creditsuppliertransactions.creditSupplierId in('.$supids_arr_str.') or creditsuppliertransactions.billNumber in('.$bills_arr_str.')) ')
							->where("creditsuppliertransactions.deleted","=","No")
							->whereIn("creditsuppliertransactions.createdBy",$createdby_arr)
							//->whereRaw('(creditsuppliertransactions.branchId in('.$emp_branches_str.') or creditsuppliertransactions.contractId in('.$emp_contracts_str.'))')
							->whereBetween("creditsuppliertransactions.date",array($frmDt,$toDt))
							->leftjoin("vehicle", "vehicle.id","=","creditsuppliertransactions.vehicleId")
							->leftjoin("employee as employee2", "employee2.id","=","creditsuppliertransactions.createdBy")
							->leftjoin("employee as employee4", "employee4.id","=","creditsuppliertransactions.inchargeId")
							->leftjoin("officebranch", "officebranch.id","=","creditsuppliertransactions.branchId")
							->leftjoin("creditsuppliers", "creditsuppliers.id","=","creditsuppliertransactions.creditSupplierId")
							->leftjoin("creditsuppliertransdetails", "creditsuppliertransdetails.creditSupplierTransId","=","creditsuppliertransactions.id");
			$entities=$qry->select($select_args)->limit($length)->groupBy("creditsuppliertransactions.id")->offset($start)->get();
			$qry = \CreditSupplierTransactions::where("creditsuppliertransactions.deleted","=","No");
			if($values["logstatus"] != "All"){
				$qry->where("creditsuppliertransactions.workFlowStatus","=",$values["logstatus"]);
			}
			$total_recs=$qry->whereRaw('(creditsuppliertransactions.branchId in('.$branchids_arr_str.') or creditsuppliertransactions.creditSupplierId in('.$supids_arr_str.') or creditsuppliertransactions.billNumber in('.$bills_arr_str.')) ')
							->where("creditsuppliertransactions.deleted","=","No")
							->whereIn("creditsuppliertransactions.createdBy",$createdby_arr)
							//->whereRaw('(creditsuppliertransactions.branchId in('.$emp_branches_str.') or creditsuppliertransactions.contractId in('.$emp_contracts_str.'))')
							->whereBetween("creditsuppliertransactions.date",array($frmDt,$toDt))
							->leftjoin("vehicle", "vehicle.id","=","creditsuppliertransactions.vehicleId")
							->leftjoin("employee as employee2", "employee2.id","=","creditsuppliertransactions.createdBy")
							->leftjoin("employee as employee4", "employee4.id","=","creditsuppliertransactions.inchargeId")
							->leftjoin("officebranch", "officebranch.id","=","creditsuppliertransactions.branchId")
							->leftjoin("creditsuppliers", "creditsuppliers.id","=","creditsuppliertransactions.creditSupplierId")
							->leftjoin("creditsuppliertransdetails", "creditsuppliertransdetails.creditSupplierTransId","=","creditsuppliertransactions.id")->groupBy("creditsuppliertransactions.id")->get();
				//->whereRaw('(creditsuppliertransactions.branchId in('.$emp_branches_str.') or creditsuppliertransactions.contractId in('.$emp_contracts_str.'))');
				$total = count($total_recs);
		}
		else {
			$qry = \CreditSupplierTransactions::where("creditsuppliertransactions.deleted","=","No")->whereIn("creditsuppliertransactions.createdBy",$createdby_arr);
							if($values["logstatus"] != "All"){
								$qry->where("creditsuppliertransactions.workFlowStatus","=",$values["logstatus"]);
							}
							$qry->where("creditsuppliertransdetails.status","=","ACTIVE")
							->whereRaw('(creditsuppliertransactions.branchId in('.$emp_branches_str.') or creditsuppliertransactions.contractId in('.$emp_contracts_str.'))')
							->whereBetween("creditsuppliertransactions.date",array($frmDt,$toDt))
							->leftjoin("creditsuppliertransdetails", "creditsuppliertransdetails.creditSupplierTransId","=","creditsuppliertransactions.id")
							->leftjoin("creditsuppliers", "creditsuppliers.id","=","creditsuppliertransactions.creditSupplierId")
							->leftjoin("officebranch", "officebranch.id","=","creditsuppliertransactions.branchId")
							->leftjoin("contracts", "contracts.id","=","creditsuppliertransactions.contractId")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("employee as employee2", "employee2.id","=","creditsuppliertransactions.createdBy")
							->leftjoin("employee as employee4", "employee4.id","=","creditsuppliertransactions.inchargeId")
							->leftjoin("depots", "depots.id","=","contracts.depotId");							
			$entities =    $qry->select($select_args)->limit($length)->groupBy("creditsuppliertransactions.id")->offset($start)->get();
			
			$qry =  \CreditSupplierTransactions::where("creditsuppliertransactions.deleted","=","No")->whereIn("creditsuppliertransactions.createdBy",$createdby_arr);
							if($values["logstatus"] != "All"){
								$qry->where("creditsuppliertransactions.workFlowStatus","=",$values["logstatus"]);
							}
			$total_recs = 	$qry->where("creditsuppliertransdetails.status","=","ACTIVE")
							->whereRaw('(creditsuppliertransactions.branchId in('.$emp_branches_str.') or creditsuppliertransactions.contractId in('.$emp_contracts_str.'))')
							->whereBetween("creditsuppliertransactions.date",array($frmDt,$toDt))
							->leftjoin("creditsuppliertransdetails", "creditsuppliertransdetails.creditSupplierTransId","=","creditsuppliertransactions.id")
							->leftjoin("creditsuppliers", "creditsuppliers.id","=","creditsuppliertransactions.creditSupplierId")
							->leftjoin("officebranch", "officebranch.id","=","creditsuppliertransactions.branchId")
							->leftjoin("contracts", "contracts.id","=","creditsuppliertransactions.contractId")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("employee as employee2", "employee2.id","=","creditsuppliertransactions.createdBy")
							->leftjoin("employee as employee4", "employee4.id","=","creditsuppliertransactions.inchargeId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")->groupBy("creditsuppliertransactions.id")->get();
			//print_r($total_recs);die();
			$total = count($total_recs);
		}
		$entities = $entities->toArray();
		$vehs_arr = array();
		$vehicles = \Vehicle::All();
		foreach ($vehicles  as $vehicle){
			$vehs_arr[$vehicle->id] = $vehicle->veh_reg;
		}
		$i=0;
		foreach($entities as $entity){
			if($entity["billNo"] != ""){
				if($entity["filePath"]==""){
					$entity["billNo"] = "<span style='color:red; font-weight:bold;'>".$entity["billNo"]."</span>";
				}
				else{
					$entity["billNo"] = "<a href='../app/storage/uploads/".$entity["filePath"]."' target='_blank'>".$entity["billNo"]."</a>";
				}
			}
			$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			$trans_items = \CreditSupplierTransDetails::where("creditSupplierTransId","=",$entity["id"])
								->where("creditsuppliertransdetails.status","=","ACTIVE")
								->leftjoin("lookuptypevalues","lookuptypevalues.id","=","creditsuppliertransdetails.repairedItem")				
								->select(array("creditsuppliertransdetails.vehicleIds as vehicleIds", "lookuptypevalues.name as itemname"))->get();
			
			$entity["vehicleIds"] = "";
			foreach($trans_items as $trans_item){
				$vehs_arr_str = "";
				$veh_ids_arr = explode(",", $trans_item->vehicleIds);
				foreach ($veh_ids_arr  as $veh_id){
					if($veh_id != ""){
						$vehs_arr_str = $vehs_arr_str.$vehs_arr[$veh_id].",";
					}
				}
				$entity["vehicleIds"] = $entity["vehicleIds"]."<span style='color:red;' >VEHICLES : ".$vehs_arr_str."</span><br/>";
				$entity["vehicleIds"] = $entity["vehicleIds"]."<span style='color:green;' >REPAIRED ITEM : ".$trans_item->itemname."</span><br/>";
			}
			$entity["vehicleIds"] = $entity["vehicleIds"]."Labour Charges : ".$entity["labourCharges"]."<br/>";
			$entity["vehicleIds"] = $entity["vehicleIds"]."Electricial Charges : ".$entity["electricianCharges"]."<br/>";
			$entity["vehicleIds"] = $entity["vehicleIds"]."Batta : ".$entity["batta"]."<br/>";
			
			if($entity["workFlowStatus"] == "Sent for Approval"){
				$entity["workFlowRemarks"] = '<label> <input name="remarks[]" type="text" class=""></label>';
			}
			else{
				$entity["workFlowRemarks"] = $entity["workFlowRemarks"].'<input name="remarks[]" type="hidden" class="">';
			}
			if($entity["workFlowStatus"] == "Requested"){
				$entity["workFlowStatus"] = 'pending for approval';
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
			$action_data = "";
			if($entity["workFlowStatus"] != "Approved"){
				$login_user = \Auth::user()->fullName;
				if((isset($entity["createdBy"]) && in_array($entity["createdById"],$createdby_arr)) || (isset($entity["createdBy"]) && in_array(507, $this->jobs))){
					$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="checkbox" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
				}
				else{
					$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
				}
			}
			else{
				$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
			}
			$data_values[13] = $action_data;
			$data[] = $data_values;
			$i++;
		}
		return array("total"=>$total, "data"=>$data);
	}
	
private function getPurchaseOrders($values, $length, $start){
		$dtrange = $values["daterange"];
		$dtrange = explode(" - ", $dtrange);
		$frmDt = date("Y-m-d",strtotime($dtrange[0]));
		$toDt = date("Y-m-d",strtotime($dtrange[1]));
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
		$select_args[] = "purchase_orders.orderDate as date";
		$select_args[] = "purchase_orders.billNumber as billNo";
		$select_args[] = "purchase_orders.amountPaid as paymentPaid";
		$select_args[] = "purchase_orders.paymentType as paymentType";
		$select_args[] = "employee3.fullName as incharge";
		$select_args[] = "purchase_orders.totalAmount as amount";
		$select_args[] = "purchase_orders.comments as comments";
		$select_args[] = "purchase_orders.comments as items";
		$select_args[] = "employee2.fullName as createdBy";
		$select_args[] = "purchase_orders.workFlowStatus as workFlowStatus";
		$select_args[] = "purchase_orders.workFlowRemarks as workFlowRemarks";
		$select_args[] = "purchase_orders.id as id";
		$select_args[] = "purchase_orders.status as status";
		$select_args[] = "purchase_orders.officeBranchId as branch";
		$select_args[] = "purchase_orders.filePath as filePath";
		$select_args[] = "purchase_orders.createdBy as createdById";
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
		$search = $_REQUEST["search"];
		$search = $search['value'];
	
		$assingedBranches = AppSettingsController::getEmpBranches();
		$emp_branches_str = "";
		foreach ($assingedBranches as $assingedBranch){
			$emp_branches_str = $emp_branches_str.$assingedBranch["id"].",";
		}
		$emp_branches_str = substr($emp_branches_str, 0, strlen($emp_branches_str)-1);
		
		$emp_contracts = \Auth::user()->contractIds;
		$emp_contracts_str = "";
		if($emp_contracts=="" || $emp_contracts==0){
			$clients = \Contract::All();
			foreach ($clients as $client){
				$emp_contracts_str = $emp_contracts_str.$client->id.",";
			}
		}
		else{
			$emp_contracts = explode(",", $emp_contracts);
			$depots = \Depot::whereIn("depots.id",$emp_contracts)
								->join("contracts", "depots.id", "=","contracts.depotId")
								->select(array("contracts.id as id"))->get();
			foreach ($depots as $depot){
				$emp_contracts_str = $emp_contracts_str.$depot->id.",";
			}
		}
		$emp_contracts_str = substr($emp_contracts_str, 0, strlen($emp_contracts_str-1));
	
		
		$createdby_arr = array();		
		if(in_array(507, $this->jobs)){
			$emps = \Employee::All();
			$createdby_arr[] = 0;
			foreach ($emps as $emp){
				$createdby_arr[] = $emp->id;
			}
		}
		else{
			$createdby_arr[] = Auth::user()->id;
			$assignedemps = \Auth::user()->assignedEmpIds;
			//print_r($assignedemps);die();
			$assignedemps_arr = explode(",", $assignedemps);
			foreach ($assignedemps_arr as $assignedemp){
				$createdby_arr[] = $assignedemp;
			}
		}
		$supids_arr = array();
		$suppliers = \CreditSupplier::where("supplierName","like","%$search%")->get();
		foreach ($suppliers as $supplier){
			$supids_arr[] = $supplier->id;
		}
		$supids_arr_str = "";
		foreach ($suppliers as $supplier){
			$supids_arr_str = $supids_arr_str.$supplier["id"].",";
		}
		$supids_arr_str = substr($supids_arr_str, 0, strlen($supids_arr_str)-1);
		
		if($search != ""){
			$qry = \PurchasedOrders::where("purchase_orders.status","=","ACTIVE")->whereIn("purchase_orders.type",array("PURCHASE ORDER","OFFICE PURCHASE ORDER"));
			if($values["logstatus"] != "All"){
				$qry->where("purchase_orders.workFlowStatus","=",$values["logstatus"]);
			}
			
				$qry->whereRaw(' (purchase_orders.officeBranchId in('.$emp_branches_str.') and purchase_orders.creditSupplierId in('.$supids_arr_str.'))')
					->whereIn("purchase_orders.createdBy",$createdby_arr)
					->whereBetween("purchase_orders.orderDate",array($frmDt,$toDt))
					->leftjoin("officebranch", "officebranch.id","=","purchase_orders.officeBranchId")
					->leftjoin("creditsuppliers", "creditsuppliers.id","=","purchase_orders.creditSupplierId")
					->leftjoin("employee as employee2", "employee2.id","=","purchase_orders.createdBy")
					->leftjoin("employee as employee3", "employee3.id","=","purchase_orders.inchargeId");
					//print_r($supids_arr_str);die();
			$entities =	$qry->select($select_args)->limit($length)->offset($start)->get();
			
			//print_r($entities);die();
			
			$qry = \PurchasedOrders::where("purchase_orders.status","=","ACTIVE")->whereIn("purchase_orders.type",array("PURCHASE ORDER","OFFICE PURCHASE ORDER"));
			if($values["logstatus"] != "All"){
				$qry->where("purchase_orders.workFlowStatus","=",$values["logstatus"]);
			}
				$qry->whereRaw(' (purchase_orders.officeBranchId in('.$emp_branches_str.') and purchase_orders.creditSupplierId in('.$supids_arr_str.'))')
					->whereIn("purchase_orders.createdBy",$createdby_arr)
					->whereBetween("purchase_orders.orderDate",array($frmDt,$toDt))
					->leftjoin("officebranch", "officebranch.id","=","purchase_orders.officeBranchId")
					->leftjoin("creditsuppliers", "creditsuppliers.id","=","purchase_orders.creditSupplierId")
					->leftjoin("employee as employee2", "employee2.id","=","purchase_orders.createdBy")
					->leftjoin("employee as employee3", "employee3.id","=","purchase_orders.inchargeId");
					
				$total = $qry->count();
		}
		else {
			$qry = \PurchasedOrders::where("purchase_orders.status","=","ACTIVE")->whereIn("purchase_orders.type",array("PURCHASE ORDER","OFFICE PURCHASE ORDER"));
			if($values["logstatus"] != "All"){
				$qry->where("purchase_orders.workFlowStatus","=",$values["logstatus"]);
			}
			$qry->whereRaw('purchase_orders.officeBranchId in('.$emp_branches_str.')')
						->whereIn("purchase_orders.createdBy",$createdby_arr)
						->whereBetween("purchase_orders.orderDate",array($frmDt,$toDt))
						->leftjoin("officebranch", "officebranch.id","=","purchase_orders.officeBranchId")
						->leftjoin("employee as employee2", "employee2.id","=","purchase_orders.createdBy")
						->leftjoin("employee as employee3", "employee3.id","=","purchase_orders.inchargeId")
						->leftjoin("creditsuppliers", "creditsuppliers.id","=","purchase_orders.creditSupplierId");
			$entities = $qry->select($select_args)->limit($length)->offset($start)->get();
				
			$qry =  \PurchasedOrders::where("purchase_orders.status","=","ACTIVE")->whereIn("purchase_orders.createdBy",$createdby_arr)
									->whereIn("purchase_orders.type",array("PURCHASE ORDER","OFFICE PURCHASE ORDER"));
						if($values["logstatus"] != "All"){
							$qry->where("purchase_orders.workFlowStatus","=",$values["logstatus"]);
						}
						$qry->whereRaw('purchase_orders.officeBranchId in('.$emp_branches_str.')')
						->whereBetween("purchase_orders.orderDate",array($frmDt,$toDt));
						
			$total = $qry->count();
		}
		$entities = $entities->toArray();
		$vehs_arr = array();
		$vehicles = \Vehicle::All();
		foreach ($vehicles  as $vehicle){
			$vehs_arr[$vehicle->id] = $vehicle->veh_reg;
		}
		//print_r($entities);die();
		$i=0;
		foreach($entities as $entity){
			if($entity["billNo"] != ""){
				if($entity["filePath"]==""){
					$entity["billNo"] = "<span style='color:red; font-weight:bold;'>".$entity["billNo"]."</span>";
				}
				else{
					$entity["billNo"] = "<a href='../app/storage/uploads/".$entity["filePath"]."' target='_blank'>".$entity["billNo"]."</a>";
				}
			}
			$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			$trans_items = \PurchasedItems::where("purchasedOrderId","=",$entity["id"])
							->where("purchased_items.status","=","ACTIVE")
							->leftjoin("items","items.id","=","purchased_items.itemId")
							->select(array("items.name as name", "purchased_items.purchasedQty as qty"))->get();
				
			$entity["items"] = "ITEMS : ";
			foreach($trans_items as $trans_item){
				$entity["items"] = $entity["items"].$trans_item->name."(".$trans_item->qty."), ";
			}
			if($entity["workFlowStatus"] == "Sent for Approval"){
				$entity["workFlowRemarks"] = '<label> <input name="remarks[]" type="text" class=""></label>';
			}
			else{
				$entity["workFlowRemarks"] = $entity["workFlowRemarks"].'<input name="remarks[]" type="hidden" class="">';
			}
			if($entity["workFlowStatus"] == "Requested"){
				$entity["workFlowStatus"] = 'pending for approval';
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
			
			$action_data = "";
			$login_user = \Auth::user()->fullName;
			if($entity["workFlowStatus"] != "Approved"){
				$login_user = \Auth::user()->fullName;
				if((isset($entity["createdBy"]) && in_array($entity["createdById"],$createdby_arr))  || (isset($entity["createdBy"]) && in_array(507, $this->jobs))){
					$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="checkbox" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
				}
				else{
					$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
				}
			}
			else{
				$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
			}
			
			$data_values[13] = $action_data;
			$data[] = $data_values;
			$i++;
		}
		return array("total"=>$total, "data"=>$data);
	}
	
	private function getFuelTransactions($values, $length, $start){
		
		$dtrange = $values["daterange"];
		$dtrange = explode(" - ", $dtrange);
		$frmDt = date("Y-m-d",strtotime($dtrange[0]));
		$toDt = date("Y-m-d",strtotime($dtrange[1]));
		$length++;
		$total = 0;
		$data = array();
		$select_args = array();
		$select_args[] = "officebranch.name as branchId";
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
		//$select_args[] = "fueltransactions.fullTank as fullTank";
		//$select_args[] = "fueltransactions.billNo as mileage";
		$select_args[] = "fueltransactions.paymentType as paymentType";
		$select_args[] = "fueltransactions.remarks as remarks";
		$select_args[] = "employee2.fullName as createdBy";
		$select_args[] = "fueltransactions.workFlowStatus as workFlowStatus";
		$select_args[] = "fueltransactions.workFlowRemarks as workFlowRemarks";
		$select_args[] = "fueltransactions.id as id";
		$select_args[] = "fueltransactions.branchId as branch";
		$select_args[] = "fueltransactions.contractId as contractId";
		$select_args[] = "clients.name as clientname";
		$select_args[] = "depots.name as depotname";
		$select_args[] = "fueltransactions.filePath as filePath";
		$select_args[] = "fueltransactions.startReading as startReading";
		$select_args[] = "fueltransactions.litres as litres";
		$select_args[] = "fueltransactions.createdBy as createdById";
		
		$actions = array();
		$values["actions"] = $actions;
	
		$search = $_REQUEST["search"];
		$search = $search['value'];
		
		$entities = \Vehicle::where("id","=",0)->get();
		
		$assingedBranches = AppSettingsController::getEmpBranches();
		$emp_branches_str = "";
		foreach ($assingedBranches as $assingedBranch){
			$emp_branches_str = $emp_branches_str.$assingedBranch["id"].",";
		}
		$emp_branches_str = substr($emp_branches_str, 0, strlen($emp_branches_str)-1);
		
		$emp_contracts = \Auth::user()->contractIds;
		$emp_contracts_str = "";
		if($emp_contracts=="" || $emp_contracts==0){
			$clients = \Contract::All();
			foreach ($clients as $client){
				$emp_contracts_str = $emp_contracts_str.$client->id.",";
			}
		}
		else{
			$emp_contracts = explode(",", $emp_contracts);
			$depots = \Depot::whereIn("depots.id",$emp_contracts)
			->join("contracts", "depots.id", "=","contracts.depotId")
			->select(array("contracts.id as id"))->get();
			foreach ($depots as $depot){
				$emp_contracts_str = $emp_contracts_str.$depot->id.",";
			}
		}
		$emp_contracts_str = substr($emp_contracts_str, 0, strlen($emp_contracts_str)-1);
		
		$createdby_arr = array();
		if(in_array(507, $this->jobs)){
			$emps = \Employee::All();
			$createdby_arr[] = 0;
			foreach ($emps as $emp){
				$createdby_arr[] = $emp->id;
			}
		}
		else{
			$createdby_arr[] = Auth::user()->id;
			$assignedemps = \Auth::user()->assignedEmpIds;
			//print_r($assignedemps);die();
			$assignedemps_arr = explode(",", $assignedemps);
			foreach ($assignedemps_arr as $assignedemp){
				$createdby_arr[] = $assignedemp;
			}
		}
		
		if($search != ""){
			$entities = \Vehicle::where("veh_reg", "like", "%$search%")
								->where("vehicle.status","=","ACTIVE")->get();
			$veh_arr_str = "0,";
			foreach ($entities as $entity){
				$veh_arr_str = $veh_arr_str.$entity->id.",";
			}
			$veh_arr_str = substr($veh_arr_str, 0, strlen($veh_arr_str)-1);
			
			$entities = \FuelStation::where("name", "like", "%$search%")->get();
			$station_arr_str = "0,";
			foreach ($entities as $entity){
				$station_arr_str = $station_arr_str.$entity->id.",";
			}
			$station_arr_str = substr($station_arr_str, 0, strlen($station_arr_str)-1);
			
			$entities = \Employee::where("fullName", "like", "%$search%")->get();
			$createdBy_arr_str= "-1,";
			foreach ($entities as $entity){
				$createdBy_arr_str= $createdBy_arr_str. $entity->id.",";
			}
			$createdBy_arr_str = substr($createdBy_arr_str, 0, strlen($createdBy_arr_str)-1);
			
			$qry = \FuelTransaction::where("fueltransactions.status","=","ACTIVE");
			if($values["logstatus"] != "All"){
				$qry->where("fueltransactions.workFlowStatus","=",$values["logstatus"]);
			}
			$qry->whereRaw('(fueltransactions.fuelStationId in('.$station_arr_str.') or fueltransactions.vehicleId in('.$veh_arr_str.') or fueltransactions.createdBy in('.$createdBy_arr_str.')) ')
				->whereRaw('(fueltransactions.branchId in('.$emp_branches_str.') or fueltransactions.contractId in('.$emp_contracts_str.'))')
				->whereBetween("filledDate",array($frmDt,$toDt))
				->leftjoin("officebranch", "officebranch.id","=","fueltransactions.branchId")
				->leftjoin("vehicle", "vehicle.id","=","fueltransactions.vehicleId")
				->leftjoin("fuelstationdetails", "fuelstationdetails.id","=","fueltransactions.fuelStationId")
				->leftjoin("contracts", "contracts.id","=","fueltransactions.contractId")
				->leftjoin("employee as employee2", "employee2.id","=","fueltransactions.createdBy")
				->leftjoin("employee as employee4", "employee4.id","=","fueltransactions.inchargeId")
				->leftjoin("clients", "clients.id","=","contracts.clientId")
				->leftjoin("depots", "depots.id","=","contracts.depotId");
			$entities = $qry->select($select_args)->orderBy("fueltransactions.vehicleId")->orderBy("filledDate","desc")->limit($length)->offset($start)->get();
			
			$qry = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")->whereIn("fueltransactions.createdBy",$createdby_arr);
			if($values["logstatus"] != "All"){
				$qry->where("fueltransactions.workFlowStatus","=",$values["logstatus"]);
			}
			$qry->whereRaw('(fueltransactions.fuelStationId in('.$station_arr_str.') or fueltransactions.vehicleId in('.$veh_arr_str.') or fueltransactions.createdBy in('.$createdBy_arr_str.')) ')
				->whereRaw('(fueltransactions.branchId in('.$emp_branches_str.') or fueltransactions.contractId in('.$emp_contracts_str.'))');
			$total = $qry->count();
			
		}
		else {
			$qry = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")->whereIn("fueltransactions.createdBy",$createdby_arr);
			if($values["logstatus"] != "All"){
				$qry->where("fueltransactions.workFlowStatus","=",$values["logstatus"]);
			}
			$qry->whereRaw('(fueltransactions.branchId in('.$emp_branches_str.') or fueltransactions.contractId in('.$emp_contracts_str.'))')
				->whereBetween("filledDate",array($frmDt,$toDt))
				->leftjoin("officebranch", "officebranch.id","=","fueltransactions.branchId")
				->leftjoin("vehicle", "vehicle.id","=","fueltransactions.vehicleId")
				->leftjoin("fuelstationdetails", "fuelstationdetails.id","=","fueltransactions.fuelStationId")
				->leftjoin("contracts", "contracts.id","=","fueltransactions.contractId")
				->leftjoin("clients", "clients.id","=","contracts.clientId")
				->leftjoin("employee as employee2", "employee2.id","=","fueltransactions.createdBy")
				->leftjoin("employee as employee4", "employee4.id","=","fueltransactions.inchargeId")
				->leftjoin("depots", "depots.id","=","contracts.depotId");
			$entities = $qry->select($select_args)->orderBy("filledDate","desc")->limit($length)->offset($start)->get();
				
			$qry = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")->whereIn("fueltransactions.createdBy",$createdby_arr);
			if($values["logstatus"] != "All"){
				$qry->where("fueltransactions.workFlowStatus","=",$values["logstatus"]);
			}
			$qry->whereRaw('(fueltransactions.branchId in('.$emp_branches_str.') or fueltransactions.contractId in('.$emp_contracts_str.'))')
			->whereBetween("filledDate",array($frmDt,$toDt));
	
			$total = $qry->count();
		}
		$entities = $entities->toArray();
		$r_cnt = 0;
		$k = 0;
		$i = 0;
		foreach($entities as $entity){
			if($r_cnt==$length){
				continue;
			}
			$r_cnt++;
			$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			if($entity["contractId"]>0){
				$entity["branchId"] = $entity["depotname"]."(".$entity["clientname"].")";
			}
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
						break;
					}
					if($j<count($entities) && $entities[$j]["fullTank"]=="NO"){
						$ltrs = $ltrs+$entities[$j]["litres"];
					}
					$j++;
				}
			}
			$k++;
			if($entity["workFlowStatus"] == "Sent for Approval"){
				$entity["workFlowRemarks"] = '<label> <input name="remarks[]" type="text" class=""></label>';
			}
			else{
				$entity["workFlowRemarks"] = $entity["workFlowRemarks"].'<input name="remarks[]" type="hidden" class="">';
			}
			if($entity["workFlowStatus"] == "Requested"){
				$entity["workFlowStatus"] = 'pending for approval';
			}
			$data_values = array_values($entity);
			$values1 = array("branch"=>$entity["branch"],"date"=>$entity["date"]);
			$action_data = "";
			if($entity["workFlowStatus"] != "Approved"){
				$login_user = \Auth::user()->fullName;
				if((isset($entity["createdBy"]) && in_array($entity["createdById"],$createdby_arr)) || (isset($entity["createdBy"]) && in_array(507, $this->jobs))){
					$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="checkbox" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
				}
				else{
					$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
				}
			}
			else{
				$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
			}
			if($entity["workFlowStatus"] == "requested"){
				$entity["workFlowStatus"] = 'pending for approval';
			}
			$data_values[16] = $action_data;
			$data[] = $data_values;
			$i++;
		}
		return array("total"=>$total, "data"=>$data);
	}
	
	private function getFuelTransactions1($values, $length, $start){
		//$val["test"];
		$total = 0;
		$data = array();
		$select_args = array();
		$select_args[] = "officebranch.name as branchId";
		$select_args[] = "fuelstationdetails.name as fuelStationName";
		$select_args[] = "vehicle.veh_reg as vehicleId";
		$select_args[] = "fueltransactions.filledDate as date";
		$select_args[] = "fueltransactions.amount as amount";
		$select_args[] = "fueltransactions.billNo as billNo";
		//$select_args[] = "fueltransactions.fullTank as fullTank";
		//$select_args[] = "fueltransactions.billNo as mileage";
		$select_args[] = "fueltransactions.paymentType as paymentType";
		$select_args[] = "fueltransactions.remarks as remarks";
		$select_args[] = "employee2.fullName as createdBy";
		$select_args[] = "fueltransactions.workFlowStatus as workFlowStatus";
		$select_args[] = "fueltransactions.workFlowRemarks as workFlowRemarks";
		$select_args[] = "fueltransactions.id as id";
		$select_args[] = "fueltransactions.branchId as branch";
		$select_args[] = "fueltransactions.contractId as contractId";
		$select_args[] = "clients.name as clientname";
		$select_args[] = "depots.name as depotname";
		$select_args[] = "fueltransactions.filePath as filePath";
		$select_args[] = "fueltransactions.startReading as startReading";
		$select_args[] = "fueltransactions.litres as litres";

		$actions = array();
		$values["actions"] = $actions;
	
		$search = $_REQUEST["search"];
		$search = $search['value'];
		$entities = \Vehicle::where("id","=",0)->get();
		
		$assingedBranches = AppSettingsController::getEmpBranches();
		$emp_branches_str = "";
		foreach ($assingedBranches as $assingedBranch){
			$emp_branches_str = $emp_branches_str.$assingedBranch["id"].",";
		}
		$emp_branches_str = substr($emp_branches_str, 0, strlen($emp_branches_str)-1);
		$emp_contracts = \Auth::user()->contractIds;
		$emp_contracts_str = "";
		if($emp_contracts=="" || $emp_contracts==0){
			$clients = \Contract::All();
			foreach ($clients as $client){
				$emp_contracts_str = $emp_contracts_str.$client->id.",";
			}
		}
		else{
			$emp_contracts = explode(",", $emp_contracts);
			$depots = \Depot::whereIn("depots.id",$emp_contracts)
						->join("contracts", "depots.id", "=","contracts.depotId")
						->select(array("contracts.id as id"))->get();
			foreach ($depots as $depot){
				$emp_contracts_str = $emp_contracts_str.$depot->id.",";
			}
		}
		$emp_contracts_str = substr($emp_contracts_str, 0, strlen($emp_contracts_str)-1);
		
		if($search != ""){
			$entities = \Vehicle::where("veh_reg", "like", "%$search%")
							->where("vehicle.status","=","ACTIVE")->get();
			$veh_arr = array();
			foreach ($entities as $entity){
				$veh_arr[] = $entity->id;
			}
			$qry = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")
							->whereIn("vehicleId",$veh_arr)
							->whereRaw('(fueltransactions.branchId in('.$emp_branches_str.') or fueltransactions.contractId in('.$emp_contracts_str.'))')
							->leftjoin("officebranch", "officebranch.id","=","fueltransactions.branchId")
							->leftjoin("vehicle", "vehicle.id","=","fueltransactions.vehicleId")
							->leftjoin("fuelstationdetails", "fuelstationdetails.id","=","fueltransactions.fuelStationId")
							->leftjoin("contracts", "contracts.id","=","fueltransactions.contractId")
							->leftjoin("employee as employee2", "employee2.id","=","fueltransactions.createdBy")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("depots", "depots.id","=","contracts.depotId");
			$entities = $qry->select($select_args)->limit($length)->offset($start)->get();
			
			$total = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")
							->whereRaw('(fueltransactions.branchId in('.$emp_branches_str.') or fueltransactions.contractId in('.$emp_contracts_str.'))')
							->count();
		}
		else {
			$qry = \FuelTransaction::where("fueltransactions.status","=","ACTIVE");
						if($values["logstatus"] != "All"){
							$qry->where("fueltransactions.workFlowStatus","=",$values["logstatus"]);
						}
						$qry->whereRaw('(fueltransactions.branchId in('.$emp_branches_str.') or fueltransactions.contractId in('.$emp_contracts_str.'))')
						->leftjoin("officebranch", "officebranch.id","=","fueltransactions.branchId")
						->leftjoin("vehicle", "vehicle.id","=","fueltransactions.vehicleId")
						->leftjoin("fuelstationdetails", "fuelstationdetails.id","=","fueltransactions.fuelStationId")
						->leftjoin("contracts", "contracts.id","=","fueltransactions.contractId")
						->leftjoin("clients", "clients.id","=","contracts.clientId")
						->leftjoin("employee as employee2", "employee2.id","=","fueltransactions.createdBy")
						->leftjoin("depots", "depots.id","=","contracts.depotId");
			$entities = $qry->select($select_args)->limit($length)->offset($start)->get();
			
			$qry = \FuelTransaction::where("fueltransactions.status","=","ACTIVE");
						if($values["logstatus"] != "All"){
							$qry->where("fueltransactions.workFlowStatus","=",$values["logstatus"]);
						}
						$qry->whereRaw('(fueltransactions.branchId in('.$emp_branches_str.') or fueltransactions.contractId in('.$emp_contracts_str.'))');
						
			$total = $qry->count();
		}
		$entities = $entities->toArray();
		$i = 0;
		$k = 0;
		foreach($entities as $entity){
			$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
			if($entity["contractId"]>0){
				$entity["branchId"] = $entity["depotname"]."(".$entity["clientname"].")";
			}
			if($entity["billNo"] != ""){
				if($entity["filePath"]==""){
					$entity["billNo"] = "<span style='color:red; font-weight:bold;'>".$entity["billNo"]."</span>";
				}
				else{
					$entity["billNo"] = "<a href='../app/storage/uploads/".$entity["filePath"]."' target='_blank'>".$entity["billNo"]."</a>";
				}
			}
			/*$entity["mileage"] = "0";
			if($entity["fullTank"]=="YES"){
				$j = $k+1;
				while($j<count($entities))
				{
					if($entities[$j]["vehicleId"]==$entity["vehicleId"] && $entities[$j]["fullTank"]=="YES"){
						$entity["mileage"] = round((($entity["startReading"]-$entities[$j]["startReading"])/$entity["litres"]), 2);
						echo $entities[$i]["startReading"].", ";
						break;
					}
					$j++;
				}
			}*/
			$k++;
			if($entity["workFlowStatus"] == "Sent for Approval"){
				$entity["workFlowRemarks"] = '<label> <input name="remarks[]" type="text" class=""></label>';
			}
			else{
				$entity["workFlowRemarks"] = $entity["workFlowRemarks"].'<input name="remarks[]" type="hidden" class="">';
			}
			if($entity["workFlowStatus"] == "Requested"){
				$entity["workFlowStatus"] = 'pending for approval';
			}
			$data_values = array_values($entity);
			$values1 = array("branch"=>$entity["branch"],"date"=>$entity["date"]);
			$action_data = "";
			if($entity["workFlowStatus"] != "Approved"){
				$login_user = \Auth::user()->fullName;
				if((isset($entity["createdBy"]) && $entity["createdBy"]==$login_user) || (isset($entity["createdBy"]) && in_array(507, $this->jobs))){
					$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="checkbox" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
				}
				else{
					$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
				}
			}
			else{
				$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
			}
			if($entity["workFlowStatus"] == "requested"){
				$entity["workFlowStatus"] = 'pending for approval';
			}
			$data_values[11] = $action_data;
			$data[] = $data_values;
			$i++;
		}
		return array("total"=>$total, "data"=>$data);
	}
	
	private function getExpenseTransactions($values, $length, $start){
		$dtrange = $values["daterange"];
		$dtrange = explode(" - ", $dtrange);
		$frmDt = date("Y-m-d",strtotime($dtrange[0]));
		$toDt = date("Y-m-d",strtotime($dtrange[1]));
		$total = 0;
		$data = array();
	
		$actions = array();
		$values["actions"] = $actions;
		$search = $_REQUEST["search"];
		$search = $search['value'];
		
		$assingedBranches = AppSettingsController::getEmpBranches();
		$emp_branches_str = "";
		foreach ($assingedBranches as $assingedBranch){
			$emp_branches_str = $emp_branches_str.$assingedBranch["id"].",";
		}
		$emp_branches_str = substr($emp_branches_str, 0, strlen($emp_branches_str)-1);
		$emp_contracts = \Auth::user()->contractIds;
		$emp_contracts_str = "";
		if($emp_contracts=="" || $emp_contracts==0){
			$clients = \Contract::All();
			foreach ($clients as $client){
				$emp_contracts_str = $emp_contracts_str.$client->id.",";
			}
		}
		else{
			$emp_contracts = explode(",", $emp_contracts);
			$depots = \Depot::whereIn("depots.id",$emp_contracts)
								->join("contracts", "depots.id", "=","contracts.depotId")
								->select(array("contracts.id as id"))->get();
			foreach ($depots as $depot){
				$emp_contracts_str = $emp_contracts_str.$depot->id.",";
			}
		}
		$emp_contracts_str = substr($emp_contracts_str, 0, strlen($emp_contracts_str-1));
		
		if(true){
			$select_args = array();
			$select_args[] = "officebranch.name as branchId";
			$select_args[] = "expensetransactions.amount as amount";
			$select_args[] = "expensetransactions.date as date";
			$select_args[] = "lookuptypevalues.name as name";
			$select_args[] = "expensetransactions.billNo as billNo";
			$select_args[] = "expensetransactions.remarks as remarks";
			$select_args[] = "employee2.fullName as createdBy";
			$select_args[] = "expensetransactions.workFlowStatus as workFlowStatus";
			$select_args[] = "expensetransactions.workFlowRemarks as workFlowRemarks";
			$select_args[] = "expensetransactions.transactionId as id";
			$select_args[] = "expensetransactions.lookupValueId as lookupValueId";
			$select_args[] = "expensetransactions.branchId as branch";
			$select_args[] = "clients.name as clientname";
			$select_args[] = "depots.name as depotname";
			$select_args[] = "employee.empCode as empCode";
			$select_args[] = "expensetransactions.filePath as filePath";
			$select_args[] = "expensetransactions.entity as entity";
			$select_args[] = "expensetransactions.entityValue as entityValue";
			$select_args[] = "expensetransactions.vehicleIds as vehicleIds";
			$select_args[] = "expensetransactions.createdBy as createdById";
				
			
			$createdby_arr = array();
			if(in_array(507, $this->jobs)){
				$emps = \Employee::All();
				$createdby_arr[] = 0;
				foreach ($emps as $emp){
					$createdby_arr[] = $emp->id;
				}
			}
			else{
				$createdby_arr[] = Auth::user()->id;
				$assignedemps = \Auth::user()->assignedEmpIds;
				//print_r($assignedemps);die();
				$assignedemps_arr = explode(",", $assignedemps);
				foreach ($assignedemps_arr as $assignedemp){
					$createdby_arr[] = $assignedemp;
				}
			}
			$entities = \Employee::where("fullName", "like", "%$search%")->get();
			$emp_arr_str = "-1,";
			foreach ($entities as $entity){
				$emp_arr_str = $emp_arr_str.$entity->id.",";
			}
			$emp_arr_str = substr($emp_arr_str, 0, strlen($emp_arr_str)-1);
			
			if($search != ""){
				$logstatus_arr = array();
				if($values["logstatus"] != "All"){
					//$qry->where("purchase_orders.workFlowStatus","=",$values["logstatus"]);
					$logstatus_arr [] =  $values["logstatus"];
				}
				else{
					$logstatus_arr = array("Requested","Sent for Approval","Approved","Rejected","Hold");
				}
				$entities = \ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
								->whereRaw('(expensetransactions.createdBy in('.$emp_arr_str.'))')
								->whereIn("expensetransactions.workFlowStatus",$logstatus_arr)
								->whereIn("expensetransactions.createdBy",$createdby_arr)
								->whereRaw('(expensetransactions.branchId in('.$emp_branches_str.') or expensetransactions.contractId in('.$emp_contracts_str.'))')
								->whereBetween("date",array($frmDt,$toDt))
								->leftjoin("contracts", "contracts.id","=","expensetransactions.contractId")
								->leftjoin("officebranch", "officebranch.id","=","expensetransactions.branchId")
								->leftjoin("employee as employee2", "employee2.id","=","expensetransactions.createdBy")
								->leftjoin("employee", "employee.id","=","expensetransactions.inchargeId")
								->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","expensetransactions.lookupValueId")
								->leftjoin("clients", "clients.id","=","contracts.clientId")
								->leftjoin("depots", "depots.id","=","contracts.depotId")
								->orderBy("date","desc")
								->select($select_args)->limit($length)->offset($start)->get();
				$total =\ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
								->whereRaw('(expensetransactions.createdBy in('.$emp_arr_str.'))')
								->whereIn("expensetransactions.workFlowStatus",$logstatus_arr)
								->whereIn("expensetransactions.createdBy",$createdby_arr)
								->whereRaw('(expensetransactions.branchId in('.$emp_branches_str.') or expensetransactions.contractId in('.$emp_contracts_str.'))')
								->whereBetween("date",array($frmDt,$toDt))
								->leftjoin("contracts", "contracts.id","=","expensetransactions.contractId")
								->leftjoin("officebranch", "officebranch.id","=","expensetransactions.branchId")
								->leftjoin("employee as employee2", "employee2.id","=","expensetransactions.createdBy")
								->leftjoin("employee", "employee.id","=","expensetransactions.inchargeId")
								->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","expensetransactions.lookupValueId")
								->leftjoin("clients", "clients.id","=","contracts.clientId")
								->leftjoin("depots", "depots.id","=","contracts.depotId")
								->get();
				$total = count($total);
				foreach ($entities as $entity){
					$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
				}
			}
			else{
				$logstatus_arr = array();
				if($values["logstatus"] != "All"){
					//$qry->where("purchase_orders.workFlowStatus","=",$values["logstatus"]);
					$logstatus_arr [] =  $values["logstatus"];
				}
				else{
					$logstatus_arr = array("Requested","Sent for Approval","Approved","Rejected","Hold");
				}
				$entities = \ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
								->whereIn("expensetransactions.createdBy",$createdby_arr)
								->whereIn("expensetransactions.workFlowStatus",$logstatus_arr)
								//->where("expensetransactions.inchargeId","=",0)
								->whereRaw('(expensetransactions.branchId in('.$emp_branches_str.') or expensetransactions.contractId in('.$emp_contracts_str.'))')
								->whereBetween("date",array($frmDt,$toDt))
								->leftjoin("officebranch", "officebranch.id","=","expensetransactions.branchId")
								->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","expensetransactions.lookupValueId")
								->leftjoin("contracts", "contracts.id","=","expensetransactions.contractId")
								->leftjoin("employee", "employee.id","=","expensetransactions.inchargeId")
								->leftjoin("employee as employee2", "employee2.id","=","expensetransactions.createdBy")
								->leftjoin("clients", "clients.id","=","contracts.clientId")
								->leftjoin("depots", "depots.id","=","contracts.depotId")
								->orderBy("date","desc")
								->select($select_args)->limit($length)->offset($start)->get();
				$total = \ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
								->whereIn("expensetransactions.createdBy",$createdby_arr)
								->whereIn("expensetransactions.workFlowStatus",$logstatus_arr)
								//->where("expensetransactions.inchargeId","=",0)
								->whereRaw('(expensetransactions.branchId in('.$emp_branches_str.') or expensetransactions.contractId in('.$emp_contracts_str.'))')
								->whereBetween("date",array($frmDt,$toDt))
								->leftjoin("officebranch", "officebranch.id","=","expensetransactions.branchId")
								->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","expensetransactions.lookupValueId")
								->leftjoin("contracts", "contracts.id","=","expensetransactions.contractId")
								->leftjoin("employee", "employee.id","=","expensetransactions.inchargeId")
								->leftjoin("employee as employee2", "employee2.id","=","expensetransactions.createdBy")
								->leftjoin("clients", "clients.id","=","contracts.clientId")
								->leftjoin("depots", "depots.id","=","contracts.depotId")
								->count();
				//$total = count($total);
				foreach ($entities as $entity){
					$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
				}
			}
			$i = 0;
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
				if($entity["workFlowStatus"] == "Sent for Approval"){
					$entity["workFlowRemarks"] = '<label> <input name="remarks[]" type="text" class=""></label>';
				}
				else{
					$entity["workFlowRemarks"] = $entity["workFlowRemarks"].'<input name="remarks[]" type="hidden" class="">';
				}
				if($entity["workFlowStatus"] == "Requested"){
					$entity["workFlowStatus"] = 'pending for approval';
				}
				if($entity["lookupValueId"]>900){
					$expenses_arr = array();
					$expenses_arr["998"] = "CREDIT SUPPLIER PAYMENT";
					$expenses_arr["997"] = "FUEL STATION PAYMENT";
					$expenses_arr["996"] = "LOAN PAYMENT";
					$expenses_arr["995"] = "RENT";
					$expenses_arr["994"] = "INCHARGE ACCOUNT CREDIT";
					$expenses_arr["993"] = "PREPAID RECHARGE";
					$expenses_arr["992"] = "ONLINE OPERATORS";
					$expenses_arr["999"] = "PREPAID RECHARGE";
					$expenses_arr["991"] = "DAILY FINANCE PAYMENT";
					$expenses_arr["989"] = "VEHICLE RENEWALS";
					$entity["name"] = "";
					if(isset($expenses_arr[$entity["lookupValueId"]])){
						$entity["name"] = $expenses_arr[$entity["lookupValueId"]];
					}
				}
				
				if($entity["lookupValueId"]==999){
					if($entity["entityValue"]>0){
						$prepaidName = \LookupTypeValues::where("id","=",$entity["entityValue"])->first();
						$prepaidName = $prepaidName->name;
						$entity["name"] = $entity["name"]." - ".strtoupper($entity["entity"]);
					}
				}
				else if($entity["lookupValueId"]==998){
					if($entity["entityValue"]>0){
						$creditsupplier = \CreditSupplier::where("id","=",$entity["entityValue"])->first();
						$creditsupplier = $creditsupplier->supplierName;
						$entity["name"] = $entity["name"]." - ".$creditsupplier;
					}
				}
				else if($entity["lookupValueId"]==997){
					if($entity["entityValue"]>0){
						$fuelstation = \FuelStation::where("id","=",$entity["entityValue"])->first();
						$fuelstation = $fuelstation->name;
						$entity["name"] = $entity["name"]." - ".$fuelstation;
					}
				}
				else if($entity["lookupValueId"]==996){
					if($entity["entityValue"]>0){
						$loan = \Loan::where("id","=",$entity["entityValue"])->first();
						$dfid = $loan->financeCompanyId;
						$finanacecompany = \FinanceCompany::where("id","=",$dfid)->first();
						$finanacecompany = $finanacecompany->name;
						$entity["name"] = $entity["name"]." - ".$loan->loanNo." (".$finanacecompany.")";
					}
				}
				else if($entity["lookupValueId"]==991){
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
				$vehicles =  \Vehicle::All();
				$veh_arr = array();
				foreach ($vehicles as $vehicle){
					$veh_arr[$vehicle->id] = $vehicle->veh_reg;
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
				
				$data_values = array_values($entity);
				$actions = $values['actions'];
				$action_data = "";
				$bde = new BlockDataEntryController();
				$values1 = array("branch"=>$entity["branch"],"date"=>$entity["date"]);
				$valid = $bde->verifyTransactionDateandBranchLocally($values1);
				$action_data = "";
				if($entity["workFlowStatus"] != "Approved"){
					$login_user = \Auth::user()->fullName;
					if((isset($entity["createdBy"]) && in_array($entity["createdById"],$createdby_arr)) || (isset($entity["createdBy"]) && in_array(507, $this->jobs))){
						$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="checkbox" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
					}
					else{
						$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
					}
				}
				else{
					$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
				}
				if($entity["workFlowStatus"] == "requested"){
					$entity["workFlowStatus"] = 'pending for approval';
				}
				
				
				$data_values[9] = $action_data;
				$data[] = $data_values;
				$i++;
			}
			return array("total"=>$total, "data"=>$data);
		}
	}
	
	private function getInchargeTransactions($values, $length, $start){
		$dtrange = $values["daterange"];
		$dtrange = explode(" - ", $dtrange);
		$frmDt = date("Y-m-d",strtotime($dtrange[0]));
		$toDt = date("Y-m-d",strtotime($dtrange[1]));
		$total = 0;
		$data = array();
		$actions = array();
		$values["actions"] = $actions;
		$search = $_REQUEST["search"];
		$search = $search['value'];
		
		$assingedBranches = AppSettingsController::getEmpBranches();
		$emp_branches_str = "";
		foreach ($assingedBranches as $assingedBranch){
			$emp_branches_str = $emp_branches_str.$assingedBranch["id"].",";
		}
		$emp_branches_str = substr($emp_branches_str, 0, strlen($emp_branches_str)-1);
		$emp_contracts = \Auth::user()->contractIds;
		$emp_contracts_str = "";
		if($emp_contracts=="" || $emp_contracts==0){
			$clients = \Contract::All();
			foreach ($clients as $client){
				$emp_contracts_str = $emp_contracts_str.$client->id.",";
			}
		}
		else{
			$emp_contracts = explode(",", $emp_contracts);
			$depots = \Depot::whereIn("depots.id",$emp_contracts)
								->join("contracts", "depots.id", "=","contracts.depotId")
								->select(array("contracts.id as id"))->get();
			foreach ($depots as $depot){
				$emp_contracts_str = $emp_contracts_str.$depot->id.",";
			}
		}
		$emp_contracts_str = substr($emp_contracts_str, 0, strlen($emp_contracts_str-1));
		
		$entities = \Employee::where("fullName", "like", "%$search%")->get();
		$emp_arr_str = "-1,";
		foreach ($entities as $entity){
			$emp_arr_str = $emp_arr_str.$entity->id.",";
		}
		$emp_arr_str = substr($emp_arr_str, 0, strlen($emp_arr_str)-1);
		
		$createdby_arr = array();
		if(in_array(507, $this->jobs)){
			$emps = \Employee::All();
			$createdby_arr[] = 0;
			foreach ($emps as $emp){
				$createdby_arr[] = $emp->id;
			}
		}
		else{
			$createdby_arr[] = Auth::user()->id;
			$assignedemps = \Auth::user()->assignedEmpIds;
			//print_r($assignedemps);die();
			$assignedemps_arr = explode(",", $assignedemps);
			foreach ($assignedemps_arr as $assignedemp){
				$createdby_arr[] = $assignedemp;
			}
		}
		
		
		if(isset($values["inchargereporttype"]) && $values["inchargereporttype"] == "Income"){
			$select_args = array();
			$select_args[] = "officebranch.name as branchId";
			$select_args[] = "employee.fullName as inchargeId";
			$select_args[] = "incometransactions.amount as amount";
			$select_args[] = "incometransactions.date as date";
			$select_args[] = "lookuptypevalues.name as name";
			$select_args[] = "incometransactions.billNo as billNo";
			$select_args[] = "incometransactions.remarks as remarks";
			$select_args[] = "employee2.fullName as createdBy";
			$select_args[] = "incometransactions.workFlowStatus as workFlowStatus";
			$select_args[] = "incometransactions.workFlowRemarks as workFlowRemarks";
			$select_args[] = "incometransactions.transactionId as id";
			$select_args[] = "incometransactions.lookupValueId as lookupValueId";
			$select_args[] = "incometransactions.branchId as branch";
			$select_args[] = "clients.name as clientname";
			$select_args[] = "depots.name as depotname";
			$select_args[] = "employee.empCode as empCode";
			$select_args[] = "incometransactions.filePath as filePath";
			$select_args[] = "incometransactions.createdBy as createdById";
				
			if($search != ""){
				if($values["logstatus"] != "All"){
					//$qry->where("purchase_orders.workFlowStatus","=",$values["logstatus"]);
					$logstatus_arr [] =  $values["logstatus"];
				}
				else{
					$logstatus_arr = array("Requested","Sent for Approval","Approved","Rejected","Hold");
				}
				
				$entities = \IncomeTransaction::whereRaw('(incometransactions.inchargeId in('.$emp_arr_str.'))')
							->where("incometransactions.status","=","ACTIVE")
							->whereIn("incometransactions.createdBy",$createdby_arr)
							->whereIn("incometransactions.workFlowStatus",$logstatus_arr)
							->whereBetween("date",array($frmDt,$toDt))
							//->where("transactionId", "like", "%$search%")
							->leftjoin("officebranch", "officebranch.id","=","incometransactions.branchId")
							->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","incometransactions.lookupValueId")
							->leftjoin("contracts", "contracts.id","=","incometransactions.contractId")
							->leftjoin("employee", "employee.id","=","incometransactions.inchargeId")
							->leftjoin("employee as employee2", "employee2.id","=","incometransactions.createdBy")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")
							->select($select_args)->limit($length)->offset($start)->get();
				$total = \IncomeTransaction::where("incometransactions.status","=","ACTIVE")
							->whereRaw('(incometransactions.inchargeId in('.$emp_arr_str.'))')
							->whereIn("incometransactions.inchargeId",$incharge_arr)
							->whereIn("incometransactions.workFlowStatus",$logstatus_arr)
							->whereIn("incometransactions.createdBy",$createdby_arr)
							->whereRaw('(incometransactions.branchId in('.$emp_branches_str.') or incometransactions.contractId in('.$emp_contracts_str.'))')
							->whereBetween("date",array($frmDt,$toDt))
							->leftjoin("officebranch", "officebranch.id","=","incometransactions.branchId")
							->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","incometransactions.lookupValueId")
							->leftjoin("contracts", "contracts.id","=","incometransactions.contractId")
							->leftjoin("employee", "employee.id","=","incometransactions.inchargeId")
							->leftjoin("employee as employee2", "employee2.id","=","incometransactions.createdBy")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")
							->count();
				foreach ($entities as $entity){
					$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
				}
			}
			else{
				if($values["logstatus"] != "All"){
					//$qry->where("purchase_orders.workFlowStatus","=",$values["logstatus"]);
					$logstatus_arr [] =  $values["logstatus"];
				}
				else{
					$logstatus_arr = array("Requested","Sent for Approval","Approved","Rejected","Hold");
				}
				$incharge_arr = array();
				if($values["incharge"] == 0){
					$incharges = \InchargeAccounts::All();
					foreach ($incharges as $incharge){
						$incharge_arr[] = $incharge->empid;
					}
				}
				else{
					$incharge_arr[] =  $values["incharge"];
				}
				$entities = \IncomeTransaction::where("incometransactions.status","=","ACTIVE")
											->whereIn("incometransactions.createdBy",$createdby_arr)
											->whereIn("incometransactions.inchargeId",$incharge_arr)
											->whereIn("incometransactions.workFlowStatus",$logstatus_arr)
											->whereRaw('(incometransactions.branchId in('.$emp_branches_str.') or incometransactions.contractId in('.$emp_contracts_str.'))')
											->whereBetween("date",array($frmDt,$toDt))
											->leftjoin("officebranch", "officebranch.id","=","incometransactions.branchId")
											->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","incometransactions.lookupValueId")
											->leftjoin("contracts", "contracts.id","=","incometransactions.contractId")
											->leftjoin("employee", "employee.id","=","incometransactions.inchargeId")
											->leftjoin("employee as employee2", "employee2.id","=","incometransactions.createdBy")
											->leftjoin("clients", "clients.id","=","contracts.clientId")
											->leftjoin("depots", "depots.id","=","contracts.depotId")
											->select($select_args)->limit($length)->offset($start)->get();
				$total = \IncomeTransaction::where("incometransactions.status","=","ACTIVE")
							->whereIn("incometransactions.inchargeId",$incharge_arr)
							->whereIn("incometransactions.workFlowStatus",$logstatus_arr)
							->whereIn("incometransactions.createdBy",$createdby_arr)
							->whereRaw('(incometransactions.branchId in('.$emp_branches_str.') or incometransactions.contractId in('.$emp_contracts_str.'))')
							->whereBetween("date",array($frmDt,$toDt))
							->leftjoin("officebranch", "officebranch.id","=","incometransactions.branchId")
							->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","incometransactions.lookupValueId")
							->leftjoin("contracts", "contracts.id","=","incometransactions.contractId")
							->leftjoin("employee", "employee.id","=","incometransactions.inchargeId")
							->leftjoin("employee as employee2", "employee2.id","=","incometransactions.createdBy")
							->leftjoin("clients", "clients.id","=","contracts.clientId")
							->leftjoin("depots", "depots.id","=","contracts.depotId")
							->count();
				foreach ($entities as $entity){
					$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
				}
			}
			$i=0;
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
				if($entity["workFlowStatus"] == "Sent for Approval"){
					$entity["workFlowRemarks"] = '<label> <input name="remarks[]" type="text" class=""></label>';
				}
				else{
					$entity["workFlowRemarks"] = $entity["workFlowRemarks"].'<input name="remarks[]" type="hidden" class="">';
				}
				if($entity["workFlowStatus"] == "Requested"){
					$entity["workFlowStatus"] = 'pending for approval';
				}
				$entity["inchargeId"] = $entity["inchargeId"]." (".$entity["empCode"].")";
				if($entity["lookupValueId"]>900){
					$expenses_arr = array();
					$expenses_arr["998"] = "CREDIT SUPPLIER PAYMENT";
					$expenses_arr["997"] = "FUEL STATION PAYMENT";
					$expenses_arr["996"] = "LOAN PAYMENT";
					$expenses_arr["995"] = "RENT";
					$expenses_arr["994"] = "INCHARGE ACCOUNT CREDIT";
					$expenses_arr["993"] = "PREPAID RECHARGE";
					$expenses_arr["992"] = "ONLINE OPERATORS";
					$expenses_arr["999"] = "PREPAID RECHARGE";
					if(isset($expenses_arr[$entity["lookupValueId"]])){
						$entity["name"] = $expenses_arr[$entity["lookupValueId"]];
					}
					else {
						$entity["name"] = "";
					}
					
				}
				
				$data_values = array_values($entity);
				$actions = $values['actions'];
				$action_data = "";
				$bde = new BlockDataEntryController();
				$values1 = array("branch"=>$entity["branch"],"date"=>$entity["date"]);
				$valid = $bde->verifyTransactionDateandBranchLocally($values1);
				$action_data = "";
				if($entity["workFlowStatus"] != "Approved"){
					$login_user = \Auth::user()->fullName;
					if((isset($entity["createdBy"]) && in_array($entity["createdById"],$createdby_arr))  || (isset($entity["createdBy"]) && in_array(507, $this->jobs))){
						$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="checkbox" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
					}
					else{
						$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
					}
				}
				else{
					$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
				}
				$data_values[10] = $action_data;
				$data[] = $data_values;
				$i++;
			}
			return array("total"=>$total, "data"=>$data);
		}
		else if(isset($values["inchargereporttype"]) && $values["inchargereporttype"] == "Expense"){
			$select_args = array();
			$select_args[] = "officebranch.name as branchId";
			$select_args[] = "employee.fullName as inchargeId";
			$select_args[] = "expensetransactions.amount as amount";
			$select_args[] = "expensetransactions.date as date";
			$select_args[] = "lookuptypevalues.name as name";
			$select_args[] = "expensetransactions.billNo as billNo";
			$select_args[] = "expensetransactions.remarks as remarks";
			$select_args[] = "employee2.fullName as createdBy";
			$select_args[] = "expensetransactions.workFlowStatus as workFlowStatus";
			$select_args[] = "expensetransactions.workFlowRemarks as workFlowRemarks";
			$select_args[] = "expensetransactions.transactionId as id";
			$select_args[] = "expensetransactions.lookupValueId as lookupValueId";
			$select_args[] = "expensetransactions.branchId as branch";
			$select_args[] = "clients.name as clientname";
			$select_args[] = "depots.name as depotname";
			$select_args[] = "employee.empCode as empCode";
			$select_args[] = "expensetransactions.filePath as filePath";
			$select_args[] = "expensetransactions.createdBy as createdById";
			$select_args[] = "expensetransactions.vehicleIds as vehicleIds";
				
			if($search != ""){
				if($values["logstatus"] != "All"){
					//$qry->where("purchase_orders.workFlowStatus","=",$values["logstatus"]);
					$logstatus_arr [] =  $values["logstatus"];
				}
				else{
					$logstatus_arr = array("Requested","Sent for Approval","Approved","Rejected","Hold");
				}
				$entities = \ExpenseTransaction::whereRaw('(expensetransactions.inchargeId in('.$emp_arr_str.'))')
												->whereIn("expensetransactions.createdBy",$createdby_arr)
												->where("expensetransactions.status","=","ACTIVE")
												->whereIn("expensetransactions.workFlowStatus",$logstatus_arr)
												->whereBetween("date",array($frmDt,$toDt))
												//->where("transactionId", "like", "%$search%")
												->leftjoin("officebranch", "officebranch.id","=","expensetransactions.branchId")
												->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","expensetransactions.lookupValueId")
												->leftjoin("contracts", "contracts.id","=","expensetransactions.contractId")
												->leftjoin("employee", "employee.id","=","expensetransactions.inchargeId")
												->leftjoin("employee as employee2", "employee2.id","=","expensetransactions.createdBy")
												->leftjoin("clients", "clients.id","=","contracts.clientId")
												->leftjoin("depots", "depots.id","=","contracts.depotId")
												->select($select_args)->limit($length)->offset($start)->get();
				$total = \ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
								->whereRaw('(expensetransactions.inchargeId in('.$emp_arr_str.'))')
								->whereIn("expensetransactions.inchargeId",$incharge_arr)
								->whereIn("expensetransactions.workFlowStatus",$logstatus_arr)
								->whereRaw('(expensetransactions.branchId in('.$emp_branches_str.') or expensetransactions.contractId in('.$emp_contracts_str.'))')
								->whereBetween("date",array($frmDt,$toDt))
								->count();
				foreach ($entities as $entity){
					$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
				}
			}
			else{
				if($values["logstatus"] != "All"){
					//$qry->where("purchase_orders.workFlowStatus","=",$values["logstatus"]);
					$logstatus_arr [] =  $values["logstatus"];
				}
				else{
					$logstatus_arr = array("Requested","Sent for Approval","Approved","Rejected","Hold");
				}
				$incharge_arr = array();
				if($values["incharge"] == 0){
					$incharges = \InchargeAccounts::All();
					foreach ($incharges as $incharge){
						$incharge_arr[] = $incharge->empid;
					}
				}
				else{
					$incharge_arr[] =  $values["incharge"];
				}
				$entities = \ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
												->whereIn("expensetransactions.createdBy",$createdby_arr)
												->whereIn("expensetransactions.workFlowStatus",$logstatus_arr)
												->whereIn("expensetransactions.inchargeId",$incharge_arr)
												->whereRaw('(expensetransactions.branchId in('.$emp_branches_str.') or expensetransactions.contractId in('.$emp_contracts_str.'))')
												->whereBetween("date",array($frmDt,$toDt))
												->leftjoin("officebranch", "officebranch.id","=","expensetransactions.branchId")
												->leftjoin("lookuptypevalues", "lookuptypevalues.id","=","expensetransactions.lookupValueId")
												->leftjoin("contracts", "contracts.id","=","expensetransactions.contractId")
												->leftjoin("employee", "employee.id","=","expensetransactions.inchargeId")
												->leftjoin("employee as employee2", "employee2.id","=","expensetransactions.createdBy")
												->leftjoin("clients", "clients.id","=","contracts.clientId")
												->leftjoin("depots", "depots.id","=","contracts.depotId")
												->select($select_args)->limit($length)->offset($start)->get();
				$total = \ExpenseTransaction::where("expensetransactions.status","=","ACTIVE")
								->whereIn("expensetransactions.inchargeId",$incharge_arr)
								->whereIn("expensetransactions.workFlowStatus",$logstatus_arr)
								->whereRaw('(expensetransactions.branchId in('.$emp_branches_str.') or expensetransactions.contractId in('.$emp_contracts_str.'))')
								->whereBetween("date",array($frmDt,$toDt))
								->count();
				foreach ($entities as $entity){
					$entity["date"] = date("d-m-Y",strtotime($entity["date"]));
				}
			}
			$i = 0;
			$vehicles =  \Vehicle::All();
			$veh_arr = array();
			foreach ($vehicles as $vehicle){
				$veh_arr[$vehicle->id] = $vehicle->veh_reg;
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
				if($entity["workFlowStatus"] == "Sent for Approval"){
					$entity["workFlowRemarks"] = '<label> <input name="remarks[]" type="text" class=""></label>';
				}
				else{
					$entity["workFlowRemarks"] = $entity["workFlowRemarks"].'<input name="remarks[]" type="hidden" class="">';
				}
				if($entity["workFlowStatus"] == "Requested"){
					$entity["workFlowStatus"] = 'pending for approval';
				}
				$entity["inchargeId"] = $entity["inchargeId"]." (".$entity["empCode"].")";
				if($entity["lookupValueId"]>900){
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
				$data_values = array_values($entity);
				$actions = $values['actions'];
				$action_data = "";
				$bde = new BlockDataEntryController();
				$values1 = array("branch"=>$entity["branch"],"date"=>$entity["date"]);
				$valid = $bde->verifyTransactionDateandBranchLocally($values1);
				$action_data = "";
				if($entity["workFlowStatus"] != "Approved"){
				$login_user = \Auth::user()->fullName;
					if((isset($entity["createdBy"]) && in_array($entity["createdById"],$createdby_arr))|| (isset($entity["createdBy"]) && in_array(507, $this->jobs))){
						$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="checkbox" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
					}
					else{
						$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
					}
				}
				else{
					$action_data = '<input type="hidden" name="recid[]" value='.$entity["id"].' /> <label> <input name="action[]" type="hidden" class="ace" value="'.$i.'"> <span class="lbl">&nbsp;</span></label>';
				}
				$data_values[10] = $action_data;
				$data[] = $data_values;
				$i++;
			}
			return array("total"=>$total, "data"=>$data);
		}
	}
}


