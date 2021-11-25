/**
 * 	Datei	  	: 	app.js 
 * 	Zweck	  	: 	Die Hauptapplikation
 * 	Version		: 	14.09.2020 
 * 	Author		:	Volker A Mönch 	 
 *  Historie:   : 				500 -> 5500, _510 -> 5510
 * 					25.03.2021 	und wieder zurück auf 500 und 510
 * 					10.05.2021	Pause hinzugefügt
 */

/**
 * Variablen, Konstanten 
 */


var FormElementsZeiterfassung = [
	
	{ view:"text", label:'Mitarbeiter', name:"username",disabled:true },
	{ view:"text", label:'Status', name:"status",disabled:true },
	{ view:"text", label:'bde_funktion', name:"bde_funktion",disabled:true ,hidden:true},
	{ view:"text", label:'berech_unter_fkt', name:"berech_unter_fkt",disabled:true ,hidden:true },	
	{ view:"text", label:'userlogin', name:"userlogin",disabled:true ,hidden:true},

	{ view:"button",css:"webix_primary",  value: "Kommen", click:function(){
		recWorkStart();
	}},
	
	{ view:"button",css:"webix_primary",  value: "Gehen", click:function(){
		recWorkEnd();
	}},
	
	{ view:"button", css:"webix_primary", value: "Dienstgang Beginn", click:function(){
		recServiceStart();
	}},
	
	{ view:"button", css:"webix_primary", value: "Dienstgang Ende", click:function(){
		recServiceEnd();
	}},
	{ view:"button", css:"webix_primary", value: "Abmelden", click:function(){
		logoff();
	}},
	{ view:"button", css:"webix_transparent", value: "Passwort &auml;ndern",  borderless:true,  click:function(){
		changePassword();
	}}
];

var FormElementsInformation =[
	{ view:"text", label:'Anwesend?', name:"anwesend",disabled:true},
	{ view:"text", label:'Arbeitszeit Vormonat', name:"vormonat",format:"1,111.00 h",disabled:true },
	{ view:"text", label:'Ist-Arbeitszeit lfd. Monat', name:"monat",format:"1,111.00 h" ,disabled:true},
	{ view:"text", label:'Soll-Arbeitszeit lfd. Monat', name:"sollarbeitszeit",format:"1,111.00 h",disabled:true },
	{ view:"text", label:'Mehr/Minderarbeit', name:"mehrminderarbeit",format:"1,111.00 h" ,disabled:true},
	{ template:"Ihr Urlaubskonto", type:"section"},
	{ view:"text", label:'Anspr&uuml;che f&uuml;r Jahr', name:"jahr",format:"11",disabled:true } ,
	{ view:"text", label:'Urlaubsanspruch Jahr', name:"anspruch",format:"1,111.00 h",disabled:true },
	{ view:"text", label:'Bisher genommener Urlaub', name:"genommen" ,format:"1,111.00 h",disabled:true},
	{ view:"text", label:'Restanspruch aus Vorjahr', name:"vorjahr",format:"1,111.00 h" ,disabled:true},
	{ view:"text", label:'Resturlaub aktuell', name:"rest",format:"1,111.00 h" ,disabled:true},
	{ view:"text", label:'Zuk&uuml;nftig geplanter Urlaub', name:"geplant",format:"1,111.00 h",disabled:true },
	{ view:"text", label:'Verf&uuml;gbar', name:"verfuegbar",format:"1,111.00 h",disabled:true },
	{ view:"text", label:'Saldo Gleitzeitkonto', name:"saldo_ueberstunden",format:"1,111.00 h" ,disabled:true},

];
	
/**
 * 
 * Anwendung  
 * 
 */

SessionLoad();
var sessiondata;
var data;

