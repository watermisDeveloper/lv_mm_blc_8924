<?php
/**
 * Dataface action class definition
 * sync action is callable for any admin user Role. Will manage the synchronization
 * process and update the sync message on startpage
 * 
 * @todo Insert a wait page or busy page to signalize work in progress to the user
 * @see run_sync
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class actions_sync {
    function handle($params){
         $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        
        if (isset($user)){
            if($user->val('Role') == 'admin_data' || $user->val('Role') == 'admin_system'){
                /* run the synchronization process*/
                $this->run_sync();
                
                /* renew sync information */
                $time = new DateTime();
                $query = "update blc_startpage set content='".$time->getTimestamp()."'  where element='sync'";
                mysql_query($query, df_db());
            }
        }
    }
    
    /** 
     * run_sync function
     * checks for each required file in temp_sync folder if it exists. 
     * calls the specific import function for each sync table
     * 
     * @see sync_blc_dataset
     * @see sync_blc_dataset_nb
     * @see sync_blc_dmnd_hydromonths_vol
     * @see sync_blc_resources_types
     * @see sync_blc_rsc_hydromonths_vol
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function run_sync(){
       /* sync the blc_dataset */
       if (file_exists('tmp_sync/blc_dataset.csv')){
           if (($i = $this->sync_blc_dataset('tmp_sync/blc_dataset.csv')) > 0){
               echo "<p>$i balance datasets were synchronized</p>"; 
           } 
           else { echo  "<p>blc_dataset.csv has a corrupt format.</p>"; }
           unlink('tmp_sync/blc_dataset.csv');
       } 
       else { echo  "<p>blc_dataset.csv was not found.</p>"; }
        
       /* sync the catchment codes */
       if (file_exists('tmp_sync/blc_dataset_nb.csv')){
           if (($i = $this->sync_blc_dataset_nb('tmp_sync/blc_dataset_nb.csv')) > 0){
               echo "<p>$i catchment codes were synchronized</p>"; 
           } 
           else { echo  "<p>blc_dataset_nb.csv has a corrupt format.</p>"; }
           unlink('tmp_sync/blc_dataset_nb.csv');
       } 
       else { echo  "<p>blc_dataset_nb.csv was not found.</p>"; }
       
       /* sync the demand scenarios */
       if(file_exists('tmp_sync/blc_dmnd_hydromonths_vol.csv')){
           if(($i = $this->sync_blc_dmnd_hydromonths_vol('tmp_sync/blc_dmnd_hydromonths_vol.csv')) > 0){
               echo  "<p>$i demand scenarios were synchronized</p>";
           } 
           else { echo  "<p>blc_dmnd_hydromonths_vol.csv has a corrupt format.</p>"; }
           unlink ('tmp_sync/blc_dmnd_hydromonths_vol.csv');
       } 
       else { echo  "<p>blc_dmnd_hydromonths_vol.csv was not found.</p>"; }
       
       /* sync the resource sets */
       if(file_exists('tmp_sync/blc_rsc_hydromonths_vol.csv')){
           if(($i = $this->sync_blc_rsc_hydromonths_vol('tmp_sync/blc_rsc_hydromonths_vol.csv')) > 0){
               echo  "<p>$i resource sets were synchronized</p>";
           } 
           else { echo  "<p>blc_rsc_hydromonths_vol.csv has a corrupt format.</p>"; }
           unlink('tmp_sync/blc_rsc_hydromonths_vol.csv');
       } 
       else { echo  "<p>blc_rsc_hydromonths_vol.csv was not found.</p>"; }

       /* sync the blc_resources_types */
       if(file_exists('tmp_sync/blc_resources_types.csv')){
           if(($i = $this->sync_blc_resources_types('tmp_sync/blc_resources_types.csv')) > 0){
               echo  "<p>$i resource type descriptions were synchronized</p>";
           } 
           else { echo  "<p>blc_resources_types.csv has a corrupt format.</p>"; }
           unlink('tmp_sync/blc_resources_types.csv');
       } 
       else { echo  "<p>blc_resources_types.csv was not found.</p>"; }
       
       /* remove the synchrnization folder again */
       rmdir('tmp_sync/');
    }

    /** 
     * sync_blc_dataset function
     * opens $filePath in tmp_sync folder and uploads content to stations
     * table using a INSERT ON DUPLICATE KEY UPDATE SQL query. Lines are checked
     * for having 7 columns, as required by the database.
     * 
     * @return int number of inserted or updated rows
     * @param String $filePath path to the file to upload
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function sync_blc_dataset($filePath){
        if (($dataset = fopen($filePath, 'r')) !== FALSE){
            $i = 0;
            while (($row = fgetcsv($dataset, 0, ',')) !== FALSE){
                if (count($row) == 7){
                    $query = "insert into blc_dataset (balance_ds_code, balance_ds_title, hydro_year, created_date,
                        created_by, comment_use_project, comment_resources)
                        values ('".$row[0]."','".$row[1]."','".$row[2]."','".$row[3]."','".$row[4]."','"
                        .$row[5]."','".$row[6]."')
                        on duplicate key update balance_ds_title=values(balance_ds_title), 
                        hydro_year=values(hydro_year), created_date=values(created_date), created_by=values(created_by),
                        comment_use_project=values(comment_use_project), comment_resources=values(comment_resources)";
                mysql_query($query, df_db());
                $i++;
                }
            }
            return $i;
        }
        fclose($dataset);
    }
    
    /** 
     * sync_blc_dataset_nb
     * opens $filePath in tmp_sync folder and uploads content to sensors
     * table using a INSERT ON DUPLICATE KEY UPDATE SQL query. Lines are checked
     * for having 3 columns, as required by the database.
     * 
     * @return int number of inserted or updated rows
     * @param String $filePath path to the file to upload
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function sync_blc_dataset_nb($filePath){
        if (($nb = fopen($filePath, 'r')) !== FALSE){
            $i = 0;
            while (($row = fgetcsv($nb, 0, ',')) !== FALSE){
                if (count($row) == 3){
                    $query = "insert into blc_dataset_nb ( balance_ds_code, nb_code, nb_level ) values 
                        ('".$row[0]."','".$row[1]."','".$row[2]."') 
                        on duplicate key update nb_level=values(nb_level)";
                mysql_query($query, df_db());
                $i++;
                }
            }
            return $i;
        }
        fclose($nb);
    }
    
    /** 
     * sync_blc_dmnd_hydromonths_vol function
     * opens $filePath in tmp_sync folder and uploads content to rivers
     * table using a INSERT ON DUPLICATE KEY UPDATE SQL query. Lines are checked
     * for having 18 columns, as required by the database.
     * 
     * @return int number of inserted or updated rows
     * @param String $filePath path to the file to upload
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function sync_blc_dmnd_hydromonths_vol($filePath){
        if (($demand = fopen($filePath, 'r')) !== FALSE){
            $i = 0;
            while (($row = fgetcsv($demand,0, ',')) !== FALSE){
                if (count($row) == 18){
                    $query = "insert into blc_dmnd_hydromonths_vol(`balance_ds_code`,`nb_code`,`hydro_year_of_demand`,
                        `scenario`,`scenario_desc`,`yrA_m09`,`yrA_m10`,`yrA_m11`,`yrA_m12`,`yrB_m01`,`yrB_m02`,`yrB_m03`,
                        `yrB_m04`,`yrB_m05`,`yrB_m06`,`yrB_m07`,`yrB_m08`,`commentary`) values 
                        ('".$row[0]."','".$row[1]."','".$row[2]."','".$row[3]."','".$row[4]."','"
                        .$row[5]."','".$row[6]."','".$row[7]."','".$row[8]."','".$row[9]."','".$row[10]."','"
                        .$row[11]."','".$row[12]."','".$row[13]."','".$row[14]."','".$row[15]."','".$row[16].",'".$row[17]."') 
                        on duplicate key update scenario_desc=values(scenario_desc), yrA_m09=values(yrA_m09) , yrA_m10=values(yrA_m10)
                        , yrA_m11=values(yrA_m11), yrA_m12=values(yrA_m12), yrB_m01=values(yrB_m01), yrB_m02=values(yrB_m02)
                        , yrB_m03=values(yrB_m03), yrB_m04=values(yrB_m04), yrB_m05=values(yrB_m05), yrB_m06=values(yrB_m06)
                        , yrB_m07=values(yrB_m07), yrB_m08=values(yrB_m08), commentary=values(commentary)";
                    mysql_query($query, df_db());
                    $i++;
                }
            }
            return $i;
        }
        fclose($demand);
    }
    
    /** 
     * sync_blc_resources_types function
     * opens $filePath in tmp_sync folder and uploads content to 
     * discharge_measrmnts table using a INSERT ON DUPLICATE KEY UPDATE SQL 
     * query. Lines are checked for having 2 columns, as required by the database.
     * 
     * @return int number of inserted or updated rows
     * @param String $filePath path to the file to upload
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function sync_blc_resources_types($filePath){
        if (($types = fopen($filePath, 'r')) !== FALSE){
            $i = 0;
            while(($row = fgetcsv($types, 0, ',')) !== FALSE){
                if (count($row) == 2){
                    $query = "insert into blc_resources_types ( resources_type, description ) values 
                        ('".$row[0]."','".$row[1]."') 
                        on duplicate key update description=values(description))";
                    mysql_query($query, df_db());
                    $i++;
                }
            }
            return $i;
        }
        fclose($types);
    }
    
    /** 
     * sync_blc_rsc_hydromonths_vol function
     * opens $filePath in tmp_sync folder and uploads content to rating_dates
     * table using a INSERT ON DUPLICATE KEY UPDATE SQL query. Lines are checked
     * for having 15 columns, as required by the database.
     * 
     * @return int number of inserted or updated rows
     * @param String $filePath path to the file to upload
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function sync_blc_rsc_hydromonths_vol($filePath){
        if (($resource = fopen($filePath, 'r')) !== FALSE){
            $i = 0;
            while (($row = fgetcsv($resource, 0, ',')) !== FALSE){
                if (count($row) == 15){
                    $query = "insert into rating_dates ( `balance_ds_code`,`nb_code`,`resources_type`,
                        `yrA_m09`,`yrA_m10`,`yrA_m11`,`yrA_m12`,`yrB_m01`,`yrB_m02`,`yrB_m03`,`yrB_m04`,
                        `yrB_m05`,`yrB_m06`,`yrB_m07`,`yrB_m08` ) values 
                        (('".$row[0]."','".$row[1]."','".$row[2]."','".$row[3]."','".$row[4]."','"
                        .$row[5]."','".$row[6]."','".$row[7]."','".$row[8]."','".$row[9]."','".$row[10]."','"
                        .$row[11]."','".$row[12]."','".$row[13]."','".$row[14]."') 
                        on duplicate key update yrA_m10=values(yrA_m10), yrA_m11=values(yrA_m11), 
                        yrA_m12=values(yrA_m12), yrB_m01=values(yrB_m01), yrB_m02=values(yrB_m02), yrB_m03=values(yrB_m03),
                        yrB_m04=values(yrB_m04), yrB_m05=values(yrB_m05), yrB_m06=values(yrB_m06), yrB_m07=values(yrB_m07),
                        yrB_m08=values(yrB_m08), ";
                mysql_query($query, df_db());
                $i++;
                }
            }
            return $i;
        }
        fclose($resource);
    }
    
    /** 
     * rmSyncDir function
     * secure removal of all tmp_sync dir content and dir removal afterwads
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */   
    function rmSyncDir(){
        if(file_exists('tmp_sync/rivers.csv')){
            unlink('tmp_sync/rivers.csv');
        }
        if(file_exists('tmp_sync/stations.csv')){
            unlink('tmp_sync/stations.csv');
        }
        if(file_exists('tmp_sync/sensors.csv')){
            unlink('tmp_sync/sensors.csv');
        }
        if(file_exists('tmp_sync/discharge_measrmnts.csv')){
            unlink('tmp_sync/discharge_measrmnts.csv');
        }
        if(file_exists('tmp_sync/rating_dates.csv')){
            unlink('tmp_sync/rating_dates.csv');
        }
        rmdir('tmp_sync/');
    }
}

?>
