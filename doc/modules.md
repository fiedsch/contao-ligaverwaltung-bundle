# Module


## Frontend

* `ModuleMannschaftsseitenReader`
  * Konfiguration: keine
  * Dieses Modul wird auf einer eigenen Seite eingebaut, die dann in 
    System → Einstellungen unter Mannschaftsseite angegeben wird.
     
* `ModuleSpielberichtReader`
  * Konfiguration: keine
  * Dieses Modul wird auf einer eigenen Seite eingebaut, die dann in 
    System → Einstellungen unterSpielberichtsseite angegeben wird.
    
* `ModuleSpielortseitenreader`
  * Konfiguration: keine
  * Dieses Modul ist nur experimentell und wird noch nirgends verwendet!
  * Alternativ: Contao-Standard-Moduel "Auflistung" der Tabelle `tl_spielort` einsetzen
  (mit selbst erstelltem/modifizertem Templete `list_default_spielorte`).
 


## Backed (nur technisch)

* `ModuleBegegnungserfassung` (Formular mit `Vue.js`; versteckt; wird in Begegnungsansicht verwendet "erfassen").
Siehe das eigenständige [Github Repository](https://github.com/fiedsch/begegnungserfassung) dafür.

* `ModuleSpielerHistory` (versteckt; wird in Mitgliederansicht verwendet "eigener Button")

