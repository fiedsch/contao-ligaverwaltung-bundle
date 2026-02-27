# Änderungen im laufenden Betrieb

## Beispiele

### Eine Mannschaft stellt während einer laufenden Saison den Spielbetrieb ein

* Aktion: In der Konfiguration der Mannschaft das Häkchen bei "aktiv" entfernen

* Ergebnis:
  * Auf die Mannschaftsseite dieser Mannschaft wird an keiner Stelle mehr verlinkt
  * Im Spielplan werden die Spiele dieser Mannschaft wie folgt dargestellt:
    * bereits gespielte Begegnungen: "(nicht mehr aktiv)" hinter dem Mannschaftsnamen
      und "nicht gewertet" als Ergebnis
    * zukünftige Begegnungen: anstelle des Mannschaftsnamens steht "spielfrei"
  * Die Spiele dieser Mannschaft werden im Ranking (Tabelle) nicht berücksichtigt
  * Die Spieler dieser Mannschaft tauchen im Ranking nicht auf.


 ### Ein Spieler wechselt in einer laufenden Saison die Mannschaft

 * Aktion:
   * Bei dem Spieler in der alten Mannschaft das Häkchen "aktiv" entfernen
   * Den Spieler der neuen Mannschaft hinzufügen

 * Ergebnis:
   * Der Spieler wird bei der alten Mannschaft nicht mehr aufgeführt
   * Der Spieler wird bei der neuen Mannschaft aufgeführt
   * TODO:
     * In den Einzelspieler-Highlight-Rankings behält der Spieler seine
       Ergebnisse (in der Spalte Mannschaft steht die alte und die neue Mannschaft)
     * Idee: ein Highlight ist eine Lsistung der Prson und unabhängig von der Liga (Spielklasse)
   * TODO abhängig davon, ob der Wechsel innerhalb der gleichen Liga (Spielklasse) stattgefunden hat:
     * Im Spielerranking erscheinen nur noch die Spiele, die der Spieler für die neue Mannschaft gemacht hat.
     * vs.
     * Im Spielerranking erscheinen alle Spiele, die der Spieler für die alte und die neue Mannschaft gemacht hat.


### Erfassung von Begegnungen bei denen der Gegner nicht angetreten ist

In der Begegnungserfassung werden zunächst alle Spiele als "zu Null verloren"
erfasst.

Hier gibt es zwei Optionen:
* Spieler bei keiner Mannschaft hinterlegen (die nicht gespielte Begegnung hat keine Auswirkung auf das Spielerranking)
* Spieler bei der angetretenen Mannschaft hinterlegen (diese Spieler erhalten Punkte für das Spielerranking)

Bei die Berechnung der Tabellenplätze der Mannschaften führen beide Wege zum identischen Ergebnis.
