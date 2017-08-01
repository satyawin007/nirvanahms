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
		
		<div id="modal-table" class="modal fade in" tabindex="-1" >
			<div class="modal-dialog" style="min-width: 99%;">
				<div class="modal-content">
					<div class="modal-header no-padding">
						<div class="table-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
								<span class="white">x</span>
							</button>
							Results for fuel station - <span id="headval"></span> <span style="float:right;font-weight: bold;">Total Litres : <span id="tltrs">120.00</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total Amount : <span id="tamt">120.00</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
						</div>
					</div>

					<div class="modal-body no-padding">
						<table  id="dynamic-table5" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
							<thead>
								<tr>
									<th>FUEL STATION</th>
									<th>VEHICLE</th>
									<th>DATE</th>
									<th>LTRS</th>
									<th>AMOUNT</th>
									<th>PAYMENT INFO</th>
									<th>REMARKS</th>
									<th>CREATED BY</th>
								</tr>
							</thead>
							<tbody id="tbodydata"></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div id="modal-table2" class="modal fade in" tabindex="-1" >
			<div class="modal-dialog" style="min-width: 99%;">
				<div class="modal-content">
					<div class="modal-header no-padding">
						<div class="table-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
								<span class="white">x</span>
							</button>
							Results for fuel station - <span id="headval"></span>
						</div>
					</div>

					<div class="modal-body no-padding">
						<table  id="dynamic-table6" class="table table-striped table-bordered table-hover no-margin-bottom no-border-top">
							<thead>
								<tr>
									<td>FUEL STATION</td>
									<td>FORMONTH</td>
									<td>TRANSACTION ID</td>
									<td>TRANSACTION</td>
									<td>BRANCH</td>
									<td>AMOUNT</td>
									<td>PAID Y/N</td>
									<td>PAID DATE</td>
									<td>PAYMENT TYPE</td>
									<td>COMMENTS</td>
									<td>CREATED BY</td>
								</tr>
							</thead>
							<tbody id="tbodydata1"></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row" >
			<div id="table1">
				<div class="row col-xs-12" style="padding-left:2%; padding-top: 1%">
					<?php if(!isset($values['entries'])) $values['entries']=10; if(!isset($values['branch'])) $values['branch']=0; if(!isset($values['page'])) $values['page']=1; ?>
					<div class="clearfix">
						<div id="tableTools-container1" class="pull-right tableTools-container"></div>
					</div>
					<div class="table-header" style="margin-top: 9px;">
						Results for <?php if(isset($values['type'])){ echo '"'.strtoupper($values['type'])." REPORT".'"';} ?>	
						<span style="float:right; font-size: 14px; font-weight: bold;">TOT-LTRS : <span id="totltrs">0</span>&nbsp;&nbsp;RANGE TOTAL : <span id="rangetotal">0</span>&nbsp;&nbsp;RANGE PAID : <span id="rangepaid">0</span>&nbsp;&nbsp;TILL TOTAL : <span id="tilltotamt">0</span>&nbsp;&nbsp;TILL PAID : <span id="tillpayamt">0</span> &nbsp;&nbsp;TILL BALANCE : <span id="tillbalance">0</span> </span>				 
					</div>
					<!-- div.table-responsive -->
					<!-- div.dataTables_borderWrap -->
					<div>
						<table id="dynamic-table1" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<td>FUEL STATION </td>
									<td>FROM DATE</td>
									<td>TO DATE</td>
									<td>TOT VEHS</td>
									<td>TOT LTRS </td>
									<td>TOT AMT</td>
									<td>PAID AMT</td>
									<td>PAID YES</td>
									<td>PAID NO</td>
									<td>TILL DT AMT</td>
									<td>TDT PAY AMT</td>
									<td>BALANCE</td>
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
						<span> Results for <?php if(isset($values['type'])){ echo '"'.strtoupper($values['type'])." REPORT".'"';} ?> </span>	
						<span style="float:right; font-size: 16px; font-weight: bold;">TOTAL AMOUNT : <span id="totalamount">0</span>&nbsp;</span>	
					</div>
					<!-- div.table-responsive -->
					<!-- div.dataTables_borderWrap -->
					<div>
						<table id="dynamic-table2" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<td>FUEL STATION</td>
									<td>PAID AMOUNT</td>
									<td>PAID DATE</td>
									<td>PAYMENT INFO</td>
									<td>COMMENTS</td>
									<td>CREATED BY</td>
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
						<span> Results for <?php if(isset($values['type'])){ echo '"'.strtoupper($values['type'])." REPORT".'"';} ?> </span>	
						<span style="float:right; font-size: 16px; font-weight: bold;">TOTAL AMOUNT : <span id="totamt">0</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  TOTAL FUEL : <span id="totfuel">0</span> &nbsp;&nbsp;&nbsp;</span>	
					</div>
					<!-- div.table-responsive -->
					<!-- div.dataTables_borderWrap -->
					<div>
						<table id="dynamic-table3" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<td>FUEL STATION</td>
									<td>VEHICLE</td>
									<td>FILLED DATE</td>
									<td>NO OF LTRS</td>
									<td>AMOUNT</td>
									<td>PAID BY BRANCH</td>
									<td>COMMENTS</td>
									<td>CREATED BY</td>
								</tr>
							</thead>
							<tbody id="tbody3">
							</tbody>
						</table>								
					</div>
				</div>					
			</div>
			<div id="table7">
				<div class="row col-xs-12" style="padding-left:2%; padding-top: 1%">
					<?php if(!isset($values['entries'])) $values['entries']=10; if(!isset($values['branch'])) $values['branch']=0; if(!isset($values['page'])) $values['page']=1; ?>
					<div class="clearfix">
						<div id="tableTools-container3" class="pull-right tableTools-container"></div>
					</div>
					<div class="table-header" style="margin-top: 10px;">
						<span> Results for <?php if(isset($values['type'])){ echo '"'.strtoupper($values['type'])." REPORT".'"';} ?> </span>	
						<span style="float:right; font-size: 16px; font-weight: bold;">TOTAL AMOUNT : <span id="totamtv">0</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  TOTAL FUEL : <span id="totfuelv">0</span> &nbsp;&nbsp;&nbsp;</span>	
					</div>
					<!-- div.table-responsive -->
					<!-- div.dataTables_borderWrap -->
					<div>
						<table id="dynamic-table7" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<td>FUEL STATION</td>
									<td>VEHICLE</td>
									<td>FILLED DATE</td>
									<td>NO OF LTRS</td>
									<td>AMOUNT</td>
									<td>START READING</td>
									<td>FULL TANK</td>
									<td>MILEAGE</td>
									<td>BILL NO</td>
									<td>PAID BY BRANCH</td>
									<td>COMMENTS</td>
									<td>CREATED BY</td>
								</tr>
							</thead>
							<tbody id="tbody7">
							</tbody>
						</table>								
					</div>
				</div>					
			</div>
			<div id="table4">
				<div class="row col-xs-12" style="padding-left:2%; padding-top: 1%">
					<?php if(!isset($values['entries'])) $values['entries']=10; if(!isset($values['branch'])) $values['branch']=0; if(!isset($values['page'])) $values['page']=1; ?>
					<div class="clearfix">
						<div id="tableTools-container3" class="pull-right tableTools-container"></div>
					</div>
					<div class="table-header" style="margin-top: 10px;">
						<span> Results for <?php if(isset($values['type'])){ echo '"'.strtoupper($values['type'])." REPORT".'"';} ?> </span>	
					</div>
					<!-- div.table-responsive -->
					<!-- div.dataTables_borderWrap -->
					<div>
						<table id="dynamic-table4" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<td>FUEL STATION</td>
									<td>FORMONTH</td>
									<td>TOTAL AMOUNT</td>
									<td>TOTAL PAID</td>
									<td>BALANCE</td>
								</tr>
							</thead>
							<tbody id="tbody4">
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
			$("#month").prop("disabled",true);
			
			reporttype = "";
			var refresh1 = 0;
			var refresh2 = 0;
			var refresh3 = 0;
			var refresh4 = 0;
			var refresh6 = 0;
			var refresh7 = 0;
			function generateReport(){
				reporttype = "ticket_corgos_summery";
				paginate(1);
			}

			function showSelectionType(val){
				if(val=="balanceSheetNoDt" || val=="payment" || val=="balanceSheet" || val=="tracking"|| val=="advances" || val=="formonthlypayments"){
					$("#fuelstationid").show();
					$("#vehicleid").hide();
					$("#driverid").hide();
					if(val=="balanceSheetNoDt"){
						$("#fromdate").prop("disabled",false);
						$("#todate").prop("disabled",false);
					}
					else if(val=="formonthlypayments"){
						$("#fuelstationid").show();
						$("#fromdate").prop("disabled",true);
						$("#todate").prop("disabled",true);
						$("#vehicleid").hide();
						$("#driverid").hide();
						$("#month").prop("disabled",false);
					}
					else{
						$("#fromdate").prop("disabled",false);
						$("#todate").prop("disabled",false);
					}
				}
				else if(val=="vehicleReport"){
					$("#fuelstationid").hide();
					$("#vehicleid").show();
					$("#driverid").hide();
				}
				else if(val=="employeeReport"){
					$("#fuelstationid").hide();
					$("#vehicleid").hide();
					$("#driverid").show();
				}
			}

			function getData(id, name, fromdate, todate){
				$("#headval").html(name);
				//alert(id+", "+name+", "+fromdate+", "+todate);

				var myin = document.createElement("input"); 
				myin.type='hidden'; 
				myin.name='reporttype1'; 
				myin.value="fuelstation_detailed"; 
				document.getElementById('getreport').appendChild(myin);

				var myin = document.createElement("input"); 
				myin.type='hidden'; 
				myin.name='fuelstationname'; 
				myin.value=name; 
				document.getElementById('getreport').appendChild(myin);
				
				$("#processing").show();
				var form=$("#getreport");

				$.ajax({
			        type:"POST",
			        url:form.attr("action"),
			        data:form.serialize(),
			        success: function(response){
			           //alert(response);  
			           var json = JSON.parse(response);
			           var data  = json['data'];
			           $("#tltrs").html(json['total_ltrs']);
			           $("#tamt").html(json['total_amt']);
			           var tbody = "";
			           
			           var arr = [];
			           for(var i = 0; i < data.length; i++) {
			        	    var parsed = data[i];
			        	    var row = [];
			        	    for(var x in parsed){
			        	    	row.push(parsed[x]);
				            }
				            arr.push(row);
			        	}
			           	model1.clear().draw();
			           	model1.rows.add(arr); // Add new data
			           	model1.columns.adjust().draw(); // Redraw theDataTable
			        	
			           //$("#tbodydata").html(tbody);
			           $("#processing").hide();
			        }
				});	
					
				/*$.ajax({
			        type:"POST",
			        url:form.attr("action"),
			        data:form.serialize(),
			        success: function(response){
			           //alert(response);  
			           var json = JSON.parse(response);
			           var data  = json['data'];
			           $("#tltrs").html(json['total_ltrs']);
			           $("#tamt").html(json['total_amt']);
			           var tbody = "";
			           for(var i = 0; i < data.length; i++) {
			        	    tbody = tbody+"<tr>";
			        	    var parsed = data[i];
			        	    for(var x in parsed){
			        	    	tbody = tbody+"<td>"+parsed[x]+"</td>";
				            }
			        	    tbody = tbody+"<tr>";
			        	}
			           $("#tbodydata").html(tbody);
			           $("#processing").hide();
			        }
				});*/				
			}
			function getDetails(id, name, formonth){
				$("#headval").html(name);
				//alert(id+", "+name+", "+formonth);

				var myin = document.createElement("input"); 
				myin.type='hidden'; 
				myin.name='reporttype2'; 
				myin.value="fuelstation_transactions"; 
				document.getElementById('getreport').appendChild(myin);

				var myin = document.createElement("input"); 
				myin.type='hidden'; 
				myin.name='fuelstationname'; 
				myin.value=name; 
				document.getElementById('getreport').appendChild(myin);
				
				$("#processing").show();
				var form=$("#getreport");

				$.ajax({
			        type:"POST",
			        url:form.attr("action"),
			        data:form.serialize(),
			        success: function(response){
			           //alert(response);  
			           var json = JSON.parse(response);
			           var data  = json['data'];
			           var tbody = "";
			           var arr = [];
			           for(var i = 0; i < data.length; i++) {
			        	    var parsed = data[i];
			        	    var row = [];
			        	    for(var x in parsed){
			        	    	row.push(parsed[x]);
				            }
				            arr.push(row);
			        	}
			           	model2.clear().draw();
			           	model2.rows.add(arr); // Add new data
			           	model2.columns.adjust().draw(); 
			           	//$("#tbodydata").html(tbody);
			           $("#processing").hide();
			        }
				});	
					
				/*$.ajax({
			        type:"POST",
			        url:form.attr("action"),
			        data:form.serialize(),
			        success: function(response){
			           //alert(response);  
			           var json = JSON.parse(response);
			           var data  = json['data'];
			           $("#tltrs").html(json['total_ltrs']);
			           $("#tamt").html(json['total_amt']);
			           var tbody = "";
			           for(var i = 0; i < data.length; i++) {
			        	    tbody = tbody+"<tr>";
			        	    var parsed = data[i];
			        	    for(var x in parsed){
			        	    	tbody = tbody+"<td>"+parsed[x]+"</td>";
				            }
			        	    tbody = tbody+"<tr>";
			        	}
			           $("#tbodydata").html(tbody);
			           $("#processing").hide();
			        }
				});*/				
			}

			function paginate(page){
				reporttype = $("#fuelreporttype").val();
				if(reporttype == ""){
					alert("select report type");
					return;
				}
				fdt="";
				tdt="";
				if(reporttype != "balanceSheetNoDt" && reporttype != "formonthlypayments"){
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
				}
				else{
					fdt = "01-01-2010";
					tdt = <?php echo "'".date("d-m-Y")."';"?>;
				}
				dt = fdt+" - "+tdt;	
				var form=$("#getreport");	

				var reporttype1 = $("#reporttype1").val();
				if(reporttype1 != undefined){
					document.getElementById("reporttype1").remove();
				}

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
			        	    var row = [];
			        	    for(var x in parsed){
			        	    	row.push(parsed[x]);
				            }
				            arr.push(row);
			        	}
			        	if(reporttype == "balanceSheetNoDt" || reporttype == "balanceSheet"){
			        		totltrs = json["totltrs"];
			        		rangetotal = json["rangetotal"];
			        		rangepaid = json["rangepaid"];
			        		tilltotamt = json["tilltotamt"];
			        		tillpayamt = json["tillpayamt"];
			        		tillbalance = json["tillbalance"];
			        		$("#totltrs").html(totltrs);
			        		$("#rangetotal").html(rangetotal);
			        		$("#rangepaid").html(rangepaid);
			        		$("#tilltotamt").html(tilltotamt);
			        		$("#tillpayamt").html(tillpayamt);
			        		$("#tillbalance").html(tillbalance);
							myTable1.clear().draw();
							myTable1.rows.add(arr); // Add new data
							myTable1.columns.adjust().draw(); // Redraw 
							$("#table1").show();
							$("#table2").hide();
							$("#table3").hide();
							$("#table7").hide();
							if(refresh1 == 0){
								refresh1 = 1;
								myTable1.draw();
							}		
			        	}
			        	else if(reporttype == "payment" || reporttype == "advances" ){
			        		totalamount = json["total_amt"];
			        		$("#totalamount").html(totalamount);
							myTable2.clear().draw();
							myTable2.rows.add(arr); // Add new data
							myTable2.columns.adjust().draw(); // Redraw theDataTable
							$("#table1").hide();
							$("#table2").show();	
							$("#table3").hide();
							$("#table4").hide();
							$("#table7").hide();
							if(refresh2 == 0){
								refresh2 = 1;
								myTable2.draw();
							}	
			        	}
			        	else if(reporttype == "tracking"){
				        	totamt = json["total_amt"];
				        	totfuel = json["total_ltrs"];
			        		$("#totamt").html(totamt);
				        	$("#totfuel").html(totfuel);
							myTable3.clear().draw();
							myTable3.rows.add(arr); // Add new data
							myTable3.columns.adjust().draw(); // Redraw theDataTable
							$("#table1").hide();
							$("#table2").hide();
							$("#table3").show();
							$("#table4").hide();
							$("#table7").hide();
							if(refresh3 == 0){
								refresh3 = 1;
								myTable3.draw();
							}		
			        	}
			        	else if(reporttype == "vehicleReport"){
				        	totamt = json["total_amt"];
				        	totfuel = json["total_ltrs"];
			        		$("#totamtv").html(totamt);
				        	$("#totfuelv").html(totfuel);
							myTable7.clear().draw();
							myTable7.rows.add(arr); // Add new data
							myTable7.columns.adjust().draw(); // Redraw theDataTable
							$("#table1").hide();
							$("#table2").hide();
							$("#table3").hide();
							$("#table4").hide();
							$("#table7").show();
							if(refresh7 == 0){
								refresh7 = 1;
								myTable7.draw();
							}		
			        	}
			        	else if(reporttype == "formonthlypayments"){
				        	//totamt = json["total_amt"];
				        	//paidamt = json["total_paid"];
				        	//balanceamt = json["tot_balance"];
			        		//$("#monthlyamount").html(totamt);
			        		//$("#monthlypaidamount").html(paidamt);
			        		//$("#monthlybalanceamount").html(balanceamt);
							myTable4.clear().draw();
							myTable4.rows.add(arr); // Add new data
							myTable4.columns.adjust().draw(); // Redraw theDataTable
							$("#table1").hide();
							$("#table2").hide();
							$("#table3").hide();
							$("#table4").show();
							$("#table7").hide();
							if(refresh4 == 0){
								refresh4 = 1;
								myTable4.draw();
							}		
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
			var myTable7 = null;
			var model1 = null;
			var model2 = null;
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
						  null, null, null,  null, null, null, null,  null, null, null, null,  null
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
						  null, null, null, null, null, null, null, null
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
						  null, null, null, null,null
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
					myTable7 = $('#dynamic-table7')
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
						  null, null, null,  null, null, null, null,  null, null, null, null, null
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
					setTimeout(function() {
						$("#table1").hide();
						$("#table2").hide();
						$("#table3").hide();
						$("#table4").hide();
					}, 500);
				})
				model1 = $('#dynamic-table5')
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
							{ "bSortable": false },	{ "bSortable": false },
							{ "bSortable": false }, { "bSortable": false }, 
							{ "bSortable": false }, { "bSortable": false }, 
							{ "bSortable": false }, { "bSortable": false } 
						
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
			model2 = $('#dynamic-table6')
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
					{ "bSortable": false }, { "bSortable": false },
					{ "bSortable": false }, { "bSortable": false }, 
					{ "bSortable": false }, { "bSortable": false }, 
					{ "bSortable": false }, { "bSortable": false },
					{ "bSortable": false }, { "bSortable": false },
					{ "bSortable": false }
				
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
			
		</script>
	@stop