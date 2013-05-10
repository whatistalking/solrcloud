function get_schema_field_support_options(name){
    if(name=='choose'){
        $('#schema_field_ext').html('');
        $('#schema_field_ext').hide();
        return false;
    }

    if(name=='ignored'){
        $(":input[name='indexed'][value='false']").attr("checked",true);
        $(":input[name='stored'][value='false']").attr("checked",true);
    }

    $.get('ajax.php',{action:'get_schema_field_support_options',name:name},function(data){
        if(data){
            var option_html='<legend>Optional</legend>';
            for(var i=0;i<data.length;i++){
                option_html += "<div class='control-group'>";
                option_html += "<label class='control-label' for=\""+data[i].name+"\">"+data[i].name+" : </label><div class='controls'>";
                if(!data[i].value){
                    option_html += "<input name=\""+data[i].name+"\" id=\""+data[i].name+"\" type=\"text\" />";
                }else{
                    option = data[i].value.split("|");
                    option_html += "<select name=\""+data[i].name+"\" ><option value=\"\">choose</option>";
                    for(var j=0;j<option.length;j++){
                        option_html += "<option value=\""+option[j]+"\">"+option[j]+"</option>";
                    }
                    option_html += "</select>";
                }
                option_html += "</div></div>";
            }
            $('#schema_field_ext').show();
            $('#schema_field_ext').html(option_html);
        }else{
            $('#schema_field_ext').html('');
            $('#schema_field_ext').hide();
        }
    },'json');
}

function edit_schema_field(service_id,name,page){

    $.get('ajax.php',{action:'edit_schema',service_id:service_id,name:name},function(data){
        if(data){
        	
        	var type_list = data.type_list;
        	var field = data.field;
        	var support_types = data.support_types;
        	
        	var type_option_html = '';
            for(var i=0;i<type_list.length;i++){
            	if((field.type == type_list[i].name)){
            		type_option_html += '<option value="'+type_list[i].name+'" selected="selected">'+type_list[i].name+'</option>';
            	}else{
            		type_option_html += '<option value="'+type_list[i].name+'">'+type_list[i].name+'</option>';
            	}    
            }
            
            var indexed_html = '';
            if(field.indexed == 'true'){	
            	indexed_html = '<input type="checkbox" name="indexed" checked="checked">';
            } else {
            	indexed_html = '<input type="checkbox" name="indexed">';
            }
            
            var stored_html = '';
            if(field.stored == 'true'){	
            	stored_html = '<input type="checkbox" name="stored" checked="checked">';
            } else {
            	stored_html = '<input type="checkbox" name="stored">';
            }

            var dynamic_field_html = '';
            if(field.is_dynamic_field == true){	
            	dynamic_field_html = '<input type="checkbox" name="dynamic_field" checked="checked">';
            } else {
            	dynamic_field_html = '<input type="checkbox" name="dynamic_field">';
            }
            
            var form = '';
            if(page == 's'){
            	form = '<form id="form" class="form-horizontal" action="service.php" method="post">';
            } else {
            	form = '<form id="form" class="form-horizontal" action="schema_fields.php" method="post">';
            }
            
            var option_html = '';
            if(support_types){
            	option_html += '<fieldset id="schema_field_ext"><legend>Optional</legend>';	
                for(var i=0;i<support_types.length;i++){
                    option_html += "<div class='control-group'>";
                    option_html += "<label class='control-label' for=\""+support_types[i].name+"\">"+support_types[i].name+" : </label><div class='controls'>";
                    var o_name = support_types[i].name;
                    if(!support_types[i].value){
                    	if(field[o_name]){
                    		option_html += "<input name=\""+support_types[i].name+"\" id=\""+support_types[i].name+"\" value=\""+field[o_name]+"\" type=\"text\" />";
                    	} else {
                    		option_html += "<input name=\""+support_types[i].name+"\" id=\""+support_types[i].name+"\" type=\"text\" />";
                    	}
                    }else{
                        option = support_types[i].value.split("|");
                        option_html += "<select name=\""+support_types[i].name+"\" ><option value=\"\">choose</option>";
                        for(var j=0;j<option.length;j++){
                        	if(field[o_name] && field[o_name] == option[j]){
                        		option_html += "<option selected=\"selected\" value=\""+option[j]+"\">"+option[j]+"</option>";
                        	}else{
                        		option_html += "<option value=\""+option[j]+"\">"+option[j]+"</option>";
                        	}  
                        }
                        option_html += "</select>";
                    }
                    option_html += "</div></div>";
                }          	
            } else {
            	option_html += '<fieldset id="schema_field_ext" style="display:none"><legend>Optional</legend>';
            }
            
            option_html += '</fieldset>';
        	
            var html = form+
            '<fieldset id="schema_field">'+
                '<div class="control-group">'+
                    '<label class="control-label" for="name">Name : </label>'+
                    '<div class="controls">'+field.name+
                    '<input name="name" id="name" type="hidden" value="'+field.name+'" />'+
                    '</div>'+ 
                '</div>'+ 
                '<div class="control-group">'+
                    '<label class="control-label" for="type">Type : </label>'+
                    '<div class="controls">'+
                        '<select name="type" id="type" onchange="get_schema_field_support_options($(this).val());">'+
                            '<option value="choose">choose type</option>'+
                            type_option_html+
                        '</select>'+
                    '</div>'+
                '</div>'+
                '<div class="control-group">'+
                    '<label class="control-label" for="indexed">Indexed : </label>'+
                    '<div class="controls">'+
                    indexed_html+
                    '</div>'+
                '</div>'+
                '<div class="control-group">'+
                    '<label class="control-label" for="stored">Stored : </label>'+
                    '<div class="controls">'+
                    stored_html+
                    '</div>'+
                '</div>'+
                '<div class="control-group">'+
                    '<label class="control-label" for="dynamic_field">DynamicField : </label>'+
                    '<div class="controls">'+
                    dynamic_field_html+
                    '</div>'+
                '</div>'+
            '</fieldset>'+
            option_html+
            '<div class="control-group">'+
                '<div class="controls">'+
                    '<input class="btn btn-primary" id="button1" type="submit" value="Save" />'+
                    '<input style="margin-left:5px;" class="btn" type="button" value="Cancel" onclick="cancel_edit_'+page+'();" />'+
                    '<input type="hidden" name="service_id" value="'+service_id+'" />'+
                    '<input type="hidden" name="action" value="update_field" />'+
                    '<input type="hidden" name="schema_type" value="1" />'+
                '</div>'+
            '</div>'+   
            '</form>';
        	
            $('#schema_field_edit_ajax').html(html);
            if(page == 's'){
            	$('#add_box').hide();
            }else{
            	$('#add').hide();
            }    
        }else{
            //$('#schema_field_ext').html('');
            //$('#schema_field_ext').hide();
        }
    },'json');
}

