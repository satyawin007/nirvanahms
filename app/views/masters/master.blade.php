<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta charset="utf-8" />
		<title>HOSPITAL MANAGEMENT SYSTEM</title>

		<meta name="description" content="top menu &amp; navigation" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

		<!-- bootstrap & fontawesome -->
		<link rel="stylesheet" href="../assets/css/bootstrap.css" />
		<link rel="stylesheet" href="../assets/css/font-awesome.css" />

		<!-- page specific plugin styles -->
		@yield('page_css')

		<!-- text fonts -->
		<link rel="stylesheet" href="../assets/css/ace-fonts.css" />

		<!-- ace styles -->
		<link rel="stylesheet" href="../assets/css/ace.css" class="ace-main-stylesheet" id="main-ace-style" />

		<!--[if lte IE 9]>
			<link rel="stylesheet" href="../assets/css/ace-part2.css" class="ace-main-stylesheet" />
		<![endif]-->

		<!--[if lte IE 9]>
		  <link rel="stylesheet" href="../assets/css/ace-ie.css" />
		<![endif]-->

		<!-- inline styles related to this page -->

		<!-- ace settings handler -->
		<script src="../assets/js/ace-extra.js"></script>

		<!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->

		<!--[if lte IE 8]>
		<script src="../assets/js/html5shiv.js"></script>
		<script src="../assets/js/respond.js"></script>
		<![endif]-->
		<style>
			body {
			    zoom: 85% !important
			}
			
		</style>
		@yield('inline_css')
		
	</head>

	<body class="no-skin">
		<!-- #section:basics/navbar.layout -->
		<div id="navbar" class="navbar navbar-default    navbar-collapse       h-navbar">
			<script type="text/javascript">
				try{ace.settings.check('navbar' , 'fixed')}catch(e){}
			</script>
			

			<div class="navbar-container" id="navbar-container">
				<div class="navbar-header pull-left">
					<!-- #section:basics/navbar.layout.brand -->
					<a href="#" class="navbar-brand">
						<?php 
							$banner_type = Session::get("banner_type");
							if($banner_type=="title"){ 
								$title = Session::get("title");
								echo '<small>'.'<i class="fa fa-lightbulb-o"></i>&nbsp;'.$title.'</small>';
							}
							else {
								$banner = Session::get("banner");
								echo "<img style='width:100%; max-height:26px;' src='../app/storage/uploads/".$banner."' />";
							}
						?>
					</a>

							
						
					<!-- /section:basics/navbar.layout.brand -->

					<!-- #section:basics/navbar.toggle -->
					<button class="pull-right navbar-toggle navbar-toggle-img collapsed" type="button" data-toggle="collapse" data-target=".navbar-buttons,.navbar-menu">
						<span class="sr-only">Toggle user menu</span>

						<img src="../assets/avatars/user.jpg" alt="Jason's Photo" />
					</button>

					<button class="pull-right navbar-toggle collapsed" type="button" data-toggle="collapse" data-target="#sidebar">
						<span class="sr-only">Toggle sidebar</span>

						<span class="icon-bar"></span>

						<span class="icon-bar"></span>

						<span class="icon-bar"></span>
					</button>

					<!-- /section:basics/navbar.toggle -->
				</div>

				<!-- #section:basics/navbar.dropdown -->
				<div class="navbar-buttons navbar-header pull-right  collapse navbar-collapse" role="navigation">
					<ul class="nav ace-nav">
						<li class="transparent"><span style="font-weight: bolder; color: white; margin-right: 10px;"><i class="fa fa-calendar bigger-110"></i> {{date("d-m-Y")}}</span><span style="font-weight: bolder; color: white; margin-right: 10px;"><i class="fa fa-clock-o bigger-110"></i> <span id="time">10:23:00</span></span></li>
						<li class="transparent">
						<?php $jobs = Session::get("jobs");?>
							<?php if(in_array(10, $jobs)) {?>	
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-bell icon-animated-bell"></i>
							</a>
										
							<div class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<div class="tabbable">
									<ul class="nav nav-tabs">
										<li class="active">
											<a data-toggle="tab" href="#navbar-tasks">
												Alerts
												<span class="badge badge-danger">{{$total_alerts}}</span>
											</a>
										</li>
									</ul><!-- .nav-tabs -->

									<div class="tab-content">
										<div id="navbar-tasks" class="tab-pane in active">
											<ul class="dropdown-menu-right dropdown-navbar dropdown-menu">
												<li class="dropdown-content">
													<ul class="dropdown-menu dropdown-navbar">
														<li>
															<a href="showalerts">
																<div class="clearfix">
																	<span style="font-size:13px; font-weight: bold;" class="pull-left">Vehicle Tire Change Alerts : </span><br/>
																	<div style="padding: 5px; color: red; font-weight: bold;">
																	{{$tires_alert_data}}
																	</div>
																</div>
															</a>
															<a href="showempincreamentalerts">
																<div class="clearfix">
																	<span style="font-size:13px; font-weight: bold;" class="pull-left">Employee Increament Alerts : </span><br/>
																	<div style="padding: 5px; color: red; font-weight: bold;">
																	{{$count}}
																	</div>
																</div>
																

																<div class="progress progress-mini">
																	<div style="width:65%" class="progress-bar"></div>
																</div>
															</a>
														</li>
													</ul>
												</li>
											</ul>
										</div><!-- /.tab-pane -->
									</div><!-- /.tab-content -->
								</div><!-- /.tabbable -->
							</div>
							<?php }?><!-- /.dropdown-menu -->
						</li>

						<!-- #section:basics/navbar.user_menu -->
						<li class="light-blue user-min">
							<a data-toggle="dropdown" href="#" class="dropdown-toggle">
								<?php $filename = "../app/storage/uploads/".Auth::user()->filePath;?>
								<img class="nav-user-photo" src="{{$filename}}" alt="Jason's Photo" />
								<span class="user-info">
									<small>Welcome,</small>
									{{Auth::user()->fullName}}
								</span>

								<i class="ace-icon fa fa-caret-down"></i>
							</a>

							<ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
								<li>
									<a href="profile">
										<i class="ace-icon fa fa-cog"></i>
										Settings
									</a>
								</li>

								<li>
									<a href="profile">
										<i class="ace-icon fa fa-user"></i>
										Profile
									</a>
								</li>

								<li class="divider"></li>

								<li>
									<a href="logout">
										<i class="ace-icon fa fa-power-off"></i>
										Logout
									</a>
								</li>
							</ul>
						</li>

						<!-- /section:basics/navbar.user_menu -->
					</ul>
				</div>
			</div><!-- /.navbar-container -->
		</div>

		<!-- /section:basics/navbar.layout -->
		<div class="main-container" id="main-container">
			<script type="text/javascript">
				try{ace.settings.check('main-container' , 'fixed')}catch(e){}
			</script>

			<!-- #section:basics/sidebar.horizontal -->
			<div id="sidebar" class="sidebar      h-sidebar                navbar-collapse collapse">
				<script type="text/javascript">
					try{ace.settings.check('sidebar' , 'fixed')}catch(e){}
				</script>

				<div class="sidebar-shortcuts" id="sidebar-shortcuts">
					<div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
						<button class="btn btn-success">
							<i class="ace-icon fa fa-signal"></i>
						</button>

						<button class="btn btn-info">
							<i class="ace-icon fa fa-pencil"></i>
						</button>

						<!-- #section:basics/sidebar.layout.shortcuts -->
						<button class="btn btn-warning">
							<i class="ace-icon fa fa-users"></i>
						</button>

						<button class="btn btn-danger">
							<i class="ace-icon fa fa-cogs"></i>
						</button>

						<!-- /section:basics/sidebar.layout.shortcuts -->
					</div>

					<div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini">
						<span class="btn btn-success"></span>

						<span class="btn btn-info"></span>

						<span class="btn btn-warning"></span>

						<span class="btn btn-danger"></span>
					</div>
				</div><!-- /.sidebar-shortcuts -->

				<ul class="nav nav-list">
					<li class="hover">
						<a href="dashboard">
							<i class="menu-icon fa fa-tachometer"></i>
							<span class="menu-text"> DASHBOARD </span>
						</a>
						<b class="arrow"></b>
					</li>
					
					<li class=" hover">
						<?php $jobs = Session::get("jobs");?>
						<?php if(in_array(1, $jobs)){?>
						<a href="#" class="dropdown-toggle">
							<i class="menu-icon fa fa-desktop"></i>
							<span class="menu-text">
								ADMINISTRATION
							</span>

							<b class="arrow fa fa-angle-down"></b>
						</a>
						<?php }?>

						<b class="arrow"></b>

						<ul class="submenu">
							<?php if(in_array(101, $jobs)){?>
							<li class="hover">
								<a href="masters">
									<i class="menu-icon fa fa-caret-right"></i>
									MASTERS
								</a>
								<b class="arrow"></b>
							</li>
							<?php } if(in_array(104, $jobs)){?>								
							<li class="hover">
								<a href="roles">
									<i class="menu-icon fa fa-caret-right"></i>
									MANAGE PREVILAGES
								</a>
								<b class="arrow"></b>
							</li>
							<?php }?>
						</ul>
					</li>
					
					<li class="hover">
						<?php if(in_array(2, $jobs)){?>
						<a href="#" class="dropdown-toggle">
							<i class="menu-icon fa fa-pencil-square-o"></i>
							<span class="menu-text">
								REGISTRATIONS
							</span>
							<b class="arrow fa fa-angle-down"></b>
						</a>
						<?php }?>
						<b class="arrow"></b>
						<ul class="submenu">
							<?php if(in_array(105, $jobs)){?>
							<li class="hover">
								<a href="register">
									<i class="menu-icon fa fa-caret-right"></i>
									REGISTRATION
								</a>
							</li>
							<?php } if(in_array(106, $jobs)){?>
							<li class="hover">
								<a href="inpatients">
									<i class="menu-icon fa fa-caret-right"></i>
									ADMISSIONS
								</a>
							</li>
							<?php } ?>
							
						</ul>
					</li>
					
					<li class="hover">
						<?php if(in_array(2, $jobs)){?>
						<a href="#" class="dropdown-toggle">
							<i class="menu-icon fa fa-print"></i>
							<span class="menu-text">
								BILLING
							</span>
							<b class="arrow fa fa-angle-down"></b>
						</a>
						<?php }?>
						<b class="arrow"></b>
						<ul class="submenu">
							<?php if(in_array(105, $jobs)){?>
							<li class="hover">
								<a href="billing?type=diagnostics">
									<i class="menu-icon fa fa-caret-right"></i>
									DIAGNOSTICS
								</a>
							</li>
							<?php } if(in_array(106, $jobs)){?>
							<li class="hover">
								<a href="billing?type=pharmacy">
									<i class="menu-icon fa fa-caret-right"></i>
									PHARMACY
								</a>
							</li>
							<?php } if(in_array(106, $jobs)){?>
							<li class="hover">
								<a href="outpatients">
									<i class="menu-icon fa fa-caret-right"></i>
									IP BILLING
								</a>
							</li>
							<?php } ?>
							
						</ul>
					</li>
					
					<?php if(in_array(6, $jobs)){?>	
					<li class="open hover">
						<a href="#" class="dropdown-toggle">
							<i class="menu-icon fa fa-list-alt"></i>
							<span class="menu-text">
								PHARMACY &nbsp;
							</span>
							<b class="arrow fa fa-angle-down"></b>
						</a>
						<b class="arrow"></b>
						<ul class="submenu">
							<?php if(in_array(115, $jobs)){?>
							<li class="hover">
								<a href="inventorylookupvalues">
									<i class="menu-icon fa fa-caret-right"></i>
									LOOKUP VALUES
								</a>
							</li>
							<?php } if(in_array(116, $jobs)){?>
							<li class="hover">
								<a href="manufacturers">
									<i class="menu-icon fa fa-caret-right"></i>
									MANUFACTURERS
								</a>
							</li>
							<?php //} if(in_array(117, $jobs)){?>
							<!-- 
							<li class="hover">
								<a href="itemcategories">
									<i class="menu-icon fa fa-caret-right"></i>
									ITEM NAMES
								</a>
							</li>
							 -->
							<?php } if(in_array(118, $jobs)){?>
							<li class="hover">
								<a href="itemtypes">
									<i class="menu-icon fa fa-caret-right"></i>
									DRUG TYPES
								</a>
							</li>
							<?php } if(in_array(119, $jobs)){?>
							<li class="hover">
								<a href="items">
									<i class="menu-icon fa fa-caret-right"></i>
									DRUGS
								</a>
							</li>
							<?php } if(in_array(120, $jobs)){?>
							<li class="hover">
								<a href="purchaseorder">
									<i class="menu-icon fa fa-caret-right"></i>
									PURCHASE ORDERS
								</a>
							</li>
							<?php }?>
						</ul>
					</li>
					<?php } ?>
					
					
					
					<li class="hover">
						<?php if(in_array(7, $jobs)){?>	
						<a href="reports">
							<i class="menu-icon  fa fa-bar-chart-o"></i>
							<span class="menu-text"> REPORTS </span>
						</a>
						<?php } ?>
						<b class="arrow"></b>
					</li>
					
					<li class="hover">
						<?php if(in_array(8, $jobs)){?>	
						<a href="settings">
							<i class="menu-icon fa fa-cog"></i>
							<span class="menu-text"> SETTINGS </span>
						</a>
						<?php } ?>
						<b class="arrow"></b>
					</li>
				</ul><!-- /.nav-list -->

				<!-- #section:basics/sidebar.layout.minimize -->

				<!-- /section:basics/sidebar.layout.minimize -->
				<script type="text/javascript">
					try{ace.settings.check('sidebar' , 'collapsed')}catch(e){}
				</script>
			</div>

			<!-- /section:basics/sidebar.horizontal -->
			<div class="main-content">
				<div class="main-content-inner">
					<div class="page-content">
						<!-- #section:settings.box -->
						<!-- /section:settings.box -->
						<div class="page-header">
							<h1>
								@yield('bredcum')
							</h1>
						</div><!-- /.page-header -->

						<div class="row">							
								<!-- PAGE CONTENT BEGINS -->
								@yield('page_content')
								<!-- PAGE CONTENT ENDS -->
							</div><!-- /.col -->
						</div><!-- /.row -->
					</div><!-- /.page-content -->
				</div>
			</div><!-- /.main-content -->

		<!-- basic scripts -->

		<!--[if !IE]> -->
		<script type="text/javascript">
			window.jQuery || document.write("<script src='../assets/js/jquery.js'>"+"<"+"/script>");
		</script>

		<!-- <![endif]-->

		<!--[if IE]>
