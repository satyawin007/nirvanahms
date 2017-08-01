<?php
use Illuminate\Support\Facades\Input;
use settings\AppSettingsController;
?>
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
			    white-space: wrap;
			}
			td {
			    white-space: wrap;
			}
			panel-group .panel {
			    margin-bottom: 20px;
			    border-radius: 4px;
			}
			.table {
			    width: 100%;
			    max-width: 100%;
			    margin-bottom: 0px;
			}
			.form-actions {
			    display: block;
			    background-color: #F5F5F5;
			    border-top: 1px solid #E5E5E5;
			    /* margin-bottom: 20px; 
			    margin-top: 20px;
			    padding: 19px 20px 20px;*/
			}
			.chosen-container{
			  width: 100% !important;
			}
		</style>
	@section('page_css')
		<link rel="stylesheet" href="../assets/css/jquery-ui.custom.css" />
		<link rel="stylesheet" href="../assets/css/bootstrap-datepicker3.css"/>
		<link rel="stylesheet" href="../assets/css/chosen.css" />
		<link rel="stylesheet" href="../assets/css/daterangepicker.css" />
	@stop
		
	@stop
	
	@section('bredcum')	
		<small>
			TRANSACTIONS
			<i class="ace-icon fa fa-angle-double-right"></i>
			{{$values['bredcum']}}
		</small>
	@stop

	@section('page_content')
	<div id="accordion2" class="col-xs-offset-0 col-xs-12 accordion-style1 panel-group" style="width: 99%;">			
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#TEST">
							<i class="ace-icon fa fa-angle-down bigger-110" data-icon-hide="ace-icon fa fa-angle-down" data-icon-show="ace-icon fa fa-angle-right"></i>
							&nbsp;ADD REPAIR TRANSACTION
						</a>
					</h4>
				</div>
				<div class="panel-collapse collapse in" id="TEST">
					<div class="panel-body" style="padding: 0px">
						<div class="col-xs-offset-1 col-xs-10" style="margin-top: 1%; margin-bottom: 1%">
							<div class="col-xs-6">
								<div class="form-group">
									<?php 
										$branches = AppSettingsController::getEmpBranches();
										$branches_arr = array();
										foreach ($branches as $branch){
											$branches_arr[$branch["id"]] = $branch["name"];
										}
									?>
									<?php $form_field = array("name"=>"branch2", "content"=>"branch", "readonly"=>"",  "required"=>"required", "type"=>"select", "class"=>"form-control chosen-select", "options"=>$branches_arr); ?>
									<label class="col-xs-5 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-7">
										<select class="{{$form_field['class']}}"  {{$form_field['required']}}  name="{{$form_field['name']}}" id="{{$form_field['name']}}" <?php if(isset($form_field['action'])) { $action = $form_field['action'];  echo $action['type']."=".$action['script']; }?> <?php if(isset($form_field['multiple'])) { echo " multiple "; }?>>
											<option value="">-- {{$form_field['name']}} --</option>
											<?php 
												foreach($form_field["options"] as $key => $value){
													echo "<option value='$key'>$value</option>";
												}
											?>
										</select>
									</div>			
								</div>	
							</div>
							<div class="col-xs-5">
								<div class="form-group">
									<label style="margin-left: 40px;" class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper("date");  echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-6">
										<input type="text" id="dt" required="required" name="dt" class="form-control date-picker" />
									</div>			
								</div>
							</div>			
							<div class="col-xs-1" >
								<div class="form-group">
									<label class="col-xs-0 control-label no-padding-right" for="form-field-1"> </label>
									<div class="col-xs-5" id="verify">
										<input type="button" class="btn btn-sm btn-primary" value="VERIFY" onclick="verifyDate()"/>
									</div>			
								</div>
							</div>
					</div>
				</div>
			</div>
		</div>
	
		<div class="col-xs-12" style="margin-top: -1%">
		<div class="">
			<div class="">
				<div>
				<form style="padding-top: 0px; display: none;" class="form-horizontal" action="addtransaction" method="post" name="transactionform" id="transactionform" enctype="multipart/form-data">
										<div id="formbody">	
										</div>
					
