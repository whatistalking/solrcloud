//default merge
var display_type = 1;

function load_setting(){
	
	var id = $("#update_id").val();
	if (id != '0'){	
		$.get('ajax.php',{action:'get_select'},function(data){	
			build_select_html(data)
			//load setting info to draw chart
			apply_setting();
		},'json');
	}else{
		$.cookie("_TempSavedCookie", '');
	}
}

function show_select(){
	
	var con = $('#content').html();
	if(con == ''){	
		$.get('ajax.php',{action:'get_select'},function(data){
			build_select_html(data);
			$('#content').show(500);
		},'json');
	} else {
		$('#content').show(500);
	}
}

function build_select_html(data){
	
	var list_service = data.l_service;
	var list_host = data.l_host;
	var report = data.report;

	var list_service_html = '';
	for(var i=0;i<list_service.length;i++){ 
		if(list_service[i].service_status == '1'){
			list_service_html += '<label class="checkbox"><input type="checkbox" name="target_s" value="'+list_service[i].service_id+'">'+list_service[i].service_name+'</label>';
		}      	
	}

	var list_host_html = '';
	for(var i=0;i<list_host.length;i++){ 	
		list_host_html += '<label class="checkbox"><input type="checkbox" name="target_h" value="'+list_host[i].host_id+'">'+list_host[i].host_name+'</label>';
	}

	var report_service_html = '';
	var report_host_html = '';
	var report_global_html = '';
	var report_instance_html = '';
	var report_host_instance_html = '';
	for(var i in report){
		if(report[i].target_type == 'service'){
			report_service_html += '<label class="checkbox"><input type="checkbox" name="report_s" value="'+i+'">'+report[i].name+'</label>';		
                }else if(report[i].target_type == 'instance'){
			report_instance_html += '<label class="checkbox"><input type="checkbox" name="report_s" value="'+i+'">'+report[i].name+'</label>';
		}else if(report[i].target_type == 'host'){
			report_host_html += '<label class="checkbox"><input type="checkbox" name="report_h" value="'+i+'">'+report[i].name+'</label>';
                }else if(report[i].target_type == 'host-instance'){
			report_host_instance_html += '<label class="checkbox"><input type="checkbox" name="report_h" value="'+i+'">'+report[i].name+'</label>';
		}else if(report[i].target_type == 'global'){
			report_global_html += '<label class="checkbox"><input type="checkbox" name="report_g" value="'+i+'">'+report[i].name+'</label>';        		
		}
	}

	var html = '<table class="table">'+
	'<thead>'+
	'<tr><th colspan="2">Service</th><th colspan="2">Host</th><th>Global</th></tr>'+
	'</thead>'+
	'<tbody>'+
	'<tr>'+
	'<td>'+
	list_service_html+
	'</td>'+
	'<td>'+
	report_service_html+
        '<br />'+
        report_instance_html+
	'</td>'+
	'<td>'+
	list_host_html+
	'</td>'+
	'<td>'+
	report_host_html+
        '<br />'+
	report_host_instance_html+
	'</td>'+
	'<td>'+
	report_global_html+
	'</td>'+
	'</tr>'+
	'</tbody>'+
	'</table>'+
	'<div style="margin-left:650px">'+
	'<a id="select_cancel" href="#">Cancel</a>  '+
	'<input id="select_send" class="btn btn-primary btn-small" type="submit" value="OK" />'+
	'</div>';

	$('#content').hide();
	$('#content').html(html);	
}

function apply_setting(){
	
	var id = $("#update_id").val();
	if (id != '0'){
		$.get('ajax.php',{action:'get_report_setting',setting_id:id},function(data){
			if(data.res == 'ok'){
				var setting = data.data.setting;
			
				for(var i=0;i<setting.service.target.length;i++){
					$(':checkbox[name="target_s"][value="'+setting.service.target[i]+'"]').attr('checked','checked');
				}
	        
				for(var i=0;i<setting.service.report.length;i++){ 
					$(':checkbox[name="report_s"][value="'+setting.service.report[i]+'"]').attr('checked','checked');
				}
	        
				for(var i=0;i<setting.host.target.length;i++){ 
	        		$(':checkbox[name="target_h"][value="'+setting.host.target[i]+'"]').attr('checked','checked');
				}
	        
				for(var i=0;i<setting.host.report.length;i++){ 
					$(':checkbox[name="report_h"][value="'+setting.host.report[i]+'"]').attr('checked','checked');
				}
	        
				for(var i=0;i<setting.global.report.length;i++){ 
					$(':checkbox[name="report_g"][value="'+setting.global.report[i]+'"]').attr('checked','checked');
				}
	        
				save_select_cookie();
			}
		},'json');
	}
}

