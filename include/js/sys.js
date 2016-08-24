if(typeof(ajaxFailed)==="undefined"){ajaxFailed="failed"}if(typeof(ajaxSuccess)==="undefined"){ajaxSuccess="success"}function setupCalendar(){var a=document.getElementById("startdate");if(a!=null){Calendar.setup({inputField:"startdate",ifFormat:"%Y-%m-%d",button:"startdatebutton"});Calendar.setup({inputField:"enddate",ifFormat:"%Y-%m-%d",button:"enddatebutton"})}return true}function miniSetupCalendar(a,c){var b=document.getElementById(c);var d=document.getElementById(a);if(b!=null&&d!=null){Calendar.setup({inputField:a,ifFormat:"%Y-%m-%d",button:c})}return true}function tinymce_image_process(c){if(c=="math"){var b=getElementByClass("AM");if(isArray(b)&&b.length>0){var a,d;for(i in b){a=b[i];d=a.innerHTML;if(d.indexOf("mimetex")<0&&d.indexOf("cgi-bin")<0){a.innerHTML="";d=d.replace(/\`/g,"");a.appendChild(AMTparseMath(d))}}}}else{if(c=="svg"){drawPics()}else{return false}}return true}function getElementByClass(e){var a=new Array();var d=document.getElementsByTagName("*");if(d!=undefined&&d.length>0){var b=0;var c;for(c=0;c<d.length;c++){if(d[c].className==e){a[b]=d[c];b++}}}return a}function getTargetDiv(b){var a=document.getElementById(b);if(!(isObject(a)&&typeof a.innerHTML!="undefined")){if(typeof window.opener!="undefined"&&typeof window.opener.document!="undefined"){a=window.opener.document.getElementById(b)}}if(isObject(a)&&typeof a.innerHTML!="undefined"){return a}return false}function ajaxRequest(){if(window.XMLHttpRequest){return new XMLHttpRequest()}else{if(window.ActiveXObject){var a=["Msxml2.XMLHTTP","Microsoft.XMLHTTP"];for(var b=0;b<a.length;b++){try{return new ActiveXObject(a[b])}catch(c){}}}else{return false}}}function ajaxGetpage(e,a,d,c,b){return ajaxPostform(e,a,false,d,true,false,false,false,c,b)}function ajaxPostform(k,b,j,g,h,a,f,c,d,e){if(typeof(this.ajaxobj)==="undefined"){this.ajaxobj=new ajaxPostformObj()}return this.ajaxobj.runit(k,b,j,g,h,a,f,c,d,e)}function ajaxClipboard(b){if(typeof(this.ajaxobj)==="undefined"){this.ajaxobj=new ajaxPostformObj()}var a=this.ajaxobj.clipboard;if(b===true){this.ajaxobj.clipboard=""}return a}ajaxPostformObj=function(){if(typeof(ajaxPostformObj.singleton)!=="undefined"){return ajaxPostformObj.singleton}ajaxPostformObj.singleton=this;var that=this;var parent=this;this.ajaxtag_prefix="<!---:ajproto_TaG_";this.ajaxtag_postfix=":--->";this.ajaxtag_sep=":";this.clipboard="";var private_obj=new function(){var that=this;if(typeof(ajaxServerError)!=="undefined"&&isString(ajaxServerError)){this.ajax_error=ajaxServerError}else{this.ajax_error="An error has occured while accessing the server."}this.get_headers=function(headers){if(!isArray(headers)||headers.length<=0){return}var item,count=0;var ret_array=new Array();if(!isArray(headers[0])&&headers.length==2){headers=[headers]}for(var i=0;i<headers.length;i++){item=headers[i];if(isArray(item)&&isString(item[0])&&isString(item[1])){ret_array[count++]=item}}return ret_array};this.ajax_command=function(ret_text){if(!isString(ret_text)){return false}var index=ret_text.indexOf(parent.ajaxtag_prefix);if(index<0){return false}var begin_index=index;if(isInteger(index)&&index>=0){index+=parent.ajaxtag_prefix.length;var index_end=ret_text.indexOf(parent.ajaxtag_sep,index);if(!(isInteger(index_end)&&index_end>=0)||index_end+2>=ret_text.length){return false}var str=ret_text.substr(index,index_end-index);if(isString(str)){index_end++;var index_end2=ret_text.indexOf(parent.ajaxtag_postfix,index_end);if(!(isInteger(index_end2)&&index_end2>=0)||index_end2<=index_end){return false}var param=ret_text.substr(index_end,index_end2-index_end);if(!isString(param)){return false}var ret_array=new Array();ret_array[0]=str;ret_array[1]=param;ret_array[2]="";if(begin_index>0){ret_array[2]=ret_text.substring(0,begin_index)}var startindex=index_end2+parent.ajaxtag_postfix.length;if(startindex<ret_text.length){ret_array[2]+=ret_text.substring(startindex,ret_text.length)}return ret_array}}return false};this.check_response_error=function(ret_text,printerror){if(typeof(ret_text)=="undefined"||ret_text==""||ret_text==ajaxSuccess||ret_text==ajaxFailed){return true}if(typeof(printerror)=="undefined"){printerror=true}var ajax_command=that.ajax_command(ret_text);if(ajax_command!==false){switch(ajax_command[0]){case"ERROR":parent.printErrorWindow(ajax_command[1]);return false;case"REDIRECTHOME":parent.printErrorWindow(ajax_command[1],430,180,true);return false;case"URL":parent.runit(false,ajax_command[1],false,true,false);break;case"MSG":parent.printErrorWindow(ajax_command[1]);break;case"CLIPBOARD":parent.clipboard=ajax_command[1];break;case"CLIPBOARD_ARRAY":parent.clipboard="";var tmp_array="";if(isString(ajax_command[1])){try{eval("tmp_array="+ajax_command[1]+";")}catch(e){tmp_array=""}if(isArray(tmp_array)){parent.clipboard=tmp_array}}break;default:return true}if(isString(ajax_command[2])){return ajax_command[2]}}return true};this.post_process=function(request_obj,printerror,divid){if(!isObject(request_obj)||typeof(request_obj.responseText)==="undefined"){if(printerror){parent.printErrorWindow(that.ajax_error)}return false}var check_result=that.check_response_error(request_obj.responseText,printerror);var valid_divid=isString(divid);if(!valid_divid){if(check_result!==false){if(isString(check_result)){return check_result}return request_obj.responseText}else{return false}}else{if(check_result!==false){if(valid_divid){var divobj=getTargetDiv(divid);if(divobj!==false&&isString(request_obj.responseText)){if(isString(check_result)){divobj.innerHTML=check_result}else{divobj.innerHTML=request_obj.responseText}doJscriptProcess(request_obj.responseText)}}return true}else{return false}}}};this.runit=function(divid,url,formname,useasync,printerror,pre_callback,post_callback,headers,nobustcache,getmethod){var parameters="";if(!isString(url)){return false}if(typeof(printerror)==="undefined"||!isBoolean(printerror)){printerror=true}if(!isBoolean(useasync)){useasync=true}var mypostrequest=ajaxRequest();if(mypostrequest===false){alert(gGeneralJavasriptError);return false}if(useasync){mypostrequest.onreadystatechange=function(){if(mypostrequest.readyState==4){if(isString(pre_callback)){eval("callback_"+pre_callback+"(url,mypostrequest)")}else{if(typeof(pre_callback)=="function"){pre_callback(url,mypostrequest)}}if(typeof(cms_session_class)!=="undefined"&&typeof(cms_session_class.singleton)!=="undefined"){cms_session_class.singleton.request_result(url,mypostrequest)}if(mypostrequest.status==200){private_obj.post_process(mypostrequest,printerror,divid)}else{if(printerror){that.printErrorWindow(private_obj.ajax_error)}}if(isString(post_callback)){eval("callback_"+post_callback+"(url,mypostrequest)")}else{if(typeof(post_callback)=="function"){post_callback(url,mypostrequest)}}}}}if(isString(formname)){parameters=this.getPostQueryString(formname)}if(nobustcache!==true){if(parameters==""){parameters="bustcache="+new Date().getTime()}else{parameters+="&bustcache="+new Date().getTime()}}try{if(getmethod===true){mypostrequest.open("GET",url,useasync)}else{mypostrequest.open("POST",url,useasync)}mypostrequest.setRequestHeader("Content-type","application/x-www-form-urlencoded");mypostrequest.setRequestHeader("X-Requested-With","XMLHttpRequest");if(typeof(headers)!=="undefined"&&isArray(headers)){var valid_headers=private_obj.get_headers(headers);if(isArray(valid_headers)&&valid_headers.length>0){var i=0;for(i in valid_headers){mypostrequest.setRequestHeader(valid_headers[i][0],valid_headers[i][1])}}}if(getmethod===true){mypostrequest.send()}else{mypostrequest.send(parameters)}}catch(e){if(printerror){that.printErrorWindow(private_obj.ajax_error)}return false}if(useasync){return true}if(typeof(cms_session_class)!=="undefined"&&typeof(cms_session_class.singleton)!=="undefined"){try{cms_session_class.singleton.request_result(url,mypostrequest)}catch(err){}}if(mypostrequest.status==200){var ret=private_obj.post_process(mypostrequest,printerror,divid);return ret}else{if(printerror){that.printErrorWindow(private_obj.ajax_error)}}return false};this.printErrorWindow=function(errortext,winwidth,winheight,doRedirect){if(typeof winwidth==="undefined"){winwidth="400";winheight="250"}if(typeof doRedirect==="undefined"){doRedirect=false}if(doRedirect){if(typeof appHomeUrl==="undefined"){appHomeUrl="/"}errortext='<div class="errorwindow"><div class="errorwindowtext">'+errortext+"</div></div>";setTimeout('window.location="'+appHomeUrl+'"',10000)}else{errortext='<div class="errorwindow"><div class="errorwindowtext">'+errortext+'<div class="errorwindowbutton"><input type="button" class="btn" value="'+closeButtonText+'" onClick="closeDhtmlWindow(\'errorwin_div\'); return false;" onMouseover="if(this.className) this.className=\'btnhov\';" onMouseout="if(this.className) this.className=\'btn\'" /></div></div></div>'}newDhtmlWindow_text("Message",winwidth,winheight,"errorwin_div",errortext)};this.getPostQueryString=function(formname){if(!isString(formname)){return""}var theform=document.forms[formname];var reqStr="";var elementname="";var elementName="";var sel;var ampchar="";if(typeof(theform)=="undefined"){return""}for(var i=0;i<theform.elements.length;i++){elementname=theform.elements[i].name;if(typeof(elementname)=="undefined"||elementname==""){elementname=theform.elements[i].id}if(typeof(elementname)=="undefined"||elementname==""){continue}elementName=theform.elements[i].tagName.toLowerCase();switch(elementName){case"input":switch(theform.elements[i].type){case"text":case"hidden":reqStr+=ampchar+elementname+"="+encodeURIComponent(theform.elements[i].value);break;case"checkbox":if(theform.elements[i].checked){reqStr+=ampchar+elementname+"="+theform.elements[i].value}else{reqStr+=ampchar+elementname+"="}break;case"radio":if(theform.elements[i].checked){reqStr+=ampchar+elementname+"="+theform.elements[i].value}}break;case"textarea":reqStr+=ampchar+elementname+"="+encodeURIComponent(theform.elements[i].value);break;case"select":sel=theform.elements[i];for(var j=0;j<sel.options.length;j++){if(sel.options[j].selected){reqStr+=ampchar+elementname+"="+encodeURIComponent(sel.options[j].value);ampchar="&"}}break}if(reqStr!=""){ampchar="&"}}return reqStr}};function hideDiv(a){var c=document.getElementsByTagName("div");for(var b=0;b<c.length;b++){if(c[b].id.match(a)){if(document.getElementById){c[b].style.visibility="hidden"}else{if(document.layers){document.layers[c[b]].display="hidden"}else{document.all.hideShow.divs[b].visibility="hidden"}}}}}function showDiv(a){var c=document.getElementsByTagName("div");for(var b=0;b<c.length;b++){if(c[b].id.match(a)){if(document.getElementById){c[b].style.visibility="visible"}else{if(document.layers){document.layers[c[b]].display="visible"}else{document.all.hideShow.divs[b].visibility="visible"}}}}}function getDivobj(a){var c=document.getElementsByTagName("div");var b;for(b=0;b<c.length;b++){if(c[b].id.match(a)){return c[b]}}return false}function doJscriptProcess(b){if(b.search(/id=['"]datebutton["']/i)>0){miniSetupCalendar("fielddate","datebutton")}if(b.search(/id=['"]startdatebutton["']/i)>0){setupCalendar()}if(b.search(/<span class=['"]am["']>/i)>0){tinymce_image_process("math")}if(b.search(/type=['"]image.svg.xml["']/i)>0){tinymce_image_process("svg")}var c=1;var d="templatedatebutton";var a="templatedatetext";var e=new RegExp("id=['\"]"+d+c+"[\"']","i");while(true){if(b.search(e)>0){miniSetupCalendar(a+c,d+c)}else{break}c++;e=new RegExp("id=['\"]"+d+c+"[\"']","i")}}function closeDhtmlWindow(a){modDhtmlWindow(a,1)}function hideDhtmlWindow(a){modDhtmlWindow(a,2)}function showDhtmlWindow(a){modDhtmlWindow(a,3)}function isObject(a){return(typeof(a)==="object"&&a!==null)}function isInteger(a){return(typeof(a)==="number"&&parseInt(a,10)===a)}function isString(a){return(typeof(a)==="string"&&a.replace(/^\s+|\s+$/g,"")!=="")}function isArray(a){return $.isArray(a)}function isBoolean(a){return(typeof(a)==="boolean")}function isDhtmlxObject(a){return(isObject(a)&&typeof(a.idd)!=="undefined")}function btn_mouse(b,a){if(typeof(b.className)!=="undefined"){if(a===1){a="btnhov"}else{a="btn"}b.className=a}}function get_dhxWinsParentObj(){if(typeof get_dhxWinsParentObj.myobject=="undefined"){get_dhxWinsParentObj.myobject=new dhtmlXWindows();get_dhxWinsParentObj.myobject.setSkin(gDhtmlxwindowsSkin);get_dhxWinsParentObj.myobject.setImagePath(appIncludePath+"js/dhtmlxwindows/codebase/imgs/")}return get_dhxWinsParentObj.myobject}function window_size_array(){var b=0;var a=0;b=$(window).width();a=$(window).height();return{width:b,height:a}}function viewscreen_center(e,c){var d=0;var b=0;var a=0;var g=0;g=$(window).scrollTop();a=$(window).scrollLeft();var f=window_size_array();d=((f.width-e)/2)+a;b=((f.height-c)/2)+g;if(d<0){d=0}if(b<0){b=0}return{x:d,y:b}}function newDhtmlWindow(b,e,c,a,f){var d="dhtmlwin_"+f;if(!isString(f)){d="winid"+Math.random()}return create_dhtmlxwindow(d,e,c,a,b,true)}function newDhtmlWindowTop(b,d,c,a,e){return newDhtmlWindow(b,d,c,a,e)}function newDhtmlWindow_text(e,b,a,f,d){var c=newDhtmlWindow("",e,b,a,f);if(typeof c!="undefined"){c.attachHTMLString(d);doJscriptProcess(d);return c}}function newIframeWindow(b,e,c,a,f){var d="dhtmlwin_"+f;if(typeof f=="undefined"){d="winid"+Math.random()}return create_dhtmlxwindow(d,e,c,a,b,false)}function create_dhtmlxwindow(l,m,c,n,b,p){var a=parseInt(n)+gDhtmlxwindowsExtraheight;var e=parseInt(c);var d=get_dhxWinsParentObj();var k=d.window(l);var g;var h=false;if(typeof b!="undefined"&&b!=""&&p){h=ajaxGetpage("",b,false);if(h==false){return false}}if(typeof(create_dhtmlxwindow.alter_width_flag)==="undefined"){create_dhtmlxwindow.alter_width_flag=true}var o=$(window).height();if(a>o){a=o;if(a<300){a=300}}if(create_dhtmlxwindow.alter_width_flag){var f=$(window).width();if(e>f){e=f;if(e<300){e=300}}}else{create_dhtmlxwindow.alter_width_flag=true}g=viewscreen_center(e,a);if(!isDhtmlxObject(k)){k=d.createWindow(l,g.x,g.y,e,a)}else{k.setDimension(e,a);k.setPosition(g.x,g.y)}if(!isDhtmlxObject(k)){alert(gGeneralJavasriptError);exit}k.cms_width=e;k.cms_height=a;if(k.isOnTop()==false){k.bringToTop()}k.setText(m);k.show();if(h!=false){k.attachHTMLString(h);doJscriptProcess(h)}else{k.attachURL(b,false)}var j=d._engineGetWindowHeader(k);if(typeof j.ondblclick!="undefined"){j.ondblclick=null}return k}function modDhtmlWindow(d,c){var b=get_dhxWinsParentObj();var a=b.window("dhtmlwin_"+d);if(typeof a=="undefined"){return}if(c==1){a.close()}else{if(c==2){a.hide()}else{if(c==3){a.show()}}}}function getDhtmlWindowById(c){var a=get_dhxWinsParentObj();var b=a.window("dhtmlwin_"+c);if(typeof b!="undefined"&&b!=null){return b}return null}function winobjAttachHtml(b,a){if(isDhtmlxObject(b)){b.attachHTMLString(a);doJscriptProcess(a);return true}return false}function resizeDhtmlWindow(h,g,c,f){var a=0,b=0;if((!isInteger(c)&&!isInteger(g))||!isString(h)){return false}if(typeof(f)=="undefined"){f=false}var d=getDhtmlWindowById(h);if(isDhtmlxObject(d)){if(f){if(isInteger(c)&&isInteger(g)){b=g;a=c}}else{var e=d.getDimension();if(isArray(e)&&e.length==2){a=e[0];b=e[1];if(isInteger(g)){b+=g}if(isInteger(c)){a+=c}}}if(b>0&&a>0){d.setDimension(a,b);return true}}return false};