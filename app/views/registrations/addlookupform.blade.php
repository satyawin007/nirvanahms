<?php 
	/*
	$form_info = array();
	$form_info["name"] = "addstate";
	$form_info["action"] = "addstate";
	$form_info["method"] = "post";
	$form_info["class"] = "form-horizontal";
	$form_info["back_url"] = "states";
	$form_fields = array();
	$form_field = array("name"=>"fullname", "content"=>"full name", "readonly"=>"",  "required"=>"required","type"=>"text", "class"=>"form-control");
	$form_fields[] = $form_field;
	$form_field = array("name"=>"lastname", "content"=>"last name", "readonly"=>"", "required"=>"","type"=>"email", "class"=>"form-control");
	$form_fields[] = $form_field;
	$form_field = array("name"=>"age", "content"=>"age", "readonly"=>"", "required"=>"required","type"=>"password", "class"=>"form-control");
	$form_fields[] = $form_field;
	$form_field = array("name"=>"id", "content"=>"id", "readonly"=>"", "required"=>"", "type"=>"hidden", "value"=>"1", "class"=>"form-control");
	$form_fields[] = $form_field;
	$form_field = array("name"=>"date", "content"=>"date", "readonly"=>"", "required"=>"required", 	"type"=>"text", "class"=>"form-control date-picker");
	$form_fields[] = $form_field;
	$form_field = array("name"=>"State", "readonly"=>"", "content"=>"state", "class"=>"form-control", "required"=>"required", "type"=>"select",
			"options"=>array("1"=>"test1","2"=>"test2", "3"=>"test3"),
			"action"=>array("type"=>"onChange", "script"=>"paginate(1)"));
	$form_fields[] = $form_field;
	$form_field = array("name"=>"gender", "readonly"=>"","content"=>"gender", "required"=>"required","type"=>"radio", "class"=>"form-control", "options"=>array("male"=>"male", "female"=>"female"));
	$form_fields[] = $form_field;
	$form_field = array("name"=>"address", "readonly"=>"", "content"=>"address", "required"=>"required", "type"=>"textarea", "class"=>"form-control");
	$form_fields[] = $form_field;
	$form_info["form_fields"] = $form_fields;
	return View::make("masters.layouts.addform",array("form_info"=>$form_info));
	*/
?>

	@section('page_css')
		<link rel="stylesheet" href="../assets/css/bootstrap-datepicker3.css"/>
	@stop
	@section('inline_css')
		<style>
			label {
			    font-weight: normal;
			    font-size: 13px;
			}
		</style>
	@stop	
	
		<div class="widget-box col-xs-12">
			<div class="widget-header">
				<h4 class="widget-title">{{ strtoupper($form_info['bredcum'])}}</h4>
				<div style="float:right;padding-right: 2%; margin-top: 5px">
					<a style="color: white;" href="masters" title="masters"><span style="color:white"><i class="ace-icon fa fa-home bigger-200"></i></span></a> &nbsp; &nbsp;
					<a style="color: grey;"  title="{{$form_info['back_url']}}" href="{{$form_info['back_url']}}"><span style="color:white"><i class="ace-icon fa fa-arrow-circle-left bigger-200"></i></span></a>
					<?php if(isset($form_info["addlink"])){ ?>
					    &nbsp;&nbsp;&nbsp;&nbsp;<a style="color: grey;"  title="{{$form_info['addlink']}}"  data-toggle='modal' href="#{{$form_info['addlink']}}"><span style="color:white"><i class="ace-icon fa fa-plus-circle bigger-200"></i></span></a>					    
					<?php } ?>
				</div>
			</div>
			<div class="widget-body">
				<div class="widget-main no-padding">
				<form style="padding-top:20px;" class="{{$form_info['class']}}" action="{{$form_info['action']}}" method="{{$form_info['method']}}" name="{{$form_info['name']}}"  id="{{$form_info['name']}}" enctype="multipart/form-data">
					<div>
					<?php $form_fields = $form_info['form_fields'];?>	
					<?php foreach ($form_fields as $form_field) {?>
						<div class="col-xs-6" id="div_{{$form_field['name']}}">
						<?php if($form_field['type'] === "text" || $form_field['type'] === "email" ||$form_field['type'] === "number" || $form_field['type'] === "password"){ ?>
						<div class="form-group" >
							<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
							<div class="col-xs-8">
								<input {{$form_field['readonly']}} type="{{$form_field['type']}}" id="{{$form_field['name']}}" {{$form_field['required']}} name="{{$form_field['name']}}" class="{{$form_field['class']}}" <?php if(isset($form_field['action'])) { $action = $form_field['action'];  echo $action['type']."=".$action['script']; }?>  <?php if(isset($form_field['value'])) { echo " value='".$form_field['value']."' "; }?>>
							</div>			
						</div>
						<?php } ?>
						<?php if($form_field['type'] === "empty" ){ ?>
						<div class="form-group" >
							<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
							<div class="col-xs-8">
								<label class="control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
							</div>			
						</div>
						<?php } ?>
						<?php if($form_field['type'] === "hidden"){ ?>
						<div class="form-group">
							<div class="col-xs-8">
								<input type="{{$form_field['type']}}" id="{{$form_field['name']}}" name="{{$form_field['name']}}" value="{{$form_field['value']}}" >
							</div>			
						</div>
						<?php } ?>
						<?php if($form_field['type'] === "textarea"){ ?>				
						<div class="form-group">
							<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
							<div class="col-xs-8">
								<textarea {{$form_field['readonly']}} id="{{$form_field['name']}}" name="{{$form_field['name']}}" class="{{$form_field['class']}}"></textarea>
							</div>			
						</div>
						<?php } ?>
						<?php if($form_field['type'] === "radio"){ ?>				
						<div class="form-group">
							<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
							<div class="col-xs-8">
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
							<div class="col-xs-8">
								<select class="{{$form_field['class']}}" {{$form_field['required']}} name="{{$form_field['name']}}" <?php if(isset($form_field['id'])) { echo 'id="'.$form_field['id'].'"'; } else { echo 'id="'.$form_field['name'].'"'; }?>  <?php if(isset($form_field['action'])) { $action = $form_field['action'];  echo $action['type']."=".$action['script']; }?>  <?php if(isset($form_field['multiple'])) { echo " multiple "; }?>>
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
						
						<?php if($form_field['type'] === "file"){ ?>				
						<div class="form-group">
							<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
							<div class="col-xs-8">
								<input type="file" id="{{$form_field['name']}}" name="{{$form_field['name']}}" class="form-control file"/>
							</div>			
						</div>
						<?php } ?>
						
						<?php if($form_field['type'] === "checkbox"){ ?>
							<div class="form-group">
								<label class="col-xs-4 control-label no-padding-right" for="form-field-1"> <?php echo strtoupper($form_field['content']); if($form_field['required']=="required") echo '<span style="color:red;">*</span>'; ?> </label>
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
					</div>							
					
					<?php } ?>
					<div id="addfields"></div>
					<div class="clearfix">
						<div class="col-md-offset-0 col-md-12 form-actions" style="margin: 0px">
							<div class="col-md-offset-4 col-md-5">
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
					</div>
				</form>
				</div>
				</div>
			</div>
		</div>
	