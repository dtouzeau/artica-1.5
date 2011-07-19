// + --------------------------------------------------------------------------------------
// + XHRConnection
// + V1.3
// + Thanh Nguyen, http://www.sutekidane.net
// + 20.10.2005
// + http://creativecommons.org/licenses/by-nc-sa/2.0/fr/deed.fr
// + --------------------------------------------------------------------------------------
// + Modifications par Vincent Rémond (2007.03.08-08:56) pac1250@gmail.com
// + --------------------------------------------------------------------------------------
function XHRConnection() {
    
    // + ----------------------------------------------------------------------------------
    var conn = false;
    var debug = false;
    var datas = new String();
    var areaId = new String();
    var response = new String();
    // Objet XML
    var xmlObj;
    // Type de comportement au chargement du XML
    var xmlLoad;

    // + ----------------------------------------------------------------------------------
    try {
        conn = new XMLHttpRequest();        
    }
    catch (error) {
        if (debug) { alert('Erreur lors de la tentative de création de l\'objet \nnew XMLHttpRequest()\n\n' + error); }
        try {
            conn = new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch (error) {
            if (debug) { alert('Erreur lors de la tentative de création de l\'objet \nnew ActiveXObject("Microsoft.XMLHTTP")\n\n' + error); }
            try {
                conn = new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch (error) {
                if (debug) { alert('Erreur lors de la tentative de création de l\'objet \nnew ActiveXObject("Msxml2.XMLHTTP")\n\n' + error); }
                conn = false;
            }
        }
    }
    
    // + ----------------------------------------------------------------------------------
    // + setDebugOff
    // + Désactive l'affichage des exceptions
    // + ----------------------------------------------------------------------------------
    this.setDebugOff = function() {
        debug = false;
    };

    // + ----------------------------------------------------------------------------------
    // + setDebugOn
    // + Active l'affichage des exceptions
    // + ----------------------------------------------------------------------------------
    this.setDebugOn = function() {
        debug = true;
    };
    
    // + ----------------------------------------------------------------------------------
    // + resetData
    // + Permet de vider la pile des données
    // + ----------------------------------------------------------------------------------
    this.resetData = function() {
        datas = new String();
        datas = '';
    };
    
    // + ----------------------------------------------------------------------------------
    // + appendData
    // + Permet d'empiler des données afin de les envoyer
    // + ----------------------------------------------------------------------------------
    this.appendData = function(pfield, pvalue) {
        datas += (datas.length == 0) ? pfield+ "=" + escape(pvalue) : "&" + pfield + "=" + escape(pvalue);
    };
    
    // + ----------------------------------------------------------------------------------
    // + setRefreshArea
    // + Indique quel elment identifié par id est valoris lorsque l'objet XHR reoit une réponse
    // + ----------------------------------------------------------------------------------
    this.setRefreshArea = function(id) {
        areaId = id;
    };
    
    // + ----------------------------------------------------------------------------------
    // + createXMLObject
    // + Méthode permettant de créer un objet DOM, retourne la réfrence
    // + Inspiré de: http://www.quirksmode.org/dom/importxml.html
    // + ----------------------------------------------------------------------------------
    this.createXMLObject = function() {
        try {
                 xmlDoc = document.implementation.createDocument("", "", null);
                xmlLoad = 'onload';
        }
        catch (error) {
            try {
                xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
                xmlLoad = 'onreadystatechange ';
            }
            catch (error) {
                if (debug) { alert('Erreur lors de la tentative de création de l\'objet XML\n\n'); }
                return false;
            }
        }
        return xmlDoc;
    }
    
    // + ----------------------------------------------------------------------------------
    // + Permet de définir l'objet XML qui doit être valorisé lorsque l'objet XHR reoit une réponse
    // + ----------------------------------------------------------------------------------
    this.setXMLObject = function(obj) {
        if (obj == undefined) {
                if (debug) { alert('Paramètre manquant lors de l\'appel de la méthode setXMLObject'); }
                return false;
        }
        try {
            //xmlObj = this.createXMLObject();
            xmlObj = obj;
        }
        catch (error) {
                if (debug) { alert('Erreur lors de l\'affectation de l\'objet XML dans la méthode setXMLObject'); }
        }
    }
    
    // + ----------------------------------------------------------------------------------
    // + loadXML
    // + Charge un fichier XML
    // + Entrées
    // +     xml            String        Le fichier XML à charger
    // + ----------------------------------------------------------------------------------
    this.loadXML = function(xml, callBack) {
        if (!conn) return false;
        // Chargement pour alimenter un objet DOM
        if (xmlObj && xml) {
            if (typeof callBack == "function") {
                if (xmlLoad == 'onload') {
                    xmlObj.onload = function() {
                        callBack(xmlObj);
                    }
                }
                else {
                    xmlObj.onreadystatechange = function() {
                        if (xmlObj.readyState == 4) callBack(xmlObj)
                    }
                }
            }
            xmlObj.load(xml);
            return;
        }        
    }
    // + ----------------------------------------------------------------------------------
    // + sendAndLoad
    // + Connexion à la page désirée avec envoie des données, puis mise en attente de la réponse
    // + Entrées
    // +     Url            String        L'url de la page à laquelle l'objet doit se connecter
    // +     httpMode        String        La méthode de communication HTTP : GET, HEAD ou POST
    // +     callBack        Objet            Le nom de la fonction de callback
    // +     pamar            Objet            Paramètre à passer à la fonction callback.
    // + ----------------------------------------------------------------------------------
    this.sendAndLoad = function(Url, httpMode, callBack,param) {
        conn.onreadystatechange = function() {
            if (conn.readyState == 4 && conn.status == 200) {
                // Si une fonction de callBack a été définie
                if (typeof callBack == "function") {
                    callBack(conn,param);
                    return;
                }
                // Si une zone destinée à récupérer le résultat a été définie
                else if (areaId.length > 0){
                    try {
                        document.getElementById(areaId).innerHTML = conn.responseText;
                    }
                    catch(error) {
                        if (debug) { alert('Echec, ' + areaId + ' n\'est pas un objet valide'); }
                    }
                    return;
                }
            }
        };
        return this.load(Url, httpMode, true);
    };

    // + ----------------------------------------------------------------------------------
    // + sendAndLoad
    // + Connexion à la page désirée avec envoie des données, puis attente de la réponse
    // + Entrées
    // +     Url            String        L'url de la page à laquelle l'objet doit se connecter
    // +     httpMode        String        La méthode de communication HTTP : GET, HEAD ou POST
    // + ----------------------------------------------------------------------------------
    
    this.sendAndWait = function(Url, httpMode){
        if(this.load(Url, httpMode, false)){
            response = conn.responseText;
            return true;
        }
        return false;
    }

    // + ----------------------------------------------------------------------------------
    // + sendAndLoad
    // + Connexion à la page désirée.
    // + Entrées
    // +     Url            String        L'url de la page à laquelle l'objet doit se connecter
    // +     httpMode        String        La méthode de communication HTTP : GET, HEAD ou POST
    // +     async        Boolean        true pour ne pas attendre la reponse (la reponse sera,
    // +                             traitée par callback) false sinon.
    // + ----------------------------------------------------------------------------------
    
    this.load = function (Url, httpMode, async) {
        httpMode = httpMode.toUpperCase();
        switch(httpMode) {
            case "GET":
                try {
                    Url = (datas.length > 0) ? Url + "?" + datas : Url;
                    conn.open("GET", Url, async);
                    conn.send(null);
                    if(!async){
                        this.response = conn.responseText;
                    }
                }
                catch(error) {
                    if (debug) { alert('Echec lors de la transaction avec ' + Url + ' via la méthode GET.'+error.message); }
                    return false;
                }
            break;
            case "POST":
                try {
                    conn.open("POST", Url, async); 
                    conn.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    conn.send(datas);
                    if(!async){
                        this.response = conn.responseText;
                    }
                }
                catch(error) {
                    if (debug) { alert('Echec lors de la transaction avec ' + Url + ' via la méthode POST.'+error.description ); }
                    return false;
                }
            break;
            default :
                return false;
            break;
        }
        return true;
    }
    return this;
}