function save_select_cookie(){
	
	var target_s = {};
    var i = 0;
    $(':checkbox[name="target_s"]:checked').each(function() {
    	target_s[i] = this.value;
        i++;
    });
    
    
	var target_h = {};
    var i = 0;
    $(':checkbox[name="target_h"]:checked').each(function() {
    	target_h[i] = this.value;
        i++;
    });
    
	var report_s = {};
    var i = 0;
    $(':checkbox[name="report_s"]:checked').each(function() {
    	report_s[i] = this.value;
        i++;
    });
    
	var report_h = {};
    var i = 0;
    $(':checkbox[name="report_h"]:checked').each(function() {
    	report_h[i] = this.value;
        i++;
    });
    
	var report_g = {};
    var i = 0;
    $(':checkbox[name="report_g"]:checked').each(function() {
    	report_g[i] = this.value;
        i++;
    });
    
    var select_date = {};
	if($("#select-compare").attr("checked") == 'checked'){
		var from1 = $("#from_1").val();
		var to1 = $("#to_1").val();
		var from2 = $("#from_2").val();
		var to2 = $("#to_2").val();
		select_date[0] = {};
		select_date[0].from = from1;
		select_date[0].to = to1;
		select_date[1] = {};
		select_date[1].from = from2;
		select_date[1].to = to2;
	} else {
		var from1 = $("#from_1").val();
		var to1 = $("#to_1").val();
		select_date[0] = {};
		select_date[0].from = from1;
		select_date[0].to = to1;
	}

    var value = {};
    value.target_s = target_s;
    value.target_h = target_h;
    value.report_s = report_s;
    value.report_h = report_h;
    value.report_g = report_g;
    value.select_date = select_date;
    $.cookie("_TempSavedCookie", JSON.stringify(value));
    
    build_chart_setting();
}

function build_chart_setting(){
	
	var cookie = $.cookie("_TempSavedCookie");
	if(cookie != ''){
		var data = eval('(' + cookie + ')');

		var chart_data = {};
		var div_ids = {};
		var i = 0;
	
		if (display_type == 1){
		
			//merge
			for(var j in data.report_s){
				chart_data[i] = {};
				chart_data[i].report_id = data.report_s[j];
				chart_data[i].target_id = data.target_s;
				chart_data[i].div_id = 'chart'+i;
				chart_data[i].select_date = data.select_date;
				div_ids[i] = 'chart'+i;
				i++
			}
		
			for(var k in data.report_h){
				chart_data[i] = {};
				chart_data[i].report_id = data.report_h[k];
				chart_data[i].target_id = data.target_h;
				chart_data[i].div_id = 'chart'+i;
				chart_data[i].select_date = data.select_date;
				div_ids[i] = 'chart'+i;
				i++
			}
		
			for(var l in data.report_g){
				chart_data[i] = {};
				chart_data[i].report_id = data.report_g[l];
				chart_data[i].target_id = {};
				chart_data[i].div_id = 'chart'+i;
				chart_data[i].select_date = data.select_date;
				div_ids[i] = 'chart'+i;
				i++
			}		
		} else if (display_type == 2){
		
			//grid
			for(var j in data.report_s){
				for(var k in data.target_s){
					chart_data[i] = {};
					chart_data[i].report_id = data.report_s[j];
					chart_data[i].target_id = {};
					chart_data[i].target_id[0] = data.target_s[k];
					chart_data[i].div_id = 'chart'+i;
					chart_data[i].select_date = data.select_date;
					div_ids[i] = 'chart'+i;
					i++
				}
			}
			
			for(var j in data.report_h){
				for(var k in data.target_h){
					chart_data[i] = {};
					chart_data[i].report_id = data.report_h[j];
					chart_data[i].target_id = {};
					chart_data[i].target_id[0] = data.target_h[k];
					chart_data[i].div_id = 'chart'+i;
					chart_data[i].select_date = data.select_date;
					div_ids[i] = 'chart'+i;
					i++
				}
			}
			
			for(var l in data.report_g){
				chart_data[i] = {};
				chart_data[i].report_id = data.report_g[l];
				chart_data[i].target_id = {};
				chart_data[i].div_id = 'chart'+i;
				chart_data[i].select_date = data.select_date;
				div_ids[i] = 'chart'+i;
				i++
			}
		}

	        //add_chart_div(div_ids);
                drop_old_chart();
		draw_chart(chart_data);
	}
}

