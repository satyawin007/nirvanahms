@extends('masters.master')
	@section('page_css')
		<link rel="stylesheet" href="../assets/css/bootstrap-datepicker3.css"/>
		<link rel="stylesheet" href="../assets/css/chosen.css" />
	@stop
	
	@section('inline_css')
		<style>
			.accordion-style1.panel-group .panel + .panel {
			    margin-top: 10px;
			}
			.chosen-container{
			  width: 100% !important;
			}
		</style>
	@stop

	@section('bredcum')	
		<small>
			ADMINISTRATION
			<i class="ace-icon fa fa-angle-double-right"></i>
			MASTERS
			<i class="ace-icon fa fa-angle-double-right"></i>
			{{strtoupper($form_info['bredcum'])}}
		</small>
	@stop

	@section('page_content')
		<div class="row">
			<div class="col-xs-1"></div>
			<div class="col-xs-10">
					@include("registrations.addlookupform",$form_info)
		</div>
	@stop
	
	@section('page_js')
		<script src="../assets/js/date-time/bootstrap-datepicker.js"></script>
		<script src="../assets/js/bootbox.js"></script>
		<script src="../assets/js/chosen.jquery.js"></script>
	@stop
	
	@section('inline_js')
		<script>
			function changeState(val){
				$.ajax({
			      url: "getcitiesbystateid?id="+val,
			      success: function(data) {
			    	  $("#cityname").html(data);
			    	  $('.chosen-select').trigger("chosen:updated");
			      },
			      type: 'GET'
			   });
			}

			function checkvalidation(val,id,table){
				url = "";
				message ="";
				if(table == "OfficeBranch"){
					stateId = $("#statename").val();
					cityId = $("#cityname").val();
					if(stateId != undefined && stateId ==""){
						alert("Please select state");
						 $("#"+id).val("");
						return false;
					}
					if(cityId != undefined && cityId ==""){
						alert("Please select city");
						 $("#"+id).val("");
						return false;
					}
					
					url = "checkvalidation?table="+table+"&name="+val+"&stateId="+stateId+"&cityId="+cityId;
					message = "This OfficeBranch Name: "+val+" is already existed";
				}
				else if(table == "Vehicle"){
					url = "checkvalidation?table="+table+"&veh_reg="+val;
					message = "This Vehicle No: "+val+" is already existed";
				}
				$.ajax({
				      url: url,
				      success: function(data) {
					      if(data == "exists"){
					    	  bootbox.alert(message, function(result) {});
					    	  $("#"+id).val("");
					      }
				      },
				      type: 'GET'
				   });
			}

			function showPaymentFields(val){
				//alert(val);
				$("#addfields").html('<div style="margin-left:600px; margin-top:100px;"><i class="ace-icon fa fa-spinner fa-spin orange bigger-125" style="font-size: 250% !important;"></i></div>');
				$.ajax({
			      url: "getmasterspaymentfields?paymenttype="+val,
			      success: function(data) {
			    	  $("#addfields1").html(data);
			    	  $('.date-picker').datepicker({
						autoclose: true,
						todayHighlight: true
					  });
			    	  $("#addfields").show();
			      },
			      type: 'GET'
			   });
			}
			

			function changeCity(val){
				$.ajax({
			      url: "getbranchbycityid?id="+val,
			      success: function(data) {
				      alert(data);
			    	  $("#branch").html(data);
			      },
			      type: 'GET'
			   });
			}

			
			//datepicker plugin
			//link
			$('.date').datepicker({
				autoclose: true,
				todayHighlight: true
			})
			//show datepicker when clicking on the icon
			.next().on(ace.click_event, function(){
				$(this).prev().focus();
			});
			
			<?php 
				if(Session::has('message')){
					echo "bootbox.hideAll();";echo "bootbox.alert('".Session::pull('message')."', function(result) {});";
				}
			?>
			

			$("#submit").on("click",function(){
				var statename = $("#statename").val();
				if(statename != undefined && statename ==""){
					alert("Please select statename");
					return false;
				}

				var cityname = $("#cityname").val();
				if(cityname != undefined && cityname ==""){
					alert("Please select cityname");
					return false;
				}

				var paymenttype = $("#paymenttype").val();
				if(paymenttype != undefined && paymenttype ==""){
					alert("Please select paymenttype");
					return false;
				}

				var bankaccount = $("#bankaccount").val();
				var path = $(location).attr('pathname');
				path = path.split("/"); 
				path = path[path.length-1];
				if(bankaccount != undefined && bankaccount =="" && path != "addofficebranch"){
					alert("Please select bankaccount");
					return false;
				}
				
				$("#{{$form_info['name']}}").submit();
			});

			$("#reset").on("click",function(){
				$("#{{$form_info['name']}}").reset();
			});
			
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
			
		</script>
	@stop
