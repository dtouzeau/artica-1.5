<?php
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.users.menus.inc");
include_once(dirname(__FILE__)."/ressources/class.mini.admin.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");



unset($_SESSION["uid"]);
unset($_SESSION["privileges"]);
unset($_SESSION["qaliases"]);
unset($_SERVER['PHP_AUTH_USER']);
unset($_SESSION["ARTICA_HEAD_TEMPLATE"]);
unset($_SESSION['smartsieve']['authz']);
unset($_SESSION["passwd"]);
unset($_SESSION["LANG_FILES"]);
unset($_SESSION["TRANSLATE"]);
unset($_SESSION["translation"]);
unset($_SESSION["__CLASS-USER-MENUS"]);
$_COOKIE["username"]="";
$_COOKIE["password"]="";


while (list ($num, $ligne) = each ($_SESSION) ){
	unset($_SESSION[$num]);
}

$mini=new miniadmin();
$mini->content="
<script>
	MyHref('/miniadm.php');
</script>

";
$mini->Buildpage();

echo $mini->webpage;