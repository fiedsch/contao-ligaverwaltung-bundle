# Inhaltselemente


## Listen

* `ContentMannschaftsliste` Liste aller Mannschaften, die in einer Liga aktiv sind. 
Optional mit Link zu einer Mannschaftsseite.
  * Konfiguration
    * Auswahl einer Liga

* `ContentMannschaftenuebersicht` Übersicht der Mannschaften einer Saison und je Mannschart die Teamcaptains.
Im Gegensatz zu `ContentMannschaftsliste` mehr Informationen als "nur" der (verlinkte) Mannschaftsname.
TODO (?) mit `ContentMannschaftsliste` zusammenführen.
  * Konfiguration
    * Auswahl einer Saison

* `ContentSpielerliste` Liste aller (aktiven) Spielrr einer Mannschaft.
  * Konfiguration 
    * Auswahl einer Mannschaft 
    * Details anzeigen (ja/nein)
  

* `ContentSpielbericht` Einzelergebnisse zu einer Begegnung
  * Konfiguration
    * Auswahl einer Begegnung 
 
* `ContentLigenliste` Liste aller Ligen (eines Verbands).
* Konfiguration
    * Auswahl Verband
    * Auswahl Saisons

* `ContentSpielplan` Liste aller Begegnungen in einer Liga sortiert nach Spieltagen
* Konfiguration
    * Auswahl Liga
    * Auswahl "alle Mannschaften" oder eine bestimmte Mannschaft 
 
* `ContentSpielortinfo` Informationen zu einem Spielort 
* Konfiguration
    * Auswahl Spielort

## Rankings

* `ContentRanking` Ranking von Mannschaften einer Liga.
* Konfiguration
    * Auswahl Liga
    * Auswahl "Mannschaften" oder "Spieler" Ranking
      * Bei "Spieler" zusätzlich Auswahl "alle Mannschaften" oder eine bestimmte Mannschaft

* `ContentHighlightRanking` Highlights wie (Anzahl 180er/171er, Highfinished, Shortlegs)
* Konfiguration
    * Auswahl Liga
    * Auswahl "Mannschaften" oder "Spieler" Ranking
      * Bei "Spieler" zusätzlich Auswahl "alle Mannschaften" oder eine bestimmte Mannschaft
    * Auswahl anzuzeigendes Highlight ("180+171", "Shortleg", "Highfinish")
FIXME: wird noch nirgends produktiv verwendet und ist nicht "fertig" implementiert!          


## Zusammenstellungen

* `ContentMannschaftsseite` Bündelt `ContentMannschaftsliste`, `ContentRanking` etc. damit diese
nicht je Mannschaft einzeln zusammengestellt werden müssen.
* Konfiguration
    * Auswahl Mannschaft 
 
* `ContentSpielortseite` Informationen zum Spielort (`ContentSpielortinfo`) und allen Mannschaften,
die dort spielen.
* Konfiguration
    * Auswahl Spielort
    * Auswahl Ligen
