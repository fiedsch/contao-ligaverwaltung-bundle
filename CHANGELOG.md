# Changes

## Version 0.5.6

* Standarduhrzeit beim Kalender-Export hinzufügen, wenn nötig

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
