<?php

/**
 * Xataface ApplicationDelegate
 * ApplicationDelegate Class defines rules, configurations and actions 
 * linked to the entire Water MIS application. This file is part of Water MIS
 * application using Xataface 2.0alpha
 *
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 * @version 1.0
 * 
 */
class conf_ApplicationDelegate {
    /**
     * Returns permissions array.  This method is called every time an action is 
     * performed to make sure that the user has permission to perform the action.
     * 
     * @param record A Dataface_Record object (may be null) against which we check
     *               permissions.
     * @see Dataface_PermissionsTool
     * @see Dataface_AuthenticationTool
     * @return Dataface Permission
     * @version 1.0
     * @autor Mirko Maelicke <mirko@maelicke-online.de>
     */
    function getPermissions(&$record){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        //$app = Dataface_Application::getInstance();
        //$app_query =& $app->getQuery();
        
        if ( !isset($user) ){ return Dataface_PermissionsTool::NO_ACCESS(); }
		else {
			return Dataface_PermissionsTool::getRolePermissions($user->val('Role'));
		}
    }
    
     /**
      * before rendering any page, create a extended navigation bar for the system
      * admin. Set default action for startpage, help page and import history page.
      * include custom css and javascript
      * 
      * @version 1.0
      * @author Mirko Maelicke <mirko@maelicke-online.de>
      */      
      function beforeHandleRequest(){
          $app =& Dataface_Application::getInstance();
          $query =& $app->getQuery();
          
          //make help action the default table action
          if ( $query['-table'] == 'help' and ($query['-action'] == 'browse' or $query['-action'] == 'list') ){
              $query['-action'] = 'help';
          }
          
          //Make the startpage action the default table action
          if ( $query['-table'] == 'blc_startpage' and ($query['-action'] == 'browse' or $query['-action'] == 'list') ){
              $query['-action'] = 'startpage';
          }
          
          /* add the custom stylesheet for page style */
          $app->addHeadContent('<link rel="stylesheet" type="text/css" href="themes/style.css"/>'); 
      }
      
    /**
     * Depending on the actual logged in user role and the called table, a 
     * proper sidebar is created. In order to make it easy and useable only links
     * should be shown, the user is allowed to use.
     * 
     * @deprecated since version 1.1
     * @version 1.1
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__sidebar(){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $app =& Dataface_Application::getInstance();
        $query =& $app->getQuery();
        $user =& $auth->getLoggedInUser();
        
        /* sidebar is invisble by default */
        echo "<ul style='display: none;'>";
        /* include at least back and home button */
        if (isset($_SESSION['backlink'])){
            echo "<li><a href='".$_SESSION['backlink']."'><img src='images/s_back.png' alt='back' />
                <span>back</span></a></li>";
        }
        echo "<li><a href='index.php?-table=blc_startpage'><img src='images/s_home.png' alt='Home' /><span>Start
            </span></a></li>";
        
        if (isset($user) && ($user->val('Role') == 'admin_data' || $user->val('Role') == 'admin_system')){
            echo "<li><a href='".$app->url('-table=mis_users&-action=list')."'><img src='images/s_users.png' alt='users' />
                    <span>Users</span></a></li>";
            if ($query['-action'] !== 'startSync'){
                echo "<li><a href='".$app->url('-action=startSync')."'><img src='images/s_sync.png' alt='sync' />
                        <span>Sync</span></a></li>";
            }  
        }
        
        /* help button for everyone */
        echo "<li><a href='index.php?-table=help'><img src='images/s_help.png' alt='back' />
            <span>Help</span></a></li>";
        echo "</ul>";
        
        /* save the actual backlink in the session */
        $_SESSION['backlink'] = ''.$app->url('');
    }
    
    /**
     * as the logged in user is an admin, include a Edit Message link on 
     * synchronization page to redirect to an editing form for the message, to 
     * be shown on sync page 
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__sync_message(){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        $content = df_get_record('blc_startpage', array('element'=>'sync_message'));
        

        echo $content->val('content');
        if (isset($user)){
            if ($user->val('Role') == 'admin_system' || $user->val('Role') == 'admin_data'){
                echo "<br><a href='index.php?-table=blc_startpage&-action=edit&element=sync_message' style='float:right;'>
                    Edit message</a><br>";
            }
        }
        echo "<h1>  </h1>";
    }
    
    /**
     * insert the popup link and content into the page as the popup folder 
     * contained a popup named after the actual table or a given -popup name.
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__popup_link(){
        if (isset($_GET['-popup'])){
            $filename = "{$_GET['-popup']}.html";
        }
        elseif (isset($_GET['-table'])){
            $filename = "{$_GET['-table']}.html";
        }
        else {
            $filename = 'blc_startpage.html';
        }
        if (file_exists("popup/".$filename)){
            $content = htmlentities(str_replace("\"", "'",file_get_contents("popup/".$filename)), ENT_QUOTES);
            echo "<a href='javascript:popup(\"$content\");' style='float:left; margin-left: 1em'>".
                    "<img src='images/s_help.png' alt='help' width='24' height='24'/>".
                    "<span style='margin-left: 1em;'>Help for this Page</span></a>";                          
        }
    }
    
        /**
     * Versioning number is is included into fineprint
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__version(){
        $version = file_get_contents("version.txt");
        
        echo $version;
    }

    
}


?>
