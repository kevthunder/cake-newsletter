Array.prototype.in_array = function(p_val) {
	for(var i = 0, l = this.length; i < l; i++) {
		if(this[i] == p_val) {
			return true;
		}
	}
	return false;
}
String.prototype.ucfirst = function() {
	str = this;
    var f = str.charAt(0).toUpperCase();
    return f + str.substr(1);
}

var last_id = 0;
var initedMce = false;

$(function(e){
	$(".add_box_link").click(add_box_click);
	$(".add_link").click(add_link_click);
	$(".close_link").click(close_link_click);
	$(".newsletter_box .edit_box_link").click(edit_box_click);
	$(".newsletter_box .del_box_link").click(del_box_click);
	$(".nltr_column > tbody").sortable({items: 'tr.box_row', connectWith: '.nltr_column > tbody', placeholder: 'placeholder',stop: order_change});
	$("#edit_form_zone").draggable({handle:".edit_box_title"});
	
	$("#NewsletterLang").change(updateLangInputs);
	updateLangInputs();
	
	var clone = $("#add_elem_box #NewsletterElements").clone();
	clone.attr('id','NewsletterElementsClone').hide();
	$("#add_elem_box #NewsletterElements").before(clone);
	adjustStyles();
});

////////////////////////// general newsletter functions //////////////////////////
function updateLangInputs(){
	var lang = $("#NewsletterLang").val();
	if(lang){
		$(".langAssoc").show().find('select,input').removeAttr('disabled');
		$("#NewsletterAssociated"+lang.charAt(0).toUpperCase()+lang.substr(1)).attr('disabled','disabled').closest(".langAssoc").hide();
	}else{
		$(".langAssoc").hide().find('select,input').attr('disabled','disabled');
	}
}
function newsletter_submit(){
	order_change();
	return true;
}
function order_change(){//event, ui
	var zone_id;
	$(".nltr_container").each(function(i){
		zone_id = $(this).attr("zoneid");
		$(this).find(".newsletter_box").each(function(i){
			$(this).find("#NewsletterBoxOrder").val(i);
			$(this).find("#NewsletterBoxZone").val(zone_id);
		});
	});
}

function adjustStyles(elem){
	if(elem){
		var $target = $(elem);
	}else{
		var $target = $("#edit_zone .preview");
	}
	$("table[bgcolor], td[bgcolor], tr[bgcolor]", $target).each(function(){
		$(this).css("background-color",$(this).attr("bgcolor"));
	});
	$("table[width]", $target).each(function(){
		if($(this).attr("width").substring($(this).attr("width").length-1)!= '%'){
			$(this).css("width",$(this).attr("width")+"px");
		}else{
			$(this).css("width",$(this).attr("width"));
		}
	});
	$("table[align]", $target).each(function(){
		$(this).css("text-align",$(this).attr("align"));
		if($(this).attr("align") == "center"){
			$(this).css("margin-left","auto").css("margin-right","auto");
		}
	});
	$("td[align]", $target).each(function(){
		$(this).css("text-align",$(this).attr("align"));
		if($(this).attr("align") == "center"){
			$(this).children("table").css("margin-left","auto").css("margin-right","auto");
		}
	});
	$("td[valign]", $target).each(function(){
		$(this).css("vertical-align",$(this).attr("valign"));
	});
}
////////////////////////// box adding functions //////////////////////////
function add_box_click(e){
	var container = $(this).closest(".nltr_container");
	
	$("#add_elem_box").show("fast");
	$("#add_elem_box").css("top",$(this).offset().top+18-$("#add_elem_box").offsetParent().offset().top);
	$("#add_elem_box").css("left",$(this).offset().left-$("#add_elem_box").offsetParent().offset().left);
	if(container.attr("boxlist")){
		$("#add_elem_box #NewsletterElements").empty();
		var boxlist = container.attr("boxlist").split(";");
		$('#add_elem_box #NewsletterElementsClone option').each(function(i){
			if(boxlist.in_array($(this).attr('value'))){
				$("#add_elem_box #NewsletterElements").append($(this).clone());
			}
		});
	}else{
		$("#add_elem_box #NewsletterElements").empty().append($('#add_elem_box #NewsletterElementsClone option').clone());
	}
	$("#add_elem_box").attr("container",container.attr("id"));
}
function add_link_click(e){
	last_id++;
	var box_element = $("#add_elem_box #NewsletterElements :selected").val();
	var container = $("#"+$("#add_elem_box").attr("container"));
	var cell = $("<td/>").addClass("newsletter_box");
	if(container.hasClass("nltr_column")){
		container.children("tbody").append($("<tr/>").addClass('box_row').append(cell));
	}else if(container.hasClass("nltr_row")){
		container.children("tbody").children("tr").append(cell);
	}
	var url = root+"admin/newsletter/newsletter/add_box/"+box_element+"/"+newsletter_id+"/"+container.attr("zoneid")+"/"+new Date().getTime();
	//alert(url);
	cell.load(url,null,box_loaded);
	$(this).closest(".popup").hide("fast");
}
function box_loaded(responseText, textStatus, XMLHttpRequest){
	//alert($(this).html());
	$(this).find(".edit_box_link").click(edit_box_click);
	$(this).find(".del_box_link").click(del_box_click);
	
	/*$(this).find("input, select, textarea").change(input_change);*/
	var id = $(this).find("#NewsletterBoxId").val();
	$(this).attr("boxid",id);
	$(this).attr("id","box"+id);
	$(this).prepend('<a name="box'+id+'"></a>');
	location.href="#box"+id;
	adjustStyles(this);
	show_edit_form($(this));
}
function close_link_click(e){
	$(this).closest(".popup").hide("fast");
}
function del_box_click(){
	var newsletter_box = $(this).closest(".newsletter_box");
	var url = root+"admin/newsletter/newsletter/delete_box/"+newsletter_box.attr("boxid")+"/"+new Date().getTime();
	$.get(url, function(data){
  		//alert("Data Loaded: " + data);
	});
	if(newsletter_box.hasClass('selected')){
		hide_edit_form();
	}
	newsletter_box.remove();
}