function add_chart_div(div_ids){
	
	var html = '';
	for(var i in div_ids){	
		html += '<div id="'+div_ids[i]+'" style="width:550px; height:400px; float:left;"></div>';
	}
	$("#report_chart").html(html);
}
function drop_old_chart(){
    $("#report_chart").html('');
}


function draw_chart(chart_data){
    //url = sc_url + '/ajax/report.php?param=' + JSON.stringify(chart_data);
    url = './ajax/report.php?param=' + JSON.stringify(chart_data);
    $.ajax({
        url:url,
            async:false,
            success:function(data){
                $('#content2').append(data);
            }
    });
	
}

function save_select_db(){
	
	var cookie = $.cookie("_TempSavedCookie");
	if(cookie != ''){
		var report_name = $("#report_name").html();
		var update_id = $("#update_id").val();
	
		$.get('ajax.php',{action:'save_select',update_id:update_id,setting:cookie,report_name:report_name},function(data){
			//alert("Saved Successfully")
			if(data.res == 'ok'){
				var r_list = data.data;
				var r_html = '';
				for(var i in r_list){
					r_html += '<a class="a_r_name" href="report.php?r_id='+r_list[i].id+'">'+r_list[i].name+'</a>';
				}
				$('#saved_list').html(r_html);
			}
			if(data.res != 'error'){
				var upd_id = data.id;
				$("#update_id").val(upd_id);
			
				var d_html = '<a href="report.php?action=delete&r_id='+upd_id+'" id="name_delete">Delete</a>';
				$('#name_delete_span').html(d_html);
			}
		},'json');
	}
}

function edit_name(){
	
	$("#report_name").hide();
	$("#name_edit").hide();
	$("#report_name_edit").show();
	$("#name_save").show();
}

function save_name(){
	
	var new_name = $("#report_name_edit").val();
	$("#report_name").html(new_name);
	$("#report_name").show();
	$("#name_edit").show();
	$("#report_name_edit").hide();
	$("#name_save").hide();
	var update_id = $("#update_id").val();
        /*修改个人report的名称*/
        $.get('ajax.php',{action:'modify_report_seeting',update_id:update_id,report_name:new_name},function(data){
           console.log(data); 
        });        

}

function change_time_select(){

	var select_date = {};
	if($("#select-compare").attr("checked") == 'checked'){
		var from1 = $("#from_1").val();
		var to1 = $("#to_1").val();
		var from2 = $("#from_2").val();
		var to2 = $("#to_2").val();
		var html = from1+' to '+to1+' compare '+from2+' to '+to2;
		
		select_date[0] = {};
		select_date[0].from = from1;
		select_date[0].to = to1;
		select_date[1] = {};
		select_date[1].from = from2;
		select_date[1].to = to2;

		$('#time-select').val(html);
		$('#time-select').css('width','400px');	
	} else {
		var from1 = $("#from_1").val();
		var to1 = $("#to_1").val();
		var html = from1+' - '+to1;
		
		select_date[0] = {};
		select_date[0].from = from1;
		select_date[0].to = to1;

		$('#time-select').val(html);
		$('#time-select').css('width','180px');   
	}
	$('#time-select-content').hide();
	
	var cookie = $.cookie("_TempSavedCookie");
	if(cookie != ''){
		var value = eval('(' + cookie + ')');
		value.select_date = select_date;
    	$.cookie("_TempSavedCookie", JSON.stringify(value));

		build_chart_setting();
	}
}

function change_display_type(type){
	
	if(type == '1'){
		display_type = 1;
	}else if(type == '2'){
		display_type = 2;
	}
	
	build_chart_setting();
}
