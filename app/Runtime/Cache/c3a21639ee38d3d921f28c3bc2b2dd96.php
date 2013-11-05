<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>dan.ci</title>
</head>

<body>
	<form method = 'post' action = "__URL__/update">
		<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><input type = "hidden" name = "id" value = "<?php echo ($vo["id"]); ?>">
		<input type = "text" name = "words" value = "<?php echo ($vo["words"]); ?>">
		<input type = "text" name = "mean" value = "<?php echo ($vo["mean"]); ?>">
		<br><?php endforeach; endif; else: echo "" ;endif; ?>
		<input type = "submit" value = "保存">
	</form>
	<form method = 'post' action = "__URL__/insert">
		<label>单词</label>
		<input type = "text" name = "words">
		<label>解释</label>
		<input type = "text" name = "mean">
		<input type = "submit" value = "提交"> 
		<input type = "reset" value = "清空">
	</form>
</body>
</html>