webix.ui.fullScreen();
webix.ready(function(){
	webix.ui({
		id:"views",	
		container: "app",
		width:330,
		view:"accordion",
		rows:[
			{
				view:"accordionitem",
				header:"Stempeln",
				headerAlt:"Stempeln",
				collapsed: false,
				body:{ 	view:"form",  hidden:false, id:"FormElementsZeiterfassung", scroll:false, width:0,padding:{
					top:5, bottom:0, left:2, right:28
				  },
						elements: FormElementsZeiterfassung,
						rules:{
							// "userlogin":webix.rules.isNotEmpty
						},
						elementsConfig:{
						 labelPosition:"top"
						 
					} ,				
				data:sessiondata }

			},{
				
				view:"accordionitem",
				id:"stempelungen",
				header:"Ihre heutigen Stempelungen",
				headerAlt:"Stempelungen heute",
				collapsed: true,
				width:360,
				body:{
					id:"buchungen",
					view:"datatable",
					columns:[
						{ id:"bdefunktion",	header:"Stempelung",width:100},
						{ id:"zeit"       , header:"Zeit", 			width:70, css:{'text-align':'left'}  ,footer:{text:"Summe"} },
						{ id:"dauer"      ,	header:"Dauer (m)", 	width:70, css:{'text-align':'right'} , footer:{ content:"summColumn", css:{'text-align':'right'} } },
						{ id:"pause"      ,	header:"Pause (m)", 	width:60, css:{'text-align':'right'} , footer:{ content:"summColumn", css:{'text-align':'right'} } }
					],
					autoheight:true,
					autowidth:true,
					footer:true
					 				
				} // body 

			},
			{
				view:"accordionitem",
				id:"info",
				header:"Informationen ",
				headerAlt:"Information",
				collapsed: true,
				
				body:{
					view:"form",  
					hidden:false, 
					id:"FormElementsInformation", 
					scroll:false, 
					width:400, 
					elements: FormElementsInformation,
					rules:{
						// "userlogin":webix.rules.isNotEmpty
					},
					elementsConfig:{
						labelWidth: 200
					},
					padding:{
						 left:2, right:38
					}
	
			}
		
			}
		]
	});	// webix.ui({
					
	//-----------------------------------------------------------------------------
	// Daten laden 
	//-----------------------------------------------------------------------------	

	// userlogin = webix.storage.session.get("userlogin");
	$$('buchungen').load("./server/stempelungen.php?userlogin=" + webix.storage.session.get("userlogin")).then(function(data){
		
	});
	$$('FormElementsInformation').load("./server/info.php?userlogin=" + webix.storage.session.get("userlogin")).then(function(data){
		
	});

}) // webix.ready({

/**
 * 
 * 
 *				 Funktionen 
 * 
 * 
 */

//-----------------------------------------------------------------------------
// Session
//-----------------------------------------------------------------------------

function SessionLoad(){
	sessiondata = { id:1, userlogin:webix.storage.session.get("userlogin"), username:webix.storage.session.get("username"), status:webix.storage.session.get("status")} ;
}

//-----------------------------------------------------------------------------
// Authentifizierung 
//-----------------------------------------------------------------------------

function login(){

	// 1.) Formulardaten
	userlogin 		= $$("FormLogin").elements.userlogin.getValue();
	userpasswort	= $$("FormLogin").elements.userpasswort.getValue();

	// 2.) Datenbankdaten 
	$url   = "./server/user.php?userlogin="+ userlogin + "&userpasswort=" + userpasswort ;
	
	// 3.) Daten verwenden 
	$$("FormElementsZeiterfassung").load($url).then(function(data){
		data = data.json();
		if ( data.userlogin != 'FALSCH'){
			webix.storage.session.put("userlogin",data.userlogin);
			webix.storage.session.put("username",data.username);
			
			// Ergebniss in Session schreiben 
			setStatus("jetzt","eingeloggt");

			if (data.bde_funktion == 500){
				setStatus(data.stempelzeit,"Dienstbeginn");
			} else if (data.bde_funktion == 510) {
				setStatus(data.stempelzeit,"Dienstende");
			} else if (data.bde_funktion == 520 && data.berech_unter_fkt == 10  ) {		
				setStatus(data.stempelzeit,"Dienstgang Anfang");
			} else if (data.bde_funktion == 520 && data.berech_unter_fkt == 20  ) {				
				setStatus(data.stempelzeit,"Dienstgang Ende");
			}
				

		} else {
			webix.message({type:"error", text:"Sie haben falsche Zugangsdaten eingegeben!"});
			logoff();
			return false; 
		}
	});
}