<!-- 					<div id="addfields"></div> -->
					<div class="clearfix form-actions">
						<div class="col-md-offset-4 col-md-8" style="margin-top: 2%; margin-bottom: 1%">
							<button id="submit" class="btn primary" type="submit">
								<i class="ace-icon fa fa-check bigger-110"></i>
								SUBMIT
							</button>
							<!--  <input type="submit" class="btn btn-info" type="button" value="SUBMIT"> -->
							&nbsp; &nbsp; &nbsp;
							<button id="reset" class="btn" type="reset">
								<i class="ace-icon fa fa-undo bigger-110"></i>
								RESET
							</button>
						</div>
					</div>
				</form>
			</div>
				</div>
			</div>
		</div>
							
					</div>
				</div>
			</div>
		</div>
		<div id="totalbody">
		<div id="accordion1" class="col-xs-offset-0 col-xs-12 accordion-style1 panel-group">			
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#TEST">
							<i class="ace-icon fa fa-angle-down bigger-110" data-icon-hide="ace-icon fa fa-angle-down" data-icon-show="ace-icon fa fa-angle-right"></i>
							&nbsp;ADD REPAIR TRANSACTION
						</a>
					</h4>
				</div>
				<div class="panel-collapse collapse in" id="TEST">
					<div class="panel-body" style="padding: 0px">
						<?php $form_info = $values["form_info"]; ?>
						<form style="padding-top:20px;" class="{{$form_info['class']}}" action="{{$form_info['action']}}" method="{{$form_info['method']}}" name="{{$form_info['name']}}"  id="{{$form_info['name']}}" enctype="multipart/form-data">
							<div>
							<?php $form_fields = $form_info['form_fields'];?>	
							<?php foreach ($form_fields as $form_field) {?>
								<div class="col-xs-6">
								<?php if($form_field['type'] === "text" || $form_field['type'] === "email" ||$form_field['type'] === "number" || $form_field['type'] === "password"){ ?>
								<div class="form-group" >
									<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-7">
										<input {{$form_field['readonly']}} type="{{$form_field['type']}}" id="{{$form_field['name']}}" {{$form_field['required']}} name="{{$form_field['name']}}" class="{{$form_field['class']}}" <?php if(isset($form_field['action'])) { $action = $form_field['action'];  echo $action['type']."=".$action['script']; }?>>
									</div>			
								</div>
								<?php } ?>
								<?php if($form_field['type'] === "empty" ){ ?>
								<div class="form-group" >
									<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-7">
										<label class="control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									</div>			
								</div>
								<?php } ?>
								<?php if($form_field['type'] === "hidden"){ ?>
										<input type="{{$form_field['type']}}" id="{{$form_field['name']}}" name="{{$form_field['name']}}" value="{{$form_field['value']}}" >
								<?php } ?>
								<?php if($form_field['type'] === "textarea"){ ?>				
								<div class="form-group">
									<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-7">
										<textarea {{$form_field['readonly']}} id="{{$form_field['name']}}" name="{{$form_field['name']}}" class="{{$form_field['class']}}"></textarea>
									</div>			
								</div>
								<?php } ?>
								
								<?php if($form_field['type'] === "file"){ ?>				
								<div class="form-group">
									<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-7">
										<input type="file" id="{{$form_field['name']}}" name="{{$form_field['name']}}" class="form-control file"/>
									</div>			
								</div>
								<?php } ?>
								
								<?php if($form_field['type'] === "radio"){ ?>				
								<div class="form-group">
									<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-7">
										<div class="radio">
										<?php 
											foreach($form_field["options"] as $key => $value){
												echo "<label><input type='radio' name=\"".$form_field['name']."\"class='ace' value='$key'> <span class='lbl'>".$value."</span></label>&nbsp;&nbsp;";
											}
										?>
										</div>
									</div>			
								</div>
								<?php } ?>
								<?php if($form_field['type'] === "select"){ ?>
								<div class="form-group">
									<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-7">
										<select class="{{$form_field['class']}}" {{$form_field['required']}} name="{{$form_field['name']}}" id="{{$form_field['name']}}" <?php if(isset($form_field['action'])) { $action = $form_field['action'];  echo $action['type']."=".$action['script']; }?>  <?php if(isset($form_field['multiple'])) { echo " multiple "; }?>>
											<option value="">-- {{$form_field['name']}} --</option>
											<?php 
												foreach($form_field["options"] as $key => $value){
													if(isset($form_field['value']) && $form_field['value']==$key) { 
														echo "<option selected='selected' value='$key'>$value</option>";
													}
													else{
														echo "<option value='$key'>$value</option>";
													}
												}
											?>
										</select>
									</div>			
								</div>				
								<?php } ?>
								
								<?php if($form_field['type'] === "checkboxslide"){ ?>
									<div class="form-group">
										<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
										<div class="col-xs-7" style="margin-top: 3px;">
											<input name="switch-field-1" class="ace ace-switch ace-switch-5" type="checkbox" />
											<span class="lbl"></span>
										</div>
									</div>
								<?php } ?>	
								
								<?php if($form_field['type'] === "checkbox"){ ?>
									<div class="form-group">
										<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
										<div class="col-xs-7">
											<?php 
											$options = $form_field["options"];
											foreach ($options as $key=>$value) {
											?>
											<div class="checkbox inline">
												<label>
													<input name="{{$key}}" value="YES" type="checkbox" class="ace">
													<span class="lbl">&nbsp;{{$key}} &nbsp;&nbsp;</span>
												</label>
											</div>
											<?php } ?>
										</div>
									</div>
								<?php } ?>						
							</div>							
							<?php } ?>
					</div>
					 <div id="addfields"></div> 
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-xs-12">
				<!-- div.dataTables_borderWrap -->
				<div>
					<div style="margin-top: 10px;">
					<a class="btn btn-sm btn-primary"  href="#modal-form"  data-toggle="modal">ADD ITEM</a> &nbsp;&nbsp;
				</div>
					<table id="dynamic-table1" class="table table-striped table-bordered table-hover">
					<thead>
						<tr>
							<th>Item</th>
							<th>Vehicles</th>
							<th>Veh Reading</th>
							<th>Quantity</th>
							<th>Amount</th>
							<th>Remarks</th>
							<th>Actions</th>
						</tr>
					</thead>
						<tbody id="tbody">
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="clearfix form-actions" style="margin-bottom: 0px;" >
			<div class="col-md-offset-4 col-md-8" style="margin-top: 2%; margin-bottom: 1%">
				<button class="btn primary" type="submit" id="submit1">
					<i class="ace-icon fa fa-check bigger-110"></i>
					SUBMIT
				</button>
				<!--  <input type="submit" class="btn btn-info" type="button" value="SUBMIT"> -->
				&nbsp; &nbsp; &nbsp;
				<button id="reset" class="btn" type="reset">
					<i class="ace-icon fa fa-undo bigger-110"></i>
					RESET
				</button>
			</div>
		</div>
		</form>
	</div>	
	</div>
	<div id="modal-form" class="modal" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="blue bigger">Please fill the following form fields</h4>
				</div>
	
				<div class="modal-body">
					<?php 
						$modals = $values["modals"];
						foreach ($modals as $modal){
						$form_fields = $modal['form_fields'];
					?>
					<div class="row">
						<div class="col-xs-12">
						<form class="form-horizontal" >	
							<?php $form_fields = $modal['form_fields'];?>	
							<?php foreach ($form_fields as $form_field) {?>
								<?php if($form_field['type'] === "text" || $form_field['type'] === "email" || $form_field['type'] === "password"){ ?>
								<div class="form-group">
									<label class="col-xs-3 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-7">
										<input {{$form_field['readonly']}} type="{{$form_field['type']}}" id="{{$form_field['name']}}" required="{{$form_field['required']}}" name="{{$form_field['name']}}" class="{{$form_field['class']}}" <?php if(isset($form_field['action'])) { $action = $form_field['action'];  echo $action['type']."=".$action['script']; }?>>
									</div>			
								</div>
								<?php } ?>
								<?php if($form_field['type'] === "hidden"){ ?>
									<input type="{{$form_field['type']}}" id="{{$form_field['name']}}" name="{{$form_field['name']}}" value="{{$form_field['value']}}" >
								<?php } ?>
								<?php if($form_field['type'] === "textarea"){ ?>				
								<div class="form-group">
									<label class="col-xs-3 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-7">
										<textarea {{$form_field['readonly']}} id="{{$form_field['name']}}" name="{{$form_field['name']}}" class="{{$form_field['class']}}"></textarea>
									</div>			
								</div>
								<?php } ?>
								
								<?php if($form_field['type'] === "select"){ ?>
								<div class="form-group">
									<label class="col-xs-3 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-7">
										<select class="{{$form_field['class']}}" name="{{$form_field['name']}}" id="{{$form_field['name']}}"  <?php if(isset($form_field['action'])) { $action = $form_field['action'];  echo $action['type']."=".$action['script']; }?>  <?php if(isset($form_field['multiple'])) {  echo " multiple "; }?>>
											<option value="">-- {{$form_field['name']}} --</option>
											<?php 
												foreach($form_field["options"] as $key => $value){
													echo "<option value='$key'>$value</option>";
												}
											?>
										</select>
									</div>			
								</div>				
								<?php } ?>
								<?php if($form_field['type'] === "checkbox"){ ?>
								<div class="form-group">
									<label class="col-xs-3 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-8">
										<?php 
										$options = $form_field["options"];
										foreach ($options as $key=>$value) {
										?>
										<div class="checkbox inline">
											<label>
												<input name="{{$key}}" id="{{$key}}" value="YES" type="checkbox" class="ace">
												<span class="lbl">&nbsp;{{$value}} &nbsp;&nbsp;</span>
											</label>
										</div>
										<?php } ?>
									</div>
								</div>
								<?php } ?>	
								<?php if($form_field['type'] === "radio"){ ?>
								<div class="form-group">
									<label class="col-xs-3 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-8">
										<?php 
											$options = $form_field["options"];
											foreach ($options as $key=>$value) {
										?>
										<div class="radio inline">
											<label>
												<input name="{{$form_field['content']}}" id="{{$value}}" value="{{$value}}" type="radio" class="ace">
												<span class="lbl">&nbsp;{{$value}} &nbsp;&nbsp;</span>
											</label>
										</div>
										<?php } ?>
									</div>
								</div>
								<?php } ?>
							
							<?php } ?>
							
							<div class="modal-footer">
								<button class="btn btn-sm" data-dismiss="modal" onclick="return resetValues()">
									<i class="ace-icon fa fa-times"></i>
									Cancel
								</button>
				
								<button class="btn btn-sm btn-primary" data-dismiss="modal" onclick="return getFormValues()">
									<i class="ace-icon fa fa-check"></i>
									Save
								</button>
							</div>
	
							</form>
						</div>
					</div>
					<?php } ?>	
				</div>
			</div>
		</div>
	</div><!-- PAGE CONTENT ENDS -->
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
		<script src="../assets/js/date-time/daterangepicker.js"></script>		
		<script src="../assets/js/bootbox.js"></script>
		<script src="../assets/js/chosen.jquery.js"></script>
	@stop
	
	@section('inline_js')
		<!-- inline scripts related to this page -->
		<script type="text/javascript">
			$("#totalamount").attr("readonly",true);
			$("#totalamount").val("0.00");
			$("#paymenttype").attr("disabled",true);
			$("#paymentdate").attr("disabled",true);
			$("#incharge").attr("disabled",true);
			$("#enableincharge").val("NO");
			$("#totalbody").hide();
			tabledata = [];
			row = 0;
			isEdit = false;
			editRowId = -1;
			function getFormValues(){
				tr = [];
				country = $("#item option:selected").text();
				if($("#item").val() == ""){
					alert("Please Select Item");
					return;
				}
				tr[0] = country;
				text_data =  "";
				$("#vehicles option").each(function() { if(this.selected){ text_data=text_data+this.text+",";} });
				tr[1] = text_data;
				meeterreading = $("#meeterreading").val();
				tr[2] = meeterreading;
				lname = $("#quantity").val();
				tr[3] = lname;
				unitprice = $("#amount").val();
				tr[4] = unitprice;
				status = $("#remarks").val();
				tr[5] = status;
				tr[6] = '<button class="btn btn-sm btn-primary" onclick="editItem('+row+')">Edit</button>&nbsp;&nbsp;&nbsp;'+'<button class="btn btn-sm btn-danger" onclick="removeItem('+row+')">Remove</button>';
				tr[7] = $("#item").val();
				text_data =  "";
				$("#vehicles option").each(function() { if(this.selected){ text_data=text_data+this.value+",";} });
				tr[8] = text_data;
				if(country != ""  && lname!="" && unitprice!=""){
					if(isEdit && editRowId>=0){
						for(i=0; i<row; i++){
							if(editRowId == i){
								tabledata[i][0] = tr[0];
								tabledata[i][1] = tr[1];
								tabledata[i][2] = tr[2];
								tabledata[i][3] = tr[3];
								tabledata[i][4] = tr[4];
								tabledata[i][5] = tr[5];
								tabledata[i][6] = '<button class="btn btn-sm btn-primary" onclick="editItem('+editRowId+')">Edit</button>&nbsp;&nbsp;&nbsp;'+'<button class="btn btn-sm btn-danger" onclick="removeItem('+editRowId+')">Remove</button>';;
								tabledata[i][7] = $("#item").val();
								text_data =  "";
								$("#vehicles option").each(function() { if(this.selected){ text_data=text_data+this.value+",";} });
								tabledata[i][8] = text_data;
								//alert("test"+tabledata[i][7]);
							}
						}
						isEdit = false;
						editRowId = -1;
						drawTable();
					}
					else{
						tabledata[row] = tr;
						row++;
						drawTable();
					}
					$("#item option").each(function() { this.selected = (this.value == ""); });
					$("#vehicles option").each(function() { this.selected = false; });
					$("#quantity").val("");
					$("#meeterreading").val("");
					$("#amount").val("");
					$("#remarks").val("");
					$('.chosen-select').trigger('chosen:updated');
				}
			}

			function verifyDate(){
				branch = $("#branch2").val();
				dt = $("#dt").val();
				if(branch == ""){
					alert("select branch office");
					return;
				}
				if(dt == ""){
					alert("select date");
					return;
				}
				$('#verify').hide();
				$.ajax({
			      url: "verifytransactiondateandbranch?branch="+branch+"&date="+dt,
			      success: function(data) {
				      if(data=="NO"){
				    	  bootbox.confirm('Sorry!!! Transactions closed for this date. Please contact Accountant.', function(result) {});
				    	  $('#verify').show();
				    	  $("#totalbody").hide();
				    	  $("#accordion2").show();
				      }
				      else{
					      $("#branch").val(branch);
					      $("#branchname").val($("#branch2 option:selected").text());
					      $("#date").val(dt);
				    	  $("#totalbody").show();
				    	  $("#accordion2").hide();

				    	  $.ajax({
						      url: "getendreading?id="+1+"&date="+dt,
						      success: function(data) {
						    	  json_data = JSON.parse(data);
						    	  $("#incharge").html(json_data.incharges);
						    	  $('.chosen-select').trigger("chosen:updated");
						      },
						      type: 'GET'
						   });
				      }
			      },
			      type: 'GET'
			   });	
				//$('#trantypebody').show();
			}

			function getManufacturers(id){
				$.ajax({
			      url: "getmanufacturers?itemid="+id,
			      success: function(data) {
			    	  $("#iteminfo").html(data);
					  $('.chosen-select').trigger('chosen:updated');
			      },
			      type: 'GET'
			   });
			}

			function enableIncharge(val){
				if(val == "YES"){
			  		$("#amountpaid").val("Yes");
				  	$("#amountpaid").attr("disabled",false);
				  	$("#paymenttype").attr("disabled",false);
					$("#incharge").attr("disabled",false);
					$('.chosen-select').trigger('chosen:updated');
				}
				else{
					$("#amountpaid").val("No");
				  	$("#amountpaid").attr("disabled",false);
				  	$("#paymenttype").attr("disabled",true);
					$("#incharge").attr("disabled",true);
					$('.chosen-select').trigger('chosen:updated');
				}
			}

			function getFormData(val){
				clientId =  $("#clientname").val();
				depotId = $("#depot").val();
				$.ajax({
			      url: "getvehiclecontractinfo?clientid="+clientId+"&depotid="+depotId+"&type=vehicleids",
			      success: function(data) {
			    	  $("#vehicles").html(data);
			    	  $('.chosen-select').trigger("chosen:updated");
			      },
			      type: 'GET'
			   });
			}

			function changeDepot(val){
				$.ajax({
			      url: "getdepotsbyclientId?id="+val,
			      success: function(data) {
			    	  $("#depot").html(data);
			    	  $('.chosen-select').trigger("chosen:updated");
			      },
			      type: 'GET'
			    });

				clientId =  $("#clientname").val();
				depotId = $("#depot").val();
			}

			function getContractVehicles(val){
				clientId =  $("#clientname").val();
				depotId = $("#depot").val();
				vehiclestatus =  $("input[name=contract_status]:checked").val();
				if(vehiclestatus == undefined){
					vehiclestatus = "ACTIVE";
				}
				//alert(vehiclestatus);
				$.ajax({
			      url: "getvehiclecontractinfo?clientid="+clientId+"&depotid="+depotId+"&vehiclestatus="+vehiclestatus,
			      success: function(data) {
			    	  $("#vehicles").append(data);
			    	  $('.chosen-select').trigger("chosen:updated");
			      },
			      type: 'GET'
			   });
			}			

			function showPaymentFields(val){
				//alert(val);
				$("#addfields").html('<div style="margin-left:600px; margin-top:100px;"><i class="ace-icon fa fa-spinner fa-spin orange bigger-125" style="font-size: 250% !important;"></i></div>');
				$.ajax({
			      url: "getpaymentfields?paymenttype="+val,
			      success: function(data) {
			    	  $("#addfields").html(data);
			    	  $('.date-picker').datepicker({
						autoclose: true,
						todayHighlight: true
					  });
			    	  $("#addfields").show();
			    	  $('.chosen-select').chosen();
			      },
			      type: 'GET'
			   });
			}

			function enablePaymentType(val){
				if(val == "Yes"){
					$("#paymenttype").attr("disabled",false);
					$("#paymentdate").attr("disabled",false);
				}
				else{
					$("#paymenttype").val("");
					$("#paymenttype").attr("disabled",true);
					$("#paymentdate").attr("disabled",true);
					$("#addfields").hide();
				}
			}

			function resetValues(){
				isEdit = false;
				editRowId = -1;
				$("#item option").each(function() { this.selected = (this.value == ""); });
				$("#quantity").val("");
				$("#amount").val("");
				$("#meeterreading").val("");
				$("#remarks").val("");
				$('.chosen-select').trigger('chosen:updated');
			}

			function removeItem(rowid){
				for(i=0; i<row; i++){
					if(rowid == i){
						for(j=0; j<6; j++){	
							tabledata[i][j]= "";
						}
					}
				}
				drawTable();
			}

			function editItem(rowid){
				isEdit = true;
				editRowId = rowid;
				for(i=0; i<row; i++){
					if(editRowId == i){
						$("#meeterreading").val(tabledata[i][2]);
						$("#quantity").val(tabledata[i][3]);				
						$("#amount").val(tabledata[i][4]);
						$("#remarks").val(tabledata[i][5]);
						$("#item option").each(function() { this.selected = (this.text == tabledata[i][0]); });
						$("#vehicles option").each(function() { 
								//alert("test"+tabledata[i][1]);
								var text_arr = tabledata[i][1];
								text_arr = text_arr.split(",");
								for(j=0;j<text_arr.length;j++){
									if(this.text == text_arr[j]){
										this.selected = true; 
									}
								}
							}
						);
						$('.chosen-select').trigger('chosen:updated');
						$("#modal-form").modal("show");
						break;
					}
				}
			}

			function getInchargeBalance(val){
				$.ajax({
			      url: "getinchargebalance?id="+val,
			      success: function(data) {
			    	  $("#inchargebalance").val(data);
			      },
			      type: 'GET'
			   });
			}

			function drawTable(){
				tdata = "";
				totalamt = 0;
				jsondata = "[";
				for(i=0; i<row; i++){
					if(tabledata[i][0] != ""){
						jsondata = jsondata+"{";
						tdata = tdata+"<tr>";
						for(j=0; j<7; j++){	
							tdata = tdata+"<td>"+tabledata[i][j]+"</td>";
						}
						for(j=0; j<9; j++){	
							if(j==6){
							}
							else if(j<8){
								jsondata = jsondata+"\"i"+j+"\":\""+tabledata[i][j]+"\",";
							}
							else if(j==8){
								totalamt = (totalamt*1)+(tabledata[i][4]*1);
								jsondata = jsondata+"\"i"+8+"\":\""+tabledata[i][8]+"\"";
							}
						}
						tdata = tdata+"</tr>";
						if((i+1)==row){
						jsondata = jsondata+"}";
						}
						else{
							jsondata = jsondata+"},";
						}
					}
				}
				jsondata = jsondata+"]";
				$("#jsondata").val(jsondata);
				$("#totalamount").val(totalamt.toFixed(2));
				$("#tbody").html(tdata);
			}

			$("#submit1").on("click",function(){
				creditsupplier = $("#creditsupplier").val();
				if(creditsupplier != undefined && creditsupplier == ""){
					alert("select creditsupplier");
					return false;
				}
				vehicleno = $("#vehicle").val();
				if(vehicleno != undefined && vehicleno == ""){
					alert("select vehicle");
					return false;
				}
				vehicleno = $("#amountpaid").val();
				if(vehicleno != undefined && vehicleno == ""){
					alert("select amount paid");
					return false;
				}
				$("#{{$form_info['name']}}").submit();
			});

			$('.number').keydown(function(e) {
				this.value = this.value.replace(/[^0-9.]/g, ''); 
				this.value = this.value.replace(/(\..*)\./g, '$1');
			});
		
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

			//$('.input-mask-phone').mask('(999) 999-9999');
			
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

			jQuery(function($) {
				//initiate dataTables plugin
				var myTable = 
				$('#dynamic-table')
				//.wrap("<div class='dataTables_borderWrap' />")   //if you are applying horizontal scrolling (sScrollX)
				.DataTable( {
					bAutoWidth: false,
					"aoColumns": [
					  { "bSortable": false },
					  { "bSortable": false },{ "bSortable": false },{ "bSortable": false },{ "bSortable": false },{ "bSortable": false },{ "bSortable": false },{ "bSortable": false },{ "bSortable": false },
					  { "bSortable": false }
					],
					"aaSorting": [],
					
					
					//"bProcessing": true,
			        //"bServerSide": true,
			        //"sAjaxSource": "http://127.0.0.1/table.php"	,
			
					//,
					//"sScrollY": "200px",
					//"bPaginate": false,
			
					"sScrollX": "100%",
					//"sScrollXInner": "120%",
					//"bScrollCollapse": true,
					//Note: if you are applying horizontal scrolling (sScrollX) on a ".table-bordered"
					//you may want to wrap the table inside a "div.dataTables_borderWrap" element
			
					//"iDisplayLength": 50
			
			
					select: {
						style: 'multi'
					}
			    } );
			
				
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
			
				/////////////////////////////////
				//table checkboxes
				
				//select/deselect a row when the checkbox is checked/unchecked
				$('#dynamic-table').on('click', 'td input[type=checkbox]' , function(){
					var row = $(this).closest('tr').get(0);
					if(!this.checked) myTable.row(row).deselect();
					else myTable.row(row).select();
				});
			
				$(document).on('click', '#dynamic-table .dropdown-toggle', function(e) {
					e.stopImmediatePropagation();
					e.stopPropagation();
					e.preventDefault();
				});
				
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
				
			
			})
		</script>
	@stop