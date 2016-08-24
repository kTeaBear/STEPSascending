(function(){tinymce.PluginManager.requireLangPack("asciimath");tinymce.create("tinymce.plugins.AsciimathPlugin",{init:function(a,b){var c=this;a.addCommand("mceAsciimath",function(d){if(c.lastAMnode==null){existing=a.selection.getContent();if(existing.indexOf("class=AM")==-1){existing=existing.replace(/<([^>]*)>/g,"");existing=existing.replace(/&(.*?);/g,"$1");if(d){existing=d}entity="<span class=AMedit>`"+existing+'<span id="removeme"></span>`</span> ';if(tinymce.isIE){a.focus()}a.selection.setContent(entity);a.selection.select(a.dom.get("removeme"));a.dom.remove("removeme");a.selection.collapse(true);a.nodeChanged()}}else{if(d){a.selection.setContent(d)}}});a.addCommand("mceAsciimathDlg",function(){if(typeof AMTcgiloc=="undefined"){AMTcgiloc=""}a.windowManager.open({file:b+"/amcharmap.htm",width:630+parseInt(a.getLang("asciimathdlg.delta_width",0)),height:390+parseInt(a.getLang("asciimathdlg.delta_height",0)),inline:1},{plugin_url:b,AMTcgiloc:AMTcgiloc})});a.onKeyPress.add(function(d,f){var e=String.fromCharCode(f.charCode||f.keyCode);if(e=="`"){if(c.lastAMnode==null){existing=d.selection.getContent();if(existing.indexOf("class=AM")==-1){existing=existing.replace(/<([^>]*)>/g,"");entity="<span class=AMedit>`"+existing+'<span id="removeme"></span>`</span> ';if(tinymce.isIE){d.focus()}d.selection.setContent(entity);d.selection.select(d.dom.get("removeme"));d.dom.remove("removeme");d.nodeChanged()}}if(f.stopPropagation){f.stopPropagation();f.preventDefault()}else{f.cancelBubble=true;f.returnValue=false}}});a.addButton("asciimath",{title:"asciimath.desc",cmd:"mceAsciimath",image:b+"/img/ed_mathformula2.gif"});a.addButton("asciimathcharmap",{title:"asciimathcharmap.desc",cmd:"mceAsciimathDlg",image:b+"/img/ed_mathformula.gif"});a.onPreInit.add(function(d){if(tinymce.isIE){addhtml='<object id="mathplayer" classid="clsid:32F66A20-7614-11D4-BD11-00104BD3F987"></object>';addhtml+='<?import namespace="m" implementation="#mathplayer"?>';d.dom.doc.getElementsByTagName("head")[0].insertAdjacentHTML("beforeEnd",addhtml)}});a.onPreProcess.add(function(e,g){if(g.get){AMtags=e.dom.select("span.AM",g.node);for(var f=0;f<AMtags.length;f++){c.math2ascii(AMtags[f])}AMtags=e.dom.select("span.AMedit",g.node);for(var f=0;f<AMtags.length;f++){var d=AMtags[f].innerHTML;d="`"+d.replace(/\`/g,"")+"`";AMtags[f].innerHTML=d;AMtags[f].className="AM"}}});a.onLoadContent.add(function(d,f){AMtags=d.dom.select("span.AM");for(var e=0;e<AMtags.length;e++){c.nodeToAM(AMtags[e])}});a.onBeforeExecCommand.add(function(d,f){if(f!="mceAsciimath"&&f!="mceAsciimathDlg"){AMtags=d.dom.select("span.AM");for(var e=0;e<AMtags.length;e++){c.math2ascii(AMtags[e]);AMtags[e].className="AMedit"}}});a.onExecCommand.add(function(d,f){if(f!="mceAsciimath"&&f!="mceAsciimathDlg"){AMtags=d.dom.select("span.AMedit");for(var e=0;e<AMtags.length;e++){c.nodeToAM(AMtags[e]);AMtags[e].className="AM"}}});a.onNodeChange.add(function(f,d,g){var h=true;if(c.testAMclass(g)){p=g}else{p=f.dom.getParent(g,c.testAMclass)}d.setDisabled("charmap",p!=null);d.setDisabled("sub",p!=null);d.setDisabled("sup",p!=null);if(p!=null){if(c.lastAMnode==p){h=false}else{c.math2ascii(p);p.className="AMedit";if(c.lastAMnode!=null){c.nodeToAM(c.lastAMnode);c.lastAMnode.className="AM"}c.lastAMnode=p;h=false}}if(h&&(c.lastAMnode!=null)){if(c.lastAMnode.innerHTML.match(/`(&nbsp;|\s|\u00a0|&#160;)*`/)||c.lastAMnode.innerHTML.match(/^(&nbsp;|\s|\u00a0|&#160;)*$/)){p=c.lastAMnode.parentNode;p.removeChild(c.lastAMnode)}else{c.nodeToAM(c.lastAMnode);c.lastAMnode.className="AM"}c.lastAMnode=null}});a.onDeactivate.add(function(d){if(c.lastAMnode!=null){if(c.lastAMnode.innerHTML.match(/`(&nbsp;|\s)*`/)||c.lastAMnode.innerHTML.match(/^(&nbsp;|\s|\u00a0|&#160;)*$/)){p=c.lastAMnode.parentNode;p.removeChild(c.lastAMnode)}else{c.nodeToAM(c.lastAMnode);c.lastAMnode.className="AM"}c.lastAMnode=null}})},getInfo:function(){return{longname:"Asciimath plugin",author:"David Lippman",authorurl:"http://www.pierce.ctc.edu/dlippman",infourl:"",version:"1.0"}},math2ascii:function(b){var a=b.innerHTML;if(a.indexOf("`")==-1){a=a.replace(/.+(alt|title)=\"(.*?)\".+/g,"$2");a=a.replace(/.+(alt|title)=\'(.*?)\'.+/g,"$2");a=a.replace(/.+(alt|title)=([^>]*?)\s.*>.*/g,"$2");a=a.replace(/.+(alt|title)=(.*?)>.*/g,"$2");a=a.replace(/>/g,"&gt;");a=a.replace(/</g,"&lt;");a="`"+a.replace(/\`/g,"")+"`";b.innerHTML=a}},nodeToAM:function(d){if(tinymce.isIE){var b=d.innerHTML.replace(/\`/g,"");b.replace(/\"/,"&quot;");var c=document.createElement("span");if(AMnoMathML){c.appendChild(AMTparseMath(b))}else{c.appendChild(AMparseMath(b))}d.innerHTML=c.innerHTML}else{var a="`"+d.innerHTML.replace(/\`/g,"")+"`";d.innerHTML=a;AMprocessNode(d)}},lastAMnode:null,preventAMrender:false,testAMclass:function(a){if((a.className=="AM")||(a.className=="AMedit")){return true}else{return false}}});tinymce.PluginManager.add("asciimath",tinymce.plugins.AsciimathPlugin)})();