function logoff(){
	webix.storage.session.clear();
	$$("FormElementsZeiterfassung").clear();
	webix.message("Sie sind abgemeldet.");
	window.location.href = "login.html";
} 

function setStatus(zeit,status){
	if (zeit == 'jetzt') {
		jetzt = new Date(Date.now());
	} else {
		jetzt = new Date(zeit); 
	}
	var time = jetzt.getHours() + ":" + addZero(jetzt.getMinutes()) ;
		
	var statusstring = 'Um ' +time + ' ' + status;
	webix.storage.session.put("status", statusstring);
 
	$$("FormElementsZeiterfassung").setValues({
		status: statusstring
	},true);
}

function changePassword (){

	window.location.href = "password.html";
}

//-----------------------------------------------------------------------------
// Buchungen 
//-----------------------------------------------------------------------------
// 16.12.2020 Auf Wunsch von Herr Greven _500 nun 5500 und 510 nun 5510 
// 24.03.2021 Wieder zurück
                            
function recWorkStart(){
	// Eintrag in bde_receive, BDE-Funktion _500
	if (buchen(500,0)) {
		webix.message("Ihre Arbeit beginnt.");
		setStatus("jetzt","Dienstbeginn");
	}
};
	
function recWorkEnd(){
	// Eintrag in bde_receive, BDE-Funktion 510
	if (buchen(510,0)) {
		webix.message("Ihre Arbeit endet.");
		setStatus("jetzt","Dienstende");
	}
};

function recServiceStart(){
	// Eintrag in bde_receive, BDE-Funktion 520
	if (buchen(520,10)) {
		webix.message("Ihre Arbeit endet.");
		setStatus("jetzt","Dienstgangbeginn");
	}

};

function recServiceEnd(){
		// Eintrag in bde_receive, BDE-Funktion 520,100
		if (buchen(520,20)) {
			webix.message("Ihre Arbeit endet.");
			setStatus("jetzt","Dienstgangbeginn");
		}
};

function buchen(bde_funktion,berech_unter_fkt) { 

	/**
	 * Die Werte der Buchung, werden vollständig aus dem Formular entnommen 
	 * Nur ob überhaupt wie geplant gestempelt werden darf, wird direkt in 
	 * der Datenbank ermittelt 
	 */
	
	$$("FormElementsZeiterfassung").setValues({
		bde_funktion:bde_funktion,
		berech_unter_fkt:berech_unter_fkt
	},true);

	// Doppelten Dienstbeginn prüfen 
	webix.ajax().post("server/pruefen.php", $$('FormElementsZeiterfassung').getValues() , function(text, data, XmlHttpRequest) {
		data = data.json();
		if(data.text.substring(0,3) == "NOK") {
			// webix.message({type:"error", text:"Dies ist eine ung&uuml;ltige Stempelung:<br><br><b>" + data.text.substring(5,60) }); 
			webix.alert({ title:"Dies ist eine ung&uuml;ltige Stempelung:<br><br>" + data.text.substring(5,60), type:"alert-error", id:"1" });
		} else {
		
			// Stempeln 
			var Hinweis; 
			var Titel; 

			webix.ajax().post("server/buchen.php", $$('FormElementsZeiterfassung').getValues() , function(text, data, XmlHttpRequest) {
				data = data.json();
				webix.message({type:data.type, text:data.text}); // data.status enthält temporäre ID
				if (bde_funktion == 500){
					setStatus("jetzt","Dienstbeginn");
				} else if (bde_funktion == 510) {
					setStatus("jetzt","Dienstende");
				} else if (bde_funktion == 520 && berech_unter_fkt == 10  ) {		
					setStatus("jetzt","Dienstgang Anfang");
				} else if (bde_funktion == 520 && berech_unter_fkt == 20  ) {				
					setStatus("jetzt","Dienstgang Ende");
				}
				window.location.reload();
			});
			return true ;
				

		} // if(data.text == "NOK") {
	});
};

function addZero(i) {
	if (i < 10) {
	  i = "0" + i;
	}
	return i;
}
