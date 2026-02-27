# Module


## Frontend

* `MannschaftsseitenReader`
  * Konfiguration: keine
  * Dieses Modul wird auf einer eigenen Seite eingebaut, die dann in
    System → Einstellungen unter Mannschaftsseite angegeben wird.

* `SpielberichtReader`
  * Konfiguration: keine
  * Dieses Modul wird auf einer eigenen Seite eingebaut, die dann in
    System → Einstellungen unterSpielberichtsseite angegeben wird.

* `Spielortseitenreader`
  * Konfiguration: keine
  * Dieses Modul ist nur experimentell!
  * Alternativ: Contao-Standard-Moduel "Auflistung" der Tabelle `tl_spielort` einsetzen
  (mit selbst erstelltem/modifizertem Template `list_default_spielorte`).


## Backed (nur technisch)

* `BegegnungDataEntryWidget` (Formular mit `Vue.js`; versteckt; wird in Begegnungsansicht verwendet "erfassen").
Siehe das eigenständige [Github Repository](https://github.com/fiedsch/begegnungserfassung) dafür.

* `PlayerHistoryController` (wird in Mitgliederansicht verwendet, um anzuzeigen, in welchen Mannschaften ein Mitglied
  in der Vergangenheit gespielt hat ("eigener Button").

