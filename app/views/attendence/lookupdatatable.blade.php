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
			th,td{
				text-align: center;
			}
			.chosen-container{
			  width: 100% !important;
			}
		</style>
	@stop
	
	@section('page_css')
		<link rel="stylesheet" href="../assets/css/bootstrap-datepicker3.css"/>
		<link rel="stylesheet" href="../assets/css/chosen.css" />
	@stop
	
	@section('bredcum')	
		<small>
			HOME
			<i class="ace-icon fa fa-angle-double-right"></i>
			ATTENDENCE
		</small>
	@stop

	@section('page_content')
		<div class="row " style="max-width: 96%;margin-left: 2%;">
		<div class="row ">
			<div class="col-xs-offset-0 col-xs-12">
				<?php $form_info = $values["form_info"]; ?>
				<?php $jobs = Session::get("jobs");?>
				<?php if(($form_info['action']=="addattendence" && in_array(336, $jobs)) or 
						($form_info['action']=="addclient" && in_array(403, $jobs))
					  ){ ?>
					@include("attendence.addlookupform",$form_info)
				<?php } ?>
			</div>
		</div>
		</div>
				
		<div class="row " style="max-width: 98%;margin-left: 1%;">
		<div class="col-xs-offset-0 col-xs-12">
			<?php if(!isset($values['entries'])) $values['entries']=10; if(!isset($values['branch'])) $values['branch']=0; if(!isset($values['page'])) $values['page']=1; ?>
			<div class="clearfix">
				<div class="pull-left">
				</div>
				<div class="pull-right tableTools-container"></div>
			</div>
			<form action="test" method="post" name="workflowform" id="workflowform" onsubmit="return false;">
			<h3 class="header smaller lighter blue" style="font-size: 15px; font-weight: bold;margin-bottom: 5px;">CHANGE STATUS OF {{$values["bredcum"]}}</h3>
			<div class="table-header" style="margin-top: 10px;">
				Results for "{{$values['bredcum']}}"				 
			</div>
			<!-- div.table-responsive -->
			<!-- div.dataTables_borderWrap -->
			<div>
				<table id="dynamic-table" class="table table-striped table-bordered table-hover">
					<thead>
						<tr>
							<?php
								if(isset($values["startdate"]) && $values["startdate"] != ""){
									echo "<th>EMPLOYEE (EMPCODE)</th>";
									$date = date_create(date('d-m-Y',strtotime($values["startdate"])));
									$month = date("m",strtotime($values["startdate"]));
									if($month === date("m")){
										$today = date_create(date("d-m-Y"));
									}
									else{
										$today = date_create(date("d-m-Y", strtotime($values["date"])));
									}
									$diff=date_diff($date,$today);
									$diff =  $diff->format("%a");
									for($i=0; $i<=$diff; $i++){
										if(date_format($date, 'D') == "Sun"){
											echo "<th style='color: red; background-color: #D0D0D6;'>".strtoupper(date_format($date, 'd D'))."</th>";
										}
										else{
											echo "<th>".strtoupper(date_format($date, 'd D'))."</th>";
										}
										$date = date_add($date, date_interval_create_from_date_string('1 days'));
									}
								}
								else{
									echo "<th>EMPLOYEE (EMPCODE)</th>";
									$date = date_create(date('d-m-Y',strtotime("first day of this month")));
									$today = date_create(date("d-m-Y"));
									$diff=date_diff($date,$today);
									$diff =  $diff->format("%a");
									for($i=0; $i<=$diff; $i++){
										if(date_format($date, 'D') == "Sun"){
											echo "<th style='color: red; background-color: #D0D0D6;'>".strtoupper(date_format($date, 'd D'))."</th>";
										}
										else{
											echo "<th>".strtoupper(date_format($date, 'd D'))."</th>";
										}
										$date = date_add($date, date_interval_create_from_date_string('1 days'));
									}
								}
							?>
						</tr>
					</thead>
				</table>								
			</div>
			</form>
		</div>
		
		<div id="edit" class="modal" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="blue bigger">Please fill the following form fields</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div name="edit" id="editform" class="form-horizontal" action="edit"  method="post">	
									<div class="form-group">
										<label class="col-xs-3 control-label no-padding-right" for="form-field-1"> SUBSTITUTE </label>
										<div class="col-xs-7">
											<select class="form-control chosen-select" name="substitute"  id="substitute">
												<option value="">-- substitute --</option>
												<?php 
													$employees = Employee::where("status","=","ACTIVE")
																	->whereIn("roleId",array(19,20))->get();
													foreach($employees as $employee){
														echo '<option value="'.$employee->id.'">'.$employee->fullName.'('.$employee->empCode.')</option>';
													}
												?>
											</select>
										</div>			
									</div>				
									<div class="form-group">
										<label class="col-xs-3 control-label no-padding-right" for="form-field-1"> COMMENTS </label>
										<div class="col-xs-7">
											<textarea  id="comments"  name=""comments"" class="form-control" ></textarea>
										</div>			
									</div>
									<input type="hidden" name="empid" id="empid" value="" />
												
									<div class="modal-footer">
										<button class="btn btn-sm" data-dismiss="modal">
											<i class="ace-icon fa fa-times"></i>
											Cancel
										</button>
						
										<button class="btn btn-sm btn-primary" data-dismiss="modal" onclick="formValues()">
											<i class="ace-icon fa fa-check"></i>
											Save
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<a class="" href="#edit" id="modaledit" data-toggle="modal"></a>
		
		<div id="update1" class="modal" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="blue bigger">Please fill the following form fields</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div name="update" id="update" class="form-horizontal" action="update"  method="post">	
									<div class="form-group">
										<label class="col-xs-3 control-label no-padding-right" for="form-field-1"> SUBSTITUTE </label>
										<div class="col-xs-7">
											<select class="form-control chosen-select" name="substitute1"  id="substitute1">
												<option value="">-- substitute --</option>
												<?php 
													$employees = Employee::where("status","=","ACTIVE")
																	->whereIn("roleId",array(19,20))->get();
													foreach($employees as $employee){
														echo '<option value="'.$employee->id.'">'.$employee->fullName.'('.$employee->empCode.')</option>';
													}
												?>
											</select>
										</div>			
									</div>				
									<div class="form-group">
										<label class="col-xs-3 control-label no-padding-right" for="form-field-1"> ATTENDENCE STATUS </label>
										<div class="col-xs-7">
											<select class="form-control chosen-select" name="attendence_status"  id="attendence_status">
												<option value="">-- attendence status --</option>
												<option value="A">A</option>
												<option value="PL">PL</option>
												<option value="P">P</option>
											</select>
										</div>			
									</div>
									<div class="form-group">
										<label class="col-xs-3 control-label no-padding-right" for="form-field-1"> COMMENTS </label>
										<div class="col-xs-7">
											<textarea  id="comments1"  name="comments1" class="form-control" ></textarea>
										</div>			
									</div>
									<div class="form-group">
										<label class="col-xs-3 control-label no-padding-right" for="form-field-1"> STATUS CHANGE COMMENTS </label>
										<div class="col-xs-7">
											<textarea  id="statuschangecomments1"  name="statuschangecomments1" class="form-control" ></textarea>
										</div>			
									</div>
									<input type="hidden" name="recid" id="recid" value="" />
									<input type="hidden" name="empid" id="empid" value="" />
									<input type="hidden" name="cellid" id="cellid" value="" />
												
									<div class="modal-footer">
										<button class="btn btn-sm" data-dismiss="modal">
											<i class="ace-icon fa fa-times"></i>
											Cancel
										</button>
						
										<button class="btn btn-sm btn-primary" data-dismiss="modal" onclick="formValuesUpdate()">
											<i class="ace-icon fa fa-check"></i>
											Save
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<a class="" href="#update1" id="modalupdate" data-toggle="modal"></a>
		</div>
	@stop
	
	@section('page_js')
		<!-- page specific plugin scripts -->
		<script src="../assets/js/dataTables/jquery.dataTables.js"></script>
		<script src="../assets/js/dataTables/jquery.dataTables.bootstrap.js"></script>
		<script src="../assets/js/dataTables/extensions/buttons/dataTables.buttons.js"></script>
		<script src="../assets/js/dataTables/extensions/buttons/buttons.flash.js"></script>
		<script src="../assets/js/dataTables/extensions/buttons/buttons.html5.js"></script>
		<script src="../assets/js/dataTables/extensions/buttons/buttons.print.js"></script>
		<script src="../assets/js/dataTables/extensions/buttons/buttons.colVis.js"></script>
		<script src="../assets/js/dataTables/extensions/select/dataTables.select.js"></script>
		<script src="../assets/js/date-time/bootstrap-datepicker.js"></script>
		<script src="../assets/js/date-time/moment.js"></script>
		<script src="../assets/js/bootbox.js"></script>
		<script src="../assets/js/chosen.jquery.js"></script>
		<script src="../assets/js/jquery.maskedinput.js"></script>
	@stop
	
	@section('inline_js')
		<!-- inline scripts related to this page -->
		<script type="text/javascript">
			var attendence_data = [];
			attendence_data_update = [];
			data = [];
			$("#entries").on("change",function(){paginate(1);});

			$("#clientname").attr("disabled",true);
			$("#depot").attr("disabled",true);
			$("#officebranch").attr("disabled",true);

			<?php
				if(isset($values["officebranch"]) && $values["officebranch"]!=0 ){
					echo '$("#officebranch").attr("disabled",false); ';
				}
				if(isset($values["client"]) && $values["client"]!=0 ){
					echo '$("#clientname").attr("disabled",false); ';
				}
				if(isset($values["depot"])){
					echo '$("#depot").attr("disabled",false); ';
				}
			?>
		
			$("#getbtn").on("click",function(){
				logstatus = $("#logstatus").val();
				url = "";

				inchargereporttype = $("#inchargereporttype").val();
				if(inchargereporttype != undefined && inchargereporttype !=""){
					url = url+"&inchargereporttype="+inchargereporttype;
				}
				incharge = $("#incharge").val();
				if(incharge != undefined && incharge !=""){
					url = url+"&incharge="+incharge;
				}
				myTable.ajax.url(url).load();
			})

			function postData(){
				$.ajax({
                    url: "getattendencelog",
                    type: "post",
                    data: $("#attendence").serialize(),
                    success: function(response) {
                    	response = jQuery.parseJSON(response);	
                        if(response.status=="success" && response.rec_count==0){
	                    	$.ajax({
	                            url: "addattendencelog",
	                            type: "post",
	                            data: $("#attendence").serialize(),
	                            success: function(response) {
	                            	response = jQuery.parseJSON(response);	
	                                if(response.status=="success"){
	                                	bootbox.alert(response.message);
	                                	window.setTimeout(function(){location.reload();}, 2000 ); // 5 seconds
	                                }
	                                if(response.status=="fail"){
	                                	bootbox.alert(response.message);
	                                }
	                            }
	                        });
	                    }
                        else if(response.status=="fail"){
                        	bootbox.alert(response.message);
                        }
                        else{
                        	if(attendence_data.length == 0){
                        		bootbox.alert("attendence is already given for the session");
                        	}
                        }
                
                    }
                });
				if(attendence_data.length == 0){
					return;
				}

				$.ajax({
                    url: "addattendencelog",
                    type: "post",
                    data: $("#attendence").serialize(),
                    success: function(response) {}
                });
                
				$('#jsondata').val(JSON.stringify(attendence_data));
				$.ajax({
                    url: "addattendence",
                    type: "post",
                    data: $("#attendence").serialize(),
                    success: function(response) {
                    	response = jQuery.parseJSON(response);	
                        if(response.status=="success"){
                        	bootbox.alert(response.message);
                        	window.setTimeout(function(){location.reload();}, 2000 ); // 5 seconds
                        }
                        if(response.status=="fail"){
                        	bootbox.alert(response.message);
                        }
                    }
                });
			}

			function postDataUpdate(){
				$.ajax({
                    url: "addattendencelog?action=update",
                    type: "post",
                    data: $("#attendence").serialize(),
                    success: function(response) {}
                });
                
				$('#jsondata').val(JSON.stringify(attendence_data_update));
				$.ajax({
                    url: "updateattendence",
                    type: "post",
                    data: $("#attendence").serialize(),
                    success: function(response) {
                    	response = jQuery.parseJSON(response);	
                        if(response.status=="success"){
                        	bootbox.alert(response.message);
                        	window.setTimeout(function(){location.reload();}, 2000 ); // 5 seconds
                        }
                        if(response.status=="fail"){
                        	bootbox.alert(response.message);
                        }
                    }
                });
			}

			function formValues(){
				data = {};
				data["empid"] = $("#empid").val();
				data["Substitute"] = $("#substitute").val();
				data["comments"] = $("#comments").val();
				attendence_data.push(data);
				$("#comments").val("");
				$("#substitute").find('option:selected').removeAttr("selected");
				$('.chosen-select').trigger('chosen:updated');
			}

			function formValuesUpdate(){
				data = {};
				data["recid"] = $("#recid").val();
				data["empid"] = $("#empid").val();
				data["Substitute"] = $("#substitute1").val();
				data["comments"] = $("#comments1").val();
				data["statuschangecomments"] = $("#statuschangecomments1").val();
				data["attendence_status"] = $("#attendence_status").val();

				var index = -1;		
				for( var i = 0; i<attendence_data_update.length; i++ ) {
					if( attendence_data_update[i]["empid"] == $("#empid").val() ) {
						index = i;
						break;
					}
				}
				if( index != -1 ) {
					attendence_data_update.splice(index, 1);
				}
				attendence_data_update.push(data);
				console.log(attendence_data_update);
				
				
				$("#comments1").val("");
				$("#substitute1").find('option:selected').removeAttr("selected");
				$('.chosen-select').trigger('chosen:updated');

				id = $("#cellid").val();
				if($("#attendence_status").val() == "PL"){
					$("#"+id).html("PL");
					$("#"+id).css("color","orange");
				}
				if($("#attendence_status").val() == "P"){
					$("#"+id).html("P");
					$("#"+id).css("color","green");
				}
				if($("#attendence_status").val() == "A"){
					$("#"+id).html("A");
					$("#"+id).css("color","red");
				}
			}

			function printtable(){
				for(i=0; i<attendence_data.length; i++){
					alert(attendence_data[i][0]+" "+attendence_data[i][1]+" "+attendence_data[i][2]);
				}
			}

			function showData(substitute, comments){
				bootbox.alert("Substitute : "+substitute+"<br/>"+"Comments : "+comments);
			}

			function changeValue(id, empid, type){
				//alert(id+" "+empid+" "+type);
				$("#empid").val(empid);
				$text = $("#"+id).html();
				day = $('input[name=day]:radio:checked').val();
				if(day == "HOLIDAY"){
					$("#"+id).html("H");
					$("#"+id).css("color","red");
				}
				else{
					$("#"+id).html("P");
					$("#"+id).css("color","green");
				}
				if($text == "P" || $text == "H"){
					if(type == "driver"|| type == "helper"){
						$("#modaledit").click();
					}
					else{
						data = {};
						data["empid"] = empid;
						data["Substitute"] = 0;
						data["comments"] = "";
						attendence_data.push(data);
					}
					$("#"+id).html("A");
					$("#"+id).css("color","red");
				}
				if($text == "A"){
					var index = -1;		
					for( var i = 0; i<attendence_data.length; i++ ) {
						if( attendence_data[i]["empid"] == empid ) {
							index = i;
							break;
						}
					}
					if( index === -1 ) {
						return;
					}
					attendence_data.splice(index, 1);	
				}
			}

			function updateAttendenceValues(id, empid, type, substitute, status, comments, comments1, recid){
				text = $("#"+id).html();
				$("#cellid").val(id);
				$("#recid").val(recid);
				if(type == "driver"|| type == "helper"){
					$("#substitute1").attr("disabled",false);
					$("#comments1").attr("disabled",false);
					$("#substitute1 option").each(function() { this.selected = (this.text == substitute); });
					$("#attendence_status option").each(function() { this.selected = (this.text == status); });
					$("#comments1").val(comments);
					$("#statuschangecomments1").val(comments1);
					$("#recid").val(recid);
					$("#empid").val(empid);
					$('.chosen-select').trigger("chosen:updated");
					$("#modalupdate").click();
				}
				else {
					$("#substitute1").attr("disabled",true);
					$("#attendence_status option").each(function() { this.selected = (this.text == status); });
					$("#comments1").attr("disabled",true);
					$("#statuschangecomments1").val(comments1);
					$("#recid").val(recid);
					$("#empid").val(empid);
					$('.chosen-select').trigger("chosen:updated");
					$("#modalupdate").click();
				}
			}

			function getEmployees(){
				url = "getattendencedatatabledata?name=getattendence";
				
				employeetype = $('#employeetype').val();
				if(employeetype == ""){
					alert("please select employee type");
					return;
				}
				url = url+"&employeetype="+employeetype;
				
				officebranch = $('#officebranch').val();
				if(employeetype == "OFFICE" && officebranch==""){
					alert("please select office branch");
					return;
				}
				url = url+"&officebranch="+officebranch;

				showemployees = $('#show_employees').val();
				//alert(showemployees);
				if(showemployees == "" ){
					alert("please select employees");
					return;
				}
				url = url+"&show_employees="+showemployees;

				clientname = $('#clientname').val();
				clientbranch = $('#depot').val();
				if(employeetype == "CLIENT BRANCH" && (clientname=="" || clientbranch=="")){
					alert("please select client name and  branch");
					return;
				}
				url = url+"&client="+clientname+"&depot="+clientbranch;
				
				date = $('#date').val();
				if(date == ""){
					alert("please select date");
					return;
				}
				url = url+"&date="+date;
				
				session = $('input[name=session]:radio:checked').val();
				if(session == undefined){
					alert("please select session");
					return;
				}
				url = url+"&session="+session;
				
				day = $('input[name=day]:radio:checked').val();
				if(day == undefined){
					alert("please select day");
					return;
				}
				url = url+"&day="+day;

				holidayreason = $("#holidayreason").val();
				if(!$("#holidayreason").attr("readonly") && holidayreason==""){
					alert("please enter holiday reason");
					return;
				}
				url = url+"&holidayreason="+holidayreason;

				dt1 = new Date();
				dt1 = dt1.getMonth()+1;
				dt2 = date.split("-");
				dt2 = parseInt(dt2[1]);

				yr1 = new Date();
				yr1 = yr1.getFullYear();
				yr2 = date.split("-");
				yr2 = parseInt(yr2[2]);
				if(dt2<dt1 || yr1>yr2){
					url = url.replace('getattendencedatatabledata?name=getattendence','attendence?');
					window.location.href = url;
					return;
				}
				$("#get").show();
				$("#modify").show();
				$("#add").show();
				myTable.ajax.url(url).load(function ( json ) {
				    //alert(json.total_abs);
				    $("#noofpresents").val(json.total_att);
                	$("#noofabsents").val(json.total_abs);
				});
			}
			<?php 
				if(isset($values["name"]) && $values["name"]=="getattendencetoupdate"){ echo "getEmployeesToUpdateInit();";}
				else{ echo "getEmployeesInit();";}
			?>
				
			function getEmployeesInit(){
				url = "getattendencedatatabledata?name=getattendence";
				employeetype = $('#employeetype').val();
				if(employeetype == ""){
					return;
				}
				url = url+"&employeetype="+employeetype;
				
				officebranch = $('#officebranch').val();
				if(employeetype == "OFFICE" && officebranch==""){
					alert("please select office branch");
					return;
				}
				url = url+"&officebranch="+officebranch;

				showemployees = $('#show_employees').val();
				if(showemployees == "" ){
					alert("please select employees");
					return;
				}
				url = url+"&show_employees="+showemployees;
				

				clientname = $('#clientname').val();
				clientbranch = $('#depot').val();
				if(employeetype == "CLIENT BRANCH" && (clientname=="" || clientbranch=="")){
					alert("please select client name and  branch");
					return;
				}
				url = url+"&client="+clientname+"&depot="+clientbranch;
				
				date = $('#date').val();
				if(date == ""){
					alert("please select date");
					return;
				}
				url = url+"&date="+date;
				
				session = $('input[name=session]:radio:checked').val();
				if(session == undefined){
					alert("please select session");
					return;
				}
				url = url+"&session="+session;
				
				day = $('input[name=day]:radio:checked').val();
				if(day == undefined){
					alert("please select day");
					return;
				}
				url = url+"&day="+day;

				holidayreason = $("#holidayreason").val();
				if(!$("#holidayreason").attr("readonly") && holidayreason==""){
					alert("please enter holiday reason");
					return;
				}
				url = url+"&holidayreason="+holidayreason;
				setTimeout(function() {
					myTable.ajax.url(url).load(function ( json ) {
					    $("#noofpresents").val(json.total_att);
	                	$("#noofabsents").val(json.total_abs);
					});
				}, 500);
			}
			
			
			function getdaytotatt(){ 
				$.ajax({
                    url: "getdaytotalattendence",
                    data: $("#attendence").serialize(),
                    type: "post",
                    success: function(response) {
                    	response = jQuery.parseJSON(response);	
                    	$("#noofpresents").val(response.noofpresents);
                    	$("#noofabsents").val(response.noofabsents);
                    }
                });
			};

			function changeDepot(val){
				$.ajax({
			      url: "getdepotsbyclientId?id="+val,
			      success: function(data) {
			    	  data = "<option value='0'>ALL</option>"+data;
			    	  $("#depot").html(data);
			    	  $('.chosen-select').trigger("chosen:updated");
			      },
			      type: 'GET'
			    });

				clientId =  $("#clientname").val();
				depotId = $("#depot").val();
			}

			function enableClientDepot(val){
				if(val == "OFFICE"){
					$("#clientname").attr("disabled",true);
					$("#depot").attr("disabled",true);
					$("#officebranch").attr("disabled",false);
					$('.chosen-select').trigger("chosen:updated");
				}
				else if(val == "CLIENT BRANCH"){
					$("#clientname").attr("disabled",false);
					$("#depot").attr("disabled",false);
					$("#officebranch").attr("disabled",true);
					$('.chosen-select').trigger("chosen:updated");
				}
			}

			function getEmployeesToUpdate(){
				url = "getattendencedatatabledata?name=getattendencetoupdate";
				
				employeetype = $('#employeetype').val();
				if(employeetype == ""){
					alert("please select employee type");
					return;
				}
				url = url+"&employeetype="+employeetype;
				
				officebranch = $('#officebranch').val();
				if(employeetype == "OFFICE" && officebranch==""){
					alert("please select office branch");
					return;
				}
				url = url+"&officebranch="+officebranch;

				showemployees = $('#show_employees').val();
				if(showemployees == "" ){
					alert("please select employees");
					return;
				}
				url = url+"&show_employees="+showemployees;
				

				clientname = $('#clientname').val();
				clientbranch = $('#depot').val();
				if(employeetype == "CLIENT BRANCH" && (clientname=="" || clientbranch=="")){
					alert("please select client name and  branch");
					return;
				}
				url = url+"&client="+clientname+"&depot="+clientbranch;
				
				date = $('#date').val();
				if(date == ""){
					alert("please select date");
					return;
				}
				url = url+"&date="+date;
				
				session = $('input[name=session]:radio:checked').val();
				if(session == undefined){
					alert("please select session");
					return;
				}
				url = url+"&session="+session;
				
				day = $('input[name=day]:radio:checked').val();
				if(day == undefined){
					alert("please select day");
					return;
				}
				url = url+"&day="+day;
				dt1 = new Date();
				dt1 = dt1.getMonth()+1;
				dt2 = date.split("-");
				dt2 = parseInt(dt2[1]);
				
				yr1 = new Date();
				yr1 = yr1.getFullYear();
				yr2 = date.split("-");
				yr2 = parseInt(yr2[2]);

				if(dt2<dt1 || yr1>yr2){
					url = url.replace('getattendencedatatabledata?name=getattendencetoupdate','attendence?name=getattendencetoupdate');
					window.location.href = url;
					return;
				}

				$("#get").show();
				$("#modify").show();
				$("#add").show();
				myTable.ajax.url(url).load(function ( json ) {
				    //alert(json.total_abs);
				    $("#noofpresents").val(json.total_att);
                	$("#noofabsents").val(json.total_abs);
				});
			}
			
			function getEmployeesToUpdateInit(){
				url = "getattendencedatatabledata?name=getattendencetoupdate";
				
				employeetype = $('#employeetype').val();
				if(employeetype == ""){
					//alert("please select employee type");
					return;
				}
				url = url+"&employeetype="+employeetype;
				
				officebranch = $('#officebranch').val();
				if(employeetype == "OFFICE" && officebranch==""){
					alert("please select office branch");
					return;
				}
				url = url+"&officebranch="+officebranch;

				showemployees = $('#show_employees').val();
				if(showemployees == "" ){
					alert("please select employees");
					return;
				}
				url = url+"&show_employees="+showemployees;
				

				clientname = $('#clientname').val();
				clientbranch = $('#depot').val();
				if(employeetype == "CLIENT BRANCH" && (clientname=="" || clientbranch=="")){
					alert("please select client name and  branch");
					return;
				}
				url = url+"&client="+clientname+"&depot="+clientbranch;
				
				date = $('#date').val();
				if(date == ""){
					alert("please select date");
					return;
				}
				url = url+"&date="+date;
				
				session = $('input[name=session]:radio:checked').val();
				if(session == undefined){
					alert("please select session");
					return;
				}
				url = url+"&session="+session;
				
				day = $('input[name=day]:radio:checked').val();
				if(day == undefined){
					alert("please select day");
					return;
				}
				url = url+"&day="+day;
				$("#get").show();
				$("#modify").show();
				$("#add").show();
				setTimeout(function() {
					myTable.ajax.url(url).load(function ( json ) {
					    $("#noofpresents").val(json.total_att);
	                	$("#noofabsents").val(json.total_abs);
					});
				}, 500);
			}

			<?php 
				if(Session::has('message')){
					echo "bootbox.alert('".Session::pull('message')."', function(result) {});";
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

			$('.number').keydown(function(e) {
				this.value = this.value.replace(/[^0-9.]/g, ''); 
				this.value = this.value.replace(/(\..*)\./g, '$1');
			});
		
			//datepicker plugin
			//link
			$('.date-picker').datepicker({
				 endDate: '+0d',
				autoclose: true,
				todayHighlight: true
			})
			//show datepicker when clicking on the icon
			.next().on(ace.click_event, function(){
				$(this).prev().focus();
			});

			$('.input-daterange').datepicker({autoclose:true,todayHighlight: true});

			$('.input-mask-phone').mask('(999) 999-9999');
			
			var myTable = null;
			jQuery(function($) {		

				$('input[type=radio][name=day]').change(function() {
			        if (this.value == 'HOLIDAY') {
			            $("#holidayreason").attr("readonly",false);
			        }
			        else {
			        	$("#holidayreason").attr("readonly",true);
			        }
			    });
				
				//initiate dataTables plugin
				myTable = 
				$('#dynamic-table')
				//.wrap("<div class='dataTables_borderWrap' />")   //if you are applying horizontal scrolling (sScrollX)

				//.wrap("<div id='tableData' style='width:300px; overflow: auto;overflow-y: hidden;-ms-overflow-y: hidden; position:relative; margin-right:5px; padding-bottom: 15px;display:block;'/>"); 
		
				.DataTable( {
					bJQueryUI: true,
					"bPaginate": true, "bDestroy": true,
					bInfo: true,
					"aoColumns": [
					  <?php $cnt=$diff; $cnt++; for($i=0; $i<=$cnt; $i++){ echo '{ "bSortable": false },'; }?>
					],
					"aaSorting": [],
					oLanguage: {
				        sProcessing: '<i class="ace-icon fa fa-spinner fa-spin orange bigger-250"></i>'
				    },
					"bProcessing": true,
			        /*"bServerSide": true,
					"ajax":{
		                url :"getattendencedatatabledata?name=<?php //echo $values["provider"] ?>", // json datasource
		                type: "post",  // method  , by default get
		                error: function(){  // error handling
		                    $(".employee-grid-error").html("");
		                    $("#dynamic-table").append('<tbody class="employee-grid-error"><tr>No data found in the server</tr></tbody>');
		                    $("#employee-grid_processing").css("display","none");
		 
		                }
		            },*/
			
					//"sScrollY": "500px",
					//"bPaginate": false,
					"sScrollX" : "true",
					//"sScrollX": "300px",
					//"sScrollXInner": "120%",
					"bScrollCollapse": true,
					//Note: if you are applying horizontal scrolling (sScrollX) on a ".table-bordered"
					//you may want to wrap the table inside a "div.dataTables_borderWrap" element
			
					//"iDisplayLength": 50
			    } );
			
				
				
				$.fn.dataTable.Buttons.swfPath = "../assets/js/dataTables/extensions/buttons/swf/flashExport.swf"; //in Ace demo ../assets will be replaced by correct assets path
				$.fn.dataTable.Buttons.defaults.dom.container.className = 'dt-buttons btn-overlap btn-group btn-overlap';
				
				/*new $.fn.dataTable.Buttons( myTable, {
					buttons: [
					  {
						"extend": "colvis",
						"text": "<i class='fa fa-search bigger-110 blue'></i> <span class='hidden'>Show/hide columns</span>",
						"className": "btn btn-white btn-primary btn-bold",
						columns: ':not(:first):not(:last)'
					  },
					  {
						"extend": "copy",
						"text": "<i class='fa fa-copy bigger-110 pink'></i> <span class='hidden'>Copy to clipboard</span>",
						"className": "btn btn-white btn-primary btn-bold"
					  },
					  {
						"extend": "csv",
						"text": "<i class='fa fa-database bigger-110 orange'></i> <span class='hidden'>Export to CSV</span>",
						"className": "btn btn-white btn-primary btn-bold"
					  },
					  {
						"extend": "excel",
						"text": "<i class='fa fa-file-excel-o bigger-110 green'></i> <span class='hidden'>Export to Excel</span>",
						"className": "btn btn-white btn-primary btn-bold"
					  },
					  {
						"extend": "pdf",
						"text": "<i class='fa fa-file-pdf-o bigger-110 red'></i> <span class='hidden'>Export to PDF</span>",
						"className": "btn btn-white btn-primary btn-bold"
					  },
					  {
						"extend": "print",
						"text": "<i class='fa fa-print bigger-110 grey'></i> <span class='hidden'>Print</span>",
						"className": "btn btn-white btn-primary btn-bold",
						autoPrint: false,
						message: 'This print was produced using the Print button for DataTables'
					  }		  
					]
				} );
				myTable.buttons().container().appendTo( $('.tableTools-container') );
				*/
				
				//style the message box
				var defaultCopyAction = myTable.button(1).action();
				myTable.button(1).action(function (e, dt, button, config) {
					defaultCopyAction(e, dt, button, config);
					$('.dt-button-info').addClass('gritter-item-wrapper gritter-info gritter-center white');
				});
				
				
				var defaultColvisAction = myTable.button(0).action();
				myTable.button(0).action(function (e, dt, button, config) {
					
					defaultColvisAction(e, dt, button, config);
					
					
					if($('.dt-button-collection > .dropdown-menu').length == 0) {
						$('.dt-button-collection')
						.wrapInner('<ul class="dropdown-menu dropdown-light dropdown-caret dropdown-caret" />')
						.find('a').attr('href', '#').wrap("<li />")
					}
					$('.dt-button-collection').appendTo('.tableTools-container .dt-buttons')
				});
			
				////
			
				setTimeout(function() {
					$($('.tableTools-container')).find('a.dt-button').each(function() {
						var div = $(this).find(' > div').first();
						if(div.length == 1) div.tooltip({container: 'body', title: div.parent().text()});
						else $(this).tooltip({container: 'body', title: $(this).text()});
					});
				}, 500);
				
				
				//And for the first simple table, which doesn't have TableTools or dataTables
				//select/deselect all rows according to table header checkbox
				var active_class = 'active';
				$('#simple-table > thead > tr > th input[type=checkbox]').eq(0).on('click', function(){
					var th_checked = this.checked;//checkbox inside "TH" table header
					
					$(this).closest('table').find('tbody > tr').each(function(){
						var row = this;
						if(th_checked) $(row).addClass(active_class).find('input[type=checkbox]').eq(0).prop('checked', true);
						else $(row).removeClass(active_class).find('input[type=checkbox]').eq(0).prop('checked', false);
					});
				});
				
				//select/deselect a row when the checkbox is checked/unchecked
				$('#simple-table').on('click', 'td input[type=checkbox]' , function(){
					var $row = $(this).closest('tr');
					if(this.checked) $row.addClass(active_class);
					else $row.removeClass(active_class);
				});
			
				
			
				/********************************/
				//add tooltip for small view action buttons in dropdown menu
				$('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
				
				//tooltip placement on right or left
				function tooltip_placement(context, source) {
					var $source = $(source);
					var $parent = $source.closest('table')
					var off1 = $parent.offset();
					var w1 = $parent.width();
			
					var off2 = $source.offset();
					//var w2 = $source.width();
			
					if( parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2) ) return 'right';
					return 'left';
				}
				$('<button style="margin-top:-5px;" class="btn btn-minier btn-primary" id="refresh"><i style="margin-top:-2px; padding:6px; padding-right:5px;" class="ace-icon fa fa-refresh bigger-110"></i></button>').appendTo('div.dataTables_filter');
				$("#refresh").on("click",function(){ myTable.search( '', true ).draw(); });
			});
			
		</script>
	@stop