function cancel_edit_s(){
	$('#schema_field_edit_ajax').html('');
	$('#add_box').show();
}

function cancel_edit_d(){
	$('#schema_field_edit_ajax').html('');
	$('#add').show();
}

function add_alert_info(alert_id, target_id){
    $.get('ajax.php',{action:'add_alert',alert_id:alert_id,target_id:target_id},function(data){
        if(data.res == 'ok'){
        	alert('添加成功');
        }else{
        	alert('添加失败，请先登录！');
        }
    },'json');
}

function setcookie(name,value){
    var expiration = new Date((new Date()).getTime()+14400*60000);
    expire = expiration.toGMTString();
    document.cookie=name+'='+value+';expires='+expire+';';
}
var f3 = "";
var f4 = "";
var f5 = "";
var f6 = "";
var f7 = "";
function service_valid(){
    var f1 = new LiveValidation('service_name');
    f1.add( Validate.Presence );
    f1.add( Validate.Length, { minimum: 3, maximum: 30 } );
    f1.add( Validate.Format, { pattern: /^([a-z0-9-])+$/, failureMessage: "Service name只能包含26个小写字母数字以及'-'" } );

    var f2 = new LiveValidation('optimize_time');
    f2.add( Validate.Format, { pattern: /^(([0-9]|1[0-9]|2[0-3]),){0,}([0-9]|1[0-9]|2[0-3])$/, failureMessage: "The optimize time must be like '0,1,2,3,4,15,6,17,22,23' ." } );
    
  //  if(document.getElementById("config_type_1").checked){
   // f3 = new LiveValidation('pollInterval');
   // f3.add( Validate.Presence );
    //f3.add( Validate.Format, { pattern: /^([0-9]{2}):([0-9]{2}):([0-9]{2})$/, failureMessage: "The poll interval must be like '00:00:00'." } );
    
    if(document.getElementById("config_type_1").checked){
	    f3 = new LiveValidation('pollInterval');
	    f3.add( Validate.Presence );
	    f3.add( Validate.Format, { pattern: /^([0-9]{2}):([0-9]{2}):([0-9]{2})$/, failureMessage: "The poll interval must be like '00:00:00'." } );
	
	    f4 = new LiveValidation('maxDocs');
	    f4.add( Validate.Presence );
	    f4.add( Validate.Numericality, { onlyInteger: true } );
	
	    f5 = new LiveValidation('maxTime');
	    f5.add( Validate.Presence );
	    f5.add( Validate.Numericality, { onlyInteger: true } );
    }else{
    	f6 = new LiveValidation('hand_config');
        f6.add( Validate.Presence );
    }

    if(document.getElementById("schema_type_2").checked){
    	f7 = new LiveValidation('hand_config_schema');
        f7.add( Validate.Presence );
    }else{

    }   
//}
}

