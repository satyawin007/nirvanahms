<?php
use Illuminate\Support\Facades\Input;
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
			INVENTORY
			<i class="ace-icon fa fa-angle-double-right"></i>
			{{$values['bredcum']}}
		</small>
	@stop

	@section('page_content')
		<div id="accordion1" class="col-xs-offset-0 col-xs-12 accordion-style1 panel-group">			
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#TEST">
							<i class="ace-icon fa fa-angle-down bigger-110" data-icon-hide="ace-icon fa fa-angle-down" data-icon-show="ace-icon fa fa-angle-right"></i>
							&nbsp; {{$values['bredcum']}}
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
						<div class="col-xs-6" style="margin-top: 15px; margin-bottom: -10px">
						<?php if($form_field['type'] === "text" || $form_field['type'] === "email" ||$form_field['type'] === "number" || $form_field['type'] === "password"){ ?>
						<div class="form-group">
							<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
							<div class="col-xs-7">
								<input {{$form_field['readonly']}}  type="{{$form_field['type']}}" value="{{$form_field['value']}}"  id="{{$form_field['name']}}" {{$form_field['required']}} name="{{$form_field['name']}}" class="{{$form_field['class']}}" <?php if(isset($form_field['action'])) { $action = $form_field['action'];  echo $action['type']."=".$action['script']; }?>>
							</div>			
						</div>
						<?php } ?>
						
						<?php if($form_field['type'] === "hidden"){ ?>
						<div class="form-group">
							<div class="col-xs-7">
								<input type="{{$form_field['type']}}" id="{{$form_field['name']}}" name="{{$form_field['name']}}" value="{{$form_field['value']}}" />
							</div>			
						</div>
						<?php } ?>
						
						<?php if($form_field['type'] === "textarea"){ ?>				
						<div class="form-group">
							<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
							<div class="col-xs-7">
								<textarea {{$form_field['readonly']}} id="{{$form_field['name']}}" name="{{$form_field['name']}}" class="{{$form_field['class']}}">{{$form_field['value']}}</textarea>
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
										$selcted = "";
										if($form_field['value']==$key){
											$selcted = " checked='true' ";
										}
										echo "<label><input type='radio' $selcted name=\"".$form_field['name']."\"class='ace' value='$key'> <span class='lbl'>".$value."</span></label>&nbsp;&nbsp;";
									}
								?>
								</div>
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
							<script><?php if(isset($form_field['value']) && $form_field['value'] != "") echo "fileExist = true; fileName='".$form_field['value']."'; "; else echo "fileExist = false;";?></script>
						<?php } ?>
						
						<?php if($form_field['type'] === "checkboxslide"){ ?>
							<div class="form-group">
								<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
								<div class="col-xs-7" style="margin-top: 6px;">
									<input id="{{$form_field['name']}}"  name="{{$form_field['name']}}"  class="ace ace-switch ace-switch-5" <?php if(isset($form_field['value']) && $form_field['value'] == "Yes") echo ' checked="checked" '; ?> type="checkbox" />
									<span class="lbl"></span>
								</div>
							</div>
						<?php } ?>	
						
						<?php if($form_field['type'] === "select"){ ?>
						<div class="form-group">
							<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
							<div class="col-xs-7">
								<select class="{{$form_field['class']}}"  {{$form_field['required']}}  name="{{$form_field['name']}}" id="{{$form_field['id']}}" <?php if(isset($form_field['action'])) { $action = $form_field['action'];  echo $action['type']."=".$action['script']; }?> <?php if(isset($form_field['multiple'])) { echo " multiple "; }?>>
									<option value="">-- {{$form_field['name']}} --</option>
									<?php 
										foreach($form_field["options"] as $key => $value){
											if(isset($form_field['multiple']) && isset($form_field['value'])&& in_array($key, $form_field['value'])){
												echo "<option selected value='$key'>$value</option>";
											}
											else if(isset($form_field['value']) && $form_field['value']==$key){
												echo "<option selected value='$key'>$value</option>";
											}
											else {
												echo "<option value='$key'>$value</option>";
											}											
										}
									?>
								</select>
							</div>			
						</div>				
						<?php } ?>
					</div>
					
					<?php } ?>
					<div id="addfields">
					<?php $form_fields = $form_info['form_payment_fields'];?>	
					<?php foreach ($form_fields as $form_field) {?>
						<div class="col-xs-6" >
						<?php if($form_field['type'] === "text" || $form_field['type'] === "email" ||$form_field['type'] === "number" || $form_field['type'] === "password"){ ?>
						<div class="form-group">
							<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
							<div class="col-xs-7">
								<input {{$form_field['readonly']}}  type="{{$form_field['type']}}" value="{{$form_field['value']}}"  id="{{$form_field['name']}}" {{$form_field['required']}} name="{{$form_field['name']}}" class="{{$form_field['class']}}" <?php if(isset($form_field['action'])) { $action = $form_field['action'];  echo $action['type']."=".$action['script']; }?>>
							</div>			
						</div>
						<?php } ?>
						
						<?php if($form_field['type'] === "hidden"){ ?>
						<div class="form-group">
							<div class="col-xs-7">
								<input type="{{$form_field['type']}}" id="{{$form_field['name']}}" name="{{$form_field['name']}}" value="{{$form_field['value']}}" />
							</div>			
						</div>
						<?php } ?>
						
						<?php if($form_field['type'] === "textarea"){ ?>				
						<div class="form-group">
							<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
							<div class="col-xs-7">
								<textarea {{$form_field['readonly']}} id="{{$form_field['name']}}" name="{{$form_field['name']}}" class="{{$form_field['class']}}">{{$form_field['value']}}</textarea>
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
						
						<?php if($form_field['type'] === "file"){ ?>				
							<div class="form-group">
								<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
								<div class="col-xs-7">
									<input type="file" id="{{$form_field['name']}}" name="{{$form_field['name']}}" class="form-control file"/>
								</div>			
							</div>
							<script><?php if(isset($form_field['value']) && $form_field['value'] != "") echo "fileExist = true; fileName='".$form_field['value']."'; "; ?></script>
						<?php } ?>
						
						<?php if($form_field['type'] === "checkboxslide"){ ?>
							<div class="form-group">
								<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
								<div class="col-xs-7" style="margin-top: 6px;">
									<input id="{{$form_field['name']}}"  name="{{$form_field['name']}}"  class="ace ace-switch ace-switch-5" <?php if(isset($form_field['value']) && $form_field['value'] == "Yes") echo ' checked="checked" '; ?> type="checkbox" />
									<span class="lbl"></span>
								</div>
							</div>
						<?php } ?>	
						
						<?php if($form_field['type'] === "select"){ ?>
						<div class="form-group">
							<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
							<div class="col-xs-7">
								<select class="{{$form_field['class']}}"  {{$form_field['required']}}  name="{{$form_field['name']}}" id="{{$form_field['id']}}" <?php if(isset($form_field['action'])) { $action = $form_field['action'];  echo $action['type']."=".$action['script']; }?> <?php if(isset($form_field['multiple'])) { echo " multiple "; }?>>
									<option value="">-- {{$form_field['name']}} --</option>
									<?php 
										foreach($form_field["options"] as $key => $value){
											if(isset($form_field['multiple']) && isset($form_field['value'])&& in_array($key, $form_field['value'])){
												echo "<option selected value='$key'>$value</option>";
											}
											else if(isset($form_field['value']) && $form_field['value']==$key){
												echo "<option selected value='$key'>$value</option>";
											}
											else {
												echo "<option value='$key'>$value</option>";
											}											
										}
									?>
								</select>
							</div>			
						</div>				
						<?php } ?>
						</div>
					<?php } ?>	
				</div>
			</div>
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
							<th>Drug</th>
							<th>Drug Type</th>
							<th>Drug Info</th>
							<th>Quantity</th>
							<th>Price of Unit</th>
							<th>Entity Id</th>
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
				<button id="submit" class="btn primary" type="submit" id="submit">
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
										<textarea {{$form_field['readonly']}} id="{{$form_field['name']}}" name="{{$form_field['name']}}" class="{{$form_field['class']}}" <?php if(isset($form_field['action'])) { $action = $form_field['action'];  echo $action['type']."=".$action['script']; }?>></textarea>
									</div>			
								</div>
								<?php } ?>
								
								<?php if($form_field['type'] === "select"){ ?>
								<div class="form-group">
									<label class="col-xs-3 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
									<div class="col-xs-7">
										<select class="{{$form_field['class']}}" name="{{$form_field['name']}}" id="{{$form_field['name']}}"  <?php if(isset($form_field['action'])) { $action = $form_field['action'];  echo $action['type']."=".$action['script']; }?>>
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
		<script src="../assets/js/autosize.js"></script>
	@stop
	
	@section('inline_js')
		<!-- inline scripts related to this page -->
		<script type="text/javascript">
			$("#totalamount").attr("readonly",true);
			$("#paymenttype").attr("disabled",true);
			$("#incharge").attr("disabled",true);
			$("#enableincharge").val("NO");
			$("#paymentdate").attr("disabled",true);
			pmtpaid = $("#amountpaid").val();
			if(pmtpaid == "Yes"){
				$("#paymentdate").attr("disabled",false);
			}
			<?php
				$select_args = array();
				$select_args[] = "items.name as item";
				$select_args[] = "item_types.name as itemtype";
				$select_args[] = "manufactures.name as manufacturer";
				$select_args[] = "purchased_items.itemNumbers as itemNumbers";
				$select_args[] = "purchased_items.qty as qty";
				$select_args[] = "purchased_items.unitPrice as unitPrice";
				$select_args[] = "purchased_items.itemStatus as itemStatus";
				$select_args[] = "purchased_items.status as status";
				$select_args[] = "purchased_items.id as id";
				$select_args[] = "purchased_items.itemId as itemId";
				$select_args[] = "purchased_items.itemTypeId as itemTypeId";
				$select_args[] = "purchased_items.manufacturerId as manufacturerId";
				$entities = \PurchasedItems::where("purchased_items.status","=","ACTIVE")
								->where("purchasedOrderId","=",$values["id"])
								->leftjoin("items","items.id","=","purchased_items.itemId")
								->leftjoin("manufactures","manufactures.id","=","purchased_items.manufacturerId")
								->leftjoin("item_types","item_types.id","=","purchased_items.itemTypeId")
								->select($select_args)->get();
				echo "var row = ".count($entities)."; ";
				$table_data = "tabledata = [";
				$i = -1;
				foreach ($entities as $entity){
					$i++;
					$table_data = $table_data."['".$entity->item."', '".$entity->itemtype."', '".$entity->manufacturer."', '".$entity->qty."', '".$entity->unitPrice."', '".$entity->id."', ";
					$table_data = $table_data.'\'<button class="btn btn-sm btn-primary" onclick="editItem('.($i).')">Edit</button>&nbsp;&nbsp;&nbsp;<button class="btn btn-sm btn-danger" onclick="removeItem('.($i).')">Remove</button>\', \''.$entity->itemId."', '".$entity->itemTypeId."', '".$entity->manufacturerId."'],";    
				}
				$table_data = $table_data."]; ";
				echo $table_data;
			?>
			drawTable();
			isEdit = false;
			editRowId = -1;
			function getFormValues(){
				if(!$("#itemnumbers").attr("readonly")){
					ckval = $("#itemnumbers").val();
					if(!validateInput(ckval)){
						return false;
					}
				}
				tr = [];
				country = $("#item option:selected").text();
				tr[0] = country;
				itemtype = $("#itemtype option:selected").text();
				tr[1] = itemtype;
				fname = $("#iteminfo option:selected").text();
				tr[2] = fname;
				lname = $("#quantity").val();
				tr[3] = lname;
				unitprice = $("#unitprice").val();
				tr[4] = unitprice;
				tr[6] = '<button class="btn btn-sm btn-primary" onclick="editItem('+row+')">Edit</button>&nbsp;&nbsp;&nbsp;'+'<button class="btn btn-sm btn-danger" onclick="removeItem('+row+')">Remove</button>';
				tr[7] = $("#item").val();
				tr[8] = $("#itemtype").val();
				tr[9] = $("#iteminfo").val();
				if(country != "" && fname!="" && lname!="" && unitprice!=""){
					if(isEdit && editRowId>=0){
						for(i=0; i<row; i++){
							if(editRowId == i){
								tabledata[i][0] = tr[0];
								tabledata[i][1] = tr[1];
								tabledata[i][2] = tr[2];
								tabledata[i][3] = tr[3];
								tabledata[i][4] = tr[4];
								tabledata[i][6] = '<button class="btn btn-sm btn-primary" onclick="editItem('+editRowId+')">Edit</button>&nbsp;&nbsp;&nbsp;'+'<button class="btn btn-sm btn-danger" onclick="removeItem('+editRowId+')">Remove</button>';;
								tabledata[i][7] = $("#item").val();
								tabledata[i][8] = $("#itemtype").val();
								tabledata[i][9] = $("#iteminfo").val();
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
					$("#itemtype option").each(function() { this.selected = (this.value == ""); });
					$("#iteminfo option").each(function() { this.selected = (this.value == ""); });
					$("#quantity").val("");
					$("#unitprice").val("");
					$('.chosen-select').trigger('chosen:updated');
				}
			}

			function getCreditSupplierByState(val){
				$.ajax({
				  url: "getcreditsuppliersbystate?branchId="+val,
				  success: function(data) {
					  var obj = JSON.parse(data);
					  $("#creditsupplier").html(obj.suppliers);
					  $("#receivedby").html(obj.incharges);
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

			function validateInput(val){
				itemnumbers = $("#itemnumbers").val();
				qty = $("#quantity").val();
				itemnumbers = itemnumbers.split(",");
				if(qty != itemnumbers.length){
					alert("Quantity and Item Numbers count does not match");
					return false;
				}
				return true;
			}


			function getManufacturers(id){
				$("#itemnumbers").attr("readonly",true);
				$.ajax({
			      url: "getmanufacturers?itemid="+id,
			      success: function(data) {
			    	  var obj = JSON.parse(data);
			    	  $("#iteminfo").html(obj.manufactures);
			    	  $("#itemtype").html(obj.itemtypes);
			    	  if(obj.itemnumberstatus=="Yes"){
			    		  $("#itemnumbers").attr("readonly",false);
			    	  }			    	  
					  $('.chosen-select').trigger('chosen:updated');
			      },
			      type: 'GET'
			   });
			}
			
			function showPaymentFields(val){
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
					$("#paymenttype option:selected").removeAttr("selected");
					$("#paymenttype").attr("disabled",true);
					$("#paymentdate").attr("disabled",true);
					$("#addfields").html("");
				}
			}

			function resetValues(){
				isEdit = false;
				editRowId = -1;
				$("#status option").each(function() { this.selected = (this.value == ""); });
				$("#item option").each(function() { this.selected = (this.value == ""); });
				$("#itemtype option").each(function() { this.selected = (this.value == ""); });
				$("#iteminfo option").each(function() { this.selected = (this.value == ""); });
				$("#quantity").val("");
				$("#unitprice").val("");
				$("#itemnumbers").val("");
				$("#itemnumbers").attr("readonly",false);
				$('.chosen-select').trigger('chosen:updated');
			}

			function removeItem(rowid){
				for(i=0; i<row; i++){
					if(rowid == i){
						for(j=0; j<8; j++){	
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
						getManufacturers(tabledata[i][7]);
						$("#itemnumbers").val(tabledata[i][3]);
						$("#quantity").val(tabledata[i][3]);				
						$("#unitprice").val(tabledata[i][4]);
						$("#item option").each(function() {this.text.trim(); tempele = tabledata[i][0].trim();   this.selected = (this.text == tempele)});
						$("#itemtype option").each(function() {this.text.trim(); tempele = tabledata[i][1].trim();  this.selected = (this.text == tabledata[i][1]); });
						$("#iteminfo option").each(function() { this.text.trim(); tempele = tabledata[i][2].trim();  this.selected = (this.text == tabledata[i][2]); });
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
							if(j<5){
								jsondata = jsondata+"\"i"+j+"\":\""+tabledata[i][j]+"\",";
							}
							if(j==5){
								jsondata = jsondata+"\"i"+j+"\":\""+tabledata[i][j]+"\",";
								jsondata = jsondata+"\"i"+6+"\":\""+tabledata[i][7]+"\",";
								jsondata = jsondata+"\"i"+7+"\":\""+tabledata[i][8]+"\",";
								jsondata = jsondata+"\"i"+8+"\":\""+tabledata[i][9]+"\"";
							}
						}
						totalamt = totalamt+(tabledata[i][3]*tabledata[i][4]);
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

			if(fileExist)
				$('.file').ace_file_input('show_file_list', [fileName]);

			//$('.input-mask-phone').mask('(999) 999-9999');
			
			<?php 
				if(Session::has('message')){
					echo "bootbox.confirm('".Session::pull('message')."', function(result) {});";
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
		
		
			$('#id-input-file-3').ace_file_input({
				style: 'well',
				btn_choose: 'Drop files here or click to choose',
				btn_change: null,
				no_icon: 'ace-icon fa fa-cloud-upload',
				droppable: true,
				thumbnail: 'small'//large | fit
				//,icon_remove:null//set null, to hide remove/reset button
				/**,before_change:function(files, dropped) {
					//Check an example below
					//or examples/file-upload.html
					return true;
				}*/
				/**,before_remove : function() {
					return true;
				}*/
				,
				preview_error : function(filename, error_code) {
					//name of the file that failed
					//error_code values
					//1 = 'FILE_LOAD_FAILED',
					//2 = 'IMAGE_LOAD_FAILED',
					//3 = 'THUMBNAIL_FAILED'
					//alert(error_code);
				}
		
			}).on('change', function(){
				//console.log($(this).data('ace_input_files'));
				//console.log($(this).data('ace_input_method'));
			});
			
			
			//$('#id-input-file-3')
			//.ace_file_input('show_file_list', [
				//{type: 'image', name: 'name of image', path: 'http://path/to/image/for/preview'},
				//{type: 'file', name: 'hello.txt'}
			//]);
		
			
			
		
			//dynamically change allowed formats by changing allowExt && allowMime function
			$('#id-file-format').removeAttr('checked').on('change', function() {
				var whitelist_ext, whitelist_mime;
				var btn_choose
				var no_icon
				if(this.checked) {
					btn_choose = "Drop images here or click to choose";
					no_icon = "ace-icon fa fa-picture-o";
		
					whitelist_ext = ["jpeg", "jpg", "png", "gif" , "bmp"];
					whitelist_mime = ["image/jpg", "image/jpeg", "image/png", "image/gif", "image/bmp"];
				}
				else {
					btn_choose = "Drop files here or click to choose";
					no_icon = "ace-icon fa fa-cloud-upload";
					
					whitelist_ext = null;//all extensions are acceptable
					whitelist_mime = null;//all mimes are acceptable
				}
				var file_input = $('#id-input-file-3');
				file_input
				.ace_file_input('update_settings',
				{
					'btn_choose': btn_choose,
					'no_icon': no_icon,
					'allowExt': whitelist_ext,
					'allowMime': whitelist_mime
				})
				file_input.ace_file_input('reset_input');
				
				file_input
				.off('file.error.ace')
				.on('file.error.ace', function(e, info) {
					//console.log(info.file_count);//number of selected files
					//console.log(info.invalid_count);//number of invalid files
					//console.log(info.error_list);//a list of errors in the following format
					
					//info.error_count['ext']
					//info.error_count['mime']
					//info.error_count['size']
					
					//info.error_list['ext']  = [list of file names with invalid extension]
					//info.error_list['mime'] = [list of file names with invalid mimetype]
					//info.error_list['size'] = [list of file names with invalid size]
					
					
					/**
					if( !info.dropped ) {
						//perhapse reset file field if files have been selected, and there are invalid files among them
						//when files are dropped, only valid files will be added to our file array
						e.preventDefault();//it will rest input
					}
					*/
					
					
					//if files have been selected (not dropped), you can choose to reset input
					//because browser keeps all selected files anyway and this cannot be changed
					//we can only reset file field to become empty again
					//on any case you still should check files with your server side script
					//because any arbitrary file can be uploaded by user and it's not safe to rely on browser-side measures
				});
				
				
				/**
				file_input
				.off('file.preview.ace')
				.on('file.preview.ace', function(e, info) {
					console.log(info.file.width);
					console.log(info.file.height);
					e.preventDefault();//to prevent preview
				});
				*/
			
			});
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