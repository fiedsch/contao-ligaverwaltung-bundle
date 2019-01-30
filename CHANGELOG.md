# Changes

## Development

* 


## Version 0.6.0

* New: "Jugendliche". Bei den Spielern (Tabelle `tl_spieler`) kann in `tl_spieler.jugendlich`
  erfasst werden, ob der Spieler in der Altersklasse "Jugendlich" gewertet wird. Die
  Angabe gilt mit dieser Konstruktion für die gesamte Saison.
  Diese Option dient zunächst nur der Erfassung der Daten und hat bis auf die
  Vergabe von CSS-Klassen bei der Ausgabe von Ergebnis- und Highlight-Tabellen keine
  weitere Auswirkung.
  
* New: CSS-Klassen bei der Ausgabe von Ergebnis- und Highlight-Tabellen. Klassen für
  Jugendlich (`youth`) und Geschlecht (`male`, `female` analog zu `tl_member.gender`) 
  werden Tabellenzeilen abhängig vom dargestellten Spieler vergeben.


## Version 0.5.7

* Fix: "Nicht angetreten" bei der Erfassung der Begegnungen. Wenn sowohl für die Heim- als
  auch für die Gastmanschaft keine Spieler hinterlegt wurden, alle Spiele aber 3:0 für die
  Heimmannschaft gewertet wurden wurde der falsche Infotext "Heim nicht angetreten" angezeigt.
  Hier wird nun "nicht angetreten" angezeigt. Dies ermöglicht zudem, die Begegnung (z.B.)
  16:0 für die angetretene Mannschaft zu werten, aber dennoch keine Spieler hnterlegen zu 
  müssen, wenn man nicht will, daß Ergebnisse solcher Begegnungen Einfluss auf die 
  Einzelspielerrangliste haben.
  
* New: Bei Begegnungen kann auch nach der ID gesucht werden. Kann hilfreich sein, wenn 
  im Log ausgegeben wird, daß in einer Begegnung (deren ID wird angeben) ein Fehler 
  enthalten ist. (Tip: "Exakte" Suche mit RegEx `^123$` "ist geich 123" vs. `123` "enthält 123").


## Version 0.5.6

* Standarduhrzeit beim Kalender-Export hinzufügen, wenn nötig.

  Wenn in sitesepzifischen Anpassungen die DCA-Definition der Begenungen von "Datum und Uhrzeit" zu "nur Datum" abgeändert wurde beim Export eine Standardzeit hinzufügen.

  Diese Uhrzeit ist aktuell noch nicht konfigurierbar! `:-(`


## Version 0.5.5

* Bugfix in `ContentHighlightRanking` um einen Crash bei unvollständigen
 `tl_highlight`-Daten zu vermeiden.


## Version 0.5.4

* Textbausteine konfigurierbar machen.
  Siehe dazu in `languages/de/default.php` die Definition von
  `$GLOBALS['TL_LANG']['MSC']['tc1']` und
  `$GLOBALS['TL_LANG']['MSC']['tc1']`.

* Begegnungserfassung: Spielerpassnummer optional anzeigen.


## Version 0.5.3

* Mannschaftsnamen beim Ranking auf einer Mannschaftsseite nicht ausgeben.
  Siehe `ce_highlightranking.html5`.
