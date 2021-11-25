/**
 * 	Datei	: 	password.js 
 * 	Zweck	: 	Passwortändernung  
 * 	Beginn	: 	24.09.2020
 * 	Author	:	Volker A Mönch 	 
 *  Version : 	24.09.2020 08:48
 */


/**
 * Variablen 
 */
 
var FormElementsLogin = [
	{ view:"text", label:'Benutzername', name:"userlogin", disabled:true },
	{ view:"text", label:'Neues Passwort', name:"passnew",type:"password" },
	{ view:"text", label:'Passwort best&auml;tigen', name:"passchk", type:"password" },
	{ view:"button", value: "Passwort &auml;ndern", click:function(){
		var form = this.getParentView();
		if (form.validate()) {
			Passwordchange();		
		}else {
			webix.message({ type:"error", text:"Eingegebene Daten sind falsch" });
		}	
	}}
];

/**
 * Anwendung  
 */

webix.ui.fullScreen();
webix.ready(function(){
	webix.ui({
		
			view:"form",  
			container:"app",
			
			hidden:false, 
			id:"FormPassword", 
			scroll:false, 
			width:330, 
			elements: FormElementsLogin,
			rules:{
				"passnew":webix.rules.isNotEmpty
			},
			elementsConfig:{
				labelPosition:"top"
			},
			scroll:false,
			ready:function(){ 
				webix.message("ready");
			},
			collapsed: false
		
		
	});	// webix.ui({
		
		
		$$("FormPassword").focus("passnew");
 
		$$("FormPassword").setValues({
			userlogin: webix.storage.session.get("userlogin")
		},true);

	
}) // webix.ready({

/**
 * Funktionen 
 */

// Mitarbeiter anmelden 
function Passwordchange(){

	erfolg = 0; 
	passnew = $$("FormPassword").elements.passnew.getValue();
	passchk	= $$("FormPassword").elements.passchk.getValue();

	// Beide Eingaben identisch? 
	if (passnew != passchk){
		webix.alert({ title:"Ihre Eingaben sind nicht identisch.<br><small>Bitte wiederholen Sie den Vorgang.</small>" , type:"alert-error", id:"1" });
		erfolg = 0; 
		
	} else {

		webix.ajax().post("server/password.php", $$('FormPassword').getValues() , function(text, data, XmlHttpRequest) {
			data = data.json();
			if (data.type == 'error'){
				webix.alert({ title:data.text, type:"alert-error", id:"1" });
			} else {
				
				webix.confirm({
					title:"Ihr Passwort wurde ge&auml;ndert.",
					ok:"Ok", 
					cancel:"Abbruch"
				}).then(function(result){
					window.location.href = "app.html";
				}).fail(function(){
				  webix.message("Sie haben den Vorgang abgebrochen. Bitte vergeben Sie erneut Ihr Passwort.");
			  });
			}
		});
	}
}



