<?php
require_once 'ajaxPro.inc.php';
require_once 'ajax.inc';
require_once 'ajax2.inc';
echo AjaxPro::register ( new Langdaren\Ajax\PageUtil (), "demo" );
echo AjaxPro::register ( new Langdaren\Ajax\PageUtil2 (), "demo2" );
?>

<script>
AjaxPro.demo.test(true,function(result){
	console.log(result);
});
</script>