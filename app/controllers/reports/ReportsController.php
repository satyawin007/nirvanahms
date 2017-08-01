<?php namespace reports;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use settings\AppSettingsController;
use masters\OfficeBranchController;
class ReportsController extends \Controller {

	
	
	/**
	 * add a new state.
	 *
	 * @return Response
	 */
	
	public function getLoansByFinance(){
		$values = Input::All();
		$options_data = "";
		$options_data = $options_data."<option value='0'>ALL LOANS</option>";
		$loans =  \Loan::leftJoin("financecompanies","financecompanies.id","=","loans.financeCompanyId")
							->where("loans.status","=","ACTIVE")
							->where("financecompanies.id","=",$values["id"])
							->select(array("loans.id","loans.loanNo","loans.vehicleId","financecompanies.name as finName"))->get();
		foreach ($loans as $loan){
			$vehs = "";
			if($loan->vehicleId != ""){
				$veh_arr = explode(",", $loan->vehicleId);
				$vehicles = \Vehicle::whereIn("id",$veh_arr)->get();
				$i = 0;
				for($i=0; $i<count($vehicles); $i++){
					if($i+1 == count($vehicles)){
						$vehs = $vehs.$vehicles[$i]->veh_reg;
					}
					else{
						$vehs = $vehs.$vehicles[$i]->veh_reg.", ";
					}
				}
			}
			$options_data = $options_data."<option value='".$loan->id."'>".$loan->loanNo." - ".$loan->finName." (".$vehs.")"."</option>";
		}
		echo $options_data;
	}
	public function getFinance(){
		$values = Input::All();
		$options_data = "";
		if($values["loantype"] == "unsecure_loans"){
			$loans =  \Loan::where("loans.purpose","=","UNSECURE LOANS")
							->leftJoin("financecompanies","financecompanies.id","=","loans.financeCompanyId")
							->where("loans.status","=","ACTIVE")
							->select(array("financecompanies.id as id","financecompanies.name as finName"))
							->groupby("financecompanies.id")->get();
		}
		else{
			$loans =  \Loan::where("loans.purpose","!=","UNSECURE LOANS")
							->leftJoin("financecompanies","financecompanies.id","=","loans.financeCompanyId")
							->where("loans.status","=","ACTIVE")
							->select(array("financecompanies.id as id","financecompanies.name as finName"))
							->groupby("financecompanies.id")
							->get();
		}
		$options_data = $options_data."<option >--select finance--</option>";
		foreach ($loans as $loan){
			$options_data = $options_data."<option value='".$loan->id."'>".$loan->finName."</option>";
		}
		echo $options_data;
	}
	
	public function carryForward()
	{
		$values = Input::All();
		$values["type"] = "194";
		$nextDay = strtotime(date("Y-m-d", strtotime($values["date1"])) . " +1 day");
		$nextDay = date ( 'Y-m-d' , $nextDay );
		$values["remarks"] = "C/F from ".$values["date1"];
		$values["date1"] = $nextDay;
		$values["paymenttype"] = "cash";
		
		$cf_details = \IncomeTransaction::where("branchId","=",$values["branch"])->where("date","=",$nextDay)->where("status","=","ACTIVE")->where("lookupValueId","=","194")->get();
		if(count($cf_details)>0){
			$cf_details = $cf_details[0];
			$values["amount"] = $cf_details->amount+$values["amount"];
			$field_names = array("branch"=>"branchId","amount"=>"amount","paymenttype"=>"paymentType", "transtype"=>"name", "type"=>"lookupValueId",
					"branch1"=>"branchId1","incharge"=>"inchargeId","employee"=>"employeeId","vehicle"=>"vehicleIds", "bankId"=>"bankId",
					"remarks"=>"remarks","bankaccount"=>"bankAccount","chequenumber"=>"chequeNumber","issuedate"=>"issueDate",
					"transactiondate"=>"transactionDate", "suspense"=>"suspense", "date1"=>"date","accountnumber"=>"accountNumber","bankname"=>"bankName"
			);
			$fields = array();
			foreach ($field_names as $key=>$val){
				if(isset($values[$key])){
					$fields[$val] = $values[$key];
				}
			}
			$fields["name"] = "income";
			$db_functions_ctrl = new DBFunctionsController();
			$table = "IncomeTransaction";
			$data = array("id"=>$cf_details->transactionId);
			if($db_functions_ctrl->updatetrans($table, $fields, $data)){
				echo "success";
				return;
			}
			else{
				echo "fail";
				return;
			}
		}
		$field_names = array("branch"=>"branchId","amount"=>"amount","paymenttype"=>"paymentType", "transtype"=>"name", "type"=>"lookupValueId",
				"branch1"=>"branchId1","incharge"=>"inchargeId","employee"=>"employeeId","vehicle"=>"vehicleIds", "bankId"=>"bankId",
				"remarks"=>"remarks","bankaccount"=>"bankAccount","chequenumber"=>"chequeNumber","issuedate"=>"issueDate",
				"transactiondate"=>"transactionDate", "suspense"=>"suspense", "date1"=>"date","accountnumber"=>"accountNumber","bankname"=>"bankName"
		);
		$fields = array();
		foreach ($field_names as $key=>$val){
			if(isset($values[$key])){
				$fields[$val] = $values[$key];
			}
		}
		$transid =  strtoupper(uniqid().mt_rand(100,999));
		$chars = array("a"=>"1","b"=>"2","c"=>"3","d"=>"4","e"=>"5","f"=>"6");
		foreach($chars as $k=>$v){
			$transid = str_replace($k, $v, $transid);
		}
		$fields["transactionId"] = $transid;
		$fields["source"] = "income transaction";
		$db_functions_ctrl = new DBFunctionsController();
		$table = "IncomeTransaction";
		if($db_functions_ctrl->insert($table, $fields)){
			echo "success";
			return;
		}
		else{
			echo "fail";
			return;
		}
		
	}
	
	public function processBranchSuspense(){
		$values = Input::all();
		$field_names = array("reportbranchid"=>"branchId","reportdate"=>"reportDate","itreportdate"=>"itReportDate", "acbookingincome"=>"bookings_income", "acbookingscancel"=>"bookings_cancel",
				"accargossimplyincome"=>"cargos_simply_income","accargossimplycancel"=>"cargos_simply_cancel","acotherincome"=>"other_income","actotalincome"=>"total_income", "actotalexpense"=>"total_expense",
				"acdepositamount"=>"bank_deposit","acbranchdeposit"=>"branch_deposit","actodaysuspense"=>"today_suspense","adjustedamount"=>"adjusted_amount",
				"verstatus"=>"verification_status", "vercomments"=>"comments"
		);
		$fields = array();
		foreach ($field_names as $key=>$val){
			if(isset($values[$key])){
				if($key == "reportdate" || $key == "itreportdate" ){
					$fields[$val] = date("Y-m-d",strtotime($values[$key]));
				}
				else {
					$fields[$val] = $values[$key];
				}
			}
		}
		
		$branch_suspense = \BranchSuspenseReport::where("branchId","=",$fields["branchId"])->where("reportDate","=",$fields["reportDate"])->get();
		if(count($branch_suspense)>0){
			$db_functions_ctrl = new DBFunctionsController();
			$table = "BranchSuspenseReport";
			$data = array("branchId"=>$fields["branchId"],"reportDate"=>$fields["reportDate"]);
			if($db_functions_ctrl->updatesuspense($table, $fields, $data)){
				\Session::put("message","Operation completed Successfully");
				return \Redirect::to("report?reporttype=dailysettlement");
			}
			else{
				\Session::put("message","Operation Could not be completed, Try Again!");
				return \Redirect::to("report?reporttype=dailysettlement");
			}
		}
		
		$db_functions_ctrl = new DBFunctionsController();
		$table = "BranchSuspenseReport";
		if($db_functions_ctrl->insert($table, $fields)){
			\Session::put("message","Operation completed Successfully");
			return \Redirect::to("report?reporttype=dailysettlement");
		}
		else{
			\Session::put("message","Operation Could not be completed, Try Again!");
			return \Redirect::to("report?reporttype=dailysettlement");
		}
	}

	
	/**
	 * add a new state.
	 *
	 * @return Response
	 */
	
	public function getReport()
	{
		$values = Input::all();
		if(isset($values["reporttype"]) && $values["reporttype"] == "dailytransactions"){
			return $this->getDailyTransactiosReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "dailytransactionsofemployee"){
			return $this->getDailyTransactiosEmployeeReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "dailysettlement"){
			return $this->getDailySettlementReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "dailysettlementreport"){
			return $this->getDailySettlementReportsReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "monthlyreportsheet"){
			return $this->getMonthlyFuelReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "dailyfinancedetailed"){
			return $this->getDailyFinanceDetailedReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "fuel"){
			return $this->getFuelReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "creditsupplier"){
			return $this->getCreditSupplierReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "salaryadvances"){
			return $this->getSalaryAdvancesReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "salary"){
			return $this->getSalaryReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "estimatedsalary"){
			return $this->getEstimateSalaryReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "attendence"){
			return $this->getAttendenceReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "attendencenew"){
			return $this->getAttendenceNewReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "employeeinfo"){
			return $this->getEmployeeInfoReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "attendencedetailed"){
			return $this->getAttendenceDetailedReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "inchargetransactions"){
			return $this->getInchargeTransactionsReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "loans"){
			return $this->getLoansReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "dailyfinance"){
			return $this->getDailyFinanceReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "bankposition"){
			return $this->getBankPositionReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "stockpurchase"){
			return $this->getStockPurchaseReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "vehiclestockhistory"){
			return $this->getVehicleStockHistoryReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "inventory"){
			return $this->getInventoryReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "officeinventory"){
			return $this->getOfficeInventoryReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "repairstock"){
			return $this->getRepairStockReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "loginlog"){
			return $this->getLoginLogInfo($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "vehiclemileage"){
			return $this->getVehicleMileage($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "vehicleperformance"){
			return $this->getVehiclePerformance($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "vehicletrackingreport"){
			return $this->getVehicleTracking($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "clientvehicletrips"){
			return $this->getClientVehicleTrips($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "servicelog"){
			return $this->getServiceLog($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "clientholidaysworking"){
			return $this->getClientHolidaysWorking($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "employeemainloginlog"){
			return $this->getEmployeeMainLoginLogInfo($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "vehiclemileage_full"){
			return $this->getVehicleMileageFullReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "holidaysrunningreport"){
			return $this->getHolidaysRunningReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "extrakmsreport"){
			return $this->getExtraKmsReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "contractvehiclesreport"){
			return $this->getContractVehiclesReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "vendorpaymentsreport"){
			return $this->getVendorPaymentsReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "insurancereport"){
			return $this->getInsuranceReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "globalloansreport"){
			return $this->getGlobalLoansReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "vehiclerenewalsreport"){
			return $this->getVehicleRenewalsReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "cardspaymentreport"){
			return $this->getCardsPaymentReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "vehicleincome"){
			return $this->vehicleincomeReport($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "report1"){
			return $this->report1($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "report2"){
			return $this->report2($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "report3"){
			return $this->report3($values);
		}
		if(isset($values["reporttype"]) && $values["reporttype"] == "report4"){
			return $this->report4($values);
		}
	}
	
	private function getDailyTransactiosReport($values){
		if (\Request::isMethod('post'))
		{
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$brachId = $values["branch"];
			$empId = "-1";
			if(isset($values["employee"])){
				$empId = $values["employee"];
			}
			$reportFor = "-1";
			if(isset($values["reportfor"])){
				$reportFor = $values["reportfor"];
			}
			$resp = array();
			if($values["btntype"] == "ticket_corgos_summery"){
				if($brachId == 0){
					$branches =  \OfficeBranch::OrderBy("name")->get();
					foreach ($branches as $branch){
						$recs = DB::select( DB::raw("select lookupValueId,sum(amount) as amt from incometransactions where paymentType='CASH' and  branchId=".$branch->id." and date between '".$frmDt."' and '".$toDt."' group by lookupValueId order By lookupValueId"));
						if(count($recs)>0) {
							$row = array();
							$row["branch"] = "<a href='#edit' data-toggle='modal' onclick=\"modalGetInfo(".$branch->id.", '".$frmDt."', '".$toDt."', ".$empId.", '".$reportFor."')\" title='get report details'>".$branch->name."</a>";
							$totalAmt = 0;
							foreach ($recs as $rec){
								if($rec->lookupValueId==85){
									$row["tickets"] = $rec->amt;
									$totalAmt = $totalAmt+$rec->amt;
								}
								if($rec->lookupValueId==86){
									$row["ticketcancel"] = $rec->amt;
									$totalAmt = $totalAmt+$rec->amt;
								}
								if($rec->lookupValueId==87){
									$row["cargosimply"] = $rec->amt;
									$totalAmt = $totalAmt+$rec->amt;
								}
								if($rec->lookupValueId==-1){
									$row["cargosimplycancel"] = $rec->amt;
									$totalAmt = $totalAmt+$rec->amt;
								}
							}
							if(!isset($row["tickets"])){
								$row["tickets"] = 0;
							}
							if(!isset($row["ticketcancel"])){
								$row["ticketcancel"] = 0;
							}
							if(!isset($row["cargosimply"])){
								$row["cargosimply"] = 0;
							}
							if(!isset($row["cargosimplycancel"])){
								$row["cargosimplycancel"] = 0;
							}
							if(!isset($row["cargos"])){
								$row["cargos"] = 0;
							}
							$row["total"] = $totalAmt;
							$resp[] = $row;
						}
					}
				}
				else if($brachId > 0){
					if($empId>0){
						$recs = DB::select( DB::raw("select lookupValueId,sum(amount) as amt from incometransactions where paymentType='CASH' and createdBy=".$empId." and branchId=".$brachId." and date between '".$frmDt."' and '".$toDt."' group by lookupValueId order By lookupValueId"));
						if(count($recs)>0) {
							$row = array();
							$brachName = \OfficeBranch::where("id","=",$brachId)->first();
							$brachName = $brachName->name;
							$row["branch"] = "<a href='#edit' data-toggle='modal' onclick=\"modalGetInfo(".$brachId.", '".$frmDt."', '".$toDt."', ".$empId.", '".$reportFor."')\" title='get report details'>".$brachName."</a>";
							$totalAmt = 0;
							foreach ($recs as $rec){
								if($rec->lookupValueId==85){
									$row["tickets"] = $rec->amt;
									$totalAmt = $totalAmt+$rec->amt;
								}
								if($rec->lookupValueId==86){
									$row["ticketcancel"] = $rec->amt;
									$totalAmt = $totalAmt+$rec->amt;
								}
								if($rec->lookupValueId==87){
									$row["cargosimply"] = $rec->amt;
									$totalAmt = $totalAmt+$rec->amt;
								}
								if($rec->lookupValueId==-1){
									$row["cargosimplycancel"] = $rec->amt;
									$totalAmt = $totalAmt+$rec->amt;
								}
							}
							if(!isset($row["tickets"])){
								$row["tickets"] = 0;
							}
							if(!isset($row["ticketcancel"])){
								$row["ticketcancel"] = 0;
							}
							if(!isset($row["cargosimply"])){
								$row["cargosimply"] = 0;
							}
							if(!isset($row["cargosimplycancel"])){
								$row["cargosimplycancel"] = 0;
							}
							if(!isset($row["cargos"])){
								$row["cargos"] = 0;
							}
							$row["total"] = $totalAmt;
							$resp[] = $row;
						}
					}
					else {
						$recs = DB::select( DB::raw("select lookupValueId,sum(amount) as amt from incometransactions where paymentType='CASH' and  branchId=".$brachId." and date between '".$frmDt."' and '".$toDt."' group by lookupValueId order By lookupValueId"));
						if(count($recs)>0) {
							$row = array();
							$brachName = \OfficeBranch::where("id","=",$brachId)->first();
							$brachName = $brachName->name;
							$row["branch"] = "<a href='#edit' data-toggle='modal' onclick=\"modalGetInfo(".$brachId.", '".$frmDt."', '".$toDt."', ".$empId.", '".$reportFor."')\" title='get report details'>".$brachName."</a>";
							$totalAmt = 0;
							foreach ($recs as $rec){
								if($rec->lookupValueId==85){
									$row["tickets"] = $rec->amt;
									$totalAmt = $totalAmt+$rec->amt;
								}
								if($rec->lookupValueId==86){
									$row["ticketcancel"] = $rec->amt;
									$totalAmt = $totalAmt+$rec->amt;
								}
								if($rec->lookupValueId==87){
									$row["cargosimply"] = $rec->amt;
									$totalAmt = $totalAmt+$rec->amt;
								}
								if($rec->lookupValueId==-1){
									$row["cargosimplycancel"] = $rec->amt;
									$totalAmt = $totalAmt+$rec->amt;
								}
							}
							if(!isset($row["tickets"])){
								$row["tickets"] = 0;
							}
							if(!isset($row["ticketcancel"])){
								$row["ticketcancel"] = 0;
							}
							if(!isset($row["cargosimply"])){
								$row["cargosimply"] = 0;
							}
							if(!isset($row["cargosimplycancel"])){
								$row["cargosimplycancel"] = 0;
							}
							if(!isset($row["cargos"])){
								$row["cargos"] = 0;
							}
							$row["total"] = $totalAmt;
							$resp[] = $row;
						}
					}
				}
			}
			else if($values["btntype"] == "branch_summery"){
				DB::statement(DB::raw("CALL branch_summary_report('".$frmDt."', '".$toDt."');"));
				if(true){
					if ($brachId == 0){
						$branches =  \OfficeBranch::OrderBy("name")->get();
					}
					else{
						$branches =  \OfficeBranch::where("id","=",$brachId)->OrderBy("name")->get();
					}
					foreach ($branches as $branch){
						$row = array();
						$recs = DB::select( DB::raw("select sum(amount) as amt from temp_branch_summary where type!=243 and  transactiontype ='incometransactions' and branchId=".$branch->id." and date between '".$frmDt."' and '".$toDt."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$row["branch"] = $branch->name;
							$row["income"] = $rec->amt;
						}
						$recs = DB::select( DB::raw("select sum(amount) as amt from temp_branch_summary where type=243 and  transactiontype ='incometransactions' and  branchId=".$branch->id." and date between '".$frmDt."' and '".$toDt."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$row["amtreceived"] = $rec->amt;
						}
						$recs = DB::select( DB::raw("select sum(amount) as amt from temp_branch_summary where type!=123 and  transactiontype ='expensetransactions' and type!=125 and  branchId=".$branch->id." and date between '".$frmDt."' and '".$toDt."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$row["expense"] = $rec->amt;
						}
						$recs = DB::select( DB::raw("select sum(amount) as amt from temp_branch_summary where type=123 and  transactiontype ='expensetransactions' and  branchId=".$branch->id." and date between '".$frmDt."' and '".$toDt."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$row["amtdeposited"] = $rec->amt;
						}
						$recs = DB::select( DB::raw("select sum(amount) as amt from temp_branch_summary where type=125 and  transactiontype ='expensetransactions' and  branchId=".$branch->id." and date between '".$frmDt."' and '".$toDt."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$row["bank_deposits"] = $rec->amt;
						}
						if ($row["income"] != 0 || $row["amtreceived"] !=0 || $row["expense"] !=0 || $row["amtdeposited"] !=0 || $row["bank_deposits"] !=0){
							$income = $row["income"]+$row["amtreceived"];
							$expens = $row["expense"]+$row["amtdeposited"]+$row["bank_deposits"];
							$row["balance"] = $income - $expens ;
							$resp[] = $row;
						}
					}
				}
			}
			else if($values["btntype"] == "txn_details"){
				$employee_name = "";
				if($empId>0){
					$employee_name = \Employee::where("id","=",$empId)->first();
					$employee_name = $employee_name->fullName;
				}
				
				DB::statement(DB::raw("CALL daily_transactions_report('".$frmDt."', '".$toDt."');"));
				if($brachId == 0 && $reportFor=="0"){
					if($empId>0){
						$recs = DB::select( DB::raw("select * from temp_daily_transaction where createdBy='".$employee_name."' order by branchId"));
					}
					else{
						$recs = DB::select( DB::raw("select * from temp_daily_transaction order by branchId"));
					}
				}
				else if($brachId > 0 && $reportFor=="0"){
					if($empId>0){
						$recs = DB::select( DB::raw("select * from temp_daily_transaction where branchId=".$brachId." and createdBy='".$employee_name."' order by branchId"));
					}
					else{
						$recs = DB::select( DB::raw("select * from temp_daily_transaction where branchId=".$brachId." order by branchId"));
					}
				}
				else if($brachId > 0 && $reportFor != "0"){
					if($empId>0){
						$recs = DB::select( DB::raw("select * from temp_daily_transaction where branchId=".$brachId." and name='".$reportFor."' and createdBy='".$employee_name."' order by branchId"));
					}
					else{
						$recs = DB::select( DB::raw("select * from temp_daily_transaction where branchId=".$brachId." and name='".$reportFor."' order by branchId"));
					}
				}
				else if($brachId > 0 && $reportFor != "0"){
					if($empId>0){
						$recs = DB::select( DB::raw("select * from temp_daily_transaction where branchId=".$brachId." and name='".$reportFor."' and createdBy='".$employee_name."' order by branchId"));
					}
					else{
						$recs = DB::select( DB::raw("select * from temp_daily_transaction where branchId=".$brachId." and name='".$reportFor."' and createdBy='".$employee_name."' order by branchId"));
					}
				}
				else if($brachId == 0 && $reportFor != "0"){
					if($empId>0){
						$recs = DB::select( DB::raw("select * from temp_daily_transaction where  name='".$reportFor."' order by branchId"));
					}
					else {
						$recs = DB::select( DB::raw("select * from temp_daily_transaction where  name='".$reportFor."' order by branchId"));
					}
				}
				if(count($recs)>0) {
					$totalAmt = 0;
					foreach ($recs as $rec){
						$row = array();
						$brachName = "";
						if($rec->branchId>0){
							$brachName = \OfficeBranch::where("id","=",$rec->branchId)->first();
							$brachName = $brachName->name;
						}
						$row = array();
						$row["branch"] = $brachName;
						$row["type"] = strtoupper($rec->type);
						$row["date"] = date("d-m-Y",strtotime($rec->date));
						$row["amount"] = $rec->amount;
						$row["purpose"] = strtoupper($rec->name);
						if($rec->lookupValueId==999){
							if($rec->entityValue>0){
								$prepaidName = \LookupTypeValues::where("id","=",$rec->entityValue)->first();
								$prepaidName = $prepaidName->name;
								$row["purpose"] = strtoupper($rec->entity);
								$row["employee"] = $prepaidName;
							}
							else{
								$row["purpose"] = strtoupper($rec->entity);
								$row["employee"] = "";
							}
						}
						else if($rec->lookupValueId==73){
							$bankdetails = \IncomeTransaction::where("transactionId","=",$rec->rowid)->leftjoin("bankdetails","bankdetails.id","=","incometransactions.bankId")->first();
							$bankdetails = $bankdetails->bankName." - ".$bankdetails->accountNo;
							$row["employee"] = $bankdetails;
						}
						else if($rec->lookupValueId==84){
							$bankdetails = \ExpenseTransaction::where("transactionId","=",$rec->rowid)->leftjoin("bankdetails","bankdetails.id","=","expensetransactions.bankId")->first();
							$bankdetails = $bankdetails->bankName." - ".$bankdetails->accountNo;
							$row["employee"] = $bankdetails;
						}
						else{
							if($rec->entityValue != "0"){
								$row["employee"] = $rec->entity." - ".$rec->entityValue;
							}
							else{
								$row["employee"] = $rec->entity;
							}
						}
						$row["comments"] = $rec->remarks;
						//$row["billno"] = $rec->billNo;
						$row["createdby"] = $rec->createdBy;
						$totalAmt = $totalAmt+$rec->amount;
						$row["total"] = $totalAmt;
						$resp[] = $row;
					}
				}
			}
			echo json_encode($resp);
			return;
		}
		
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
		
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
		
		$form_fields = array();
		
		$branches =  \OfficeBranch::All();
		$branches_arr = array();
		$branches_arr["0"] = "ALL BRANCHES";
		foreach ($branches as $branch){
			$branches_arr[$branch->id] = $branch->name;
		}
		
		$emps =  \Employee::All();
		$emps_arr = array();
		$emps_arr["0"] = "ALL EMPLOYEES";
		foreach ($emps as $emp){
			$emps_arr[$emp->id] = $emp->fullName;
		}
		
		$parentId = -1;
		$parent = \LookupTypeValues::where("name","=","INCOME")->get();
		if(count($parent)>0){
			$parent = $parent[0];
			$parentId = $parent->id;
		}
		$incomes =  \LookupTypeValues::where("parentId","=",$parentId)->where("status","=","ACTIVE")->get();
		$transtype_arr = array();
		$transtype_arr["0"] = "ALL";
		foreach ($incomes as $income){
			$transtype_arr [$income->name] = strtoupper($income->name);
		}
		
		$parentId = -1;
		$parent = \LookupTypeValues::where("name","=","EXPENSE")->get();
		if(count($parent)>0){
			$parent = $parent[0];
			$parentId = $parent->id;
		}
		$incomes =  \LookupTypeValues::where("parentId","=",$parentId)->where("status","=","ACTIVE")->get();
		foreach ($incomes as $income){
			$transtype_arr [$income->name] = strtoupper($income->name);
		}
		
		$form_field = array("name"=>"branch", "content"=>"branch name", "readonly"=>"",  "required"=>"required","type"=>"select", "action"=>array("type"=>"onchange","script"=>"disableEmployee(this.value)"), "options"=>$branches_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"employee", "content"=>"employee", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$emps_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reportfor", "content"=>"report for ", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$transtype_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
		
		$values["provider"] = "bankdetails";
		return View::make('reports.dailytransactionreport', array("values"=>$values));
	}
	
	
	private function getDailyTransactiosEmployeeReport($values){
		if (\Request::isMethod('post'))
		{
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$brachId = $values["branch"];
			$empId = "-1";
			if(isset($values["employee"])){
				$empId = $values["employee"];
			}
			$reportFor = "-1";
			if(isset($values["reportfor"])){
				$reportFor = $values["reportfor"];
			}
			$resp = array();
			$employees = \Employee::All();
			foreach ($employees as $employee){
				$recs = DB::select( DB::raw("select lookupValueId, sum(amount) as amt from incometransactions where paymentType='CASH' and createdBy=".$employee->id." and branchId=".$brachId." and date between '".$frmDt."' and '".$toDt."' group by lookupValueId order By lookupValueId"));
				if(count($recs)>0) {
					$row = array();
					$row["branch"] = $employee->fullName;
					$totalAmt = 0;
					foreach ($recs as $rec){
						if($rec->lookupValueId==85){
							$row["tickets"] = $rec->amt;
							$totalAmt = $totalAmt+$rec->amt;
						}
						if($rec->lookupValueId==86){
							$row["ticketcancel"] = $rec->amt;
							$totalAmt = $totalAmt+$rec->amt;
						}
						if($rec->lookupValueId==87){
							$row["cargosimply"] = $rec->amt;
							$totalAmt = $totalAmt+$rec->amt;
						}
						if($rec->lookupValueId==-1){
							$row["cargosimplycancel"] = $rec->amt;
							$totalAmt = $totalAmt+$rec->amt;
						}
					}
					if(!isset($row["tickets"])){
						$row["tickets"] = 0;
					}
					if(!isset($row["ticketcancel"])){
						$row["ticketcancel"] = 0;
					}
					if(!isset($row["cargosimply"])){
						$row["cargosimply"] = 0;
					}
					if(!isset($row["cargosimplycancel"])){
						$row["cargosimplycancel"] = 0;
					}
					if(!isset($row["cargos"])){
						$row["cargos"] = 0;
					}
					$row["total"] = $totalAmt;
					$resp[] = $row;
				}
			}
			echo json_encode($resp);
			return;
		}
		
		$values["branch"] = 1;
		$values["fromdate"] = "2015-10-10";
		$values["todate"] = "2016-10-10";
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
	
		$branches =  \OfficeBranch::All();
		$branches_arr = array();
		$branches_arr["0"] = "ALL BRANCHES";
		foreach ($branches as $branch){
			$branches_arr[$branch->id] = $branch->name;
		}
	
		$emps =  \Employee::All();
		$emps_arr = array();
		$emps_arr["0"] = "ALL EMPLOYEES";
		foreach ($emps as $emp){
			$emps_arr[$emp->id] = $emp->fullName;
		}
	
		$parentId = -1;
		$parent = \LookupTypeValues::where("name","=","INCOME")->get();
		if(count($parent)>0){
			$parent = $parent[0];
			$parentId = $parent->id;
		}
		$incomes =  \LookupTypeValues::where("parentId","=",$parentId)->where("status","=","ACTIVE")->get();
		$transtype_arr = array();
		$transtype_arr["0"] = "ALL";
		foreach ($incomes as $income){
			$transtype_arr [$income->name] = strtoupper($income->name);
		}
	
		$parentId = -1;
		$parent = \LookupTypeValues::where("name","=","EXPENSE")->get();
		if(count($parent)>0){
			$parent = $parent[0];
			$parentId = $parent->id;
		}
		$incomes =  \LookupTypeValues::where("parentId","=",$parentId)->where("status","=","ACTIVE")->get();
		foreach ($incomes as $income){
			$transtype_arr [$income->name] = strtoupper($income->name);
		}
	
		$form_field = array("name"=>"branch", "content"=>"branch name", "readonly"=>"",  "required"=>"required","type"=>"select", "action"=>array("type"=>"onchange","script"=>"disableEmployee(this.value)"), "options"=>$branches_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"employee", "content"=>"employee", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$emps_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reportfor", "content"=>"report for ", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$transtype_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
	
		$values["provider"] = "bankdetails";
		return View::make('reports.dailytransactionemployeemodal', array("values"=>$values));
	}
	
	private function getSalaryAdvancesReport($values){
		if (\Request::isMethod('post'))
		{
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			$select_args = array();
			$select_args[] = "employee.fullName as empname";
			$select_args[] = "empdueamount.amount as amount";
			$select_args[] = "empdueamount.paymentDate as paymentDate";
			$select_args[] = "officebranch.name as branch";
			$select_args[] = "empdueamount.comments as remarks";
			$select_args[] = "empdueamount.id as id";
			$emps_arr = array();
			if(isset($values["clientname"]) && isset($values["depot"]) ){
				if(isset($values["depot"]) && $values["depot"]==0){
					DB::statement(DB::raw("CALL contract_driver_helper_all('".$values["clientname"]."');"));
				}
				else{
					DB::statement(DB::raw("CALL contract_driver_helper('".$values["depot"]."', '".$values["clientname"]."');"));
				}
				$entities = DB::select( DB::raw("select * from temp_contract_drivers_helpers group by id"));
				foreach ($entities as $entity){
					$emps_arr[] = $entity->id;
				}
			}
			
			$sql =  \EmpDueAmount::whereRaw(" (empdueamount.status='ACTIVE' or empdueamount.deleted='No') ");
							if(isset($values["employee"]) && $values["employee"]>0){
								$sql->where("empdueamount.empId","=",$values["employee"]);
							}
							else{
								if(isset($values["officebranch"])){
									$sql->whereRaw(" (roleId!=20 and roleId!=19) and FIND_IN_SET('".$values["officebranch"]."',employee.officeBranchIds)");
								}
								else{
									$sql->whereIn("empdueamount.empId",$emps_arr);
								}
							}
							$sql->whereBetween("paymentDate",array($frmDt,$toDt))
							->leftjoin("employee","employee.id","=","empdueamount.empId")
							->leftjoin("officebranch","officebranch.id","=","empdueamount.branchId");
			$salaryadvances =   $sql->OrderBy("paymentDate")->select($select_args)->get();
			$totaladvances = 0;
			$totalreturns = 0;
			foreach ($salaryadvances as $salaryadvance){
				$row = array();
				$row["empname"] = $salaryadvance->empname;
				if($salaryadvance->amount>0){
					$totaladvances = $totaladvances+$salaryadvance->amount;
					$row["amount"] = "<span style='color:green'> ".$salaryadvance->amount."</span>";
				}
				else{
					$totalreturns = $totaladvances+$totalreturns;
					$row["amount"] = "<span style='color:red'> ".$salaryadvance->amount."</span>";
				}
				$row["paymentDate"] = date("d-m-Y",strtotime($salaryadvance->paymentDate));
				$row["branch"] = $salaryadvance->branch;
				$row["remarks"] = $salaryadvance->remarks;
				$row["id"] = $salaryadvance->id;
				$resp[] = $row;
			}
			echo json_encode($resp);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$branches_arr = array();
		$branches = \OfficeBranch::where("status","=","ACTIVE")->get();
		foreach ($branches as $branch){
			$branches_arr[$branch->id] = $branch->name;
		}
		
		$clients =  \Client::where("status","=","ACTIVE")->get();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
		
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		if(isset($values["type"]) && $values["type"]=="office"){
			$form_field = array("name"=>"officebranch","content"=>"office branch", "readonly"=>"","required"=>"", "type"=>"select", "options"=>$branches_arr, "action"=>array("type"=>"onChange", "script"=>"getEmployeesByOffice(this.value);"), "class"=>"form-control chosen-select");
			$form_fields[] = $form_field;
		}
		else{
			$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
			$form_fields[] = $form_field;
			$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"getEmployeesByDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>array());
			$form_fields[] = $form_field;
		}
		$form_field = array("name"=>"employee", "content"=>"report for ", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>array("0"=>"ALL"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		
		$add_form_fields = array();
		$emps =  \Employee::where("roleId","=",19)->get();
		$emps_arr = array();
		$emps_arr["0"] = "ALL DRIVERS";
		foreach ($emps as $emp){
			$emps_arr[$emp->id] = $emp->fullName;
		}
		
		$vehs =  \Vehicle::where("status","=","ACTIVE")->get();
		$vehs_arr = array();
		foreach ($vehs as $veh){
			$vehs_arr[$veh->id] = $veh->veh_reg;
		}
		$form_field = array("name"=>"fuelstation", "content"=>"by station", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$branches_arr, "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;
		$form_field = array("name"=>"driver", "content"=>"by driver", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$emps_arr, "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;
		$form_field = array("name"=>"vehicle", "content"=>"by vehicle", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$vehs_arr, "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;

		$form_info["form_fields"] = $form_fields;
		$form_info["add_form_fields"] = $add_form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.salaryadvancesreport', array("values"=>$values));
	}
	
	private function getBankPositionReport($values){
		if (\Request::isMethod('post'))
		{
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			$select_args = array();
			$select_args[] = "officebranch.name as branch";
			$select_args[] = "bankdetails.bankName as bankName";
			$select_args[] = "bankdetails.branchName as branchName";
			$select_args[] = "bankdetails.accountNo as accountNo";
			$select_args[] = "employee.fullName as empname";
			$select_args[] = "empdueamount.amount as amount";
			$select_args[] = "empdueamount.paymentDate as paymentDate";
			$select_args[] = "officebranch.name as branch";
			$select_args[] = "empdueamount.comments as remarks";
			$select_args[] = "empdueamount.id as id";
			$totaladvances = 0;
			$totalreturns  = 0;
			$salaryadvances = 0;
			if($values["opening_balance"] == "yes"){
				$init_amt = 0;
				$banks_init_balance = \BankDetails::where("id","=",$values["bankaccount"])->get();
				if(count($banks_init_balance)>0){
					$banks_init_balance = $banks_init_balance[0];
					$init_amt = $banks_init_balance->balanceAmount;
				}
				$frmDt1 = date("Y-m-d", strtotime("01-01-2016"));
				DB::statement(DB::raw("CALL bankposition_report('".$frmDt1."', '".$toDt."');"));
				$sql = "SELECT sum(amount) as amt FROM `temp_bankposition_transaction` where type='income' and bankAccountId=".$values["bankaccount"];
				$rec = DB::select( DB::raw($sql));
				$rec = $rec[0];
				$total_credit = $rec->amt;
				
				$sql = "SELECT sum(amount) as amt FROM `temp_bankposition_transaction` where  type!='income' and bankAccountId=".$values["bankaccount"];
				$rec = DB::select( DB::raw($sql));
				$rec = $rec[0];
				$total_debit = $rec->amt;				
				$end_balance = $init_amt+($total_credit-$total_debit);
				
				$sql = "SELECT sum(amount) as amt FROM `temp_bankposition_transaction` where type='income' and date<'".$frmDt."' and bankAccountId=".$values["bankaccount"];
				$rec = DB::select( DB::raw($sql));
				$rec = $rec[0];
				$total_credit_todate = $rec->amt;
				
				$sql = "SELECT sum(amount) as amt FROM `temp_bankposition_transaction` where type!='income' and date<'".$frmDt."' and bankAccountId=".$values["bankaccount"];
				$rec = DB::select( DB::raw($sql));
				$rec = $rec[0];
				$total_debit_todate = $rec->amt;
				$start_balance = $init_amt+($total_credit_todate-$total_debit_todate);
				
				$sql = "SELECT sum(amount) as amt FROM `temp_bankposition_transaction` where (type='SALARY ADVANCE' and amount<0) and date between'".$frmDt."' and '".$toDt."' and bankAccountId=".$values["bankaccount"];
				$rec = DB::select( DB::raw($sql));
				$rec = $rec[0];
				$salaryadvances = -($rec->amt);
				
				$sql = "SELECT sum(amount) as amt FROM `temp_bankposition_transaction` where (type='income') and date between'".$frmDt."' and '".$toDt."' and bankAccountId=".$values["bankaccount"];
				$rec = DB::select( DB::raw($sql));
				$rec = $rec[0];
				$total_credit_todate = $rec->amt+$salaryadvances;
				
				
				$sql = "SELECT sum(amount) as amt FROM `temp_bankposition_transaction` where (type='expense' or type='FUEL' or type='REPAIR TRANSACTION' or type='STOCK PURCHASE' or (type='SALARY ADVANCE' and amount>0)) and date between'".$frmDt."' and '".$toDt."' and bankAccountId=".$values["bankaccount"];
				$rec = DB::select( DB::raw($sql));
				$rec = $rec[0];
				$total_debit_todate = $rec->amt;
				echo json_encode(array("opening_balance"=>$start_balance,"closing_balance"=>$end_balance,"total_credit"=>$total_credit_todate,"total_debit"=>$total_debit_todate));
				return;
			}
			DB::statement(DB::raw("CALL bankposition_report('".$frmDt."', '".$toDt."');"));
			if($values["reportfor"] == "transaction_details"){
				$sql = "SELECT rowid as rowid, officebranch.name as branch,";
				$sql = $sql." bankdetails.bankName as bankName, ";
				$sql = $sql." bankdetails.branchName as branchName, ";
				$sql = $sql." bankdetails.accountNo as accountNo, ";
				$sql = $sql." temp_bankposition_transaction.name as name, ";
				$sql = $sql." temp_bankposition_transaction.type as type, ";
				$sql = $sql." temp_bankposition_transaction.workFlowStatus as wfstatus, ";
				$sql = $sql." paymentType, date, chequeNumber, amount, lookupValueId, entity, bankAccountId, ";
				$sql = $sql." entityValue, temp_bankposition_transaction.createdBy as createdBy, temp_bankposition_transaction.remarks as remarks ";
				$sql = $sql." FROM temp_bankposition_transaction ";
				$sql = $sql."left join officebranch on officebranch.id=temp_bankposition_transaction.branchId ";
				$sql = $sql."left join 	lookuptypevalues on lookuptypevalues.id=temp_bankposition_transaction.lookupValueId ";
				$sql = $sql."left join bankdetails on bankdetails.id=temp_bankposition_transaction.bankAccountId ";
				$sql = $sql."where paymentType!='credit_card' and (paymentType='debit_card' or  bankAccountId=".$values["bankaccount"].") ";
				
				/*if($values["bank"] == "0" && $values["branch"] == "0"){
					$sql = $sql."where paymentType!='credit_card' and paymentType!='debit_card' ";
				}
				else if($values["bank"] == "0" && $values["branch"] > 0){
					$sql = $sql."where branchId=".$values["branch"]." and paymentType!='credit_card' and paymentType!='debit_card' ";
				}
				else if($values["bank"] != "0" && $values["branch"]== "0"){
					$sql = $sql." where bankdetails.bankName='".$values["bank"]."' and paymentType!='credit_card' and paymentType!='debit_card' ";
				}
				else if($values["bank"] != "0" &&  $values["branch"] > 0){
					$sql = $sql." where branchId=".$values["branch"]." and bankdetails.bankName='".$values["bank"]."' and paymentType!='credit_card' and paymentType!='debit_card' ";
				}*/
				$recs = DB::select( DB::raw($sql));
				$ex_ref_nos = array();
				foreach ($recs as $rec){
					$row = array();
// 					$select_args[] = "officebranch.name as branch";
// 					$select_args[] = "bankdetails.bankName as bankName";
// 					$select_args[] = "bankdetails.branchName as branchName";
// 					$select_args[] = "bankdetails.accountNo as accountNo";
					$row["branch"] = $rec->branch;
					$row["bank"] = "";
					if($rec->accountNo != ""){
						$row["bank"] = $rec->accountNo." (".$rec->branchName."-".$rec->bankName.")";
					}
					if($rec->paymentType=="debit_card"){
						$select_args = array();
						$select_args[] = "bankdetails.accountNo as  accountNo";
						$select_args[] = "bankdetails.branchName as  branchName";
						$select_args[] = "bankdetails.bankName as  bankName";
						$entities1 = \BankDetails::join("cards","cards.bankAccountId","=","bankdetails.id")
										->where("bankdetails.id","=",$values["bankaccount"])
										->where("cards.id","=",$rec->bankAccountId)
										->get();
						if(count($entities1)==0){
							continue;
						}
						else{
							$entity1 = $entities1[0];
							$row["bank"] = $entity1->accountNo." (".$entity1->branchName."-".$entity1->bankName.")";
						}
					}
					$row["type"] = "DEBIT";
					if($rec->type=="income" || $rec->type=="LOCAL"){
						$row["type"] = "CREDIT";
						
					} 
					if($rec->type=="SALARY ADVANCE" && ($rec->amount<0)){
						$row["type"] = "CREDIT";
					}
					$purpose_str = "";
					$amt_str = "";
					if(!in_array($rec->chequeNumber, $ex_ref_nos)){
							
						if($rec->chequeNumber != "" && !in_array($rec->chequeNumber, $ex_ref_nos)){
							$ex_ref_nos[] = $rec->chequeNumber;
							$sql1 = $sql." and chequeNumber='".$rec->chequeNumber."';";
							$recs1 = DB::select( DB::raw($sql1));
							foreach ($recs1 as $rec1){
								$purpose_str = $purpose_str.strtoupper($rec1->name)."<br/>";
								if($rec1->entity != ""){
									$purpose_str = $purpose_str." (".strtoupper($rec1->entity)."-".strtoupper($rec1->entityValue).")<br/>";
								}
								if($rec1->name == "SALARY TRANSACTION"){
									$purpose_str = $purpose_str.strtoupper($rec1->name)."<br/>Paid On ".date("d-m-Y", strtotime($rec1->entity))." TO ".strtoupper($rec1->entityValue)."<br/>";
								}
								if($rec1->lookupValueId==991|| $rec1->lookupValueId==996){
									$purpose_str = $purpose_str.strtoupper($rec1->entity)."<br/>";
								}
								$amt_str = $amt_str.$rec1->amount."<br/>";
							}
						}
						if($purpose_str != ""){
							$row["purpose"] = $purpose_str;
						}
						else{
							$row["purpose"] = strtoupper($rec->name);
							if($rec->entity != ""){
								$row["purpose"] = $row["purpose"]." (".strtoupper($rec->entity)."-".strtoupper($rec->entityValue).")";
							}
						}
						if($rec->name == "SALARY TRANSACTION"){
							$row["purpose"] = strtoupper($rec->name)."<br/>Paid On ".date("d-m-Y", strtotime($rec->entity))." TO ".strtoupper($rec->entityValue);
						}
						if($rec->lookupValueId==991|| $rec->lookupValueId==996){
							$row["purpose"] = strtoupper($rec->entity);
						}
						if($rec->lookupValueId==251 || $rec->lookupValueId==161){
							if($rec->lookupValueId==251){
								$incharge = \ExpenseTransaction::leftjoin("employee","employee.id","=","expensetransactions.inchargeId")
												->where("transactionId","=",$rec->rowid)
												->select(array("employee.fullName"))->get();
								if(count($incharge)>0){
									$incharge = $incharge[0];
									$row["purpose"] = $row["purpose"]." (".$incharge->fullName.")";
								}
							}
							if($rec->lookupValueId==161){
								$incharge = \IncomeTransaction::leftjoin("employee","employee.id","=","incometransactions.inchargeId")
												->where("transactionId","=",$rec->rowid)
												->select(array("employee.fullName"))->get();
								if(count($incharge)>0){
									$incharge = $incharge[0];
									$row["purpose"] = $row["purpose"]." (".$incharge->fullName.")";
								}
							}
						}
						if($rec->lookupValueId==134){
							$fuelstation = \FuelStation::where("id","=",$rec->entityValue)->get();
							if(count($fuelstation)>0){
								$fuelstation = $fuelstation[0];
								$row["purpose"] = "FUEL STATION PAYMENT (".$fuelstation->name.")";
							}
						}
						if($rec->lookupValueId==124){
							$creditSupplier = \CreditSupplier::where("id","=",$rec->entityValue)->get();
							if(count($creditSupplier)>0){
								$creditSupplier = $creditSupplier[0];
								$row["purpose"] = "CREDIT SUPPLIER PAYMENT (".$creditSupplier->supplierName.")";
							}
						}
						if($purpose_str != ""){
							$row["amount"] = $amt_str;
						}
						else{
							$row["amount"] = $rec->amount;
						}
						
						$row["date"] = date("d-m-Y", strtotime($rec->date))." (".$rec->wfstatus.")";
						$row["pmtinfo"] = "PAYMENT TYPE : ".strtoupper($rec->paymentType)."</br>";
						if($rec->paymentType == "debit_card"){
							$card = \Cards::where("id","=",$rec->bankAccountId)->get();
							if(count($card)>0){
								$card = $card[0];
								$row["pmtinfo"] = $row["pmtinfo"]."CARD NO : ".$card->cardNumber."<br/>";
							}
						}
						if($rec->chequeNumber!=""){
							$row["pmtinfo"] = $row["pmtinfo"]."REF NUM : ".$rec->chequeNumber;
						}
						if($rec->paymentType=="cheque_credit" || $rec->paymentType=="cheque_debit"){
							$row["pmtinfo"] = $row["pmtinfo"]."CHQUE NUM : ".$rec->chequeNumber;
						}
						$row["createdby"] = $rec->createdBy;
						//$row["obalance"] = "0.00";
						//$row["cbalance"] = "0.00";
						$row["desc"] = $rec->remarks;
						$resp[] = $row;
					}
				}
			}
			echo json_encode($resp);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$branches =  \OfficeBranch::ALL();
		$branches_arr = array();
		$branches_arr["0"] = "ALL BRANCHES";
		foreach ($branches as $branch){
			$branches_arr[$branch->id] = $branch->name;
		}
		$parentId = -1;
		$parent = \LookupTypeValues::where("name","=","BANK NAME")->get();
		if(count($parent)>0){
			$parent = $parent[0];
			$parentId = $parent->id;
		}
		$banks =  \LookupTypeValues::where("parentId","=",$parentId)->where("status","=","ACTIVE")->get();
		$banks_arr = array();
		//$banks_arr["0"] = "ALL BANKS";
		foreach ($banks as $bank){
			$banks_arr [$bank->name] = $bank->name;
		}
		//"bank_summary"=>"Bank Summary Report",
		$form_field = array("name"=>"reportfor", "content"=>"report for ", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>array("transaction_details"=>"Transaction Details Report"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"bank", "content"=>"bank ", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>$banks_arr, "action"=>array("type"=>"onChange", "script"=>"getBankAccounts(this.value);"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"bankaccount", "content"=>"bank account ", "readonly"=>"",  "required"=>"required", "type"=>"select",  "options"=>array(), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"opening_balance", "value"=>"no", "content"=>"", "readonly"=>"",  "required"=>"","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.bankpositionreport', array("values"=>$values));
	}
	
	private function getLoansReport($values){
		if (\Request::isMethod('post'))
		{
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			$select_args = array();
			$select_args[] = "employee.fullName as empname";
			$select_args[] = "empdueamount.amount as amount";
			$select_args[] = "empdueamount.paymentDate as paymentDate";
			$select_args[] = "officebranch.name as branch";
			$select_args[] = "empdueamount.comments as remarks";
			$select_args[] = "empdueamount.id as id";
			$totaladvances = 0;
			$totalreturns  = 0;
			$mons = array(1 => "JANUARY", 2 => "FEBRUARY", 3 => "MARCH", 4 => "APRIL", 5 => "MAY", 6 => "JUNE", 7 => "JULY", 8 => "AUGUST", 9 => "SEPTEMBER", 10 => "OCTOBER", 11 => "NOVEMBER", 12 => "DECEMBER");
			if($values["reportfor"] == "loan_payment" && $values["loantype"] == "unsecure_loans" ){
				if($values["loan"] == "0"){
					$sql = 'SELECT expensetransactions.date, financecompanies.name, loans.amountFinanced, loans.vehicleId, loans.agmtDate, loans.installmentAmount, loans.totalInstallments, loans.paidInstallments, expensetransactions.paymentType, expensetransactions.amount,expensetransactions.entityDate, loans.bankAccountId, concat(bankdetails.bankName," - ",bankdetails.accountNo) as bankName,loans.loanNo, loans.id as loanId, expensetransactions.remarks as remarks FROM `expensetransactions` left join loans on loans.id=expensetransactions.entityValue left join financecompanies on financecompanies.id=loans.financeCompanyId left join bankdetails on bankdetails.id=loans.bankAccountId where entity="LOAN PAYMENT"  and loans.purpose = "UNSECURE LOANS" and financecompanies.id='.$values["financecompany"].' and expensetransactions.status="ACTIVE" and date between "'.$frmDt.'" and "'.$toDt.'" order by date;';
				}
				else if($values["loan"] > 0){
					$sql = 'SELECT expensetransactions.date, financecompanies.name, loans.amountFinanced, loans.vehicleId, loans.agmtDate, loans.installmentAmount, loans.totalInstallments, loans.paidInstallments, expensetransactions.paymentType, expensetransactions.amount,expensetransactions.entityDate, loans.bankAccountId, concat(bankdetails.bankName," - ",bankdetails.accountNo) as bankName,loans.loanNo, loans.id as loanId, expensetransactions.remarks as remarks FROM `expensetransactions` left join loans on loans.id=expensetransactions.entityValue left join financecompanies on financecompanies.id=loans.financeCompanyId left join bankdetails on bankdetails.id=loans.bankAccountId where loans.id='.$values["loan"].' and entity="LOAN PAYMENT" and loans.purpose = "UNSECURE LOANS" and expensetransactions.status="ACTIVE" and date between "'.$frmDt.'" and "'.$toDt.'" order by date ;';
				}
				$recs = DB::select( DB::raw($sql));
				foreach ($recs as $rec){
					$row = array();
					$row["date"] = date("d-m-Y",strtotime($rec->date));
					$row["loanno"] = $rec->loanNo;
					$row["fincompany"] = $rec->name;
					$agmtDate = $rec->agmtDate;
					$month = date("m", strtotime($agmtDate));
					$month_name = $mons[intval($month)];
					$year = date("Y", strtotime($agmtDate));
					$endDate = date('Y-m-d', strtotime("+$rec->totalInstallments months", strtotime($agmtDate)));						
					$endmonth = date("m", strtotime($endDate));
					$endmonth_name = $mons[intval($endmonth)];
					$endyear = date("Y", strtotime($endDate));
					$row["emiperiod"] = $month_name.", ".$year." - ".$endmonth_name.", ".$endyear;
					$row["formonth"] = date('d-m-Y', strtotime($rec->entityDate));
					if($row["formonth"]=="30-11--0001" || $row["formonth"]=="1970-01-01" || $row["formonth"]=="01-01-1970"){
						$row["formonth"] = "";
					}
					else{
						$row["formonth"] = date('F-Y', strtotime($row["formonth"]));
					}
					$row["amountFinanced"] = sprintf('%0.2f', $rec->amountFinanced);					
					$sql = 'select count(*) as cnt, sum(amount) as sumamt,entityValue as entityValue from expensetransactions where entity="LOAN PAYMENT" and entityValue='.$rec->loanId.' and date BETWEEN "'.$rec->agmtDate.'" and "'.$rec->date.'";';
					$rec1 = DB::select( DB::raw($sql));
					$rec1 = $rec1[0];
					$loans = \Loan::where("id","=",$rec1->entityValue)->first();
					$row["ondateamt"] = $rec->amount;
					$row["paidemiamt"] = sprintf('%0.2f', $rec1->sumamt);
					$row["balemiamt"] = sprintf('%0.2f', $row["amountFinanced"]-$row["paidemiamt"]);
					$row["paymenttype"] = $rec->paymentType;
					if($rec->bankName != ""){
						$row["paymenttype"] = $row["paymenttype"]."<br/>Bank : ".$rec->bankName;
					}
					$row["remarks"] = $rec->remarks;
					$row["bankdetails"] = $rec->bankName;
					$resp[] = $row;
				}
			}
			if($values["reportfor"] == "loan_payment" && $values["loantype"] == "secure_loans" ){
				if($values["loan"] == "0"){
					$sql = 'SELECT expensetransactions.date, financecompanies.name, loans.amountFinanced, loans.vehicleId, loans.agmtDate, loans.installmentAmount, loans.totalInstallments, loans.paidInstallments, expensetransactions.paymentType, expensetransactions.amount,expensetransactions.entityDate, loans.bankAccountId, concat(bankdetails.bankName," - ",bankdetails.accountNo) as bankName,loans.loanNo, loans.id as loanId, expensetransactions.remarks as remarks FROM `expensetransactions` left join loans on loans.id=expensetransactions.entityValue left join financecompanies on financecompanies.id=loans.financeCompanyId left join bankdetails on bankdetails.id=loans.bankAccountId where entity="LOAN PAYMENT"  and loans.purpose != "UNSECURE LOANS" and financecompanies.id='.$values["financecompany"].' and expensetransactions.status="ACTIVE" and date between "'.$frmDt.'" and "'.$toDt.'" order by date;';
				}
				else if($values["loan"] > 0){
					$sql = 'SELECT expensetransactions.date, financecompanies.name, loans.amountFinanced, loans.vehicleId, loans.agmtDate, loans.installmentAmount, loans.totalInstallments, loans.paidInstallments, expensetransactions.paymentType, expensetransactions.amount,expensetransactions.entityDate, loans.bankAccountId, concat(bankdetails.bankName," - ",bankdetails.accountNo) as bankName,loans.loanNo, loans.id as loanId, expensetransactions.remarks as remarks FROM `expensetransactions` left join loans on loans.id=expensetransactions.entityValue left join financecompanies on financecompanies.id=loans.financeCompanyId left join bankdetails on bankdetails.id=loans.bankAccountId where loans.id='.$values["loan"].' and entity = "LOAN PAYMENT" and loans.purpose != "UNSECURE LOANS" and expensetransactions.status="ACTIVE" and date between "'.$frmDt.'" and "'.$toDt.'" order by date ;';
				}
				$recs = DB::select( DB::raw($sql));
				foreach ($recs as $rec){
					$row = array();
					$row["date"] = date("d-m-Y",strtotime($rec->date));
					$row["loanno"] = $rec->loanNo;
					$row["fincompany"] = $rec->name;
						
					$veh_arr = explode(",", $rec->vehicleId);
					$vehs = \Vehicle::whereIn("id",$veh_arr)->get();
					$veh_arr = "";
					foreach ($vehs as $veh){
						$veh_arr = $veh_arr.$veh->veh_reg.", ";
					}
					$row["vehicles"] = $veh_arr;
						
					$agmtDate = $rec->agmtDate;
					$month = date("m", strtotime($agmtDate));
					$month_name = $mons[intval($month)];
					$year = date("Y", strtotime($agmtDate));
					$endDate = date('Y-m-d', strtotime("+$rec->totalInstallments months", strtotime($agmtDate)));
					$endmonth = date("m", strtotime($endDate));
					$endmonth_name = $mons[intval($endmonth)];
					$endyear = date("Y", strtotime($endDate));
					$row["emiperiod"] = $month_name.", ".$year." - ".$endmonth_name.", ".$endyear;
					$row["formonth"] = date('d-m-Y', strtotime($rec->entityDate));
					if($row["formonth"]=="30-11--0001" || $row["formonth"]=="1970-01-01" || $row["formonth"]=="01-01-1970"){
						$row["formonth"] = "";
					}
					else{
						$row["formonth"] = date('F-Y', strtotime($row["formonth"]));
					}
					$row["amountFinanced"] = sprintf('%0.2f', $rec->amountFinanced);
					$sql = 'select count(*) as cnt, sum(amount) as sumamt,entityValue as entityValue from expensetransactions where entity="LOAN PAYMENT" and entityValue='.$rec->loanId.' and date BETWEEN "'.$rec->agmtDate.'" and "'.$rec->date.'";';
					$rec1 = DB::select( DB::raw($sql));
					$rec1 = $rec1[0];
					$loans = \Loan::where("id","=",$rec1->entityValue)->first();
					$row["totemiamt"] = sprintf('%0.2f', ($rec->paidInstallments+$rec1->cnt)*$rec->installmentAmount);
					$row["paidemiamt"] = sprintf('%0.2f', $rec1->sumamt);
					$row["balemiamt"] = sprintf('%0.2f', $row["totemiamt"]-$row["paidemiamt"]);
					$row["totemis"] = $rec->totalInstallments;
					$row["paidemis"] = ($rec->paidInstallments+$rec1->cnt)."/".$rec->totalInstallments;
					$row["remainingemiamt"] = ($row["totemis"]*$rec->installmentAmount)-($row["paidemiamt"]);
					$row["remainingemiamt"] = sprintf('%0.2f', $row["remainingemiamt"]);
					$row["emiamt"] = sprintf('%0.2f', $rec->installmentAmount);
					$row["amount"] = sprintf('%0.2f', $rec->amount);
					$row["paymenttype"] = $rec->paymentType;
					if($rec->bankName != ""){
						$row["paymenttype"] = $row["paymenttype"]."<br/>Bank : ".$rec->bankName;
					}
					$row["remarks"] = $rec->remarks;
					$row["bankdetails"] = $rec->bankName;
					$resp[] = $row;
			}
			}
			else if($values["reportfor"] == "interest_payment" && $values["loantype"] == "unsecure_loans"){
				if($values["loan"] == "0"){
					$sql = 'SELECT date, financecompanies.name, loans.amountFinanced, loans.vehicleId, loans.agmtDate, loans.installmentAmount, loans.totalInstallments, loans.paidInstallments, expensetransactions.paymentType, expensetransactions.amount,expensetransactions.entityDate, loans.bankAccountId, concat(bankdetails.bankName," - ",bankdetails.accountNo) as bankName,loans.loanNo, loans.id as loanId, expensetransactions.remarks as remarks FROM `expensetransactions` left join loans on loans.id=expensetransactions.entityValue left join financecompanies on financecompanies.id=loans.financeCompanyId left join bankdetails on bankdetails.id=loans.bankAccountId where entity="LOAN INTEREST PAYMENT" and loans.purpose = "UNSECURE LOANS" and financecompanies.id='.$values["financecompany"].' and expensetransactions.status="ACTIVE" and date between "'.$frmDt.'" and "'.$toDt.'" order by date, loans.id;';
				}
				else if($values["loan"] > 0){
					$sql = 'SELECT date, financecompanies.name, loans.amountFinanced, loans.vehicleId, loans.agmtDate, loans.installmentAmount, loans.totalInstallments, loans.paidInstallments, expensetransactions.paymentType, expensetransactions.amount,expensetransactions.entityDate, loans.bankAccountId, concat(bankdetails.bankName," - ",bankdetails.accountNo) as bankName,loans.loanNo, loans.id as loanId, expensetransactions.remarks as remarks FROM `expensetransactions` left join loans on loans.id=expensetransactions.entityValue left join financecompanies on financecompanies.id=loans.financeCompanyId left join bankdetails on bankdetails.id=loans.bankAccountId where loans.id='.$values["loan"].'  and loans.purpose = "UNSECURE LOANS" and entity="LOAN INTEREST PAYMENT" and expensetransactions.status="ACTIVE" and date between "'.$frmDt.'" and "'.$toDt.'" order by date ;';
				}
				$recs = DB::select( DB::raw($sql));
				foreach ($recs as $rec){
					$row = array();
					$row["date"] = date("d-m-Y",strtotime($rec->date));
					$row["loanno"] = $rec->loanNo;
					$row["fincompany"] = $rec->name;
					$agmtDate = $rec->agmtDate;
					$month = date("m", strtotime($agmtDate));
					$month_name = $mons[intval($month)];
					$year = date("Y", strtotime($agmtDate));
					$endDate = date('Y-m-d', strtotime("+$rec->totalInstallments months", strtotime($agmtDate)));						
					$endmonth = date("m", strtotime($endDate));
					$endmonth_name = $mons[intval($endmonth)];
					$endyear = date("Y", strtotime($endDate));
					
					$row["emiperiod"] = $month_name.", ".$year." - ".$endmonth_name.", ".$endyear;
					$row["formonth"] = date('d-m-Y', strtotime($rec->entityDate));
					if($row["formonth"]=="30-11--0001" || $row["formonth"]=="1970-01-01" || $row["formonth"]=="01-01-1970"){
						$row["formonth"] = "";
					}
					else{
						$row["formonth"] = date('F-Y', strtotime($row["formonth"]));
					}
					$row["amountFinanced"] = sprintf('%0.2f', $rec->amountFinanced);
					$sql = 'select count(*) as cnt, sum(amount) as sumamt from expensetransactions where entity="LOAN INTEREST PAYMENT" and entityValue='.$rec->loanId.' and entityDate BETWEEN "'.$rec->agmtDate.'" and "'.$rec->entityDate.'";';
					$rec1 = DB::select( DB::raw($sql));
					$rec1 = $rec1[0];
					$row["amount"] = sprintf('%0.2f', $rec->amount);
					$row["paidemiamt"] = sprintf('%0.2f', $rec1->sumamt);
					$row["balemiamt"] = "";
					$row["paymenttype"] = $rec->paymentType;
					if($rec->bankName != ""){
						$row["paymenttype"] = $row["paymenttype"]."<br/>Bank : ".$rec->bankName;
					}
					$row["remarks"] = $rec->remarks;
					$row["bankdetails"] = $rec->bankName;
					$resp[] = $row;
				}
			}
			else if($values["reportfor"] == "interest_payment" && $values["loantype"] == "secure_loans"){
				if($values["loan"] == "0"){
					$sql = 'SELECT date, financecompanies.name, loans.amountFinanced, loans.vehicleId, loans.agmtDate, loans.installmentAmount, loans.totalInstallments, loans.paidInstallments, expensetransactions.paymentType, expensetransactions.amount,expensetransactions.entityDate, loans.bankAccountId, concat(bankdetails.bankName," - ",bankdetails.accountNo) as bankName,loans.loanNo, loans.id as loanId, expensetransactions.remarks as remarks FROM `expensetransactions` left join loans on loans.id=expensetransactions.entityValue left join financecompanies on financecompanies.id=loans.financeCompanyId left join bankdetails on bankdetails.id=loans.bankAccountId where entity="LOAN INTEREST PAYMENT" and loans.purpose = "UNSECURE LOANS" and financecompanies.id='.$values["financecompany"].' and expensetransactions.status="ACTIVE" and date between "'.$frmDt.'" and "'.$toDt.'" order by date, loans.id;';
				}
				else if($values["loan"] > 0){
					$sql = 'SELECT date, financecompanies.name, loans.amountFinanced, loans.vehicleId, loans.agmtDate, loans.installmentAmount, loans.totalInstallments, loans.paidInstallments, expensetransactions.paymentType, expensetransactions.amount,expensetransactions.entityDate, loans.bankAccountId, concat(bankdetails.bankName," - ",bankdetails.accountNo) as bankName,loans.loanNo, loans.id as loanId, expensetransactions.remarks as remarks FROM `expensetransactions` left join loans on loans.id=expensetransactions.entityValue left join financecompanies on financecompanies.id=loans.financeCompanyId left join bankdetails on bankdetails.id=loans.bankAccountId where loans.id='.$values["loan"].'  and loans.purpose = "UNSECURE LOANS" and entity="LOAN INTEREST PAYMENT" and expensetransactions.status="ACTIVE" and date between "'.$frmDt.'" and "'.$toDt.'" order by date ;';
				}
				$recs = DB::select( DB::raw($sql));
				foreach ($recs as $rec){
					$row = array();
					$row["date"] = date("d-m-Y",strtotime($rec->date));
					$row["loanno"] = $rec->loanNo;
					$row["fincompany"] = $rec->name;
						
					$veh_arr = explode(",", $rec->vehicleId);
					$vehs = \Vehicle::whereIn("id",$veh_arr)->get();
					$veh_arr = "";
					foreach ($vehs as $veh){
						$veh_arr = $veh_arr.$veh->veh_reg.", ";
					}
					$row["vehicles"] = $veh_arr;
						
					$agmtDate = $rec->agmtDate;
					$month = date("m", strtotime($agmtDate));
					$month_name = $mons[intval($month)];
					$year = date("Y", strtotime($agmtDate));
					$endDate = date('Y-m-d', strtotime("+$rec->totalInstallments months", strtotime($agmtDate)));
					$endmonth = date("m", strtotime($endDate));
					$endmonth_name = $mons[intval($endmonth)];
					$endyear = date("Y", strtotime($endDate));
					$row["emiperiod"] = $month_name.", ".$year." - ".$endmonth_name.", ".$endyear;
					$row["formonth"] = date('d-m-Y', strtotime($rec->entityDate));
					if($row["formonth"]=="30-11--0001" || $row["formonth"]=="1970-01-01" || $row["formonth"]=="01-01-1970"){
						$row["formonth"] = "";
					}
					else{
						$row["formonth"] = date('F-Y', strtotime($row["formonth"]));
					}
					$row["amountFinanced"] = sprintf('%0.2f', $rec->amountFinanced);
					$sql = 'select count(*) as cnt, sum(amount) as sumamt,entityValue as entityValue from expensetransactions where entity="LOAN INTEREST PAYMENT" and entityValue='.$rec->loanId.' and date BETWEEN "'.$rec->agmtDate.'" and "'.$rec->date.'";';
					$rec1 = DB::select( DB::raw($sql));
					$rec1 = $rec1[0];
					$loans = \Loan::where("id","=",$rec1->entityValue)->first();
					$row["totemiamt"] = sprintf('%0.2f', ($rec->paidInstallments+$rec1->cnt)*$rec->installmentAmount);
					$row["paidemiamt"] = sprintf('%0.2f', $rec1->sumamt);
					$row["balemiamt"] = sprintf('%0.2f', $row["totemiamt"]-$row["paidemiamt"]);
					$row["totemis"] = $rec->totalInstallments;
					$row["paidemis"] = ($rec->paidInstallments+$rec1->cnt)."/".$rec->totalInstallments;
					$row["remainingemiamt"] = ($row["totemis"]*$rec->installmentAmount)-($row["paidemiamt"]);
					$row["remainingemiamt"] = sprintf('%0.2f', $row["remainingemiamt"]);
					$row["emiamt"] = sprintf('%0.2f', $rec->installmentAmount);
					$row["amount"] = sprintf('%0.2f', $rec->amount);
					$row["paymenttype"] = $rec->paymentType;
					if($rec->bankName != ""){
						$row["paymenttype"] = $row["paymenttype"]."<br/>Bank : ".$rec->bankName;
					}
					$row["remarks"] = $rec->remarks;
					$row["bankdetails"] = $rec->bankName;
					$resp[] = $row;
				}
			}
			else if($values["reportfor"] == "loan_event"){
				if($values["loan"] == "0"){
					$recs = \LoanEvent::where("loan_events.status","=","ACTIVE")
										->leftjoin("loans","loans.id","=","loan_events.loanId")
										->where("loans.financeCompanyId","=",$values["financecompany"])
										->whereBetween("loan_events.date",array($frmDt,$toDt))->OrderBy("loan_events.date")->get() ;
				}
				else if($values["loan"] > 0){
					$recs = \LoanEvent::where("status","=","ACTIVE")->where("loanId","=",$values["loan"])->whereBetween("date",array($frmDt,$toDt))->OrderBy("date")->get() ;
				}
				$i=1;
				foreach ($recs as $rec){
					$row = array();
					$row["id"] = $i;
					$loan_arr = array();
					$loans = \Loan::where("status","=","ACTIVE")->get();
					foreach ($loans as $loan){
						$loan_arr[$loan->id] = $loan->loanNo;
					}
					$row["loanno"] = "";
					if(isset($loan_arr[$rec->loanId])){
						$row["loanno"] = $loan_arr[$rec->loanId];
					}
					$row["event"] = $rec->event;
					$row["date"] = date("d-m-Y",strtotime($rec->date));
					$row["value"] = $rec->value;
					$row["oldvalue"] = $rec->oldValue;
					$row["remarks"] = $rec->remarks;
					$resp[] = $row;
					$i++;
				}
			}
			echo json_encode($resp);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$entity_arr = array();

		$fincompanies = \FinanceCompany::All();
		$fincompanies_arr = array();
		//$fincompanies_arr["0"] = "ALL FINANCE COMPANIES";
		foreach ($fincompanies as $fincompany){
			$fincompanies_arr[$fincompany->id] = $fincompany->name;
		}
		
		$form_field = array("name"=>"reportfor", "content"=>"report for ", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>array("loan_payment"=>"LOAN PAYMENT", "interest_payment"=>"INTEREST PAYMENT","loan_event"=>"LOAN EVENT"), "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"loantype", "content"=>"loan type", "readonly"=>"",  "required"=>"required","type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeLoan(this.value);"), "options"=>array("unsecure_loans"=>"UNSECURE LOANS","secure_loans"=>"SECURE LOANS"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"financecompany", "content"=>"finance company", "readonly"=>"",  "required"=>"required","type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeFinance(this.value);"),"options"=>array(), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"loan", "content"=>"loan no", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>array(), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.loansreport', array("values"=>$values));
	}
	
	private function getDailyFinanceReport($values){
		if (\Request::isMethod('post'))
		{
			$frmDt = date("Y-m-d", strtotime($values["date"]));
			$resp = array();
			$select_args = array();
			$select_args[] = "employee.fullName as empname";
			$select_args[] = "empdueamount.amount as amount";
			$select_args[] = "empdueamount.paymentDate as paymentDate";
			$select_args[] = "officebranch.name as branch";
			$select_args[] = "empdueamount.comments as remarks";
			$select_args[] = "empdueamount.id as id";
			$totaladvances = 0;
			$totalreturns  = 0;
			if(true){
				$sql = 'SELECT date, expensetransactions.amount, financecompanies.name, loans.amountFinanced,  
						loans.agmtDate, loans.installmentAmount, loans.totalInstallments, loans.paidInstallments, 
						loans.paymentType, loans.bankAccountId, concat(bankdetails.bankName," - ",bankdetails.accountNo) as bankName,
						loans.id as loanId, expensetransactions.remarks as remarks FROM `expensetransactions` 
						left join dailyfinances as loans on loans.id=expensetransactions.entityValue 
						left join financecompanies on financecompanies.id=loans.financeCompanyId 
						left join bankdetails on bankdetails.id=loans.bankAccountId 
						where entity="DAILY FINANCE PAYMENT" and date between "'.$frmDt.'" and "'.$frmDt.'" order by date;';
				$recs = DB::select( DB::raw($sql));
				$totalloan=0;
				$tilltotsuspense=0;
				foreach ($recs as $rec){
					$row = array();
					$Date = $rec->agmtDate;
					$startDate = strtotime(date("Y-m-d", strtotime($Date)) . " +".$rec->paidInstallments." day");
					$startDate = date ( 'Y-m-d' , $startDate );
					$endDate = strtotime(date("Y-m-d", strtotime($Date)) . " +".$rec->totalInstallments." day");
					$endDate = date ( 'Y-m-d' , $endDate );

					$sql = 'select sum(amount) as amt from expensetransactions where  entity="DAILY FINANCE PAYMENT" and status="ACTIVE" and entityValue='.$rec->loanId.' and date BETWEEN "'.$rec->agmtDate.'" and "'.$frmDt.'";';
					$rec1 = DB::select( DB::raw($sql));
					$rec1 = $rec1[0];
					$totalamnt = $rec1->amt;
					$amount = $rec->installmentAmount;
					$todayPaidAmnt = $rec->amount;
					$start = strtotime($startDate);
					$end = strtotime($frmDt);
					$datediff = $end - $start;
					$days = floor($datediff/(60*60*24));

					$paidStuff = (int)($totalamnt/$amount);
					$remstuff = $totalamnt%$amount;
						
					$currentDay = $days+$rec->paidInstallments;
					$totalTobePaid = $currentDay*$amount;
					$suspenseAmount = ($days*$amount) - $totalamnt;
					$total_installments = $rec->totalInstallments;
					
					if(true){ //$currentDay <= $total_installments
						$row["fincompany"] = $rec->name;
						$row["loanamt"] = sprintf('%0.2f', $rec->amountFinanced);
						$row["startdt"] = date("d-m-Y",strtotime($startDate));
						$row["enddt"] = date("d-m-Y",strtotime($endDate));
						$row["suspense"] = "<font color='red'><b>".sprintf('%0.2f', $suspenseAmount)."</b></font>";
						$row["paidemis"] = ($days+1+$rec->paidInstallments)." Day";
						$row["todaypayment"] = sprintf('%0.2f',$todayPaidAmnt);
						$row["todaysuspense"] = sprintf('%0.2f',$todayPaidAmnt-$amount);
					}
					
					$resp[] = $row;
				}
			}
			echo json_encode($resp);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$loans =  \Loan::ALL();
		$loans_arr = array();
		$loans_arr["0"] = "ALL LOANS";
		foreach ($loans as $loan){
			$vehs = "";
			if($loan->vehicleId != ""){
				$veh_arr = explode(",", $loan->vehicleId);
				$vehicles = \Vehicle::whereIn("id",$veh_arr)->get();
				$i = 0;
				for($i=0; $i<count($vehicles); $i++){
					if($i+1 == count($vehicles)){
						$vehs = $vehs.$vehicles[$i]->veh_reg;
					}
					else{
						$vehs = $vehs.$vehicles[$i]->veh_reg.", ";
					}
				}
			}
			$loans_arr[$loan->id] = $loan->loanNo." (".$vehs.")";
		}
		$form_field = array("name"=>"date", "content"=>"date", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control date-picker");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.dailyfinancereport', array("values"=>$values));
	}
	
	private function getDailySettlementReport($values){
		if (\Request::isMethod('post'))
		{
			$dt = date("Y-m-d", strtotime($values["date"]));
			$brachId = $values["branch"];
			$resp = array();
			
			$booking_income = 0;
			$booking_cancel = 0;
			$corgos_simply_income = 0;
			$corgos_simply_cancel = 0;
			$other_income = 0;
			$total_expenses = 0;
			$bank_deposited = 0;
			$branch_deposited = 0;
			
			
			DB::statement(DB::raw("CALL daily_transactions_report('".$dt."', '".$dt."');"));
			$recs = DB::select( DB::raw("select * from temp_daily_transaction where branchId=".$brachId." order by branchId"));
			if(count($recs)>0) {
				$totalAmt = 0;
				foreach ($recs as $rec){
					$row = array();
					$brachName = "";
					if($rec->branchId>0){
						$brachName = \OfficeBranch::where("id","=",$rec->branchId)->first();
						$brachName = $brachName->name;
					}
					$row = array();
					$row["branch"] = $brachName;
					if($rec->type=="LOCAL"  || $rec->type == "DAILY"){
						$rec->type = $rec->type." TRIP";
					}
					$row["type"] = strtoupper($rec->type);
					$row["date"] = date("d-m-Y",strtotime($rec->date));
					$row["amount"] = $rec->amount;
					$row["purpose"] = strtoupper($rec->name);
					if($rec->lookupValueId==8888){
						$row["purpose"] = "CREDITED TO BRANCH - TRIP BALANCE";
					}
					if($rec->lookupValueId==9999){
						$row["purpose"] = "DEBITED FROM BRANCH - TRIP BALANCE";
					}
					else if($rec->lookupValueId==999){
						if($rec->entityValue>0){
							$prepaidName = \LookupTypeValues::where("id","=",$rec->entityValue)->first();
							$prepaidName = $prepaidName->name;
							$row["purpose"] = strtoupper($rec->entity);
							$row["employee"] = $prepaidName;
						}
						else{
							$row["purpose"] = strtoupper($rec->entity);
							$row["employee"] = "";
						}
					}
					else if($rec->lookupValueId==998){
						if($rec->entityValue>0){
							$creditsupplier = \CreditSupplier::where("id","=",$rec->entityValue)->first();
							$creditsupplier = $creditsupplier->supplierName;
							$row["purpose"] = strtoupper($rec->entity);
							$row["employee"] = $creditsupplier;
						}
						else{
							$row["purpose"] = strtoupper($rec->entity);
							$row["employee"] = "";
						}
					}
					else if($rec->lookupValueId==997){
						if($rec->entityValue>0){
							$fuelstation = \FuelStation::where("id","=",$rec->entityValue)->first();
							$fuelstation = $fuelstation->name;
							$row["purpose"] = strtoupper($rec->entity);
							$row["employee"] = $fuelstation;
						}
						else{
							$row["purpose"] = strtoupper($rec->entity);
							$row["employee"] = "";
						}
					}
					
					else if($rec->lookupValueId==996){
						if($rec->entityValue>0){
							$loan = \Loan::where("id","=",$rec->entityValue)->first();
							$dfid = $loan->financeCompanyId;
							$finanacecompany = \FinanceCompany::where("id","=",$dfid)->first();
							$finanacecompany = $finanacecompany->name;
							$row["purpose"] = strtoupper($rec->entity);
							$row["employee"] = $loan->loanNo." (".$finanacecompany.")";
						}
						else{
							$row["purpose"] = strtoupper($rec->entity);
							$row["employee"] = "";
						}
					}
					else if($rec->lookupValueId==283){
						if($rec->entityValue>0){
							$card = \Cards::where("id","=",$rec->entityValue)->first();
							$lookupvalue = $card->cardNumber." (".$card->cardHolderName.")";
							$row["purpose"] = strtoupper($rec->entity);
							$row["employee"] = $lookupvalue;
						}
						else{
							$row["purpose"] = strtoupper($rec->entity);
							$row["employee"] = "";
						}
					}
					else if($rec->lookupValueId==991){
						if($rec->entityValue>0){
							$dfid = \DailyFinance::where("id","=",$rec->entityValue)->first();
							$dfid = $dfid->financeCompanyId;
							$finanacecompany = \FinanceCompany::where("id","=",$dfid)->first();
							$finanacecompany = $finanacecompany->name;
							$row["purpose"] = strtoupper($rec->entity);
							$row["employee"] = $finanacecompany;
						}
						else{
							$row["purpose"] = strtoupper($rec->entity);
							$row["employee"] = "";
						}
					}
					else if($rec->lookupValueId==73){
						$bankdetails = \IncomeTransaction::where("transactionId","=",$rec->rowid)->leftjoin("bankdetails","bankdetails.id","=","incometransactions.bankId")->first();
						$bankdetails = $bankdetails->bankName." - ".$bankdetails->accountNo;
						$row["employee"] = $bankdetails;
					}
					else if($rec->lookupValueId==84){
						$bankdetails = \ExpenseTransaction::where("transactionId","=",$rec->rowid)->leftjoin("bankdetails","bankdetails.id","=","expensetransactions.bankId")->first();
						$bankdetails = $bankdetails->bankName." - ".$bankdetails->accountNo;
						$row["employee"] = $bankdetails;
					}
					else if($rec->lookupValueId==63){
						$lookupvalue = \LookupTypeValues::where("id","=",$rec->lookupValueId)->first();
						$lookupvalue = $lookupvalue->name;
						$row["employee"] = "";
					}
					else{
						if($rec->entityValue != "0"){
							$row["purpose"] = strtoupper($rec->entity);
							$row["employee"] = $rec->lookupValueId." - ".$rec->entityValue;
						}
						else{
							$row["employee"] = $rec->entity;
						}
							
					}
					
					if($row["type"] == "LOCAL TRIP" || $row["type"]=="DAILY TRIP"){
						if($row["purpose"] == "VEHICLE ADVANCES"){
							$row["purpose"] = $row["purpose"]." (".$row["type"].")";
							$row["type"] = "EXPENSE";
						}
						if($row["purpose"] == "ADVANCE AMOUNT"){
							$row["purpose"] = $row["purpose"]." (".$row["type"].")";
							$row["type"] = "INCOME";
						}
						if($row["purpose"] == "CREDITED TO BRANCH - TRIP BALANCE"){
							$row["purpose"] = $row["purpose"]." (".$row["type"].")";
							$row["type"] = "INCOME";
						}
						if($row["purpose"] == "DEBITED FROM BRANCH - TRIP BALANCE"){
							$row["purpose"] = $row["purpose"]." (".$row["type"].")";
							$row["type"] = "EXPENSE";
						}
					}
					
					if($row["purpose"] == "TICKETS AMOUNT" ){
						$booking_income = $booking_income+$row["amount"];
					}
					else if($row["purpose"] == "TICKETS CANCEL AMOUNT" ){
						$booking_cancel = $booking_cancel+$row["amount"];
					}
					else if($row["purpose"] == "CARGO SIMPLY AMOUNT" ){
						$corgos_simply_income = $corgos_simply_income+$row["amount"];
					}
					else if($row["purpose"] == "CARGO SIMPLY CANCEL" ){
						$corgos_simply_cancel = $corgos_simply_cancel+$row["amount"];
					}
					else if($row["purpose"] == "BANK DEPOSITS" ){
						$bank_deposited = $bank_deposited+$row["amount"];
					}
					else if($row["type"] == "INCOME" ){
						$other_income = $other_income+$row["amount"];
					}
					else if($row["type"] == "EXPENSE" && $row["purpose"] == "BRANCH DEPOSIT" ){
						$branch_deposited = $branch_deposited+$row["amount"];
					}
					else if($row["type"] == "EXPENSE" ){
						$total_expenses = $total_expenses+$row["amount"];
					}
					
					$row["comments"] = $rec->remarks;
					$row["createdby"] = $rec->createdBy;
					$resp[] = $row;
				}
			}
			$booking_income = sprintf('%0.2f', $booking_income);
			$booking_cancel = sprintf('%0.2f', $booking_cancel);
			$corgos_simply_income = sprintf('%0.2f', $corgos_simply_income);
			$corgos_simply_cancel = sprintf('%0.2f', $corgos_simply_cancel);
			$other_income = sprintf('%0.2f', $other_income);
			$total_expenses = sprintf('%0.2f', $total_expenses);
			$bank_deposited = sprintf('%0.2f', $bank_deposited);
			$branch_deposited = sprintf('%0.2f', $branch_deposited);
				
			$cf_amt = 0;
			$cf_prev_amt = 0;
			$nextDay = strtotime(date("Y-m-d", strtotime($dt)) . " +1 day");
			$nextDay = date ('Y-m-d', $nextDay);
			$cf_details = \IncomeTransaction::where("branchId","=",$brachId)->where("date","=",$nextDay)->where("status","=","ACTIVE")->where("lookupValueId","=","194")->get();
			if(count($cf_details)>0){
				$cf_details = $cf_details[0];
				$cf_amt = $cf_details->amount;
			}
			$cf_details = \IncomeTransaction::where("branchId","=",$brachId)->where("date","=",date("Y-m-d",strtotime($dt)))->where("status","=","ACTIVE")->where("lookupValueId","=","194")->get();
			if(count($cf_details)>0){
				$cf_details = $cf_details[0];
				$cf_prev_amt = $cf_details->amount;
			}
			
			$resp_arr = array("data"=>$resp,"booking_income"=>$booking_income,"booking_cancel"=>$booking_cancel,"cargos_simply_income"=>$corgos_simply_income,
					"cargos_simply_cancel"=>$corgos_simply_cancel,"other_income"=>$other_income,"total_expense"=>$total_expenses,
					"branch_deposites"=>$branch_deposited,"bank_deposits"=>$bank_deposited,"cf_amt"=>$cf_amt,"cf_prev_amt"=>$cf_prev_amt
					);
			echo json_encode($resp_arr);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$branches =  \OfficeBranch::ALL();
		$branches_arr = array();
		foreach ($branches as $branch){
			$branches_arr[$branch->id] = $branch->name;
		}
		$form_field = array("name"=>"branch", "content"=>"branch ", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>$branches_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"date", "content"=>"date ", "readonly"=>"",  "required"=>"required", "type"=>"text", "class"=>"form-control date-picker");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "dailysettlement";
		return View::make('reports.dailysettlementreport', array("values"=>$values));
	}
	
	private function getDailySettlementReportsReport($values){
		if (\Request::isMethod('post'))
		{
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			$select_args = array();
			$select_args[] = "officebranch.name as branch";
			$select_args[] = "sum(actualSalary) as actualSalary";
			$select_args[] = "sum(dueDeductions) as dueDeductions";
			$select_args[] = "sum(leaveDeductions) as leaveDeductions";
			$select_args[] = "sum(pf) as pf";
			$select_args[] = "sum(esi) as esi";
			$select_args[] = "sum(salaryPaid) as salaryPaid";
			if(isset($values["branch"])){
				if($values["branch"] == "0"){
					$branchsuspenses = \BranchSuspenseReport::whereBetween("reportDate",array($frmDt,$toDt))->OrderBy("reportDate")->get() ;
					foreach ($branchsuspenses as $branchsuspense){
						$row = array();
						$branchname = \OfficeBranch::where("id","=",$branchsuspense->branchId)->get();
						if(count($branchname)>0){
							$branchname = $branchname[0];
							$branchname = $branchname->name;
						}
						else {
							$branchname = "";
						}
						$row["branchname"] = $branchname;
						$row["reportdate"] = date("d-m-Y",strtotime($branchsuspense->reportDate));
						$row["income"] = sprintf('%0.2f', $branchsuspense->total_income);
						$row["expense"] = sprintf('%0.2f', $branchsuspense->total_expense);
						$row["bankdeposit"] = sprintf('%0.2f', $branchsuspense->bank_deposit);
						$row["branchdeposit"] = sprintf('%0.2f', $branchsuspense->branch_deposit);
						$balanceWithoutCF = $branchsuspense->total_income-($branchsuspense->total_expense+$branchsuspense->bank_deposit+$branchsuspense->branch_deposit);
						$row["balance"] = sprintf('%0.2f', $balanceWithoutCF);
						
						$cf_amt = 0;
						$checkString = "";
						$col ="";
						$nextDay = strtotime(date("Y-m-d", strtotime($branchsuspense->reportDate)) . " +1 day");
						$nextDay = date ( 'Y-m-d' , $nextDay );
						$cf_details = \IncomeTransaction::where("branchId","=",$branchsuspense->branchId)->where("date","=",$nextDay)->where("status","=","ACTIVE")->where("lookupValueId","=","194")->get();
						if(count($cf_details)>0){
							$cf_details = $cf_details[0];
							$cf_amt = $cf_details->amount;
						}
						if($cf_amt>($branchsuspense->bank_deposit+$branchsuspense->branch_deposit)){
							$rem = round(($cf_amt-($branchsuspense->bank_deposit+$branchsuspense->branch_deposit)),2);
							$checkString = $rem." (LESS)";
							$col = "red";
						}
						else if($cf_amt<($branchsuspense->bank_deposit+$branchsuspense->branch_deposit)){
							$rem = round(($cf_amt-($branchsuspense->bank_deposit+$branchsuspense->branch_deposit)),2);
							$checkString = -1*$rem." (MORE)";
							$col = "green";
						}
						else {
							$checkString="DONE";
							$col="lightgrey";
						}
						
						$row["carryforward"] = sprintf('%0.2f', ($balanceWithoutCF-$cf_amt));
						$row["settlement"] = sprintf('%0.2f', $cf_amt);
						$row["status"] = "<span style='color:".$col.";font-weight:bold;'>".$checkString."</span>"; 
						$row["comments"] = $branchsuspense->comments;
						$date = date("d-m-Y",strtotime($branchsuspense->reportDate));
						$row["action"] = '<a href="report?reporttype=dailysettlement&branch='.$branchsuspense->branchId.'&date='.$date.'" target="_blank"><button class="btn btn-minier btn-primary">&nbsp;&nbsp;EDIT&nbsp;&nbsp;</button></a>';
						$resp[] = $row;
					}
				}
				else if($values["branch"]>0){
					$branchsuspenses = \BranchSuspenseReport::where("branchId","=",$values["branch"])->whereBetween("reportDate",array($frmDt,$toDt))->OrderBy("reportDate")->get() ;
					foreach ($branchsuspenses as $branchsuspense){
						$row = array();
						$branchname = \OfficeBranch::where("id","=",$branchsuspense->branchId)->get();
						if(count($branchname)>0){
							$branchname = $branchname[0];
							$branchname = $branchname->name;
						}
						else {
							$branchname = "";
						}
						$row["branchname"] = $branchname;
						$row["reportdate"] = date("d-m-Y",strtotime($branchsuspense->reportDate));
						$row["income"] = sprintf('%0.2f', $branchsuspense->total_income);
						$row["expense"] = sprintf('%0.2f', $branchsuspense->total_expense);
						$row["bankdeposit"] = sprintf('%0.2f', $branchsuspense->bank_deposit);
						$row["branchdeposit"] = sprintf('%0.2f', $branchsuspense->branch_deposit);
						$balanceWithoutCF = $branchsuspense->total_income-($branchsuspense->total_expense+$branchsuspense->bank_deposit+$branchsuspense->branch_deposit);
						$row["balance"] = sprintf('%0.2f', $balanceWithoutCF);
						
						$cf_amt = 0;
						$checkString = "";
						$col ="";
						$nextDay = strtotime(date("Y-m-d", strtotime($branchsuspense->reportDate)) . " +1 day");
						$nextDay = date ( 'Y-m-d' , $nextDay );
						$cf_details = \IncomeTransaction::where("branchId","=",$branchsuspense->branchId)->where("date","=",$nextDay)->where("status","=","ACTIVE")->where("lookupValueId","=","194")->get();
						if(count($cf_details)>0){
							$cf_details = $cf_details[0];
							$cf_amt = $cf_details->amount;
						}
						if($cf_amt>($branchsuspense->bank_deposit+$branchsuspense->branch_deposit)){
							$rem = round(($cf_amt-($branchsuspense->bank_deposit+$branchsuspense->branch_deposit)),2);
							$checkString = $rem." (LESS)";
							$col = "red";
						}
						else if($cf_amt<($branchsuspense->bank_deposit+$branchsuspense->branch_deposit)){
							$rem = round(($cf_amt-($branchsuspense->bank_deposit+$branchsuspense->branch_deposit)),2);
							$checkString = -1*$rem." (MORE)";
							$col = "green";
						}
						else {
							$checkString="DONE";
							$col="lightgrey";
						}
						
						$row["carryforward"] = sprintf('%0.2f', ($balanceWithoutCF-$cf_amt));
						$row["settlement"] = sprintf('%0.2f', $cf_amt);
						$row["status"] = "<span style='color:".$col.";font-weight:bold;'>".$checkString."</span>"; 
						$row["comments"] = $branchsuspense->comments;
						$date = date("d-m-Y",strtotime($branchsuspense->reportDate));
						$row["action"] = '<a href="report?reporttype=dailysettlement&branch='.$branchsuspense->branchId.'&date='.$date.'" target="_blank"><button class="btn btn-minier btn-primary">&nbsp;&nbsp;EDIT&nbsp;&nbsp;</button></a>';
						$resp[] = $row;
					}
				}
			}
			echo json_encode($resp);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$branches =  \OfficeBranch::ALL();
		$branches_arr = array();
		$branches_arr["0"] = "ALL BRANCHES";
		foreach ($branches as $branch){
			$branches_arr[$branch->id] = $branch->name;
		}
		$form_field = array("name"=>"branch", "content"=>"branch ", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>$branches_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "dailysettlement";
		return View::make('reports.dailysettlementreportsreport', array("values"=>$values));
	}
	
	private function getInchargeTransactionsReport($values){
		if (\Request::isMethod('post'))
		{
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			$resp2 = array();			
			if(isset($values["opening_balance"]) && $values["opening_balance"] == "yes"){
				$init_amt = 0;
				$frmDt1 = date("Y-m-d", strtotime("01-01-2016"));
				DB::statement(DB::raw("CALL incharge_transaction_report('".$frmDt1."', '".$toDt."');"));
				$sql = "SELECT sum(amount) as amt FROM `temp_incharge_transaction` where (lookupValueId='251' and inchargeId=".$values['incharge'].") or (lookupValueId='265' and  employeeId=".$values['incharge'].")";
				$rec = DB::select( DB::raw($sql));
				$rec = $rec[0];
				$total_credit = $rec->amt;
				
				$sql = "SELECT sum(amount) as amt FROM `temp_incharge_transaction` where  ((lookupValueId!='251' or lookupValueId is NULL) and inchargeId=".$values['incharge'].") or ((lookupValueId!='265' or lookupValueId is NULL) and employeeId=".$values['incharge'].")";
				$rec = DB::select( DB::raw($sql));
				$rec = $rec[0];
				$total_debit = $rec->amt;

				$end_balance = $init_amt+($total_credit-$total_debit);
			
				$sql = "SELECT sum(amount) as amt FROM `temp_incharge_transaction` where (lookupValueId='251' and inchargeId=".$values['incharge']." and date<'".$frmDt."' ) or (lookupValueId='265' and employeeId=".$values['incharge']." and date<'".$frmDt."')";
				$rec = DB::select( DB::raw($sql));
				$rec = $rec[0];
				$total_credit_todate = $rec->amt;
			
				$sql = "SELECT sum(amount) as amt FROM `temp_incharge_transaction` where ((lookupValueId!='251' or lookupValueId is NULL) and inchargeId=".$values['incharge']." and date<'".$frmDt."') or ((lookupValueId!='265' or lookupValueId is NULL) and employeeId=".$values['incharge']." and date<'".$frmDt."')";
				$rec = DB::select( DB::raw($sql));
				$rec = $rec[0];
				$total_debit_todate = $rec->amt;
				
				$start_balance = $init_amt+($total_credit_todate-$total_debit_todate);
			
				$sql = "SELECT sum(amount) as amt FROM `temp_incharge_transaction`  where (lookupValueId in(251,265) and ((lookupValueId=251 and inchargeId=".$values['incharge'].") or (lookupValueId=265 and type='income' and inchargeId=".$values['incharge'].")) and date between '".$frmDt."' and '".$toDt."')";
				$rec = DB::select( DB::raw($sql));
				$rec = $rec[0];
				$total_credit_todate = $rec->amt;
			
				$sql = "SELECT sum(amount) as amt FROM `temp_incharge_transaction` where ((((lookupValueId!='251' and  inchargeId=".$values['incharge']." and type='expense') or (inchargeId=".$values['incharge']." and lookupValueId is null))or (inchargeId=".$values['incharge']." and lookupValueId='161' and type='income')) and date between'".$frmDt."' and '".$toDt."')";
				$rec = DB::select( DB::raw($sql));
				$rec = $rec[0];
				$total_debit_todate = $rec->amt;
				
				echo json_encode(array("opening_balance"=>$start_balance,"closing_balance"=>$end_balance,"total_credit"=>$total_credit_todate,"total_debit"=>$total_debit_todate));
				return;
			}
			
			$totexpenses = 0;
			$totrepairs = 0;
			$totpurchases = 0;
			$totsalaries = 0;
			$totfuel = 0;
			$totincome = 0;
			$select_args = array();
			$select_args[] = "officebranch.name as branch";
			$select_args[] = "incometransactions.amount as amount";
			$select_args[] = "incometransactions.date as date";
			$select_args[] = "incometransactions.remarks as remarks";
			$select_args[] = "employee.fullName as name";
			if(isset($values["incharge"])){
				//$val["test"];
				$select_args = array();
				$select_args[] = "officebranch.name as branch";
				$select_args[] = "incometransactions.amount as amount";
				$select_args[] = "incometransactions.date as date";
				$select_args[] = "incometransactions.remarks as remarks";
				$select_args[] = "incometransactions.paymentType as paymentType";
				$select_args[] = "incometransactions.bankAccount as bankAccountId";
				$select_args[] = "incometransactions.bankName as bankName";
				$select_args[] = "incometransactions.accountNumber as accountNumber";
				$select_args[] = "incometransactions.chequeNumber as chequeNumber";
				$select_args[] = "incometransactions.issueDate as issueDate";
				$select_args[] = "incometransactions.transactionDate as transactionDate";
				$select_args[] = "incometransactions.lookupValueId as lookupValueId";
				
				$inchargetransactions = \IncomeTransaction::leftjoin("officebranch","officebranch.id","=","incometransactions.branchId")
							->leftjoin("employee","employee.id","=","incometransactions.createdBy")
							//->whereRaw("( inchargeId=".$values["incharge"]." or employeeId=".$values["incharge"].") ")
							->where("inchargeId","=",$values["incharge"])
							->whereIn("lookupValueId",array(265))
							->where("incometransactions.status","=","ACTIVE")
							->whereBetween("date",array($frmDt,$toDt))
							->OrderBy("date")->select($select_args)->get() ;
				//print_r($inchargetransactions);die();
				
				foreach ($inchargetransactions as $inchargetransaction){
					$row = array();
					$row["branch"] = $inchargetransaction->branch;
					//$row["type"] =  "<span style='color:green;'>Debited from Incharge Account</span>";
					if($inchargetransaction->lookupValueId==265){
						$row["type"] =  "<span style='color:green;'>Credited (from other Incharge)</span>";
					}
					$row["amount"] = $inchargetransaction->amount;
					if($inchargetransaction->paymentType != "cash"){
						if($inchargetransaction->paymentType == "ecs" || $inchargetransaction->paymentType == "neft" || $inchargetransaction->paymentType == "rtgs" || $inchargetransaction->paymentType == "cheque_debit" || $inchargetransaction->paymentType == "cheque_credit"){
							$inchargetransaction->paymentType = "Payment Type : ".$inchargetransaction->paymentType."<br/>";
							$bank_dt = \BankDetails::where("id","=",$inchargetransaction->bankAccount)->first();
							if(count($bank_dt)>0){
								$inchargetransaction->paymentType = $inchargetransaction->paymentType."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
							}
							$inchargetransaction->paymentType = $inchargetransaction->paymentType."Ref No : ".$inchargetransaction->chequeNumber;
						}
						if($inchargetransaction->paymentType == "credit_card" || $inchargetransaction->paymentType == "debit_card"){
							$inchargetransaction->paymentType = "Payment Type : ".$inchargetransaction->paymentType."<br/>";
							$bank_dt = \Cards::where("id","=",$inchargetransaction->bankAccount)->first();
							if(count($bank_dt)>0){
								$inchargetransaction->paymentType = $inchargetransaction->paymentType."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
							}
							$inchargetransaction->paymentType = $inchargetransaction->paymentType."Ref No : ".$inchargetransaction->chequeNumber;
						}
						if($inchargetransaction->paymentType == "dd"){
							$inchargetransaction->paymentType = "Payment Type : ".$inchargetransaction->paymentType."<br/>";
							$inchargetransaction->paymentType = $inchargetransaction->paymentType."Ref No : ".$inchargetransaction->chequeNumber;
						}
					}
					$row["paymentType"] = $inchargetransaction->paymentType;					
					$row["date"] = date("d-m-Y",strtotime($inchargetransaction->date));
					$row["remarks"] = $inchargetransaction->remarks;
					$row["name"] = $inchargetransaction->name;
					$resp[] = $row;
				}
				
				$select_args = array();
				$select_args[] = "officebranch.name as branch";
				$select_args[] = "expensetransactions.amount as amount";
				$select_args[] = "expensetransactions.date as date";
				$select_args[] = "expensetransactions.remarks as remarks";
				$select_args[] = "employee.fullName as name";
				$select_args[] = "expensetransactions.paymentType as paymentType";
				$select_args[] = "expensetransactions.bankAccount as bankAccountId";
				$select_args[] = "expensetransactions.bankName as bankName";
				$select_args[] = "expensetransactions.accountNumber as accountNumber";
				$select_args[] = "expensetransactions.chequeNumber as chequeNumber";
				$select_args[] = "expensetransactions.issueDate as issueDate";
				$select_args[] = "expensetransactions.transactionDate as transactionDate";
				$qry = \ExpenseTransaction::leftjoin("officebranch","officebranch.id","=","expensetransactions.branchId")
										->leftjoin("employee","employee.id","=","expensetransactions.createdBy")
										//->whereIn("lookupValueId",array(251,265))
										->whereRaw('(expensetransactions.lookupValueId in(251) and ((lookupValueId=251 and inchargeId='.$values["incharge"].')))');
							$qry->where("expensetransactions.status","=","ACTIVE")
							->whereBetween("date",array($frmDt,$toDt));
				$inchargetransactions=	$qry->OrderBy("date")->select($select_args)->get() ;
				foreach ($inchargetransactions as $inchargetransaction){
					$row = array();
					$row["branch"] = $inchargetransaction->branch;
					$row["type"] =  "<span style='color:red;'>Credited into Incharge Account</span>";
					$row["amount"] = $inchargetransaction->amount;
					if($inchargetransaction->paymentType != "cash"){
						if($inchargetransaction->paymentType == "ecs" || $inchargetransaction->paymentType == "neft" || $inchargetransaction->paymentType == "rtgs" || $inchargetransaction->paymentType == "cheque_debit" || $inchargetransaction->paymentType == "cheque_credit"){
							$inchargetransaction->paymentType = "Payment Type : ".$inchargetransaction->paymentType."<br/>";
							$bank_dt = \BankDetails::where("id","=",$inchargetransaction->bankAccount)->first();
							if(count($bank_dt)>0){
								$inchargetransaction->paymentType = $inchargetransaction->paymentType."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
							}
							$inchargetransaction->paymentType = $inchargetransaction->paymentType."Ref No : ".$inchargetransaction->chequeNumber;
						}
						if($inchargetransaction->paymentType == "credit_card" || $inchargetransaction->paymentType == "debit_card"){
							$inchargetransaction->paymentType = "Payment Type : ".$inchargetransaction->paymentType."<br/>";
							$bank_dt = \Cards::where("id","=",$inchargetransaction->bankAccount)->first();
							if(count($bank_dt)>0){
								$inchargetransaction->paymentType = $inchargetransaction->paymentType."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
							}
							$inchargetransaction->paymentType = $inchargetransaction->paymentType."Ref No : ".$inchargetransaction->chequeNumber;
						}
						if($inchargetransaction->paymentType == "dd"){
							$inchargetransaction->paymentType = "Payment Type : ".$inchargetransaction->paymentType."<br/>";
							$inchargetransaction->paymentType = $inchargetransaction->paymentType."Ref No : ".$inchargetransaction->chequeNumber;
						}
					}
					$row["paymentinfo"] = $inchargetransaction->paymentType;
					$row["date"] = date("d-m-Y",strtotime($inchargetransaction->date));
					$row["remarks"] = $inchargetransaction->remarks;
					$row["name"] = $inchargetransaction->name;
					$resp[] = $row;
				}
				
 				DB::statement(DB::raw("CALL incharge_transaction_report('".$frmDt."', '".$toDt."');"));
				$recs = DB::select( DB::raw("SELECT *,temp_incharge_transaction.entity as entity, temp_incharge_transaction.name as purpose, temp_incharge_transaction.createdBy as createdBy, officebranch.name as branchname FROM `temp_incharge_transaction` left join officebranch on officebranch.id=temp_incharge_transaction.branchId where ((inchargeId=".$values['incharge']." and lookupValueId is null )or(inchargeId=".$values['incharge']." and type='expense')or(inchargeId=".$values['incharge']." and lookupValueId=161 and type='income')) order by date"));
				foreach ($recs as $rec){
					if($rec->lookupValueId==251){
						continue;
					}
					$row = array();
					$row["branch"] = $rec->branchname;
					if($rec->branchname == ""){
						$row["branch"] = $rec->depotName."(".$rec->clientName.")";
					}
					$row["date"] = date("d-m-Y",strtotime($rec->date));
					$row["amount"] =  $rec->amount;
					if($rec->paymentType != "cash"){
						if($rec->paymentType == "ecs" || $rec->paymentType == "neft" || $rec->paymentType == "rtgs" || $rec->paymentType == "cheque_debit" || $rec->paymentType == "cheque_credit"){
							$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
							$bank_dt = \BankDetails::where("id","=",$rec->bankAccountId)->first();
							if(count($bank_dt)>0){
								$rec->paymentType = $rec->paymentType."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
							}
							$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
						}
						if($rec->paymentType == "credit_card" || $rec->paymentType == "debit_card"){
							$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
							$bank_dt = \Cards::where("id","=",$rec->bankAccount)->first();
							if(count($bank_dt)>0){
								$rec->paymentType = $rec->paymentType."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
							}
							$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
						}
						if($rec->paymentType == "dd"){
							$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
							$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
						}
					}
					$row["paymentType"] = $rec->paymentType;
					
					$row["type"] =  strtoupper($rec->type);
					if($row["type"]=="EXPENSE"){
						$totexpenses = $totexpenses+$rec->amount;
					}
					if($row["type"]=="REPAIR TRANSACTION"){
						$totrepairs = $totrepairs+$rec->amount;
					}
					if($row["type"]=="PURCHASE ORDER"){
						$totpurchases = $totpurchases+$rec->amount;
					}
					if($row["type"]=="OFFICE PURCHASE ORDER"){
						$totpurchases = $totpurchases+$rec->amount;
					}
					if($row["type"]=="SALARY TRANSACTION"){
						$totsalaries = $totsalaries+$rec->amount;
					}
					if($row["type"]=="FUEL TRANSACTION"){
						$totfuel = $totfuel+$rec->amount;
					}
					if($row["type"]=="INCOME"){
						$totincome = $totincome+$rec->amount;
					}
					
					$row["purpose"] =  strtoupper($rec->purpose);
					if($rec->purpose == ""){
						$row["purpose"] = $rec->entity;
					}
					$row["paidto"] =  strtoupper($rec->entityValue);
					if($rec->type=="expense"){
						if($rec->lookupValueId==999){
							if($inchargetransaction->entityValue>0){
								$prepaidName = \LookupTypeValues::where("id","=",$rec->entityValue)->first();
								$prepaidName = $prepaidName->name;
								$row["purpose"] = strtoupper($rec->entity)." - ".$prepaidName;
							}
							else{
								$row["purpose"] = strtoupper($rec->entity);
							}
						}
						else if($rec->lookupValueId==998){
							if($rec->entityValue>0){
								$creditsupplier = \CreditSupplier::where("id","=",$rec->entityValue)->first();
								$creditsupplier = $creditsupplier->supplierName;
								$row["purpose"] = strtoupper($rec->entity)." - ".$creditsupplier;
							}
							else{
								$row["purpose"] = strtoupper($rec->entity);
							}
						}
						else if($rec->lookupValueId==997){
							if($rec->entityValue>0){
								$fuelstation = \FuelStation::where("id","=",$rec->entityValue)->first();
								$fuelstation = $fuelstation->name;
								$row["purpose"] = strtoupper($rec->entity);
								$row["paidto"] =  strtoupper($fuelstation);
							}
							else{
								$row["purpose"] = strtoupper($rec->entity);
							}
						}
						else if($rec->lookupValueId==991){
							if($rec->entityValue>0){
								$dfid = \DailyFinance::where("id","=",$rec->entityValue)->first();
								$dfid = $dfid->financeCompanyId;
								$finanacecompany = \FinanceCompany::where("id","=",$dfid)->first();
								$finanacecompany = $finanacecompany->name;
								$row["purpose"] = strtoupper($rec->entity)." - ".$finanacecompany;
							}
							else{
								$row["purpose"] = strtoupper($inchargetransaction->entity);
							}
						}
					}
					$vehreg = "";
					if($rec->type == "LOCAL"){
						$row["purpose"] = "LOCAL TRIP ADVANCE : <br/>";
						$entities = \BusBookings::where("id","=",$rec->tripId)->get();
						foreach ($entities as $entity){
							$entity["sourcetrip"] = $entity["source_start_place"]."<br/> ".$entity["source_end_place"];
							$entity["sourcetrip"] = $entity["sourcetrip"]."<br/>Date & Time &nbsp;: ".$entity["source_date"]." ".$entity["source_time"];
							$row["purpose"] = $row["purpose"].$entity->sourcetrip;
						}
						if($rec->entityValue>0){
							$vehicle = \Vehicle::where("id","=",$rec->entityValue)->get();
							if(count($vehicle)>0){
								$vehicle = $vehicle[0];
								$vehreg = $vehicle->veh_reg;
							}
						}
						$row["paidto"] = $vehreg;
					}
					if($rec->type == "DAILY"){
						$select_args = array();
						$select_args[] = "vehicle.veh_reg as vehicleId";
						$select_args[] = "tripdetails.tripStartDate as tripStartDate";
						$select_args[] = "tripdetails.id as routeInfo";
						$select_args[] = "tripdetails.tripCloseDate as tripCloseDate";
						$select_args[] = "tripdetails.routeCount as routes";
						$select_args[] = "tripdetails.id as id";
						$routeInfo = "";
						$entities = \TripDetails::where("tripdetails.id","=",$rec->tripId)->leftjoin("vehicle", "vehicle.id","=","tripdetails.vehicleId")->select($select_args)->get();
						foreach ($entities as $entity){
							$entity["tripStartDate"] = date("d-m-Y",strtotime($entity["tripStartDate"]));
							$tripservices = \TripServiceDetails::where("tripId","=",$entity->id)->where("status","=","Running")->get();
							foreach($tripservices as $tripservice){
								$select_args = array();
								$select_args[] = "cities.name as sourceCity";
								$select_args[] = "cities1.name as destinationCity";
								$select_args[] = "servicedetails.serviceNo as serviceNo";
								$select_args[] = "servicedetails.active as active";
								$select_args[] = "servicedetails.serviceStatus as serviceStatus";
								$select_args[] = "servicedetails.id as id";
								$service = \ServiceDetails::where("servicedetails.id","=",$tripservice->serviceId)->join("cities","cities.id","=","servicedetails.sourceCity")->join("cities as cities1","cities1.id","=","servicedetails.destinationCity")->select($select_args)->get();
								if(count($service)>0){
									$service = $service[0];
									$routeInfo = $routeInfo."<span style='font-size:13px; font-weight:bold; color:red;'>".$service->serviceNo."</span> - &nbsp; ".$service->sourceCity." TO ".$service->destinationCity."<br/>";
								}
							}
							$row["purpose"] = "DAILY TRIP ADVANCE : <br/>";
							$row["purpose"] = $row["purpose"].$routeInfo;
							$row["paidto"] = $entity->vehicleId;
						}
					}
					if($row["paidto"] == "0"){
						$row["paidto"] = "";
					}
					$row["remarks"] =$rec->remarks;
					$row["name"] = $rec->createdBy;
					$resp2[] = $row;
				} 
			}
			$resp_json = array("data1"=>$resp,"data2"=>$resp2,"total_income"=>$totincome,"total_expenses"=>$totexpenses,"total_repairs"=>$totrepairs,"total_purchase"=>$totpurchases,"total_salaries"=>$totsalaries,"total_fuel"=>$totfuel);
			echo json_encode($resp_json);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		
		$select_args = array();
		$select_args[] = "inchargeaccounts.empid as id";
		$select_args[] = "employee.fullName as fullName";	
		$incharges =  \InchargeAccounts::join("employee","employee.id","=","inchargeaccounts.empId")->select($select_args)->get();
		$incharges_arr = array();
		foreach ($incharges as $incharge){
			$incharges_arr[$incharge->id] = $incharge->fullName;
		}
		$form_field = array("name"=>"incharge", "content"=>"incharge ", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>$incharges_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"opening_balance", "value"=>"no", "content"=>"", "readonly"=>"",  "required"=>"","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "dailysettlement";
		return View::make('reports.inchargetransactionsreport', array("values"=>$values));
	}
	
	private function getEstimateSalaryReport($values){
		if (\Request::isMethod('post'))
		{
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			$total_amount = 0;
			if(isset($values["employeetype"]) && $values["employeetype"]=="OFFICE"){
				if(true){
					$entities = \Employee::whereRaw(" status='ACTIVE' and (roleId!=20 and roleId!=19) and FIND_IN_SET('".$values["officebranch"]."',employee.officeBranchIds)")
												  ->get();
					foreach ($entities as $entity){
						$row = array();
						$branch = \OfficeBranch::where("id","=",$values["officebranch"])->first();
						$row["branch"] = $branch->name;
						$row["employee"] = $entity->fullName." - ".$entity->empCode;
						$row["month"] = "";
						$salary_amt = 0;
						$salary = \SalaryDetails::where("empId","=",$entity->id)->get();
						if(count($salary)>0){
							$salary = $salary[0];
							$salary_amt = $salary->salary;
						}
						$row["salary"] = $salary_amt;
						
						$leaves = 0;
						$leaves_amt = 0;
						$fromdt = date("Y-m-d",strtotime($values["fromdate"]));
						$todt = date("Y-m-d",strtotime($values["todate"]));
						$recs = DB::select( DB::raw($sql = "select count(*) as cnt from attendence where attendence.empId='".$entity->id."' and (attendenceStatus = 'A') and date between '$fromdt' and '$todt'") );
						foreach ($recs as $rec){
							$leaves = $rec->cnt;
							$leaves = $leaves/2;
							$after_leaves = $leaves;
							$after_leaves = $leaves-$values["casualleaves"];
							if($after_leaves<0){
								$after_leaves = 0;
							}
							
							$date1=date_create($fromdt);
							$date2=date_create($todt);
							$diff=date_diff($date1,$date2);
							$working_days =  $diff->format("%a");
							$working_days = $working_days+1;
							$leaves_amt = ($salary_amt/$working_days)*$after_leaves;
							$leaves_amt = intval($leaves_amt);
						}
						$row["leaves"] = $leaves;
						$row["appliedleaves"] = $after_leaves;
						$amounts = \SalaryTransactions::where("empId","=",$entity->id)
														->where("source","=","ESTIMATE")
														->wherebetween("salaryMonth",array($frmDt,$toDt))->get();
						if(count($amounts)>0){
							foreach($amounts as $amount){
								$row["month"] = date("F-Y", strtotime($amount->salaryMonth));
								$row["leave_amount"] = $amount->leaveDeductions;			
								$row["due_amount"] = $amount->dueDeductions;
								$row["other_amount"] = $amount->otherAmount;
								$row["estimated_salary"] = $amount->salaryPaid;
							}
						}
						else{
							continue;
						}
						$total_amount = $total_amount+$row["estimated_salary"];
						$resp[] = $row;
					}
				}
			}
			if(isset($values["employeetype"]) && $values["employeetype"]=="CLIENT BRANCH"){
				if(true){
					if(isset($values["depot"]) && $values["depot"]==0){
						DB::statement(DB::raw("CALL contract_driver_helper_all('".$values["clientname"]."');"));
					}
					else{
						DB::statement(DB::raw("CALL contract_driver_helper('".$values["depot"]."', '".$values["clientname"]."');"));
					}
					$entities = DB::select( DB::raw("select * from temp_contract_drivers_helpers where status='".$values["show_employees"]."' group by id"));
					$select_args = array();
					$select_args[] = "clients.name as cname";
					$select_args[] = "depots.name as dname";
					$select_args[] = "vehicle.veh_reg as veh_reg";
					$select_args[] = "contract_vehicles.helperId as helperId";
					foreach ($entities as $entity){
						$row = array();
						$emp_contract = \ContractVehicle::leftjoin("contracts","contracts.id","=","contract_vehicles.contractId")
											->leftjoin("clients","clients.id","=","contracts.clientId")
											->leftjoin("depots","depots.id","=","contracts.depotId")
											->leftjoin("vehicle","vehicle.id","=","contract_vehicles.vehicleId")
											->where("contract_vehicles.status","=",$values["show_employees"])
											->whereRaw("  (driver1Id=".$entity->id." or driver2Id=".$entity->id." or driver3Id=".$entity->id." or driver4Id=".$entity->id." or driver5Id=".$entity->id." or helperId=".$entity->id." ) ")
											->select($select_args)->get();
						$row["branch"] = "";
						$row["employee"] = $entity->fullName." - ".$entity->empCode;
						if(count($emp_contract)>0){
							$emp_contract = $emp_contract[0];
							$row["branch"] = $emp_contract->cname." (".$emp_contract->dname.")";
							if($emp_contract->helperId == $entity->id){
								$row["employee"] = $entity->fullName." - ".$entity->empCode." (".$emp_contract->veh_reg."-HELPER)";
							}
							else{
								$row["employee"] = $entity->fullName." - ".$entity->empCode." (".$emp_contract->veh_reg."-DRIVER)";
							}
						}
						$row["month"] = "";
						$salary_amt = 0;
						$salary = \SalaryDetails::where("empId","=",$entity->id)->get();
						if(count($salary)>0){
							$salary = $salary[0];
							$salary_amt = $salary->salary;
						}
						$row["salary"] = $salary_amt;
			
						$leaves = 0;
						$leaves_amt = 0;
						$fromdt = date("Y-m-d",strtotime($values["fromdate"]));
						$todt = date("Y-m-d",strtotime($values["todate"]));
						$recs = DB::select( DB::raw($sql = "select count(*) as cnt from attendence where attendence.empId='".$entity->id."' and (attendenceStatus = 'A') and date between '$fromdt' and '$todt'") );
						foreach ($recs as $rec){
							$leaves = $rec->cnt;
							$leaves = $leaves/2;
							$after_leaves = $leaves;
							$after_leaves = $leaves-$values["casualleaves"];
							if($after_leaves<0){
								$after_leaves = 0;
							}
							
							$date1=date_create($fromdt);
							$date2=date_create($todt);
							$diff=date_diff($date1,$date2);
							$working_days =  $diff->format("%a");
							$working_days = $working_days+1;
							$leaves_amt = ($salary_amt/$working_days)*$after_leaves;
							$leaves_amt = intval($leaves_amt);
						}
						$row["leaves"] = $leaves;
						$row["appliedleaves"] = $after_leaves;
						$amounts = \SalaryTransactions::where("empId","=",$entity->id)
														->where("source","=","ESTIMATE")
														->wherebetween("salaryMonth",array($frmDt,$toDt))->get();
						if(count($amounts)>0){
							foreach($amounts as $amount){
								$row["month"] = date("F-Y", strtotime($amount->salaryMonth));
								$row["leave_amount"] = $amount->leaveDeductions;			
								$row["due_amount"] = $amount->dueDeductions;
								$row["other_amount"] = $amount->otherAmount;
								$row["estimated_salary"] = $amount->salaryPaid;
							}
						}
						else{
							continue;
						}
						$total_amount = $total_amount+$row["estimated_salary"];
						$resp[] = $row;
					}
				}
			}
			$row = array();
			$row["1"] = ""; $row["2"] = "";  $row["3"] = ""; $row["4"] = ""; $row["5"] = ""; $row["6"] = ""; $row["7"] = "";$row["8"] = ""; $row["amt"] = "TOTAL ESTIMATED AMOUNT";
			$row["total_amt"] = $total_amount;
			$resp[] = $row;
			echo json_encode($resp);
			return;
		}
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$branch_arr = array();
		$branches = \OfficeBranch::where("status","=","ACTIVE")->get();
		foreach ($branches as $branch){
			$branch_arr[$branch->id] = $branch->name;
		}
		
		$clients =  \Client::where("status","=","ACTIVE")->get();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
		
		$report_type_arr = array();
		$form_field = array("name"=>"employeetype", "content"=>"employee type", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"enableClientDepot(this.value);"),  "options"=>array("OFFICE"=>"OFFICE", "CLIENT BRANCH"=>"CLIENT BRANCH"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"officebranch", "content"=>"office branch", "readonly"=>"","required"=>"", "type"=>"select", "options"=>$branch_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"casualleaves", "value"=>2, "content"=>"casual leaves", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"show_employees","content"=>"employees", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>array("ACTIVE"=>"ACTIVE","INACTIVE"=>"INACTIVE"),  "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$add_form_fields = array();
		$form_info["form_fields"] = $form_fields;
		$form_info["add_form_fields"] = $add_form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.estimatesalaryreport', array("values"=>$values));
	}
	private function getEmployeeInfoReport($values){
		if (\Request::isMethod('post'))
		{
				
			//$fromDt = date("Y-m-d", strtotime($values["fromdate"]));
			//$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			$total_amount = 0;
			if(isset($values["employeetype"]) && $values["employeetype"]=="OFFICE EMPLOYEE"){
				$select_args = array();
				$select_args[] = "employee.empCode as empCode";
				$select_args[] = "employee.fullName as fullName";
				$select_args[] = "employee.officeBranchIds as officeBranchIds";
				$select_args[] = "employee.mobileNo as mobileNo";
				$select_args[] = "employee.roleId as roleId";
				$select_args[] = "employee.emailId as emailId";
				$select_args[] = "employee.status as status";
				$select_args[] = "employee.workGroup as workGroup";
				$select_args[] = "employee.clientBranch as clientBranch";
				$entities = \Employee::whereRaw("employee.status='ACTIVE' and (roleId!=20 and roleId!=19) and FIND_IN_SET('".$values["officebranch"]."',employee.officeBranchIds)")->get();
				$role_arr = array();
				$roles = \Role::where("status","=","ACTIVE")->get();
				foreach ($roles as $role){
					$role_arr[$role->id] = $role->roleName;
				}
				$branch_arr = array();
				$branches = \OfficeBranch::where("status","=","ACTIVE")->get();
				foreach ($branches as $branch){
					$branch_arr[$branch->id] = $branch->name;
				}
				foreach ($entities as $entity){
					$row = array();
					$row["empCode"] = $entity->empCode;
					$row["fullName"] = $entity->fullName;
					
					if(isset($branch_arr[$entity->officeBranchIds])){
						$row["officeBranchIds"] = $branch_arr[$entity->officeBranchIds];
					}
					else {
						$row["officeBranchIds"] = "";
					}
					$row["clientBranch"] = "NO DEPOT";
					$row["mobileNo"] = $entity->mobileNo;
					if(isset($role_arr[$entity->roleId])){
						$row["roleName"] = $role_arr[$entity->roleId];
					}
					else {
						$row["roleName"] = "";
					}
					$row["emailid"] = $entity->emailId;
					$row["status"] = $entity->status;
					$resp[] = $row;
				}
			}
			if(isset($values["employeetype"]) && $values["employeetype"]=="CLIENT BRANCH"){
				if(true){
				if($values["depot"]==0){
						\DB::statement(\DB::raw("CALL contract_driver_helper_all('".$values["clientname"]."');"));
					}
					else{
						\DB::statement(\DB::raw("CALL contract_driver_helper('".$values["depot"]."', '".$values["clientname"]."');"));
					}
					
					$entities = \DB::select( \DB::raw("select contracts.depotId, contracts.clientId, employee.empCode, employee.fullName, employee.mobileNo, employee.roleId, employee.emailId, temp_contract_drivers_helpers.status as status, employee.workGroup as workGroup  from temp_contract_drivers_helpers 
													left join employee on employee.id=temp_contract_drivers_helpers.id 
													left join contracts on contracts.id=temp_contract_drivers_helpers.contractId
													group by temp_contract_drivers_helpers.id"));
					$role_arr = array();
					$roles = \Role::where("status","=","ACTIVE")->get();
					foreach ($roles as $role){
						$role_arr[$role->id] = $role->roleName;
					}
					$clients_arr = array();
					$clients = \Client::where("status","=","ACTIVE")->get();
					foreach ($clients as $client){
						$clients_arr[$client->id] = $client->name;
					}
					$depot_arr = array();
					$depots = \Depot::where("status","=","ACTIVE")->get();
					foreach ($depots as $depot){
						$depot_arr[$depot->id] = $depot->name;
					}
					
					foreach ($entities as $entity){
						$row = array();
						$row["empCode"] = $entity->empCode;
						$row["fullName"] = $entity->fullName;
						$row["clientIds"] = $clients_arr[$entity->clientId];
						
						if(isset($depot_arr[$entity->depotId])){
							$row["clientBranch"] = $depot_arr[$entity->depotId];
						}
						else {
							$row["clientBranch"] = "";
						}
						
						$row["mobileNo"] = $entity->mobileNo;
						
						if(isset($role_arr[$entity->roleId])){
							$row["roleName"] = $role_arr[$entity->roleId];
						}
						else {
							$row["roleName"] = "";
						}						
						$row["email"] = "";
						$row["status"] = $entity->status;
						$resp[] = $row;
					}					
				}
			}
			echo json_encode($resp);
			return;
		}
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$branch_arr = array();
		$branches = \OfficeBranch::where("status","=","ACTIVE")->get();
		foreach ($branches as $branch){
			$branch_arr[$branch->id] = $branch->name;
		}
	
		$clients =  \Client::where("status","=","ACTIVE")->get();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$report_type_arr = array();
		$form_field = array("name"=>"employeetype", "content"=>"employee type", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"enableClientDepot(this.value);"),  "options"=>array("OFFICE EMPLOYEE"=>"OFFICE EMPLOYEE", "CLIENT BRANCH"=>"CLIENT BRANCH"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"officebranch", "content"=>"office branch", "readonly"=>"","required"=>"", "type"=>"select", "options"=>$branch_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		//$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		//$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$add_form_fields = array();
		$form_info["form_fields"] = $form_fields;
		$form_info["add_form_fields"] = $add_form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.empinforeport', array("values"=>$values));
	}
	
	private function getAttendenceReport($values){
		if (\Request::isMethod('post'))
		{
			
			$fromDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			$total_amount = 0;
			if(isset($values["employeetype"]) && $values["employeetype"]=="OFFICE"){
				$select_args = array();
				$select_args[] = "officebranch.name as branch";
				$select_args[] = "attendence_log.date as date";
				$select_args[] = "attendence_log.time as time";
				$select_args[] = "attendence_log.session as session";
				$select_args[] = "attendence_log.day as day";
				$select_args[] = "attendence_log.day as day";
				$select_args[] = "attendence_log.day as day";
				$select_args[] = "employee.fullName as name";
				
				$employees_arr = array();
				$employees = \Employee::whereRaw("employee.status='ACTIVE' and (roleId!=20 and roleId!=19) and FIND_IN_SET('".$values["officebranch"]."',employee.officeBranchIds)")->get();
				foreach($employees as $employee){
					$employees_arr[$employee->id] = $employee->fullName." (".$employee->empCode.")";
				}
				if(true){
					$tot_emps = \Employee::whereRaw(" status='ACTIVE' and (roleId!=20 and roleId!=19) and FIND_IN_SET('".$values["officebranch"]."',employee.officeBranchIds)")->count();
					
					$entities = \AttendenceLog::where("attendence_log.officeBranchId","=",$values["officebranch"])
									->join("officebranch","officebranch.id","=","attendence_log.officeBranchId")
									->join("employee","employee.id","=","attendence_log.createdBy")
									->whereBetween("date",array($fromDt,$toDt))
									->where("attendence_log.status","=","ACTIVE")
									->select($select_args)->orderBy("date")->get();
					foreach ($entities as $entity){
						$row = array();
						$row["branch"] = $entity->branch;
						$row["date"] = date("d-m-Y", strtotime($entity->date));
						$row["time"] = $entity->time;
						$row["session"] = $entity->session;
						$row["day"] = $entity->day;
						
						$absenties_str = "";
						$cnt = 0;
						$absenties = \Attendence::where("date","=",$entity->date)
										->where("session","=",$entity->session)
										->where("attendenceStatus","=","A")
										->get();
						foreach($absenties as $absentee){
							 if(isset($employees_arr[$absentee->empId])){
							 	$cnt++;
							 	$absenties_str = $employees_arr[$absentee->empId].", ".$absenties_str;
							 }
						}
						$row["cnt1"] = count($employees_arr)-$cnt;
						$row["cnt2"] = "Total Absentees : ".$cnt."<br/> ".$absenties_str;
						$row["createdby"] = $entity->name;
						$resp[] = $row;
					}
				}
			}
			if(isset($values["employeetype"]) && $values["employeetype"]=="CLIENT BRANCH"){
				if(true){
					$select_args = array();
					$select_args[] = "clients.name as clientname";
					$select_args[] = "depots.name as depotname";
					$select_args[] = "attendence_log.date as date";
					$select_args[] = "attendence_log.time as time";
					$select_args[] = "attendence_log.session as session";
					$select_args[] = "attendence_log.day as day";
					$select_args[] = "attendence_log.day as day";
					$select_args[] = "attendence_log.day as day";
					$select_args[] = "employee.fullName as name";
					$select_args[] = "clients.id as cid";
					$select_args[] = "depots.id as did";
					
					
					$employees_arr = array();
					if($values["depot"]==0){
						\DB::statement(\DB::raw("CALL contract_driver_helper_all('".$values["clientname"]."');"));
					}
					else{
						\DB::statement(\DB::raw("CALL contract_driver_helper('".$values["depot"]."', '".$values["clientname"]."');"));
					}
					$employees = \DB::select( \DB::raw("select * from temp_contract_drivers_helpers where status='".$values["show_employees"]."' group by id"));
					$tot_emps = count($employees);
					foreach($employees as $employee){
						$employees_arr[$employee->id] = $employee->fullName." (".$employee->empCode.")";
					}
					if(true){
						$sql = \AttendenceLog::where("attendence_log.clientId","=",$values["clientname"]);
								if($values["depot"]!=0){
										$sql->where("attendence_log.depotId","=",$values["depot"]);
								}
						$entities = $sql->leftjoin("clients","clients.id","=","attendence_log.clientId")
										->leftjoin("depots","depots.id","=","attendence_log.depotId")
										->leftjoin("employee","employee.id","=","attendence_log.createdBy")
										->whereBetween("date",array($fromDt,$toDt))
										->where("attendence_log.status","=","ACTIVE")
										->select($select_args)->orderBy("date")->get();
						foreach ($entities as $entity){
							$row = array();
							$row["branch"] = $entity->depotname." (".$entity->clientname.")";
							$row["date"] = date("d-m-Y", strtotime($entity->date));
							$row["time"] = $entity->time;
							$row["session"] = $entity->session;
							$row["day"] = $entity->day;
							
							$temp_emps = \ContractVehicle::join("contracts","contracts.id","=","contract_vehicles.contractId")
															->where("contracts.clientId","=",$entity->cid)
															->where("contracts.depotId","=",$entity->did)
															->where("contract_vehicles.status","=",$values["show_employees"])->get();
							$emp_cnt = 0;
							$temp_emps_arr = array();
							foreach ($temp_emps as $temp_emp){
								if($temp_emp->driver1Id>0){
									$emp_cnt++;
									$temp_emps_arr[] = $temp_emp->driver1Id;
								}
								if($temp_emp->driver12d>0){
									$emp_cnt++;
									$temp_emps_arr[] = $temp_emp->driver12d;
								}
								if($temp_emp->driver3Id>0){
									$emp_cnt++;
									$temp_emps_arr[] = $temp_emp->driver3Id;
								}
								if($temp_emp->driver4Id>0){
									$emp_cnt++;
									$temp_emps_arr[] = $temp_emp->driver4Id;
								}
								if($temp_emp->driver5Id>0){
									$emp_cnt++;
									$temp_emps_arr[] = $temp_emp->driver5Id;
								}
								if($temp_emp->helperId>0){
									$emp_cnt++;
									$temp_emps_arr[] = $temp_emp->helperId;
								}
							}
							
							$absenties_str = "";
							$cnt = 0;
							$absenties = \Attendence::where("date","=",$entity->date)
											->where("session","=",$entity->session)
											->where("attendenceStatus","=","A")
											->get();
							foreach($absenties as $absentee){
								 if(in_array($absentee->empId, $temp_emps_arr)){
								 	$cnt++;
								 	$absenties_str = $employees_arr[$absentee->empId].", ".$absenties_str;
								 }
							}
							
							$row["cnt1"] = $emp_cnt-$cnt;
							$row["cnt2"] = "Total Absentees : ".$cnt."<br/> ".$absenties_str;
							$row["createdby"] = $entity->name;
							$resp[] = $row;
						}
					}
				}
			}
			echo json_encode($resp);
			return;
		}
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$branch_arr = array();
		$branches = \OfficeBranch::where("status","=","ACTIVE")->get();
		foreach ($branches as $branch){
			$branch_arr[$branch->id] = $branch->name;
		}
	
		$clients =  \Client::where("status","=","ACTIVE")->get();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$report_type_arr = array();
		$form_field = array("name"=>"employeetype", "content"=>"employee type", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"enableClientDepot(this.value);"),  "options"=>array("OFFICE"=>"OFFICE", "CLIENT BRANCH"=>"CLIENT BRANCH"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"officebranch", "content"=>"office branch", "readonly"=>"","required"=>"", "type"=>"select", "options"=>$branch_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"show_employees","content"=>"employees", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>array("ACTIVE"=>"ACTIVE","INACTIVE"=>"INACTIVE"),  "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$add_form_fields = array();
		$form_info["form_fields"] = $form_fields;
		$form_info["add_form_fields"] = $add_form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.attendencereport', array("values"=>$values));
	}
	
	/*
	private function getAttendenceDetailedReport($values){
		if (\Request::isMethod('post'))
		{
			$fromDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			
			//$values["DSF"];
			$data = array();
			$select_args = array("employee.id", "employee.fullName", "employee.empCode", "employee.joiningDate", "employee.terminationDate");
			
			$entities = null;
			if($values["employeetype"] == "CLIENT BRANCH"){
				if(isset($values["depot"]) &&  $values["depot"] == 0){
					\DB::statement(\DB::raw("CALL contract_driver_helper_all('".$values["clientname"]."');"));
					$entities = \DB::select( \DB::raw("select * from temp_contract_drivers_helpers group by id"));
				}
				else{
					\DB::statement(\DB::raw("CALL contract_driver_helper('".$values["depot"]."', '".$values["clientname"]."');"));
					$entities = \DB::select( \DB::raw("select * from temp_contract_drivers_helpers group by id"));
				}
			}
			else{
				$entities = \Employee::whereRaw(" status='ACTIVE' and (roleId!=20 and roleId!=19) and FIND_IN_SET('".$values["officebranch"]."',employee.officeBranchIds)")
						->select($select_args)->get();
			}
			
			//$entities = $entities->toArray();
			foreach($entities as $entity){
				$data_values = array();
				$data_values[] = $entity->fullName."(".$entity->empCode.")";
				$date = date_create($fromDt);
				$today = date_create($toDt);
				$diff = date_diff($date,$today);
				$diff =  $diff->format("%a");
					
				$emptype ="office";
				if($values["employeetype"] == "CLIENT BRANCH"){
					$emptype = "driver";
				}
				$total_absent_days = 0; 
					
				for($i=0; $i<=$diff; $i++){
					$date1 = strtotime(date("Y-m-d",strtotime($entity->joiningDate)));
					$date2 = strtotime(date("Y-m-d",strtotime(date_format($date, 'Y-m-d'))));
					$date3 = strtotime(date('Y-m-01'));
			
					if($date1>$date2){
						$date = date_add($date, date_interval_create_from_date_string('1 days'));
						$data_values[] = "";
						continue;
					}
					$date1 = strtotime(date("Y-m-d",strtotime($entity->terminationDate)));
					$date2 = strtotime(date("Y-m-d",strtotime(date_format($date, 'Y-m-d'))));
			
					if($entity->terminationDate!="" && $entity->terminationDate!="0000-00-00" && $date1 != "1970-01-01" && $date1<$date2){
						$date = date_add($date, date_interval_create_from_date_string('1 days'));
						$data_values[] = "";
						continue;
					}
					$isHoliday = false;
					$qry = \AttendenceLog::where("date","=",date_format($date, 'Y-m-d'));
					if($values["employeetype"] == "CLIENT BRANCH"){
						$qry->where("depotId","=",$values["depot"])->where("clientId","=",$values["clientname"]);
					}
					else{
						$qry->where("officeBranchId","=",$values["officebranch"]);
					}
					$at_log = 	$qry->where("day","=","HOLIDAY")->where("session","=","MORNING")->get();
					if(count($at_log)>0){
						$isHoliday = true;
					}
					$session_val = "&nbsp;&nbsp;";
					$emp = \Attendence::where("empId","=",$entity->id)->where("session","=","MORNING")->where("date","=",date("Y-m-d", strtotime(date_format($date, 'd-m-Y'))))->get();
					if(count($emp)>0){
						$emp = $emp[0];
						if($emp->day=="HOLIDAY" || $isHoliday){
							$session_val =  "&nbsp;&nbsp;<span style='font-weight:bold; color:red'>".$emp["attendenceStatus"]."</span>";
						}
						else{
							$session_val =  "&nbsp;&nbsp;<span style='font-weight:bold; color:red'>".$emp["attendenceStatus"]."</span>";
						}
						if($emp["attendenceStatus"]=="A"){
							$total_absent_days = $total_absent_days+.5;
						}
					}
					else{
						$qry = \AttendenceLog::where("date","=",date_format($date, 'Y-m-d'));
						if($values["employeetype"] == "CLIENT BRANCH"){
							$qry->where("depotId","=",$values["depot"])->where("clientId","=",$values["clientname"]);
						}
						else{
							$qry->where("officeBranchId","=",$values["officebranch"]);
						}
						$at_log = 	$qry->get();
						if(count($at_log)>0){
							if($isHoliday){
								$session_val =  "&nbsp;&nbsp;<span style='font-weight:bold; color:red'>H</span>";
							}
							else{
								$session_val =  "&nbsp;&nbsp;<span style='font-weight:bold; color:green'>P</span>";
							}
						}
						else{
							$session_val = "&nbsp;&nbsp;";
						}
					}
					$isHoliday = false;
					$qry = \AttendenceLog::where("date","=",date_format($date, 'Y-m-d'));
					if($values["employeetype"] == "CLIENT BRANCH"){
						$qry->where("depotId","=",$values["depot"])->where("clientId","=",$values["clientname"]);
					}
					else{
						$qry->where("officeBranchId","=",$values["officebranch"]);
					}
					$at_log = 	$qry->where("day","=","HOLIDAY")->where("session","=","AFTERNOON")->get();
					if(count($at_log)>0){
						$isHoliday = true;
					}
					$emp = \Attendence::where("empId","=",$entity->id)->where("session","=","AFTERNOON")->where("date","=",date("Y-m-d", strtotime(date_format($date, 'd-m-Y'))))->get();
					if(count($emp)>0){
						$emp = $emp[0];
						if($emp->day=="HOLIDAY" || $isHoliday){
							$session_val =  $session_val." | "."<span style='font-weight:bold; color:red'>".$emp["attendenceStatus"]."</span>";
						}
						else{
							$session_val =  $session_val." | "."<span style='font-weight:bold; color:red'>".$emp["attendenceStatus"]."</span>";
						}
						if($emp["attendenceStatus"]=="A"){
							$total_absent_days = $total_absent_days+.5;
						}
					}
					else{
						$qry = \AttendenceLog::where("date","=",date_format($date, 'Y-m-d'));
						if($values["employeetype"] == "CLIENT BRANCH"){
							$qry->where("depotId","=",$values["depot"])->where("clientId","=",$values["clientname"]);
						}
						else{
							$qry->where("officeBranchId","=",$values["officebranch"]);
						}
						$at_log = 	$qry->get();
						if(count($at_log)>0){
							if($isHoliday){
								$session_val =  $session_val." | "."<span style='font-weight:bold; color:red'>H</span>";
							}
							else{
								$session_val =  $session_val." | "."<span style='font-weight:bold; color:green'>P</span>";
							}
						}
						else{
							$session_val =  $session_val."";
						}
					}
					$data_values[] = $session_val;
					$date = date_add($date, date_interval_create_from_date_string('1 days'));
				}
				$data_values[] = $total_absent_days;
				$data[] = $data_values;
			}
			
			echo json_encode($data);
			return;
		}
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$branch_arr = array();
		$branches = \OfficeBranch::where("status","=","ACTIVE")->get();
		foreach ($branches as $branch){
			$branch_arr[$branch->id] = $branch->name;
		}
	
		$clients =  \Client::where("status","=","ACTIVE")->get();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$report_type_arr = array();
		$form_field = array("name"=>"employeetype", "content"=>"employee type", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"enableClientDepot(this.value);"),  "options"=>array("OFFICE"=>"OFFICE", "CLIENT BRANCH"=>"CLIENT BRANCH"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"officebranch", "content"=>"office branch", "readonly"=>"","required"=>"", "type"=>"select", "options"=>$branch_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$add_form_fields = array();
		$form_info["form_fields"] = $form_fields;
		$form_info["add_form_fields"] = $add_form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.attendencedetailedreport', array("values"=>$values));
	}
	*/
	

	private function getAttendenceDetailedReport($values){
		if (\Request::isMethod('post'))
		{
			$fromDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
				
			//$values["DSF"];
			$data = array();
			$select_args = array("employee.id", "employee.fullName", "employee.empCode", "employee.joiningDate", "employee.terminationDate");
				
			$entities = null;
			if($values["employeetype"] == "CLIENT BRANCH"){
				if(isset($values["depot"]) &&  $values["depot"] == 0){
					\DB::statement(\DB::raw("CALL contract_driver_helper_all('".$values["clientname"]."');"));
					$entities = \DB::select( \DB::raw("select * from temp_contract_drivers_helpers where status='".$values["show_employees"]."' group by id"));
				}
				else{
					\DB::statement(\DB::raw("CALL contract_driver_helper('".$values["depot"]."', '".$values["clientname"]."');"));
					$entities = \DB::select( \DB::raw("select * from temp_contract_drivers_helpers where status='".$values["show_employees"]."' group by id"));
				}
			}
			else{
				$entities = \Employee::whereRaw(" status='ACTIVE' and (roleId!=20 and roleId!=19) and FIND_IN_SET('".$values["officebranch"]."',employee.officeBranchIds)")
				->select($select_args)->get();
			}
			$tablename1 = "temp_attendence";
			$tablename2 = "temp_attendencelog";
			\DB::statement(\DB::raw("CALL attendence_report('".date("Y-m-d",strtotime($values["fromdate"]))."','".date("Y-m-d",strtotime($values["todate"]))."','".$tablename1."','".$tablename2."');"));
			//$entities = $entities->toArray();
			foreach($entities as $entity){
				$data_values = array();
				$data_values[] = $entity->fullName."(".$entity->empCode.")";
				$date = date_create($fromDt);
				$today = date_create($toDt);
				$diff = date_diff($date,$today);
				$diff =  $diff->format("%a");
					
				$emptype ="office";
				if($values["employeetype"] == "CLIENT BRANCH"){
					$emptype = "driver";
				}
				$total_absent_days = 0;
				$isTerminated = false;
				for($i=0; $i<=$diff; $i++){
					$date1 = strtotime(date("Y-m-01",strtotime($entity->joiningDate)));
					$date2 = strtotime(date("Y-m-01",strtotime($values["fromdate"])));
					$date3 = strtotime(date('Y-m-01'));
					if($date1>$date2){
						$date = date_add($date, date_interval_create_from_date_string('1 days'));
						$data_values[] = "";
						$isTerminated = true;
						continue;
					}
					$date1 = strtotime(date("Y-m-d",strtotime($entity->terminationDate)));
					$date2 = strtotime(date("Y-m-d",strtotime(date_format($date, 'Y-m-d'))));
					
					if($entity->terminationDate!="" && $entity->terminationDate!= null && $entity->terminationDate!="0000-00-00" && $date1 != "1970-01-01" && $date1<$date2){
						$date = date_add($date, date_interval_create_from_date_string('1 days'));
						$data_values[] = "";
						$isTerminated  = true;
						continue;
					}
				//	echo "after : ".date("d-m-Y",$date1)." - ".date("d-m-Y",$date2)."\n";
					$isHoliday = false;
					$qry = \DB::table($tablename2)->where("date","=",date_format($date, 'Y-m-d'));
					if($values["employeetype"] == "CLIENT BRANCH"){
						$qry->where("depotId","=",$values["depot"])->where("clientId","=",$values["clientname"]);
					}
					else{
						$qry->where("officeBranchId","=",$values["officebranch"]);
					}
					$at_log = 	$qry->where("day","=","HOLIDAY")->where("session","=","MORNING")->get();
					if(count($at_log)>0){
						$isHoliday = true;
					}
					$session_val = "&nbsp;&nbsp;";
					$emp = \DB::table($tablename1)->where("empId","=",$entity->id)->where("session","=","MORNING")->where("date","=",date("Y-m-d", strtotime(date_format($date, 'd-m-Y'))))->get();
					if(count($emp)>0){
						$emp = $emp[0];
						if($emp->day=="HOLIDAY" || $isHoliday){
							$session_val =  "&nbsp;&nbsp;<span style='font-weight:bold; color:red'>".$emp->attendenceStatus."</span>";
						}
						else{
							$session_val =  "&nbsp;&nbsp;<span style='font-weight:bold; color:red'>".$emp->attendenceStatus."</span>";
						}
						if($emp->attendenceStatus=="A"){
							$total_absent_days = $total_absent_days+.5;
						}
					}
					else{
						$qry = \DB::table($tablename2)->where("date","=",date_format($date, 'Y-m-d'));
						if($values["employeetype"] == "CLIENT BRANCH"){
							$qry->where("depotId","=",$values["depot"])->where("clientId","=",$values["clientname"]);
						}
						else{
							$qry->where("officeBranchId","=",$values["officebranch"]);
						}
						$at_log = 	$qry->get();
						if(count($at_log)>0){
							if($isHoliday){
								$session_val =  "&nbsp;&nbsp;<span style='font-weight:bold; color:red'>H</span>";
							}
							else{
								$session_val =  "&nbsp;&nbsp;<span style='font-weight:bold; color:green'>P</span>";
							}
						}
						else{
							$session_val = "&nbsp;&nbsp;";
						}
					}
					$isHoliday = false;
					$qry = \DB::table($tablename2)->where("date","=",date_format($date, 'Y-m-d'));
					if($values["employeetype"] == "CLIENT BRANCH"){
						$qry->where("depotId","=",$values["depot"])->where("clientId","=",$values["clientname"]);
					}
					else{
						$qry->where("officeBranchId","=",$values["officebranch"]);
					}
					$at_log = 	$qry->where("day","=","HOLIDAY")->where("session","=","AFTERNOON")->get();
					if(count($at_log)>0){
						$isHoliday = true;
					}
					$emp = \DB::table($tablename1)->where("empId","=",$entity->id)->where("session","=","AFTERNOON")->where("date","=",date("Y-m-d", strtotime(date_format($date, 'd-m-Y'))))->get();
					if(count($emp)>0){
						$emp = $emp[0];
						if($emp->day=="HOLIDAY" || $isHoliday){
							$session_val =  $session_val." | "."<span style='font-weight:bold; color:red'>".$emp->attendenceStatus."</span>";
						}
						else{
							$session_val =  $session_val." | "."<span style='font-weight:bold; color:red'>".$emp->attendenceStatus."</span>";
						}
						if($emp->attendenceStatus=="A"){
							$total_absent_days = $total_absent_days+.5;
						}
					}
					else{
						$qry = \DB::table($tablename2)->where("date","=",date_format($date, 'Y-m-d'));
						if($values["employeetype"] == "CLIENT BRANCH"){
							$qry->where("depotId","=",$values["depot"])->where("clientId","=",$values["clientname"]);
						}
						else{
							$qry->where("officeBranchId","=",$values["officebranch"]);
						}
						$at_log = 	$qry->get();
						if(count($at_log)>0){
							if($isHoliday){
								$session_val =  $session_val." | "."<span style='font-weight:bold; color:red'>H</span>";
							}
							else{
								$session_val =  $session_val." | "."<span style='font-weight:bold; color:green'>P</span>";
							}
						}
						else{
							$session_val =  $session_val."";
						}
					}
					$data_values[] = $session_val;
					$date = date_add($date, date_interval_create_from_date_string('1 days'));
				}
				$data_values[] = $total_absent_days;
				if(!$isTerminated){
					$data[] = $data_values;
				}
			}
			echo json_encode($data);
			return;
		}
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$branch_arr = array();
		$branches = \OfficeBranch::where("status","=","ACTIVE")->get();
		foreach ($branches as $branch){
			$branch_arr[$branch->id] = $branch->name;
		}
	
		$clients =  \Client::where("status","=","ACTIVE")->get();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$report_type_arr = array();
		$form_field = array("name"=>"employeetype", "content"=>"employee type", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"enableClientDepot(this.value);"),  "options"=>array("OFFICE"=>"OFFICE", "CLIENT BRANCH"=>"CLIENT BRANCH"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"officebranch", "content"=>"office branch", "readonly"=>"","required"=>"", "type"=>"select", "options"=>$branch_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"show_employees","content"=>"employees", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>array("ACTIVE"=>"ACTIVE","INACTIVE"=>"INACTIVE"),  "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		
	
		$add_form_fields = array();
		$form_info["form_fields"] = $form_fields;
		$form_info["add_form_fields"] = $add_form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.attendencedetailedreport', array("values"=>$values));
	}
	private function getAttendenceNewReport($values){
		if (\Request::isMethod('post'))
		{
			$fromDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
				
			//$values["DSF"];
			$data = array();
			$select_args = array("employee.id", "employee.fullName", "employee.empCode", "employee.joiningDate", "employee.terminationDate");
				
			$entities = null;
			if($values["employeetype"] == "CLIENT BRANCH"){
// 				if(isset($values["depot"]) &&  $values["depot"] == 0){
// 					\DB::statement(\DB::raw("CALL contract_driver_helper_all('".$values["clientname"]."');"));
// 					$entities = \DB::select( \DB::raw("select * from temp_contract_drivers_helpers group by id"));
// 				}
// 				else{
// 					\DB::statement(\DB::raw("CALL contract_driver_helper('".$values["depot"]."', '".$values["clientname"]."');"));
// 					$entities = \DB::select( \DB::raw("select * from temp_contract_drivers_helpers group by id"));
// 				}
			}
			else{
				$entities = \Employee::whereRaw(" status='ACTIVE' and (roleId!=20 and roleId!=19) and FIND_IN_SET('".$values["officebranch"]."',employee.officeBranchIds)")
				->select($select_args)->get();
			}
			foreach($entities as $entity){
				$data_values = array();
				$data_values[] = $entity->fullName."(".$entity->empCode.")";
				$date = date_create($fromDt);
				$today = date_create($toDt);
				$diff = date_diff($date,$today);
				$diff =  $diff->format("%a");
					
				$emptype ="office";
				$total_absent_days = 0;
					
				for($i=0; $i<=$diff; $i++){
					$date1 = strtotime(date("Y-m-d",strtotime($entity->joiningDate)));
					$date2 = strtotime(date("Y-m-d",strtotime(date_format($date, 'Y-m-d'))));
					$date3 = strtotime(date('Y-m-01'));
						
					if($date1>$date2){
						$date = date_add($date, date_interval_create_from_date_string('1 days'));
						$data_values[] = "";
						continue;
					}
					$date1 = strtotime(date("Y-m-d",strtotime($entity->terminationDate)));
					$date2 = strtotime(date("Y-m-d",strtotime(date_format($date, 'Y-m-d'))));
						
					if($entity->terminationDate!="" && $entity->terminationDate!="0000-00-00" && $date1 != "1970-01-01" && $date1<$date2){
						$date = date_add($date, date_interval_create_from_date_string('1 days'));
						$data_values[] = "";
						continue;
					}
					$session_val = "&nbsp;&nbsp;";
					$leaves = \Leaves::where("empId","=",$entity->id)
										->where("fromDate","<=",$date)
										->where("toDate",">=",$date)->get();
					if(count($leaves)>0){
						$leaves = $leaves[0];
							if($leaves->toMrngEve == "Morning"  && ((strtotime(date("Y-m-d",strtotime($leaves->toDate))) == strtotime(date_format($date, 'Y-m-d'))))){
								$session_val =  "&nbsp;&nbsp;<span style='font-weight:bold; color:green'>P</span>";
							}
							else if($leaves->fromMrngEve == "Afternoon"  && ((strtotime(date("Y-m-d",strtotime($leaves->fromDate))) == strtotime(date_format($date, 'Y-m-d'))))){
								$session_val = "&nbsp;&nbsp;<span style='font-weight:bold; color:green'>P</span>";
							}
							else{
								$session_val =  "&nbsp;&nbsp;<span style='font-weight:bold; color:red'>A</span>";
								$total_absent_days=$total_absent_days+1;
							}
							
					}
					else{
						$session_val =  "&nbsp;&nbsp;<span style='font-weight:bold; color:green'>P</span>";
					}
					
					$leaves = \Leaves::where("empId","=",$entity->id)
										->where("fromDate","<=",$date)
										->where("toDate",">=",$date)->get();
					if(count($leaves)>0){
						$leaves = $leaves[0];
								if($leaves->toMrngEve == "Morning"  && ((strtotime(date("Y-m-d",strtotime($leaves->toDate))) == strtotime(date_format($date, 'Y-m-d'))))){
									$session_val =  $session_val." | "."<span style='font-weight:bold; color:green'>P</span>";
								}
								else if($leaves->toMrngEve == "Afternoon"  && ((strtotime(date("Y-m-d",strtotime($leaves->toDate))) == strtotime(date_format($date, 'Y-m-d'))))){
									$session_val =  $session_val." | "."<span style='font-weight:bold; color:green'>P</span>";
								}
								else{
									$session_val =  $session_val." | "."<span style='font-weight:bold; color:red'>A</span>";
									$total_absent_days=$total_absent_days+1;
								}
													
					}
					else{
						$session_val =   $session_val." | "."<span style='font-weight:bold; color:green'>P</span>";
					}
					$data_values[] = $session_val;
					$date = date_add($date, date_interval_create_from_date_string('1 days'));
				}
				$total_absent_days= $total_absent_days/2;
				$data_values[] = $total_absent_days;
				$data[] = $data_values;
			}
			//die();
			echo json_encode($data);
			return;
		}
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$branch_arr = array();
		$branches = \OfficeBranch::where("status","=","ACTIVE")->get();
		foreach ($branches as $branch){
			$branch_arr[$branch->id] = $branch->name;
		}
	
		$clients =  \Client::where("status","=","ACTIVE")->get();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$report_type_arr = array();
		$form_field = array("name"=>"employeetype", "content"=>"employee type", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"enableClientDepot(this.value);"),  "options"=>array("OFFICE"=>"OFFICE", "CLIENT BRANCH"=>"CLIENT BRANCH"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"officebranch", "content"=>"office branch", "readonly"=>"","required"=>"", "type"=>"select", "options"=>$branch_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$add_form_fields = array();
		$form_info["form_fields"] = $form_fields;
		$form_info["add_form_fields"] = $add_form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.attendencenew', array("values"=>$values));
	}
	
	
	private function getSalaryReport($values){
		if (\Request::isMethod('post'))
		{
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp1 = array("data"=>array());
			$resp = array();
			$select_args = array();
			$select_args[] = "officebranch.name as branch";
			$select_args[] = "sum(actualSalary) as actualSalary";
			$select_args[] = "sum(dueDeductions) as dueDeductions";
			$select_args[] = "sum(leaveDeductions) as leaveDeductions";
			$select_args[] = "sum(pf) as pf";
			$select_args[] = "sum(esi) as esi";
			$select_args[] = "sum(salaryPaid) as salaryPaid";
			
			if(isset($values["typeofreport"]) && $values["typeofreport"]=="branch_wise_salary_report"){
				$totalactsalary = 0;
				$totalduedeductions  = 0;
				$totalleavedeductions  = 0;
				$totalother = 0;
				$totalpf  = 0;
				$totalesi = 0;
				$totalsalarypaid = 0;
				if($values["paidfrombranch"] == "0"){
					$sql = "SELECT officebranch.name as branch, sum(actualSalary) as actualSalary, sum(dueDeductions) as dueDeductions, sum(leaveDeductions) as leaveDeductions, sum(pf) as pf, sum(esi) as esi, sum(otherAmount) as other_amount, sum(salaryPaid) as salaryPaid from empsalarytransactions left join officebranch on officebranch.id=empsalarytransactions.branchId where paymentDate BETWEEN '".$frmDt."' and '".$toDt."' and empsalarytransactions.source='SALARY TRANSACTION' group by branchId";
					$salarytransactions =  \DB::select(DB::raw($sql));
					foreach ($salarytransactions as $salarytransaction){
						$row = array();
						$row["branch"] = $salarytransaction->branch;
						$row["actualSalary"] = $salarytransaction->actualSalary;
						$totalactsalary = $totalactsalary+$salarytransaction->actualSalary;
						$row["dueDeductions"] = $salarytransaction->dueDeductions;
						$totalduedeductions = $totalduedeductions+$salarytransaction->dueDeductions;
						$row["leaveDeductions"] = $salarytransaction->leaveDeductions;
						$totalleavedeductions = $totalleavedeductions+$salarytransaction->leaveDeductions;
						$row["other_amount"] = $salarytransaction->other_amount;
						$totalother = $totalother+$salarytransaction->other_amount;
						$row["pf"] = $salarytransaction->pf;
						$totalpf = $totalpf+$row["pf"] ;
						$row["esi"] = $salarytransaction->esi;
						$totalesi = $totalesi+$row["esi"];
						$row["salaryPaid"] = $salarytransaction->salaryPaid;
						$totalsalarypaid = $totalsalarypaid+$row["salaryPaid"];
						$resp[] = $row;
					}
					$resp1 = array("data"=>$resp,"tot_actsal"=>$totalactsalary,"tot_due"=>$totalduedeductions,"tot_leave"=>$totalleavedeductions,"tot_other"=>$totalother,"tot_pf"=>$totalpf,"tot_esi"=>$totalesi,"tot_psal"=>$totalsalarypaid);
				}
				else if($values["paidfrombranch"]>0){
					$sql = "SELECT officebranch.name as branch, sum(actualSalary) as actualSalary, sum(dueDeductions) as dueDeductions, sum(leaveDeductions) as leaveDeductions, sum(pf) as pf, sum(esi) as esi, sum(otherAmount) as other_amount, sum(salaryPaid) as salaryPaid from empsalarytransactions left join officebranch on officebranch.id=empsalarytransactions.branchId where branchId= ".$values["paidfrombranch"]." and paymentDate BETWEEN '".$frmDt."' and '".$toDt."'  and empsalarytransactions.source='SALARY TRANSACTION' group by branchId";
					$salarytransactions =  \DB::select(DB::raw($sql));
					foreach ($salarytransactions as $salarytransaction){
						$row = array();
						$row["branch"] = $salarytransaction->branch;
						$row["actualSalary"] = $salarytransaction->actualSalary;
						$row["dueDeductions"] = $salarytransaction->dueDeductions;
						$row["leaveDeductions"] = $salarytransaction->leaveDeductions;
						$row["other_amount"] = $salarytransaction->other_amount;
						$row["pf"] = $salarytransaction->pf;
						$row["esi"] = $salarytransaction->esi;
						$row["salaryPaid"] = $salarytransaction->salaryPaid;
						$resp[] = $row;
						$totalactsalary = $totalactsalary+$salarytransaction->actualSalary;
						$totalduedeductions = $totalduedeductions+$salarytransaction->dueDeductions;
						$totalleavedeductions = $totalleavedeductions+$salarytransaction->leaveDeductions;
						$totalother = $totalother+$salarytransaction->other_amount;
						$totalpf = $totalpf+$salarytransaction->pf;
						$totalesi = $totalesi+$salarytransaction->esi;
						$totalsalarypaid = $totalsalarypaid+$salarytransaction->salaryPaid;
					}
					$resp1 = array("data"=>$resp,"tot_actsal"=>$totalactsalary,"tot_due"=>$totalduedeductions,"tot_leave"=>$totalleavedeductions,"tot_other"=>$totalother,"tot_pf"=>$totalpf,"tot_esi"=>$totalesi,"tot_psal"=>$totalsalarypaid);
				}
			}
			if(isset($values["typeofreport"]) && $values["typeofreport"]=="bank_payment_report"){
				$totalactsalary = 0;
				$totalduedeductions  = 0;
				$totalleavedeductions  = 0;
				$totalother = 0;
				$totalpf  = 0;
				$totalesi = 0;
				$totalsalarypaid = 0;
				if($values["paidfrombranch"] == "0"){
					$sql = "SELECT officebranch.name as branch, bankdetails.bankName as bankName1, bankdetails.accountNo as accountNumber1,  employee.empCode as empId, employee.fullName as name, salaryMonth, paymentDate, (actualSalary) as actualSalary, (dueDeductions) as dueDeductions, (leaveDeductions) as leaveDeductions, (pf) as pf, (esi) as esi, (otherAmount) as other_amount, (salaryPaid) as salaryPaid, empsalarytransactions.paymentType, empsalarytransactions.bankAccount, empsalarytransactions.chequeNumber, empsalarytransactions.chequeNumber, empsalarytransactions.bankName, empsalarytransactions.accountNumber, empsalarytransactions.issueDate, empsalarytransactions.transactionDate from empsalarytransactions left join employee on employee.id=empsalarytransactions.empId left join bankdetails on bankdetails.id=empsalarytransactions.bankAccount left join officebranch on officebranch.id=empsalarytransactions.branchId where paymentDate BETWEEN '".$frmDt."' and '".$toDt."' and empsalarytransactions.source='SALARY TRANSACTION' order by branchId";
				}
				else if($values["paidfrombranch"]>0){
					$sql = "SELECT officebranch.name as branch, bankdetails.bankName as bankName1, bankdetails.accountNo as accountNumber1,  employee.empCode as empId, employee.fullName as name, salaryMonth, paymentDate, (actualSalary) as actualSalary, (dueDeductions) as dueDeductions, (leaveDeductions) as leaveDeductions, (pf) as pf, (esi) as esi, (otherAmount) as other_amount, (salaryPaid) as salaryPaid, empsalarytransactions.paymentType, empsalarytransactions.bankAccount, empsalarytransactions.chequeNumber, empsalarytransactions.chequeNumber, empsalarytransactions.bankName, empsalarytransactions.accountNumber, empsalarytransactions.issueDate, empsalarytransactions.transactionDate from empsalarytransactions left join employee on employee.id=empsalarytransactions.empId left join bankdetails on bankdetails.id=empsalarytransactions.bankAccount left join officebranch on officebranch.id=empsalarytransactions.branchId where branchId=".$values["paidfrombranch"]." and empsalarytransactions.source='SALARY TRANSACTION' and paymentDate BETWEEN '".$frmDt."' and '".$toDt."' order by branchId";
				}
				$salarytransactions =  \DB::select(DB::raw($sql));
				foreach ($salarytransactions as $salarytransaction){
					$row = array();
					$row["branch"] = $salarytransaction->branch;
					$row["name"] = $salarytransaction->name."-".$salarytransaction->empId;
					$row["salaryMonth"] = date("M",strtotime($salarytransaction->salaryMonth)).", ".date("Y",strtotime($salarytransaction->salaryMonth));
					$row["paymentDate"] = date("d-m-Y",strtotime($salarytransaction->paymentDate));
					
					$row["paymentInfo"] = "";
					if($salarytransaction->paymentType == "cash"){
						$row["paymentInfo"] = "CASH";
					}
					if($salarytransaction->paymentType == "neft"){
						$row["paymentInfo"] = "Payment type : NEFT<br/>";
						$row["paymentInfo"] = $row["paymentInfo"]."Bank Name : ".$salarytransaction->bankName1."<br/>";
						$row["paymentInfo"] = $row["paymentInfo"]."Acct No. : ".$salarytransaction->accountNumber1."<br/>";
						$row["paymentInfo"] = $row["paymentInfo"]."Trans Ref No. : ".$salarytransaction->chequeNumber."<br/>";
					}
					else if($salarytransaction->paymentType == "ecs"){
						$row["paymentInfo"] = "Payment type : ESC<br/>";
						$row["paymentInfo"] = $row["paymentInfo"]."Bank Name : ".$salarytransaction->bankName1."<br/>";
						$row["paymentInfo"] = $row["paymentInfo"]."Acct No. : ".$salarytransaction->accountNumber1."<br/>";
						$row["paymentInfo"] = $row["paymentInfo"]."Trans Ref No. : ".$salarytransaction->chequeNumber."<br/>";
					}
					else if($salarytransaction->paymentType == "rtgs"){
						$row["paymentInfo"] = "Payment type : RTGS<br/>";
						$row["paymentInfo"] = $row["paymentInfo"]."Bank Name : ".$salarytransaction->bankName1."<br/>";
						$row["paymentInfo"] = $row["paymentInfo"]."Acct No. : ".$salarytransaction->accountNumber1."<br/>";
						$row["paymentInfo"] = $row["paymentInfo"]."Trans Ref No. : ".$salarytransaction->chequeNumber."<br/>";
					}
					else if($salarytransaction->paymentType == "cheque_debit"){
						$row["paymentInfo"] = "Payment type : CHEQUE DEBIT<br/>";
						$bankinfo = "";
						$bank = \BankDetails::where("id","=",$salarytransaction->bankAccount)->get();
						if(count($bank)>0){
							$bank = $bank[0];
							$bankinfo = $bankinfo."Bank Name : ".$bank->bankName." - ".$bank->accountNo."(".$bank->branchName.")<br/>";
						}
						$row["paymentInfo"] = $row["paymentInfo"].$bankinfo;
						$row["paymentInfo"] = $row["paymentInfo"]."Cheque No. : ".$salarytransaction->chequeNumber."<br/>";
					}
					$row["actualSalary"] = $salarytransaction->actualSalary;
					$row["dueDeductions"] = $salarytransaction->dueDeductions;
					$row["leaveDeductions"] = $salarytransaction->leaveDeductions;
					$row["other_amount"] = $salarytransaction->other_amount;
					$row["pf"] = $salarytransaction->pf;
					$row["esi"] = $salarytransaction->esi;
					$row["salaryPaid"] = $salarytransaction->salaryPaid;
					$resp[] = $row;
					$totalactsalary = $totalactsalary+$salarytransaction->actualSalary;
					$totalduedeductions = $totalduedeductions+$salarytransaction->dueDeductions;
					$totalleavedeductions = $totalleavedeductions+$salarytransaction->leaveDeductions;
					$totalother = $totalother+$salarytransaction->other_amount;
					$totalpf = $totalpf+$salarytransaction->pf;
					$totalesi = $totalesi+$salarytransaction->esi;
					$totalsalarypaid = $totalsalarypaid+$salarytransaction->salaryPaid;
				}
				$resp1 = array("data"=>$resp,"tot_actsal"=>$totalactsalary,"tot_due"=>$totalduedeductions,"tot_leave"=>$totalleavedeductions,"tot_other"=>$totalother,"tot_pf"=>$totalpf,"tot_esi"=>$totalesi,"tot_psal"=>$totalsalarypaid);
			}
			if(isset($values["typeofreport"]) && $values["typeofreport"]=="employee_wise_salary_report"){
				$totalactsalary = 0;
				$totalduedeductions  = 0;
				$totalleavedeductions  = 0;
				$totalother = 0;
				$totalpf  = 0;
				$totalesi = 0;
				$totalsalarypaid = 0;
				if($values["employee"] == "0"){
					$sql = "SELECT officebranch.name as branch, employee.empCode as empId, employee.fullName as name, salaryMonth, paymentDate, (actualSalary) as actualSalary, (dueDeductions) as dueDeductions, (leaveDeductions) as leaveDeductions, (pf) as pf, (esi) as esi, (otherAmount) as other_amount, (salaryPaid) as salaryPaid from empsalarytransactions join employee on employee.id=empsalarytransactions.empId left join officebranch on officebranch.id=empsalarytransactions.branchId where paymentDate BETWEEN '".$frmDt."' and '".$toDt."' and empsalarytransactions.source='SALARY TRANSACTION' order by branchId";
					$salarytransactions =  \DB::select(DB::raw($sql));
					foreach ($salarytransactions as $salarytransaction){
						$row = array();
						$row["branch"] = $salarytransaction->branch;
						$row["empId"] = $salarytransaction->empId;
						$row["name"] = $salarytransaction->name;
						$row["salaryMonth"] = date("M",strtotime($salarytransaction->salaryMonth)).", ".date("Y",strtotime($salarytransaction->salaryMonth));
						$row["paymentDate"] = date("d-m-Y",strtotime($salarytransaction->paymentDate));
						$row["actualSalary"] = $salarytransaction->actualSalary;
						$row["dueDeductions"] = $salarytransaction->dueDeductions;
						$row["leaveDeductions"] = $salarytransaction->leaveDeductions;
						$row["other_amount"] = $salarytransaction->other_amount;
						$row["pf"] = $salarytransaction->pf;
						$row["esi"] = $salarytransaction->esi;
						$row["salaryPaid"] = $salarytransaction->salaryPaid;
						$resp[] = $row;
						$totalactsalary = $totalactsalary+$salarytransaction->actualSalary;
						$totalduedeductions = $totalduedeductions+$salarytransaction->dueDeductions;
						$totalleavedeductions = $totalleavedeductions+$salarytransaction->leaveDeductions;
						$totalother = $totalother+$salarytransaction->other_amount;
						$totalpf = $totalpf+$salarytransaction->pf;
						$totalesi = $totalesi+$salarytransaction->esi;
						$totalsalarypaid = $totalsalarypaid+$salarytransaction->salaryPaid;
					}
					$resp1 = array("data"=>$resp,"tot_actsal"=>$totalactsalary,"tot_due"=>$totalduedeductions,"tot_leave"=>$totalleavedeductions,"tot_other"=>$totalother,"tot_pf"=>$totalpf,"tot_esi"=>$totalesi,"tot_psal"=>$totalsalarypaid);
				}
				else if($values["employee"]>0){
					$sql = "SELECT officebranch.name as branch, employee.empCode as empId, employee.fullName as name, salaryMonth, paymentDate, (actualSalary) as actualSalary, (dueDeductions) as dueDeductions, (leaveDeductions) as leaveDeductions, (pf) as pf, (esi) as esi, (otherAmount) as other_amount, (salaryPaid) as salaryPaid from empsalarytransactions join employee on employee.id=empsalarytransactions.empId left join officebranch on officebranch.id=empsalarytransactions.branchId where employee.id=".$values["employee"]." and empsalarytransactions.source='SALARY TRANSACTION' and paymentDate BETWEEN '".$frmDt."' and '".$toDt."' order by branchId";
					$salarytransactions =  \DB::select(DB::raw($sql));
					foreach ($salarytransactions as $salarytransaction){
						$row = array();
						$row["branch"] = $salarytransaction->branch;
						$row["empId"] = $salarytransaction->empId;
						$row["name"] = $salarytransaction->name;
						$row["salaryMonth"] = date("M",strtotime($salarytransaction->salaryMonth)).", ".date("Y",strtotime($salarytransaction->salaryMonth));
						$row["paymentDate"] = date("d-m-Y",strtotime($salarytransaction->paymentDate));
						$row["actualSalary"] = $salarytransaction->actualSalary;
						$row["dueDeductions"] = $salarytransaction->dueDeductions;
						$row["leaveDeductions"] = $salarytransaction->leaveDeductions;
						$row["other_amount"] = $salarytransaction->other_amount;
						$row["pf"] = $salarytransaction->pf;
						$row["esi"] = $salarytransaction->esi;
						$row["salaryPaid"] = $salarytransaction->salaryPaid;
						$resp[] = $row;
						$totalactsalary = $totalactsalary+$salarytransaction->actualSalary;
						$totalduedeductions = $totalduedeductions+$salarytransaction->dueDeductions;
						$totalleavedeductions = $totalleavedeductions+$salarytransaction->leaveDeductions;
						$totalother = $totalother+$salarytransaction->other_amount;
						$totalpf = $totalpf+$salarytransaction->pf;
						$totalesi = $totalesi+$salarytransaction->esi;
						$totalsalarypaid = $totalsalarypaid+$salarytransaction->salaryPaid;
					}
					$resp1 = array("data"=>$resp,"tot_actsal"=>$totalactsalary,"tot_due"=>$totalduedeductions,"tot_leave"=>$totalleavedeductions,"tot_other"=>$totalother,"tot_pf"=>$totalpf,"tot_esi"=>$totalesi,"tot_psal"=>$totalsalarypaid);
				}
			}
			if(isset($values["typeofreport"]) && $values["typeofreport"]=="detailed_salary_report"){
				if(true){
					  if(isset($values["employeetype"]) && $values["employeetype"]=="OFFICE"){
						$entities = \Employee::whereRaw("(roleId!=20 and roleId!=19) and FIND_IN_SET('".$values["officebranch"]."',employee.officeBranchIds)")->get();
					  }
					  else if(isset($values["employeetype"]) && $values["employeetype"]=="CLIENT BRANCH"){
					  	if($values["depot"]==0){
					  		DB::statement(DB::raw("CALL contract_driver_helper_all('".$values["clientname"]."');"));
					  	}
					  	else{
					  		DB::statement(DB::raw("CALL contract_driver_helper('".$values["depot"]."', '".$values["clientname"]."');"));
					  	}
						$entities = DB::select( DB::raw("select * from temp_contract_drivers_helpers where status='".$values["show_employees"]."' group by id"));
					  }
					 // print_r($entities);die();
					  $i = 0;
					  $tot_dueamt = 0;
					  $tot_otheramt = 0;
					  $tot_leaveamt = 0;
					  $tot_incramt = 0;
					  $tot_prevsal=0;
					  $tot_gross =0;
					  $tot_net = 0;
					  $roles_arr = array();
					  $roles = \Role::All();
					  foreach ($roles as $role){
					  	$roles_arr[$role->id] = $role->roleName;
					  }
					  $fromdt = date("Y-m-d",strtotime($values["fromdate"]));
					  $todt = date("Y-m-d",strtotime($values["todate"]));
					  foreach($entities as $entity){
					  	$row = array();
					  	$br_name = "";
					  	if(isset($values["employeetype"]) && $values["employeetype"]=="OFFICE"){
						  	$off_br = \OfficeBranch::where("id","=",$values["officebranch"])->get();
						  	if(count($off_br)>0){
						  		$off_br = $off_br[0];
						  		$br_name = $off_br->name;
						  	}
					  	}
					  	else if(isset($values["employeetype"]) && $values["employeetype"]=="CLIENT BRANCH"){
					  		$clientname="";
					  		$client = \Client::where("id","=",$values["clientname"])->get();
					  		if(count($client)>0){
					  			$client = $client[0];
					  			$clientname = $client->name;
					  		}
					  		$depotname="";
					  		$depot = \Depot::where("id","=",$values["depot"])->get();
					  		if(count($depot)>0){
					  			$depot = $depot[0];
					  			$depotname = $depot->name;
					  		}
					  		$br_name = $depotname." (".$clientname.")";
					  	}
					  	$row["branch"] = $br_name;
					  	$row["employee"] = $entity->fullName." - ".$entity->empCode;
					  	if($entity->roleId == 19){
					  		$entity->roleId = "DRIVER";
					  	}
					  	else if($entity->roleId == 20){
					  		$entity->roleId = "HELPER";
					  	}
					  	$dt_salary = 0;
					  	$dt_allowance = 0;
					  	$lt_salary = 0;
					  	$deductions = 0;
					  	$salaryMonth = $values["month"];
					  	$noOfDays = date("t", strtotime($salaryMonth)) -1;
					  	$startDate = $salaryMonth;
					  	$endDate =  date('Y-m-d', strtotime($salaryMonth.'+ '.$noOfDays.' days'));
					  	$recs = \SalaryTransactions::where("salaryMonth","=",$values["month"])->where("empId","=",$entity->id)->where("deleted","=","No")->where("source","=","SALARY TRANSACTION")->get();
					  	if(count($recs)>0){
					  		$rec = $recs[0];
							$salary_amt = 0;
					  		$salary_amt = 0;
							$salary = \SalaryDetails::where("empId","=",$entity->id)->get();
							if(count($salary)>0){
								$salary = $salary[0];
								$salary_amt = $salary->salary;
								$increaments = \SalaryDetails::where('empId','=',$entity->id)
															->whereMonth('increamentDate', '=', date('m',strtotime($fromdt)))
															->whereYear('increamentDate', '=', date('Y',strtotime($fromdt)))
															->orderBy("increamentDate","desc")->first();
								if(count($increaments)>0){
									$date7 = date("m-y",strtotime($increaments->increamentDate));
									$date8 = date("m-y",strtotime($values["fromdate"]));
									if($date7==$date8){
										$salary_amt =$salary_amt+$increaments->arrearamount;
									}
										
								}
							}
  							$due_amt = "0.00";
  							$recs1 = \DB::select( DB::raw("SELECT SUM(`amount`) amt FROM `empdueamount` WHERE empId = ".$entity->id." and deleted='No'") );
  							foreach ($recs1 as $rec1){
  								$due_amt = $rec1->amt;
  								if($due_amt == ""){
  									$due_amt = "0.00";
  								}
							}
					  		$leaves = 0;
							$leaves_amt = 0;
							$recs1 = \DB::select( DB::raw($sql = "select count(*) as cnt from attendence where attendence.empId='".$entity->id."' and (attendenceStatus = 'A') and date between '$fromdt' and '$todt'") );
							foreach ($recs1 as $rec1){
								$leaves = $rec1->cnt;
								$leaves = $leaves/2;
								$after_leaves = $leaves;
								$date1 = strtotime(date("Y-m-d",strtotime($entity->joiningDate)));
								$date2 = strtotime(date("Y-m-d",strtotime($entity->terminationDate)));
								$date3 = strtotime($fromdt);
								$date4 = strtotime($todt);
								if($date1>$date3 || ($entity->terminationDate!="" && $entity->terminationDate!="0000-00-00" && $date1 != "1970-01-01" &&  $date2<$date4)){
									$after_leaves = $leaves;
								}
// 								else{
// 									$after_leaves = $leaves-$values["casualleaves"];
// 								}
								if($after_leaves<0){
									$after_leaves = 0;
								}
								
								$date1=date_create($fromdt);
								$date2=date_create($todt);
								$diff=date_diff($date1,$date2);
								$working_days =  $diff->format("%a");
								$working_days = $working_days+1;
								$leaves_amt = ($salary_amt/$working_days)*$after_leaves;
								$leaves_amt = intval($leaves_amt);
							}
							
							$net_salary = $salary_amt-($leaves_amt+$due_amt);
							
							$total_days = 0;
							$date1=date_create($fromdt);
							$date2=date_create($todt);
							$diff=date_diff($date1,$date2);
							$total_days =  $diff->format("%a");
							$total_days = $total_days+1;
							
							$casual_leaves = $rec->casualLeaves;
							$late_joing_days = 0;
							$early_erminated_days = 0;
							$actual_working_days = $total_days;
							$employee_working_days = $total_days-$leaves;
							
							$previous_salary = 0;
							$increment = 0;
							$increment_dt = "";
							$salary_details = \SalaryDetails::where("empId","=",$entity->id)->Get();
							if(count($salary_details)>0){
								$salary_details = $salary_details[0];
								$previous_salary = $salary_details->previousSalary;
								$increment = $salary_details->increament;
								$increment_dt = date("d-m-Y",strtotime($salary_details->increamentDate));
								if($increment_dt=="30-11--0001" || $increment_dt=="01-01-1970"){
									$increment_dt = "";
								}
								//echo $entity->fullName." - ".$increment." - ".$previous_salary." - ".$increment_dt; die();
							}
							if(isset($roles_arr[$entity->roleId])){
								$entity->roleId = $roles_arr[$entity->roleId];
							}
							
							$date1 = strtotime(date("Y-m-d",strtotime($entity->joiningDate)));
							$date2 = strtotime(date("Y-m-d",strtotime($entity->terminationDate)));
							$date3 = strtotime($fromdt);
							$date4 = strtotime($todt);
							if(($entity->terminationDate!="" && $entity->terminationDate!="0000-00-00" && $date2!="01-01-1970"  && $date2>=$date3 &&  $date2<$date4)){
								$date3_=date_create($fromdt);
								$date2_=date_create(date("Y-m-d",strtotime($entity->terminationDate)));
								$diff=date_diff($date3_,$date2_);
								$early_erminated_days =  $diff->format("%a");
								$early_erminated_days = $early_erminated_days+1;
								$actual_working_days = $early_erminated_days;
								$employee_working_days = $actual_working_days-$leaves;
								//$net_salary = ($salary_amt/$total_days)*$actual_working_days;
								//$salary_amt = $net_salary;
							}
							if(($entity->joiningDate!="" && $entity->joiningDate!="0000-00-00" && $date1!="01-01-1970"  && $date1>$date3 &&  $date1<$date4)){
								$date3_=date_create($fromdt);
								$date1_=date_create(date("Y-m-d",strtotime($entity->joiningDate)));
								$diff=date_diff($date3_,$date1_);
								$late_joing_days =  $diff->format("%a");
								$late_joing_days = $late_joing_days+1;
							}
							
							$row["designation"] = $entity->roleId;
							$row["salary_month"] = strtoupper(date("M",strtotime($rec->salaryMonth)));
							$row["paidDate"] = date("d-m-Y",strtotime($rec->paymentDate));
							$row["joining_date"] = date("d-m-Y",strtotime($entity->joiningDate));
							$row["actualSalary"] = $rec->actualSalary;
							$row["total_days"] = $total_days;
							$row["cas_leaves"] = $rec->casualLeaves;
							$row["leaves"] = $leaves;
							$row["early_erminated_days"] = $early_erminated_days;
							$row["employee_working_days"] = $employee_working_days;
							$row["dueDeductions"] = $rec->dueDeductions;
							$tot_dueamt = $tot_dueamt+$rec->dueDeductions;
							$row["leaveDeductions"] = $rec->leaveDeductions;
							$tot_leaveamt = $tot_leaveamt+$rec->leaveDeductions;
							$row["otherAmount"] = $rec->otherAmount;
							$tot_otheramt = $tot_otheramt+$rec->otherAmount;
							$row["previous_salary"] = $previous_salary;
							$tot_prevsal = $tot_prevsal+$previous_salary;
							$row["increment"] = $increment;
							$tot_incramt = $tot_incramt+$increment;
							if($increment_dt == "01-01-1970"){
								$increment_dt = "";
							}
							$row["increment_date"] = $increment_dt;
							$row["grosssalary"] = $rec->actualSalary;
							$tot_gross =$tot_gross+$rec->actualSalary;
							$row["netsalary"] = $rec->salaryPaid;
							$tot_net = $tot_net+$rec->salaryPaid;
							$row["cardNumber"] = $rec->cardNumber;
							$row["comments"] = $rec->comments;
							$resp[] = $row;
					  	}
					  	$resp1 = array("data"=>$resp, "total_due"=>$tot_dueamt,"total_other"=>$tot_otheramt,"total_leave"=>$tot_leaveamt,"total_prev"=>$tot_prevsal,"total_incre"=>$tot_incramt,"total_gross"=>$tot_gross,"total_net"=>$tot_net);
					  }
					
				}
			}
			if(isset($values["typeofreport"]) && $values["typeofreport"]=="pf_report"){
				$tot_pf = 0;
				$sql = "SELECT officebranch.name as branch, employee.empCode as empId, employee.fullName as name, salaryMonth, paymentDate, (pf) as pf from empsalarytransactions join employee on employee.id=empsalarytransactions.empId left join officebranch on officebranch.id=empsalarytransactions.branchId where pfOpted='Yes' and empsalarytransactions.source='SALARY TRANSACTION' and  paymentDate BETWEEN '".$frmDt."' and '".$toDt."' order by branchId";
				$salarytransactions =  \DB::select(DB::raw($sql));
				foreach ($salarytransactions as $salarytransaction){
					$row = array();
					$row["branch"] = $salarytransaction->branch;
					$row["empId"] = $salarytransaction->empId;
					$row["name"] = $salarytransaction->name;
					$row["salaryMonth"] = date("M",strtotime($salarytransaction->salaryMonth)).", ".date("Y",strtotime($salarytransaction->salaryMonth));
					$row["paymentDate"] = date("d-m-Y",strtotime($salarytransaction->paymentDate));
					$row["pf"] = $salarytransaction->pf;
					$tot_pf = $tot_pf+$salarytransaction->pf;
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp,"tot_pf"=>$tot_pf);
			}
			if(isset($values["typeofreport"]) && $values["typeofreport"]=="esi_report"){
				$tot_esi = 0;
				$sql = "SELECT officebranch.name as branch, employee.empCode as empId, employee.fullName as name, salaryMonth, paymentDate, (esi) as esi from empsalarytransactions join employee on employee.id=empsalarytransactions.empId left join officebranch on officebranch.id=empsalarytransactions.branchId where pfOpted='Yes' and empsalarytransactions.source='SALARY TRANSACTION' and  paymentDate BETWEEN '".$frmDt."' and '".$toDt."' order by branchId";
				$salarytransactions =  \DB::select(DB::raw($sql));
				foreach ($salarytransactions as $salarytransaction){
					$row = array();
					$row["branch"] = $salarytransaction->branch;
					$row["empId"] = $salarytransaction->empId;
					$row["name"] = $salarytransaction->name;
					$row["salaryMonth"] = date("M",strtotime($salarytransaction->salaryMonth)).", ".date("Y",strtotime($salarytransaction->salaryMonth));
					$row["paymentDate"] = date("d-m-Y",strtotime($salarytransaction->paymentDate));
					$row["pf"] = $salarytransaction->esi;
					$tot_esi = $tot_esi+$salarytransaction->esi;
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp,"tot_esi"=>$tot_esi);
			}
			echo json_encode($resp1);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$branches =  \OfficeBranch::ALL();
		$branches_arr = array();
		$branches_arr["0"] = "ALL BRANCHES";
		foreach ($branches as $branch){
			$branches_arr[$branch->id] = $branch->name;
		}
	
		$employees =  \Employee::ALL();
		$employees_arr = array();
		$employees_arr["0"] = "ALL EMPLOYEES";
		foreach ($employees as $employee){
			$employees_arr[$employee->id] = $employee->fullName." (".$employee->empCode.")";
		}
		
		$branch_arr = array();
		$branches = \OfficeBranch::where("status","=","ACTIVE")->get();
		foreach ($branches as $branch){
			$branch_arr[$branch->id] = $branch->name;
		}
		
		$clients =  \Client::where("status","=","ACTIVE")->get();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
		
		$month_arr = array();
		$month_arr[date('Y', strtotime('-1 year'))."-04-01"] = 'April '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-05-01"] = 'may '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-06-01"] = 'June '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-07-01"] = 'July '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-08-01"] = 'Aug '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-09-01"] = 'Sep '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-10-01"] = 'Oct '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-11-01"] = 'Nov '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-12-01"] = 'Dec '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y')."-01-01"] = 'Jan '.date('Y');
		$month_arr[date('Y')."-02-01"] = 'Feb '.date('Y');
		$month_arr[date('Y')."-03-01"] = 'March '.date('Y');
		$month_arr[date('Y')."-04-01"] = 'April '.date('Y');
		$month_arr[date('Y')."-05-01"] = 'May '.date('Y');
		$month_arr[date('Y')."-06-01"] = 'June '.date('Y');
		$month_arr[date('Y')."-07-01"] = 'July'.date('Y');
		$month_arr[date('Y')."-08-01"] = 'Aug '.date('Y');
		$month_arr[date('Y')."-09-01"] = 'Sep '.date('Y');
		$month_arr[date('Y')."-10-01"] = 'Oct '.date('Y');
		$month_arr[date('Y')."-11-01"] = 'Nov '.date('Y');
		$month_arr[date('Y')."-12-01"] = 'Dec '.date('Y');
	
		$report_type_arr = array();
		$report_type_arr["detailed_salary_report"] = "DETAILED SALARY REPORT";
		$report_type_arr["branch_wise_salary_report"] = "BRANCH WISE SALARY REPORT";
		$report_type_arr["employee_wise_salary_report"] = "EMPLOYEE WISE SALARY REPORT";
		$report_type_arr["pf_report"] = "PF REPORT";
		$report_type_arr["esi_report"] = "ESI REPORT";
		$report_type_arr["bank_payment_report"] = "BANK PAYMENT REPORT";
		$form_field = array("name"=>"typeofreport", "content"=>"type of report ", "readonly"=>"",  "required"=>"required","type"=>"select", "action"=>array("type"=>"onChange","script"=>"showSelectionType(this.value)"),  "options"=>$report_type_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"employeetype", "content"=>"employee type", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"enableClientDepot(this.value);"),  "options"=>array("OFFICE"=>"OFFICE", "CLIENT BRANCH"=>"CLIENT BRANCH"), "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"month", "value"=>"", "content"=>"salary month", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control", "options"=>$month_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"officebranch", "content"=>"office branch", "readonly"=>"","required"=>"required", "type"=>"select", "options"=>$branch_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"paidfrombranch", "content"=>"paid from ", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>$branches_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"employee", "content"=>"employee ", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>$employees_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"show_employees","content"=>"employees", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>array("ACTIVE"=>"ACTIVE","INACTIVE"=>"INACTIVE"),  "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		
	
		$add_form_fields = array();
		$emps =  \Employee::where("roleId","=",19)->get();
		$emps_arr = array();
		$emps_arr["0"] = "ALL DRIVERS";
		foreach ($emps as $emp){
			$emps_arr[$emp->id] = $emp->fullName;
		}
	
		$vehs =  \Vehicle::where("status","=","ACTIVE")->get();
		$vehs_arr = array();
		foreach ($vehs as $veh){
			$vehs_arr[$veh->id] = $veh->veh_reg;
		}
		$form_field = array("name"=>"fuelstation", "content"=>"by station", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$branches_arr, "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;
		$form_field = array("name"=>"driver", "content"=>"by driver", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$emps_arr, "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;
		$form_field = array("name"=>"vehicle", "content"=>"by vehicle", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$vehs_arr, "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;
	
		$form_info["form_fields"] = $form_fields;
		$form_info["add_form_fields"] = $add_form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.salaryreport', array("values"=>$values));
	}
	private function getMonthlyFuelReport($values){
		if (\Request::isMethod('post'))
		{
			$fromDt = date("Y-m-01", strtotime($values["fromdate"]));
			$toDt = date("Y-m-01", strtotime($values["todate"]));
			$resp = array();
				
			//$values["DSF"];
			$data = array();
			//$select_args = array("employee.id", "employee.fullName", "employee.empCode", "employee.joiningDate", "employee.terminationDate");
				
			$entities = null;
			
			$date = strtotime($fromDt);
			$today = strtotime($toDt);
			if($values["branch"] == "0"){
				$fuelstations =  \FuelStation::OrderBy("fuelstationdetails.name")->where("fuelstationdetails.status","=","ACTIVE")
								->leftjoin("cities","cities.id","=","fuelstationdetails.cityId")
								->select(array("fuelstationdetails.id as id","fuelstationdetails.name as fname", "cities.name as cname"))->get();
			}
			else{
				$fuelstations =  \FuelStation::where("fuelstationdetails.id","=",$values["branch"])
								->leftjoin("cities","cities.id","=","fuelstationdetails.cityId")
								->select(array("fuelstationdetails.id as id","fuelstationdetails.name as fname", "cities.name as cname"))->get();
			}
			
			foreach ($fuelstations as $fuelstation){
				$data_values = array();
				$tot_amt = 0;
				$till_paid = 0;
				$fuelstation_name = $fuelstation->fname." (".$fuelstation->cityId.")";
				$fuelstation_name1 = $fuelstation->fname." (".$fuelstation->cname.")";
				$data_values[] = $fuelstation_name1;
				$temp_date = $date;
				while($temp_date <= $today){
					$fdt =  date("Y-m-01",$temp_date);
					$td = date("Y-m-t",$temp_date);
					$sql = 'select count(*) as cnt, sum(amount) as sumamt,sum(litres) as litres from fueltransactions where status="ACTIVE" and fuelStationId='.$fuelstation->id.' and filledDate BETWEEN "'.$fdt.'" and "'.$td.'";';
					$rec1 = DB::select( DB::raw($sql));
					$rec1 = $rec1[0];
					$temp_date = strtotime("+1 month",$temp_date);
					$data_values[] = $rec1->cnt;
					$data_values[] = $rec1->litres;
					$data_values[] = $rec1->sumamt;
					$tot_amt =$tot_amt+$rec1->sumamt;					
				}
				$toDt = date("Y-m-t", strtotime($values["todate"]));
				$sql = 'select sum(amount) as sumamt from fueltransactions where status="ACTIVE" and fuelStationId='.$fuelstation->id.' and paymentPaid="Yes" and filledDate BETWEEN "'.$fromDt.'" and "'.$toDt.'";';
				$rec1 = DB::select( DB::raw($sql));
				$rec1 = $rec1[0];
				$till_paid = $till_paid+$rec1->sumamt;
				
				$sql = 'select sum(amount) as sumamt from expensetransactions where status="ACTIVE" and entity="FUEL STATION PAYMENT" and entityValue='.$fuelstation->id.' and date BETWEEN "'.$fromDt.'" and "'.$toDt.'";';
				$rec1 = DB::select( DB::raw($sql));
				$rec1 = $rec1[0];
				$till_paid = $till_paid+$rec1->sumamt;
				
				$data_values[] = $tot_amt;
				$data_values[] = $till_paid;
				$data_values[] = $tot_amt-$till_paid;
				$data[] = $data_values;
			}			
			echo json_encode($data);
			return;
		}
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
		
		$branches =  \FuelStation::leftjoin("cities","cities.id","=","fuelstationdetails.cityId")->select($select_args)->get();
		$branches_arr = array();
		$branches_arr["0"] = "ALL FUEL STATIONS";
		foreach ($branches as $branch){
			$branches_arr[$branch->id] = $branch->fname." (".$branch->cname.")";
		}
		$form_field = array("name"=>"branch", "content"=>"branch name", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$branches_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
	
		$values["provider"] = "bankdetails";
		return View::make('reports.fuelmonthlyreport', array("values"=>$values));
	}
	
	private function getFuelReport($values){
		if (\Request::isMethod('post'))
		{
			if(!isset($values["fromdate"])){
				$values["fromdate"] = "10-10-2013";
			}
			if(!isset($values["todate"])){
				$values["todate"] = date("d-m-Y");
			}
			if(!isset($values["month"])){
				$values["month"] = date("d-m-Y");
			}
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$formonth = date("Y-m-d", strtotime($values["month"]));
			$resp1 = array("data"=>array());
			$resp = array();
			if(isset($values["reporttype1"]) && $values["reporttype1"] == "fuelstation_detailed"){
				DB::statement(DB::raw("CALL fuel_transactions_report('".$frmDt."', '".$toDt."');"));
				$recs = DB::select( DB::raw("SELECT * FROM `temp_fuel_transaction` where (entity='FUEL TRANSACTION') and fuelStation='".$values["fuelstationname"]."' order by date"));
				$tot_ltrs = 0;
				$tot_amt = 0;
				foreach($recs as  $rec) {
					$row = array();
					$row["fuelstation"] = $rec->fuelStation;
					$row["vehicle"] = $rec->veh_reg;
					$row["date"] = date("d-m-Y",strtotime($rec->date));
					$row["ltrs"] = $rec->ltrs;
					$tot_ltrs = $tot_ltrs+$rec->ltrs;
					$row["amount"] = $rec->amount;
					$tot_amt = $tot_amt+$rec->amount;
					$row["info"] = "Source : ".$rec->entity."<br/>"."Payment Type : ". $rec->paymentType;
					$row["remarks"] = $rec->remarks;
					$row["createdBy"] = $rec->createdBy;
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp, "total_amt"=>$tot_amt, "total_ltrs"=>$tot_ltrs);
			}
			else if(isset($values["reporttype2"]) && $values["reporttype2"] == "fuelstation_transactions"){
				$frmDt1 = date("Y-m-d", strtotime("10-10-2013"));
				$toDt1 = date("Y-m-d", strtotime(date("d-m-Y")));
				DB::statement(DB::raw("CALL fuel_monthly_payments('".$frmDt1."', '".$toDt1."');"));
				$row = array();
				$recs = DB::select( DB::raw("SELECT * FROM `temp_monthly_fuelpayments`  where fuelStation='".$values["fuelstationname"]."' and MONTH(date)=MONTH('".$formonth."') and YEAR(date)=YEAR('".$formonth."')"));
				foreach($recs as  $rec) {
					$row["fuelstation"] = $rec->fuelStation;
					$row["month"] = date("M-Y",strtotime($formonth));
					if($rec->entity == 'FUEL TRANSACTION' ){
						$row["transactionid"] = "";
					}else{
						$row["transactionid"] = $rec->entityValue;
					}
					$row["transaction"]= $rec->entity;
					$branchname = "";
					$b = \OfficeBranch::where("id","=",$rec->branchId)->get();
					if(count($b)>0){
						$b = $b[0];
						$branchname = $b->name;
					}
					$row["branch"] = $branchname;
					if($rec->branchId==""){
						$entities = \Contract::where("id","=",$rec->contractId)->get();
						if(count($entities)>0){
							$entities= $entities[0];
							$clients = \Client::where("id","=",$entities->clientId)->first();
							$row["branch"]=$clients->name;
						}
					}
					$row["amount"] = $rec->amount;
					$row["paid"] = "";
					if($rec->entity == 'EXPENSE TRANSACTION'){
						$row["paid"] = 'Yes';
					}
					else{
						$row["paid"] = $rec->paymentPaid;
					}
					$row["paiddate"] = date("d-M-Y",strtotime($rec->date));
					$row["paymenttype"] = "";
					if($rec->paymentType != "cash"){
						if($rec->paymentType == "ecs" || $rec->paymentType == "neft" || $rec->paymentType == "rtgs" || $rec->paymentType == "cheque_debit" || $rec->paymentType == "cheque_credit"){
							$row["paymenttype"] = "Payment Type : ".$rec->paymentType."<br/>";
							$bank_dt = \BankDetails::where("id","=",$rec->bankAccount)->first();
							if(count($bank_dt)>0){
								$row["paymenttype"] = $rec->paymentType."<br/>"."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
							}
							$row["paymenttype"]= $row["paymenttype"]."Ref No : ".$rec->chequeNumber;
						}
						if($rec->paymentType == "credit_card" || $rec->paymentType == "debit_card" ||$rec->paymentType == "hp_card"){
							$row["paymenttype"] = "Payment Type : ".$rec->paymentType."<br/>";
							$bank_dt = \Cards::where("id","=",$rec->bankAccount)->first();
							if(count($bank_dt)>0){
								$row["paymenttype"] = $rec->paymentType."<br/>"."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
							}
							$row["paymenttype"] = $row["paymenttype"]."Ref No : ".$rec->chequeNumber;
						}
						if($rec->paymentType == "dd"){
							$row["paymenttype"] = "Payment Type : ".$rec->paymentType."<br/>";
							$row["paymenttype"] = $row["paymenttype"]."Ref No : ".$rec->chequeNumber;
						}
					}
					else{
						$row["paymenttype"] = $rec->paymentType;
					}
					$row["remarks"] = $rec->remarks;
					$row["createdBy"] = $rec->createdBy;
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp);
			}
			else if($values["fuelreporttype"] == "balanceSheetNoDt" || $values["fuelreporttype"] == "balanceSheet"){
				$tot_ltrs=0;
				$range_total=0;
				$range_paid=0;
				$till_total=0;
				$till_paid=0;
				$till_balance=0;
				$frmDt1 = date("Y-m-d", strtotime("10-10-2013"));
				$toDt1 = date("Y-m-d", strtotime(date("d-m-Y")));
				DB::statement(DB::raw("CALL fuel_transactions_report('".$frmDt1."', '".$toDt1."');"));
				if($values["fuelstation"] == "0"){
					$fuelstations =  \FuelStation::OrderBy("name")->get();
				}
				else{
					$fuelstations =  \FuelStation::where("id","=",$values["fuelstation"])->get();
				}
				foreach ($fuelstations as $fuelstation){
					$row = array();
					$fuelstation_name = $fuelstation->name.$fuelstation->cityId;
					$row["fuelstation"] = '<a href="#modal-table" role="button" data-toggle="modal" onclick="getData('.$fuelstation->id.', \''.$fuelstation_name.'\', \''.$values["fromdate"].'\', \''.$values["todate"].'\')" <span="">'.$fuelstation->name.'</a>';
					$row["fromdate"] = $values["fromdate"];
					/*$recs = DB::select( DB::raw("SELECT  FROM `temp_fuel_transaction` where entity='FUEL TRANSACTION' and fuelStation='".$fuelstation->name."'"));
					if(count($recs)>0) {
						$rec = $recs[0];
						$row["fromdate"] = $rec->amt;
					}*/
					$row["todate"] = $values["todate"];
					/*$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_fuel_transaction` where entity='FUEL TRANSACTION' and fuelStation='".$fuelstation->name."'"));
					if(count($recs)>0) {
						$rec = $recs[0];
						$row["todate"] = $rec->amt;
					}*/
					$row["totvehs"] = 0;
					$recs = DB::select( DB::raw("SELECT count(vehicleId) as count  FROM `temp_fuel_transaction` where entity='FUEL TRANSACTION' and fuelStation='".$fuelstation_name."' and date BETWEEN '".$frmDt."' and '".$toDt."'"));
					if(count($recs)>0) {
						$rec = $recs[0];
						$row["totvehs"] = $rec->count;
					}
					$row["totltrs"] = 0;
					$recs = DB::select( DB::raw("SELECT sum(ltrs) as ltrs FROM `temp_fuel_transaction` where entity='FUEL TRANSACTION' and fuelStation='".$fuelstation_name."'and date BETWEEN '".$frmDt."' and '".$toDt."'"));
					if(count($recs)>0) {
						$rec = $recs[0];
						$row["totltrs"] = sprintf('%0.2f',$rec->ltrs);
						$tot_ltrs = $tot_ltrs+sprintf('%0.2f',$rec->ltrs);
					}
					$row["totalamt"] = 0;
					$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_fuel_transaction` where entity='FUEL TRANSACTION' and fuelStation='".$fuelstation_name."'and date BETWEEN '".$frmDt."' and '".$toDt."'"));
					if(count($recs)>0) {
						$rec = $recs[0];
						$row["totalamt"] = $rec->amt;
						$range_total = $range_total+$rec->amt;
					}
					$row["paidamt"] = 0;
					$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_fuel_transaction` where fuelStation='".$fuelstation_name."' and (paymentPaid='Yes') and date BETWEEN '".$frmDt."' and '".$toDt."'"));
					if(count($recs)>0) {
						$rec = $recs[0];
						$row["paidamt"] = $rec->amt;
						$range_paid = $range_paid+$rec->amt;
					}
					$row["paidyes"] = 0;
					$recs = DB::select( DB::raw("SELECT count(paymentPaid) as yes, sum(amount) as amt FROM `temp_fuel_transaction` where (entity='FUEL TRANSACTION') and fuelStation='".$fuelstation_name."' and  paymentPaid='Yes' and date BETWEEN '".$frmDt."' and '".$toDt."'"));
					if(count($recs)>0) {
						$rec = $recs[0];
						$row["paidyes"] = $rec->yes." (".$rec->amt.")";
					}
					$row["paidno"] = 0;
					$recs = DB::select( DB::raw("SELECT count(paymentPaid) as no, sum(amount) as amt FROM `temp_fuel_transaction` where (entity='FUEL TRANSACTION')  and fuelStation='".$fuelstation_name."' and (paymentPaid!='Yes') and date BETWEEN '".$frmDt."' and '".$toDt."'"));
					if(count($recs)>0) {
						$rec = $recs[0];
						$row["paidno"] = $rec->no." (".$rec->amt.")";;
					}
					$row["tilldtamt"] = 0;
					$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_fuel_transaction` where  (entity='FUEL TRANSACTION')  and fuelStation='".$fuelstation_name."' and  date BETWEEN '".$frmDt1."' and '".$toDt1."'"));
					if(count($recs)>0) {
						$rec = $recs[0];
						$row["tilldtamt"] = $rec->amt;
						$till_total = $till_total+$rec->amt;
					}
					$row["tdtpayamt"] = 0;
					$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_fuel_transaction` where date BETWEEN '".$frmDt1."' and '".$toDt1."' and fuelStation='".$fuelstation_name."' and ((entity='FUEL TRANSACTION' and paymentPaid='yes') or entity='EXPENSE TRANSACTION') "));
					if(count($recs)>0) {
						$rec = $recs[0];
						$row["tdtpayamt"] = $rec->amt;
						$till_paid = $till_paid+$rec->amt;
					}
					$row["balance"] = 0;
					if($row["tilldtamt"] != 0  || $row["tdtpayamt"] != 0){
						$row["balance"] = $row["tilldtamt"]-$row["tdtpayamt"];
						$till_balance = $till_balance+$row["balance"];
						
					}
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp,"totltrs"=>$tot_ltrs,"rangetotal"=>$range_total,"rangepaid"=>$range_paid,"tilltotamt"=>$till_total,"tillpayamt"=>$till_paid,"tillbalance"=>$till_balance);
			}
			else if($values["fuelreporttype"] == "payment"){
				DB::statement(DB::raw("CALL fuel_transactions_report('".$frmDt."', '".$toDt."');"));
				$tot_amt=0;
				if($values["fuelstation"] == "0"){
					$fuelstations =  \FuelStation::OrderBy("name")->get();
					foreach ($fuelstations as $fuelstation){
						$row = array();
						$row["fuelstation"] = $fuelstation->name;
						$recs = DB::select( DB::raw("SELECT * FROM `temp_fuel_transaction` where ((entity='FUEL TRANSACTION' and paymentPaid='yes') or entity='EXPENSE TRANSACTION') and fuelStation='".$fuelstation->name.$fuelstation->cityId."'"));
						foreach($recs as  $rec) {
							$row["amount"] = $rec->amount;
							$tot_amt = $tot_amt+$rec->amount;
							$row["date"] = date("d-m-Y",strtotime($rec->date));
							$branchname = "";
							$b = \OfficeBranch::where("id","=",$rec->branchId)->get();
							if(count($b)>0){
								$b = $b[0];
								$branchname = $b->name;
							}
							$row["info"] = "Source : ".$rec->entity."<br/>"."Payment Type : ". $rec->paymentType."<br/>"."Transaction Branch : ".$branchname;
							$row["remarks"] = $rec->remarks;
							$row["createdBy"] = $rec->createdBy;
							$resp[] = $row;
						}
						$resp1 = array("data"=>$resp,"total_amt"=>$tot_amt);
					}
				}
				else if($values["fuelstation"] > 0){
					$fuelstations =  \FuelStation::where("id","=",$values["fuelstation"])->get();
					foreach ($fuelstations as $fuelstation){
						$row = array();
						$row["fuelstation"] = $fuelstation->name;
						$recs = DB::select( DB::raw("SELECT * FROM `temp_fuel_transaction` where ((entity='FUEL TRANSACTION' and paymentPaid='yes') or entity='EXPENSE TRANSACTION') and fuelStation='".$fuelstation->name.$fuelstation->cityId."'"));
						foreach($recs as  $rec) {
							$row["amount"] = $rec->amount;
							$tot_amt = $tot_amt+$rec->amount;
							$row["date"] = date("d-m-Y",strtotime($rec->date));
							$branchname = "";
							$b = \OfficeBranch::where("id","=",$rec->branchId)->get();
							if(count($b)>0){
								$b = $b[0];
								$branchname = $b->name;
							}
							$row["info"] = "Source : ".$rec->entity."<br/>"."Payment Type : ". $rec->paymentType."<br/>"."Transaction Branch : ".$branchname;
							$row["remarks"] = $rec->remarks;
							$row["createdBy"] = $rec->createdBy;
							$resp[] = $row;
						}
						$resp1 = array("data"=>$resp,"total_amt"=>$tot_amt);
					}
				}
			}
			
			else if($values["fuelreporttype"] == "advances"){
				$tot_amt=0;
				//DB::statement(DB::raw("CALL fuel_transactions_report('".$frmDt."', '".$toDt."');"));
				if($values["fuelstation"] == "0"){
					$fuelstations =  \FuelStation::OrderBy("name")->get();
					$emps =  \Employee::where("status","=","ACTIVE")->get();
					$emp_arr = array();
					foreach ($emps as $emp){
						$emp_arr[$emp->id] = $emp->fullName;
					}
					foreach ($fuelstations as $fuelstation){
						$row = array();
						$row["fuelstation"] = $fuelstation->name;
						$recs = DB::select( DB::raw("SELECT * FROM `expensetransactions` where entity='FUEL STATION ADVANCE' and entityValue='".$fuelstation->id."'and date BETWEEN '".$frmDt."' and '".$toDt."'"));
						foreach($recs as  $rec) {
							$row["amount"] = $rec->amount;
							$tot_amt = $tot_amt+$rec->amount;
							$row["date"] = date("d-m-Y",strtotime($rec->date));
							$branchname = "";
							$b = \OfficeBranch::where("id","=",$rec->branchId)->get();
							if(count($b)>0){
								$b = $b[0];
								$branchname = $b->name;
							}
							$row["info"] = "Source : ".$rec->entity."<br/>"."Payment Type : ". $rec->paymentType."<br/>"."Transaction Branch : ".$branchname;
							$row["remarks"] = $rec->remarks;
							if(isset($emp_arr[$rec->createdBy])){
								$row["createdBy"] = $emp_arr[$rec->createdBy];
							}
							else {
								$row["createdBy"] = "";
							}
							$resp[] = $row;
						}
						$resp1 = array("data"=>$resp,"total_amt"=>$tot_amt);
					}
				}
				else if($values["fuelstation"] > 0){
					$fuelstations =  \FuelStation::where("id","=",$values["fuelstation"])->get();
					foreach ($fuelstations as $fuelstation){
						$row = array();
						$row["fuelstation"] = $fuelstation->name;
						$recs = DB::select( DB::raw("SELECT * FROM `expensetransactions` where entity='FUEL STATION ADVANCE' and entityValue='".$fuelstation->id."'and date BETWEEN '".$frmDt."' and '".$toDt."'"));
						$emps =  \Employee::where("status","=","ACTIVE")->get();
						$emp_arr = array();
						foreach ($emps as $emp){
							$emp_arr[$emp->id] = $emp->fullName;
						}
						foreach($recs as  $rec) {
							$row["amount"] = $rec->amount;
							$tot_amt = $tot_amt+$rec->amount;
							$row["date"] = date("d-m-Y",strtotime($rec->date));
							$branchname = "";
							$b = \OfficeBranch::where("id","=",$rec->branchId)->get();
							if(count($b)>0){
								$b = $b[0];
								$branchname = $b->name;
							}
							$row["info"] = "Source : ".$rec->entity."<br/>"."Payment Type : ". $rec->paymentType."<br/>"."Transaction Branch : ".$branchname;
							$row["remarks"] = $rec->remarks;
							$row["createdBy"] = $emp_arr[$rec->createdBy];
							$resp[] = $row;
						}
						$resp1 = array("data"=>$resp,"total_amt"=>$tot_amt);
					}
				}
			}
			else if($values["fuelreporttype"] == "formonthlypayments"){
				$frmDt1 = date("Y-m-d", strtotime("10-10-2013"));
				$toDt1 = date("Y-m-d", strtotime(date("d-m-Y")));
				DB::statement(DB::raw("CALL fuel_monthly_payments('".$frmDt1."', '".$toDt1."');"));
				if($values["fuelstation"] == "0"){
					$fuelstations =  \FuelStation::OrderBy("name")->get();
				}
				else{
					$fuelstations =  \FuelStation::where("id","=",$values["fuelstation"])->get();
				}
				$tot_amt = 0;
				$tot_paid = 0;
				$tot_balance= 0;
				$city_arr = array();
				$cities = \City::All();
				foreach ($cities as $city){
					$city_arr[$city->id] = $city->name;
				}
				
				foreach ($fuelstations as $fuelstation){
					$row = array();
					$fuelstation_name = $fuelstation->name.$fuelstation->cityId;
					$row["fuelstation"] = '<a href="#modal-table2" role="button" data-toggle="modal" onclick="getDetails('.$fuelstation->id.', \''.$fuelstation_name.'\', \''.$values["month"].'\')" <span="">'.$fuelstation->name.'('.$city_arr[$fuelstation->cityId].')</a>';
					$row["month"] = date("M-Y",strtotime($formonth));
					$row["total"] = 0;
					$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_monthly_fuelpayments` where entity='FUEL TRANSACTION' and fuelStation='".$fuelstation_name."'and MONTH(date)=MONTH('".$formonth."') and YEAR(date)=YEAR('".$formonth."')"));
					if(count($recs)>0) {
						$rec = $recs[0];
						$row["total"]= $rec->amt;
					}	
					$row["totalpaid"]=0;
					$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_monthly_fuelpayments` where ((entity='FUEL TRANSACTION' and paymentPaid='Yes') or entity='EXPENSE TRANSACTION') and fuelStation='".$fuelstation_name."' and MONTH(date)=MONTH('".$formonth."') and YEAR(date)=YEAR('".$formonth."')"));
					if(count($recs)>0) {
						$rec = $recs[0];
						$row["totalpaid"]=$rec->amt;
					}
					$row["balance"]=0;
					$tot_balance= $row["total"]-$row["totalpaid"];
					if($tot_balance !=0){
						$row["balance"]=$tot_balance;
					}
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp);
			}
			else if($values["fuelreporttype"] == "tracking"){
				DB::statement(DB::raw("CALL fuel_transactions_report('".$frmDt."', '".$toDt."');"));
				if($values["fuelstation"] == "0"){
					$fuelstations =  \FuelStation::OrderBy("name")->get();
					$tot_ltrs = 0;
					$tot_amt = 0;
					foreach ($fuelstations as $fuelstation){
						$row = array();
						$row["fuelstation"] = $fuelstation->name;
						$recs = DB::select( DB::raw("SELECT * FROM `temp_fuel_transaction` where (entity='FUEL TRANSACTION') and fuelStation='".$fuelstation->name.$fuelstation->cityId."'"));
						foreach($recs as  $rec) {
							$row["vehicle"] = $rec->veh_reg;
							$row["date"] = date("d-m-Y",strtotime($rec->date));
							$row["ltrs"] = $rec->ltrs;
							$tot_ltrs = $tot_ltrs+$rec->ltrs;
							$row["amount"] = $rec->amount;
							$tot_amt = $tot_amt+$rec->amount;
							$row["info"] = "Source : ".$rec->entity."<br/>"."Payment Type : ". $rec->paymentType;
							$row["remarks"] = $rec->remarks;
							$row["createdBy"] = $rec->createdBy;
							$resp[] = $row;
						}
						$resp1 = array("data"=>$resp, "total_amt"=>$tot_amt, "total_ltrs"=>$tot_ltrs);
						
					}
				}
				else if($values["fuelstation"] > 0){
					$fuelstations =  \FuelStation::where("id","=",$values["fuelstation"])->get();
					//print_r($fuelstations);die();
					$tot_ltrs = 0;
					$tot_amt = 0;
					foreach ($fuelstations as $fuelstation){
						$row = array();
						$row["fuelstation"] = $fuelstation->name;
						$recs = DB::select( DB::raw("SELECT * FROM `temp_fuel_transaction` where (entity='FUEL TRANSACTION') and fuelStation='".$fuelstation->name.$fuelstation->cityId."'"));
						foreach($recs as  $rec) {
							$row["vehicle"] = $rec->veh_reg;
							$row["date"] = date("d-m-Y",strtotime($rec->date));
							$row["ltrs"] = $rec->ltrs;
							$tot_ltrs = $tot_ltrs+$rec->ltrs;
							$row["amount"] = $rec->amount;
							$tot_amt = $tot_amt+$rec->amount;
							$row["info"] = "Source : ".$rec->entity."<br/>"."Payment Type : ". $rec->paymentType;
							$row["remarks"] = $rec->remarks;
							$row["createdBy"] = $rec->createdBy;
							$resp[] = $row;
						}
						$resp1 = array("data"=>$resp, "total_amt"=>$tot_amt, "total_ltrs"=>$tot_ltrs);
					}
				}
			}
			else if($values["fuelreporttype"] == "vehicleReport"){
				DB::statement(DB::raw("CALL fuel_transactions_report('".$frmDt."', '".$toDt."');"));
				$recs = DB::select( DB::raw("SELECT * FROM `temp_fuel_transaction` where (entity='FUEL TRANSACTION') and vehicleId='".$values["vehicle"]."' ORDER BY date DESC"));
				$tot_ltrs = 0;
				$tot_amt = 0;
				foreach($recs as  $rec) {
					$row = array();
					$row["fuelstation"] = $rec->fuelStation;
					$row["vehicle"] = $rec->veh_reg;
					$row["date"] = date("d-m-Y",strtotime($rec->date));
					$row["ltrs"] = $rec->ltrs;
					$tot_ltrs = $tot_ltrs+$rec->ltrs;
					$row["amount"] = $rec->amount;
					$tot_amt = $tot_amt+$rec->amount;
					$row["startreading"] = $rec->startReading;
					$row["fullTank"] = $rec->fullTank;
					$row["mileage"] = $rec->mileage;
					$row["bill"] = $rec->billNo;
					$row["info"] = "Source : ".$rec->entity."<br/>"."Payment Type : ". $rec->paymentType;
					$row["remarks"] = $rec->remarks;
					$row["createdBy"] = $rec->createdBy;
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp, "total_amt"=>$tot_amt, "total_ltrs"=>$tot_ltrs);
			}
			echo json_encode($resp1);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$branches =  \FuelStation::leftjoin("cities","cities.id","=","fuelstationdetails.cityId")->select($select_args)->get();
		$branches_arr = array();
		$branches_arr["0"] = "ALL FUEL STATIONS";
		foreach ($branches as $branch){
			$branches_arr[$branch->id] = $branch->fname." (".$branch->cname.")";
		}
	
		$fuel_rep_arr = array();
		$fuel_rep_arr['balanceSheetNoDt'] = "Fuel Station Balance Sheet";
		//$fuel_rep_arr['balanceSheet'] = "Fuel Station Range Sheet";
		$fuel_rep_arr['payment'] = "Fuel Station Payments";
		$fuel_rep_arr['tracking'] = "Track By Station";
		$fuel_rep_arr['vehicleReport'] = "Track By Vehicle";
		$fuel_rep_arr['advances'] = "Fuel Station Advance";
		$fuel_rep_arr['formonthlypayments'] = "For Monthly Payments";
		
		//$fuel_rep_arr['employeeReport'] = "Track By Driver";
		$month_arr = array();
		$month_arr[date('Y', strtotime('-1 year'))."-04-01"] = 'April '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-05-01"] = 'may '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-06-01"] = 'June '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-07-01"] = 'July '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-08-01"] = 'Aug '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-09-01"] = 'Sep '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-10-01"] = 'Oct '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-11-01"] = 'Nov '.date('Y', strtotime('-1 year'));
		$month_arr[date('Y', strtotime('-1 year'))."-12-01"] = 'Dec '.date('Y', strtotime('-1 year'));	
		$month_arr[date('Y')."-01-01"] = 'Jan '.date('Y');
		$month_arr[date('Y')."-02-01"] = 'Feb '.date('Y');
		$month_arr[date('Y')."-03-01"] = 'March '.date('Y');
		$month_arr[date('Y')."-04-01"] = 'April '.date('Y');
		$month_arr[date('Y')."-05-01"] = 'May '.date('Y');
		$month_arr[date('Y')."-06-01"] = 'June '.date('Y');
		$month_arr[date('Y')."-07-01"] = 'July'.date('Y');
		$month_arr[date('Y')."-08-01"] = 'Aug '.date('Y');
		$month_arr[date('Y')."-09-01"] = 'Sep '.date('Y');
		$month_arr[date('Y')."-10-01"] = 'Oct '.date('Y');
		$month_arr[date('Y')."-11-01"] = 'Nov '.date('Y');
		$month_arr[date('Y')."-12-01"] = 'Dec '.date('Y');
	
		$form_field = array("name"=>"fuelreporttype", "content"=>"report for ", "readonly"=>"",  "required"=>"required","type"=>"select", "action"=>array("type"=>"onChange","script"=>"showSelectionType(this.value)"), "options"=>$fuel_rep_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"month","content"=>"For month", "readonly"=>"",  "required"=>"", "type"=>"select", "class"=>"form-control", "options"=>$month_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$add_form_fields = array();
		$emps =  \Employee::where("roleId","=",19)->get();
		$emps_arr = array();
		$emps_arr["0"] = "ALL DRIVERS";
		foreach ($emps as $emp){
			$emps_arr[$emp->id] = $emp->fullName;
		}
	
		$vehs =  \Vehicle::All();
		$vehs_arr = array();
		foreach ($vehs as $veh){
			$vehs_arr[$veh->id] = $veh->veh_reg;
		}
		$form_field = array("name"=>"fuelstation", "content"=>"by station", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$branches_arr, "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;
		/* $form_field = array("name"=>"driver", "content"=>"by driver", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$emps_arr, "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field; */
		$form_field = array("name"=>"vehicle", "content"=>"by vehicle", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$vehs_arr, "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;
	
		$form_info["form_fields"] = $form_fields;
		$form_info["add_form_fields"] = $add_form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.fuelreport', array("values"=>$values));
	}
	
	private function getDailyFinanceDetailedReport($values){
		if (\Request::isMethod('post'))
		{
			if(!isset($values["fromdate"])){
				$values["fromdate"] = "10-10-2013";
			}
			if(!isset($values["todate"])){
				$values["todate"] = date("d-m-Y");
			}
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			$select_args = array();//'Finance Company',"Loan Amount",'Loan No', "Paid Amount", "Paid Date","Office Branch", "Created By"
			$select_args[] = "financecompanies.name as name";
			$select_args[] = "officebranch.name as bname";
			$select_args[] = "expensetransactions.date as date";
			$select_args[] = "expensetransactions.amount as amount";
			$select_args[] = "employee.fullName as ename";
			$select_args[] = "expensetransactions.paymentType as paymentType";
			$select_args[] = "expensetransactions.createdBy as createdBy";
			$recs = \ExpenseTransaction::where("expensetransactions.entity","=","DAILY FINANCE PAYMENT")
										->leftjoin("dailyfinances","expensetransactions.entityValue","=","dailyfinances.Id")
										->leftjoin("financecompanies","dailyfinances.financeCompanyId","=","financecompanies.Id")
										->leftjoin("officebranch","dailyfinances.branchId","=","officebranch.Id")
										->leftjoin("inchargeaccounts","expensetransactions.inchargeId","=","inchargeaccounts.Id")
										->leftjoin("employee","inchargeaccounts.empid","=","employee.Id")
										->where("expensetransactions.entityValue","=",$values["dailyfinance"])
										->whereBetween('expensetransactions.date', array($frmDt, $toDt))
										->select($select_args)->get();
// 								$val = "%FINA%";
// 										$recs = \ExpenseTransaction::where("entity","like",$val)->get();
// 			print_r($recs);
// 			die();
			foreach($recs as  $rec) {
				$row = array();
				$row["name"] = $rec->name;
				$row["bname"] = $rec->bname;
				$row["date"] = date("d-m-Y",strtotime($rec->date));
				$row["amount"] = $rec->amount;
				$row["ename"] = $rec->ename;
				$row["paymentType"] = $rec->paymentType;
				$single = \Employee::where("id","=",$rec->createdBy)->first();
				$row["createdBy"] = $single->fullName;
				$resp[] = $row;
			}
			echo json_encode($resp);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array("Finance Company","Loan No","Loan Amount", "Paid Amount", "Paid Date","Office Branch", "Created By");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$qry = "select df.id as id, name, amountFinanced, installmentAmount, agmtDate, paidInstallments, totalInstallments from dailyfinances df, financecompanies f where df.financeCompanyId=f.id and df.deleted='No' order by name, agmtDate asc";
				$dailyfinances = \DB::select(\DB::raw($qry));
				$entity_arr = array();
				$dfName = '';
				$i  = 0;
				$loanNo= 0;
				foreach ($dailyfinances as $dailyfinance){
					$id = $dailyfinance->id;
					$name = $dailyfinance->name;
					$amountFinanced = $dailyfinance->amountFinanced;
					$paidInstallments = $dailyfinance->paidInstallments;
					$installmentAmount = $dailyfinance->installmentAmount;
					$eqry = "select sum(amount) as paidAmount from expensetransactions where entity='DAILY FINANCE PAYMENT' and entityValue=$id and status='ACTIVE'";
					$eresults = \DB::select(\DB::raw($eqry));
					$paidAmount = 0;
					if(count($eresults)>0){
						$erow = $eresults[0];
						$paidAmount = $erow->paidAmount;
					}
					if($paidAmount+($paidInstallments*$installmentAmount) >= $amountFinanced)
						continue;
					
					if($i == 0)
					{
						$dfName = $name;
						$loanNo = 1;
					}
					else if($dfName === $name)
					{
						$loanNo++;
					}
					else
					{
						$dfName = $name;
						$loanNo = 1;
					}
					$amountFinanced=$dailyfinance->amountFinanced;
					$installmentAmount=$dailyfinance->installmentAmount;
					$finName = $name.'-'.$amountFinanced.'-'.$installmentAmount.'- Loan No'.$loanNo;
					$i++;
					$entity_arr[$id] = $finName;
				}
				$entity_name = "dailyfinance";
				$entity_text = "daily finance ";
		$form_field = array("name"=>$entity_name, "content"=>$entity_text, "readonly"=>"",  "required"=>"", "type"=>"select", "class"=>"form-control chosen-select",  "options"=>$entity_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.dailyfinancedetailed', array("values"=>$values));
	}
	
	private function getCreditSupplierReport($values){
		if (\Request::isMethod('post'))
		{
			if(!isset($values["fromdate"])){
				$values["fromdate"] = "10-10-2013";
			}
			if(!isset($values["todate"])){
				$values["todate"] = date("d-m-Y");
			}
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			DB::statement(DB::raw("CALL credit_supplier_report('".$frmDt."', '".$toDt."');"));
			if($values["supplierreporttype"] == "balanceSheetNoDt" || $values["supplierreporttype"] == "balanceSheet"){
				if($values["creditsupplier"] == "0"){
					$creditSuppliers =  \CreditSupplier::OrderBy("supplierName")->get();
					foreach ($creditSuppliers as $creditSupplier){
						$row = array();
						$row["fuelstation"] = $creditSupplier->supplierName;
						$repair_paidamt = 0;
						$repair_unpaidamt = 0;
						$purchase_paidamt = 0;
						$purchase_unpaidamt = 0;
						$payments = 0;
		
						$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_credit_supplier` where entity='repairs' and paymentPaid='Yes' and creditsupplier='".$creditSupplier->supplierName."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$repair_paidamt = $rec->amt;
						}
						$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_credit_supplier` where entity='repairs' and paymentPaid='No' and creditsupplier='".$creditSupplier->supplierName."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$repair_unpaidamt = $rec->amt;
						}
		
						$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_credit_supplier` where entity='purchase' and paymentPaid='Yes' and creditsupplier='".$creditSupplier->supplierName."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$purchase_paidamt = $rec->amt;
						}
						$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_credit_supplier` where entity='purchase' and paymentPaid='No' and creditsupplier='".$creditSupplier->supplierName."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$purchase_unpaidamt = $rec->amt;
						}
		
						$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_credit_supplier` where entity='expensetransactions' and creditsupplier='".$creditSupplier->supplierName."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$payments = $rec->amt;
						}
		
						$supplier_balance = ($repair_paidamt-$repair_unpaidamt)+($purchase_paidamt-$purchase_unpaidamt)+($payments);
		
						if($supplier_balance != 0){
							$row["repairs"] = "<div><span style='color:red;float:right;'>UNPAID :".sprintf('%0.2f',$repair_unpaidamt)."<span><br/>";
							$row["repairs"] = $row["repairs"]."<span style='color:green;float:right;'>PAID : ".sprintf('%0.2f',$repair_paidamt)."<span></div>";
								
							$row["purchases"] = "<div><span style='color:red;float:right;'>UNPAID :".sprintf('%0.2f',$purchase_unpaidamt)."<span><br/>";
							$row["purchases"] = $row["purchases"]."<span style='color:green;float:right;'>PAID : ".sprintf('%0.2f',$purchase_paidamt)."<span></div>";
								
							$row["payments"] = "<div><span style='color:blue;float:right;'>".sprintf('%0.2f',$payments)."<span><br/>";
							$color = "color:red";
							if($supplier_balance>0){
								$color = "color:green";
							}
							$row["balance"] = "<div><span style='".$color.";float:right;'>".sprintf('%0.2f',$supplier_balance)."<span><br/>";
								
							$resp[] = $row;
						}
					}
				}
				else if($values["creditsupplier"] > 0){
					$creditSuppliers =  \CreditSupplier::where("id","=",$values["creditsupplier"])->get();
					foreach ($creditSuppliers as $creditSupplier){
						$row = array();
						$row["fuelstation"] = $creditSupplier->supplierName;
						$repair_paidamt = 0;
						$repair_unpaidamt = 0;
						$purchase_paidamt = 0;
						$purchase_unpaidamt = 0;
						$payments = 0;
						$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_credit_supplier` where entity='repairs' and paymentPaid='Yes' and creditsupplier='".$creditSupplier->supplierName."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$repair_paidamt = $rec->amt;
						}
						$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_credit_supplier` where entity='repairs' and paymentPaid='No' and creditsupplier='".$creditSupplier->supplierName."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$repair_unpaidamt = $rec->amt;
						}
		
						$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_credit_supplier` where entity='purchase' and paymentPaid='Yes' and creditsupplier='".$creditSupplier->supplierName."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$purchase_paidamt = $rec->amt;
						}
						$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_credit_supplier` where entity='purchase' and paymentPaid='No' and creditsupplier='".$creditSupplier->supplierName."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$purchase_unpaidamt = $rec->amt;
						}
		
						$recs = DB::select( DB::raw("SELECT sum(amount) as amt FROM `temp_credit_supplier` where entity='expensetransactions' and creditsupplier='".$creditSupplier->supplierName."'"));
						if(count($recs)>0) {
							$rec = $recs[0];
							$payments = $rec->amt;
						}
		
						$supplier_balance = ($repair_paidamt-$repair_unpaidamt)+($purchase_paidamt-$purchase_unpaidamt)+($payments);
		
						if($supplier_balance != 0){
							$row["repairs"] = "<div><span style='color:red;float:right;'>UNPAID :".sprintf('%0.2f',$repair_unpaidamt)."<span><br/>";
							$row["repairs"] = $row["repairs"]."<span style='color:green;float:right;'>PAID : ".sprintf('%0.2f',$repair_paidamt)."<span></div>";
								
							$row["purchases"] = "<div><span style='color:red;float:right;'>UNPAID :".sprintf('%0.2f',$purchase_unpaidamt)."<span><br/>";
							$row["purchases"] = $row["purchases"]."<span style='color:green;float:right;'>PAID : ".sprintf('%0.2f',$purchase_paidamt)."<span></div>";
								
							$row["payments"] = "<div><span style='color:blue;float:right;'>".sprintf('%0.2f',$payments)."<span><br/>";
							$color = "color:red";
							if($supplier_balance>0){
								$color = "color:green";
							}
							$row["balance"] = "<div><span style='".$color.";float:right;'>".sprintf('%0.2f',$supplier_balance)."<span><br/>";
								
							$resp[] = $row;
						}
					}
				}
			}
			else if($values["supplierreporttype"] == "payment"){
				if($values["creditsupplier"] == "0"){
					$creditSuppliers =  \CreditSupplier::OrderBy("supplierName")->get();
					foreach ($creditSuppliers as $creditSupplier){
						$row = array();
						$recs = DB::select( DB::raw("SELECT * FROM `temp_credit_supplier` where (paymentPaid='Yes' or entity='expensetransactions') and creditsupplier='".$creditSupplier->supplierName."'"));
						foreach($recs as  $rec) {
							$row["fuelstation"] = $creditSupplier->supplierName;
							$row["amount"] = $rec->amount;
							$row["date"] = date("d-m-Y",strtotime($rec->date));
							$row["info"] = "Source : ".$rec->entity."<br/>"."Payment Type : ". $rec->paymentType;
							$row["remarks"] = $rec->remarks;
							$row["createdBy"] = $rec->createdBy;
							$resp[] = $row;
						}
					}
				}
				else if($values["creditsupplier"] > 0){
					$creditSuppliers =  \CreditSupplier::where("id","=",$values["creditsupplier"])->get();
					foreach ($creditSuppliers as $creditSupplier){
						$row = array();
						$row["fuelstation"] = $creditSupplier->supplierName;
						$recs = DB::select( DB::raw("SELECT * FROM `temp_credit_supplier` where (paymentPaid='Yes' or entity='expensetransactions') and creditsupplier='".$creditSupplier->supplierName."'"));
						foreach($recs as  $rec) {
							$row["amount"] = $rec->amount;
							$row["date"] = date("d-m-Y",strtotime($rec->date));
							$row["info"] = "Source : ".$rec->entity."<br/>"."Payment Type : ". $rec->paymentType;
							$row["remarks"] = $rec->remarks;
							$row["createdBy"] = $rec->createdBy;
							$resp[] = $row;
						}
					}
				}
			}
			else if($values["supplierreporttype"] == "repairs"){
				$qry=  \CreditSupplierTransactions::whereBetween("date",array($frmDt,$toDt))->where("creditsuppliertransactions.deleted","=","No");
				if($values["creditsupplier"] != "0"){
					$qry->where("creditSupplierId","=",$values["creditsupplier"]);
				}
				$qry->leftjoin("creditsuppliertransdetails","creditsuppliertransactions.id","=","creditsuppliertransdetails.creditSupplierTransId")
				->leftjoin("creditsuppliers","creditsuppliers.id","=","creditsuppliertransactions.creditSupplierId")
				->leftjoin("lookuptypevalues","creditsuppliertransdetails.repairedItem","=","lookuptypevalues.id");
		
				$select_args[] = "creditsuppliers.supplierName as creditsuppliername";
				$select_args[] = "lookuptypevalues.name as itemdetails";
				$select_args[] = "creditsuppliertransactions.transactionType as transactionType";
				$select_args[] = "creditsuppliertransdetails.vehicleIds as vehicleIds";
				$select_args[] = "creditsuppliertransactions.date as transactionDate";
				$select_args[] = "creditsuppliertransdetails.amount as amount";
				$select_args[] = "creditsuppliertransactions.labourCharges as labourCharges";
				$select_args[] = "creditsuppliertransactions.electricianCharges as electricianCharges";
				$select_args[] = "creditsuppliertransactions.batta as batta";
				$select_args[] = "creditsuppliertransactions.billNumber as bill";
				$select_args[] = "creditsuppliertransactions.paymentPaid as paymentPaid";
				$select_args[] = "creditsuppliertransactions.comments as remarks";
				$select_args[] = "creditsuppliertransactions.filePath as filePath";
		
		
				$recs = $qry->select($select_args)->get();
		
				$veh_arr = array();
				$vehicles = \Vehicle::all();
				foreach ($vehicles as $vehicle){
					$veh_arr[$vehicle->id] = $vehicle->veh_reg;
				}
				foreach($recs as  $rec) {
					$row = array();
					$row["creditsuppliername"] = $rec->creditsuppliername;
					$row["transactionType"] = "REPAIRS";
					$veh_arr_str = "";
					$veh_arr_ids = explode(",", $rec->vehicleIds);
					foreach ($veh_arr_ids as $veh){
						if ($veh != ""){
							$veh_arr_str = $veh_arr_str.$veh_arr[$veh].",";
						}
					}
					$row["vehiclename"] = $veh_arr_str;
					$row["transactiondate"] = date("d-m-Y",strtotime($rec->transactionDate));
					$row["itemdetails"] = $rec->itemdetails;
					$row["repairamount"] = $rec->amount;
					$row["labourcharge"] = $rec->labourCharges;
					$row["electriciancharge"] = $rec->electricianCharges;
					$row["batta"] = $rec->batta;
					$row["paymentPaid"] = $rec->paymentPaid;
					if($rec->bill != ""){
						if($rec->filePath==""){
							$row["bill"] = "<span style='color:red; font-weight:bold;'>".$rec->bill."</span>";
						}
						else{
							$row["bill"] = "<a href='../app/storage/uploads/".$rec->filePath."' target='_blank'>".$rec->bill."</a>";
						}
					}
					$row["remarks"] = $rec->comments;
					$resp[] = $row;
				}
			}
			else if($values["supplierreporttype"] == "purchase"){
				$qry=  \PurchasedOrders::whereBetween("orderDate",array($frmDt,$toDt))->where("type","=","PURCHASE ORDER");
				if($values["creditsupplier"] != "0"){
					$qry->where("creditSupplierId","=",$values["creditsupplier"]);
				}
				$qry->join("creditsuppliers","creditsuppliers.id","=","purchase_orders.creditSupplierId")
					->join("purchased_items","purchased_items.purchasedOrderId","=","purchase_orders.id")
					->join("items","items.id","=","purchased_items.itemId")
					->join("manufactures","manufactures.id","=","purchased_items.manufacturerId");
		
				$select_args[] = "creditsuppliers.supplierName as creditsuppliername";
				$select_args[] = "items.name as itemname";
				$select_args[] = "manufactures.name as itemcompany";
				$select_args[] = "purchased_items.purchasedQty as purchasedQty";
				$select_args[] = "purchased_items.unitPrice as unitPrice";
				$select_args[] = "purchase_orders.amountPaid as amountPaid";
				$select_args[] = "purchase_orders.orderDate as orderDate";
				$select_args[] = "purchase_orders.billNumber as billNumber";
				$select_args[] = "purchase_orders.comments as comments";
				$select_args[] = "purchase_orders.filePath as filePath";
		
				$recs = $qry->select($select_args)->get();
		
				foreach($recs as  $rec) {
					$row = array();
					$row["creditsuppliername"] = $rec->creditsuppliername;
					$row["itemname"] = $rec->itemname;
					$row["itemcompany"] = $rec->itemcompany;
					$row["purchasedQty"] = $rec->purchasedQty;
					$row["amount"] = ($rec->purchasedQty*$rec->unitPrice);
					$row["orderDate"] = date("d-m-Y",strtotime($rec->orderDate));
					$row["amountPaid"] = $rec->amountPaid;
					if($rec->billNumber != ""){
						if($rec->filePath==""){
							$row["bill"] = "<span style='color:red; font-weight:bold;'>".$rec->billNumber."</span>";
						}
						else{
							$row["bill"] = "<a href='../app/storage/uploads/".$rec->filePath."' target='_blank'>".$rec->billNumber."</a>";
						}
					}
					$row["comments"] = $rec->comments;
						
					$resp[] = $row;
				}
			}
			else if($values["supplierreporttype"] == "vehicleReport"){
				//$qry=  \CreditSupplierTransactions::whereBetween("date",array($frmDt,$toDt));
		
				$qry1 = "select creditsuppliertransdetails.vehicleIds as vehicleIds, creditsuppliers.supplierName as creditsuppliername,creditsuppliertransactions.billNumber as billNumber,creditsuppliertransactions.filePath as filePath,creditsuppliertransactions.paymentPaid as paymentPaid, creditsuppliertransactions.comments as comments,creditsuppliertransactions.date as date, lookuptypevalues.name as itemdetails, creditsuppliertransactions.amount as amount from ";
				$qry1 = $qry1."creditsuppliertransactions left join creditsuppliertransdetails on creditsuppliertransactions.id = creditsuppliertransdetails.creditSupplierTransId";
				$qry1 = $qry1." left join creditsuppliers on creditsuppliers.id = creditsuppliertransactions.creditSupplierId";
				$qry1 = $qry1." left join lookuptypevalues on creditsuppliertransdetails.repairedItem = lookuptypevalues.id";
				$qry1 = $qry1." where date between '$frmDt' and '$toDt' and creditsuppliertransdetails.vehicleIds RLIKE '^".$values["vehicle"].",|,".$values["vehicle"].",'";
		
				// 				$qry->join("creditsuppliertransdetails","creditsuppliertransactions.id","="," creditsuppliertransdetails.creditSupplierTransId")
				// 					->join("creditsuppliers","creditsuppliers.id","=","creditsuppliertransactions.creditSupplierId")
				// 					->join("lookuptypevalues","creditsuppliertransdetails.repairedItem","=","lookuptypevalues.id");
		
		
				// 				if($values["vehicle"] != "0"){
				// 					$qry->whereIn($values["vehicle"],"creditsuppliertransdetails.vehicleIds");
				// 				}
		
				// 				$select_args = array();
				// 				$select_args[] = "creditsuppliertransdetails.vehicleIds as vehicleIds";
				// 				$select_args[] = "creditsuppliers.supplierName as creditsuppliername";
				// 				$select_args[] = "creditsuppliertransactions.transactionType as transactionType";
				// 				$select_args[] = "creditsuppliertransactions.transactionDate as transactionDate";
				// 				$select_args[] = "lookuptypevalues.name as itemdetails";
				// 				$select_args[] = "creditsuppliertransactions.amount as amount";
					
				$recs = \DB::select(DB::raw($qry1));
		
					
				$veh_arr = array();
				$vehicles = \Vehicle::all();
				foreach ($vehicles as $vehicle){
					$veh_arr[$vehicle->id] = $vehicle->veh_reg;
				}
				foreach($recs as  $rec) {
					$row = array();
						
					$veh_arr_str = "";
					$veh_arr_ids = explode(",", $rec->vehicleIds);
					foreach ($veh_arr_ids as $veh){
						if ($veh != ""){
							$veh_arr_str = $veh_arr_str.$veh_arr[$veh].",";
						}
					}
					$row["vehiclename"] = $veh_arr_str;
					$row["creditsuppliername"] = $rec->creditsuppliername;
					$row["transactiondate"] = date("d-m-Y",strtotime($rec->date));
					$row["itemdetails"] = $rec->itemdetails;
					$row["repairamount"] = $rec->amount;
					$row["paymentPaid"] = $rec->paymentPaid;
					if($rec->billNumber != ""){
						if($rec->filePath==""){
							$row["bill"] = "<span style='color:red; font-weight:bold;'>".$rec->billNumber."</span>";
						}
						else{
							$row["bill"] = "<a href='../app/storage/uploads/".$rec->filePath."' target='_blank'>".$rec->billNumber."</a>";
						}
					}
					$row["comments"] = $rec->comments;
						
					$resp[] = $row;
				}
			}
			echo json_encode($resp);
			return;
		}
		
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
		
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
		
		$form_fields = array();
		$select_args = array();
		$select_args[] = "creditsuppliers.id as id";
		$select_args[] = "creditsuppliers.supplierName as fname";
		$select_args[] = "cities.name as cname";
		
		$branches =  \CreditSupplier::leftjoin("cities","cities.id","=","creditsuppliers.cityId")->select($select_args)->get();
		$branches_arr = array();
		$branches_arr["0"] = "ALL CREDIT SUPPLIERS";
		foreach ($branches as $branch){
			$branches_arr[$branch->id] = $branch->fname." (".$branch->cname.")";
		}
		
		$supplier_rep_arr = array();
		$supplier_rep_arr['balanceSheetNoDt'] = "Credit Supplier Balance Sheet";
		$supplier_rep_arr['balanceSheet'] = "Credit Supplier Range Sheet";
		$supplier_rep_arr['payment'] = "Credit Supplier Payments";
		$supplier_rep_arr['repairs'] = "Repairs";
		$supplier_rep_arr['purchase'] = "Purchases";
		$supplier_rep_arr['vehicleReport'] = "Track By Vehicle";
		
		$form_field = array("name"=>"supplierreporttype", "content"=>"report for ", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange","script"=>"showSelectionType(this.value)"), "options"=>$supplier_rep_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		
		$add_form_fields = array();
		$vehs =  \Vehicle::All();
		$vehs_arr = array();
		foreach ($vehs as $veh){
			$vehs_arr[$veh->id] = $veh->veh_reg;
		}
		$form_field = array("name"=>"creditsupplier", "content"=>"by supplier", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$branches_arr, "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;
		$form_field = array("name"=>"vehicle", "content"=>"by vehicle", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$vehs_arr, "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;
		
		$form_info["form_fields"] = $form_fields;
		$form_info["add_form_fields"] = $add_form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.creditsupplierreport', array("values"=>$values));
	}
	
	private function getStockPurchaseReport($values){
	if (\Request::isMethod('post'))
		{
			//$values["test"];				
			if(!isset($values["fromdate"]) || !isset($values["todate"])){
					echo json_encode(array("total"=>0, "data"=>array()));
					return ;
				}
				$frmdt = date("Y-m-d",strtotime($values["fromdate"]));
				$todt = date("Y-m-d",strtotime($values["todate"]));
			if ($values["reporttype"] == "stockpurchase")
			{	
			$select_args = array();
			$select_args[] = "officebranch.name as officeBranchId";
			$select_args[] = "items.name as item";
			$select_args[] = "manufactures.name as manufacturer";
			$select_args[] = "purchased_items.qty as qty";
			$select_args[] = "purchased_items.qty as totalAmount";
			$select_args[] = "purchase_orders.created_at as created_at";
			$select_args[] = "purchase_orders.orderDate as orderDate";
			$select_args[] = "creditsuppliers.suppliername as creditSupplierId";
			$select_args[] = "employee1.fullName as incharge";
			$select_args[] = "purchase_orders.billNumber as billNumber";
			$select_args[] = "purchase_orders.status as paymentInfo";
			$select_args[] = "purchase_orders.comments as comments";
			$select_args[] = "employee.fullName as receivedBy";
			$select_args[] = "purchase_orders.id as id";
			$select_args[] = "purchase_orders.amountPaid as amountPaid";
			$select_args[] = "purchase_orders.paymentType as paymentType";
			$select_args[] = "employee.fullName as receivedBy";
			$select_args[] = "purchased_items.unitPrice as unitPrice";
			$select_args[] = "purchase_orders.filePath as filePath";
			$select_args[] = "purchase_orders.createdBy as createdBy";
			$select_args[] = "creditsuppliers.id as id";
			$select_args[] = "creditsuppliers.supplierName as fname";
			//$select_args[] = "cities.name as cname";
			$resp = array();
			$warehouses = \OfficeBranch::where("isWarehouse","=","Yes")->get();
				$warehouse_arr = array();
				foreach ($warehouses as $warehouse){
					$warehouse_arr[$warehouse->id] = $warehouse->name;
				}
				
				
			$entities = \PurchasedItems::where("purchased_items.status","=","ACTIVE")
										->where("purchase_orders.type","=","PURCHASE ORDER")
										->where("purchase_orders.officeBranchId", "=", $values["warehouse"])
										->whereBetween("purchase_orders.orderDate",array($frmdt,$todt))
										->leftjoin("purchase_orders","purchase_orders.id","=","purchased_items.purchasedOrderId")
										->leftjoin("items","items.id","=","purchased_items.itemId")
										->leftjoin("manufactures","manufactures.id","=","purchased_items.manufacturerId")
										->leftjoin("officebranch","officebranch.id","=","purchase_orders.officeBranchId")
										->leftjoin("creditsuppliers","creditsuppliers.id","=","purchase_orders.creditSupplierId")
										->leftjoin("employee","employee.id","=","purchase_orders.createdBy")
										->leftjoin("employee as employee1","employee1.id","=","purchase_orders.inchargeId")
										->select($select_args)->orderBy("purchase_orders.orderDate","desc")->get();
			//$entities = $entities->toArray();
			foreach ($entities as $entity){
				$row = array();
				$row["warehouse"] = $entity->officeBranchId;			
				$row["itemname"] = $entity->item;
				$row["manufacturer"] = $entity->manufacturer;
				$row["purchasedQty"] = $entity->qty;
				$row["totalamt"] = sprintf('%0.2f',$entity->qty*$entity->unitPrice);
				$row["entrydate"] = date("d-m-Y",strtotime($entity->created_at));
				$row["purchasedDate"] = date("d-m-Y",strtotime($entity->orderDate));
				$row["purchasedfrom"] = $entity->fname;
				$row["incharge"] = $entity->incharge;
				$row["billNumber"] = $entity->billNumber;
				$row["paymentInfo"] = "Amount Paid : ".$entity->amountPaid."<br/>"."Payment Type : ".$entity->paymentType;
				$row["comments"] = $entity->comments;
				$row["createdBy"] = $entity->receivedBy;
				$resp[] = $row;
			}
			
			}
			echo json_encode($resp);
			return;
		}

		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';;
		$theads = array('Warehouse','Item Name', "Manufacturer", "Quantity", "Amount","entry date", "Purchased Date", "Purchased From", "Incharge", "BillNo", "payment info", "comments", "Created By");
		$values["theads"] = $theads;
		//$values["test"];

		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];

		$warehouses = \OfficeBranch::where("isWarehouse","=","Yes")->get();
		$warehouse_arr = array();
		foreach ($warehouses as $warehouse){
		$warehouse_arr[$warehouse->id] = $warehouse->name;
		}	
		$form_field = array("name"=>"warehouse", "content"=>"report for ", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>$warehouse_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;

		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "stockpurchase";
		return View::make('reports.stockpurchase', array("values"=>$values));
	
	}
	
	
	private function getVehicleStockHistoryReport($values){
		if (\Request::isMethod('post'))
		{
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			if($values["reporttype"] == "vehiclestockhistory"){
				$select_args = array();
				$select_args[] = "vehicle.veh_reg as veh_reg";
				$select_args[] = "officebranch.name as officebranch";
				$select_args[] = "items.name as item";
				$select_args[] = "manufactures.name as manufacturer";
				$select_args[] = "purchased_items.qty as qty";
				$select_args[] = "purchased_items.qty as totalAmount";
				$select_args[] = "inventory_transaction.date as transactiondate";
				$select_args[] = "purchase_orders.orderDate as orderDate";
				$select_args[] = "creditsuppliers.suppliername as creditSupplierId";
				$select_args[] = "purchase_orders.billNumber as billNumber";
				$select_args[] = "purchase_orders.status as paymentInfo";
				$select_args[] = "inventory_transaction.remarks as comments";
				$select_args[] = "purchase_orders.id as id";
				$select_args[] = "purchase_orders.amountPaid as amountPaid";
				$select_args[] = "purchase_orders.paymentType as paymentType";
				$select_args[] = "employee.fullName as createdBy";
				$select_args[] = "purchased_items.unitPrice as unitPrice";
				$select_args[] = "purchase_orders.filePath as filePath";
				$select_args[] = "depots.name as depotName";
				$select_args[] = "officebranch.id as branchId";
				$select_args[] = "purchase_orders.paymentType as paymentType";
				$select_args[] = "purchase_orders.chequeNumber as chequeNumber";
				$select_args[] = "purchase_orders.bankAccount as bankAccountId";
				$select_args[] = "inventory_transaction.fromWareHouseId as fromWareHouseId";
				if($values["vehicle"] != 0){
					$recs = \InventoryTransactions::where("toVehicleId","=",$values["vehicle"])
									->where("inventory_transaction.status","=","ACTIVE")
									->whereBetween("inventory_transaction.date",array($frmDt,$toDt))
									->leftjoin("purchased_items","purchased_items.id","=","inventory_transaction.stockItemId")
									->leftjoin("purchase_orders","purchase_orders.id","=","purchased_items.purchasedOrderId")
									->leftjoin("items","items.id","=","purchased_items.itemId")
									->leftjoin("vehicle","vehicle.id","=","inventory_transaction.toVehicleId")
									->leftjoin("manufactures","manufactures.id","=","purchased_items.manufacturerId")
									->leftjoin("officebranch","officebranch.id","=","inventory_transaction.fromWareHouseId")
									->leftjoin("depots","depots.id","=","inventory_transaction.fromWareHouseId")
									->leftjoin("creditsuppliers","creditsuppliers.id","=","purchase_orders.creditSupplierId")
									->leftjoin("employee","employee.id","=","purchase_orders.createdBy")
									->select($select_args)->orderBy("inventory_transaction.date","desc")->get();
								
					foreach ($recs as $rec){
						$row = array();
						$row["veh_reg"] = $rec->veh_reg;
						$row["officebranch"] = $rec->officebranch;
						if($rec->fromWareHouseId>999){
							$row["officebranch"] = $rec->depotName;
						}
						$row["item"] = $rec->item;
						$row["manufacturer"] = $rec->manufacturer;
						$row["qty"] = $rec->qty;
						$row["totalAmount"] = $rec->totalAmount;
						$row["transactiondate"] = date("d-m-Y",strtotime($rec->transactiondate));
						$row["orderDate"] = date("d-m-Y",strtotime($rec->orderDate));
						$row["creditSupplierId"] = $rec->creditSupplierId;
						$row["billNumber"] = $rec->billNumber;
						$row["pmtinfo"] = "PAYMENT TYPE : ".strtoupper($rec->paymentType)."</br>";
						if($rec->paymentType == "debit_card" || $rec->paymentType == "credit_card"){
							$card = \Cards::where("id","=",$rec->bankAccountId)->get();
							if(count($card)>0){
								$card = $card[0];
								$row["pmtinfo"] = $row["pmtinfo"]."CARD NO : ".$card->cardNumber."<br/>";
							}
						}
						if($rec->chequeNumber!=""){
							$row["pmtinfo"] = $row["pmtinfo"]."REF NUM : ".$rec->chequeNumber;
						}
						if($rec->paymentType=="cheque_credit" || $rec->paymentType=="cheque_debit"){
							$row["pmtinfo"] = $row["pmtinfo"]."CHQUE NUM : ".$rec->chequeNumber;
						}
						$row["comments"] = $rec->comments;
						$row["createdby"] = $rec->createdBy;
						$resp[] = $row;
					}
				}
			echo json_encode($resp);
			return;
			}
		}
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
		
		
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
		
		$vehicles = \Vehicle::All();
		$vehicles_arr = array();
		foreach ($vehicles as $vehicle){
			$vehicles_arr[$vehicle->id] = $vehicle->veh_reg;
		}
		$form_field = array("name"=>"vehicle", "content"=>"report for ", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>$vehicles_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.vehiclestockhistoryreport', array("values"=>$values));
	}
	
	private function getRepairStockReport($values){
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Warehouse','Item Name', "Manufacturer", "Quantity", "Amouont", "Transaction Date",  "Repair To", "Incharge", "BillNo", "payment info", "comments", "Created By");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "creditsuppliers.id as id";
		$select_args[] = "creditsuppliers.supplierName as fname";
		$select_args[] = "cities.name as cname";
	
		$suppliers = \CreditSupplier::All();
		$suppliers_arr = array();
		foreach ($suppliers as $supplier){
			$suppliers_arr[$supplier->id] = $supplier->supplierName;
		}
		$items = \Items::All();
		$items_arr = array();
		$items_arr[0] = "All";
		foreach ($items as $item){
			$items_arr[$item->id] = $item->name;
		}
		$form_field = array("name"=>"creditsupplier", "content"=>"report for ", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>$suppliers_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"item", "content"=>"item ", "readonly"=>"",  "required"=>"required","type"=>"select",  "options"=>$items_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "repairstock";
		return View::make('reports.reportsdatatable', array("values"=>$values));
	}
	
	private function getInventoryReport($values){
		if (\Request::isMethod('post'))
		{
			//$values["test"];
			$resp = array();
			$select_args = array();
			$select_args[] = "officebranch.name as officeBranchId";
			$select_args[] = "items.name as item";
			$select_args[] = "manufactures.name as manufacturer";
			$select_args[] = "purchased_items.qty as qty";
			$select_args[] = "purchased_items.purchasedQty as purchasedQty";
			$select_args[] = "purchase_orders.orderDate as orderDate";
			$select_args[] = "creditsuppliers.suppliername as creditSupplierId";
			$select_args[] = "employee1.fullName as incharge";
			$select_args[] = "purchase_orders.billNumber as billNumber";
			$select_args[] = "purchase_orders.status as paymentInfo";
			$select_args[] = "purchase_orders.comments as comments";
			$select_args[] = "employee.fullName as receivedBy";
			$select_args[] = "purchase_orders.id as id";
			$select_args[] = "purchase_orders.type as ptype";
			$select_args[] = "purchase_orders.amountPaid as amountPaid";
			$select_args[] = "purchase_orders.paymentType as paymentType";
			$select_args[] = "employee.fullName as receivedBy";
			$select_args[] = "purchased_items.unitPrice as unitPrice";
			$select_args[] = "purchase_orders.filePath as filePath";
			$select_args[] = "depots.name as depotName";
			$select_args[] = "officebranch.id as branchId";
			if(isset($values["inventoryreporttype"])){
				if($values["inventoryreporttype"] == "find_available_items" ){
					$query = \PurchasedItems::where("purchased_items.status","=","ACTIVE")
								->whereIn("purchase_orders.type",array("PURCHASE ORDER","TO WAREHOUSE"))
								->where("items.stockType","=","NON OFFICE")
								->where("purchase_orders.workFlowStatus","=","Approved")
								->where("purchase_orders.status","=","ACTIVE");
					if($values["warehouse"]>0 && $values["item"]>0){
						$query->where("purchase_orders.officeBranchId","=",$values["warehouse"])
							  ->where("items.id","=",$values["item"]);
					}
					if($values["warehouse"]>0 && ($values["item"]==0 || $values["item"]=="")){
						$query->where("purchase_orders.officeBranchId","=",$values["warehouse"]);
					}
					
					if($values["warehouse"]==0 && $values["item"]>0){
						$query->where("items.id","=",$values["item"]);
					}
					$query->leftjoin("purchase_orders","purchase_orders.id","=","purchased_items.purchasedOrderId")
						->leftjoin("items","items.id","=","purchased_items.itemId")
						->leftjoin("manufactures","manufactures.id","=","purchased_items.manufacturerId")
						->leftjoin("officebranch","officebranch.id","=","purchase_orders.officeBranchId")
						->leftjoin("depots","depots.id","=","purchase_orders.officeBranchId")
						->leftjoin("creditsuppliers","creditsuppliers.id","=","purchase_orders.creditSupplierId")
						->leftjoin("employee","employee.id","=","purchase_orders.createdBy")
						->leftjoin("employee as employee1","employee1.id","=","purchase_orders.inchargeId");
					$entities = $query->select($select_args)->orderBy("purchase_orders.orderDate","desc")->get();
					foreach ($entities as $entity){
						$row = array();
						$row["officeBranchId"] = $entity->officeBranchId;
						if($entity->officeBranchId == "" || $entity->officeBranchId == "0" || $entity->officeBranchId == "null"){
							$row["officeBranchId"] = $entity->depotName;
						}
						$row["item"] = $entity->item;
						$row["qty"] = $entity->qty;
						$row["manufacturer"] = $entity->manufacturer;
						$row["orderDate"] = date("d-m-Y",strtotime($entity->orderDate));
						$row["orderqty"] = $entity->purchasedQty;
						if($entity->ptype=="TO WAREHOUSE"){
							$row["orderqty"] = $row["orderqty"]." (MOVED STOCK)";
						}
						$row["billNumber"] = $entity->billNumber;
						if($entity->filePath != ""){
							$row["billNumber"] = "<a target='_blank' href='../app/storage/uploads/".$entity->filePath."'>".$entity->billNumber."</a>";
						}
						$row["creditSupplierId"] = $entity->creditSupplierId;
						$resp[] = $row;
					}
				}
				if(isset($values["inventoryreporttype"]) && $values["inventoryreporttype"] == "history"){
					$select_args = array();
					$select_args[] = "officebranch.name as officebranch";
					$select_args[] = "items.name as item";
					$select_args[] = "manufactures.name as manufacturer";
					$select_args[] = "inventory_transaction.qty as qty";
					$select_args[] = "purchased_items.purchasedQty as purchasedQty";
					$select_args[] = "inventory_transaction.date as transactionDate";
					$select_args[] = "inventory_transaction.action as transactiontype";
					$select_args[] = "inventory_transaction.fromWareHouseId as fromWareHouseId";
					$select_args[] = "officebranch1.name as toWareHouseId";
					$select_args[] = "vehicle1.veh_reg as veh_reg1";
					$select_args[] = "inventory_transaction.fromActionId as fromActionId";
					$select_args[] = "inventory_transaction.toActionId as toActionId";
					$select_args[] = "inventory_transaction.remarks as remarks";
					$select_args[] = "purchase_orders.orderDate as orderDate";
					$select_args[] = "creditsuppliers.suppliername as creditSupplierId";
					$select_args[] = "purchase_orders.billNumber as billNumber";
					$select_args[] = "inventory_transaction.id as id";
					$select_args[] = "purchase_orders.amountPaid as amountPaid";
					$select_args[] = "purchase_orders.paymentType as paymentType";
					$select_args[] = "employee.fullName as receivedBy";
					$select_args[] = "purchased_items.unitPrice as unitPrice";
					$select_args[] = "purchase_orders.filePath as filePath";
					$select_args[] = "purchase_orders.type as ptype";
					$select_args[] = "vehicle.veh_reg as veh_reg";
					$select_args[] = "depots.name as depotName";
					$select_args[] = "depots1.name as depot1Name";
					$select_args[] = "officebranch.id as branchId";
					if($values["inventoryreporttype"] == "history" ){
						$fromdt = date("Y-m-d",strtotime($values['fromdate']));
						$todt = date("Y-m-d",strtotime($values['todate']));
						$query = \InventoryTransactions::where("inventory_transaction.status","=","ACTIVE")
										->where("purchase_orders.status","=","ACTIVE")
										->where("items.stockType","=","NON OFFICE")
										->whereBetween("inventory_transaction.date",array($fromdt,$todt));
						if($values["warehouse"]>0 && $values["warehouse"]<999){
							$query->whereRaw(" (officebranch.id=".$values["warehouse"]." or officebranch1.id=".$values["warehouse"].") ");
						}
						if($values["warehouse"]>999){
							$query->whereRaw("(depots.id=".$values["warehouse"]." or depots1.id=".$values["warehouse"].") ");
						}
						if($values["item"]>0){
							$query->where("items.id","=",$values["item"]);
						}
						$query->leftjoin("purchased_items","purchased_items.id","=","inventory_transaction.stockItemId")
									->leftjoin("purchase_orders","purchase_orders.id","=","purchased_items.purchasedOrderId")
									->leftjoin("items","items.id","=","purchased_items.itemId")
									->leftjoin("vehicle","vehicle.id","=","inventory_transaction.toVehicleId")
									->leftjoin("vehicle as vehicle1","vehicle1.id","=","inventory_transaction.fromVehicleId")
									->leftjoin("manufactures","manufactures.id","=","purchased_items.manufacturerId")
									->leftjoin("officebranch","officebranch.id","=","inventory_transaction.fromWareHouseId")
									->leftjoin("officebranch as officebranch1","officebranch1.id","=","inventory_transaction.toWareHouseId")
									->leftjoin("depots","depots.id","=","inventory_transaction.fromWareHouseId")
									->leftjoin("depots as depots1","depots1.id","=","inventory_transaction.toWareHouseId")
									->leftjoin("creditsuppliers","creditsuppliers.id","=","purchase_orders.creditSupplierId")
									->leftjoin("employee","employee.id","=","purchase_orders.createdBy");
						$entities = $query->select($select_args)->orderBy("inventory_transaction.id","asc")->get();
						foreach ($entities as $entity){
							$row = array();
							$row["officeBranchId"] = $entity->officebranch;
							if($entity->officebranch == "" || $entity->officebranch == "0" || $entity->officebranch == "null"){
								$row["officeBranchId"] = $entity->depotName;
							}
							if($entity->depot1Name != ""){
								$entity->toWareHouseId = $entity->depot1Name;
							}
							
							$row["item"] = $entity->item;
							$row["qty"] = $entity->qty;
							$row["manufacturer"] = $entity->manufacturer;
							$row["transactionDate"] = date("d-m-Y",strtotime($entity->transactionDate));
							$transdetails = "";
							if($entity->transactiontype=="itemtovehicles"){
								$transdetails = $transdetails."Transaction Type : Items<br/> To Vehicle : ".$entity->veh_reg;
							}
							if($entity->transactiontype=="vehicletovehicle"){
								$transdetails = $transdetails."Transaction Type : Vehicle To Vehicle <br/>".$entity->veh_reg1." To ". $entity->veh_reg;
							}
							if($entity->transactiontype=="warehousetowarehouse"){
								$transdetails = $transdetails."Transaction Type : Warehouse To Warehouse <br/>".$entity->officebranch." To ". $entity->toWareHouseId;
							}
							$row["transinfo"] = $transdetails;
							//$row["orderDate"] = date("d-m-Y",strtotime($entity->orderDate));
							$row["orderqty"] = $entity->purchasedQty;
							if($entity->ptype=="TO WAREHOUSE"){
								$row["orderqty"] = $row["orderqty"]." (MOVED STOCK)";
							}
							$row["creditSupplierId"] = $entity->creditSupplierId;
							$row["billNumber"] = $entity->billNumber;
							if($entity->filePath != ""){
								$row["billNumber"] = "<a target='_blank' href='../app/storage/uploads/".$entity->filePath."'>".$entity->billNumber."</a>";
							}
							$row["remarks"] = $entity->remarks;
							$resp[] = $row;
						}
					}
				}
			}
			echo json_encode($resp);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$warehouse_arr_total = array();
		$warehouse_arr = array();
		$warehouses = \OfficeBranch::where("isWareHouse","=","Yes")->get();
		foreach ($warehouses as $warehouse){
			$warehouse_arr[$warehouse->id] = $warehouse->name;
		}
		$warehouse_arr_total["main warehouses"] = $warehouse_arr;
		foreach ($warehouses as $warehouse){
			$warehouse_arr = array();
			$sub_warehouses = \Depot::where("status","=","ACTIVE")
								->where("ParentWarehouse","=",$warehouse->id)->get();
			foreach ($sub_warehouses as $sub_warehouse){
				$warehouse_arr[$sub_warehouse->id] = $sub_warehouse->name."(".$sub_warehouse->code.")";
			}
			$warehouse_arr_total[$warehouse->name] = $warehouse_arr;
		}
		
		$items = \Items::where("stockType","=","NON OFFICE")->get();
		$items_arr = array();
		$items_arr[0] = "All";
		foreach ($items as $item){
			$items_arr[$item->id] = $item->name;
		}
		$report_arr = array();
		$report_arr['find_available_items'] = "FIND AVAILABLE ITEMS";
		$report_arr['history'] = "history";
		$form_field = array("name"=>"inventoryreporttype", "content"=>"report for ", "readonly"=>"",  "required"=>"required","type"=>"select", "action"=>array("type"=>"onChange","script"=>"showSelectionType(this.value)"), "options"=>$report_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"warehouse", "id"=>"warehouse", "content"=>"warehouse ", "readonly"=>"",  "required"=>"required","type"=>"selectgroup",  "options"=>$warehouse_arr_total, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"item", "content"=>"item ", "readonly"=>"",  "required"=>"","type"=>"select",  "options"=>$items_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		
	
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "dailysettlement";
		return View::make('reports.inventoryreport', array("values"=>$values));
	}
	private function getOfficeInventoryReport($values){
		if (\Request::isMethod('post'))
		{
			$resp = array();
			$select_args = array();
			$select_args[] = "officebranch.name as officeBranchId";
			$select_args[] = "items.name as item";
			$select_args[] = "manufactures.name as manufacturer";
			$select_args[] = "purchased_items.qty as qty";
			$select_args[] = "purchased_items.purchasedQty as purchasedQty";
			$select_args[] = "purchase_orders.orderDate as orderDate";
			$select_args[] = "creditsuppliers.suppliername as creditSupplierId";
			$select_args[] = "employee1.fullName as incharge";
			$select_args[] = "purchase_orders.billNumber as billNumber";
			$select_args[] = "purchase_orders.status as paymentInfo";
			$select_args[] = "purchase_orders.comments as comments";
			$select_args[] = "employee.fullName as receivedBy";
			$select_args[] = "purchase_orders.id as id";
			$select_args[] = "purchase_orders.amountPaid as amountPaid";
			$select_args[] = "purchase_orders.paymentType as paymentType";
			$select_args[] = "employee.fullName as receivedBy";
			$select_args[] = "purchased_items.unitPrice as unitPrice";
			$select_args[] = "purchase_orders.filePath as filePath";
			$select_args[] = "depots.name as depotName";
			$select_args[] = "officebranch.id as branchId";
			$select_args[] = "purchase_orders.type as type";
			if(isset($values["officeinventoryreporttype"])){
				if($values["officeinventoryreporttype"] == "find_available_items" ){
					$query = \PurchasedItems::where("purchased_items.status","=","ACTIVE")
												->whereIn("purchase_orders.type",array("OFFICE PURCHASE ORDER","TO OFFICE WAREHOUSE"))
												->where("items.stockType","=","OFFICE")
												->where("purchase_orders.status","=","ACTIVE");
					if($values["warehouse"]>0 && $values["item"]>0){
						$query->where("purchase_orders.officeBranchId","=",$values["warehouse"])
							  ->where("items.id","=",$values["item"]);
					}
					if($values["warehouse"]>0 && ($values["item"]==0 || $values["item"]=="")){
						$query->where("purchase_orders.officeBranchId","=",$values["warehouse"]);
					}
					if($values["warehouse"]==0 && $values["item"]>0){
						$query->where("items.id","=",$values["item"]);
					}
					$query->leftjoin("purchase_orders","purchase_orders.id","=","purchased_items.purchasedOrderId")
							->leftjoin("items","items.id","=","purchased_items.itemId")
							->leftjoin("manufactures","manufactures.id","=","purchased_items.manufacturerId")
							->leftjoin("officebranch","officebranch.id","=","purchase_orders.officeBranchId")
							->leftjoin("depots","depots.id","=","purchase_orders.officeBranchId")
							->leftjoin("creditsuppliers","creditsuppliers.id","=","purchase_orders.creditSupplierId")
							->leftjoin("employee","employee.id","=","purchase_orders.createdBy")
							->leftjoin("employee as employee1","employee1.id","=","purchase_orders.inchargeId");
					$entities = $query->select($select_args)->orderBy("purchase_orders.orderDate","desc")->get();
					foreach ($entities as $entity){
						$row = array();
						$row["officeBranchId"] = $entity->officeBranchId;
						if($entity->officeBranchId == "" || $entity->officeBranchId == "0" || $entity->officeBranchId == "null"){
							$row["officeBranchId"] = $entity->depotName;
						}
						$row["item"] = $entity->item;
						$row["qty"] = $entity->qty;
						$row["manufacturer"] = $entity->manufacturer;
						$row["orderDate"] = date("d-m-Y",strtotime($entity->orderDate));
						$row["orderqty"] = $entity->purchasedQty;
						if($entity->type=="TO OFFICE WAREHOUSE"){
							$row["orderqty"] = $row["orderqty"]." (MOVED STOCK)";
						}
						$row["billNumber"] = $entity->billNumber;
						if($entity->filePath != ""){
							$row["billNumber"] = "<a target='_blank' href='../app/storage/uploads/".$entity->filePath."'>".$entity->billNumber."</a>";
						}
						$row["creditSupplierId"] = $entity->creditSupplierId;
						$resp[] = $row;
					}
				}
				if(isset($values["officeinventoryreporttype"]) && $values["officeinventoryreporttype"] == "history"){
					$select_args = array();
					$select_args[] = "officebranch.name as officebranch";
					$select_args[] = "items.name as item";
					$select_args[] = "manufactures.name as manufacturer";
					$select_args[] = "inventory_transaction.qty as qty";
					$select_args[] = "purchased_items.purchasedQty as purchasedQty";
					$select_args[] = "inventory_transaction.date as transactionDate";
					$select_args[] = "inventory_transaction.action as transactiontype";
					$select_args[] = "inventory_transaction.fromWareHouseId as fromWareHouseId";
					$select_args[] = "officebranch1.name as toWareHouseId";
					$select_args[] = "vehicle1.veh_reg as veh_reg1";
					$select_args[] = "inventory_transaction.fromActionId as fromActionId";
					$select_args[] = "inventory_transaction.toActionId as toActionId";
					$select_args[] = "inventory_transaction.remarks as remarks";
					$select_args[] = "purchase_orders.orderDate as orderDate";
					$select_args[] = "creditsuppliers.suppliername as creditSupplierId";
					$select_args[] = "purchase_orders.billNumber as billNumber";
					$select_args[] = "purchase_orders.id as id";
					$select_args[] = "purchase_orders.amountPaid as amountPaid";
					$select_args[] = "purchase_orders.type as ptype";
					$select_args[] = "purchase_orders.paymentType as paymentType";
					$select_args[] = "employee.fullName as receivedBy";
					$select_args[] = "purchased_items.unitPrice as unitPrice";
					$select_args[] = "purchase_orders.filePath as filePath";
					$select_args[] = "vehicle.veh_reg as veh_reg";
					$select_args[] = "depots.name as depotName";
					$select_args[] = "depots1.name as depot1Name";
					$select_args[] = "officebranch.id as branchId";
					if($values["officeinventoryreporttype"] == "history" ){
						$fromdt = date("Y-m-d",strtotime($values['fromdate']));
						$todt = date("Y-m-d",strtotime($values['todate']));
						$query = \InventoryTransactions::where("inventory_transaction.status","=","ACTIVE")
						->where("purchase_orders.status","=","ACTIVE")
						->where("items.stockType","=","OFFICE")
						->whereBetween("inventory_transaction.date",array($fromdt,$todt));
						if($values["warehouse"]>0 && $values["warehouse"]<999){
							$query->where("officebranch.id","=",$values["warehouse"]);
						}
						if($values["warehouse"]>999){
							$query->where("depots.id","=",$values["warehouse"]);
						}
						if($values["item"]>0){
							$query->where("items.id","=",$values["item"]);
						}
						$query->leftjoin("purchased_items","purchased_items.id","=","inventory_transaction.stockItemId")
						->leftjoin("purchase_orders","purchase_orders.id","=","purchased_items.purchasedOrderId")
						->leftjoin("items","items.id","=","purchased_items.itemId")
						->leftjoin("vehicle","vehicle.id","=","inventory_transaction.toVehicleId")
						->leftjoin("vehicle as vehicle1","vehicle1.id","=","inventory_transaction.fromVehicleId")
						->leftjoin("manufactures","manufactures.id","=","purchased_items.manufacturerId")
						->leftjoin("officebranch","officebranch.id","=","inventory_transaction.fromWareHouseId")
						->leftjoin("officebranch as officebranch1","officebranch1.id","=","inventory_transaction.toWareHouseId")
						->leftjoin("depots","depots.id","=","inventory_transaction.fromWareHouseId")
						->leftjoin("depots as depots1","depots1.id","=","inventory_transaction.toWareHouseId")
						->leftjoin("creditsuppliers","creditsuppliers.id","=","purchase_orders.creditSupplierId")
						->leftjoin("employee","employee.id","=","purchase_orders.createdBy");
						$entities = $query->select($select_args)->orderBy("inventory_transaction.date","desc")->get();
						foreach ($entities as $entity){
							$row = array();
							$row["officeBranchId"] = $entity->officebranch;
							if($entity->officebranch == "" || $entity->officebranch == "0" || $entity->officebranch == "null"){
								$row["officeBranchId"] = $entity->depotName;
							}
							if($entity->depot1Name != ""){
								$entity->toWareHouseId = $entity->depot1Name;
							}
								
							$row["item"] = $entity->item;
							$row["qty"] = $entity->qty;
							$row["manufacturer"] = $entity->manufacturer;
							$row["transactionDate"] = date("d-m-Y",strtotime($entity->transactionDate));
							$transdetails = "";
							if($entity->transactiontype=="itemtovehicles"){
								$transdetails = $transdetails."Transaction Type : Items<br/> To Vehicle : ".$entity->veh_reg;
							}
							if($entity->transactiontype=="vehicletovehicle"){
								$transdetails = $transdetails."Transaction Type : Vehicle To Vehicle <br/>".$entity->veh_reg1." To ". $entity->veh_reg;
							}
							if($entity->transactiontype=="warehousetowarehouse"){
								$transdetails = $transdetails."Transaction Type : Warehouse To Warehouse <br/>".$entity->officebranch." To ". $entity->toWareHouseId;
							}
							$row["transinfo"] = $transdetails;
							$row["orderDate"] = date("d-m-Y",strtotime($entity->orderDate));
							$row["orderqty"] = $entity->purchasedQty;
							if($entity->ptype=="TO OFFICE WAREHOUSE"){
								$row["orderqty"] = $row["orderqty"]." (MOVED STOCK)";
							}
							$row["creditSupplierId"] = $entity->creditSupplierId;
							$row["billNumber"] = $entity->billNumber;
							if($entity->filePath != ""){
								$row["billNumber"] = "<a target='_blank' href='../app/storage/uploads/".$entity->filePath."'>".$entity->veh_reg."</a>";
							}
							$row["remarks"] = $entity->remarks;
							$resp[] = $row;
								
						}
					}
				}
			}
			echo json_encode($resp);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "fuelstationdetails.id as id";
		$select_args[] = "fuelstationdetails.name as fname";
		$select_args[] = "cities.name as cname";
	
		$warehouse_arr_total = array();
		$warehouse_arr = array();
		$warehouses = \OfficeBranch::where("isWareHouse","=","Yes")->get();
		foreach ($warehouses as $warehouse){
			$warehouse_arr[$warehouse->id] = $warehouse->name;
		}
		$warehouse_arr_total["main warehouses"] = $warehouse_arr;
		foreach ($warehouses as $warehouse){
			$warehouse_arr = array();
			$sub_warehouses = \Depot::where("status","=","ACTIVE")
			->where("ParentWarehouse","=",$warehouse->id)->get();
			foreach ($sub_warehouses as $sub_warehouse){
				$warehouse_arr[$sub_warehouse->id] = $sub_warehouse->name."(".$sub_warehouse->code.")";
			}
			$warehouse_arr_total[$warehouse->name] = $warehouse_arr;
		}
	
		$items = \Items::where("stockType","=","OFFICE")->get();
		$items_arr = array();
		$items_arr[0] = "All";
		foreach ($items as $item){
			$items_arr[$item->id] = $item->name;
		}
		$report_arr = array();
		$report_arr['find_available_items'] = "FIND AVAILABLE ITEMS";
		$report_arr['history'] = "history";
		$form_field = array("name"=>"officeinventoryreporttype", "content"=>"report for ", "readonly"=>"",  "required"=>"required","type"=>"select", "action"=>array("type"=>"onChange","script"=>"showSelectionType(this.value)"), "options"=>$report_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"warehouse", "id"=>"warehouse", "content"=>"warehouse ", "readonly"=>"",  "required"=>"required","type"=>"selectgroup",  "options"=>$warehouse_arr_total, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"item", "content"=>"item ", "readonly"=>"",  "required"=>"","type"=>"select",  "options"=>$items_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$form_info["form_fields"] = $form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "dailysettlement";
		return View::make('reports.officeinventoryreport', array("values"=>$values));
	}
	
	private function getLoginLogInfo($values)
	{
		if (\Request::isMethod('post'))
		{
			//$values["test"];
			$select_args = array();
			$select_args[] = "login_log.empid as empid";
			$select_args[] = "login_log.user_full_name as user_full_name";
			$select_args[] = "login_log.ipaddress as ipaddress";
			$select_args[] = "login_log.logindate as logindate";
			$select_args[] = "login_log.logintime as logintime";
			$select_args[] = "login_log.logouttime as logouttime";
			
			if(!isset($values["fromdate"]) || !isset($values["todate"])){
				echo json_encode(array("total"=>0, "data"=>array()));
				return ;
			}
			
			$frmdt = date("Y-m-d",strtotime($values["fromdate"]));
			$todt = date("Y-m-d",strtotime($values["todate"]));
			$resp = array();
			if(isset($values["empname"]) && $values["empname"] == 0){
				$entities = \LoginLog::whereBetween("logindate",array($frmdt,$todt))->get();
				$total = \LoginLog::wherebetween("logindate",array($frmdt,$todt))->count();
			}
			elseif (isset($values["empname"]) && $values["empname"] > 0){
				$entities = \LoginLog::wherebetween("logindate",array($frmdt,$todt))->where("user_id","=",$values["empname"])->select($select_args)->get();
				$total = \LoginLog::wherebetween("logindate",array($frmdt,$todt))->where("user_id","=",$values["empname"])->count();
			}
			foreach ($entities as $entity){
				$row = array();
				$row["empid"] = $entity->empid	;
				$row["user_full_name"] = $entity->user_full_name;
				$row["ipaddress"] = $entity->ipaddress;
				$row["logindate"] = date("d-m-Y",strtotime($entity->logindate));
				$row["logintime"] = $entity->logintime;
				$row["logouttime"] = $entity->logouttime;
				$resp[] = $row;
			}
			echo json_encode($resp);
			return;
			
		}
		$values = Input::all();
		$values['bredcum'] = "USER LOGIN INFORMATION";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads = array('user name','email', "IP Address", "login date", "login time", "logout time");
		$values["theads"] = $theads;
	
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "loginlog";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
	
		$form_fields = array();
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"empname", "content"=>"empname", "readonly"=>"", "required"=>"", "type"=>"select", "options"=>$emp_arr,  "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.logininforeport', array("values"=>$values));
	}
	
	private function getEmployeeMainLoginLogInfo($values)
	{
		if (\Request::isMethod('post'))
		{
			//$values["test"];
			$select_args = array();
			$select_args[] = "login_log.empid as empid";
			$select_args[] = "login_log.user_full_name as user_full_name";
			$select_args[] = "login_log.ipaddress as ipaddress";
			$select_args[] = "login_log.logindate as logindate";
			$select_args[] = "login_log.logintime as logintime";
			$select_args[] = "login_log.logouttime as logouttime";
				
			if(!isset($values["fromdate"]) || !isset($values["todate"])){
				echo json_encode(array("total"=>0, "data"=>array()));
				return ;
			}
				
			$frmdt = date("Y-m-d",strtotime($values["fromdate"]));
			$todt = date("Y-m-d",strtotime($values["todate"]));
			$resp = array();
			if(isset($values["empname"]) && $values["empname"] == 0){
				$entities = \LoginLog::whereBetween("logindate",array($frmdt,$todt))->get();
				$total = \LoginLog::wherebetween("logindate",array($frmdt,$todt))->count();
			}
			elseif (isset($values["empname"]) && $values["empname"] > 0){
				$entities = \LoginLog::wherebetween("logindate",array($frmdt,$todt))->where("user_id","=",$values["empname"])->select($select_args)->get();
				$total = \LoginLog::wherebetween("logindate",array($frmdt,$todt))->where("user_id","=",$values["empname"])->count();
			}
			foreach ($entities as $entity){
				$row = array();
				$row["empid"] = $entity->empid	;
				$row["user_full_name"] = $entity->user_full_name;
				$row["ipaddress"] = $entity->ipaddress;
				$row["logindate"] = date("d-m-Y",strtotime($entity->logindate));
				$row["logintime"] = $entity->logintime;
				$row["logouttime"] = $entity->logouttime;
				$resp[] = $row;
			}
			echo json_encode($resp);
			return;
				
		}
		$values = Input::all();
		$values['bredcum'] = "USER LOGIN INFORMATION";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads = array('user name','email', "IP Address", "login date", "login time", "logout time");
		$values["theads"] = $theads;
	
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "loginlog";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
		
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
		
		$form_fields = array();
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"getFormData(this.value);"), "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"empname", "content"=>"empname", "readonly"=>"", "required"=>"", "type"=>"select", "options"=>$emp_arr,  "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.employeemainlogininforeport', array("values"=>$values));
	}
	
	private function getVehicleMileage($values)
	{
		if (\Request::isMethod('post'))
		{
			//$values["test"];
			$select_args = array();
			$select_args[] = "vehicle.veh_reg as veh_reg";
			$select_args[] = "lookuptypevalues.name as vehicle_type";
			$select_args[] = "vehicle.yearof_pur as yearof_pur";
			$select_args[] = "fueltransactions.filledDate as startDate";
			$select_args[] = "fueltransactions.filledDate as endDate";
			$select_args[] = "fueltransactions.startReading as startReading";
			$select_args[] = "fueltransactions.litres as litres";
			$select_args[] = "fueltransactions.fullTank as fullTank";
			$select_args[] = "fueltransactions.mileage as mileage";
			$select_args[] = "fueltransactions.remarks as remarks";
			$select_args[] = "fueltransactions.vehicleId as vehicleId";
			if(!isset($values["fromdate"]) || !isset($values["todate"])){
				echo json_encode(array("total"=>0, "data"=>array()));
				return ;
			}
				
			$frmdt = date("Y-m-d",strtotime($values["fromdate"]));
			$todt = date("Y-m-d",strtotime($values["todate"]));
			$resp = array();
				/*
			$sql = \FuelTransaction::whereBetween("filledDate",array($frmdt,$todt));
						if($values["depot"]!=0){
							$sql->where("contracts.depotId",$values["depot"]);
						}						
						//$sql->where("fullTank","YES")
						$sql->where("fueltransactions.deleted","No")
						->where("fueltransactions.status","ACTIVE")
						->where("contracts.clientId",$values["clientname"])
						->join("contracts","contracts.id","=","fueltransactions.contractId")
						->leftjoin("vehicle","vehicle.id","=","fueltransactions.vehicleId")
						->leftjoin("lookuptypevalues","lookuptypevalues.id","=","vehicle.vehicle_type");
			$entities =	$sql->select($select_args)->orderBy("fueltransactions.vehicleId","asc")->orderBy("filledDate","desc")->get();
			$sql =\FuelTransaction::whereBetween("filledDate",array($frmdt,$todt));
						//->where("fueltransactions.fullTank","YES")
						if($values["depot"]!=0){
							$sql->where("contracts.depotId",$values["depot"]);
						}
						//$sql->where("fullTank","YES")
						$sql->where("fueltransactions.fullTank","No")
						->where("fueltransactions.deleted","No")
						->where("fueltransactions.status","ACTIVE")
						->where("contracts.depotId",$values["depot"])
						->where("contracts.clientId",$values["clientname"])
						->join("contracts","contracts.id","=","fueltransactions.contractId");
			*/			
			$sql = \FuelTransaction::where("fueltransactions.status","=","ACTIVE");
						if($values["depot"]!=0){
							$sql->where("contracts.depotId",$values["depot"]);
						}
						$sql->where("contractId",">",0)
						->where("clients.id","=",$values["clientname"])
						->whereBetween("filledDate",array($frmdt,$todt))
						->leftjoin("vehicle", "vehicle.id","=","fueltransactions.vehicleId")
						->leftjoin("lookuptypevalues","lookuptypevalues.id","=","vehicle.vehicle_type")
						->leftjoin("fuelstationdetails", "fuelstationdetails.id","=","fueltransactions.fuelStationId")
						->leftjoin("contracts", "contracts.id","=","fueltransactions.contractId")
						->leftjoin("clients", "clients.id","=","contracts.clientId")
						->leftjoin("depots", "depots.id","=","contracts.depotId");
			$entities =	$sql->select($select_args)->orderBy("fueltransactions.vehicleId")->orderBy("filledDate","asc")->get();

			//$total =  $sql->count();
			$i=0;
			$k=0;
			$no_ltrs = 0;
			$no_distance = 0;
			$st_reading = 0;
			$veh = "";
			for($i=0; $i<count($entities)-1; $i++){	
				$row = array();
				$row["veh_reg"] = $entities[$i]->veh_reg;
				$row["vehicle_type"] = $entities[$i]->vehicle_type;
				$row["yearof_pur"] = date("d-m-Y",strtotime($entities[$i]->yearof_pur));
				$row["startDate"] = date("d-m-Y",strtotime($entities[$i]->startDate));
// 				$row["endDate"] = date("d-m-Y",strtotime($entities[$i+1]->endDate));
// 				if($entities[$i]->veh_reg != $entities[$i+1]->veh_reg){
// 					$row["endDate"] = "";
// 				}

				$row["meterReading"] =  $entities[$i]->startReading;
// 				if($entities[$i]->veh_reg == $entities[$i+1]->veh_reg){
// 					$row["distance"] = $entities[$i+1]->startReading-$entities[$i]->startReading;
// 					if($row["distance"]<0){
// 						$row["distance"] = $row["distance"]*-1;
// 					}
// 				}
// 				else{
// 					$row["distance"] = "";
// 				}
				$row["distance"] = 0; 
				$row["litres"] = $entities[$i]->litres;
				$row["fullTank"] = $entities[$i]->fullTank;
				$row["mileage"] = $entities[$i]->mileage;
				$ltrs = 0;
				if($entities[$i]->fullTank=="NO"){					
					$todt = $frmdt;
					$frmdt = $todt;
					$recs = \FuelTransaction::where("vehicleId","=",$entities[$i]->vehicleId)->where("fullTank","=","YES")
									->where("filledDate","<",$entities[$i]->startDate)->where("status","=","ACTIVE")
									->orderBy("filledDate","desc")->limit(1)->get();
					if(count($recs)>0){
						$st_reading = $recs[0]->startReading;
					}
				}
				if($veh != $entities[$i]->vehicleId){
					$veh = $entities[$i]->vehicleId;
				}				
				if($entities[$i]->fullTank=="NO"){					
					continue;
				}
				if($entities[$i]->fullTank=="YES" && $entities[$i]->mileage=="0.00"){
					$j = $k+1;
					while($j<count($entities))
					{					
						if($entities[$j]->vehicleId==$entities[$i]->vehicleId && $entities[$j]->fullTank=="YES"){								
							$ltrs = $ltrs+$entities[$i]->litres;								
							$row["mileage"] = round((($entities[$i]->startReading-$entities[$j]->startReading)/$ltrs), 2);
// 							$frec = \FuelTransaction::where("id","=",$entities[$i]->id)->get();
// 							if(count($frec)>0){
// 								$frec = $frec[0];
// 								$frec->mileage = $row["mileage"]; 
// 								$frec->update();
// 							}
							break;
						}
						if($j<count($entities) && $entities[$j]->fullTank=="NO"){
							$ltrs = $ltrs+$entities[$j]->litres;
						}
						$j++;
					}
				}
				$k++;
				if($row["mileage"]<0){
					$row["mileage"] = $row["mileage"]*-1;
				}
				//$row["distance"] = $st_reading;
				if($st_reading>0){
					$st_reading = $entities[$i]->startReading-$st_reading;
					$entities[$i]->litres = $st_reading/$entities[$i]->mileage;
				}
				else{
					$st_reading =  round(($entities[$i]->litres)*$row["mileage"]);
				}
				$row["distance"] = round($st_reading);
				$row["litres"] = round($entities[$i]->litres);
// 				if($entities[$i]->veh_reg != $entities[$i+1]->veh_reg){
// 					//continue;
// 					$row["mileage"] = $entities[$i]->mileage;
// 					$row["distance"] = $row["distance"];						
// 				}
				$row["remarks"] = $entities[$i]->remarks." ";//.$entities[$i]->startReading;
				$resp[] = $row;
				if($entities[$i]->fullTank=="YES"){
					$st_reading = 0;
				}
				//print_r($resp);
			}
			//die();
			
			
// 			if(count($entities)>0){
// 				/*$row["veh_reg"] = $entities[$i]->veh_reg;
// 				$row["vehicle_type"] = $entities[$i]->vehicle_type;
// 				$row["yearof_pur"] = date("d-m-Y",strtotime($entities[$i]->yearof_pur));
// 				$row["startDate"] = date("d-m-Y",strtotime($entities[$i]->startDate));
// 				$row["endDate"] = date("d-m-Y",strtotime($entities[$i]->endDate));
// 				$row["distance"] = $entities[$i]->startReading-$entities[$i]->startReading;
// 				$row["litres"] = $entities[$i]->litres;
// 				$row["mileage"] = round(($entities[$i]->startReading-$entities[$i]->startReading)/$entities[$i]->litres, 2);
// 				$row["remarks"] = $entities[$i]->remarks;//." ".$entities[$i]->startReading;
// 				$resp[] = $row;*/
// 			}
			echo json_encode($resp);
			return;
				
		}
		$values['bredcum'] = "VEHICLE MILEAGE REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads = array('vehicle no','Vehicle Type', "Year of purchase", "filled date","meterReading", "total distance", "total fuel", "fulltank", "mileage", "remarks");
		$values["theads"] = $theads;
	
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "VEHILE MILEAGE REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
		
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$form_fields = array();
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"getFormData(this.value);"), "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.vehiclemileagereport', array("values"=>$values));
	}
	
	private function getClientVehicleTrips($values)
	{
		if (\Request::isMethod('post'))
		{
			//$values["test"];
			
			if(!isset($values["fromdate"]) || !isset($values["todate"])){
				echo json_encode(array("total"=>0, "data"=>array()));
				return ;
			}
			$select_args = array();
			$select_args[] = "service_logs.serviceDate as serviceDate";
			$select_args[] = "service_logs.contractVehicleId as contractVehicleId";
			$select_args[] = "contract_vehicles.vehicleId as contractVehicleId";
			$select_args[] = "service_logs.tripNumber as tripNumber";
			$select_args[] = "service_logs.startTime as startTime";
			$select_args[] = "service_logs.startReading as startReading";
			$select_args[] = "service_logs.endReading as endReading";
			$select_args[] = "service_logs.distance as distance";
			$select_args[] = "service_logs.driver1Id as driver1Id";
			$select_args[] = "service_logs.driver2Id as driver2Id";
			$select_args[] = "service_logs.helperId as helperId";
			$select_args[] = "service_logs.remarks as remarks";
	
			$frmdt = date("Y-m-d",strtotime($values["fromdate"]));
			$todt = date("Y-m-d",strtotime($values["todate"]));
			$resp = array();
	
			$qry=  \ServiceLog::join("contracts","service_logs.contractId","=","contracts.id")
								->join("contract_vehicles","contract_vehicles.id","=","service_logs.contractVehicleId")
								->where("contracts.clientId","=",$values["clientname"])
								->where("contracts.depotId","=",$values["depot"])
								->where("service_logs.status","=","ACTIVE");
								
			
			$recs = $qry->select($select_args)->orderBy("service_logs.serviceDate","desc")->get();
			
			
			$veh_arr = array();
			$vehicles = \Vehicle::all();
			foreach ($vehicles as $vehicle){
				$veh_arr[$vehicle->id] = $vehicle->veh_reg;
			}
			
			$drivers =  \Employee::where("roleId","=",19)->get();
			$drivers_arr = array();
			foreach ($drivers as $driver){
				
				$drivers_arr[$driver['id']] = $driver['fullName']." (".$driver->empCode.")";
				
			}
			
			$helpers =  \Employee::where("roleId","=",20)->get();
			$helpers_arr = array();
			foreach ($helpers as $helper){
					
				$helpers_arr[$helper['id']] = $helper['fullName']." (".$helper->empCode.")";
			}
			
			$prev_date = "";
			$prev_veh_no = "";
			$trip = 1;
			
			foreach($recs as  $rec) {
				if($rec->driver2Id > 0){
					$rec->driver2Id = $drivers_arr[$rec->driver2Id];
				}
				else{
					$rec->driver2Id = "";
				}
				
				if ($prev_date == date("d-m-Y",strtotime($rec->serviceDate)) && $prev_veh_no == $veh_arr[$rec->contractVehicleId]){
					$trip++;
				}
				if ($prev_date != date("d-m-Y",strtotime($rec->serviceDate))){
					$trip = 1;
				}
				$row = array();
				$row["serviceDate"] = date("d-m-Y",strtotime($rec->serviceDate));
				$prev_date = date("d-m-Y",strtotime($rec->serviceDate));
				$row["contractVehicleId"] = $veh_arr[$rec->contractVehicleId];
				$prev_veh_no = $veh_arr[$rec->contractVehicleId];
				$row["tripNumber"] = $trip;
				$row["startTime"] = $rec->startTime;
				$row["startReading"] = $rec->startReading;
				$row["endReading"] = $rec->endReading;
				$row["distance"] = $rec->distance;
				$row["driver1Id"] = $drivers_arr[$rec->driver1Id];
				$row["driver2Id"] = $rec->driver2Id;
				$row["helperId"] = "";
				if($rec->helperId != "" || $rec->helperId != 0){
					$row["helperId"] = $helpers_arr[$rec->helperId];
				}
				$row["remarks"] = $rec->remarks;
				
				$resp[] = $row;
			}
			echo json_encode($resp);
			return;
	
		}
		$values['bredcum'] = "CLIENT VEHICLE TRIPS REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads = array('service date','vehicle no', "trip number", "start time", "start reading", "end reading", "distance", "driver1", "driver2", "helper", "remarks");
		$values["theads"] = $theads;
	
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "CLIENT VEHICLE TRIPS REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
	
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$form_fields = array();
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"getFormData(this.value);"), "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.clientvehicletripsreport', array("values"=>$values));
	}
	
	private function getServiceLog($values)
	{
		if (\Request::isMethod('post'))
		{
			//$values["test"];				
			if(!isset($values["fromdate"]) || !isset($values["todate"])){
				echo json_encode(array("total"=>0, "data"=>array()));
				return ;
			}
			$frmdt = date("Y-m-d",strtotime($values["fromdate"]));
			$todt = date("Y-m-d",strtotime($values["todate"]));
			if ($values["reportfor"] == "getreport") {
				$select_args = array();
				$select_args[] = "contracts.startDate as startDate";
				$select_args[] = "contracts.endDate as endDate";
				$select_args[] = "service_logs.serviceDate as serviceDate";
				$select_args[] = "contract_vehicles.vehicleId as contractVehicleId";
				$select_args[] = "service_logs.substituteVehicleId as substituteVehicleId";
				$select_args[] = "service_logs.tripNumber as tripNumber";
				$select_args[] = "service_logs.startTime as startTime";
				$select_args[] = "service_logs.startReading as startReading";
				$select_args[] = "service_logs.endReading as endReading";
				$select_args[] = "service_logs.distance as distance";
				$select_args[] = "service_logs.repairkms as repairkms";
				$select_args[] = "contract_vehicles.driver1Id as driver1Id";
				$select_args[] = "contract_vehicles.driver2Id as driver2Id";
				$select_args[] = "contract_vehicles.helperId as helperId";
				$select_args[] = "service_logs.remarks as remarks";
				
				$resp = array();
				$qry =  \ServiceLog::join("contracts","service_logs.contractId","=","contracts.id")
								->join("contract_vehicles","contract_vehicles.id", "=", "service_logs.contractVehicleId")
								->where("contracts.clientId","=",$values["clientname"]);
							if($values["depot"]!=0){
								$qry->where("contracts.depotId","=",$values["depot"]);
							}
								$qry->where("service_logs.status","=",'ACTIVE')
								->whereBetween("service_logs.serviceDate",array($frmdt,$todt));
				
				$recs = $qry->select($select_args)->orderBy("service_logs.serviceDate","desc")->orderBy("service_logs.contractVehicleId","desc")->get();
				$veh_arr = array();
				$vehicles = \Vehicle::all();
				foreach ($vehicles as $vehicle){
					$veh_arr[$vehicle->id] = $vehicle->veh_reg;
				}
					
				$drivers =  \Employee::where("roleId","=",19)->get();
				$drivers_arr = array();
				foreach ($drivers as $driver){
					$drivers_arr[$driver['id']] = $driver['fullName']." (".$driver->empCode.")";
				}
					
				$helpers =  \Employee::where("roleId","=",20)->get();
				$helpers_arr = array();
				foreach ($helpers as $helper){
						
					$helpers_arr[$helper['id']] = $helper['fullName']." (".$helper->empCode.")";
				}
					
				$prev_date = "";
				$prev_veh_no = "";
				$trip = 1;
				$totkms = 0;
				$tot_rp_kms = 0;
				foreach($recs as  $rec) {
					$trip = 1;
					if($rec->driver2Id > 0){
						$rec->driver2Id = $drivers_arr[$rec->driver2Id];
					}
					else{
						$rec->driver2Id = "";
					}
					if ($prev_date == date("d-m-Y",strtotime($rec->serviceDate)) && $prev_veh_no == $veh_arr[$rec->contractVehicleId]){
						$trip++;
					}
					if ($prev_date != date("d-m-Y",strtotime($rec->serviceDate))){
						$trip = 1;
					}
					if ($rec->substituteVehicleId == 0){
						$rec->substituteVehicleId = "";
					}
					else{
						$rec->substituteVehicleId = $veh_arr[$rec->substituteVehicleId];
					}
					$row = array();
					$row["startDate"] = date("d-m-Y",strtotime($rec->startDate))." to ".date("d-m-Y",strtotime($rec->endDate));
					$row["serviceDate"] = date("d-m-Y",strtotime($rec->serviceDate));
					$prev_date = date("d-m-Y",strtotime($rec->serviceDate));
					$row["contractVehicleId"] = "";
					if(isset($veh_arr[$rec->contractVehicleId])){
						$row["contractVehicleId"] = $veh_arr[$rec->contractVehicleId];
					}
					$prev_veh_no = "";
					if(isset($veh_arr[$rec->contractVehicleId])){
						$prev_veh_no = $veh_arr[$rec->contractVehicleId];
					}
					$row["substituteVehicleId"] = $rec->substituteVehicleId ;
					$row["tripNumber"] = $trip;
					$row["startTime"] = $rec->startTime;
					$row["startReading"] = $rec->startReading;
					$row["endReading"] = $rec->endReading;
					$row["distance"] = (int)$rec->distance;
					$totkms = $totkms+(int)$rec->distance;
					$row["repairkms"] = $rec->repairkms;
					$tot_rp_kms = $tot_rp_kms+$rec->repairkms;
					$row["driver1Id"] = 0;
					if(isset($drivers_arr[$rec->driver1Id])){
						$row["driver1Id"] = $drivers_arr[$rec->driver1Id];
					}
					$row["driver2Id"] = 0;
					if(isset($drivers_arr[$rec->driver2Id])){
						$row["driver2Id"] = $drivers_arr[$rec->driver2Id];
					}
					$row["helperId"] = "";
					if(isset($helpers_arr[$rec->helperId])){
						$row["helperId"] = $helpers_arr[$rec->helperId];
					}
					$row["remarks"] = $rec->remarks;
					
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp, "total_kms"=>$totkms, "total_rp_kms"=>$tot_rp_kms);
			}
			else if($values["reportfor"] == "vehiclesummary"){
				$resp = array();
				$select_args = array();
				/*$select_args[] = "contracts.startDate as startDate";
				$select_args[] = "contracts.endDate as endDate";
				$select_args[] = "clients.name as client";
				$select_args[] = "depots.name as depots";
				$select_args[] = "service_logs.contractVehicleId as contractVehicleId";
				$select_args[] = "sum(service_logs.distance) as distance";
				$select_args[] = "sum(service_logs.repairkms) as repairkms";
				
				$resp = array();
				$qry=  \ServiceLog::join("contracts","service_logs.contractId","=","contracts.id")
									->join("clients","contracts.clientId","=","clients.id")
									->join("depots","contracts.depotId","=","depots.id")
									->where("contracts.clientId","=",$values["clientname"])
									->where("contracts.depotId","=",$values["depot"]);
				$recs = $qry->select($select_args)
						->groupBy("service_logs.contractVehicleId")->orderBy("service_logs.serviceDate","desc")->get();
				*/
				$sql = "select `contracts`.`startDate` as `startDate`, `contracts`.`endDate` as `endDate`," 
							."`clients`.`name` as `client`, `depots`.`name` as `depots`, `contract_vehicles`.`vehicleId` as `contractVehicleId`,  `contract_vehicles`.`id` as `contractVehicleId1`, "
							."sum(service_logs.distance) as `distance`, sum(service_logs.repairkms) as `repairkms` from `service_logs`"
							."inner join `contracts` on `service_logs`.`contractId` = `contracts`.`id`"
							."inner join `contract_vehicles` on `service_logs`.`contractVehicleId` = `contract_vehicles`.`id`"
							."inner join `clients` on `contracts`.`clientId` = `clients`.`id`"
							."inner join `depots` on `contracts`.`depotId` = `depots`.`id`"
							."where  `service_logs`.`status` = 'ACTIVE' and "
							." `contracts`.`clientId` = ".$values["clientname"];
							if($values["depot"]!=0){
								$sql = $sql." and `contracts`.`depotId` = ".$values["depot"];
							}
				$sql = 		$sql." and serviceDate between '".$frmdt."' and '".$todt."' group by `service_logs`.`contractVehicleId` order by `service_logs`.`serviceDate` desc";
				$recs = \DB::select(\DB::raw($sql));
				$veh_arr = array();
				$vehicles = \Vehicle::all();
				foreach ($vehicles as $vehicle){
					$veh_arr[$vehicle->id] = $vehicle->veh_reg;
				}
					
					
				foreach($recs as  $rec) {
					$row = array();
					$row["startDate"] = date("d-m-Y",strtotime($rec->startDate))." to ".date("d-m-Y",strtotime($rec->endDate));
					$row["client"] = $rec->client;
					$row["depots"] = $rec->depots;
					$row["contractVehicleId"] = $veh_arr[$rec->contractVehicleId];
					
					$trip2_kms = 0;
					/*$sql = "select count(serviceDate) as cnt, distance from `service_logs` where `contractVehicleId` = ".$rec->contractVehicleId1." and `substituteVehicleId` = 0 and `status` = 'ACTIVE' and serviceDate BETWEEN '".$frmdt."' and '".$todt."' GROUP BY `serviceDate` HAVING cnt > 1";
					$servlogs = \DB::select(\DB::raw($sql));
					foreach ($servlogs as $servlog){
						$trip2_kms = $trip2_kms+$servlog->distance;
					}
					*/
					$row["distance"] = (int)$rec->distance+$trip2_kms;
					$row["repairkms"] = $rec->repairkms;
				
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp);
			}
			else if($values["reportfor"] == "workingdaysvehiclesummary"){
				$select_args = array();
				$select_args[] = "contracts.startDate as startDate";
				$select_args[] = "contracts.endDate as endDate";
				$select_args[] = "contracts.avgKms as avgKms";
				$select_args[] = "clients.name as client";
				$select_args[] = "depots.name as depots";
				$select_args[] = "contract_vehicles.vehicleId as contractVehicleId";
				$select_args[] = "service_logs.distance as distance";
				$select_args[] = "service_logs.repairkms as repairkms";
			
				$resp = array();
				$totkms = 0;
				$tot_rp_kms = 0;
				$tot_tp1_kms = 0;
				$tot_tp2_kms = 0;
				$tot_hds_kms = 0;
				$tot_ex_kms = 0;
				// service_logs.substituteVehicleId=0 and
				$sql = "select `contracts`.`startDate` as `startDate`, `contracts`.`endDate` as `endDate`, `contracts`.`avgKms` as `avgKms`," 
							."`clients`.`name` as `client`, `depots`.`name` as `depots`, `depots`.`id` as `depotId`, `contract_vehicles`.`id` as `contractVehicleId`, "
							."`contract_vehicles`.`vehicleId` as `vehicleId`, `contract_vehicles`.vehicleStartDate, "
							."sum(service_logs.distance) as `distance`, sum(service_logs.repairkms) as `repairkms` from `service_logs`"
							."inner join `contracts` on `service_logs`.`contractId` = `contracts`.`id`"
							."inner join `contract_vehicles` on `service_logs`.`contractVehicleId` = `contract_vehicles`.`id`"
							."inner join `clients` on `contracts`.`clientId` = `clients`.`id`"
							."inner join `depots` on `contracts`.`depotId` = `depots`.`id`"
							."where  `service_logs`.`status` = 'ACTIVE' and "		
							." `contracts`.`clientId` = ".$values["clientname"];
							if($values["depot"]!=0){
								$sql = 	$sql." and `contracts`.`depotId` = ".$values["depot"];
							}
				$sql = 		$sql." and serviceDate between '".$frmdt."' and '".$todt."' group by `service_logs`.`contractVehicleId` order by `service_logs`.`serviceDate` desc";
				$recs = \DB::select(\DB::raw($sql));
				$veh_arr = array();
				$vehicles = \Vehicle::all();
				foreach ($vehicles as $vehicle){
					$veh_arr[$vehicle->id] = $vehicle->veh_reg;
				}
				
				$date1=date_create($frmdt);
				$date2=date_create($todt);
				$diff=date_diff($date1,$date2);
				$working_days =  $diff->format("%a")+1;				
							
				foreach($recs as  $rec) {
					$tot_holidays=0;
					$sql = \Contract::where("clientId","=",$values["clientname"])
									->where("depotId","=",$rec->depotId);
					$contracts = $sql->where("status","=","ACTIVE")->get();
					$contractid_arr = array();
					foreach($contracts as $contract){
						$contractid_arr[] = $contract->id;
					}
					$holidays = \ClientHolidays::whereIn("contractId",$contractid_arr)
									->where("status","=","Open")->get();
					$holidays_arr = array();
					foreach($holidays as  $holiday) {
						$date1 = strtotime($frmdt);
						$date2 = strtotime($todt);
						$date3 = strtotime(date("Y-m-d",strtotime($holiday->fromDate)));
						$date4 = strtotime(date("Y-m-d",strtotime($holiday->toDate)));
						if($date1<=$date3 && $date2>=$date4){
							$dt1 = date_create(date("Y-m-d",strtotime($holiday->fromDate)));
							$dt2 = date_create(date("Y-m-d",strtotime($holiday->toDate)));
							$diff = date_diff($dt1,$dt2);
							$tot_holidays =  $tot_holidays+$diff->format("%a");
							$tot_holidays = $tot_holidays+1;
							//echo $holiday->fromDate." - ".$holiday->toDate." : ".$diff->format("%a")."=====";
							$date = $dt1;
							for($i=0; $i<$diff->format("%a")+1; $i++){
								if(!in_array(date_format($date, 'Y-m-d'), $holidays_arr)){
									$holidays_arr[] =  date_format($date, 'Y-m-d');
								}
								$date = date_add($date, date_interval_create_from_date_string('1 days'));
							}
						}
						else if($date1>=$date3 && $date2<=$date4){
							$dt1 = date_create(date("Y-m-d",strtotime($frmdt)));
							$dt2 = date_create(date("Y-m-d",strtotime($todt)));
							$diff = date_diff($dt1,$dt2);
							$tot_holidays =  $tot_holidays+$diff->format("%a");
							$tot_holidays = $tot_holidays+1;
					
							$date = $dt1;
							for($i=0; $i<$diff->format("%a")+1; $i++){
								if(!in_array(date_format($date, 'Y-m-d'), $holidays_arr)){
									$holidays_arr[] =  date_format($date, 'Y-m-d');
								}
								$date = date_add($date, date_interval_create_from_date_string('1 days'));
							}
						}
						else if($date1<=$date3 && $date2<=$date4 && $date3<=$date2){
							$dt1 = date_create(date("Y-m-d",strtotime($holiday->fromDate)));
							$dt2 = date_create(date("Y-m-d",strtotime($todt)));
							$diff = date_diff($dt1,$dt2);
							$tot_holidays =  $tot_holidays+$diff->format("%a");
							$tot_holidays = $tot_holidays+1;
					
							$date = $dt1;
							for($i=0; $i<$diff->format("%a")+1; $i++){
								if(!in_array(date_format($date, 'Y-m-d'), $holidays_arr)){
									$holidays_arr[] =  date_format($date, 'Y-m-d');
								}
								$date = date_add($date, date_interval_create_from_date_string('1 days'));
							}
						}
						else if($date1>=$date3 && $date2>=$date4 && $date1<=$date4){
							$dt1 = date_create(date("Y-m-d",strtotime($frmdt)));
							$dt2 = date_create(date("Y-m-d",strtotime($holiday->toDate)));
							$diff = date_diff($dt1,$dt2);
							$tot_holidays =  $tot_holidays+$diff->format("%a");
							$tot_holidays = $tot_holidays+1;
					
							$date = $dt1;
							for($i=0; $i<$diff->format("%a")+1; $i++){
								if(!in_array(date_format($date, 'Y-m-d'), $holidays_arr)){
									$holidays_arr[] =  date_format($date, 'Y-m-d');
								}
								$date = date_add($date, date_interval_create_from_date_string('1 days'));
							}
						}
					}
					$row = array();
					$row["startDate"] = date("d-m-Y",strtotime($rec->startDate))." to ".date("d-m-Y",strtotime($rec->endDate));
					$row["client"] = $rec->client;
					$row["depots"] = $rec->depots;
					$row["contractVehicleId"] = $veh_arr[$rec->vehicleId];
					$row["avg kms"] =$rec->avgKms;
					$ex_start_days = 0;
					if($rec->vehicleStartDate!="0000-00-00" && $rec->vehicleStartDate!="1970-01-01"){
						$dt1 = date_create(date("Y-m-d",strtotime($frmdt)));
						$dt2 = date_create(date("Y-m-d",strtotime($rec->vehicleStartDate)));
						$diff = date_diff($dt2,$dt1);
						if($diff->format("%R%a")<0){
							$ex_start_days = $diff->format("%R%a");
						}
					}
					$row["working days"] = ($working_days+$ex_start_days)-count($holidays_arr);
					$row["avg kms total"] = $rec->avgKms*$row["working days"];
					$row["total holiday days"] = count($holidays_arr);
					$start_reading = 0;
					$servlogs = null;
					if($ex_start_days<0){
						$servlogs = \ServiceLog::whereIn("contractId",$contractid_arr)
											->where("contractVehicleId","=",$rec->contractVehicleId)
											->where("serviceDate",">=",$rec->vehicleStartDate)
											->where("substituteVehicleId","=",0)
											->where("status","=","ACTIVE")->orderBy("serviceDate","desc")->get();
						if(count($servlogs)>0){
							$servlog = $servlogs[count($servlogs)-1];
							$start_reading = $servlog->startReading;
						}
					}
					else{
						$servlogs = \ServiceLog::whereIn("contractId",$contractid_arr)
											->where("contractVehicleId","=",$rec->contractVehicleId)
											->where("serviceDate",">=",$frmdt)
											->where("substituteVehicleId","=",0)
											->where("status","=","ACTIVE")->orderBy("serviceDate","desc")->get();
						if(count($servlogs)>0){
							$servlog = $servlogs[count($servlogs)-1];
							$start_reading = $servlog->startReading;
						}
					}
					
					$end_reading = 0;
					$servlogs = \ServiceLog::whereIn("contractId",$contractid_arr)
											->where("contractVehicleId","=",$rec->contractVehicleId)
											->where("serviceDate","<=",$todt)
											->where("substituteVehicleId","=",0)
											->where("status","=","ACTIVE")->orderBy("serviceDate","asc")->get();
					if(count($servlogs)>0){
						$servlog = $servlogs[count($servlogs)-1];
						$end_reading = $servlog->endReading;
					}
					
					$holidays_kms = 0;
					//print_r($holidays_arr); die();
					$servlogs = \ServiceLog::whereIn("contractId",$contractid_arr)
									->where("contractVehicleId","=",$rec->contractVehicleId)
									->whereIn("serviceDate",$holidays_arr)
									->where("substituteVehicleId","=",0)
									->where("status","=","ACTIVE")->orderBy("serviceDate","asc")->get();
					foreach ($servlogs as $servlog){
						$holidays_kms = $holidays_kms+$servlog->distance;
					}
					
					$trip1_kms = $rec->distance;
					//echo  "trip-1:".$trip1_kms."-";
					$trip2_kms = 0;
					$contractid_str = "";
					foreach($contractid_arr as $conid){
						$contractid_str = $contractid_str.$conid.",";
					}
					$contractid_str= substr($contractid_str, 0, strlen($contractid_str)-1);
					$sql = "select distance from `service_logs` where `contractId` in (".$contractid_str.") and `contractVehicleId` = ".$rec->contractVehicleId." and `status` = 'ACTIVE' and serviceDate BETWEEN '".$frmdt."' and '".$todt."' GROUP BY `serviceDate`";
					$servlogs = \DB::select(\DB::raw($sql));
					foreach ($servlogs as $servlog){
						$trip2_kms = $trip2_kms+$servlog->distance;
						
					}
					//echo "1trip-2:".$trip2_kms."-";
					$trip12_kms = 0;
					/*
					if($trip2_kms>0){
						$sql = "select sum(distance) as distance from `service_logs` where `contractId` in (".$contractid_str.") and `contractVehicleId` = ".$rec->contractVehicleId." and `substituteVehicleId` = 0 and `status` = 'ACTIVE' and serviceDate BETWEEN '".$frmdt."' and '".$todt."'";
						$servlogs = \DB::select(\DB::raw($sql));
						foreach ($servlogs as $servlog){
							$trip12_kms = $trip12_kms+$servlog->distance;
						}
						//$trip2_kms = $trip2_kms-($trip12_kms-($trip1_kms-($trip2_kms+$holidays_kms)));
					}
					*/
					$row["start reading"] = $start_reading;
					$row["end reading"] = $end_reading;
					$trip2_kms = $trip1_kms-$trip2_kms;
					
					//echo "1trip-2:".$trip2_kms;die();
					
					$trip1_kms = $trip1_kms-$trip2_kms;
					$row["trip1 kms"] = ($trip1_kms-$holidays_kms);
					$tot_tp1_kms = $tot_tp1_kms+$row["trip1 kms"];
					$row["trip2 kms"] = $trip2_kms;
					$tot_tp2_kms = $tot_tp2_kms+$row["trip2 kms"];
					$totkms = $totkms+($tot_tp1_kms+$tot_tp2_kms);
					$row["holidays kms"] = (int)$holidays_kms;
					$tot_hds_kms = $tot_hds_kms+(int)$holidays_kms;
					$row["repair kms"] = $rec->repairkms;
					$tot_rp_kms = $tot_rp_kms+$rec->repairkms;
					$row["excess kms"] = (int)($trip1_kms+$trip2_kms)-($row["working days"]*$rec->avgKms);
					$tot_ex_kms = $tot_ex_kms+$row["excess kms"];
					$row["final kms"] = (int)($trip1_kms+$trip2_kms);
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp, "total_kms"=>($tot_tp1_kms+$tot_tp2_kms+$tot_hds_kms), "total_rp_kms"=>$tot_rp_kms, "total_tp1_kms"=>$tot_tp1_kms, "total_tp2_kms"=>$tot_tp2_kms, "total_hds_kms"=>$tot_hds_kms, "total_ex_kms"=>$tot_ex_kms);
			}
			echo json_encode($resp1);
			return;
	
		}
		$values['bredcum'] = "SERVICE LOG REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads1 = array('contract year','service date','vehicle no',"sub. Veh no", "trip number", "start time", "start reading", "end reading", "distance","Repair KMs", "driver1", "driver2", "helper", "remarks");
		$values["theads1"] = $theads1;
		$theads2 = array('contract year','client','client branch',"vehicle no", "distance", "Repair KMs");
		$values["theads2"] = $theads2;
		
		$theads3 = array('contract year','client','client branch',"vehicle no", "Avg KMs(per day)", "Working Days", "Avg KMs", 'Tot Holidays',"Start Reading", "End reading", "Tp1 KMs", "Tp2 KMs",'Holidays KMs',"Repair KMs", "Excess KMs", "Final KMs");
		$values["theads3"] = $theads3;
	
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "SERVICE LOG REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
	
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$form_fields = array();
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"getFormData(this.value);"), "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reportfor", "value"=>"", "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
		return View::make('reports.servicelogreport', array("values"=>$values));
	}
	
	private function getClientHolidaysWorking($values)
	{
		if (\Request::isMethod('post'))
		{
			//$values["test"];
	
			if(!isset($values["fromdate"]) || !isset($values["todate"])){
				echo json_encode(array("total"=>0, "data"=>array()));
				return ;
			}
			$frmdt = date("Y-m-d",strtotime($values["fromdate"]));
			$todt = date("Y-m-d",strtotime($values["todate"]));
			if ($values["reportfor"] == "getreport") {
					
				$select_args = array();
				$select_args[] = "service_logs.serviceDate as serviceDate";
				$select_args[] = "contract_vehicles.vehicleId as contractVehicleId";
				$select_args[] = "service_logs.startTime as startTime";
				$select_args[] = "service_logs.startReading as startReading";
				$select_args[] = "service_logs.endReading as endReading";
				$select_args[] = "service_logs.distance as distance";
				$select_args[] = "servicelogrequests.comments as comments";
				$select_args[] = "service_logs.remarks as remarks";
				$resp = array();
	
				$qry=  \ServiceLog::join("contracts","service_logs.contractId","=","contracts.id")
									->join("contract_vehicles","contract_vehicles.id","=","service_logs.contractVehicleId")
									->join("servicelogrequests","servicelogrequests.contractId","=","service_logs.contractId")
									->where("servicelogrequests.vehicleId","=","contract_vehicles.vehicleId")
									->where("servicelogrequests.customDate","=","service_logs.serviceDate")
									->where("contracts.clientId","=",$values["clientname"])
									->where("contracts.depotId","=",$values["depot"])
									->whereBetween('servicelogrequests.customDate', array($frmdt, $todt));
					
				$recs = $qry->select($select_args)->orderBy("service_logs.serviceDate","desc")->get();
					
				$veh_arr = array();
				$vehicles = \Vehicle::all();
				foreach ($vehicles as $vehicle){
					$veh_arr[$vehicle->id] = $vehicle->veh_reg;
				}
					
					
				foreach($recs as  $rec) {
					$row = array();
					$row["serviceDate"] = date("d-m-Y",strtotime($rec->serviceDate));
					$row["contractVehicleId"] = $veh_arr[$rec->contractVehicleId];
					$row["startTime"] = $rec->startTime;
					$row["startReading"] = $rec->startReading;
					$row["endReading"] = $rec->endReading;
					$row["distance"] = $rec->distance;
					$row["comments"] = $rec->comments;
					$row["remarks"] = $rec->remarks;
					
					$resp[] = $row;
				}
			}
			else if($values["reportfor"] == "summary"){
	
				$select_args = array();
				$select_args[] = "contracts.startDate as startDate";
				$select_args[] = "contracts.endDate as endDate";
				$select_args[] = "clients.name as client";
				$select_args[] = "depots.name as depots";
				$select_args[] = "contract_vehicles.vehicleId as contractVehicleId";
				$select_args[] = "service_logs.distance as distance";
				$select_args[] = "service_logs.repairkms as repairkms";
	
				$resp = array();
	
				$qry=  \ServiceLog::join("contracts","service_logs.contractId","=","contracts.id")
										->join("clients","contracts.clientId","=","clients.id")
										->join("contract_vehicles","contract_vehicles.id","=","service_logs.contractVehicleId")
										->join("depots","contracts.depotId","=","depots.id")
										->where("contracts.clientId","=",$values["clientname"])
										->where("contracts.depotId","=",$values["depot"]);
				$recs = $qry->select($select_args)->orderBy("service_logs.serviceDate","desc")->get();
				
				$veh_arr = array();
				$vehicles = \Vehicle::all();
				foreach ($vehicles as $vehicle){
					$veh_arr[$vehicle->id] = $vehicle->veh_reg;
				}
				
				foreach($recs as  $rec) {
					$row = array();
					$row["startDate"] = date("d-m-Y",strtotime($rec->startDate))." to ".date("d-m-Y",strtotime($rec->endDate));
					$row["client"] = $rec->client;
					$row["depots"] = $rec->depots;
					$row["contractVehicleId"] = $veh_arr[$rec->contractVehicleId];
					$row["distance"] = $rec->distance;
					$row["repairkms"] = $rec->repairkms;
	
					$resp[] = $row;
				}
			}
			echo json_encode($resp);
			return;
	
		}
		$values['bredcum'] = "CLIENT HOLIDAYS WORKING REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads1 = array('service date','vehicle no', "start time", "start reading", "end reading", "kms", "comments", "service log comments");
		$values["theads1"] = $theads1;
		$theads2 = array('Month','number of trips','distance');
		$values["theads2"] = $theads2;
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "CLIENT HOLIDAYS WORKING REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
	
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$form_fields = array();
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"getFormData(this.value);"), "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reportfor", "value"=>"", "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.clientholidaysworkingreport', array("values"=>$values));
	}
	
	private function getContractVehiclesReport($values)
	{
		if (\Request::isMethod('post'))
		{
			if ($values["reportfor"] == "getreport") {					
				$select_args = array();
				$select_args[] = "contracts.id as cid";
				$select_args[] = "clients.name as clientname";
				$select_args[] = "depots.name as depotname";
				$select_args[] = "vehicle.veh_reg as veh_reg";
				$select_args[] = "vehicle.eng_no as eng_no";
				$select_args[] = "vehicle.chsno as chsno";
				$select_args[] = "lookuptypevalues.name as vehicle_type";
				$select_args[] = "vehicle.yearof_pur as yearof_pur";
				$select_args[] = "vehicle.seat_cap as seat_cap";
				$resp = array();
	
				$qry = \Contract::join("clients","clients.id","=","contracts.clientId")
								->join("depots","depots.id","=","contracts.depotId")
								->join("contract_vehicles","contract_vehicles.contractId","=","contracts.id")
								->join("vehicle","vehicle.id","=","contract_vehicles.vehicleId")
								->leftjoin("lookuptypevalues","lookuptypevalues.id","=","vehicle.vehicle_type")
								->where("contract_vehicles.status","=","ACTIVE")
								->where("contracts.status","=","ACTIVE");
				if($values["clientname"] != 0 && $values["depot"] != 0){
					$qry = $qry->where("contracts.clientId","=",$values["clientname"]);
					$qry = $qry->where("contracts.depotId","=",$values["depot"]);
				}
				else if($values["clientname"] != 0 && $values["depot"] == 0){
					$qry = $qry->where("contracts.clientId","=",$values["clientname"]);
				}
					
				$recs = $qry->select($select_args)->orderBy("contracts.clientId")->orderBy("contracts.depotId")->get();
				foreach($recs as  $rec) {
					$row = array();
					$row["cid"] = $rec->cid;
					$row["client"] = $rec->clientname;
					$row["branch"] = $rec->depotname;
					$row["vehicle"] = $rec->veh_reg;
					$row["engno"] = $rec->eng_no;
					$row["chsno"] = $rec->chsno;
					$row["vehtype"] = $rec->vehicle_type;
					$row["yearofpur"] = date("Y",strtotime($rec->yearof_pur));
					$row["seatingcap"] = $rec->seat_cap;
						
					$resp[] = $row;
				}
			}
			echo json_encode($resp);
			return;	
		}
		$values['bredcum'] = "CONTRACT VEHICLE REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads1 = array('CON ID', 'CLIENT','BRANCH', "VEHICLE", "ENGINE NO", "CHASSIS NO", "veh type", "year of pur", "seating capacity");
		$values["theads1"] = $theads1;
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "CLIENT HOLIDAYS WORKING REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
	
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		$clients_arr[0] = "ALL";
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$form_fields = array();
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"getFormData(this.value);"), "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reportfor", "value"=>"", "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.contractvehiclesreport', array("values"=>$values));
	}
	
	
	private function getVendorPaymentsReport($values)
	{
		if (\Request::isMethod('post'))
		{
			if ($values["reportfor"] == "getreport") {
				$frmDt= date("Y-m-d",strtotime($values["fromdate"]));
				$toDt= date("Y-m-d",strtotime($values["todate"]));
				$resp = array();
				$totincomes = 0;
				$totexpenses = 0;
				for($i=0;$i<2; $i++){
					$select_args = array();
					$select_args[] = "lookuptypevalues1.name as vendorname";
					$select_args[] = "officebranch.name as branchname";
					$select_args[] = "date as date";
					if($i==0){
						$select_args[] = "expensetransactions.remarks as remarks";
					}
					else{
						$select_args[] = "incometransactions.remarks as remarks";
					}
					$select_args[] = "amount as amount";
					$select_args[] = "employee.fullName as empname";
					$select_args[] = "workFlowStatus as wfstatus";
					$select_args[] = "paymentType as paymentType";
					$select_args[] = "chequeNumber as chequeNumber";
					$select_args[] = "bankAccount as bankAccountId";
					$recs = array();
					if($i==0){		
						$qry = \ExpenseTransaction::leftjoin("lookuptypevalues","lookuptypevalues.id","=","expensetransactions.lookupValueId")
											->leftjoin("lookuptypevalues as lookuptypevalues1","lookuptypevalues1.id","=","expensetransactions.entityValue")
											->leftjoin("employee","employee.id","=","expensetransactions.createdBy")
											->leftjoin("officebranch","officebranch.id","=","expensetransactions.branchId")
											->where("expensetransactions.status","=","ACTIVE")
											->where("expensetransactions.lookupValueId","=",339)
											->whereBetween("date",array($frmDt,$toDt));
											if($values["vendor"] != 0){
												$qry = $qry->where("entityValue","=",$values["vendor"]);
											}					
						$recs = $qry->select($select_args)->get();
					}
					else{
						$qry = \IncomeTransaction::leftjoin("lookuptypevalues","lookuptypevalues.id","=","incometransactions.lookupValueId")
											->leftjoin("lookuptypevalues as lookuptypevalues1","lookuptypevalues1.id","=","incometransactions.entityValue")
											->leftjoin("employee","employee.id","=","incometransactions.createdBy")
											->leftjoin("officebranch","officebranch.id","=","incometransactions.branchId")
											->where("incometransactions.status","=","ACTIVE")
											->whereBetween("date",array($frmDt,$toDt))
											->where("incometransactions.lookupValueId","=",340);
											if($values["vendor"] != 0){
												$qry = $qry->where("entityValue","=",$values["vendor"]);
											}
						$recs = $qry->select($select_args)->get();
					}
					foreach($recs as  $rec) {
						$row = array();
						$row["vendor"] = $rec->vendorname;
						$row["branch"] = $rec->branchname;
						if($i==0){
							$row["type"] = "EXPENSE";
							$totexpenses = $totexpenses+$rec->amount;
						}
						else{
							$row["type"] = "INCOME";
							$totincomes = $totincomes+$rec->amount;
						}
						$row["amount"] = $rec->amount;
						$row["date"] = date("d-m-Y",strtotime($rec->date));
						if($rec->paymentType != "cash"){
							if($rec->paymentType == "ecs" || $rec->paymentType == "neft" || $rec->paymentType == "rtgs" || $rec->paymentType == "cheque_debit" || $rec->paymentType == "cheque_credit"){
								$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
								$bank_dt = \BankDetails::where("id","=",$rec->bankAccountId)->first();
								if(count($bank_dt)>0){
									$rec->paymentType = $rec->paymentType."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
								}
								$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
							}
							if($rec->paymentType == "credit_card" || $rec->paymentType == "debit_card"){
								$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
								$bank_dt = \Cards::where("id","=",$rec->bankAccountId)->first();
								if(count($bank_dt)>0){
									$rec->paymentType = $rec->paymentType."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
								}
								$rec->paymentType = $rec->paymentType."<br/>Ref No : ".$rec->chequeNumber;
							}
							if($rec->paymentType == "dd"){
								$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
								$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
							}
						}
						$row["pmtinfo"] = $rec->paymentType;
						$row["remarks"] = $rec->remarks;
						$row["createdby"] = $rec->empname;
						$row["wfstatus"] = $rec->wfstatus;
						$resp[] = $row;
					}
				}
			}
			$resp_json = array("data"=>$resp,"total_expenses"=>$totexpenses,"total_incomes"=>$totincomes);
			echo json_encode($resp_json);
			return;
		}
		$values['bredcum'] = "VENDOR PAYMENTS REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads1 = array('VENDOR','BRANCH', "TYPE", "AMOUNT", "DATE", "PAYMENT INFO", "REMARKS",  "CREATED BY",  "WF STATUS");
		$values["theads"] = $theads1;
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "VENDOR PAYMENTS REPORT";
		$form_info["reporttype"] = $values["reporttype"];
		
		$parentId = \LookupTypeValues::where("name", "=", "VENDORS")->get();
		$vendors_arr = array();
		$vendors_arr[0] = "ALL"; 
		$vendors = array();
		if(count($parentId)>0){
			$parentId = $parentId[0];
			$parentId = $parentId->id;
			$vendors =  \LookupTypeValues::where("parentId","=",$parentId)->get();
				
		}
		foreach ($vendors as $vendor){
			$vendors_arr[$vendor->id] = $vendor->name;
		}
	
		$form_fields = array();
		$form_field = array("name"=>"vendor", "content"=>"vendor", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>$vendors_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reportfor", "value"=>"", "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.vendorpaymentsreport', array("values"=>$values));
	}
	private function getInsuranceReport($values)
	{
		if (\Request::isMethod('post'))
		{
			if ($values["reportfor"] == "getreport") {
				$frmDt= date("Y-m-d",strtotime($values["fromdate"]));
				$toDt= date("Y-m-d",strtotime($values["todate"]));
				$resp = array();
				$totincomes = 0;
				$totexpenses = 0;
				$select_args = array();
				$select_args[] = "lookuptypevalues1.name as companyname";
				$select_args[] = "officebranch.name as branchname";
				$select_args[] = "date as date";
				$select_args[] = "expensetransactions.remarks as remarks";
				$select_args[] = "amount as amount";
				$select_args[] = "employee.fullName as empname";
				$select_args[] = "workFlowStatus as wfstatus";
				$select_args[] = "paymentType as paymentType";
				$select_args[] = "chequeNumber as chequeNumber";
				$select_args[] = "bankAccount as bankAccountId";
				$recs = array();
				$qry = \ExpenseTransaction::leftjoin("lookuptypevalues","lookuptypevalues.id","=","expensetransactions.lookupValueId")
						->leftjoin("lookuptypevalues as lookuptypevalues1","lookuptypevalues1.id","=","expensetransactions.entityValue")
						->leftjoin("employee","employee.id","=","expensetransactions.createdBy")
						->leftjoin("officebranch","officebranch.id","=","expensetransactions.branchId")
						->where("expensetransactions.status","=","ACTIVE")
						->where("expensetransactions.lookupValueId","=",297)
						->whereBetween("date",array($frmDt,$toDt));
				if($values["insurance"] != 0){
					$qry = $qry->where("entityValue","=",$values["insurance"]);
				}
				$recs = $qry->select($select_args)->get();
				foreach($recs as  $rec) {
					$row = array();
					$row["insurance"] = $rec->companyname;
					$row["branch"] = $rec->branchname;
					$row["type"] = "EXPENSE";
					$totexpenses = $totexpenses+$rec->amount;
					$row["amount"] = $rec->amount;
					$row["date"] = date("d-m-Y",strtotime($rec->date));
					if($rec->paymentType != "cash"){
						if($rec->paymentType == "ecs" || $rec->paymentType == "neft" || $rec->paymentType == "rtgs" || $rec->paymentType == "cheque_debit" || $rec->paymentType == "cheque_credit"){
							$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
							$bank_dt = \BankDetails::where("id","=",$rec->bankAccountId)->first();
							if(count($bank_dt)>0){
								$rec->paymentType = $rec->paymentType."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
							}
							$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
						}
						if($rec->paymentType == "credit_card" || $rec->paymentType == "debit_card"){
							$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
							$bank_dt = \Cards::where("id","=",$rec->bankAccountId)->first();
							if(count($bank_dt)>0){
								$rec->paymentType = $rec->paymentType."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
							}
							$rec->paymentType = $rec->paymentType."<br/>Ref No : ".$rec->chequeNumber;
						}
						if($rec->paymentType == "dd"){
							$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
							$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
						}
					}
					$row["pmtinfo"] = $rec->paymentType;
					$row["remarks"] = $rec->remarks;
					$row["createdby"] = $rec->empname;
					$row["wfstatus"] = $rec->wfstatus;
					$resp[] = $row;
				}
			}
			$resp_json = array("data"=>$resp,"total_expenses"=>$totexpenses);
			echo json_encode($resp_json);
			return;
		}
		$values['bredcum'] = "INSURANCE REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads1 = array('COMPANY NAME','BRANCH', "TYPE", "AMOUNT", "DATE", "PAYMENT INFO", "REMARKS",  "CREATED BY",  "WF STATUS");
		$values["theads"] = $theads1;
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "VENDOR PAYMENTS REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
		$parentId = \LookupTypeValues::where("name", "=", "INSURANCE COMPANY")->get();
		$insurance_arr = array();
		$insurance_arr[0] = "ALL";
		$insurances = array();
		if(count($parentId)>0){
			$parentId = $parentId[0];
			$parentId = $parentId->id;
			$insurances =  \LookupTypeValues::where("parentId","=",$parentId)->get();
	
		}
		foreach ($insurances as $insurance){
			$insurance_arr[$insurance->id] = $insurance->name;
		}
	
		$form_fields = array();
		$form_field = array("name"=>"insurance", "content"=>"insurance company", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>$insurance_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reportfor", "value"=>"", "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.insurancereport', array("values"=>$values));
	}
	
	
	private function getGlobalLoansReport($values)
		{
			if (\Request::isMethod('post'))
			{
				if ($values["reportfor"] == "getreport") {
					$frmDt= date("Y-m-d",strtotime($values["fromdate"]));
					$toDt= date("Y-m-d",strtotime($values["todate"]));
					$resp = array();
					$totincomes = 0;
					$totexpenses = 0;
					for($i=0;$i<2; $i++){
						$select_args = array();
						$select_args[] = "lookuptypevalues1.name as vendorname";
						$select_args[] = "officebranch.name as branchname";
						$select_args[] = "date as date";
						if($i==0){
							$select_args[] = "expensetransactions.remarks as remarks";
						}
						else{
							$select_args[] = "incometransactions.remarks as remarks";
						}
						$select_args[] = "amount as amount";
						$select_args[] = "employee.fullName as empname";
						$select_args[] = "workFlowStatus as wfstatus";
						$select_args[] = "paymentType as paymentType";
						$select_args[] = "chequeNumber as chequeNumber";
						$select_args[] = "bankAccount as bankAccountId";
						$recs = array();
						if($i==0){
							$qry = \ExpenseTransaction::leftjoin("lookuptypevalues","lookuptypevalues.id","=","expensetransactions.lookupValueId")
											->leftjoin("lookuptypevalues as lookuptypevalues1","lookuptypevalues1.id","=","expensetransactions.entityValue")
											->leftjoin("employee","employee.id","=","expensetransactions.createdBy")
											->leftjoin("officebranch","officebranch.id","=","expensetransactions.branchId")
											->where("expensetransactions.status","=","ACTIVE")
											->whereBetween("date",array($frmDt,$toDt))
											->where("expensetransactions.lookupValueId","=",342);
							if($values["globalloan"] != 0){
								$qry = $qry->where("entityValue","=",$values["globalloan"]);
							}
							$recs = $qry->select($select_args)->get();
						}
						else{
							$qry = \IncomeTransaction::leftjoin("lookuptypevalues","lookuptypevalues.id","=","incometransactions.lookupValueId")
											->leftjoin("lookuptypevalues as lookuptypevalues1","lookuptypevalues1.id","=","incometransactions.entityValue")
											->leftjoin("employee","employee.id","=","incometransactions.createdBy")
											->leftjoin("officebranch","officebranch.id","=","incometransactions.branchId")
											->where("incometransactions.status","=","ACTIVE")
											->whereBetween("date",array($frmDt,$toDt))
											->where("incometransactions.lookupValueId","=",343);
											if($values["globalloan"] != 0){
												$qry = $qry->where("entityValue","=",$values["globalloan"]);
											}
							$recs = $qry->select($select_args)->get();
						}
						foreach($recs as  $rec) {
							$row = array();
							$row["vendor"] = $rec->vendorname;
							$row["branch"] = $rec->branchname;
							if($i==0){
								$row["type"] = "EXPENSE";
								$totexpenses = $totexpenses+$rec->amount;
							}
							else{
								$row["type"] = "INCOME";
								$totincomes = $totincomes+$rec->amount;
							}
							$row["amount"] = $rec->amount;
							$row["date"] = date("d-m-Y",strtotime($rec->date));
							if($rec->paymentType != "cash"){
								if($rec->paymentType == "ecs" || $rec->paymentType == "neft" || $rec->paymentType == "rtgs" || $rec->paymentType == "cheque_debit" || $rec->paymentType == "cheque_credit"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$bank_dt = \BankDetails::where("id","=",$rec->bankAccountId)->first();
									if(count($bank_dt)>0){
										$rec->paymentType = $rec->paymentType."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
									}
									$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
								}
								if($rec->paymentType == "credit_card" || $rec->paymentType == "debit_card"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$bank_dt = \Cards::where("id","=",$rec->bankAccountId)->first();
									if(count($bank_dt)>0){
										$rec->paymentType = $rec->paymentType."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
									}
									$rec->paymentType = $rec->paymentType."<br/>Ref No : ".$rec->chequeNumber;
								}
								if($rec->paymentType == "dd"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
								}
							}
							$row["pmtinfo"] = $rec->paymentType;
							$row["remarks"] = $rec->remarks;
							$row["createdby"] = $rec->empname;
							$row["wfstatus"] = $rec->wfstatus;
							$resp[] = $row;
						}
					}
				}
				$resp_json = array("data"=>$resp,"total_expenses"=>$totexpenses,"total_incomes"=>$totincomes);
				echo json_encode($resp_json);
				return;
			}
			$values['bredcum'] = "GLOBAL LOANS REPORT";
			$values['home_url'] = 'masters';
			$values['add_url'] = 'loginlog';
			$values['form_action'] = 'loginlog';
			$values['action_val'] = '#';
			$theads1 = array('GLOBAL LOAN','BRANCH', "TYPE", "AMOUNT", "DATE", "PAYMENT INFO", "REMARKS",  "CREATED BY",  "WF STATUS");
			$values["theads"] = $theads1;
			//$values["test"];
		
			$form_info = array();
			$form_info["name"] = "getreport";
			$form_info["action"] = "getreport";
			$form_info["method"] = "post";
			$form_info["class"] = "form-horizontal";
			$form_info["back_url"] = "users";
			$form_info["bredcum"] = "GLOBAL LOANS REPORT";
			$form_info["reporttype"] = $values["reporttype"];
		
			$parentId = \LookupTypeValues::where("name", "=", "GLOBAL LOANS")->get();
			$vendors_arr = array();
			$vendors_arr[0] = "ALL";
			$vendors = array();
			if(count($parentId)>0){
				$parentId = $parentId[0];
				$parentId = $parentId->id;
				$vendors =  \LookupTypeValues::where("parentId","=",$parentId)->get();
		
			}
			foreach ($vendors as $vendor){
				$vendors_arr[$vendor->id] = $vendor->name;
			}
		
			$form_fields = array();
			$form_field = array("name"=>"globalloan", "content"=>"global loan", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>$vendors_arr);
			$form_fields[] = $form_field;
			$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
			$form_fields[] = $form_field;
			$form_field = array("name"=>"reportfor", "value"=>"", "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
			$form_fields[] = $form_field;
			$form_info["form_fields"] = $form_fields;
			$values['form_info'] = $form_info;
		
			$form_info["form_fields"] = array();
			$modals[] = $form_info;
			$values["modals"] = $modals;
			//$values['provider'] = "loginlog";
		
			return View::make('reports.globalloansreport', array("values"=>$values));
		}
	
	private function getVehicleRenewalsReport($values)
	{
		if (\Request::isMethod('post'))
		{
			if ($values["reportfor"] == "getreport") {
				$frmDt = date("Y-m-d",strtotime($values["fromdate"])); 
				$toDt  = date("Y-m-d",strtotime($values["todate"])); 
				$resp = array();
				$totexpenses = 0;
				$select_args = array();
				$select_args[] = "vehicle.veh_reg as veh_reg";
				$select_args[] = "officebranch.name as branchname";
				$select_args[] = "lookuptypevalues.name as renewaltype";
				$select_args[] = "lookuptypevalues1.name as entityValue";
				$select_args[] = "date as date";
				$select_args[] = "nextAlertDate as nextAlertDate";
				$select_args[] = "amount as amount";
				$select_args[] = "employee.fullName as empname";
				$select_args[] = "workFlowStatus as wfstatus";
				$select_args[] = "paymentType as paymentType";
				$select_args[] = "chequeNumber as chequeNumber";
				$select_args[] = "bankAccount as bankAccountId";
				$select_args[] = "expensetransactions.remarks as remarks";
				$recs = array();
				$qry = \ExpenseTransaction::leftjoin("lookuptypevalues","lookuptypevalues.id","=","expensetransactions.lookupValueId")
								->leftjoin("lookuptypevalues as lookuptypevalues1","lookuptypevalues1.id","=","expensetransactions.entityValue")
								->leftjoin("employee","employee.id","=","expensetransactions.createdBy")
								->leftjoin("officebranch","officebranch.id","=","expensetransactions.branchId")
								->leftjoin("vehicle","vehicle.id","=","expensetransactions.vehicleIds")
								->where("expensetransactions.status","=","ACTIVE")
								->orderBy("date","desc");
				if($values["renewaltype"] != 0){
					$qry = $qry->where("expensetransactions.lookupValueId","=",$values["renewaltype"]);
				}
				else{
					$lookup_arr = array(297,302,300,301,272);
					$qry = $qry->whereIn("expensetransactions.lookupValueId",$lookup_arr);
				}
				
				$recs = $qry->whereBetween("expensetransactions.date",array($frmDt,$toDt))->select($select_args)->orderBy("vehicle.veh_reg","asc")->get();
				//print_r($recs);die();
				$i=0;
				$veh_reg = "";
				
				$row = array();
				$row["vehicle"] = "";
				$row["branch"] = "";
				$row["roadtax"] = "";
				$row["insurance"] = "";
				$row["pollution"] = "";
				$row["permit"] = "";
				$row["fitness"] = "";
				$row["amount"] = "";				
				$row["pmtinfo"] = "";
				$row["remarks"] = "";
				$row["createdby"] = "";
				$row["wfstatus"] = "";
				if(count($recs)>0){
					$veh_reg = $recs[0]->veh_reg;
				}				
				for($i=0;$i<count($recs); $i++) {
					$rec = $recs[$i];
					if($rec->veh_reg==$veh_reg){
						$row["vehicle"] = $rec->veh_reg."<br/>";
						$row["branch"] = $row["branch"].$rec->branchname."<br/>";
						if($rec->renewaltype == "INSURANCE"){
							$row["insurance"] = $row["insurance"]."Insurance Company : ".$rec->entityValue."<br/>Paid Date : ".date("d-m-Y",strtotime($rec->date))."<br/>Renewal Date : ".date("d-m-Y",strtotime($rec->nextAlertDate))."<br/>";
							$totexpenses = $totexpenses+$rec->amount;
							$row["insurance"] = $row["insurance"]."Paid Amount : ".$rec->amount."<br/>";
							if($rec->paymentType != "cash"){
								if($rec->paymentType == "ecs" || $rec->paymentType == "neft" || $rec->paymentType == "rtgs" || $rec->paymentType == "cheque_debit" || $rec->paymentType == "cheque_credit"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$bank_dt = \BankDetails::where("id","=",$rec->bankAccountId)->first();
									if(count($bank_dt)>0){
										$rec->paymentType = $rec->paymentType."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
									}
									$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
								}
								if($rec->paymentType == "credit_card" || $rec->paymentType == "debit_card"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$bank_dt = \Cards::where("id","=",$rec->bankAccountId)->first();
									if(count($bank_dt)>0){
										$rec->paymentType = $rec->paymentType."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
									}
									$rec->paymentType = $rec->paymentType."<br/>Ref No : ".$rec->chequeNumber;
								}
								if($rec->paymentType == "dd"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
								}
							}
							$row["insurance"] = $row["insurance"]."Payment Info : ".$rec->paymentType."<br/>";
							$row["insurance"] = $row["insurance"]."Remarks : ".$rec->remarks."<br/>";
							$row["insurance"] = $row["insurance"]."Created By : ".$rec->empname."<br/>";
							$row["insurance"] = $row["insurance"]."WorkFlow Status : ".$rec->wfstatus."<br/>";
							$row["insurance"] = $row["insurance"]."branch name : ".$rec->branchname."<br/><br/>";
						}
						if($rec->renewaltype == "ROAD TAX"){
							$row["roadtax"] = $row["roadtax"]."Paid Date : ".date("d-m-Y",strtotime($rec->date))."<br/>Renewal Date : ".date("d-m-Y",strtotime($rec->nextAlertDate))."<br/>";
							$totexpenses = $totexpenses+$rec->amount;
							$row["roadtax"] = $row["roadtax"]."Paid Amount : ".$rec->amount."<br/>";
							if($rec->paymentType != "cash"){
								if($rec->paymentType == "ecs" || $rec->paymentType == "neft" || $rec->paymentType == "rtgs" || $rec->paymentType == "cheque_debit" || $rec->paymentType == "cheque_credit"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$bank_dt = \BankDetails::where("id","=",$rec->bankAccountId)->first();
									if(count($bank_dt)>0){
										$rec->paymentType = $rec->paymentType."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
									}
									$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
								}
								if($rec->paymentType == "credit_card" || $rec->paymentType == "debit_card"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$bank_dt = \Cards::where("id","=",$rec->bankAccountId)->first();
									if(count($bank_dt)>0){
										$rec->paymentType = $rec->paymentType."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
									}
									$rec->paymentType = $rec->paymentType."<br/>Ref No : ".$rec->chequeNumber;
								}
								if($rec->paymentType == "dd"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
								}
							}
							$row["roadtax"] = $row["roadtax"]."Payment Info : ".$rec->paymentType."<br/>";
							$row["roadtax"] = $row["roadtax"]."Remarks : ".$rec->remarks."<br/>";
							$row["roadtax"] = $row["roadtax"]."Created By : ".$rec->empname."<br/>";
							$row["roadtax"] = $row["roadtax"]."WorkFlow Status : ".$rec->wfstatus."<br/>";
							$row["roadtax"] = $row["roadtax"]."branch name : ".$rec->branchname."<br/><br/>";
						}
						if($rec->renewaltype == "POLLUTION"){
							$row["pollution"] = $row["pollution"]."Paid Date : ".date("d-m-Y",strtotime($rec->date))."<br/>Renewal Date : ".date("d-m-Y",strtotime($rec->nextAlertDate))."<br/>";
							$totexpenses = $totexpenses+$rec->amount;
							$row["pollution"] = $row["pollution"]."Paid Amount : ".$rec->amount."<br/>";
							if($rec->paymentType != "cash"){
								if($rec->paymentType == "ecs" || $rec->paymentType == "neft" || $rec->paymentType == "rtgs" || $rec->paymentType == "cheque_debit" || $rec->paymentType == "cheque_credit"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$bank_dt = \BankDetails::where("id","=",$rec->bankAccountId)->first();
									if(count($bank_dt)>0){
										$rec->paymentType = $rec->paymentType."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
									}
									$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
								}
								if($rec->paymentType == "credit_card" || $rec->paymentType == "debit_card"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$bank_dt = \Cards::where("id","=",$rec->bankAccountId)->first();
									if(count($bank_dt)>0){
										$rec->paymentType = $rec->paymentType."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
									}
									$rec->paymentType = $rec->paymentType."<br/>Ref No : ".$rec->chequeNumber;
								}
								if($rec->paymentType == "dd"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
								}
							}
							$row["pollution"] = $row["pollution"]."Payment Info : ".$rec->paymentType."<br/>";
							$row["pollution"] = $row["pollution"]."Remarks : ".$rec->remarks."<br/>";
							$row["pollution"] = $row["pollution"]."Created By : ".$rec->empname."<br/>";
							$row["pollution"] = $row["pollution"]."WorkFlow Status : ".$rec->wfstatus."<br/>";
							$row["pollution"] = $row["pollution"]."branch name : ".$rec->branchname."<br/><br/>";
						}
						if($rec->renewaltype == "PERMIT"){
							$row["permit"] = $row["permit"]."Paid Date : ".date("d-m-Y",strtotime($rec->date))."<br/>Renewal Date : ".date("d-m-Y",strtotime($rec->nextAlertDate))."<br/>";
							$totexpenses = $totexpenses+$rec->amount;
							$row["permit"] = $row["permit"]."Paid Amount : ".$rec->amount."<br/>";
							if($rec->paymentType != "cash"){
								if($rec->paymentType == "ecs" || $rec->paymentType == "neft" || $rec->paymentType == "rtgs" || $rec->paymentType == "cheque_debit" || $rec->paymentType == "cheque_credit"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$bank_dt = \BankDetails::where("id","=",$rec->bankAccountId)->first();
									if(count($bank_dt)>0){
										$rec->paymentType = $rec->paymentType."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
									}
									$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
								}
								if($rec->paymentType == "credit_card" || $rec->paymentType == "debit_card"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$bank_dt = \Cards::where("id","=",$rec->bankAccountId)->first();
									if(count($bank_dt)>0){
										$rec->paymentType = $rec->paymentType."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
									}
									$rec->paymentType = $rec->paymentType."<br/>Ref No : ".$rec->chequeNumber;
								}
								if($rec->paymentType == "dd"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
								}
							}
							$row["permit"] = $row["permit"]."Payment Info : ".$rec->paymentType."<br/>";
							$row["permit"] = $row["permit"]."Remarks : ".$rec->remarks."<br/>";
							$row["permit"] = $row["permit"]."Created By : ".$rec->empname."<br/>";
							$row["permit"] = $row["permit"]."WorkFlow Status : ".$rec->wfstatus."<br/>";
							$row["permit"] = $row["permit"]."branch name : ".$rec->branchname."<br/><br/>";
						}
						if($rec->renewaltype == "FITNESS"){
							$row["fitness"] = $row["fitness"]."Paid Date : ".date("d-m-Y",strtotime($rec->date))."<br/>Renewal Date : ".date("d-m-Y",strtotime($rec->nextAlertDate))."<br/>";
							$totexpenses = $totexpenses+$rec->amount;
							$row["fitness"] = $row["fitness"]."Paid Amount : ".$rec->amount."<br/>";
							if($rec->paymentType != "cash"){
								if($rec->paymentType == "ecs" || $rec->paymentType == "neft" || $rec->paymentType == "rtgs" || $rec->paymentType == "cheque_debit" || $rec->paymentType == "cheque_credit"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$bank_dt = \BankDetails::where("id","=",$rec->bankAccountId)->first();
									if(count($bank_dt)>0){
										$rec->paymentType = $rec->paymentType."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
									}
									$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
								}
								if($rec->paymentType == "credit_card" || $rec->paymentType == "debit_card"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$bank_dt = \Cards::where("id","=",$rec->bankAccountId)->first();
									if(count($bank_dt)>0){
										$rec->paymentType = $rec->paymentType."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
									}
									$rec->paymentType = $rec->paymentType."<br/>Ref No : ".$rec->chequeNumber;
								}
								if($rec->paymentType == "dd"){
									$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
									$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
								}
							}
							$row["fitness"] = $row["fitness"]."Payment Info : ".$rec->paymentType."<br/>";
							$row["fitness"] = $row["fitness"]."Remarks : ".$rec->remarks."<br/>";
							$row["fitness"] = $row["fitness"]."Created By : ".$rec->empname."<br/>";
							$row["fitness"] = $row["fitness"]."WorkFlow Status : ".$rec->wfstatus."<br/>";
							$row["fitness"] = $row["fitness"]."branch name : ".$rec->branchname."<br/><br/>";
						}
					}
					else{
						$resp[] = $row;
						$veh_reg = $rec->veh_reg;
						$row = array();
						$row["vehicle"] = "";
						$row["branch"] = "";
						$row["roadtax"] = "";
						$row["insurance"] = "";
						$row["pollution"] = "";
						$row["permit"] = "";
						$row["fitness"] = "";
						$row["amount"] = "";
						$row["pmtinfo"] = "";
						$row["remarks"] = "";
						$row["createdby"] = "";
						$row["wfstatus"] = "";
						$i--;
					}
				}
				$resp[] = $row;
			}
			//print_r($resp);die();
			$resp_json = array("data"=>$resp,"total_expenses"=>$totexpenses);
			echo json_encode($resp_json);
			return;
		}
		$values['bredcum'] = "VEHICLE RENEWALS REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		//$theads1 = array('VEHICLE REG','BRANCH',"ROAD TAX", "INSURANCE","POLLUTION","PERMIT","FITNESS", "AMOUNT", "PAYMENT INFO", "REMARKS",  "CREATED BY",  "WF STATUS");
		$theads1 = array('VEHICLE REG','BRANCH',"ROAD TAX", "INSURANCE","POLLUTION","PERMIT","FITNESS");
		$values["theads"] = $theads1;
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "VEHICLE RENEWALS REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
		$vendors_arr = array();
		$vendors_arr[0] = "ALL";
		$vendors_arr["297"] = "INSURANCE";
		$vendors_arr["300"] = "POLLUTION";
		$vendors_arr["301"] = "PERMIT";
		$vendors_arr["302"] = "FITNESS";
		$vendors_arr["272"] = "ROAD TAX";
	
		$form_fields = array();
		$form_field = array("name"=>"renewaltype", "content"=>"renewal type", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>$vendors_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reportfor", "value"=>"", "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.vehiclerenewalsreport', array("values"=>$values));
	}
	
	private function getCardsPaymentReport($values)
	{
		if (\Request::isMethod('post'))
		{
			if ($values["reportfor"] == "getreport") {
				$frmDt = date("Y-m-d",strtotime($values["fromdate"]));
				$toDt  = date("Y-m-d",strtotime($values["todate"]));
				$resp = array();
				$totexpenses = 0;
				DB::statement(DB::raw("CALL bankposition_report('".$frmDt."', '".$toDt."');"));
				$sql = "SELECT rowid as rowid, officebranch.name as branch,";
				$sql = $sql." bankdetails.bankName as bankName, ";
				$sql = $sql." bankdetails.branchName as branchName, ";
				$sql = $sql." bankdetails.accountNo as accountNo, ";
				$sql = $sql." temp_bankposition_transaction.name as name, ";
				$sql = $sql." temp_bankposition_transaction.type as type, ";
				$sql = $sql." lookuptypevalues1.name as entityValue1, ";
				$sql = $sql." temp_bankposition_transaction.workFlowStatus as wfstatus, ";
				$sql = $sql." paymentType, date, chequeNumber, amount,  lookupValueId, entity, bankAccountId, ";
				$sql = $sql." entityValue, temp_bankposition_transaction.createdBy as createdBy, temp_bankposition_transaction.remarks as remarks ";
				$sql = $sql." FROM temp_bankposition_transaction ";
				$sql = $sql."left join officebranch on officebranch.id=temp_bankposition_transaction.branchId ";
				$sql = $sql."left join 	lookuptypevalues on lookuptypevalues.id=temp_bankposition_transaction.lookupValueId ";
				$sql = $sql."left join 	lookuptypevalues as lookuptypevalues1 on lookuptypevalues1.id=temp_bankposition_transaction.entityValue ";
				$sql = $sql."left join bankdetails on bankdetails.id=temp_bankposition_transaction.bankAccountId ";
				if($values["card"]==0){
					if($values["cardtype"]=="CREDIT CARD"){
						$sql = $sql."where (paymentType in ('credit_card') or lookupValueId=283) ";
					}
					else{
						$sql = $sql."where paymentType in ('hp_card')";
					}
				}
				else{
					if($values["cardtype"]=="CREDIT CARD"){
						$sql = $sql."where (paymentType in ('credit_card')  and  bankAccountId=".$values["card"]." ) or (lookupValueId=283 and  entityValue=".$values["card"].") ";
					}
					else{
						$sql = $sql."where paymentType in ('hp_card')";
					}
					//$sql = $sql."where (paymentType in ('credit_card','hp_card')  and  bankAccountId=".$values["card"]." ) or (lookupValueId=283 and  entityValue=".$values["card"].") ";
				}
				
				/*if($values["bank"] == "0" && $values["branch"] == "0"){
					$sql = $sql."where paymentType!='credit_card' and paymentType!='debit_card' ";
				}
				else if($values["bank"] == "0" && $values["branch"] > 0){
					$sql = $sql."where branchId=".$values["branch"]." and paymentType!='credit_card' and paymentType!='debit_card' ";
				}
				else if($values["bank"] != "0" && $values["branch"]== "0"){
					$sql = $sql." where bankdetails.bankName='".$values["bank"]."' and paymentType!='credit_card' and paymentType!='debit_card' ";
				}
				else if($values["bank"] != "0" &&  $values["branch"] > 0){
					$sql = $sql." where branchId=".$values["branch"]." and bankdetails.bankName='".$values["bank"]."' and paymentType!='credit_card' and paymentType!='debit_card' ";
				}*/
				$recs = DB::select( DB::raw($sql));
				$ex_ref_nos = array();
				$paid_to_card = 0;
				foreach ($recs as $rec){
					$row = array();
// 					$select_args[] = "officebranch.name as branch";
// 					$select_args[] = "bankdetails.bankName as bankName";
// 					$select_args[] = "bankdetails.branchName as branchName";
// 					$select_args[] = "bankdetails.accountNo as accountNo";
					$row["branch"] = $rec->branch;
					if($row["branch"] == ""){
						$client_branch_str = "";
						$clients = \FuelTransaction::where("fueltransactions.id","=",$rec->rowid)
													->leftjoin("contracts","contracts.id","=","fueltransactions.contractId")
													->join("depots","depots.id","=","contracts.depotId")
													->join("clients","clients.id","=","contracts.clientId")
													->select(array("clients.name as cname","depots.name as dname"))->get();
						foreach ($clients as $client){
							$client_branch_str = $client_branch_str.$client->cname." (".$client->dname.")<br/>";
						}
						$row["branch"] = $client_branch_str;
					}
					
					
					$purpose_str = "";
					$amt_str = "";
					$ref_status = false;
					if(!in_array($rec->chequeNumber, $ex_ref_nos)){
// 						if($rec->chequeNumber != "" && !in_array($rec->chequeNumber, $ex_ref_nos) && $rec->lookupValueId!=283){
// 							$ex_ref_nos[] = $rec->chequeNumber;
// 							$sql1 = $sql." and chequeNumber='".$rec->chequeNumber."';";
// 							$recs1 = DB::select( DB::raw($sql1));
// 							foreach ($recs1 as $rec1){
// 								$purpose_str = $purpose_str.strtoupper($rec1->name)."<br/>";
// 								if($rec1->entity != ""){
// 									$purpose_str = $purpose_str." (".strtoupper($rec1->entity)."-".strtoupper($rec1->entityValue1).")<br/>";
// 								}
// 								if($rec1->name == "SALARY TRANSACTION"){
// 									$purpose_str = $purpose_str.strtoupper($rec1->name)."<br/>Paid On ".date("d-m-Y", strtotime($rec1->entity))." TO ".strtoupper($rec1->entityValue)."<br/>";
// 								}
// 								if($rec1->lookupValueId==991|| $rec1->lookupValueId==996){
// 									$purpose_str = $purpose_str.strtoupper($rec1->entity)."<br/>";
// 								}
// 								if($rec1->lookupValueId==283){
// 									$card = \Cards::where("id","=",$rec1->entityValue)->get();
// 									if(count($card)>0){
// 										$card = $card[0];
// 										$purpose_str = strtoupper($rec1->entity)."<br/>CARD NO : ".$card->cardNumber."<br/>";
// 									}
// 								}
// 								$amt_str = $amt_str.$rec1->amount."<br/>";
// 								$totexpenses = $totexpenses+$rec1->amount;
// 								$ref_status = true;
// 							}
// 						}
						if($purpose_str != ""){
							$row["purpose"] = $purpose_str;
						}
						else{
							$row["purpose"] = strtoupper($rec->name);
							if($rec->entity != ""){
								$row["purpose"] = $row["purpose"]." (".strtoupper($rec->entity)."-".strtoupper($rec->entityValue1).")";
							}
							if($rec->lookupValueId==283){
								$card = \Cards::where("id","=",$rec->entityValue)->get();
								if(count($card)>0){
									$card = $card[0];
									$purpose_str = strtoupper($rec->entity)."<br/>CARD NO : ".$card->cardNumber."<br/>";
									$row["purpose"] = $purpose_str;
								}
							}
						}
						if($rec->name == "SALARY TRANSACTION"){
							$row["purpose"] = strtoupper($rec->name)."<br/>Paid On ".date("d-m-Y", strtotime($rec->entity))." TO ".strtoupper($rec->entityValue);
						}
						if($rec->lookupValueId==991|| $rec->lookupValueId==996){
							$row["purpose"] = strtoupper($rec->entity);
						}
						if($rec->lookupValueId==251 || $rec->lookupValueId==161){
							if($rec->lookupValueId==251){
								$incharge = \ExpenseTransaction::leftjoin("employee","employee.id","=","expensetransactions.inchargeId")
												->where("transactionId","=",$rec->rowid)
												->select(array("employee.fullName"))->get();
								if(count($incharge)>0){
									$incharge = $incharge[0];
									$row["purpose"] = $row["purpose"]." (".$incharge->fullName.")";
								}
							}
							if($rec->lookupValueId==161){
								$incharge = \IncomeTransaction::leftjoin("employee","employee.id","=","incometransactions.inchargeId")
												->where("transactionId","=",$rec->rowid)
												->select(array("employee.fullName"))->get();
								if(count($incharge)>0){
									$incharge = $incharge[0];
									$row["purpose"] = $row["purpose"]." (".$incharge->fullName.")";
								}
							}
						}
						if($rec->lookupValueId==997){
							$fuelstation = \FuelStation::where("id","=",$rec->entityValue)->get();
							if(count($fuelstation)>0){
								$fuelstation = $fuelstation[0];
								$row["purpose"] = "FUEL STATION PAYMENT (".$fuelstation->name.")";
							}
						}
						if($rec->lookupValueId==998){
							$creditSupplier = \CreditSupplier::where("id","=",$rec->entityValue)->get();
							if(count($creditSupplier)>0){
								$creditSupplier = $creditSupplier[0];
								$row["purpose"] = "CREDIT SUPPLIER PAYMENT (".$creditSupplier->supplierName.")";
							}
						}
// 						if($purpose_str != ""){
// 							$row["amount"] = $amt_str;
// 						}
// 						else{
						if($rec->lookupValueId==283){
							$row["amount"] = $rec->amount;
							$paid_to_card=$paid_to_card+$row["amount"];
						}
						else{
							$row["amount"] = $rec->amount;
							$totexpenses = $totexpenses+$row["amount"];
						}
						
// 						if(!$ref_status){
// 							$totexpenses = $totexpenses+$row["amount"];
// 						}
						$row["date"] = date("d-m-Y", strtotime($rec->date)); //." (".$rec->wfstatus.")";
						if($rec->paymentType != "cash" && $rec->lookupValueId==283){
							if($rec->paymentType == "ecs" || $rec->paymentType == "neft" || $rec->paymentType == "rtgs" || $rec->paymentType == "cheque_debit" || $rec->paymentType == "cheque_credit"){
								$row["pmtinfo"] = "Payment Type : ".$rec->paymentType."<br/>";
								$bank_dt = \BankDetails::where("id","=",$rec->bankAccountId)->first();
								if(count($bank_dt)>0){
									$row["pmtinfo"] = $row["pmtinfo"]."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
								}
								$row["pmtinfo"] = $row["pmtinfo"]."Ref No : ".$rec->chequeNumber;
							}
							else if($rec->paymentType == "credit_card" || $rec->paymentType == "debit_card" ||$rec->paymentType == "hp_card"){
								$row["pmtinfo"] = "Payment Type : ".$rec->paymentType."<br/>";
								$bank_dt = \Cards::where("id","=",$rec->entityValue)->first();
								if(count($bank_dt)>0){
									$row["pmtinfo"] = $row["pmtinfo"]."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
								}
								$row["pmtinfo"] = $row["pmtinfo"]."Ref No : ".$rec->chequeNumber;
							}
							else if($rec->paymentType == "dd"){
								$row["pmtinfo"] = "Payment Type : ".$rec->paymentType."<br/>";
								$row["pmtinfo"] = $row["pmtinfo"]."Ref No : ".$rec->chequeNumber;
							}
							else{
								$row["pmtinfo"] = "cash";
							}
						}
						else{
							$row["pmtinfo"] = "PAYMENT TYPE : ".strtoupper($rec->paymentType)."</br>";
							if($rec->paymentType == "credit_card"){
								$card = \Cards::where("id","=",$rec->bankAccountId)->get();
								if(count($card)>0){
									$card = $card[0];
									$row["pmtinfo"] = $row["pmtinfo"]."CARD NO : ".$card->cardNumber."<br/>";
								}
							}
							if($rec->chequeNumber!=""){
								$row["pmtinfo"] = $row["pmtinfo"]."REF NUM : ".$rec->chequeNumber;
							}
							if($rec->paymentType=="cheque_credit" || $rec->paymentType=="cheque_debit"){
								$row["pmtinfo"] = $row["pmtinfo"]."CHQUE NUM : ".$rec->chequeNumber;
							}
							
						}
						//$row["obalance"] = "0.00";
						//$row["cbalance"] = "0.00";
						$row["desc"] = $rec->remarks;
						$row["createdby"] = $rec->createdBy;
						$row["wfstatus"] = $rec->wfstatus;
						$resp[] = $row;
					}
				}
			}
			$resp_json = array("data"=>$resp,"total_expenses"=>$totexpenses,"paid_to_card"=>$paid_to_card);
			echo json_encode($resp_json);
			return;
		}
		$values['bredcum'] = "CARDS PAYMENT REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads1 = array('BRANCH', "PURPOSE", "AMOUNT", "DATE", "PAYMENT INFO", "REMARKS", "CREATED BY", "WF STATUS");
		$values["theads"] = $theads1;
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "VEHICLE RENEWALS REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$form_field = array("name"=>"cardtype", "value"=>"cash", "content"=>"card type", "readonly"=>"",  "action"=>array("type"=>"onchange","script"=>"changeCards(this.value);"), "required"=>"required", "type"=>"select", "class"=>"form-control select2",  "options"=>array("CREDIT CARD"=>"CREDIT CARD","HP CARD"=>"HP CARD"));
		$form_fields[] = $form_field;
		$form_field = array("name"=>"card", "content"=>"card", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reportfor", "value"=>"", "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.cardspaymentreport', array("values"=>$values));
	}
	
	private function getExtraKmsReport($values)
	{
	if (\Request::isMethod('post'))
		{
			if(!isset($values["fromdate"])){
				$values["fromdate"] = "10-10-2013";
			}
			if(!isset($values["todate"])){
				$values["todate"] = date("d-m-Y");
			}
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			if($values["reporttype"] == "extrakmsreport"){	
				$select_args = array();
				$select_args[] = "depots.name as depotsname";
				$select_args[] = "vehicle.veh_reg as veh_reg";
				$select_args[] = "service_logs.serviceDate as serviceDate";
				$select_args[] = "service_logs.startReading as startReading";
				$select_args[] = "service_logs.endReading as endReading";
				$select_args[] = "service_logs.serviceDate as serviceDate";
				$select_args[] = "contracts.avgKms as avgKms";
				$contract = \Contract::where("clientId","=",$values["clientname"])->where("depotId","=",$values["depot"])->first();
				$recs = \ServiceLog::leftjoin("contract_vehicles","contract_vehicles.id","=","service_logs.contractVehicleId")
							->leftjoin("vehicle","contract_vehicles.vehicleId","=","vehicle.id")
							->leftjoin("contracts","contracts.id","=","service_logs.contractId")
							->leftjoin("depots","depots.id","=","contracts.depotId")
							->where("service_logs.contractId","=",$contract->id)
							->where("contract_vehicles.contractId","=",$contract->id)
							->whereBetween("service_logs.serviceDate",array($frmDt,$toDt))->select($select_args)
							->orderBy("contractVehicleId")->orderBy("serviceDate")->get();
				$start_reading = 0;
				$end_reading = 0;
				$working_days = 1;
				$prev_date = "";
				$vehicle = "";
				
				$tot_holidaykms = 0;
				$tot_lesser_kms = 0;
				$tot_excess_kms = 0;
				if(count($recs)>0){
					$start_reading = $recs[0]->startReading;
					$vehicle = $recs[0]->veh_reg;
					$prev_date = $recs[0]->serviceDate;
				}
				$i = 0;
				$sno = 1;
				foreach ($recs as $rec) {
					if($i+1<count($recs)){
						if($recs[$i+1]->veh_reg != $vehicle){
							$working_days++;
							$row = array();
							$row["sno"] = $sno;
							$row["veh_reg"] = $rec->veh_reg;
							$row["depotsname"] = $rec->depotsname;
							$row["startReading"] = $start_reading;
							$row["endReading"] = $rec->endReading;
							$row["totalkms"] = $rec->endReading-$start_reading;
							$row["holidaykms"] = 0;
							$row["billingkms"] = ($rec->endReading-$start_reading)-$row["holidaykms"];
							$row["workingdays"] = $working_days;
							$row["conrunningdays"] = $rec->avgKms;
							$row["kmspercon"] = $working_days*$rec->avgKms;
							$row["usedkms"] = $row["billingkms"]-$row["kmspercon"];
							$row["lesserkms"] = 0;
							$row["excesskms"] = 0;
							if($row["usedkms"]<0){
								$row["lesserkms"] = $row["usedkms"];
								$tot_lesser_kms = $tot_lesser_kms+$row["usedkms"];
							}
							else{
								$row["excesskms"] = $row["usedkms"];
								$tot_excess_kms = $tot_excess_kms+$row["usedkms"];
							}
							$row["excessbill"] = $row["usedkms"];
							$resp[] = $row;
							$start_reading = $recs[$i+1]->startReading;
							$vehicle = $recs[$i+1]->veh_reg;
							$working_days = 1;
							$prev_date = $recs[$i+1]->serviceDate;
							$sno++;
						}
					}
					else if($i+1 == count($recs)){
						$row = array();
						$row["sno"] = $sno;
						$row["veh_reg"] = $rec->veh_reg;
						$row["depotsname"] = $rec->depotsname;
						$row["startReading"] = $start_reading;
						$row["endReading"] = $rec->endReading;
						$row["totalkms"] = $rec->endReading-$start_reading;
						$row["holidaykms"] = 0;
						$row["billingkms"] = ($rec->endReading-$start_reading)-$row["holidaykms"];
						$row["workingdays"] = $working_days;
						$row["conrunningdays"] = $rec->avgKms;
						$row["kmspercon"] = $working_days*$rec->avgKms;
						$row["usedkms"] = $row["billingkms"]-$row["kmspercon"];
						$row["lesserkms"] = 0;
						$row["excesskms"] = 0;
						if($row["usedkms"]<0){
							$row["lesserkms"] = $row["usedkms"];
							$tot_lesser_kms = $tot_lesser_kms+$row["usedkms"];
						}
						else{
							$row["excesskms"] = $row["usedkms"];
							$tot_excess_kms = $tot_excess_kms+$row["usedkms"];
						}
						$row["excessbill"] = $row["usedkms"];
						$resp[] = $row;
					}
					if($prev_date != $rec->serviceDate){
						$working_days++;
					}
					$i++;
				}
				if(count($recs)>0){
					$row = array();
					$row["sno"] = ""; $row["veh_reg"] = ""; $row["depotsname"] = ""; $row["startReading"] = "";
					$row["endReading"] = ""; $row["totalkms"] = ""; 
					$row["holidaykms"] = "<span style='font-weight: bold; font-size: 14px;'>".$tot_holidaykms."</span>";; 
					$row["billingkms"] = ""; $row["workingdays"] = "";  $row["conrunningdays"] = "";  $row["kmspercon"] = ""; $row["usedkms"] = "";
					$row["lesserkms"] = "<span style='font-weight: bold; font-size: 14px;'>".$tot_lesser_kms."</span>";
					$row["excesskms"] = "<span style='font-weight: bold; font-size: 14px;'>".$tot_excess_kms."</span>";
					$row["excessbill"] = "<span style='font-weight: bold; font-size: 14px;'>".($tot_excess_kms-$tot_lesser_kms)."</span>";
					$resp[] = $row;
				}
			}
			echo json_encode($resp);
			return;
		}
		$values['bredcum'] = "EXTRA KMS REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads1 = array('s no', 'vehicle no','branch', "starting reading", "closing reading", "tot kms", "holiday kms", "billing kms", "working days", "con. running days", "con. kms", "used kms", "lesser kms", "excess kms", "extra bill kms ");
		$values["theads1"] = $theads1;
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "EXTRA KMS REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
	
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$form_fields = array();
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"getFormData(this.value);"), "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reportfor", "value"=>"", "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.extrakmsreport', array("values"=>$values));
	}
	
	private function getVehiclePerformance($values)
	{
		if (\Request::isMethod('post'))
		{
			//$values["test"];
			$select_args = array();
	
			if(!isset($values["fromdate"]) || !isset($values["todate"])){
				echo json_encode(array("total"=>0, "data"=>array()));
				return ;
			}
	
			$frmDt = date("Y-m-d",strtotime($values["fromdate"]));
			$toDt = date("Y-m-d",strtotime($values["todate"]));
			$resp = array();
			$resp_obj = array();
			DB::statement(DB::raw("CALL vehicle_performance_report('".$frmDt."', '".$toDt."');"));
			$veh_arr = array();
			if($values["vehicle"]==0){
				$sql = \Contract::where("contract_vehicles.status","=","ACTIVE");
							if($values["depot"]!=0){
								$sql->where("contracts.depotId",$values["depot"]);
							}
							else{
								$sql->where("contracts.clientId",$values["clientname"]);
							}
							$sql->where("contracts.depotId","=",$values["depot"])
								->join("contract_vehicles","contracts.id","=","contract_vehicles.contractId");
				$con_vehs = $sql->select(array("contract_vehicles.vehicleId as vehicleId"))->get();
				foreach ($con_vehs as $con_veh){
					$veh_arr[] = $con_veh->vehicleId; 
				}
			}
			else{
				$veh_arr[] = $values["vehicle"];
			}
			
			$branches = \OfficeBranch::All();
			$branches_arr = array();
			foreach ($branches as $branch){
				$branches_arr[$branch->id] = $branch->name;
			}
			
			$recs = DB::select( DB::raw("SELECT * FROM `temp_vehicle_performance` order by date desc"));
			$income_arr = array();
			$expense_arr = array();
			
			$all_veh_arr = array();
			$all_vehs = \Vehicle::All();
			foreach ($all_vehs as $all_veh){
				$all_veh_arr[$all_veh->id] = $all_veh->veh_reg;
			}
			$expense_sum = 0;
			$fuel_sum = 0;
			$stock_sum = 0;
			$repair_sum = 0;
			
			$repairs_veh_summery = array();
			$repairs_veh_summery_amt = 0;
			$repairs_veh_summery_veh = "";
			
			foreach($recs as  $rec) {
				$row = array();
				if($rec->type == "REPAIR TRANSACTION"){
					$veh_arr_lc = explode(",", $rec->name);
					foreach ($veh_arr_lc as $veh){
						if(in_array($veh, $veh_arr)){
							$row = array();
							$row["veh_reg"] = $all_veh_arr[$veh];
							$row["branch"] = "";
							if($rec->branchId != 0){
								$row["branch"] = $branches_arr[$rec->branchId];
							}
							$row["type"] = $rec->type;
							$row["purpose"] = $rec->entity;
							$row["date"] = date("d-m-Y",strtotime($rec->date));
							$row["transinfo"] = "Credit Supplier : ".$rec->entityValue."<br/>Bill No. - ".$rec->billNo;
							$row["amount"] = ($rec->amount)/(count($veh_arr_lc)-1);
							$repair_sum = $repair_sum+(($rec->amount)/(count($veh_arr_lc)-1));
							$row["remarks"] = $rec->remarks;
							$expense_arr[] = $row;
						}
					}
				}
				if(in_array($rec->vehicleId, $veh_arr)){
					if($rec->type == "income"){
						$row["veh_reg"] = $rec->entity;
						$row["branch"] = $branches_arr[$rec->branchId];
						$row["type"] = $rec->name;
						$row["date"] = date("d-m-Y",strtotime($rec->date));
						$row["transinfo"] = "Bill No. - ".$rec->billNo;
						$row["amount"] = $rec->amount;
						$row["remarks"] = $rec->remarks;
						$income_arr[] = $row;
					}
					else{
						$row["veh_reg"] = $rec->entity;
						$row["branch"] = "";
						if($rec->branchId != 0){
							$row["branch"] = $branches_arr[$rec->branchId];
						}
						$row["type"] = $rec->type;
						
						if($rec->type=="expense"){
							$expense_sum = $expense_sum+$rec->amount;
						}
						
						if($rec->type=="STOCK TRANSACTION"){
							$stock_sum = $stock_sum+$rec->amount;
						}
						
						$row["purpose"] = $rec->name;
						$row["date"] = date("d-m-Y",strtotime($rec->date));
						$row["transinfo"] = "";
						if($rec->name ==  "FUEL"){
							$row["transinfo"] = "Fuel Station : ".$rec->entityValue."<br/>Bill No. - ".$rec->billNo;
							$fuel_sum = $fuel_sum+$rec->amount;
						}
						$row["amount"] = $rec->amount;
						$row["remarks"] = $rec->remarks;
						$expense_arr[] = $row;
					}
				}
			}
			$resp_obj["incomes"] = $income_arr;
			$resp_obj["expenses"] = $expense_arr;
			$resp_obj["expenses_summary"] = array(array("emp_salary"=>"0.0"),array("fuel"=>sprintf('%0.2f',$fuel_sum)),array("repairs"=>sprintf('%0.2f',$repair_sum)),array("stock"=>sprintf('%0.2f',$stock_sum)),array("others"=>sprintf('%0.2f',$expense_sum)));
			
			
			$recs = DB::select( DB::raw("SELECT * FROM `temp_vehicle_performance`"));
			$summary_by_vehicle = array();
			foreach($veh_arr as  $veh_rec) {
				$expense_sum = 0;
				$fuel_sum = 0;
				$stock_sum = 0;
				$repair_sum = 0;
				foreach($recs as  $rec) {
					$row = array();
					if($rec->type == "REPAIR TRANSACTION"){
						$veh_arr_lc = explode(",", $rec->name);
						foreach ($veh_arr_lc as $veh){
							if($veh == $veh_rec){
								$repair_sum = $repair_sum+(($rec->amount)/(count($veh_arr_lc)-1));
							}
						}
					}
					else{
						if($veh_rec == $rec->vehicleId){
							if($rec->type == "expense"){
								$expense_sum = $expense_sum+$rec->amount;
							}
							if($rec->type == "FUEL"){
								$fuel_sum = $fuel_sum+$rec->amount;
							}
							if($rec->type == "STOCK TRANSACTION"){
								$stock_sum = $stock_sum+$rec->amount;
							}
						}
					}
				}
				$row = array();
				$row["vehicle"] = "";
				if(isset($all_veh_arr[$veh_rec])){
					$row["vehicle"] = $all_veh_arr[$veh_rec];
				}
				
				$row["fuel"] = $fuel_sum;
				$row["repair"] = $repair_sum;
				$row["purchases"] = "0.0";
				$row["stock"] = $stock_sum;
				$row["salaries"] = "0.0";
				$row["expense"] = $expense_sum;
				$summary_by_vehicle[] = $row;
			}
			
			$resp_obj["summary_by_vehicle"] = $summary_by_vehicle;
			echo json_encode($resp_obj);
			return;
	
		}
		$values['bredcum'] = "VEHICLE PERFORMANCE REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads = array('VEHICLE NO', 'BRANCH', "INCOME TYPE", "DATE", "TRANSACTION INFO", "AMOUNT",  "REMARKS");
		$values["theads"] = $theads;
		$theads = array('VEHICLE NO', 'BRANCH', "EXPENSES TYPE", 'PURPOSE', "DATE", "TRANSACTION INFO", "AMOUNT",  "REMARKS");
		$values["theads1"] = $theads;
		$theads = array('EXPENSES TYPE', "TOTAL AMOUNT");
		$values["theads2"] = $theads;
		$theads = array('VEHICLE NO','FUEL EXPENSE', "REPAIR EXPENSES", "PURCHASE EXPENSES", "STOCK EXPENSES", "SALARIES", "VEHICLE EXPENSES");
		$values["theads3"] = $theads;
	
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "VEHILE PERFORMANCE REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
	
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		//$clients_arr[0] = "All";
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$form_fields = array();
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"getFormData(this.value);"), "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"vehicle", "content"=>"vehicle", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.vehicleperformancereport', array("values"=>$values));
	}
	
private function getVehicleTracking($values)
	{
		if (\Request::isMethod('post'))
		{
			//$values["test"];
			$select_args = array();
	
			if(!isset($values["fromdate"]) || !isset($values["todate"])){
				echo json_encode(array("total"=>0, "data"=>array()));
				return ;
			}
			$frmDt = date("Y-m-d",strtotime($values["fromdate"]));
			$toDt = date("Y-m-d",strtotime($values["todate"]));
			$resp = array();
			$resp_obj = array();
			$resp1 = array("data"=>array());
			if(isset($values["reporttype1"]) && $values["reporttype1"] == "repair_transactions"){
				//echo $values["type"];die();
				
				//$val["test"];
				DB::statement(DB::raw("CALL vehicle_performance_report('".$frmDt."', '".$toDt."');"));
				$veh_arr = array();
				if($values["vehicle"] == 0 && $values["depot"] ==  0){
					$sql = \Contract::where("contract_vehicles.status","=","ACTIVE");
					$sql->where("contracts.clientId","=",$values["clientname"])
					->join("contract_vehicles","contracts.id","=","contract_vehicles.contractId");
					$con_vehs = $sql->select(array("contract_vehicles.vehicleId as vehicleId"))->get();
					foreach ($con_vehs as $con_veh){
						$veh_arr[] = $con_veh->vehicleId;
					}
						
				}
				else if($values["vehicle"]==0){
					$sql = \Contract::where("contract_vehicles.status","=","ACTIVE");
					if($values["depot"]!=0){
						$sql->where("contracts.depotId","=",$values["depot"]);
					}
					else{
						$sql->where("contracts.clientId","=",$values["clientname"]);
					}
					$sql->where("contracts.depotId","=",$values["depot"])
					->join("contract_vehicles","contracts.id","=","contract_vehicles.contractId");
				
					$con_vehs = $sql->select(array("contract_vehicles.vehicleId as vehicleId"))->get();
					foreach ($con_vehs as $con_veh){
						$veh_arr[] = $con_veh->vehicleId;
					}
					//print_r($veh_arr);die();
				}
				else{
					$veh_arr[] = $values["vehicle"];
				}
				
				$branches = \OfficeBranch::All();
				$branches_arr = array();
				foreach ($branches as $branch){
					$branches_arr[$branch->id] = $branch->name;
				}
				
				$all_veh_arr = array();
				$all_vehs = \Vehicle::All();
				foreach ($all_vehs as $all_veh){
					$all_veh_arr[$all_veh->id] = $all_veh->veh_reg;
				}
				if($values["type"] == "salaries"){
					$tot_amt = 0;
					$select_args = array();
					$select_args[] = "vehicle.veh_reg as veh_reg";
					$select_args[] = "clients.name as cname";
					$select_args[] = "depots.name as dname";
					$select_args[] = "contract_vehicles.vehicleStartDate as vehicleStartDate";
					$select_args[] = "contract_vehicles.inActiveDate as inActiveDate";
					$select_args[] = "contract_vehicles.remarks as remarks";
					$select_args[] = "contract_vehicles.status as status";
					$select_args[] = "employee1.id as driver1";
					$select_args[] = "employee2.id as driver2";
					$select_args[] = "employee3.id as driver3";
					$select_args[] = "employee4.id as driver4";
					$select_args[] = "employee5.id as driver5";
					$select_args[] = "employee6.id as helper";
				
					$emps = \ContractVehicle::whereIn("vehicleId",$veh_arr)
										->leftjoin("vehicle","vehicle.id","=","contract_vehicles.vehicleId")
										->leftjoin("contracts","contracts.id","=","contract_vehicles.contractId")
										->leftjoin("clients","clients.id","=","contracts.clientId")
										->leftjoin("depots","depots.id","=","contracts.depotId")
										->leftjoin("employee as employee1","employee1.id","=","contract_vehicles.driver1Id")
										->leftjoin("employee as employee2","employee2.id","=","contract_vehicles.driver2Id")
										->leftjoin("employee as employee3","employee3.id","=","contract_vehicles.driver3Id")
										->leftjoin("employee as employee4","employee4.id","=","contract_vehicles.driver4Id")
										->leftjoin("employee as employee5","employee5.id","=","contract_vehicles.driver5Id")
										->leftjoin("employee as employee6","employee6.id","=","contract_vehicles.helperId")
										->select($select_args)->get();
										
					//print_r($emps);die();
				
					$frmDt1 = date("Y-m-01",strtotime($values["fromdate"]));
					$toDt1 = date("Y-m-01",strtotime($values["todate"]));
					$drivers_arr=array();
					foreach($emps as $emp){
						$drivers_arr[]=array($emp->driver1,$emp->driver2,$emp->driver3,$emp->driver4,$emp->driver5,$emp->helper);
						$row["expensetype"] = "salaries";
						$row["branch"] = $emp->cname."</br>(".$emp->dname.")";
						$row["vehicle"] = "";
						$row["purpose"] = "SALARY TRANSACTION";
						$row["date"] = "";
						$row["amount"] = "";
						//$tot_amt=$tot_amt+$row["amount"];
						$row["paidto"] = "";
						$row["billNo"] ="";
						$row["remarks"] = "";
						$row["createdBy"] = "";
					}
					//$input	= array_unique($drivers_arr, SORT_REGULAR);
					$results = array_unique(call_user_func_array('array_merge',$drivers_arr));
					
					 $salaries = DB::table('empsalarytransactions')
											->wherebetween('salaryMonth',[$frmDt1, $toDt1])
											->whereIn('empId', $results)->get();
					$workers =  \Employee::where("status","!=","")->get();
					$worker_arr = array();
					foreach ($workers as $worker){
						$worker_arr[$worker->id] = $worker->fullName;
					}
					foreach ($salaries as $salary){
						$row["date"] = "formonth:".date("M-Y",strtotime($salary->salaryMonth))."</br> paid on:".date("d-m-Y",strtotime($salary->paymentDate));
						$row["amount"] = $salary->salaryPaid;
						$tot_amt=$tot_amt+$row["amount"];
						$row["paidto"] = $worker_arr[$salary->empId];
						$row["billNo"] ="";
						if($salary->paymentType != "cash"){
							if($salary->paymentType == "ecs" || $salary->paymentType == "neft" || $salary->paymentType == "rtgs" || $salary->paymentType == "cheque_debit" || $salary->paymentType == "cheque_credit"){
								$row["billNo"] = "Payment Type : ".$salary->paymentType."<br/>";
								$bank_dt = \BankDetails::where("id","=",$salary->bankAccount)->first();
								if(count($bank_dt)>0){
									$row["billNo"] = $salary->paymentType."<br/>"."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
								}
								$row["billNo"]= $row["billNo"]."Ref No : ".$salary->chequeNumber;
							}
							if($salary->paymentType == "credit_card" || $salary->paymentType == "debit_card" ||$salary->paymentType == "hp_card"){
								$row["paymenttype"] = "Payment Type : ".$salary->paymentType."<br/>";
								$bank_dt = \Cards::where("id","=",$salary->bankAccount)->first();
								if(count($bank_dt)>0){
									$row["billNo"] = $salary->paymentType."<br/>"."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
								}
								$row["billNo"] = $row["billNo"]."Ref No : ".$salary->chequeNumber;
							}
							if($salary->paymentType == "dd"){
								$row["billNo"] = "Payment Type : ".$salary->paymentType."<br/>";
								$row["billNo"] = $row["billNo"]."Ref No : ".$salary->chequeNumber;
							}
						}
						else{
							$row["billNo"] = $salary->paymentType;
						}
						$row["remarks"] =$salary->comments;
						$row["createdBy"] = $worker_arr[$salary->createdBy];
						$select_args = array();
						$select_args[] = "vehicle.veh_reg as veh_reg";
// 						$select_args[] = "clients.name as cname";
// 						$select_args[] = "depots.name as dname";
// 						$select_args[] = "contract_vehicles.vehicleStartDate as vehicleStartDate";
// 						$select_args[] = "contract_vehicles.inActiveDate as inActiveDate";
// 						$select_args[] = "contract_vehicles.remarks as remarks";
// 						$select_args[] = "contract_vehicles.status as status";
						$select_args[] = "employee1.id as driver1";
						$select_args[] = "employee2.id as driver2";
						$select_args[] = "employee3.id as driver3";
						$select_args[] = "employee4.id as driver4";
						$select_args[] = "employee5.id as driver5";
						$select_args[] = "employee6.id as helper";
						
						$sdrivers = \ContractVehicle::whereIn("vehicleId",$veh_arr)
														->leftjoin("vehicle","vehicle.id","=","contract_vehicles.vehicleId")
														->leftjoin("contracts","contracts.id","=","contract_vehicles.contractId")
														->leftjoin("clients","clients.id","=","contracts.clientId")
														->leftjoin("depots","depots.id","=","contracts.depotId")
														->leftjoin("employee as employee1","employee1.id","=","contract_vehicles.driver1Id")
														->leftjoin("employee as employee2","employee2.id","=","contract_vehicles.driver2Id")
														->leftjoin("employee as employee3","employee3.id","=","contract_vehicles.driver3Id")
														->leftjoin("employee as employee4","employee4.id","=","contract_vehicles.driver4Id")
														->leftjoin("employee as employee5","employee5.id","=","contract_vehicles.driver5Id")
														->leftjoin("employee as employee6","employee6.id","=","contract_vehicles.helperId")
														->select($select_args)->get();
						foreach($sdrivers as $sdriver){
							if($salary->empId == $sdriver->driver1 ||$salary->empId == $sdriver->driver2 || $salary->empId == $sdriver->driver3 || $salary->empId == $sdriver->driver4 || $salary->empId == $sdriver->driver5 || $salary->empId == $sdriver->helper){
								$row["vehicle"] = $sdriver->veh_reg;
							}
						}
						$resp[] = $row;
					}
				}
				else{
					$recs = DB::select( DB::raw("SELECT * FROM `temp_vehicle_performance` where (type='".$values["type"]."') order by date"));
					$tot_amt = 0;
					foreach($recs as  $rec) {
						$row = array();
						if($rec->type == "REPAIR TRANSACTION" ||$rec->type == "expense"){
							if($rec->vehicleIds != ""){
								$veh_arr_lc = explode(",", $rec->vehicleIds);
								foreach ($veh_arr_lc as $veh){
									if(in_array($veh, $veh_arr)){
										$row = array();
										$row["expensetype"] = $rec->type;
										$row["branch"] = "";
										if($rec->branchId != 0){
											$row["branch"] = $branches_arr[$rec->branchId];
										}
										$row["vehicle"] = $all_veh_arr[$veh];
										if($rec->type == "expense"){
											$row["purpose"] = $rec->name;
										}
										else{
											$row["purpose"] = $rec->entity;
										}
										$row["date"] = date("d-m-Y",strtotime($rec->date));
										if($rec->type == "expense"){
											$row["amount"] = ($rec->amount)/(count($veh_arr_lc));
											$tot_amt=$tot_amt+$row["amount"];
										}
										else{
											$row["amount"] = ($rec->amount)/((count($veh_arr_lc))-1);
											$tot_amt=$tot_amt+$row["amount"];
										}
										if($rec->entityValue == 2104){
											$row["paidto"] = "BAJAJALLIANZ GENERAL  INSURANCE LTD";
										}
										else{
											$row["paidto"] = $rec->entityValue;
										}
										$row["billNo"] = $rec->billNo;
										$row["remarks"] = $rec->remarks;
										$row["createdBy"] = $rec->createdBy;
										$resp[] = $row;
									}
								}
							}
						}
						else if($rec->type == "FUEL"|| $rec->type=="STOCK TRANSACTION"){
							if($rec->vehicleId != ""){
								$veh_arr_lc = explode(",", $rec->vehicleId);
								foreach ($veh_arr_lc as $veh){
									if(in_array($veh, $veh_arr)){
										$row = array();
										$row["expensetype"] = $rec->type;
										$row["branch"] = "";
										if($rec->type == "STOCK TRANSACTION"){
											$depots_arr = array();
											$depots = \Depot::All();
											foreach ($depots as $depot){
												$depots_arr[$depot->id] = $depot->name;
											}
											if($rec->branchId != 0){
												$row["branch"] = $depots_arr[$rec->branchId];
											}
												
										}
										else if($rec->type == "FUEL"){
											if($rec->branchId != 0){
												$row["branch"] = $branches_arr[$rec->branchId];
											}
											else{
												if($rec->contractId != 0){
													$select_args = array();
													$select_args[] = "contracts.id as id";
													$select_args[] = "clients.name as cname";
													$select_args[] = "depots.name as dname";
													$entities = \Contract::where("contracts.id","=",$rec->contractId)
																			->leftjoin("clients","clients.id","=","contracts.clientId")
																			->leftjoin("depots","depots.id","=","contracts.depotId")
																			->select($select_args)->get();
													if(count($entities)>0){
														$entities = $entities[0];
														$row["branch"] = $entities->cname."(".$entities->dname.")";
													}
												}
											}
										}
										$row["vehicle"] = $all_veh_arr[$veh];
										$row["purpose"] = $rec->entity;
										$row["date"] = date("d-m-Y",strtotime($rec->date));
										$row["amount"] = ($rec->amount)/(count($veh_arr_lc));
										$tot_amt=$tot_amt+$row["amount"];
										$row["paidto"] = $rec->entityValue;
										$row["billNo"] = $rec->billNo;
										$row["remarks"] = $rec->remarks;
										$row["createdBy"] = $rec->createdBy;
										$resp[] = $row;
									}
								}
								
							}
							
						}
					}
				}
				$resp_obj = array("data"=>$resp, "total_amt"=>$tot_amt);
			}
			
			DB::statement(DB::raw("CALL vehicle_performance_report('".$frmDt."', '".$toDt."');"));
			$veh_arr = array();
			
			if($values["vehicle"] == 0 && $values["depot"] ==  0){
				$sql = \Contract::where("contract_vehicles.status","=","ACTIVE");
				$sql->where("contracts.clientId","=",$values["clientname"])
					->join("contract_vehicles","contracts.id","=","contract_vehicles.contractId");
				$con_vehs = $sql->select(array("contract_vehicles.vehicleId as vehicleId"))->get();
				foreach ($con_vehs as $con_veh){
					$veh_arr[] = $con_veh->vehicleId;
				}
			
			}
			else if($values["vehicle"]==0){
				$sql = \Contract::where("contract_vehicles.status","=","ACTIVE");
				if($values["depot"]!=0){
					$sql->where("contracts.depotId","=",$values["depot"]);
				}
				else{
					$sql->where("contracts.clientId","=",$values["clientname"]);
				}
				$sql->where("contracts.depotId","=",$values["depot"])
				->join("contract_vehicles","contracts.id","=","contract_vehicles.contractId");
			
				$con_vehs = $sql->select(array("contract_vehicles.vehicleId as vehicleId"))->get();
				foreach ($con_vehs as $con_veh){
					$veh_arr[] = $con_veh->vehicleId;
				}
			}
			else{
				$veh_arr[] = $values["vehicle"];
			}
			
			$branches = \OfficeBranch::All();
			$branches_arr = array();
			foreach ($branches as $branch){
				$branches_arr[$branch->id] = $branch->name;
			}
			$recs = DB::select( DB::raw("SELECT * FROM `temp_vehicle_performance` order by date desc"));
			$income_arr = array();
			$expense_arr = array();
				
			$all_veh_arr = array();
			$all_vehs = \Vehicle::All();
			foreach ($all_vehs as $all_veh){
				$all_veh_arr[$all_veh->id] = $all_veh->veh_reg;
			}
			$tot_income=0;
			$tot_expenses=0;
			$tot_profit= 0;
			$tot_summery=0;
			$expense_sum = 0;
			$fuel_sum = 0;
			$stock_sum = 0;
			$repair_sum = 0;
				
			$repairs_veh_summery = array();
			$repairs_veh_summery_amt = 0;
			$repairs_veh_summery_veh = "";
				
			foreach($recs as  $rec) {
				$row = array();
				if($rec->type == "REPAIR TRANSACTION" ||$rec->type == "expense"){
					if($rec->vehicleIds != ""){
						$veh_arr_lc = explode(",", $rec->vehicleIds);
						foreach ($veh_arr_lc as $veh){
							if(in_array($veh, $veh_arr)){
								$row = array();
								if($rec->type == "REPAIR TRANSACTION"){
									$repair_sum = $repair_sum+(($rec->amount)/((count($veh_arr_lc))-1));
								}
								else if($rec->type == "expense"){
									$expense_sum = $expense_sum+(($rec->amount)/(count($veh_arr_lc)));
								}
							}
						}
					}
				}
				else{
					foreach($veh_arr as  $veh_rec) {
						if($veh_rec == $rec->vehicleId){
							if($rec->type == "FUEL"){
								$fuel_sum = $fuel_sum+$rec->amount;
							}
							if($rec->type == "STOCK TRANSACTION"){
								$stock_sum = $stock_sum+$rec->amount;
							}
						}
					}
					
				}
				if(in_array($rec->vehicleId, $veh_arr)){
					if($rec->type == "income"){
						$row["veh_reg"] = $all_veh_arr[$rec->vehicleId];
						if($rec->name == "CLIENT INCOME"){
							$depots_arr = array();
							$depots = \Depot::All();
							foreach ($depots as $depot){
								$depots_arr[$depot->id] = $depot->name;
							}
							$clients_arr = array();
							$clients = \Client::All();
							foreach ($clients as $client){
								$clients_arr[$client->id] = $client->name;
							}
								if($rec->branchId != 0){
									$row["branch"] = $clients_arr[$rec->branchId]."(".$depots_arr[$rec->vehicleIds].")";
								}
						}
						else {
							if($rec->branchId != 0){
								$row["branch"] = $branches_arr[$rec->branchId];
							}
						}
						$row["type"] = $rec->name;
						$row["date"] = date("d-m-Y",strtotime($rec->date));
						$row["transinfo"] = "Bill No. - ".$rec->billNo;
						$row["amount"] = $rec->amount;
						$tot_income= $tot_income+$row["amount"];
						$row["remarks"] = $rec->remarks;
						$income_arr[] = $row;
					}
				}
			}
			$tot_expenses=$expense_sum+$fuel_sum+$repair_sum+$stock_sum;
			$tot_profit = $tot_expenses-$tot_income;
			$resp_obj["incomes"] = $income_arr;
			$select_args = array();
// 			$select_args[] = "vehicle.veh_reg as veh_reg";
// 			$select_args[] = "clients.name as cname";
// 			$select_args[] = "depots.name as dname";
// 			$select_args[] = "contract_vehicles.vehicleStartDate as vehicleStartDate";
// 			$select_args[] = "contract_vehicles.inActiveDate as inActiveDate";
// 			$select_args[] = "contract_vehicles.remarks as remarks";
			//$select_args[] = "contract_vehicles.status as status";
			$select_args[] = "employee1.id as driver1";
			$select_args[] = "employee2.id as driver2";
			$select_args[] = "employee3.id as driver3";
			$select_args[] = "employee4.id as driver4";
			$select_args[] = "employee5.id as driver5";
			$select_args[] = "employee6.id as helper";
				
			$emps = \ContractVehicle::whereIn("vehicleId",$veh_arr)
// 						->leftjoin("vehicle","vehicle.id","=","contract_vehicles.vehicleId")
// 						->leftjoin("contracts","contracts.id","=","contract_vehicles.contractId")
// 						->leftjoin("clients","clients.id","=","contracts.clientId")
// 						->leftjoin("depots","depots.id","=","contracts.depotId")
						->leftjoin("employee as employee1","employee1.id","=","contract_vehicles.driver1Id")
						->leftjoin("employee as employee2","employee2.id","=","contract_vehicles.driver2Id")
						->leftjoin("employee as employee3","employee3.id","=","contract_vehicles.driver3Id")
						->leftjoin("employee as employee4","employee4.id","=","contract_vehicles.driver4Id")
						->leftjoin("employee as employee5","employee5.id","=","contract_vehicles.driver5Id")
						->leftjoin("employee as employee6","employee6.id","=","contract_vehicles.helperId")
						->select($select_args)->get();
						
			$frmDt1 = date("Y-m-01",strtotime($values["fromdate"]));
			$toDt1 = date("Y-m-01",strtotime($values["todate"]));
			$drivers_arr=array();
			foreach($emps as $emp){
				$drivers_arr[]=array($emp->driver1,$emp->driver2,$emp->driver3,$emp->driver4,$emp->driver5,$emp->helper);
			}
			//$input	= array_unique($drivers_arr, SORT_REGULAR);
			$results = array_unique(call_user_func_array('array_merge',$drivers_arr));
			$salaries = DB::table('empsalarytransactions')
						->wherebetween('salaryMonth',[$frmDt1, $toDt1])
						->whereIn('empId', $results)
						->sum('salaryPaid');
			$resp_obj["expenses_summary"] = array(array('<a href="#modal-table" role="button" data-toggle="modal" onclick="getData(\''.$rec->type="salaries".'\',\''.$frmDt1.'\', \''.$toDt1.'\')" <span="">emp_salary</a>'=>sprintf('%0.2f',$salaries)),array('<a href="#modal-table" role="button" data-toggle="modal" onclick="getData(\''.$rec->type="FUEL".'\',\''.$frmDt.'\', \''.$toDt.'\')" <span="">fuel</a>'=>sprintf('%0.2f',$fuel_sum)),array('<a href="#modal-table" role="button" data-toggle="modal" onclick="getData(\''.$rec->type="REPAIR TRANSACTION".'\',\''.$frmDt.'\', \''.$toDt.'\')" <span="">repair transaction</a>'=>sprintf('%0.2f',$repair_sum)),array('<a href="#modal-table" role="button" data-toggle="modal" onclick="getData(\''.$rec->type="STOCK TRANSACTION".'\',\''.$frmDt.'\', \''.$toDt.'\')" <span="">stock</a>'=>sprintf('%0.2f',$stock_sum)),array('<a href="#modal-table" role="button" data-toggle="modal" onclick="getData(\''.$rec->type="expense".'\',\''.$frmDt.'\', \''.$toDt.'\')" <span="">other expenses</a>'=>sprintf('%0.2f',$expense_sum)));
				
				
			$recs = DB::select( DB::raw("SELECT * FROM `temp_vehicle_performance`"));
			$summary_by_vehicle = array();
			$salaries_sum = 0;
			foreach($veh_arr as  $veh_rec) {
				$expense_sum = 0;
				$fuel_sum = 0;
				$stock_sum = 0;
				$repair_sum = 0;
				foreach($recs as  $rec) {
					$row = array();
					$select_args = array();
					$select_args[] = "vehicle.veh_reg as veh_reg";
					$select_args[] = "employee1.id as driver1";
					$select_args[] = "employee2.id as driver2";
					$select_args[] = "employee3.id as driver3";
					$select_args[] = "employee4.id as driver4";
					$select_args[] = "employee5.id as driver5";
					$select_args[] = "employee6.id as helper";
					$frmDt1 = date("Y-m-01",strtotime($values["fromdate"]));
					$toDt1 = date("Y-m-01",strtotime($values["todate"]));
					$vdrivers = \ContractVehicle::where("contract_vehicles.vehicleId","=",$veh_rec)
										->leftjoin("vehicle","vehicle.id","=","contract_vehicles.vehicleId")
										->leftjoin("contracts","contracts.id","=","contract_vehicles.contractId")
										->leftjoin("clients","clients.id","=","contracts.clientId")
										->leftjoin("depots","depots.id","=","contracts.depotId")
										->leftjoin("employee as employee1","employee1.id","=","contract_vehicles.driver1Id")
										->leftjoin("employee as employee2","employee2.id","=","contract_vehicles.driver2Id")
										->leftjoin("employee as employee3","employee3.id","=","contract_vehicles.driver3Id")
										->leftjoin("employee as employee4","employee4.id","=","contract_vehicles.driver4Id")
										->leftjoin("employee as employee5","employee5.id","=","contract_vehicles.driver5Id")
										->leftjoin("employee as employee6","employee6.id","=","contract_vehicles.helperId")
										->select($select_args)->get();
					$vdrivers_arr= array();
					foreach($vdrivers as $vdriver){
						$vdrivers_arr[]=array($vdriver->driver1,$vdriver->driver2,$vdriver->driver3,$vdriver->driver4,$vdriver->driver5,$vdriver->helper);
					}
					$empresults = array_unique(call_user_func_array('array_merge',$vdrivers_arr));
					$salaries_sum = DB::table('empsalarytransactions')
									->wherebetween('salaryMonth',[$frmDt1, $toDt1])
									->whereIn('empId', $empresults)
									->sum('salaryPaid');
					if($rec->type == "REPAIR TRANSACTION" || $rec->type == "expense"){
						if($rec->vehicleIds != ""){
							$veh_arr_lc = explode(",", $rec->vehicleIds);
							foreach ($veh_arr_lc as $veh){
								if($veh == $veh_rec){
									if($rec->type == "REPAIR TRANSACTION"){
										$repair_sum = $repair_sum+(($rec->amount)/((count($veh_arr_lc))-1));
									}
									else if($rec->type == "expense"){
										$expense_sum = $expense_sum+(($rec->amount)/(count($veh_arr_lc)));
									}
								}
							}
						}
					}
					else{
						if($veh_rec == $rec->vehicleId){
							if($rec->type == "FUEL"){
								$fuel_sum = $fuel_sum+$rec->amount;
							}
							if($rec->type == "STOCK TRANSACTION"){
								$stock_sum = $stock_sum+$rec->amount;
							}
							
						}
					}
				}
				$row = array();
				$row["vehicle"] = "";
				if(isset($all_veh_arr[$veh_rec])){
					$row["vehicle"] = $all_veh_arr[$veh_rec];
				}
				$row["fuel"] = $fuel_sum;
				$row["repair"] = $repair_sum;
				$row["stock"] = $stock_sum;
				$row["salaries"] = $salaries_sum;
				$row["expense"] = $expense_sum;
				$row["totsum"] = $fuel_sum+$repair_sum+$stock_sum+$expense_sum+$salaries_sum;
				$summary_by_vehicle[] = $row;
				$tot_summery=$tot_summery+$salaries_sum+$fuel_sum+$repair_sum+$stock_sum+$expense_sum;
			}
			$resp_obj["summary_by_vehicle"] = $summary_by_vehicle;
			$select_args = array();
			$select_args[] = "vehicle.veh_reg as veh_reg";
			$select_args[] = "clients.name as cname";
			$select_args[] = "depots.name as dname";
			$select_args[] = "contract_vehicles.vehicleStartDate as vehicleStartDate";
			$select_args[] = "contract_vehicles.inActiveDate as inActiveDate";
			$select_args[] = "contract_vehicles.remarks as remarks";
			$select_args[] = "contract_vehicles.status as status";
			$select_args[] = "employee1.fullName as driver1";
			$select_args[] = "employee2.fullName as driver2";
			$select_args[] = "employee3.fullName as driver3";
			$select_args[] = "employee4.fullName as driver4";
			$select_args[] = "employee5.fullName as driver5";
			$select_args[] = "employee6.fullName as helper";
			
			$recs = \ContractVehicle::whereIn("vehicleId",$veh_arr)
									->leftjoin("vehicle","vehicle.id","=","contract_vehicles.vehicleId")
									->leftjoin("contracts","contracts.id","=","contract_vehicles.contractId")
									->leftjoin("clients","clients.id","=","contracts.clientId")
									->leftjoin("depots","depots.id","=","contracts.depotId")
									->leftjoin("employee as employee1","employee1.id","=","contract_vehicles.driver1Id")
									->leftjoin("employee as employee2","employee2.id","=","contract_vehicles.driver2Id")
									->leftjoin("employee as employee3","employee3.id","=","contract_vehicles.driver3Id")
									->leftjoin("employee as employee4","employee4.id","=","contract_vehicles.driver4Id")
									->leftjoin("employee as employee5","employee5.id","=","contract_vehicles.driver5Id")
									->leftjoin("employee as employee6","employee6.id","=","contract_vehicles.helperId")
									->select($select_args)->get();
			
			$summary_by_vehicle = array();
			foreach($recs as  $rec) {
				$row = array();
				$row["vehicle"] = $rec->veh_reg;
				$row["contract"] = $rec->dname." (".$rec->cname.")";
				$row["status"] = $rec->status;
				$row["drivers"] = $rec->driver1;
				if($rec->driver2 != ""){
					$row["drivers"] = $row["drivers"].", ".$rec->driver2;
				}
				if($rec->driver3 != ""){
					$row["drivers"] = $row["drivers"].", ".$rec->driver3;
				}
				if($rec->driver4 != ""){
					$row["drivers"] = $row["drivers"].", ".$rec->driver4;
				}
				if($rec->driver5 != ""){
					$row["drivers"] = $row["drivers"].", ".$rec->driver5;
				}
				$row["helper"] = $rec->helper;
				$row["startDate"] = date("d-m-Y",strtotime($rec->vehicleStartDate));
				$row["endDate"] = date("d-m-Y",strtotime($rec->inActiveDate));
				if($row["endDate"]=="01-01-1970" || $row["endDate"]=="00-00-0000"){
					$row["endDate"] = "";
				}
				$row["remarks"] = $rec->remarks;
				$summary_by_vehicle[] = $row;
			}
			$resp_obj["vehicle_contracts"] = $summary_by_vehicle;
			$resp_obj["tot_income"]=$tot_income;
			$resp_obj["tot_expenses"]=$tot_expenses;
			$resp_obj["tot_summery"]=$tot_summery;
			$resp_obj["tot_profit"]=$tot_profit;
			echo json_encode($resp_obj);
			return;
		}
		$values['bredcum'] = "VEHICLE PERFORMANCE REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads = array('VEHICLE NO', 'CONTRACT', "STATUS", "DRIVERS", "HELPER", "START DT", "END DT", "REMARKS");
		$values["theads0"] = $theads;
		$theads = array('VEHICLE NO', 'BRANCH', "INCOME TYPE", "DATE", "TRANSACTION INFO", "AMOUNT",  "REMARKS");
		$values["theads"] = $theads;
		//$theads = array('VEHICLE NO', 'BRANCH', "EXPENSES TYPE", 'PURPOSE', "DATE", "TRANSACTION INFO", "AMOUNT",  "REMARKS");
		//$values["theads1"] = $theads;
		$theads = array('EXPENSES TYPE', "TOTAL AMOUNT");
		$values["theads2"] = $theads;
		$theads = array('VEHICLE NO','FUEL EXPENSE', "REPAIR EXPENSES","STOCK EXPENSES", "SALARIES", "VEHICLE EXPENSES","TOTAL");
		$values["theads3"] = $theads;
	
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "VEHILE PERFORMANCE REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
	
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		//$clients_arr[0] = "All";
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$form_fields = array();
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"getFormData(this.value);"), "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"vehicle", "content"=>"vehicle", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>array("0"=>"ALL"));
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.vehicletrackingreport', array("values"=>$values));
	}	
	private function getVehicleMileageFullReport($values){
		if (\Request::isMethod('post'))
		{
			if(!isset($values["fromdate"])){
				$values["fromdate"] = "10-10-2013";
			}
			if(!isset($values["todate"])){
				$values["todate"] = date("d-m-Y");
			}
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			if($values["reporttype"] == "vehiclemileage_full"){
				
				$select_args = array();
				$select_args[] = "vehicle.id as id";
				$select_args[] = "vehicle.veh_reg as veh_reg";
				$select_args[] = "fueltransactions.startReading as startReading";
				$select_args[] = "fueltransactions.litres as litres";
				
				if($values["vehicle"]==0){
					$recs1 = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")
								->whereBetween("fueltransactions.filledDate",array($frmDt,$toDt))->groupBy("vehicleId")->get();
					foreach ($recs1 as $rec1) {
						$recs = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")
												->leftjoin("vehicle","fueltransactions.vehicleId","=","vehicle.id")
												->where("fueltransactions.vehicleId","=",$rec1->vehicleId)
												->where("fueltransactions.fullTank","=","YES")
												->whereBetween("fueltransactions.filledDate",array($frmDt,$toDt))
												->orderby("fueltransactions.filledDate")
												->select($select_args)->get();
						$liters = \FuelTransaction::where("fueltransactions.vehicleId","=",$rec1->vehicleId)->whereBetween("fueltransactions.filledDate",array($frmDt,$toDt))
									->sum("fueltransactions.litres");
						
						$mileage = \FuelTransaction::where("fueltransactions.vehicleId","=",$rec1->vehicleId)->where("fueltransactions.fullTank","=","YES")->whereBetween("fueltransactions.filledDate",array($frmDt,$toDt))
									->avg("fueltransactions.mileage");
						$row = array();
						$len = count($recs);
						$distance = 0;
						if(count($recs)>1){
							$distance = $recs[$len-1]->startReading-$recs[0]->startReading;
						}
						if($distance==0){
							$distance = 0;
						}
						if(count($recs)>0){
							$row["veh_reg"] = $recs[0]->veh_reg;
							$row["distance"] = $distance;
							$row["liters"] = $liters;
							$row["mileage"] = sprintf('%0.2f',$mileage);
							$resp[] = $row;
						}
					}
					
				}
				else{
					$recs = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")
												->join("vehicle","fueltransactions.vehicleId","=","vehicle.id")
												->where("fueltransactions.vehicleId","=",$values["vehicle"])
												->where("fueltransactions.fullTank","=","YES")
												->whereBetween("fueltransactions.filledDate",array($frmDt,$toDt))
												->select($select_args)->get();
		
					$liters = \FuelTransaction::where("fueltransactions.vehicleId","=",$values["vehicle"])->whereBetween("fueltransactions.filledDate",array($frmDt,$toDt))
									->sum("fueltransactions.litres");
					
					$mileage = \FuelTransaction::where("fueltransactions.vehicleId","=",$values["vehicle"])->where("fueltransactions.fullTank","=","YES")->whereBetween("fueltransactions.filledDate",array($frmDt,$toDt))
									->avg("fueltransactions.mileage");
					
					$row = array();
					$len = count($recs);
					$distance = 0;
					if(count($recs)>1){
						$distance = $distance+($recs[$len-1]->startReading-$recs[0]->startReading);
					}
					if($distance==0){
						$distance = 0;
					}
					$row["veh_reg"] =  "";
					if(count($recs)>1){
						$row["veh_reg"] = $recs[0]->veh_reg;
					}
					$row["distance"] = $distance;	
					$row["liters"] = $liters;
					$row["mileage"] = sprintf('%0.2f',$mileage);;
					$resp[] = $row;
				}
				
				
			}
			echo json_encode($resp);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Bank Name','Branch Name', "Account Name", "Account No", "Account Type");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
		$select_args = array();
		$select_args[] = "vehicle.id as id";
		$select_args[] = "vehicle.veh_reg as veh_reg";
		
		$vehicles = \FuelTransaction::where("fueltransactions.status","=","ACTIVE")
									->join("vehicle","fueltransactions.vehicleId","=","vehicle.id")
									->groupBy("fueltransactions.vehicleId")
									->select($select_args)->get();
	
		$vehicles_arr = array();
		$vehicles_arr["0"] = "ALL VEHICLES";
		foreach ($vehicles as $vehicle){
			$vehicles_arr[$vehicle->id] = $vehicle->veh_reg;
		}
	
		$supplier_rep_arr = array();
		$supplier_rep_arr['balanceSheetNoDt'] = "Credit Supplier Balance Sheet";
		$supplier_rep_arr['balanceSheet'] = "Credit Supplier Range Sheet";
		$supplier_rep_arr['payment'] = "Credit Supplier Payments";
		$supplier_rep_arr['repairs'] = "Repairs";
		$supplier_rep_arr['purchase'] = "Purchases";
		$supplier_rep_arr['vehicleReport'] = "Track By Vehicle";
	
		$form_field = array("name"=>"vehicle", "content"=>"vehicle", "readonly"=>"",  "required"=>"required", "type"=>"select", "options"=>$vehicles_arr, "class"=>"form-control chosen-select");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$add_form_fields = array();
		$vehs =  \Vehicle::All();
		$vehs_arr = array();
		foreach ($vehs as $veh){
			$vehs_arr[$veh->id] = $veh->veh_reg;
		}
		$form_field = array("name"=>"creditsupplier", "content"=>"by supplier", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>array(), "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;
		$form_field = array("name"=>"vehicle", "content"=>"by vehicle", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$vehs_arr, "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;
	
		$form_info["form_fields"] = $form_fields;
		$form_info["add_form_fields"] = $add_form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.vehiclemileage_fullreport', array("values"=>$values));
	}
	
	private function getHolidaysRunningReport($values){
		if (\Request::isMethod('post'))
		{
			if(!isset($values["fromdate"])){
				$values["fromdate"] = "10-10-2013";
			}
			if(!isset($values["todate"])){
				$values["todate"] = date("d-m-Y");
			}
			$frmDt = date("Y-m-d", strtotime($values["fromdate"]));
			$toDt = date("Y-m-d", strtotime($values["todate"]));
			$resp = array();
			if($values["reporttype"] == "holidaysrunningreport"){	
				$select_args = array();
				$select_args[] = "depots.name as depotsname";
				$select_args[] = "vehicle.veh_reg as veh_reg";
				$select_args[] = "service_logs.serviceDate as serviceDate";
				$select_args[] = "service_logs.startReading as startReading";
				$select_args[] = "service_logs.endReading as endReading";
				$select_args[] = "service_logs.remarks as remarks";
				if(true){ //$values["vehicle"]==0
					$sql = \Contract::where("clientId","=",$values["clientname"]);
								if(isset($values["depot"]) && $values["depot"]!=0){
									$sql->where("depotId","=",$values["depot"]);
								}
					$contracts = $sql->get();
					$contracts_arr = array();
					foreach($contracts as $contract){
						$contracts_arr[] = $contract->id;
					}
					$recs1 = \ClientHolidays::whereIn("clientholidays.contractId",$contracts_arr)
								->where("clientholidays.status","=","Open")
								->where("clientholidays.fromDate",">=",$frmDt)
								->where("clientholidays.toDate","<=",$toDt)->get();
					$serv_dts = array();
					$serv_vehs = array();
					foreach ($recs1 as $rec1) {
						$sql = \ServiceLog::leftjoin("contract_vehicles","service_logs.contractVehicleId","=","contract_vehicles.id")
									->leftjoin("vehicle","contract_vehicles.vehicleId","=","vehicle.id")
									->leftjoin("contracts","contracts.id","=","service_logs.contractId")
									->leftjoin("depots","depots.id","=","contracts.depotId");
									if($values["vehicle"]>0){
										$cvehs = \ContractVehicle::where("contract_vehicles.vehicleId","=",$values["vehicle"])
																	->whereIn("contract_vehicles.contractId",$contracts_arr)
																	->select(array("contract_vehicles.id as id"))
																	->get();
											$cvehs_arr = array();
											foreach($cvehs as $cveh){
												$cvehs_arr[]=$cveh->id;
											}
												$sql = $sql->whereIn("service_logs.contractVehicleId",$cvehs_arr);
									}
						$recs =  $sql->whereBetween("service_logs.serviceDate",array($rec1->fromDate,$rec1->toDate))
									->whereBetween("service_logs.serviceDate",array($frmDt,$toDt))
									->where("service_logs.contractId",$rec1->contractId)->select($select_args)->get();
						foreach ($recs as $rec) {
							if(!in_array($rec->serviceDate.",".$rec->veh_reg, $serv_dts)){
								$row = array();
								$row["depotsname"] = $rec->depotsname;
								$row["veh_reg"] = $rec->veh_reg;
								$row["serviceDate"] = date("d-m-Y",strtotime($rec->serviceDate));
								$row["startReading"] = $rec->startReading;
								$row["endReading"] = $rec->endReading;
								$row["totalkms"] = $rec->endReading-$rec->startReading;
								$row["remarks"] = $rec->remarks;
								$resp[] = $row;
								$serv_dts[] = $rec->serviceDate.",".$rec->veh_reg;
							}
						}
					}
				}				
			}
			echo json_encode($resp);
			return;
		}
	
		$values['bredcum'] = strtoupper($values["reporttype"]);
		$values['home_url'] = 'masters';
		$values['add_url'] = 'getreport';
		$values['form_action'] = 'getreport';
		$values['action_val'] = '';
		$theads = array('Branch Name','Vehicle No', "Date", "Starting KMs", "Closing KMs","Total KMs","Remarks");
		$values["theads"] = $theads;
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "bankdetails";
		$form_info["bredcum"] = "add bank details";
		$form_info["reporttype"] = $values["reporttype"];
	
		$form_fields = array();
	
		$clients = \Client::where("status","=","ACTIVE")->get();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client->id] = $client->name;
		}
	
		$supplier_rep_arr = array();
		$supplier_rep_arr['balanceSheetNoDt'] = "Credit Supplier Balance Sheet";
		$supplier_rep_arr['balanceSheet'] = "Credit Supplier Range Sheet";
		$supplier_rep_arr['payment'] = "Credit Supplier Payments";
		$supplier_rep_arr['repairs'] = "Repairs";
		$supplier_rep_arr['purchase'] = "Purchases";
		$supplier_rep_arr['vehicleReport'] = "Track By Vehicle";
	
		$form_fields = array();
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"changeDepot(this.value);"), "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"depot", "content"=>"depot/branch name", "readonly"=>"",  "required"=>"required", "type"=>"select", "action"=>array("type"=>"onChange", "script"=>"getFormData(this.value);"), "class"=>"form-control chosen-select", "options"=>array());
		$form_fields[] = $form_field;
		$form_field = array("name"=>"vehicle", "content"=>"vehicle", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>array("0"=>"ALL"));
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
	
		$add_form_fields = array();
		$vehs =  \Vehicle::All();
		$vehs_arr = array();
		foreach ($vehs as $veh){
			$vehs_arr[$veh->id] = $veh->veh_reg;
		}
		$form_field = array("name"=>"creditsupplier", "content"=>"by supplier", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>array(), "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;
		$form_field = array("name"=>"vehicle", "content"=>"by vehicle", "readonly"=>"",  "required"=>"required","type"=>"select", "options"=>$vehs_arr, "class"=>"form-control chosen-select");
		$add_form_fields[] = $form_field;
	
		$form_info["form_fields"] = $form_fields;
		$form_info["add_form_fields"] = $add_form_fields;
		$values["form_info"] = $form_info;
		$values["provider"] = "bankdetails";
		return View::make('reports.holidaysrunningreport', array("values"=>$values));
	}
	
	private function vehicleincomeReport($values)
	{
		if (\Request::isMethod('post'))
		{
			//$values["test"];
	
			$resp1 = array("data"=>array());
			if(!isset($values["fromdate"]) || !isset($values["todate"])){
				echo json_encode(array("total"=>0, "data"=>array()));
				return ;
			}
			if(isset($values["reporttype1"]) && $values["reporttype1"] == "vehicle_details"){
				$recs = \ClientIncome::where("status","=","ACTIVE")
										->where("clientId","=",$values["clientname"])
										->where("month","=",$values["month"])
										->get();
				$depot_arr = array();
				$depots = \Depot::where("status","=","ACTIVE")->get();
				foreach ($depots as $depot){
					$depot_arr[$depot->id] = $depot->name;
				}
				$vehicle_arr = array();
				$vehicles = \Vehicle::where("status","=","ACTIVE")->get();
				foreach ($vehicles as $vehicle){
					$vehicle_arr[$vehicle->id] = $vehicle->veh_reg;
				}
				foreach($recs as  $rec) {
					$row = array();
					$row["month"] = date("F",strtotime($values["month"]));
					
					if(isset($depot_arr[$rec->depotId])){
						$row["depot"] = $depot_arr[$rec->depotId];
					}
					else {
						$row["depot"] = "";
					}
					
					if(isset($vehicle_arr[$rec->vehicleId])){
						$row["VEHICLE"] = $vehicle_arr[$rec->vehicleId];
					}
					else {
						$row["VEHICLE"] = "";
					}
					$row["GROSS AMOUNT"] = $rec->gross;
					$row["TDS"] = $rec->tds;
					$row["EMI"] = $rec->emi;
					$row["STOPPED"] = $rec->stopped;
					$row["INSURANCE"] = $rec->insurance;
					$row["TOLL GATE"] = $rec->tollgate;
					$row["PARKING"] = $rec->parking;
					$row["OTHER INCOME"] = $rec->otherIncome;
					$row["OTHER DEDUCTIONS"] = $rec->otherDeductions;
					$row["NET"] = $rec->netAmount;
					$row["CLIENT PAID"] = $rec->clientAmount;
					$row["COMMENTS"] = $rec->remarks;
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp);
			}
			 else if(isset($values["typeOfIncome"]) && $values["typeOfIncome"]=="CONTRACT INCOME"){
				if(true){
					$select_args = array();
					$select_args[] = "client_income.month as month";
					$select_args[] = "client_income.tds as tds";
					$select_args[] = "client_income.vehicleId as no_of_veh";
					$select_args[] = "client_income.emi as emi";
					$select_args[] = "client_income.gross as gross_amt";
					$select_args[] = "client_income.netAmount as netAmount";
					$frmdt = date("Y-m-d",strtotime($values["fromdate"]));
					$todt = date("Y-m-d",strtotime($values["todate"]));
					$clientname=$values["clientname"];
					$resp = array();
					$sql = "SELECT client_income.month as month,sum(client_income.tds) as tds, count(client_income.vehicleId) as no_of_veh, sum(client_income.emi) as emi, sum(client_income.gross) as gross_amt, sum(client_income.netAmount) as netAmount,sum(client_income.clientAmount)as received from client_income where month BETWEEN '".$frmdt."' and '".$todt."' and client_income.clientId='".$clientname."' group by client_income.month";
					$recs =  \DB::select(DB::raw($sql));
					$i=1;
					foreach($recs as  $rec) {
						$row = array();
						$row["S.no"] = $i;
						$row["month"] = date("F",strtotime($rec->month));
						$row["no_of_veh"] = '<a href="#modal-table" role="button" data-toggle="modal" onclick="getData(\''.$values["clientname"].'\', \''.$rec->month.'\',\''.$values["fromdate"].'\', \''.$values["todate"].'\')" <span="">'.$rec->no_of_veh.'</a>';
						$row["gross_amt"] = $rec->gross_amt;
						$row["tds"] = $rec->tds;
						$row["emi"] = $rec->emi;
						$row["netAmount"] = $rec->netAmount;
						$row["received"] = $rec->received;
						$row["balance"] = $row["netAmount"]-$row["received"];
						$resp[] = $row;
						$i++;
					}
					$resp1 = array("data"=>$resp);
				}
			}
				
					
					else if(isset($values["typeOfIncome"]) && $values["typeOfIncome"]=="INCOME TRANSATIONS"){
						if(true){
							$select_args = array();
							$select_args[] = "client_income.month as month";
							$select_args[] = "client_income.tds as tds";
							$select_args[] = "client_income.vehicleId as no_of_veh";
							$select_args[] = "client_income.emi as emi";
							$select_args[] = "client_income.gross as gross_amt";
							$select_args[] = "client_income.netAmount as netAmount";
							$frmdt = date("Y-m-d",strtotime($values["fromdate"]));
							$todt = date("Y-m-d",strtotime($values["todate"]));
							$clientname=$values["clientname"];
							$resp = array();
							$sql = "SELECT client_income.month as month, sum(client_income.tds) as tds, count(client_income.vehicleId) as no_of_veh, sum(client_income.emi) as emi, sum(client_income.gross) as gross_amt, sum(client_income.netAmount) as netAmount,sum(client_income.clientAmount)as received from client_income where month BETWEEN '".$frmdt."' and '".$todt."' and client_income.clientId='".$clientname."' group by client_income.month";
							$recs =  \DB::select(DB::raw($sql));
	
							/*$qry=  \ClientIncome::where("client_income.clientId","=",$values["clientname"])
							 ->where("client_income.depotId","=",$values["depot"])
							 ->whereBetween("client_income.month",array($frmdt,$todt))
							 ->count("client_income.vehicleId","as","no_of_veh")
							 ->sum("client_income.tds","as","tds")
							 ->sum("client_income.emi")
							 ->sum("client_income.gross")
							 ->sum("client_income.netAmount");
							 	
							 $recs = $qry->select($select_args)/*->count("client_income.vehicleId")
							 ->sum("client_income.tds")
							 ->sum("client_income.emi")
							 ->sum("client_income.gross")
							 ->sum("client_income.netAmount")
							 ->groupBy("client_income.month")->get();*/
							//print_r($recs); die();
							$i=1;
							foreach($recs as  $rec) {
								$row = array();
								$row["S.no"] = $i;
								$row["month"] = date("F",strtotime($rec->month));
								$row["no_of_veh"] = $rec->no_of_veh;
								$row["gross_amt"] = $rec->gross_amt;
								$row["tds"] = $rec->tds;
								$row["emi"] = $rec->emi;
								$row["netAmount"] = $rec->netAmount;
								$row["received"] = $rec->received;
								$row["balance"] = $row["netAmount"]-$row["received"];
								$resp[] = $row;
								$i++;
							}
							$resp1 = array("data"=>$resp);
						}
											
					}
						
					echo json_encode($resp1);
					return;
		}
		$values['bredcum'] = "CLIENT VEHICLE INCOME REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads = array("s.no","month", "no of veh", "gross amt", "tds", "emi", "net", "received", "balance");
		$values["theads"] = $theads;
	
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "CLIENT VEHICLE TRIPS REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
	
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$form_fields = array();
		$form_field = array("name"=>"typeOfIncome", "content"=>"type Of Income", "readonly"=>"",  "required"=>"required", "type"=>"select","class"=>"form-control chosen-select", "options"=>array("CONTRACT INCOME"=>"CONTRACT INCOME", "INCOME TRANSATIONS"=>"INCOME TRANSACTIONS"));
		$form_fields[] = $form_field;
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.vehicleincome', array("values"=>$values));
	}
	private function report1($values)
	{
		if (\Request::isMethod('post'))
		{
			//$values["test"];
			$resp1 = array("data"=>array());
			if(!isset($values["fromdate"]) || !isset($values["todate"])){
				echo json_encode(array("total"=>0, "data"=>array()));
				return ;
			}
			if(isset($values["reporttype1"]) && $values["reporttype1"] == "bill_details"){
				$bill_arr = array();
				if(isset($values["billType"]) && $values["billType"]=="ALL"){
					$bill_arr = array("Client Income", "Advance","Diesel Hike","Extra Kms","Excess Kms");
				}
				else{
					$bill_arr[] = $values["billType"];
				}
				$recs = \BillPayments::where("status","=","ACTIVE")
										->where("clientId","=",$values["clientname"])
										->where("billMonth","=",$values["month"])
										->whereIn("billType",$bill_arr)->get();
				$depot_arr = array();
				$depots = \Depot::where("status","=","ACTIVE")->get();
				foreach ($depots as $depot){
					$depot_arr[$depot->id] = $depot->name;
				}
				foreach($recs as  $rec) {
					$row = array();
					$row["month"] = date("F",strtotime($values["month"]));
					if(isset($depot_arr[$rec->depotId])){
						$row["depot"] = $depot_arr[$rec->depotId];
					}
					else 
					{
						$row["depot"]="";
					}
					$row["billNo"] = $rec->billNo;
					$row["billdate"] = date("d-m-Y",strtotime($rec->billDate));
					$row["particulars"] = $rec->billParticulars;
					$row["total"] = $rec->totalAmount;
					$row["amount"] = $rec->amountPaid;
					$row["type"] = $rec->billType;
					$row["remarks"] = $rec->remarks;
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp);
			}
			else if(isset($values["typeOfIncome"]) && $values["typeOfIncome"]==$values["typeOfIncome"]){
				if(true){
					$select_args = array();
					$select_args[] = "bill_payments.billMonth as month";
					$select_args[] = "bill_payments.totalAmount as totalAmount";
					$select_args[] = "bill_payments.tdsPercentage as tdsPercentage";
					$select_args[] = "bill_payments.emiAmount as emiAmount";
					$select_args[] = "bill_payments.amountPaid as amountPaid";
					$select_args[] = "bill_payments.billType as billType";
					$select_args[] = "bill_payments.clientId as clientId";
					$frmdt = date("Y-m-d",strtotime($values["fromdate"]));
					$todt = date("Y-m-d",strtotime($values["todate"]));
					$clientname=$values["clientname"];
					$typeofincome=$values["typeOfIncome"];
					$resp = array();
					$sql = "SELECT bill_payments.billMonth as month,bill_payments.clientId as clientId,bill_payments.billType as billType, sum(bill_payments.totalAmount) as totalAmount, sum(bill_payments.tdsPercentage) as tdsPercentage, sum(bill_payments.emiAmount) as emiAmount,sum(bill_payments.amountPaid) as amountPaid from bill_payments where billMonth BETWEEN '".$frmdt."' and '".$todt."' and billType='".$typeofincome."' and bill_payments.clientId='".$clientname."' group by bill_payments.billMonth";
					if($typeofincome=="ALL"){
						$sql = "SELECT bill_payments.billMonth as month,bill_payments.clientId as clientId,bill_payments.billType as billType, sum(bill_payments.totalAmount) as totalAmount, sum(bill_payments.tdsPercentage) as tdsPercentage, sum(bill_payments.emiAmount) as emiAmount,sum(bill_payments.amountPaid) as amountPaid from bill_payments where billMonth BETWEEN '".$frmdt."' and '".$todt."' and bill_payments.clientId='".$clientname."' group by bill_payments.billMonth";
					}
					$recs =  \DB::select(DB::raw($sql));
					$i=1;
					$tds_amt = 0;
					foreach($recs as  $rec) {
						$row = array();
						$row["S.no"] = $i;
					    $row["month"] = '<a href="#modal-table" role="button" data-toggle="modal" onclick="getData('.$rec->clientId.', \''.$rec->month.'\',\''.$typeofincome.'\',\''.$values["fromdate"].'\', \''.$values["todate"].'\')" <span="">'.date("F",strtotime($rec->month)).'</a>';
						if($typeofincome=="ALL"){
							$row["billtype"] ="";
						}
						else{
							$row["billtype"] = $rec->billType;
						}
						$row["totalAmount"] = $rec->totalAmount;
						$row["tdsPercentage"] = ($rec->totalAmount*$rec->tdsPercentage)/100;
						$row["emiAmount"] = $rec->emiAmount;
						$row["amountPaid"] = $rec->amountPaid;
						$row["balance"] = ($row["totalAmount"])-$row["amountPaid"];
						$row["totalAmount1"] = "0.00";
						$row["tdsPercentage1"] = "0.00";
						$row["emiAmount1"] = "0.00";
						$row["amountPaid1"] = "0.00";
						$row["balance1"] = "0.00";
						$sql = "SELECT bill_payments.billMonth as month, sum(bill_payments.totalAmount) as totalAmount, sum(bill_payments.tdsPercentage) as tdsPercentage, sum(bill_payments.emiAmount) as emiAmount,sum(bill_payments.amountPaid) as amountPaid from bill_payments where billMonth BETWEEN '2015-01-01' and '".$rec->month."' and billType='".$typeofincome."' and bill_payments.clientId='".$clientname."'";
						if($typeofincome=="ALL"){
							$sql = "SELECT bill_payments.billMonth as month, sum(bill_payments.totalAmount) as totalAmount, sum(bill_payments.tdsPercentage) as tdsPercentage, sum(bill_payments.emiAmount) as emiAmount,sum(bill_payments.amountPaid) as amountPaid from bill_payments where billMonth BETWEEN '2015-01-01' and '".$rec->month."' and bill_payments.clientId='".$clientname."'";
						}
						$recs1 =  \DB::select(DB::raw($sql));
						if(count($recs1)){
							$rec1 = $recs1[0];
							$row["totalAmount1"] = $rec1->totalAmount;
							$row["tdsPercentage1"] = ($rec1->totalAmount*$rec1->tdsPercentage)/100;
							$row["emiAmount1"] = $rec1->emiAmount;
							$row["amountPaid1"] = $rec1->amountPaid;
							$row["balance1"] = $row["totalAmount1"]-$row["amountPaid1"];
						}
						$resp[] = $row;
						$i++;
					}
					$resp1 = array("data"=>$resp);
				}
			}
	
					echo json_encode($resp1);
					return;
			}
		$values['bredcum'] = "CLIENT VEHICLE INCOME REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads = array("s.no","month","bill type", "bill submitted amount", "tds","emi", "received payments","balance","till date bills submitted","tds","emi","till date received amount","till date balance" );
		$values["theads"] = $theads;
	
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "CLIENT VEHICLE TRIPS REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
	
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
		$parentId = \LookupTypeValues::where("name", "=", "BILLS AND PAYMENT TYPES")->get();
		$billpayments = array();
		if(count($parentId)>0){
			$parentId = $parentId[0];
			$parentId = $parentId->id;
			$billpayments =  \LookupTypeValues::where("parentId","=",$parentId)->get();
		
		}
		$billpayments_arr = array();
		foreach ($billpayments as $billpayment){
			$billpayments_arr[$billpayment->name] = $billpayment->name;
		}
	
		$form_fields = array();
		$form_field = array("name"=>"typeOfIncome", "content"=>"type Of Income", "readonly"=>"",  "required"=>"required", "type"=>"select","class"=>"form-control chosen-select", "options"=>$billpayments_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.report1', array("values"=>$values));
	}
	private function report2($values)
	{
		if (\Request::isMethod('post'))
		{
			//$values["test"];
			$resp1 = array("data"=>array());
			if(!isset($values["fromdate"]) || !isset($values["todate"])){
				echo json_encode(array("total"=>0, "data"=>array()));
				return ;
			}
			if(isset($values["reporttype1"]) && $values["reporttype1"] == "bill_details"){
				$recs = \BillPayments::where("status","=","ACTIVE")
										->where("clientId","=",$values["clientname"])
										->where("billMonth","=",$values["month"])->get();
				foreach($recs as  $rec) {
					$row = array();
					$row["month"] = date("F",strtotime($values["month"]));
					$row["billNo"] = $rec->billNo;
					$row["billdate"] = date("d-m-Y",strtotime($rec->billDate));
					$row["particulars"] = $rec->billParticulars;
					$row["total"] = $rec->totalAmount;
					$row["amount"] = $rec->amountPaid;
					$row["type"] = $rec->billType;
					$row["remarks"] = $rec->remarks;
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp);
			}		
			else if(isset($values["typeOfIncome"]) && $values["typeOfIncome"]==$values["typeOfIncome"]){
				if(true){
					$select_args = array();
					$select_args[] = "bill_payments.billMonth as month";
					$select_args[] = "client_income.gross as gross";
					$select_args[] = "client_income.tds as tds";
					$select_args[] = "client_income.emi as emi";
					$select_args[] = "client_income.otherDeductions as otherDeductions";
					$select_args[] = "bill_payments.clientId as clientId";
					$select_args[] = "client_income.netAmount as netAmount";
					$select_args[] = "bill_payments.billType as billType";
					$frmdt = date("Y-m-d",strtotime($values["fromdate"]));
					$todt = date("Y-m-d",strtotime($values["todate"]));
					$clientname=$values["clientname"];
					$typeofincome = $values["typeOfIncome"];
					$resp = array();
					$sql = "SELECT bill_payments.billMonth as month,bill_payments.billNo as billNo,bill_payments.billDate as billDate,bill_payments.billParticulars as billParticulars,bill_payments.clientId as clientId from bill_payments left join client_income on bill_payments.clientId=client_income.clientId where billMonth BETWEEN '".$frmdt."' and '".$todt."' and billType='".$typeofincome."' and bill_payments.billMonth=client_income.month and bill_payments.clientId='".$clientname."' group by bill_payments.billMonth";
					$recs =  \DB::select(DB::raw($sql));
					$i=1;
					$tot_gross = 0;
					$tot_tds = 0;
					$tot_emi = 0;
					$tot_od = 0;
					$tot_net = 0;
					$names = \Client::All();
					$name_arr = array();
					foreach ($names as $name){
						$name_arr[$name->id] = $name->name;
					}
					foreach($recs as  $rec) {
						$row = array();
						$row["S.no"] = $i;
						$row["month"] = date("F",strtotime($rec->month));
						$row["clientId"] = $name_arr[$rec->clientId];
						$row["bill"] = '<a href="#modal-table" role="button" data-toggle="modal" onclick="getData('.$rec->clientId.', \''.$rec->month.'\',\''.$values["fromdate"].'\', \''.$values["todate"].'\')" <span="">"bill information"</a>';
						$row["gross"] = "0.00";
						$row["tds"] = "0.00";
						$row["emi"] = "0.00";
						$row["otherDeductions"] = "0.00";
						$row["netAmount"] = "0.00";
						$sql = "SELECT sum(client_income.gross) as gross, sum(client_income.tds) as tds, sum(client_income.emi) as emi,sum(client_income.otherDeductions) as otherDeductions,sum(client_income.netAmount) as netAmount from client_income where client_income.month ='".$rec->month."' and client_income.clientId='".$clientname."'";
						$recs1 =  \DB::select(DB::raw($sql));
						//print_r($recs1);die();
						if(count($recs1)){
							$rec1 = $recs1[0];
							$row["gross"] = $rec1->gross;
							$tot_gross = $tot_gross+$rec1->gross;
							$row["tds"] = $rec1->tds;
							$tot_tds = $tot_tds+$rec1->tds;
							$row["emi"] = $rec1->emi;
							$tot_emi = $tot_emi+$rec1->emi;
							$row["otherDeductions"] = $rec1->otherDeductions;
							$tot_od = $tot_od+$rec1->otherDeductions;
							$row["netAmount"] = $rec1->netAmount;
							$tot_net = $tot_net+$rec1->netAmount;
						}
							$resp[] = $row;
							$i++;
					}
					$resp1 = array("data"=>$resp,"tot_gross"=>$tot_gross,"tot_tds"=>$tot_tds,"tot_emi"=>$tot_emi,"tot_od"=>$tot_od,"tot_net"=>$tot_net);
				}
			}
			echo json_encode($resp1);
			return;
		}
		$values['bredcum'] = "REPORT2";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads = array("s.no","month", "clinet name","bill information","gross amount", "tds", "emi","other dedutions","net");
		$values["theads"] = $theads;
	
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "CLIENT VEHICLE TRIPS REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
	
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$form_fields = array();
		//$form_field = array("name"=>"typeOfIncome", "content"=>"type Of Income", "readonly"=>"",  "required"=>"required", "type"=>"select","class"=>"form-control chosen-select", "options"=>array("Client Income"=>"Client Income", "Advance"=>"Advance","Diesel Hike"=>"Diesel Hike","Extra Kms"=>"Extra Kms","Excess Kms"=>"Excess Kms"));
		$form_field = array("name"=>"typeOfIncome", "content"=>"type Of Income", "readonly"=>"",  "required"=>"required", "type"=>"select","class"=>"form-control chosen-select", "options"=>array("Client Income"=>"Client Income"));
		$form_fields[] = $form_field;
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.report2', array("values"=>$values));
	}
	private function report3($values)
	{
		if (\Request::isMethod('post'))
		{
				//$values["test"];
				$resp1 = array("data"=>array());
				$fromDt = date("Y-m-d", strtotime($values["fromdate"]));
				$toDt = date("Y-m-d", strtotime($values["todate"]));
				$select_args = array();
				$select_args[] = "client_income.month as month";
				$select_args[] = "client_income.depotId as depotId";
				$select_args[] = "client_income.billNo as billNo";
				$select_args[] = "client_income.vehicleId as vehicleId";
				$select_args[] = "client_income.clientId as clientId";
				$select_args[] = "client_income.gross as gross";
				$select_args[] = "client_income.tds as tds";
				$select_args[] = "client_income.emi as emi";
				$select_args[] = "client_income.otherDeductions as otherDeductions";
				$select_args[] = "client_income.clientAmount as clientAmount";
				$select_args[] = "client_income.netAmount as netAmount";
				$clientname=$values["clientname"];
				$resp = array();
				$recs = \ClientIncome::where("status","=","ACTIVE")
										->where("clientId","=",$values["clientname"])
										->whereBetween("month",array($fromDt,$toDt))
										->select($select_args)->orderBy("month")->get();
				//print_r($recs);die();
				$i=1;
				$tot_gross = 0;
				$tot_tds = 0;
				$tot_emi = 0;
				$tot_od = 0;
				$tot_net = 0;
				$tot_client = 0;
				$tot_bal = 0;
				$depots = \Depot::All();
				$depot_arr = array();
				foreach ($depots as $depot){
					$depot_arr[$depot->id] = $depot->name;
				}
				$names = \Client::All();
				$name_arr = array();
				foreach ($names as $name){
					$name_arr[$name->id] = $name->name;
				}
				$vehicles = \Vehicle::All();
				$vehicle_arr = array();
				foreach ($vehicles as $vehicle){
					$vehicle_arr[$vehicle->id] = $vehicle->veh_reg;
				}
				foreach($recs as  $rec) {
					$row = array();
					$row["S.no"] = $i;
					$row["month"] = date("F",strtotime($rec->month));
					
					if(isset($name_arr[$rec->clientId])){
						$row["clientId"] = $name_arr[$rec->clientId];
					}
					else {
						$row["clientId"] = "";
					}
					$row["depotId"] = $depot_arr[$rec->depotId];
					$row["billNo"] = $rec->billNo;
					$row["vehicleId"] = $vehicle_arr[$rec->vehicleId];
					$row["gross"] = $rec->gross;
					$tot_gross = $tot_gross+$rec->gross;
					$row["tds"] = $rec->tds;
					$tot_tds = $tot_tds+$rec->tds;
					$row["emi"] = $rec->emi;
					$tot_emi = $tot_emi+$rec->emi;
					$row["otherDeductions"] = $rec->otherDeductions;
					$tot_od = $tot_od+$rec->otherDeductions;
					$row["netAmount"] = $rec->netAmount;
					$tot_net = $tot_net+$rec->netAmount;
					$row["clientAmount"] = $rec->clientAmount;
					$tot_client = $tot_client+$rec->clientAmount;
					$row["balance"] = $row["netAmount"]-$row["clientAmount"];
					$tot_bal = $tot_bal+$row["balance"];
					$resp[] = $row;
					$i++;
				}
				$resp1 = array("data"=>$resp,"tot_gross"=>$tot_gross,"tot_tds"=>$tot_tds,"tot_emi"=>$tot_emi,"tot_od"=>$tot_od,"tot_net"=>$tot_net,"tot_client"=>$tot_client,"tot_bal"=>$tot_bal);
				
				echo json_encode($resp1);
				return;
		}
		$values['bredcum'] = "CLIENT VEHICLE INCOME REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads = array("s.no","month", "clinet name", "branch/depo", "bill no", "vehicle no", "gross amount", "tds", "emi","other deductions","net","client paid","balance");
		$values["theads"] = $theads;
	
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "CLIENT VEHICLE TRIPS REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
	
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$form_fields = array();
		//$form_field = array("name"=>"typeOfIncome", "content"=>"type Of Income", "readonly"=>"",  "required"=>"required", "type"=>"select","class"=>"form-control chosen-select", "options"=>array("Client Income"=>"Client Income", "Advance"=>"Advance","Diesel Hike"=>"Diesel Hike","Extra Kms"=>"Extra Kms","Excess Kms"=>"Excess Kms"));
		//$form_fields[] = $form_field;
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.report3', array("values"=>$values));
	}
	
	
	private function report4($values)
	{
		if (\Request::isMethod('post'))
		{
			//$values["test"];
			$resp1 = array("data"=>array());
			if(!isset($values["fromdate"]) || !isset($values["todate"])){
				echo json_encode(array("total"=>0, "data"=>array()));
				return ;
			}
			if(isset($values["reporttype1"]) && $values["reporttype1"] == "bill_details"){
				$bill_arr = array();
				if(isset($values["billType"]) && $values["billType"]=="ALL"){
					$bill_arr = array("Client Income", "Advance","Diesel Hike","Extra Kms","Excess Kms");
				}
				else{
					$bill_arr[] = $values["billType"];
				}
				$recs = \BillPayments::where("status","=","ACTIVE")
										->where("clientId","=",$values["clientname"])
										->where("billMonth","=",$values["month"])
										->whereIn("billType",$bill_arr)
										->where("amountPaid",">",0)->get();
				foreach($recs as  $rec) {
					$row = array();
					$row["month"] = date("F",strtotime($values["month"]));
					$row["billNo"] = $rec->billNo;
					$row["billdate"] = date("d-m-Y",strtotime($rec->billDate));
					$row["PAID AMOUNT"] = $rec->amountPaid;
					$row["PAID DATE"] = date("d-m-Y",strtotime($rec->paidDate));
					if($rec->paymentType != "cash"){
						if($rec->paymentType == "ecs" || $rec->paymentType == "neft" || $rec->paymentType == "rtgs" || $rec->paymentType == "cheque_debit" || $rec->paymentType == "cheque_credit"){
							$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
							$bank_dt = \BankDetails::where("id","=",$rec->bankAccount)->first();
							if(count($bank_dt)>0){
								$rec->paymentType = $rec->paymentType."Bank A/c : ".$bank_dt->bankName."( ".$bank_dt->accountNo.")<br/>";
							}
							$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
						}
						if($rec->paymentType == "credit_card" || $rec->paymentType == "debit_card"){
							$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
							$bank_dt = \Cards::where("id","=",$rec->bankAccount)->first();
							if(count($bank_dt)>0){
								$rec->paymentType = $rec->paymentType."Card Details : ".$bank_dt->cardNumber."( ".$bank_dt->cardHolderName.")";
							}
							$rec->paymentType = $rec->paymentType."<br/>Ref No : ".$rec->chequeNumber;
						}
						if($rec->paymentType == "dd"){
							$rec->paymentType = "Payment Type : ".$rec->paymentType."<br/>";
							$rec->paymentType = $rec->paymentType."Ref No : ".$rec->chequeNumber;
						}
					}
					$row["PAYMENT TYPE"] = $rec->paymentType;
					$row["BILL TYPE"] = $rec->billType;
					$row["remarks"] = $rec->remarks;
					$resp[] = $row;
				}
				$resp1 = array("data"=>$resp);
			}
			else if(isset($values["typeOfIncome"]) && $values["typeOfIncome"]==$values["typeOfIncome"]){
				if(true){
					$select_args = array();
					$select_args[] = "bill_payments.billMonth as month";
					$select_args[] = "bill_payments.totalAmount as totalAmount";
					$select_args[] = "bill_payments.tdsPercentage as tdsPercentage";
					$select_args[] = "bill_payments.emiAmount as emiAmount";
					$select_args[] = "bill_payments.amountPaid as amountPaid";
					$select_args[] = "bill_payments.billType as billType";
					$select_args[] = "bill_payments.clientId as clientId";
					$frmdt = date("Y-m-d",strtotime($values["fromdate"]));
					$todt = date("Y-m-d",strtotime($values["todate"]));
					$clientname=$values["clientname"];
					$typeofincome=$values["typeOfIncome"];
					$resp = array();
					$sql = "SELECT bill_payments.billMonth as month,bill_payments.clientId as clientId,bill_payments.billType as billType, sum(bill_payments.totalAmount) as totalAmount, sum(bill_payments.tdsPercentage) as tdsPercentage, sum(bill_payments.emiAmount) as emiAmount,sum(bill_payments.amountPaid) as amountPaid from bill_payments where billMonth BETWEEN '".$frmdt."' and '".$todt."' and billType='".$typeofincome."' and bill_payments.clientId='".$clientname."' and amountPaid>0 group by bill_payments.billMonth";
					if($typeofincome=="ALL"){
						$sql = "SELECT bill_payments.billMonth as month,bill_payments.clientId as clientId,bill_payments.billType as billType, sum(bill_payments.totalAmount) as totalAmount, sum(bill_payments.tdsPercentage) as tdsPercentage, sum(bill_payments.emiAmount) as emiAmount,sum(bill_payments.amountPaid) as amountPaid from bill_payments where billMonth BETWEEN '".$frmdt."' and '".$todt."' and bill_payments.clientId='".$clientname."' and amountPaid>0 group by bill_payments.billMonth";
					}
					$recs =  \DB::select(DB::raw($sql));
					$i=1;
					$tds_amt = 0;
					foreach($recs as  $rec) {
						$row = array();
						$row["S.no"] = $i;
						$row["month"] = '<a href="#modal-table" role="button" data-toggle="modal" onclick="getData('.$rec->clientId.', \''.$rec->month.'\',\''.$typeofincome.'\',\''.$values["fromdate"].'\', \''.$values["todate"].'\')" <span="">'.date("F",strtotime($rec->month)).'</a>';
						if($typeofincome=="ALL"){
							$row["billtype"] = "";
						}
						else {
							$row["billtype"] = $rec->billType;
						}
						$row["totalAmount"] = $rec->totalAmount;
						$row["tdsPercentage"] = ($rec->totalAmount*$rec->tdsPercentage)/100;
						$row["emiAmount"] = $rec->emiAmount;
						$row["amountPaid"] = $rec->amountPaid;
						$row["balance"] = ($row["totalAmount"])-$row["amountPaid"];
						$row["totalAmount1"] = "0.00";
						$row["tdsPercentage1"] = "0.00";
						$row["emiAmount1"] = "0.00";
						$row["amountPaid1"] = "0.00";
						$row["balance1"] = "0.00";
						$sql = "SELECT bill_payments.billMonth as month, sum(bill_payments.totalAmount) as totalAmount, sum(bill_payments.tdsPercentage) as tdsPercentage, sum(bill_payments.emiAmount) as emiAmount,sum(bill_payments.amountPaid) as amountPaid from bill_payments where billMonth BETWEEN '2015-01-01' and '".$rec->month."' and billType='".$typeofincome."' and bill_payments.clientId='".$clientname."' and amountPaid>0";
						if($typeofincome=="ALL"){
							$sql = "SELECT bill_payments.billMonth as month, sum(bill_payments.totalAmount) as totalAmount, sum(bill_payments.tdsPercentage) as tdsPercentage, sum(bill_payments.emiAmount) as emiAmount,sum(bill_payments.amountPaid) as amountPaid from bill_payments where billMonth BETWEEN '2015-01-01' and '".$rec->month."' and bill_payments.clientId='".$clientname."' and amountPaid>0";
						}
						$recs1 =  \DB::select(DB::raw($sql));
						if(count($recs1)){
							$rec1 = $recs1[0];
							$row["totalAmount1"] = $rec1->totalAmount;
							$row["tdsPercentage1"] = ($rec1->totalAmount*$rec1->tdsPercentage)/100;
							$row["emiAmount1"] = $rec1->emiAmount;
							$row["amountPaid1"] = $rec1->amountPaid;
							$row["balance1"] = $row["totalAmount1"]-$row["amountPaid1"];
						}
						$resp[] = $row;
						$i++;
					}
					$resp1 = array("data"=>$resp);
				}
			}
			echo json_encode($resp1);
			return;
		}
		$values['bredcum'] = "CLIENT VEHICLE INCOME REPORT";
		$values['home_url'] = 'masters';
		$values['add_url'] = 'loginlog';
		$values['form_action'] = 'loginlog';
		$values['action_val'] = '#';
		$theads = array("s.no","month","bill type", "bill submitted amount", "tds","emi", "received payments","balance","till date bills submitted","tds","emi","till date received amount","till date balance" );
		$values["theads"] = $theads;
	
		//$values["test"];
	
		$form_info = array();
		$form_info["name"] = "getreport";
		$form_info["action"] = "getreport";
		$form_info["method"] = "post";
		$form_info["class"] = "form-horizontal";
		$form_info["back_url"] = "users";
		$form_info["bredcum"] = "CLIENT VEHICLE TRIPS REPORT";
		$form_info["reporttype"] = $values["reporttype"];
	
	
		$emp_arr = array();
		$emp_arr[0] = "All";
		$emps = \Employee::where("status","=","ACTIVE")->orderby("fullName")->get();
		foreach ($emps as $emp){
			$emp_arr[$emp->id] = $emp->fullName;
		}
	
		$clients =  AppSettingsController::getEmpClients();
		$clients_arr = array();
		foreach ($clients as $client){
			$clients_arr[$client['id']] = $client['name'];
		}
	
		$form_fields = array();
		$form_field = array("name"=>"typeOfIncome", "content"=>"type Of Income", "readonly"=>"",  "required"=>"required", "type"=>"select","class"=>"form-control chosen-select", "options"=>array("ALL"=>"ALL", "Client Income"=>"Client Income", "Advance"=>"Advance","Diesel Hike"=>"Diesel Hike","Extra Kms"=>"Extra Kms","Excess Kms"=>"Excess Kms"));
		$form_fields[] = $form_field;
		$form_field = array("name"=>"clientname", "content"=>"client name", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>$clients_arr);
		$form_fields[] = $form_field;
		$form_field = array("name"=>"daterange", "content"=>"date range", "readonly"=>"",  "required"=>"required","type"=>"daterange", "class"=>"form-control");
		$form_fields[] = $form_field;
		$form_field = array("name"=>"reporttype", "value"=>$values["reporttype"], "content"=>"", "readonly"=>"",  "required"=>"required","type"=>"hidden");
		$form_fields[] = $form_field;
		$form_info["form_fields"] = $form_fields;
		$values['form_info'] = $form_info;
	
		$form_info["form_fields"] = array();
		$modals[] = $form_info;
		$values["modals"] = $modals;
		//$values['provider'] = "loginlog";
	
		return View::make('reports.report4', array("values"=>$values));
	}
	
}
