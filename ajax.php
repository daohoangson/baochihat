<?php
if (!empty($_POST['feedLast'])) {
	require('data.php');
	$feedLast = intval($_POST['feedLast']);
	$count = count($feeds);
	$feeds = array_slice($feeds,$feedLast,$perpageAjax);
	printFeeds($feeds);
	$newFeedLast = $feedLast + count($feeds);
?>
<script type="text/javascript">
window.feedLast = <?php echo $newFeedLast ?>;
<?php if ($newFeedLast >= $count): ?>
window.ranOut = true;
<?php endif; ?>
</script>
<?php
}