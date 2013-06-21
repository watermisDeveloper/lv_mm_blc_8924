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
     * Includes a Catchment selector form into startpage_intro slot of  
     * smarty template startpage.html. This information is used to load the 
     * selected catchment balance into default_balance slot afer the page was
     * reloaded.
     * 
     * @version 1.1
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */ 
    function block__nb_selector(){
        $query = "select `balance_ds_code`,`hydro_year`,`nb_code`,".
            "count(if(`in_out` = 'resources', 1, NULL)) as 'rsc_sets',".
            "count(if(`in_out` = 'demand', 1, NULL)) as 'dmnd_sets'".
                "From `vw_rsrc_dmnd` group by `balance_ds_code`,`hydro_year`,`nb_code`";
        $result = mysql_query($query, df_db());
        
        echo "<form action='{$_SERVER['PHP_SELF']}' method='post'><table style='width:100%;text-align:center;'><tr>";
        echo "<td><select onchange='printSelectedCatchment(selectedIndex);' name='nb_code'>";
        while (($row = mysql_fetch_assoc($result)) !== FALSE){
           echo "<option value='{$row['nb_code']}'>{$row['nb_code']}</option>";
           $data[] = $row;
        }
        echo "</select></td>";
        echo "<td id='hydro_year'></td>";
        echo "<td id='balance_ds_code'></td></tr></table>'";
        echo "<p id='info_box'></p>";
        echo "<input type='submit' value='Load Catchment' />";
        echo "</form>";
        echo "<script type='text/javascript'>db_balances = ".json_encode($data).
                ";printSelectedCatchment(0);</script>";
        
    }
    
    /** 
     * Include the content of a via post passed balance or, if none was passed a 
     *  default balance view into default_balance slot of  smarty template
     *  startpage.html.
     * 
     * the default balance id is defined hardcoded at this stage.
     * 
     * @version 1.1
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */ 
    function block__default_balance(){
        $app = Dataface_Application::getInstance();
        $table = "vw_rsrc_dmnd";
        
        if (isset($_POST['hydro_year'])){
            $hydro_year = $_POST['hydro_year'];
        } else {
            $hydro_year = 1213;
        }
        
        if (isset($_POST['balance_ds_code'])){
            $balance_ds_code = $_POST['balance_ds_code'];
        } else {
            $balance_ds_code = 1;
        }
        
        if (isset($_POST['nb_code'])){
            $nb_code = $_POST['nb_code'];
        } else {
            $nb_code = 'CKIV';
        }        
        
        $query = "Select * from $table where hydro_year=$hydro_year and balance_ds_code=$balance_ds_code and nb_code='$nb_code' order by in_out DESC";
        
        
        $balance_data_result = mysql_query($query, df_db());
        
        require 'renderBlcTbl.php';

    }
    
}

?>
