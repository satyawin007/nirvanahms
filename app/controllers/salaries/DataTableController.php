<?php namespace salaries;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
class DataTableController extends \Controller {

	/**
	 * add a new city.
	 *
	 * @return Response
	 */
	public function getDataTableData()
	{
		$values = Input::All();
		$start = $values['start'];
		$length = $values['length'];
		$total = 0;
		$data = array();
		if(isset($values["name"]) && $values["name"]=="salaryadvances") {
			$ret_arr = $this->getSalaryAdvances($values, $length, $start);
			$total = $ret_arr["total"];
			$data = $ret_arr["data"];
		}
		if(isset($values["name"]) && $values["name"]=="leaves") {
			$ret_arr = $this->getLeaves($values, $length, $start);
			$total = $ret_arr["total"];
			$data = $ret_arr["data"];
		}
		else if(isset($values["name"]) && isset($values["daterange"]) && $values["name"]=="localtrips") {
			$ret_arr = $this->getLocalTrips($values, $length, $start);
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
	
	private function getSalaryAdvances($values, $length, $start){
		$total = 0;
		$data = array();
		$select_args = array();
		$select_args[] = "employee.empCode as empId";
		$select_args[] = "employee.fullName as empname";
		$select_args[] = "empdueamount.amount as amount";
		$select_args[] = "officebranch.name as branchId";
		$select_args[] = "empdueamount.paymentDate as paymentDate";
		$select_args[] = "empdueamount.paymentType as paymentType";
		$select_args[] = "empdueamount.comments as comments";
		$select_args[] = "empdueamount.status as status";
		$select_args[] = "empdueamount.id as id";
		$select_args[] = "employee.id as empId1";
		$select_args[] = "empdueamount.chequeNumber as chequeNumber";
		$select_args[] = "empdueamount.bankAccount as bankAccount";
		$select_args[] = "empdueamount.createdBy as createdById";
		$select_args[] = "empdueamount.createdBy as createdBy";
		$select_args[] = "empdueamount.workFlowStatus as workFlowStatus";
		
		$actions = array();
		$jobs = \Session::get("jobs");
		if(in_array(341, $jobs)){
			$action = array("url"=>"#edit", "type"=>"modal", "css"=>"primary", "js"=>"modalEditSalaryAdvance(", "jsdata"=>array("id"), "text"=>"EDIT");
			$actions[] = $action;
			$action = array("url"=>"#","css"=>"danger", "id"=>"deleteSalryAdvance", "type"=>"", "text"=>"DELETE");
			$actions[] = $action;
			//$action = array("url"=>"printadvancevoucher?", "type"=>"", "css"=>"info", "js"=>"modalEditAssignedValues(", "jsdata"=>array("id","empId", "empname", "amount", "branchId", "paymentDate", "comments", "status"), "text"=>"PRINT");
			//$actions[] = $action;
		}
		$values["actions"] = $actions;
		
		$drivers = \Employee::where("roleId","=",19)->orWhere("roleId","=",20)->get();
		$drivers_arr = array();
		foreach($drivers as $driver){
			$drivers_arr[$driver->id] = $driver->fullName;
		}
		$vehicles = \Vehicle::All();
		$vehicles_arr = array();
		foreach($vehicles as $vehicle){
			$vehicles_arr[$vehicle->id] = $vehicle->veh_reg;
		}
	
		$search = $_REQUEST["search"];
		$search = $search['value'];
		if($search != ""){
			$sql = \EmpDueAmount::where("employee.fullName", "like", "%$search%")
										->leftjoin("employee", "employee.id","=","empdueamount.empId")
										->leftjoin("officebranch","officebranch.id","=","empdueamount.branchId")
										->where("empdueamount.status","=","ACTIVE");
										
			if(isset($values["type"]) && $values["type"]=="nonoffice"){
				$sql = $sql->where("roleId","=",19)->orWhere("roleId","=",20);
			}
			else{
				$sql = $sql->where("roleId","!=",19)->where("roleId","!=",20);
			}
			$total = $sql->where("employee.fullName", "like", "%$search%")->limit($length)->count();
			$entities	= $sql->select($select_args)->limit($length)->offset($start)->get();			
			
		}
		else{
			$sql = \EmpDueAmount::leftjoin("employee", "employee.id","=","empdueamount.empId")
									->leftjoin("officebranch","officebranch.id","=","empdueamount.branchId")
									->where("empdueamount.status","=","ACTIVE");
			if(isset($values["type"]) && $values["type"]=="nonoffice"){
				$sql = $sql->where("roleId","=",19)->orWhere("roleId","=",20);
			}
			else{
				$sql = $sql->where("roleId","!=",19)->where("roleId","!=",20);
			}
			$total = $sql->select($select_args)->limit($length)->count();
			$entities = $sql->select($select_args)->limit($length)->offset($start)->get();			
			
		}
	
		$entities = $entities->toArray();
		foreach($entities as $entity){
			$entity["paymentDate"] = date("d-m-Y",strtotime($entity["paymentDate"]));
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
				else if($action['url'] == "#"){
					$action_data = $action_data."<button class='btn btn-minier btn-".$action["css"]."' onclick='".$action["id"]."(".$entity["id"].")' >".strtoupper($action["text"])."</button>&nbsp; &nbsp;" ;
				}
				else {
					$action_data = $action_data."<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."&id=".$entity['id']."'>".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
				}
			}
			$login_user = \Auth::user()->id;
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
			$data_values[8] = $action_data;
			$data[] = $data_values;
		}
		return array("total"=>$total, "data"=>$data);
	}
	
	private function getLeaves($values, $length, $start){
		$total = 0;
		$data = array();
			
		$actions = array();
		$jobs = \Session::get("jobs");
		if(in_array(343, $jobs)){
			$action = array("url"=>"#edit", "type"=>"modal", "css"=>"primary", "js"=>"modalEditLeave(", "jsdata"=>array("id"), "text"=>"EDIT");
			$actions[] = $action;
			//$action = array("url"=>"approve","css"=>"success", "type"=>"js", "text"=>"Approve");
			//$actions[] = $action;
			//$action = array("url"=>"reject","css"=>"danger", "type"=>"js",  "text"=>"Reject");
			//$actions[] = $action;
		}
		$values["actions"] = $actions;
			
		$select_args = array();
		$select_args[] = "employee.empCode as empId";
		$select_args[] = "employee.fullName as name";
		$select_args[] = "officebranch.name as branch";
		$select_args[] = "leaves.fromDate as fromDate";
		$select_args[] = "leaves.fromMrngEve as fromMrngEve";
		$select_args[] = "leaves.toDate as toDate";
		$select_args[] = "leaves.toMrngEve as toMrngEve";
		$select_args[] = "leaves.noOfLeaves as noOfLeaves";
		$select_args[] = "leaves.leavesTaken as leavesTaken";
		$select_args[] = "leaves.workFlowStatus as status";
		$select_args[] = "leaves.workFlowRemarks as workFlowRemarks";
		$select_args[] = "leaves.id as id";
	
		$search = $_REQUEST["search"];
		$search = $search['value'];
		if($search != ""){
			$fincomids = \Employee::where("fullName", "like", "%$search%")->select("id")->get();
			$fincomids_arr = array();
			foreach($fincomids as $fincomid){
				$fincomids_arr[] = $fincomid->id;
			}
			$entities = \Leaves::whereIn("empId", $fincomids_arr)->leftjoin("employee","employee.id","=","leaves.empId")->leftjoin("officebranch","officebranch.id","=","leaves.branchId")->select($select_args)->limit($length)->offset($start)->get();
			$total = \Leaves::whereIn("empId", $fincomids_arr)->count();
		}
		else{
			$entities = \Leaves::where("leaves.id",">",0)
							->where("leaves.createdBy","=",Auth::user()->id)
							->leftjoin("employee","employee.id","=","leaves.empId")
							->leftjoin("officebranch","officebranch.id","=","leaves.branchId")
							->select($select_args)->limit($length)->offset($start)->get();
			$total = \Leaves::count();
		}
		$entities = $entities->toArray();
		foreach($entities as $entity){
			$entity["fromDate"] = date("d-m-Y",strtotime($entity["fromDate"]));
			$entity["toDate"] = date("d-m-Y",strtotime($entity["toDate"]));
			$data_values = array_values($entity);
			$actions = $values['actions'];
			$action_data = "";
			foreach($actions as $action){
				$sts = "";
				if($action["text"] == "Approve"){
					$sts = "Approved";
				}
				if($action["text"] == "Reject"){
					$sts = "Rejected";
				}
				if($entity["status"] != $sts){
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
					else if($action["type"]=="js"){
						$action_data = $action_data."<a class='btn btn-minier btn-".$action["css"]."' onclick='".$action['url']."(".$entity['id'].")'>".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
					}
					else{
						$action_data = $action_data."<a class='btn btn-minier btn-".$action["css"]."' href='".$action['url']."&id=".$entity['id']."'>".strtoupper($action["text"])."</a>&nbsp; &nbsp;" ;
					}
				}
			}
			$data_values[11] = $action_data;
			$data[] = $data_values;
		}
		return array("total"=>$total, "data"=>$data);
	}
}