////////////////////////// box editing functions //////////////////////////
function edit_box_click(){
	show_edit_form($(this).closest(".newsletter_box"));
}
function hide_edit_form(){
	var edit_form = $("#edit_form_zone").html("");
	$("#edit_form_zone").hide();
	$(".newsletter_box.selected").removeClass("selected");
}
function show_edit_form(newsletter_box){
	var samebox = $("#edit_form_zone .edit_form").attr("boxid")==newsletter_box.attr("boxid");

	hide_edit_form();
	$("#edit_form_zone").append($('.ajax_loader').clone().show());
	
	var url = root+"admin/newsletter/newsletter/get_box_edit/"+newsletter_box.attr("boxid")+"/"+new Date().getTime();
	$.get(url,null,edit_form_loaded);
	
	newsletter_box.addClass("selected");
	
	$("#edit_form_zone").show();
	if(!samebox){
		$("#edit_form_zone").css('position','absolute');
		$("#edit_form_zone").css('top',newsletter_box.offset().top-$("#edit_form_zone").offsetParent().offset().top);//
		$("#edit_form_zone").css('left',newsletter_box.offset().left-$("#edit_form_zone").offsetParent().offset().left);//
	}
}
function edit_form_loaded(responseText, textStatus, XMLHttpRequest){
	$("#edit_form_zone").empty().append(responseText);
	
	$("#edit_form_zone").find(".submit_edit_form").click(submit_edit_form);
	$("#edit_form_zone").find(".close_link").click(hide_edit_form);
	$("#edit_form_zone").find("input[type!=hidden], select, textarea").get(0).focus();
	
	entries_select_init($("#edit_form_zone"));
	entry_finder_init($("#edit_form_zone"));
	
	$("#edit_form_zone").trigger('contentAdded');
	addTinyMce();
}
function submit_edit_form(){
	var edit_form = $("#edit_form_zone .edit_form");
	var boxid = edit_form.attr("boxid");
	var url = root+"admin/newsletter/newsletter/edit_box/"+edit_form.attr("boxid")+"/"+new Date().getTime();
	if(window.tinyMCE){
		tinyMCE.triggerSave();
	}
	edit_form.find("form").ajaxForm({"url":url,"success":function(data){edit_form_sended(data,boxid)}});
	edit_form.find("form").submit();
	
	return false;
}

function edit_form_sended(data,boxid) {
	//alert(data);
	//console.debug(this);
	var box_element = $("#box"+boxid);
	box_element.html(data);
	box_loaded.call(box_element,data,null,null);
	adjustStyles(box_element);
}
/*function input_change(e){
	var edit_form = $(this).closest(".edit_form");
	var box_element = $(".newsletter_box[boxid="+edit_form.attr("boxid")+"]");
	box_element.find('#'+$(this).attr("target")).html($(this).val());
}*/

