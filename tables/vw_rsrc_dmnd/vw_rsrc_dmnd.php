<?php
class tables_vw_rsrc_dmnd {
	function getPermissions(){
		$auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();

        
        if ( !isset($user) ){ return Dataface_PermissionsTool::READ_ONLY(); }
		else {
			return Dataface_PermissionsTool::getRolePermissions($user->val('Role'));
		}
	}
}
?>