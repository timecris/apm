	<head>
	    <HTA:APPLICATION ID="oMyApp" 
	     APPLICATIONNAME="Application Executer" 
		 BORDER="no"
	     CAPTION="no"
	     SHOWINTASKBAR="yes"
	     SINGLEINSTANCE="yes"
	     SYSMENU="yes"
	     SCROLL="no"
	     WINDOWSTATE="normal">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>APM</title>

		<script type="text/javascript" src="./js/jquery-1.11.3.min.js"></script> 
		<style type="text/css">
		${demo.css}
		</style>

		<link rel="stylesheet" href="//s1.daumcdn.net/svc/attach/U0301/cssjs/tistory-web-blog/1430179976/static/css/mobile/T.m.blog.css">
		<link rel="stylesheet" href="//s1.daumcdn.net/svc/attach/U0301/cssjs/tistory-web-blog/1430179976/static/css/mobile/skin/skin_06/style_h.css">
		<style type="text/css">
		#daumHead {
		 background-image: url("http://cfile23.uf.tistory.com/R320x0/1141713E4D6B41BD225974");
		 background-size: 320px auto;
		 -moz-background-size: 320px auto;
		 -webkit-background-size: 320px auto;
		}

		#daumHead { background-color: #222d49 !important; }
		#daumHead h1 a { color: #bec8e4; }
		#header { height: 52.666666666666664px; }
		#header h1 a { line-height: 52.666666666666664px; }
		#menu .blog_menu { background: transparent !important; }
		 .em_color { color: #667aaf !important; }
		 .em_color_bg { background: none !important; background-color: #667aaf !important; }
		 .em_color_bd { border-color: #667aaf !important; }
		 </style>
	</head>

<div id="daumHead" class="has_sub">
 <div id="header">
  <h1><a href="./"><font size="5">Application Performance Management</font></a></h1>
 </div>
 <div id="menu">
  <ul class="blog_menu menu_count_4">
  <li class="<?php if (preg_match('/index.php*/', $uri)) echo 'on'?>">
   <a class="fst" href="index.php"><span class="menu_label">Result</span></a>
  </li>
  <li class="<?php if (preg_match('/comparison.php*/', $uri)) echo 'on'?>">
   <a href="comparison.php"><span class="menu_label">Comparison Result</span></a>
  </li>
  <li class="<?php if (preg_match('/realtime.php*/', $uri)) echo 'on'?>">
   <a href="realtime.php"><span class="menu_label">Realtime</span></a>
  </li>
  <li class="<?php if (preg_match('/realtime_cmd.php*/', $uri)) echo 'on'?>">
   <a class="lst" href="realtime_cmd.php"><span class="menu_label">Command (Advance)</span></a>
  </li>
  </ul>
</div>
