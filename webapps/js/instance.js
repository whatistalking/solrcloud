$(document).ready(function(){ 
    $("#b_active").click(function(event){
    	$('tr.stoped').addClass('hidden');
    	$('tr.running').removeClass('hidden');
    });
    
    $("#b_stoped").click(function(event){
    	$('tr.stoped').removeClass('hidden');
    	$('tr.running').addClass('hidden');
    });
    
    $("#b_all").click(function(event){
    	$('tr.stoped').removeClass('hidden');
    	$('tr.running').removeClass('hidden');
    });
    
    $("#t_all").click(function(event){
    	$("li.tab_depart").removeClass('active');
    	$("#t_all").addClass('active');
    	$('tr.anjuke').removeClass('d_hidden');
    	$('tr.haozu').removeClass('d_hidden');
    	$('tr.aifang').removeClass('d_hidden');
    	$('tr.jinpu').removeClass('d_hidden');
    	$('tr.inc').removeClass('d_hidden');
    });
    
    $("#t_anjuke").click(function(event){
    	$("li.tab_depart").removeClass('active');
    	$("#t_anjuke").addClass('active');
    	$('tr.anjuke').removeClass('d_hidden');
    	$('tr.haozu').addClass('d_hidden');
    	$('tr.aifang').addClass('d_hidden');
    	$('tr.jinpu').addClass('d_hidden');
    	$('tr.inc').addClass('d_hidden');	
    });
    
    $("#t_haozu").click(function(event){
    	$("li.tab_depart").removeClass('active');
    	$("#t_haozu").addClass('active');
    	$('tr.anjuke').addClass('d_hidden');
    	$('tr.haozu').removeClass('d_hidden');
    	$('tr.aifang').addClass('d_hidden');
    	$('tr.jinpu').addClass('d_hidden');
    	$('tr.inc').addClass('d_hidden'); 	
    });
    
    $("#t_aifang").click(function(event){
    	$("li.tab_depart").removeClass('active');
    	$("#t_aifang").addClass('active');
    	$('tr.anjuke').addClass('d_hidden');
    	$('tr.haozu').addClass('d_hidden');
    	$('tr.aifang').removeClass('d_hidden');
    	$('tr.jinpu').addClass('d_hidden');
    	$('tr.inc').addClass('d_hidden');    	
    });    
    
    $("#t_jinpu").click(function(event){
    	$("li.tab_depart").removeClass('active');
    	$("#t_jinpu").addClass('active');
    	$('tr.anjuke').addClass('d_hidden');
    	$('tr.haozu').addClass('d_hidden');
    	$('tr.aifang').addClass('d_hidden');
    	$('tr.jinpu').removeClass('d_hidden');
    	$('tr.inc').addClass('d_hidden'); 	
    });    
    
    $("#t_inc").click(function(event){
    	$("li.tab_depart").removeClass('active');
    	$("#t_inc").addClass('active');
    	$('tr.anjuke').addClass('d_hidden');
    	$('tr.haozu').addClass('d_hidden');
    	$('tr.aifang').addClass('d_hidden');
    	$('tr.jinpu').addClass('d_hidden');
    	$('tr.inc').removeClass('d_hidden');  	
    });  
     
    $("#t_all").click();
    $("#b_active").click();
});
