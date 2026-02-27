# Inhaltselemente


## Listen

* `Mannschaftsliste` Liste aller Mannschaften, die in einer Liga aktiv sind.
Optional mit Link zu einer Mannschaftsseite.
  * Konfiguration
    * Auswahl einer Liga

* `Mannschaftenuebersicht` Übersicht der Mannschaften einer Saison und je Mannschart die Teamcaptains.
Im Gegensatz zu `Mannschaftsliste` mehr Informationen als "nur" der (verlinkte) Mannschaftsname.
TODO (?) mit `Mannschaftsliste` zusammenführen.
  * Konfiguration
    * Auswahl einer Saison

* `Spielerliste` Liste aller (aktiven) Spieler einer Mannschaft.
  * Konfiguration
    * Auswahl einer Mannschaft
    * Details anzeigen (ja/nein)


* `Spielbericht` Einzelergebnisse zu einer Begegnung
  * Konfiguration
    * Auswahl einer Begegnung

* `Ligenliste` Liste aller Ligen (eines Verbands).
* Konfiguration
    * Auswahl Verband
    * Auswahl Saisons

* `Spielplan` Liste aller Begegnungen in einer Liga sortiert nach Spieltagen
* Konfiguration
    * Auswahl Liga
    * Auswahl "alle Mannschaften" oder eine bestimmte Mannschaft

* `Spielortinfo` Informationen zu einem Spielort
* Konfiguration
    * Auswahl Spielort

## Rankings

* `Ranking` Ranking von Mannschaften einer Liga.
* Konfiguration
    * Auswahl Liga
    * Auswahl "Mannschaften" oder "Spieler" Ranking
      * Bei "Spieler" zusätzlich Auswahl "alle Mannschaften" oder eine bestimmte Mannschaft

* `HighlightRanking` Highlights wie (Anzahl 180er/171er, Highfinished, Shortlegs)
* Konfiguration
    * Auswahl Liga
    * Auswahl "Mannschaften" oder "Spieler" Ranking
      * Bei "Spieler" zusätzlich Auswahl "alle Mannschaften" oder eine bestimmte Mannschaft
    * Auswahl anzuzeigendes Highlight ("180+171", "Shortleg", "Highfinish")
FIXME: wird noch nirgends produktiv verwendet und ist nicht "fertig" implementiert!


## Zusammenstellungen

* `Mannschaftsseite` Bündelt `Mannschaftsliste`, `Ranking` etc. damit diese
nicht je Mannschaft einzeln zusammengestellt werden müssen.
* Konfiguration
    * Auswahl Mannschaft

* `Spielortseite` Informationen zum Spielort (`Spielortinfo`) und allen Mannschaften,
die dort spielen.
* Konfiguration
    * Auswahl Spielort
    * Auswahl Ligen
