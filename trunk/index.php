<?php
require('data.php');
if (count($feeds) > $perpage) $feeds = array_slice($feeds,0,$perpage);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Bao Chi Hat</title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" /> 
	<META NAME="keywords" content="Bao Chi Hat, Báo Chí Hát, báo, nhân văn"> 
	<META NAME="description" CONTENT="An annual event of the Department of Journalism and Communication, University of Social Sciences and Humanities, Vietnam National University (Hanoi)"> 
	<META property="og:title" content="Bao Chi Hat"/>
	<META property="og:description" content="An annual event of the Department of Journalism and Communication, University of Social Sciences and Humanities, Vietnam National University (Hanoi)"/>
	<META property="og:type" content="activity"/>
	<META property="og:image" content="http://baochihat.com/logo.jpg"/>
	<META property="og:url" content="http://baochihat.com/"/>
	<META property="og:site_name" content="Bao Chi Hat"/>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
	<script type="text/javascript" src="jquery-lightbox-0.5/js/modified.min.js"></script>
	<script type="text/javascript" src="assets/baochihat-1.1.min.js"></script>
	<link rel="stylesheet" type="text/css" href="jquery-lightbox-0.5/css/jquery.lightbox-0.5.min.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="assets/baochihat-1.2.min.css" media="screen" />
</head>
<body>
<div id="feed">
<?php printFeeds($feeds); ?>
</div>
<script type="text/javascript">
	window.ranOut = false;
	window.feedLast = <?php echo count($feeds) ?>;
</script>
<div id="bottom">&nbsp;</div>
<div id="footer">
	<a href="http://www.facebook.com/BaochiHat">B&#225;o Ch&#237; H&#225;t &copy; <?php echo date('Y') ?></a>
	<div class="muted">
		Support provided by <a href="http://daohoangson.com" title="Developer &amp; Designer">Dao Hoang Son</a>.
	</div>
</div>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-19316220-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</body>