//function service_valid_destroy(){
//    f3.destroy();
//    f4.destroy();
//    f5.destroy();
//    
//    f4 = new LiveValidation('maxDocs');
//    f4.add( Validate.Presence );
//    f4.add( Validate.Numericality, { onlyInteger: true } );
//
//    f6 = new LiveValidation('hand_config');
//    f6.add( Validate.Presence );
//    //f6.add( Validate.Numericality);
//
//    f5 = new LiveValidation('maxTime');
//    f5.add( Validate.Presence );
//    f5.add( Validate.Numericality, { onlyInteger: true } );
//    }else{
//        f6 = new LiveValidation('hand_config');
//        f6.add( Validate.Presence );
//       // f6.add( Validate.Numericality );
//    }
//}

function service_valid_destroy(){
    f3.destroy();
    f4.destroy();
    f5.destroy();

    f6 = new LiveValidation('hand_config');
    f6.add( Validate.Presence );
    //f6.add( Validate.Numericality);

}

//function service_valid_add(){
//   f3 = new LiveValidation('pollInterval');
//   f3.add( Validate.Presence );
//   f3.add( Validate.Format, { pattern: /^([0-9]{2}):([0-9]{2}):([0-9]{2})$/, failureMessage: "The poll interval must be like '00:00:00'." } );
//   
//   f4 = new LiveValidation('maxDocs');
//   f4.add( Validate.Presence );
//   f4.add( Validate.Numericality, { onlyInteger: true } );
//   
//   f5 = new LiveValidation('maxTime');
//   f5.add( Validate.Presence );
//   f5.add( Validate.Numericality, { onlyInteger: true } ); 
//
//   f6.destroy();
//}

function service_valid_add(){
   f3 = new LiveValidation('pollInterval');
   f3.add( Validate.Presence );
   f3.add( Validate.Format, { pattern: /^([0-9]{2}):([0-9]{2}):([0-9]{2})$/, failureMessage: "The poll interval must be like '00:00:00'." } );
   
   f4 = new LiveValidation('maxDocs');
   f4.add( Validate.Presence );
   f4.add( Validate.Numericality, { onlyInteger: true } );
   
   f5 = new LiveValidation('maxTime');
   f5.add( Validate.Presence );
   f5.add( Validate.Numericality, { onlyInteger: true } ); 

   f6.destroy();
}

function service_schema_valid_add(){
	   
   	f7 = new LiveValidation('hand_config_schema');
    f7.add( Validate.Presence );
}

function service_schema_valid_destroy(){
	
	f7.destroy();
}

function instance_valid(){
    //var f1 = new LiveValidation('host_id');
    //f1.add( Validate.Presence );
    //f1.add(Validate.Exclusion, { within: ['choose'], failureMessage: "Please choose something!"});

//    var f3 = new LiveValidation('port_num');
//    f3.add( Validate.Presence );
//    f3.add( Validate.Numericality, { minimum: 7701, maximum: 7799, onlyInteger: true } );

    var f2 = new LiveValidation('use_memory');
    f2.add( Validate.Numericality, { onlyInteger: true } );
    
    var lb = new LiveValidation('lb_weight');
    lb.add( Validate.Format, { pattern: /^weight=([0-9]+)$|^backup$/, failureMessage: "The lb weight must be like 'weight=[num]' , or equal to 'backup'." } );
    var lb = new LiveValidation('max_fails');
    lb.add( Validate.Format, { pattern: /^max_fails=([0-9]+)\ fail_timeout=([0-9]+)s$/, failureMessage: "The max fails must be like 'max_fails=1 fail_timeout=60s'." } );

}

function lb_weight_valid(){
    var lb = new LiveValidation('lb_weight');
    lb.add( Validate.Format, { pattern: /^weight=([0-9]+)$|^backup$/, failureMessage: "The lb weight must be like 'weight=[num]' , or equal to 'backup'." } );
}

function add_schema_valid(){
    var f1 = new LiveValidation('name');
    f1.add( Validate.Presence );

    var f2 = new LiveValidation('type');
    f2.add(Validate.Exclusion, { within: ['choose'], failureMessage: "Please choose something!"});
}

function edit_schema_valid(){
    var f2 = new LiveValidation('type');
    f2.add(Validate.Exclusion, { within: ['choose'], failureMessage: "Please choose something!"});
}

function service_add_schema_valid(){
	
    if(document.getElementById("schema_type_1").checked){
    	
        f3 = new LiveValidation('name');
        f3.add( Validate.Presence );

        f4 = new LiveValidation('type');
        f4.add(Validate.Exclusion, { within: ['choose'], failureMessage: "Please choose something!"});

    }else{
    	
       	f5 = new LiveValidation('hand_config_schema');
        f5.add( Validate.Presence );
    }
}

function add_service_schema_valid_add(){
		
    f3.destroy();
    f4.destroy();
	   
   	f5 = new LiveValidation('hand_config_schema');
    f5.add( Validate.Presence );
}

function add_service_schema_valid_destroy(){
	
	f5.destroy();

    f3 = new LiveValidation('name');
    f3.add( Validate.Presence );

    f4 = new LiveValidation('type');
    f4.add(Validate.Exclusion, { within: ['choose'], failureMessage: "Please choose something!"});
}