////////////////////////// box_edit TinyMce functions //////////////////////////
function addTinyMce(){
	$('textarea.tinymce').each(function(){
		var preloader = $('.ajax_loader').clone().show();
		preloader.addClass('tinymce_preloader');
		preloader.css('position','absolute');
		preloader.css('top',$(this).position().top+5);
		preloader.css('right',10);
		$(this).before(preloader);
	});
	$(window).error(tinyMceError);
	tinyMCE.init({
		mode : "specific_textareas",
		plugins : "paste,template, table",
		paste_remove_styles : true,
		editor_selector : "tinymce",
		theme : "advanced",
		entities : "160,nbsp,38,amp,34,quot,162,cent,8364,euro,163,pound,165,yen,169,copy,174,reg,8482,trade,8240,permil,60,lt,62,gt,8804,le,8805,ge,176,deg,8722,minus",
		entity_encoding : "named",
		theme_advanced_resizing : true,
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		content_css : "/css/admin/tinymce.css",
		theme_advanced_buttons3_add : "pastetext, template, separator, tablecontrols",
		paste_auto_cleanup_on_paste: true,
		table_inline_editing : true,
		paste_text_sticky: true,
		relative_urls : false,
		setupcontent_callback : tinyMceLoaded,
		document_base_url : root
	});
};
function tinyMceError(){
	$(window).unbind( 'error', tinyMceError );
	$('.tinymce_preloader').css('background-image','none');
	$('.tinymce_preloader').css('width','auto');
	$('.tinymce_preloader').html('Error');
	$('.tinymce_preloader').fadeOut(5000);
	initedMce = false;
}
function tinyMceLoaded(){
	$('.tinymce_preloader').remove();
}
////////////////////////// box_edit EntriesSelect functions //////////////////////////
function entries_select_init($edit_box){
	$edit_box.find("#entries_list .entry .title").click(entry_title_click);
	$edit_box.find("#entries_list .entry .checkbox input.checkbox").change(entry_checkbox_change);
	$edit_box.find("#entries_list .pagin .pagin_page").click(page_click);
}
function entry_title_click(){
	$(this).closest(".entry").find(".data").toggle();
	var checkbox = $(this).closest(".entry").find(".checkbox input.checkbox");
	if(!checkbox.attr('checked')){
		checkbox.attr('checked','checked');
		checkbox.change();
	}
}
function entry_checkbox_change(){
	var inputs = $(this).closest(".entry").find(".data input, .data textarea");
	if($(this).attr('checked')){
		inputs.attr('disabled',false);
	}else{
		inputs.attr('disabled','disabled');
	}
}

function page_click(){
	var page = $(this).attr('page');
	$(this).closest("#entries_list").find(".pagin .pagin_page").removeClass('cur_page');
	$(this).closest("#entries_list").find(".pagin .pagin_page[page="+page+"]").addClass('cur_page');

	$(this).closest("#entries_list").find(".page").removeClass('cur');
	$(this).closest("#entries_list").find("#page"+page).addClass('cur');
}
////////////////////////// box_edit EntryFinder functions //////////////////////////
var $cur_entryFinder = null;
function entry_finder_init($edit_box){
	//$(".entry_finder .bt_search",$edit_box).colorbox({onOpen:function(){alert("test0")},onLoad:function(){alert("test1")},onComplete:function(){alert("test2")}});
	var entry_popup_init = function (){
		$('#cboxLoadedContent a:not(.bt_select)').colorbox(colorbox_options);
		$('#cboxLoadedContent a.bt_select').click(entry_select_bt_click);
		$('#cboxLoadedContent form').ajaxForm({"success":function(data){
			var opts = {'html':data};
			$.colorbox($.extend({},colorbox_options,opts));
		}});
	}
	var colorbox_options = {maxHeight:"95%",onComplete:entry_popup_init};
	
	$(".entry_finder .bt_search",$edit_box).click(function(){
		var $entryFinder = $(this).closest(".entry_finder");
		$cur_entryFinder = $entryFinder;
	});
	$(".entry_finder .bt_search",$edit_box).colorbox(colorbox_options);
	
	$(".entry_finder .bt_load",$edit_box).click(entry_load_bt_click);
}
function entry_load_bt_click(){
	var $entryFinder = $(this).closest(".entry_finder");
	entry_load($entryFinder,$('.id_input',$cur_entryFinder).val());
}
function entry_select_bt_click(){
	var id = $(this).attr('id');
	entry_load($cur_entryFinder,id);
	$('.id_input',$cur_entryFinder).val(id);
	$.colorbox.close();
}
function entry_load($entryFinder,id){
	var model = $entryFinder.attr('model');
	if(id && model){
		$('.loading',$entryFinder).show();
		var url = root+"admin/newsletter/newsletter_assets/ajax_get_entry/"+model+"/"+id+"/"+new Date().getTime();
		
		$.get(url, function(jsonData){
			if(model.indexOf('.') != -1){
				model = model.substring(model.indexOf('.')+1);
			}
			$('.loading',$entryFinder).hide();
			var $edit_box = $entryFinder.closest(".edit_form");
			
			var data;
			if(typeof(jsonData)=='object'){
				data = jsonData;
			}else{
				data = eval('(' + jsonData + ')');
			}
			if(window.console){
				window.console.log(data);
			}
			
			var loadCallback = false;
			try{
				loadCallback = eval('(' + $entryFinder.attr("onloaddata") + ')');
			}catch(e){
			}
			if(!$.isFunction(loadCallback) || loadCallback(data)!==false){
				for(var i in data[model]){
					var $input = $("#NewsletterBoxData"+i.ucfirst(),$edit_box);
					if($input.length){
						//alert(i);
						if(data[model][i]==null){
							$input.val("");
						}else{
							$input.val(data[model][i]);
						}
					}
				}
				if(data["newsletterbox_media"]){
					$('table.multimedia',$edit_box).multimedia.clear();
					$.each(data["newsletterbox_media"], function(index, value) { 
						$('table.multimedia',$edit_box).multimedia.add(value);
					});
				}
			}
		},"json");
	}
}
