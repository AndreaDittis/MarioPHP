<?php
/* Smarty version 3.1.30, created on 2016-09-09 23:48:40
  from "D:\xampp\htdocs\mariophp\Index\Tpl\Index\index.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_57d2d9d8025b55_26834993',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '4367ce703df17f2b6570e743e7ccb57a1879844d' => 
    array (
      0 => 'D:\\xampp\\htdocs\\mariophp\\Index\\Tpl\\Index\\index.html',
      1 => 1473435527,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_57d2d9d8025b55_26834993 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html"; charset="UTF-8">
	<title>Document</title>
	<?php echo '<script'; ?>
 type="text/javascript" src="__PUBLIC__/js/jquery-1.7.2.min.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 type="text/javascript">
		$(function(){
			alert(1);
		})


	<?php echo '</script'; ?>
>
</head>
<body>
	<form method="post" action="">
		昵称:<input type="text" name="name" id="">
		<br />
		性别:<input type="text" name="sex" id="">
		<br />
		<input type="submit" value="提交">

	</form>
	<?php echo $_smarty_tpl->tpl_vars['var']->value;?>

</body>
</html><?php }
}
