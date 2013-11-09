/* startpage.js
 * This is custom js file for control function of the startpage
 * 
 * @author: Mirko Maelicke
 */
$(document).ready(function() {
    /* Create slideToggle for admin-editing area  */
    $('#start_editing').click(function() {
        $('#edit_area').slideToggle('slow');
    });

    /* hide search form on startpage */
    $('#top-search-form').css('display', 'none');
    defaultBalance = new BalanceRenderer('defaultBalance',$('#blc_view'),resources,demand,meta);
    //defaultBalance.create();
    defaultBalance.render();

});

function BalanceRenderer(obj, DOMLocation, resources, demands, meta){
    this.obj = obj
    this.location = DOMLocation;
    this.resources = resources;
    this.demands = demands;
    this.activeDemand = demands[0];
    this.name = 'blc_'+new Date().getTime();
    this.balance;
    if(!$.isPlainObject(meta)){
        this.meta = {'hydro_year':'1213','nb_code':'default'};
    } else { this.meta = meta }
    
    
    this.create = function(){
        var newHTML = "<h1>Balance for "+this.meta['nb_code']+" Catchment</h1>"+
                "<h4>actual viewing "+this.selectedResource[1]+" Resource set of Year "+this.meta['hydro_year'][0]
                +this.meta['hydro_year'][1]+'/'+this.meta['hydro_year'][2]+this.meta['hydro_year'][3]+"</h4>";
        newHTML += "<br/><br/><select onchange='if(parseInt(this.options[this.selectedIndex].value) >= 0){"
            +this.obj+".activeDemand="+this.obj
            +".demands[this.options[this.selectedIndex].value]; "+this.obj+".render();}'>";
        newHTML += "<option value='-1'>Select demand scenario...</option>";
        $.each(this.demands, function(i,dmnd){
            newHTML += "<option value='"+i+"'>"+dmnd[1]+"</option>";
        });
        newHTML += "</select><span>  balance for selected scenarion will be based on resources set "+
                this.selectedResource[1]+"</span><br/><br/>";
        
        newHTML += "<select onchange='if(parseInt(this.options[this.selectedIndex].value) >= 0){"
                +this.obj+".selectedResource="+this.obj+".resources[this.options[this.selectedIndex].value]; "
                +this.obj+".render();}'>";
        newHTML += "<option value='-1'>Select resources set ... </option>";
        $.each(this.resources, function(i,res){
            newHTML += "<option value='"+i+"'>"+res[1]+"</option>";
        });
        newHTML += "</select><span> resource set, the balance will be based on</span><table id='"+this.name+"'>";
        
        newHTML +=  "</table>";
        $(this.location).html(newHTML);
    };
    
    this.render = function(){
        this.create();
        data = new Array();
        $.each(this.resources, function(i, resource){
           data.push(resource); 
        });
        data.push(new Array('','','','','','','','','','','','','',''));
        data.push(this.activeDemand);
        this.balance = this.calcBalance();
        data.push(this.balance);
        result = this.calcResult();
        
        $('#'+this.name).dataTable({
            "aaData":data,
            "aoColumns": [
                {'sTitle':'Data type','bVisible':false},
                {'sTitle':'Type','sClass':'blc_th'},  
                {'sTitle':'October'},
                {'sTitle':'November'},
                {'sTitle':'December'},
                {'sTitle':'January'},
                {'sTitle':'February'},
                {'sTitle':'March'},
                {'sTitle':'April'},
                {'sTitle':'May'},
                {'sTitle':'June'},
                {'sTitle':'July'},
                {'sTitle':'August'},
                {'sTitle':'September'}
            ],
            "bFilter": false,
            "bSort":false,
            "bInfo":true,
            "bLengthChange":false,
            "fnCreatedRow": function( nRow, aData, iDataIndex ) {
                if (aData[0] == 'calc'){
                    $(nRow).css('background-color','#A9F5D0');
                    cells = $(nRow).children();
                    $.each(aData,function(i,cell){
                        if(result[i]){
                            $(cells[i]).css('color','red');
                            $(cells[i]).css('font-weight','bolder');
                        }  
                    });
                }
            }
        });
    };
    
    this.calcBalance = function(){
        var row = new Array();
        /* determin the type */
        row.push('calc','Balance '+this.selectedResource[0]+" - "+this.activeDemand[0]);
        
        for (var i = 2; i <= 13;i++){
            row.push(parseFloat(this.selectedResource[i]) - parseFloat(this.activeDemand[i]));
        }
        
        return row;
    };
    
    this.calcResult = function(){
        var row = new Array();
        row.push(false);
        
        for( var i = 2; i <= 13; i++){
            if ((parseFloat(this.selectedResource[i]) - parseFloat(this.activeDemand[i])) < 0.33* parseFloat(this.selectedResource[i])){
                row.push(true)
            }
            else {
                row.push(false)
            }
        }
        
        return row;
        
    }
    
    this.getResIndex = function(){
        var index = 0, j = 0;
        $.each(this.resources, function(i,resource){
            try {
            var thisindex = parseInt(''+resource[0][3]+resource[0][4]);
            }
            catch (e){
                alert('Resource code: '+resource[0]+ ' is of not known format and will be ignored.');
                var thisindex = 0;
            }
            if (index < thisindex){
                index = thisindex;
                j = i;
            }
        });
        
        return j;
    };
    
    this.selectedResource = resources[this.getResIndex()];
}

