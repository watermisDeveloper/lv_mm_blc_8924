<?php
/**
 * Table DelegateClass
 * The Startpage Delegate Class handels the Startpage. The slots defined in 
 * startpage.html are filled with user role specific content
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class tables_blc_startpage {
    /**
     * set Permissions for startpage due to user role
     * if nobody logged in, grand read only
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function getPermissions(){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        if (isset($user)){ 
            return Dataface_PermissionsTool::getRolePermissions($user->val('Role'));
        }
        else {return Dataface_PermissionsTool::READ_ONLY(); }

    }
    
    /** 
     * Get the Title from startpage table and pass to startpage_title slot
     * in smarty template startpage.html
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__startpage_title(){
        $title = df_get_record('blc_startpage', array('element'=>'title'));
        echo $title->htmlValue('content');
    }
    
    /** 
     * According to the user role, a edit form link for the startpage 
     * will be included to the startpage_edit slot on smarty template 
     * startpage.html
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__startpage_edit(){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $app = Dataface_Application::getInstance();
        $user =& $auth->getLoggedInUser();
        
        if(isset($user)){
            if($user->val('Role') == "admin_data" || $user->val('Role') == "admin_system"){
                echo '<div id="start_editing">Edit this Page</div>';
                echo '<div id="edit_area">';
                echo '<ul><li><a href="'.$app->url('-table=blc_startpage&-action=edit&element=title').'">Edit the title</a></li>';
                echo '<li><a href="'.$app->url('-table=blc_startpage&-action=edit&element=introduction').'">Edit the Introduction</a></li>';
                echo '</ul><div>';
            }
        }
    }
    
    /** 
     * Include the content of introduction element in startpage table into 
     * startpage_intro slot of  smarty template startpage.html
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */ 
    function block__startpage_intro(){
        $introduction = df_get_record('blc_startpage', array('element'=>'introduction'));
        echo $introduction->val('content');
    }
    
    /** 
     * Include the content of a default balance view into default_balance
     *  slot of  smarty template startpage.html.
     * 
     * the default balance id is defined hardcoded at this stage.
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */ 
    function block__default_balance(){
        $app = Dataface_Application::getInstance();
        $table = "vw_rsrc_dmnd";
        $hydro_year = 1213;
        $balance_ds_code = 1;
        $nb_code = 'CKIV';
        
        $query = "Select * from $table where hydro_year=$hydro_year and balance_ds_code=$balance_ds_code and nb_code='$nb_code' order by in_out DESC";
        
        
        $balance_data_result = mysql_query($query, df_db());
        
        require 'renderBlcTbl.php';

    }
    
}

?>
