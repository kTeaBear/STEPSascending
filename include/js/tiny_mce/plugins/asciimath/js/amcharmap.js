tinyMCEPopup.requireLangPack();var waitforAMTcgiloc=true;var AsciimathDialog={init:function(){AMTcgiloc=tinyMCEPopup.getWindowArg("AMTcgiloc")},set:function(a){tinyMCEPopup.restoreSelection();tinyMCEPopup.editor.execCommand("mceAsciimath",a);tinyMCEPopup.close()}};tinyMCEPopup.onInit.add(AsciimathDialog.init,AsciimathDialog);