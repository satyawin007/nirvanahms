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
			th, td {
				white-space: nowrap;
			}
			.chosen-container{
				width: 100% !important;
			}
		</style>
	@stop
	
	@section('page_css')
		<link rel="stylesheet" href="../assets/css/bootstrap-datepicker3.css"/>
		<link rel="stylesheet" href="../assets/css/chosen.css" />
		<link rel="stylesheet" href="../assets/css/daterangepicker.css" />
	@stop
	
	@section('bredcum')	
		<small>
			FORMS
			<i class="ace-icon fa fa-angle-double-right"></i>
			{{$values['bredcum']}}
		</small>
	@stop

	@section('page_content')
		<div class="row ">
			<div class="col-xs-offset-1 col-xs-10">
				<?php $form_info = $values["form_info"]; $form_info["showbutton"]="logininfo";?>
				@include("masters.layouts.addlookupform",$form_info)
			</div>
		</div>
				
		<div class="row ">
		<div class="col-xs-offset-1 col-xs-10">
			<h3 class="header smaller lighter blue" style="font-size: 15px; font-weight: bold;margin-bottom: 10px;">MANAGE {{$values["bredcum"]}}</h3>		
			<?php if(!isset($values['entries'])) $values['entries']=10; if(!isset($values['branch'])) $values['branch']=0; if(!isset($values['page'])) $values['page']=1; ?>
			<div class="clearfix">
				<div class="pull-left">
					
					<form action="{{$values['form_action']}}" name="paginate" id="paginate">
					<?php 
					if(isset($values['selects'])){
						$selects = $values['selects'];
						foreach($selects as $select){
						?>
						<label>{{ strtoupper($select["name"]) }}</label>
						<select class="form-control-inline" id="{{$select['name']}}" style="height: 33px; padding-top: 0px;" name="{{$select["name"]}}" onChage="paginate(1)">
							<?php 
								foreach($select["options"] as $key => $value){									
									$option =  "<option value='".$key."' ";
									if($key == $values[$select['name']]){
										$option = $option." selected='selected' ";
									}
									$option = $option.">".$value."</option>";
									echo $option;
								}
							?>
						</select> &nbsp; &nbsp;
					<?php }} ?>
					<input type="hidden" name="page" id="page" /> 
					<?php 
					if(isset($values['links'])){
						$links = $values['links'];
						foreach($links as $link){
							echo "<a class='btn btn-white btn-success' href=".$link['url'].">".$link['name']."</a> &nbsp; &nbsp; &nbsp";
						}
					}
					?>
					<?php echo "<input type='hidden' name='action' value='".$values['action_val']."'/>"; ?>					
					</form>
				</div>
				<div class="pull-right tableTools-container"></div>
			</div>
			<div class="table-header" style="margin-top: 10px;">
				Results for "{{$values['bredcum']}}"				 
				<div style="float:right;padding-right: 15px;padding-top: 6px;"><a style="color: white;" href="{{$values['home_url']}}"><i class="ace-icon fa fa-home bigger-200"></i></a> </div>				
			</div>
			<!-- div.table-responsive -->
			<!-- div.dataTables_borderWrap -->
			<div>
				<table id="dynamic-table" class="table table-striped table-bordered table-hover">
					<thead>
						<tr>
							<?php 
								$theads = $values['theads'];
								foreach($theads as $thead){
									echo "<th>".strtoupper($thead)."</th>";
								}
							?>
						</tr>
					</thead>
				</table>								
			</div>
		</div>
		</div>

		<?php 
			if(isset($values['modals'])) {
				$modals = $values['modals'];
				foreach ($modals as $modal){
		?>
				@include('masters.layouts.modalform', $modal);
		<?php }} ?>
		
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
		<script src="../assets/js/bootbox.js"></script>
		<script src="../assets/js/chosen.jquery.js"></script>
		<script src="../assets/js/jquery.maskedinput.js"></script>
		<script src="../assets/js/date-time/moment.js"></script>
		<script src="../assets/js/date-time/daterangepicker.js"></script> 
	@stop
	
	@section('inline_js')
		<!-- inline scripts related to this page -->
		<script type="text/javascript">
			$('.input-daterange').datepicker({autoclose:true,todayHighlight: true});
				
			$('#policestation2').attr('disabled',true);
			$('#circle2').attr('disabled',true);
			$('#subdivision2').attr('disabled',true);
		
			function getCaseTypes(courtid){
				$.ajax({
			      url: "getcaseinfo?type=casetypenos&id="+courtid,
			      success: function(data) {
			    	  $("#casetypeno").html(data);
			    	  $('.chosen-select').trigger('chosen:updated');
			      },
			      type: 'GET'
			   });				
			}

			function getCourts(policestationid){
				$.ajax({
			      url: "getCourtsinfo?psid="+policestationid,
			      success: function(data) {
			    	  $("#court").html(data);
			    	  $('.chosen-select').trigger('chosen:updated');
			      },
			      type: 'GET'
			   });		
				$.ajax({
				      url: "getCrimeinfo?psid="+policestationid,
				      success: function(data) {
				    	  $("#crimeno").html(data);
				    	  $('.chosen-select').trigger('chosen:updated');
				  },
				  type: 'GET'
			   });				
			}

			function getCircles(subdivisionid){
				$.ajax({
			      url: "getCirclesinfo?subdivisionid="+subdivisionid,
			      success: function(data) {
			    	  $("#circle").html(data);
			    	  $('.chosen-select').trigger('chosen:updated');
			      },
			      type: 'GET'
			   });				
			}

			function getPs(circleid){
				$.ajax({
			      url: "getPsinfo?circleid="+circleid,
			      success: function(data) {
			    	  $("#policestation").html(data);
			    	  $('.chosen-select').trigger('chosen:updated');
			      },
			      type: 'GET'
			   });				
			}

			$('input[type=radio][name=range]').change(function() {
		        if (this.value == 'POLICE_STATION') {
					$('#circle2').attr('disabled',true);
					$('#subdivision2').attr('disabled',true);
		            $('#policestation2').attr('disabled',false);
		            $('.chosen-select').trigger('chosen:updated');
		            
		        }
		        else if (this.value == 'CIRCLE') {
		        	$('#circle2').attr('disabled',false);
					$('#subdivision2').attr('disabled',true);
		            $('#policestation2').attr('disabled',true);
		            $('.chosen-select').trigger('chosen:updated');
		            
		        }
		        else if (this.value == 'SUB_DIVISION') {
		        	$('#circle2').attr('disabled',true);
					$('#subdivision2').attr('disabled',false);
		            $('#policestation2').attr('disabled',true);
		            $('.chosen-select').trigger('chosen:updated');
		        }
		    });


			function getAccuses(typeid){
				$.ajax({
			      url: "getcaseinfo?type=accuses&id="+typeid,
			      success: function(data) {
			    	  $("#accusedname").html(data);
			    	  $('.chosen-select').trigger('chosen:updated');
			      },
			      type: 'GET'
			   });				
			}

			function modalEditPoliceStation(id, name, circle, additional_information, status){
				$("#name1").val(name);
				$("#id1").val(id);
				//$("#circle1").val(circle);
				$("#circle1 option").each(function() { this.selected = (this.text == circle); });
				$("#additionalinformation1").text(additional_information);
				$("#status1 option").each(function() { this.selected = (this.text == status); });
				return;				
			}

			function modalEditcourts(id, name, additionalinformation1, status){
				$("#name1").val(name);
				$("#id1").val(id);
				$("#additionalinformation1").val(additionalinformation1);
				$("#status1 option").each(function() { this.selected = (this.text == status); });
				return;				
			}

			function modalEditCourtDisposal(id, policestationname, courtId, caseTypeNoId, firNo, sectionOfLaw, judgement){
				$("#policestation1").val(policestationname);
				$("#court1").val(courtId);
				$("#cc1").val(caseTypeNoId);
				$("#crno1").val(firNo);
				$("#secoflaw1").val(sectionOfLaw);
				$("#judgement1").val(judgement);
				$("#id1").val(id);
				$("#status1 option").each(function() { this.selected = (this.text == status); });
				return;				
			}

			function modalEditCircle(id, name, sub_division, additional_information, status){
				$("#name1").val(name);
				$("#id1").val(id);
				$("#subdivision1 option").each(function() { this.selected = (this.text == sub_division); });
				$("#additionalinformation1").text(additional_information);
				$("#status1 option").each(function() { this.selected = (this.text == status); });
				return;				
			}

			function modalEditSubDivision(id, name, additional_information, status){
				$("#name1").val(name);
				$("#id1").val(id);
				$("#additionalinformation1").val(additional_information);
				$("#status1 option").each(function() { this.selected = (this.text == status); });
				return;				
			}

			function modalEditStatementsDetails(id, policestationname, courtId, date, caseTypeNoId, sno, accusedId, statement, dateOfIssue){
				$("#policestation1").val(policestationname);
				$("#name1").val(courtId);
				$("#cc1").val(caseTypeNoId);
				$("#date1").val(date);
				$("#sno1").val(sno);
				$("#nameofaccused1").val(accusedId);
				$("#statement1").val(statement);
				$("#dateofissue1").val(dateOfIssue);
				$("#id1").val(id);
				$("#status1 option").each(function() { this.selected = (this.text == status); });
				return;				
			}

			function modalEditServiceProvider(id, branchId, provider, name, number,companyName, configDetails, address, refName,refNumber, status){
				$("#provider1 option").each(function() { this.selected = (this.text == provider); });
				$("#branch1 option").each(function() { this.selected = (this.text == branchId); });
				$("#name1").val(name);				
				$("#number1").val(number);
				$("#companyname1").val(companyName);
				$("#configdetails1").val(configDetails);
				$("#address1").val(address);
				$("#referencename1").val(refName);
				$("#referencenumber1").val(refNumber);
				$("#status1 option").each(function() { this.selected = (this.text == status); });
				$("#id1").val(id);		
			}

			function modalEditState(id, name, code, status){
				$("#statename1").val(name);				
				$("#statecode1").val(code);
				$("#status1 option").each(function() { this.selected = (this.text == status); });
				$("#id1").val(id);		
			}

			function modalEditCity(id, name, code, state, status){
				$("#cityname1").val(name);				
				$("#citycode1").val(code);
				$("#statename1 option").each(function() {this.selected = (this.text == state); });
				$("#status1 option").each(function() { this.selected = (this.text == status); });
				$("#id1").val(id);		
			}

			function modalEditUsers(id,name,emailId,policestationIds,role,status){
				//alert("This is test"+policestationIds);
				$("#policestations1").each(function() {
					var policestationIds_arr = policestationIds.split(",");
					this.selected= true;
					                for(i=0;i<policestationIds_arr.length;i++)
					                {
									 	this.selected = (this.text == policestationIds_arr[i]); 
					                }
				});
				$("#id1").val(id);
				$("#name1").val(name);
				$("#email1").val(emailId);
				$("#designation1").val(role);
				$("#status1").val(status);						
			}
			

			$("#reset").on("click",function(){
				$("#{{$form_info['name']}}").reset();
			});

			$("#submit").on("click",function(){
				$("#{{$form_info['name']}}").submit();
			});

			$("#type").on("change",function(){
				if(this.value != ""){
					window.location.replace('lookupvalues?type='+this.value);
				}
			});

			$("#provider").on("change",function(){
				val = $("#provider option:selected").html();
				window.location.replace('serviceproviders?provider='+val);
			});

			function changeState(val){
				$.ajax({
			      url: "getcitiesbystateid?id="+val,
			      success: function(data) {
			    	  $("#cityname").html(data);
			      },
			      type: 'GET'
			   });
			}
			<?php 
				if(Session::has('message')){
					echo "bootbox.confirm('".Session::pull('message')."', function(result) {});";
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
				autoclose: true,
				todayHighlight: true
			})
			//show datepicker when clicking on the icon
			.next().on(ace.click_event, function(){
				$(this).prev().focus();
			});

			$('.input-mask-phone').mask('(999) 999-9999');
			

			jQuery(function($) {		
				//initiate dataTables plugin
				var myTable = 
				$('#dynamic-table')
				//.wrap("<div class='dataTables_borderWrap' />")   //if you are applying horizontal scrolling (sScrollX)

				//.wrap("<div id='tableData' style='width:300px; overflow: auto;overflow-y: hidden;-ms-overflow-y: hidden; position:relative; margin-right:5px; padding-bottom: 15px;display:block;'/>"); 
		
				.DataTable( {
					bJQueryUI: true,
					"bPaginate": true,
					bInfo: true,
					"aoColumns": [
					  <?php $cnt=count($values["theads"]); for($i=0; $i<$cnt; $i++){ echo '{ "bSortable": false },'; }?>
					],
					"aaSorting": [],
					oLanguage: {
				        sProcessing: '<i class="ace-icon fa fa-spinner fa-spin orange bigger-250"></i>'
				    },
					"bProcessing": true,
			        "bServerSide": true,
					"ajax":{
		                url :"getdatatabledata?name=<?php echo $values["provider"] ?>", // json datasource
		                type: "post",  // method  , by default get
		                error: function(){  // error handling
		                    $(".employee-grid-error").html("");
		                    $("#dynamic-table").append('<tbody class="employee-grid-error"><tr>No data found in the server</tr></tbody>');
		                    $("#employee-grid_processing").css("display","none");
		 
		                }
		            },
			
					//"sScrollY": "500px",
					//"bPaginate": false,
					"sScrollX" : "true",
					//"sScrollX": "300px",
					//"sScrollXInner": "120%",
					"bScrollCollapse": true,
					//Note: if you are applying horizontal scrolling (sScrollX) on a ".table-bordered"
					//you may want to wrap the table inside a "div.dataTables_borderWrap" element
			
					//"iDisplayLength": 50
			
			
					select: {
						style: 'multi'
					}
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
				
				
				
				
				
				myTable.on( 'select', function ( e, dt, type, index ) {
					if ( type === 'row' ) {
						$( myTable.row( index ).node() ).find('input:checkbox').prop('checked', true);
					}
				} );
				myTable.on( 'deselect', function ( e, dt, type, index ) {
					if ( type === 'row' ) {
						$( myTable.row( index ).node() ).find('input:checkbox').prop('checked', false);
					}
				} );
			
			
			
			
				/////////////////////////////////
				//table checkboxes
				$('th input[type=checkbox], td input[type=checkbox]').prop('checked', false);
				
				//select/deselect all rows according to table header checkbox
				$('#dynamic-table > thead > tr > th input[type=checkbox], #dynamic-table_wrapper input[type=checkbox]').eq(0).on('click', function(){
					var th_checked = this.checked;//checkbox inside "TH" table header
					
					$('#dynamic-table').find('tbody > tr').each(function(){
						var row = this;
						if(th_checked) myTable.row(row).select();
						else  myTable.row(row).deselect();
					});
				});
				
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
				$('<button style="margin-top:-5px;" class="btn btn-minier btn-primary" id="refresh"><i style="margin-top:-2px; padding:6px; padding-right:5px;" class="ace-icon fa fa-refresh bigger-110"></i></button>').appendTo('div.dataTables_filter');
				$("#refresh").on("click",function(){ myTable.search( '', true ).draw(); });
			});
			
		</script>
	@stop