function printSelectedCatchment(index){
    /* Empty Fields */
    $('#hydro_year').html('Select the hydrological year for the Balance: <br/>');
    $('#balance_ds_code').html('Select the Balance Dataset: <br/>');
    
    if (parseInt(db_balances[index]['rsc_sets']) === 0){
        var nb_rsc = "<span style='color:red; font-weight:bold;'>"+db_balances[index]['rsc_sets']+" Resources Sets</span>";
    }
    else {
        var nb_rsc = "<span style='color:green; font-weight:bold;'>"+db_balances[index]['rsc_sets']+" Resources Sets</span>";
    }
    
    if (parseInt(db_balances[index]['dmnd_sets']) === 0){
        var nb_dmnd = "<span style='color:red; font-weight:bold;'>"+db_balances[index]['dmnd_sets']+" Demand Scenarios</span>";
    }
    else {
        var nb_dmnd = "<span style='color:green; font-weight:bold;'>"+db_balances[index]['dmnd_sets']+" Demand Scenarios</span>";
    }
    
    /* Draw the Info Box*/
    $('#info_box').html('The XX Catchment has '+nb_rsc+' and '+ nb_dmnd + ' related to it');
    
    $.each(db_balances, function(i,bal){
        if (db_balances[index]['nb_code'] == bal['nb_code']){
            $('#hydro_year').html( $('#hydro_year').html() + 
                    "<input type='radio' name='hydro_year' value='"+bal['hydro_year']+"' />"+bal['hydro_year']+"<br/>");
        }
        if (db_balances[index]['nb_code'] == bal['nb_code']){
            $('#balance_ds_code').html( $('#balance_ds_code').html() + 
                    "<input type='radio' name='balance_ds_code' value='"+bal['balance_ds_code']+"' />"+bal['balance_ds_code']+"<br/>");
        }
 
    });
}

/**
 * Opens a html based information popup in front of the watermis application
 * explaning the use of startpage openlayers map.
 * 
 * author Mirko Maelicke <mirko@maelicke-online.de>
 */
function popup(content){
    $('#popup_wrapper').html(""+
            "<div id='popup' style='width: 60%; height: 80%; top: 10%; margin-left: auto; margin-right: auto; padding: 10px;"+
            " border: 1px solid black; background-color: white; overflow-y: auto; position: relative; border-radius: 15px'>"+
            "<a href='javascript:$(\"#popup_wrapper\").css(\"display\",\"none\")'"+
            "id='close_wrapper' style='float: right; margin-right: 0.5em; font-size: 220%; color: red; cursor: pointer;'>X</a>"+
            "<p style='margin: 2px;'>"+content+"</p>"+
            "</div>"
    );
    $('#popup_wrapper').css('display','block');
}

    $('#close_wrapper').click(
            $('#popup_wrapper').css('display', 'none')
        );
