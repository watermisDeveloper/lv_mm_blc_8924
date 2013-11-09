<?php
/* 
 * This script will parse css and javascript scripts to the calling location.
 * The mysql_query result $balance_data_result will be parsed to HTML table.
 */

//echo "<h1>Balance for Default Catchment</h1>";
//echo "<h3>for defaulft Catchment in default year</h3>";

//echo "<div style='width:940px; overflow:auto; margin-top: 50px; margin-bottom 50px;'>";
//echo "<table style='border: 1px solid black; border-radius:15px; padding:5px;'>";
/*echo "<tr><th>Type</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th><th>Jan</th>".
    "<th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th><th>Jul</th>".
    "<th>Aug</th></tr>";
while (($row = mysql_fetch_assoc($balance_data_result)) !== FALSE){
    echo "<tr class='".$row['in_out']."'>";
    echo "<th title='".$row['description']."'>".$row['type']."</th>";
            $m = 9;
            for($i = 0; $i < 13; $i++){
                if ($m == 13){$m = 1;}
                echo "<td>".$row['Hyr_m'.$m]."</td>";
                $m++;
            }
            
    echo "</tr>";
}
*/
echo "<script type='text/javascript'>";
echo " var resources = new Array(); var demand = new Array();";
while (($row = mysql_fetch_assoc($balance_data_result)) !== FALSE){
    if ($row['in_out'] === 'resources'){
        echo " resources.push(".  json_encode(array($row['in_out'],$row['type'],$row['Hyr_m10'],
            $row['Hyr_m11'],$row['Hyr_m12'],$row['Hyr_m1'],$row['Hyr_m2'],$row['Hyr_m3'],$row['Hyr_m4'],
            $row['Hyr_m5'],$row['Hyr_m6'],$row['Hyr_m7'],$row['Hyr_m8'],$row['Hyr_m9'])).");";
    }
    elseif ($row['in_out'] === 'demand') {
        echo " demand.push(".  json_encode(array($row['in_out'],''.$row['type'].' '.$row['hydro_year_of_demand'],$row['Hyr_m10'],
            $row['Hyr_m11'],$row['Hyr_m12'],$row['Hyr_m1'],$row['Hyr_m2'],$row['Hyr_m3'],$row['Hyr_m4'],
            $row['Hyr_m5'],$row['Hyr_m6'],$row['Hyr_m7'],$row['Hyr_m8'],$row['Hyr_m9'])).");";
    } 
}
 echo 'meta={"hydro_year":"'.$hydro_year.'","nb_code":"'.$nb_code.'"};';
echo "</script>";
echo "<div style='width:940px; overflow:auto; margin-top: 10px; margin-bottom 50px;' id='blc_view'>";
echo "</div>";

?>