<script type="text/javascript">
 window.jQuery || document.write("<script src='../assets/js/jquery1x.js'>"+"<"+"/script>");
</script>
<![endif]-->
		<script type="text/javascript">
			if('ontouchstart' in document.documentElement) document.write("<script src='../assets/js/jquery.mobile.custom.js'>"+"<"+"/script>");
		</script>
		<script src="../assets/js/bootstrap.js"></script>

		<!-- page specific plugin scripts -->

		<!-- ace scripts -->
		<script src="../assets/js/ace/elements.scroller.js"></script>
		<script src="../assets/js/ace/elements.colorpicker.js"></script>
		<script src="../assets/js/ace/elements.fileinput.js"></script>
		<script src="../assets/js/ace/elements.typeahead.js"></script>
		<script src="../assets/js/ace/elements.wysiwyg.js"></script>
		<script src="../assets/js/ace/elements.spinner.js"></script>
		<script src="../assets/js/ace/elements.treeview.js"></script>
		<script src="../assets/js/ace/elements.wizard.js"></script>
		<script src="../assets/js/ace/elements.aside.js"></script>
		<script src="../assets/js/ace/ace.js"></script>
		<script src="../assets/js/ace/ace.ajax-content.js"></script>
		<script src="../assets/js/ace/ace.touch-drag.js"></script>
		<script src="../assets/js/ace/ace.sidebar.js"></script>
		<script src="../assets/js/ace/ace.sidebar-scroll-1.js"></script>
		<script src="../assets/js/ace/ace.submenu-hover.js"></script>
		<script src="../assets/js/ace/ace.widget-box.js"></script>
		<script src="../assets/js/ace/ace.settings.js"></script>
		<script src="../assets/js/ace/ace.settings-rtl.js"></script>
		<script src="../assets/js/ace/ace.settings-skin.js"></script>
		<script src="../assets/js/ace/ace.widget-on-reload.js"></script>
		<script src="../assets/js/ace/ace.searchbox-autocomplete.js"></script>
		
		@yield('page_js')

		<!-- inline scripts related to this page -->
		<script type="text/javascript">
			function updateTime() {
				var date = new Date();
				hrs = date.getHours();
				mins =date.getMinutes();
				secs = date.getSeconds();
				if(date.getHours()<10){
					hrs = "0"+date.getHours();
				}
				if(date.getMinutes()<10){
					mins = "0"+date.getMinutes();
				}
				if(date.getSeconds()<10){
					secs = "0"+date.getSeconds();
				}
			    $('#time').html(
			    		hrs + ":" + mins + ":" +secs
		        );
			}
	
			setInterval(updateTime, 1000); // 5 * 1000 miliseconds
		
			jQuery(function($) {
			 var $sidebar = $('.sidebar').eq(0);
			 if( !$sidebar.hasClass('h-sidebar') ) return;
			
			 $(document).on('settings.ace.top_menu' , function(ev, event_name, fixed) {
				if( event_name !== 'sidebar_fixed' ) return;
			
				var sidebar = $sidebar.get(0);
				var $window = $(window);
			
				//return if sidebar is not fixed or in mobile view mode
				var sidebar_vars = $sidebar.ace_sidebar('vars');
				if( !fixed || ( sidebar_vars['mobile_view'] || sidebar_vars['collapsible'] ) ) {
					$sidebar.removeClass('lower-highlight');
					//restore original, default marginTop
					sidebar.style.marginTop = '';
			
					$window.off('scroll.ace.top_menu')
					return;
				}
			
			
				 var done = false;
				 $window.on('scroll.ace.top_menu', function(e) {
			
					var scroll = $window.scrollTop();
					scroll = parseInt(scroll / 4);//move the menu up 1px for every 4px of document scrolling
					if (scroll > 17) scroll = 17;
			
			
					if (scroll > 16) {			
						if(!done) {
							$sidebar.addClass('lower-highlight');
							done = true;
						}
					}
					else {
						if(done) {
							$sidebar.removeClass('lower-highlight');
							done = false;
						}
					}
			
					sidebar.style['marginTop'] = (17-scroll)+'px';
				 }).triggerHandler('scroll.ace.top_menu');
			
			 }).triggerHandler('settings.ace.top_menu', ['sidebar_fixed' , $sidebar.hasClass('sidebar-fixed')]);
			
			 $(window).on('resize.ace.top_menu', function() {
				$(document).triggerHandler('settings.ace.top_menu', ['sidebar_fixed' , $sidebar.hasClass('sidebar-fixed')]);
			 });
			
			
			});
		</script>		
		@yield('inline_js')

		<!-- the following scripts are used in demo only for onpage help and you don't need them -->
		<link rel="stylesheet" href="../assets/css/ace.onpage-help.css" />
		<link rel="stylesheet" href="../docs/assets/js/themes/sunburst.css" />

		<script type="text/javascript"> ace.vars['base'] = '..'; </script>
		<script src="../assets/js/ace/elements.onpage-help.js"></script>
		<script src="../assets/js/ace/ace.onpage-help.js"></script>
		<script src="../docs/assets/js/rainbow.js"></script>
		<script src="../docs/assets/js/language/generic.js"></script>
		<script src="../docs/assets/js/language/html.js"></script>
		<script src="../docs/assets/js/language/css.js"></script>
		<script src="../docs/assets/js/language/javascript.js"></script>
	</body>
</html>
