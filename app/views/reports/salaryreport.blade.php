@extends('masters.master')
	@section('inline_css')
		<style>
			.pagination {
			    display: inline-block;
			    padding-left: 0;
			    padding-bottom:10px;
			    margin: 0px 0;
			    border-radius: 4px;
			}
			.dataTables_wrapper .row:last-child {
			    border-bottom: 0px solid #e0e0e0;
			    padding-top: 5px;
			    padding-bottom: 0px;
			    background-color: #EFF3F8;
			}
			th {
			    white-space: nowrap;
			}
			td {
			    white-space: nowrap;
			    min-width: 100%;
			}
			table {
				min-width: 100% !important;
			}
			panel-group .panel {
			    margin-bottom: 20px;
			    border-radius: 4px;
			}
			label{
				text-align: right;
				margin-top: 3px;
			}
			.ace-file-input {
			    text-align: left !important;
			}
			.chosen-container{
			  width: 100% !important;
			}
			#loading {
			  position: absolute;
			  top: 50%;
			  left: 50%;
			  width: 32px;
			  height: 32px;
			  /* 1/2 of the height and width of the actual gif */
			  margin: -16px 0 0 -16px;
			  z-index: 100;
			}
			.dt-buttons{
			  margin-top: 5px;
			}
		</style>
	@section('page_css')
		<link rel="stylesheet" href="../assets/css/jquery-ui.custom.css" />
		<link rel="stylesheet" href="../assets/css/chosen1.css" />
		<link rel="stylesheet" href="../assets/css/bootstrap-datepicker3.css"/>
		<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.1.1/css/buttons.dataTables.min.css"/>
	@stop
		
	@stop
	
	@section('bredcum')	
		<small>
			REPORTS
			<i class="ace-icon fa fa-angle-double-right"></i>
			{{$values['bredcum']}}
		</small>
	@stop

	@section('page_content')
		<div id="accordion1" class="col-xs-offset-0 col-xs-12 accordion-style1 panel-group" style="width: 99%;">			
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#TEST">
							<i class="ace-icon fa fa-angle-down bigger-110" data-icon-hide="ace-icon fa fa-angle-down" data-icon-show="ace-icon fa fa-angle-right"></i>
							&nbsp;SEARCH BY
						</a>
					</h4>
				</div>
				<div class="panel-collapse collapse in" id="TEST">
					<div class="panel-body" style="padding: 0px">
						<?php $form_info = $values["form_info"]; ?>
						@include("reports.add3colform",$form_info)						
					</div>
				</div>
			</div>
		</div>	
		</div>	
		<div id="processing" class="modal-backdrop fade in"><div id = "loading" > <i  class="ace-icon fa fa-spinner fa-spin orange bigger-250"></i>	</div></div>
		
		
		<div class="row" >
			<div id="table1">
				<div class="row col-xs-12" style="padding-left:2%; padding-top: 1%">
					<?php if(!isset($values['entries'])) $values['entries']=10; if(!isset($values['branch'])) $values['branch']=0; if(!isset($values['page'])) $values['page']=1; ?>
					<div class="clearfix">
						<div id="tableTools-container1" class="pull-right tableTools-container"></div>
					</div>
					<div class="table-header" style="margin-top: 9px;">
						Results for BRANCH SUMMARY SALARY REPORT	 
						<span style="float:right; font-size: 14px; font-weight: bold;">&nbsp;ACTSAL : <span id="tot_actsal">0</span>&nbsp;&nbsp;DUE AMOUNT : <span id="tot_dueamt">0</span>&nbsp;&nbsp;LEAVE AMOUNT: <span id="tot_leaveamt">0</span> &nbsp;&nbsp;OTHER AMOUNT: <span id="tot_otheramt">0</span>&nbsp;&nbsp;PF: <span id="tot_pf">0</span>&nbsp;&nbsp;ESI: <span id="tot_esi">0</span>&nbsp;&nbsp;PAIDSAL: <span id="tot_psal">0</span>&nbsp;&nbsp;</span>
					</div>
					<!-- div.table-responsive -->
					<!-- div.dataTables_borderWrap -->
					<div>
						<table id="dynamic-table1" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<td>BRANCH NAME</td>
									<td>ACTUAL SALARY</td>
									<td>DUE DEDUCTIONS</td>
									<td>LEAVE DEDUCTION</td>
									<td>OTHER AMOUNT</td>
									<td>PF</td>
									<td>ESI</td>
									<td>PAID SALARY</td>
								</tr>
							</thead>
							<tbody id="tbody1">
							</tbody>
						</table>								
					</div>
				</div>					
			</div>
			<div id="table2">
				<div class="row col-xs-12" style="padding-left:2%; padding-top: 1%">
					<?php if(!isset($values['entries'])) $values['entries']=10; if(!isset($values['branch'])) $values['branch']=0; if(!isset($values['page'])) $values['page']=1; ?>
					<div class="clearfix">
						<div id="tableTools-container2" class="pull-right tableTools-container"></div>
					</div>
					<div class="table-header" style="margin-top: 10px;">
						Results for EMPLOYEE WISE SALARY REPORT
						<span style="float:right; font-size: 14px; font-weight: bold;">ACTSAL : <span id="ttl_actsal">0</span>&nbsp;&nbsp;DUE AMOUNT : <span id="ttl_dueamt">0</span>&nbsp;&nbsp;LEAVE AMOUNT: <span id="ttl_leaveamt">0</span> &nbsp;&nbsp;OTHER AMOUNT: <span id="ttl_otheramt">0</span>&nbsp;&nbsp;PF: <span id="ttl_pf">0</span>&nbsp;&nbsp;ESI: <span id="ttl_esi">0</span>&nbsp;&nbsp;PAIDSAL: <span id="ttl_psal">0</span>&nbsp;&nbsp;</span>
					</div>
					<!-- div.table-responsive -->
					<!-- div.dataTables_borderWrap -->
					<div>
						<table id="dynamic-table2" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<td>BRANCH NAME</td>
									<td>EMP CODE</td>
									<td>EMP NAME</td>
									<td>SALARY MONTH</td>
									<td>SALARY PAID ON</td>
									<td>ACTUAL SALARY</td>
									<td>DUE DEDUCTIONS</td>
									<td>LEAVE DEDUCTION</td>
									<td>OTHER AMOUNT</td>
									<td>PF</td>
									<td>ESI</td>
									<td>PAID SALARY</td>
								</tr>
							</thead>
							<tbody id="tbody2">
							</tbody>
						</table>								
					</div>
				</div>					
			</div>
			<div id="table3">
				<div class="row col-xs-12" style="padding-left:2%; padding-top: 1%">
					<?php if(!isset($values['entries'])) $values['entries']=10; if(!isset($values['branch'])) $values['branch']=0; if(!isset($values['page'])) $values['page']=1; ?>
					<div class="clearfix">
						<div id="tableTools-container3" class="pull-right tableTools-container"></div>
					</div>
					<div class="table-header" style="margin-top: 10px;">
						Results for <?php if(isset($values['type'])){ echo '"'.strtoupper($values['type'])." REPORT".'"';} ?>	
						<span style="float:right; font-size: 16px; font-weight: bold;">TOTAL AMOUNT : <span id="totpfamt">0&nbsp;&nbsp;&nbsp;&nbsp;</span></span>				 
					</div>
					<!-- div.table-responsive -->
					<!-- div.dataTables_borderWrap -->
					<div>
						<table id="dynamic-table3" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<td>BRANCH NAME</td>
									<td>EMP CODE</td>
									<td>EMP NAME</td>
									<td>SALARY MONTH</td>
									<td>PAID ON</td>
									<td>PF/ESI AMOUNT</td>
								</tr>
							</thead>
							<tbody id="tbody2">
							</tbody>
						</table>								
					</div>
				</div>					
			</div>
			
			<div id="table4">
				<div class="row col-xs-12" style="padding-left:2%; padding-top: 1%">
					<?php if(!isset($values['entries'])) $values['entries']=10; if(!isset($values['branch'])) $values['branch']=0; if(!isset($values['page'])) $values['page']=1; ?>
					<div class="clearfix">
						<div id="tableTools-container2" class="pull-right tableTools-container"></div>
					</div>
					<div class="table-header" style="margin-top: 10px;">
						Results for DETAILED SALARY REPORT	
						<span style="float:right; font-size: 14px; font-weight: bold;">DUE AMOUNT : <span id="tot_dueamount">0</span>&nbsp;&nbsp;LEAVE AMOUNT: <span id="tot_leaveamount">0</span> &nbsp;&nbsp;OTHER AMOUNT: <span id="tot_otheramount">0</span>&nbsp;&nbsp;PREVSAL: <span id="tot_prevsal">0</span>&nbsp;&nbsp;INCREMNET AMOUNT: <span id="tot_increament">0</span>&nbsp;&nbsp;GROSS: <span id="tot_grossamount">0</span>&nbsp;&nbsp;NET: <span id="tot_netamount">0</span>&nbsp;&nbsp;</span>				 				 
					</div>
					<!-- div.table-responsive -->
					<!-- div.dataTables_borderWrap -->
					<div>
						<table id="dynamic-table4" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<td>BRANCH NAME</td>
									<td>EMP NAME</td>
									<td>DESIGNATION</td>
									<td>SAL MONTH</td>
									<td>SAL PAID ON</td>
									<td>JOIN DATE</td>
									<td>ACTUAL SAL</td>
									<td>TOT WORKING DAYS</td>
									<td>CAS. LEAVES</td>
									<td>LEAVES</td>
									<td>EARLY TERM. DAYS</td>
									<td>EMP WORKING DAYS</td>
									<td>DUE AMOUNT</td>
									<td>LEAVE AMT</td>
									<td>OTHER AMT</td>
									<td>PREV SAL</td>
									<td>INCR AMT</td>
									<td>INCR DATE</td>
									<td>GROSS SAL</td>
									<td>NET SAL</td>
									<td>CARD NO</td>
									<td>COMMENTS</td>
								</tr>
							</thead>
							<tbody id="tbody2">
							</tbody>
						</table>								
					</div>
				</div>					
			</div>
			
			<div id="table5">
				<div class="row col-xs-12" style="padding-left:2%; padding-top: 1%">
					<?php if(!isset($values['entries'])) $values['entries']=10; if(!isset($values['branch'])) $values['branch']=0; if(!isset($values['page'])) $values['page']=1; ?>
					<div class="clearfix">
						<div id="tableTools-container3" class="pull-right tableTools-container"></div>
					</div>
					<div class="table-header" style="margin-top: 10px;">
						Results for BANK SALARY TRANSACTIONS
						<span style="float:right; font-size: 14px; font-weight: bold;">ACTSAL : <span id="ttl_actsalamount">0</span>&nbsp;&nbsp;DUE AMOUNT : <span id="ttl_dueamount">0</span>&nbsp;&nbsp;LEAVE AMOUNT: <span id="ttl_leaveamount">0</span> &nbsp;&nbsp;OTHER AMOUNT: <span id="ttl_otheramount">0</span>&nbsp;&nbsp;PF: <span id="ttl_pfamount">0</span>&nbsp;&nbsp;ESI: <span id="ttl_esiamount">0</span>&nbsp;&nbsp;PAIDSAL: <span id="ttl_psalamount">0</span>&nbsp;&nbsp;</span>
					</div>
					<!-- div.table-responsive -->
					<!-- div.dataTables_borderWrap -->
					<div>
						<table id="dynamic-table5" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<td>BRANCH NAME</td>
									<td>EMP NAME - CODE</td>
									<td>SALARY MONTH</td>
									<td>PAID ON</td>
									<td>PMT INFO</td>
									<td>ACT SALARY</td>
									<td>DUE DED</td>
									<td>LEAVE DED</td>
									<td>OTHERS</td>
									<td>PF</td>
									<td>ESI</td>
									<td>PAID SALARY</td>
								</tr>
							</thead>
							<tbody id="tbody5">
							</tbody>
						</table>								
					</div>
				</div>					
			</div>
			
		</div>
		

		<?php 
			if(isset($values['modals'])) {
				$modals = $values['modals'];
				foreach ($modals as $modal){
		?>
				@include('masters.layouts.modalform', $modal)
		<?php }} ?>
		
		<div id="edit" class="modal" tabindex="-1">
			<div class="modal-dialog" style="width: 80%">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="blue bigger">Please fill the following form fields</h4>
					</div>
	
					<div class="modal-body" id="modal_body">
					</div>
	
					<div class="modal-footer">
						<button class="btn btn-sm" data-dismiss="modal">
							<i class="ace-icon fa fa-times"></i>
							Close
						</button>
					</div>
				</div>
			</div>
		</div><!-- PAGE CONTENT ENDS -->
		
	@stop
	
	@section('page_js')
		<!-- page specific plugin scripts -->
		<script src="../assets/js/dataTables/jquery.dataTables.js"></script>
		<script src="../assets/js/dataTables/jquery.dataTables.bootstrap.js"></script>
		<script src="../assets/js/dataTables/extensions/buttons/dataTables.buttons.js"></script>
		<script src="../assets/js/dataTables/extensions/buttons/buttons.print.js"></script>
		<script type="text/javascript" src="../assets/js/dataTables/js/jszip.min.js"></script>
		<script type="text/javascript" src="../assets/js/dataTables/js/pdfmake.min.js"></script>
		<script type="text/javascript" src="../assets/js/dataTables/js/vfs_fonts.js"></script>
		<script type="text/javascript" src="../assets/js/dataTables/js/buttons.html5.min.js"></script>
		<script type="text/javascript" src="../assets/js/dataTables/js/buttons.colVis.min.js"></script>
		<script src="../assets/js/date-time/moment.js"></script>
		<script src="../assets/js/date-time/daterangepicker.js"></script>		
		<script src="../assets/js/date-time/bootstrap-datepicker.js"></script>
		<script src="../assets/js/bootbox.js"></script>
		<script src="../assets/js/chosen.jquery.js"></script>
		<script src="../assets/js/autosize.js"></script>
	@stop
	
	@section('inline_js')
		<!-- inline scripts related to this page -->
		<script type="text/javascript">
			$("#processing").hide();
			$("#fuelstationid").hide();
			$("#vehicleid").hide();
			$("#driverid").hide();
			$("#employeetype").attr("disabled",true);
			$("#officebranch").attr("disabled",true);
			$("#clientname").attr("disabled",true);
			$("#depot").attr("disabled",true);
			$("#paidfrombranch").attr("disabled",true);
			$("#employee").attr("disabled",true);
			$("#month").attr("disabled",true);
			reporttype = "";

			function generateReport(){
				reporttype = "ticket_corgos_summery";
				paginate(1);
			}

			function showSelectionType(val){
				$("#employeetype").attr("disabled",true);
				$("#officebranch").attr("disabled",true);
				$("#clientname").attr("disabled",true);
				$("#depot").attr("disabled",true);
				$("#show_employees").attr("disabled",true);
				$("#paidfrombranch").attr("disabled",true);
				$("#employee").attr("disabled",true);
				$("#month").attr("disabled",true);
				if(val == "detailed_salary_report"){
					$("#employeetype").attr("disabled",false);
					$("#month").attr("disabled",false);
				}
				if(val == "branch_wise_salary_report" || val == "bank_payment_report"){
					$("#employee").attr("disabled",true);
					$("#paidfrombranch").attr("disabled",false);
				}
				if(val == "employee_wise_salary_report"){
					$("#paidfrombranch").attr("disabled",true);
					$("#employee").attr("disabled",false);
				}
				if(val == "pf_report" || val == "esi_report"){
					$("#paidfrombranch").attr("disabled",true);
					$("#employee").attr("disabled",true);
				}
				$('.chosen-select').trigger('chosen:updated');
			}

			function paginate(page){
				reporttype = $("#typeofreport").val();
				if(reporttype == ""){
					alert("select type of report");
					return;
				}
				
				paidfrom = $("#paidfrombranch").val();
				if((reporttype == "branch_wise_salary_report" || reporttype == "bank_payment_report") && paidfrom == ""){
					alert("select paid from branch");
					return;
				}

				if(reporttype == "detailed_salary_report"){
					employeetype = $('#employeetype').val();
					if(employeetype == ""){
						alert("please select employee type");
						return;
					}

					month = $('#month').val();
					if(month == ""){
						alert("please select month");
						return;
					}
					
					officebranch = $('#officebranch').val();
					if(employeetype == "OFFICE" && officebranch==""){
						alert("please select office branch");
						return;
					}
	
					clientname = $('#clientname').val();
					clientbranch = $('#depot').val();
					if(employeetype == "CLIENT BRANCH" && (clientname=="" || clientbranch=="")){
						alert("please select client name and  branch");
						return;
					}
				}
				
				employee = $("#employee").val();
				if(reporttype == "employee_wise_salary_report" &&  employee == ""){
					alert("select employee");
					return;
				}
				fdt = $("#fromdate").val();
				if(fdt == ""){
					alert("select daterange FROM date");
					return;
				}
				tdt = $("#fromdate").val();
				if(tdt == ""){
					alert("select daterange TO date");
					return;
				}
				dt = fdt+" - "+tdt;	
				var form=$("#getreport");


				$("#processing").show();
				$.ajax({
			        type:"POST",
			        url:form.attr("action"),
			        data:form.serialize(),
			        success: function(response){
			           //alert(response);  
			           var json = JSON.parse(response);
			           var data  = json['data'];
			           var arr = [];
			           for(var i = 0; i < data.length; i++) {
			        	    var parsed = data[i];
			        	    //var parsed = json[i];
			        	    var row = [];
			        	    for(var x in parsed){
			        	    	row.push(parsed[x]);
				            }
				            arr.push(row);
			        	}
			        	if(reporttype == "branch_wise_salary_report"){
			        		tot_actsal = json["tot_actsal"];
			        		tot_dueamt = json["tot_due"];
                            tot_otheramt = json["tot_other"];
                            tot_leaveamt = json["tot_leave"];
                            tot_pf = json["tot_pf"];
                            tot_esi = json["tot_esi"];
                            tot_psal = json["tot_psal"];
                            $("#tot_actsal").html(tot_actsal);
			        		$("#tot_dueamt").html(tot_dueamt);
			        		$("#tot_otheramt").html(tot_otheramt);
			        		$("#tot_leaveamt").html(tot_leaveamt);
			        		$("#tot_pf").html(tot_pf);
			        		$("#tot_esi").html(tot_esi);
			        		$("#tot_psal").html(tot_psal);
							myTable1.clear().draw();
							myTable1.rows.add(arr); // Add new data
							myTable1.columns.adjust().draw(); // Redraw 
							$("#table1").show();
							$("#table2").hide();
							$("#table3").hide();
							$("#table4").hide();
							$("#table5").hide();			
			        	}
			        	else if(reporttype == "employee_wise_salary_report"){
			        		tot_actsal = json["tot_actsal"];
			        		tot_dueamt = json["tot_due"];
                            tot_otheramt = json["tot_other"];
                            tot_leaveamt = json["tot_leave"];
                            tot_pf = json["tot_pf"];
                            tot_esi = json["tot_esi"];
                            tot_psal = json["tot_psal"];
                            $("#ttl_actsal").html(tot_actsal);
			        		$("#ttl_dueamt").html(tot_dueamt);
			        		$("#ttl_otheramt").html(tot_otheramt);
			        		$("#ttl_leaveamt").html(tot_leaveamt);
			        		$("#ttl_pf").html(tot_pf);
			        		$("#ttl_esi").html(tot_esi);
			        		$("#ttl_psal").html(tot_psal);
			        		//alert(tot_actsal+"-"+tot_dueamt);
							myTable2.clear().draw();
							myTable2.rows.add(arr); // Add new data
							myTable2.columns.adjust().draw(); // Redraw theDataTable
							$("#table1").hide();
							$("#table2").show();	
							$("#table3").hide();
							$("#table4").hide();
							$("#table5").hide();		
			        	}
			        	else if(reporttype == "pf_report"|| reporttype == "esi_report"){
			        		tot_pfamount = json["tot_pf"];
			        		$("#totpfamt").html(tot_pfamount);
			        		tot_esiamount = json["tot_esi"];
			        		$("#totesiamt").html(tot_esiamount);
							myTable3.clear().draw();
							myTable3.rows.add(arr); // Add new data
							myTable3.columns.adjust().draw(); // Redraw theDataTable
							$("#table1").hide();
							$("#table2").hide();
							$("#table3").show();
							$("#table4").hide();
							$("#table5").hide();		
			        	}
			        	else if(reporttype == "detailed_salary_report"){
			        		tot_dueamt = json["total_due"];
                            tot_otheramt = json["total_other"];
                            tot_leaveamt = json["total_leave"];
                            tot_prevsal = json["total_prev"];
                            tot_increament = json["total_incre"];
                            tot_gross = json["total_gross"];
                            tot_net = json["total_net"];
			        		$("#tot_dueamount").html(tot_dueamt);
			        		$("#tot_otheramount").html(tot_otheramt);
			        		$("#tot_leaveamount").html(tot_leaveamt);
			        		$("#tot_prevsal").html(tot_prevsal);
			        		$("#tot_increament").html(tot_increament);
			        		$("#tot_grossamount").html(tot_gross);
			        		$("#tot_netamount").html(tot_net);
			        		//alert(tot_dueamt+"-"+tot_otheramt+"="+tot_leaveamt+"-"+tot_prevsal);
							myTable4.clear().draw();
							myTable4.rows.add(arr); // Add new data
							myTable4.columns.adjust().draw(); // Redraw theDataTable
							$("#table1").hide();
							$("#table2").hide();
							$("#table3").hide();
							$("#table4").show();
							$("#table5").hide();		
			        	}
			        	else if(reporttype == "bank_payment_report"){
			        		tot_actsal = json["tot_actsal"];
			        		tot_dueamt = json["tot_due"];
                            tot_otheramt = json["tot_other"];
                            tot_leaveamt = json["tot_leave"];
                            tot_pf = json["tot_pf"];
                            tot_esi = json["tot_esi"];
                            tot_psal = json["tot_psal"];
                            $("#ttl_actsalamount").html(tot_actsal);
			        		$("#ttl_dueamount").html(tot_dueamt);
			        		$("#ttl_otheramount").html(tot_otheramt);
			        		$("#ttl_leaveamount").html(tot_leaveamt);
			        		$("#ttl_pfamount").html(tot_pf);
			        		$("#ttl_esiamount").html(tot_esi);
			        		$("#ttl_psalamount").html(tot_psal);
							myTable5.clear().draw();
							myTable5.rows.add(arr); // Add new data
							myTable5.columns.adjust().draw(); // Redraw theDataTable
							$("#table1").hide();
							$("#table2").hide();
							$("#table3").hide();
							$("#table4").hide();
							$("#table5").show();		
			        	}
						$("#processing").hide();
			        }
			    });
			}

			function modalEditLookupValue(id, value){
				$("#value1").val(value);
				$("#id1").val(id);
				return;				
			}

			function changeDepot(val){
				$.ajax({
			      url: "getdepotsbyclientId?id="+val,
			      success: function(data) {
				      data = "<option value='0'> ALL </option>"+data;
			    	  $("#depot").html(data);
			    	  $('.chosen-select').trigger("chosen:updated");
			      },
			      type: 'GET'
			    });

				clientId =  $("#clientname").val();
				depotId = $("#depot").val();
			}

			function enableClientDepot(val){
				$("#paidfrombranch").attr("disabled",true);
				if(val == "OFFICE"){
					$("#clientname").attr("disabled",true);
					$("#depot").attr("disabled",true);
					$("#show_employees").attr("disabled",true);
					$("#officebranch").attr("disabled",false);
				}
				else if(val == "CLIENT BRANCH"){
					$("#clientname").attr("disabled",false);
					$("#depot").attr("disabled",false);
					$("#officebranch").attr("disabled",true);
					$("#show_employees").attr("disabled",false);
				}
				$('.chosen-select').trigger("chosen:updated");
			}

			function modalEditTransaction(id){
				//$("#addfields").html('<div style="margin-left:600px; margin-top:100px;"><i class="ace-icon fa fa-spinner fa-spin orange bigger-125" style="font-size: 250% !important;"></i></div>');
				url = "edittransaction?type="+transtype+"&id="+id;
				var ifr=$('<iframe />', {
		            id:'MainPopupIframe',
		            src:url,
		            style:'seamless="seamless" scrolling="no" display:none;width:100%;height:423px; border:0px solid',
		            load:function(){
		                $(this).show();
		            }
		        });
	    	    $("#modal_body").html(ifr);
			}

			$("#reset").on("click",function(){
				$("#{{$form_info['name']}}").reset();
			});

			$("#submit").on("click",function(){
				//$("#{{$form_info['name']}}").submit();
			});

			$("#provider").on("change",function(){
				val = $("#provider option:selected").html();
				window.location.replace('serviceproviders?provider='+val);
			});

			$('.number').keydown(function(e) {
				this.value = this.value.replace(/[^0-9.]/g, ''); 
				this.value = this.value.replace(/(\..*)\./g, '$1');
			});

			$('.file').ace_file_input({
				no_file:'No File ...',
				btn_choose:'Choose',
				btn_change:'Change',
				droppable:false,
				onchange:null,
				thumbnail:false //| true | large
				//whitelist:'gif|png|jpg|jpeg'
				//blacklist:'exe|php'
				//onchange:''
				//
			});
			//pre-show a file name, for example a previously selected file
			//$('#id-input-file-1').ace_file_input('show_file_list', ['myfile.txt'])
		
			//datepicker plugin
			//link
			$('.date-picker').datepicker({
				autoclose: true,
				todayHighlight: true
			})
			//show datepicker when clicking on the icon
			.next().on(ace.click_event, function(){
				$(this).prev().focus();
			});

			//or change it into a date range picker
			$('.input-daterange').datepicker({autoclose:true,todayHighlight: true});

			//$('.input-mask-phone').mask('(999) 999-9999');
			
			<?php 
				if(Session::has('message')){
					echo "bootbox.hideAll();";echo "bootbox.alert('".Session::pull('message')."', function(result) {});";
				}
			?>

			//to translate the daterange picker, please copy the "examples/daterange-fr.js" contents here before initialization
			$('.date-range-picker').daterangepicker({
				'applyClass' : 'btn-sm btn-success',
				'cancelClass' : 'btn-sm btn-default',	
				locale: {
					applyLabel: 'Apply',
					cancelLabel: 'Cancel',
				}
			})
			<?php 
				if(isset($values["daterange"])){
					echo "$('.date-range-picker').val('".$values["daterange"]."')";
				}
			?>
			

			if(!ace.vars['touch']) {
				$('.chosen-select').chosen({allow_single_deselect:true,search_contains: true}); 
				//resize the chosen on window resize
		
				$(window)
				.off('resize.chosen')
				.on('resize.chosen', function() {
					$('.chosen-select').each(function() {
						 var $this = $(this);
						 $this.next().css({'width': $this.parent().width()});
					})
				}).trigger('resize.chosen');
				//resize chosen on sidebar collapse/expand
				$(document).on('settings.ace.chosen', function(e, event_name, event_val) {
					if(event_name != 'sidebar_collapsed') return;
					$('.chosen-select').each(function() {
						 var $this = $(this);
						 $this.next().css({'width': $this.parent().width()});
					})
				});
		
		
				$('#chosen-multiple-style .btn').on('click', function(e){
					var target = $(this).find('input[type=radio]');
					var which = parseInt(target.val());
					if(which == 2) $('#form-field-select-4').addClass('tag-input-style');
					 else $('#form-field-select-4').removeClass('tag-input-style');
				});
			}

			var myTable1 = null;
			var myTable2 = null;
			var myTable3 = null;
			var myTable4 = null;
			var myTable5 = null;

			jQuery(function($) {
					//initiate dataTables plugin
					myTable1 = $('#dynamic-table1')
					//.wrap("<div class='dataTables_borderWrap' />")   //if you are applying horizontal scrolling (sScrollX)
					.DataTable( {
						dom:
"<'row'<'col-sm-2'l><'col-sm-7 text-center'B><'col-sm-3'f>>" +
"<'row'<''rt>>" +
"<'row  '<'col-sm-5'i><'col-sm-7'p>>",
						buttons: [
							{
								extend:'colvis',
								text : "<i class='fa fa-search bigger-110 blue'></i> <span class='hidden'>Show/hide columns</span>"
							},
							{
								extend: 'excelHtml5',
								text : "<i class='fa fa-file-excel-o bigger-110 green'></i> <span class='hidden'>Export to Excel</span>",
								exportOptions: {
									columns: ':visible'
								}
							},
							{
								extend: 'pdfHtml5',
								text : "<i class='fa fa-file-pdf-o bigger-110 red'></i> <span class='hidden'>Export to PDF</span>",
								exportOptions: {
									columns: ':visible'
								}
							}
							
						], 
						bAutoWidth: false,
						"aoColumns": [
						  null, null, null,  null, null, null, null, null
						],
						"aaSorting": [],
						//"sScrollY": "500px",
						//"bPaginate": false,
						"sScrollX" : "true",
						//"sScrollX": "300px",
						//"sScrollXInner": "120%",
						"bScrollCollapse": true,
						select: {
							style: 'multi'
						}
				    } );
					
					//initiate dataTables plugin
					myTable2 = $('#dynamic-table2')
					//.wrap("<div class='dataTables_borderWrap' />")   //if you are applying horizontal scrolling (sScrollX)
					.DataTable( {
						dom:
"<'row'<'col-sm-2'l><'col-sm-7 text-center'B><'col-sm-3'f>>" +
"<'row'<''rt>>" +
"<'row  '<'col-sm-5'i><'col-sm-7'p>>",
						buttons: [
							{
								extend:'colvis',
								text : "<i class='fa fa-search bigger-110 blue'></i> <span class='hidden'>Show/hide columns</span>"
							},
							{
								extend: 'excelHtml5',
								 title: 'reportTitle',
								text : "<i class='fa fa-file-excel-o bigger-110 green'></i> <span class='hidden'>Export to Excel</span>",
								exportOptions: {
									columns: ':visible'
								}
							},
							{
								extend: 'pdfHtml5',
								text : "<i class='fa fa-file-pdf-o bigger-110 red'></i> <span class='hidden'>Export to PDF</span>",
								exportOptions: {
									columns: ':visible'
								}
							}
							
						], 
						bAutoWidth: false,
						"aoColumns": [
						  null, null, null, null, null, null, null, null, null, null, null, null
						],
						"aaSorting": [],
						//"sScrollY": "500px",
						//"bPaginate": false,
						"sScrollX" : "true",
						//"sScrollX": "300px",
						//"sScrollXInner": "120%",
						"bScrollCollapse": true,
						
						select: {
							style: 'multi'
						}
				    } );

					//initiate dataTables plugin
					myTable3 = $('#dynamic-table3')
					//.wrap("<div class='dataTables_borderWrap' />")   //if you are applying horizontal scrolling (sScrollX)
					.DataTable( {
						dom:
"<'row'<'col-sm-2'l><'col-sm-7 text-center'B><'col-sm-3'f>>" +
"<'row'<''rt>>" +
"<'row  '<'col-sm-5'i><'col-sm-7'p>>",
						buttons: [
							{
								extend:'colvis',
								text : "<i class='fa fa-search bigger-110 blue'></i> <span class='hidden'>Show/hide columns</span>"
							},
							{
								extend: 'excelHtml5',
								 title: 'reportTitle',
								text : "<i class='fa fa-file-excel-o bigger-110 green'></i> <span class='hidden'>Export to Excel</span>",
								exportOptions: {
									columns: ':visible'
								}
							},
							{
								extend: 'pdfHtml5',
								text : "<i class='fa fa-file-pdf-o bigger-110 red'></i> <span class='hidden'>Export to PDF</span>",
								exportOptions: {
									columns: ':visible'
								}
							}
							
						], 
						bAutoWidth: false,
						"aoColumns": [
						  null, null, null, null, null, null
						],
						"aaSorting": [],
						//"sScrollY": "500px",
						//"bPaginate": false,
						"sScrollX" : "true",
						//"sScrollX": "300px",
						//"sScrollXInner": "120%",
						"bScrollCollapse": true,
						select: {
							style: 'multi'
						}
				    } );

					//initiate dataTables plugin
					myTable4 = $('#dynamic-table4')
					//.wrap("<div class='dataTables_borderWrap' />")   //if you are applying horizontal scrolling (sScrollX)
					.DataTable( {
						dom:
"<'row'<'col-sm-2'l><'col-sm-7 text-center'B><'col-sm-3'f>>" +
"<'row'<''rt>>" +
"<'row  '<'col-sm-5'i><'col-sm-7'p>>",
						buttons: [
							{
								extend:'colvis',
								text : "<i class='fa fa-search bigger-110 blue'></i> <span class='hidden'>Show/hide columns</span>"
							},
							{
								extend: 'excelHtml5',
								 title: 'reportTitle',
								text : "<i class='fa fa-file-excel-o bigger-110 green'></i> <span class='hidden'>Export to Excel</span>",
								exportOptions: {
									columns: ':visible'
								}
							},
							{
								extend: 'pdfHtml5',
								text : "<i class='fa fa-file-pdf-o bigger-110 red'></i> <span class='hidden'>Export to PDF</span>",
								exportOptions: {
									columns: ':visible'
								}
							}
							
						], 
						bAutoWidth: false,
						"aoColumns": [
						  null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null
						],
						"aaSorting": [],
						//"sScrollY": "500px",
						//"bPaginate": false,
						"sScrollX" : "true",
						//"sScrollX": "300px",
						//"sScrollXInner": "120%",
						"bScrollCollapse": true,
						select: {
							style: 'multi'	
						}
				    } );
					////

					//initiate dataTables plugin
					myTable5 = $('#dynamic-table5')
					//.wrap("<div class='dataTables_borderWrap' />")   //if you are applying horizontal scrolling (sScrollX)
					.DataTable( {
						dom:
"<'row'<'col-sm-2'l><'col-sm-7 text-center'B><'col-sm-3'f>>" +
"<'row'<''rt>>" +
"<'row  '<'col-sm-5'i><'col-sm-7'p>>",
						buttons: [
							{
								extend:'colvis',
								text : "<i class='fa fa-search bigger-110 blue'></i> <span class='hidden'>Show/hide columns</span>"
							},
							{
								extend: 'excelHtml5',
								 title: 'reportTitle',
								text : "<i class='fa fa-file-excel-o bigger-110 green'></i> <span class='hidden'>Export to Excel</span>",
								exportOptions: {
									columns: ':visible'
								}
							},
							{
								extend: 'pdfHtml5',
								text : "<i class='fa fa-file-pdf-o bigger-110 red'></i> <span class='hidden'>Export to PDF</span>",
								exportOptions: {
									columns: ':visible'
								}
							}
							
						], 
						bAutoWidth: false,
						"aoColumns": [
						  null, null, null, null, null, null, null, null, null, null, null, null
						],
						"aaSorting": [],
						//"sScrollY": "500px",
						//"bPaginate": false,
						"sScrollX" : "true",
						//"sScrollX": "300px",
						//"sScrollXInner": "120%",
						"bScrollCollapse": true,
						select: {
							style: 'multi'	
						}
				    } );
					////
					
				})
				
				$(document).ready(function() {
					setTimeout(function() {
						//$("#table1").hide();
						//$("#table2").hide();
						//$("#table3").hide();
						//$("#table4").hide();
					}, 2000);
			    });
			
		</script>
	@stop