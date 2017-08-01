@extends('masters.master')
	@section('inline_css')
		<style>
			.page-header h1 {
				padding: 0;
				margin: 0 3px;
				font-size: 12px;
				font-weight: lighter;
				color: #2679b5;
			}
			
			button, input, optgroup, select, textarea {
				color: inherit;
				font: inherit;
				margin: 10px;
				padding : 10px;
			}
			a{
				text-decoration:none;
			}
		</style>
	@stop

	@section('bredcum')	
		<small>
			ADMINISTRATION
			<i class="ace-icon fa fa-angle-double-right"></i>
			MASTERS
		</small>
	@stop

	@section('page_content')
		<div class="col-xs-12 center">
			<div class="row" style="margin-top: 20px;">
				<?php $jobs = Session::get("jobs");?>
				<?php if(in_array(151, $jobs)){?>
				<a href="employees">
				<button >
					<i class="ace-icon fa fa-user bigger-300"></i><BR/>
					&nbsp; &nbsp; &nbsp;EMPLOYEES&nbsp; &nbsp; &nbsp;
				</button>
				</a>
				<?php } if(in_array(152, $jobs)){?>
				<a href="departments">
				<button >
					<i class="ace-icon fa fa-globe bigger-300"></i><BR/>
					 &nbsp; &nbsp; &nbsp; DEPARTMENTS &nbsp; &nbsp; &nbsp; 
				</button>
				</a>
				<?php } if(in_array(157, $jobs)){?>
				<a href="doctors">
				<button >
					<i class="ace-icon fa fa-globe bigger-300"></i><BR/>
					&nbsp; &nbsp; &nbsp; DOCTORS &nbsp; &nbsp; &nbsp; 
				</button>									
				</a>
				<?php } if(in_array(153, $jobs)){?>
				<a href="manufacturers">
				<button>
					<i class="ace-icon fa fa-tag bigger-300"></i><BR/>
					&nbsp; &nbsp; &nbsp;  MANAFACTURARES &nbsp; &nbsp; &nbsp; 
				</button>
				</a>
				<?php } if(in_array(154, $jobs)){?>
				<a href="medicines">
				<button>
					<i class="ace-icon fa fa-map-marker bigger-300"></i><BR/>
					&nbsp; &nbsp; &nbsp;  MEDICINES &nbsp; &nbsp; &nbsp; 
				</button>
				</a>
				<?php } if(in_array(160, $jobs)){?>
				<a href="lookupvalues">
				<button>
					<i class="ace-icon fa fa-search bigger-300"></i><BR/>
					&nbsp; &nbsp; LOOKUP DATA &nbsp; &nbsp; 
				</button>
				</a>
				<?php }?>
			</div>
		</div>
	@stop