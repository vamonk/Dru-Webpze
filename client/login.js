/**
 * 	Datei	: 	login.js 
 * 	Zweck	: 	Den Loginprozess abbilden 
 * 	Beginn	: 	31.08.2020
 * 	Author	:	Volker A Mönch 	 
 *  Version : 	24.09.2020
 * 
 */


/**
 * Variablen 
 */
 
var FormElementsLogin = [
	{ view:"text", label:'Login', name:"userlogin" },
	{ view:"text", label:'Passwort', name:"userpasswort", type:"password", on:{"onKeyPress":function(code,e){ 
		if (code == 13) {
		var form = this.getParentView(); if (form.validate()) {
			login();						
		}else {
			webix.message({ type:"error", text:"Eingegebene Daten sind falsch" });
		} } } } },
	{ view:"button", value: "Anmelden", click:function(){
		var form = this.getParentView();
		if (form.validate()) {
			login();						
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
			id:"FormLogin", 
			scroll:false, 
			width:330, 
			elements: FormElementsLogin,
			rules:{
				"userlogin":webix.rules.isNotEmpty
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

		$$("FormLogin").focus("userlogin");

	
}) // webix.ready({

/**
 * Funktionen 
 */

// Mitarbeiter anmelden 
function login(){

	erfolg = 0; 

	// 1.) Formulardaten
	userlogin 		= $$("FormLogin").elements.userlogin.getValue();
	userpasswort	= $$("FormLogin").elements.userpasswort.getValue();

	// 2.) Datenbankdaten 
	$url   = "./server/user.php?userlogin="+ userlogin + "&userpasswort=" + userpasswort ;
	
	// 3.) Daten verwenden 
	$$("FormLogin").load($url).then(function(data){
		data = data.json();
		if ( data.userlogin != '   '){
			webix.storage.session.put("userlogin",data.userlogin);
			webix.storage.session.put("username",data.username);
			
			// Ergebniss in Session schreiben 
			setStatus("jetzt","eingeloggt");

			webix.message("Sie haben sich erfolgreich angemeldet.");
			window.location.href = "app.html";
		} else {
			webix.alert({ title:"Anmeldung ist fehlgeschlagen!<br><br><small>Sie haben vermutlich falsche Zugangsdaten eingegeben.</snall>" , type:"alert-error", id:"1" });
			webix.storage.session.clear();	
			$$('FormLogin').clear();
			
			erfolg = 0; 
		}
	});
	return erfolg;
}

// Nach dem Login in die Session eintragen 
function setStatus(zeit,status){
	if (zeit == 'jetzt') {
		jetzt = new Date(Date.now());
	} else {
		jetzt = new Date(zeit); // jetzt = new Date('2011-04-11T12:20:30');
	}
	var sTime = jetzt.getHours() + ":" + addZero(jetzt.getMinutes()) ;
		
	var statusstring = 'Um ' + sTime + ' ' + status;
	webix.storage.session.put("status", statusstring);
	
}
function addZero(i) {
	if (i < 10) {
	  i = "0" + i;
	}
	return i;
  }
