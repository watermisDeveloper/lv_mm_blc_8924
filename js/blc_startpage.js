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
                {'sTitle':'September'},
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
                {'sTitle':'August'}
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