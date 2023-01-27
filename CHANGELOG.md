# Changes

## Development

* Update Vue components for daat entry to Vue 2.7


# Version 2.1.0

* Remove sensio/framework-extra-bundle requirement
* Update eluceo/ical

## Version 2.0.0 - 2.0.2

* Version for PHP 8 and Contao 4.13

## Version 1.1.5

* add missing `use` statement in `DCAHelper`

## Version 1.1.5

* add missing `setContainer()` call


## Version 1.1.4

* Fix collation in `tl_member.username` change
* Add `loadLanguageFile()` calls in DCA files

## Version 1.1.3

* Fix #18 (require `patchwork/utf8` which is nor required in Contao 4.13+)

## Version 1.1.2

* Minor fix for Contao 4.9.x (Contao\ArrayUtil is not available there)


## Version 1.1.1

* Fix: creation of backend labels failed if corresponding "liga" has been deleted


## Version 1.1.0

* New Content Element `TeamsAndPlayersOverview` (first draft)


## Version 1.0.8

* Remove "visual debug" (a `print_r()` that unintendedly made it into the code :-(
* convert `strnatcmp()` arguments to string (avoid PHP 8 warning)


## Version 1.0.7

* Mitigate effect of badly entered match results in `RankingHelperInterface::getPunkte()` where `"1:0"` (which is an
  impossible result if you have a best of three match) would have yielded `0` points.
  instead of `1` point.


## Version 1.0.6

* Link team names in ranking tables


## Version 1.0.5

* Member listing in back end: make gender a filter
* Add custom template selection to all provided content elements


## Version 1.0.4

* Require a haste version that supports PHP 8
* Bugfix: Do not crash when a referenced member does no longer exist


## Version 1.0.3

* Be compatible with https://github.com/contao/contao/issues/2708


## Version 1.0.2

* require alt least PHP 7.4 and mark as compatible with PHP 8
* apply CS fixer
* Refactor `RechnungsDatenAbzugCommand` to allow multiple output formats
* Rechnungsdaten: Implement Markdown and CSV output
* TODO: wait for `codefog/contao-haste` to be marked as ready for PHP 8


## Version 1.0.1

* Fix silly bugs introduced in 1.0.0
* CS fixes


## Version 1.0.0

* Refactor directory structure (e.g. move code from `src/Resources/contao/{controllers,models,modules}`
  to `src/{Controller,Model,Module}/`)
* Text der Message bei "bereits veröffentlichte Begegnung speichern" klarerer formuliert.
* Prepare Help Wizard with explanation text(currently not active though)


## Version 0.9.6

* Neues Feld `tl_begegnung.postponed` um Begegnungen markieren zu können, die (bis auf weiteres) verschoben wurden.


## Version 0.9.5

* Bugfix in `Fiedsch\LigaverwaltungBundle\Helper\Spielplan`: Leerzeichen im Array-Key erzeugte Zugriff
  auf undefinierten Index und damit weitere JS-Folgefehler.
* In `Helper\DataEntrySaver::getTeamData()` `html_entity_decode()` für Spielernamen vor der Verwendung
  im Twig-Template
* add `'exclude' => true` to `tl_begegnung.begegnung_data`
* add `tl_member.dateOfBirth` to `SpielerAbzugCommand` output


## Version 0.9.4

* Twig Templates für die `rechnungsdaten` umstrukturiert. Die angepassten Versionen (mit den
  echten Daten wie Ansender oder Fußzeile) können in der Contao-Installation unter
  `templates/bundles/FiedschLigaverwaltungBundle/` gespeichert werden. Sie ersetzen dann die
  mitgelieferten Templates des Bundles.


## Version 0.9.3

* Update README.md
* Bugfix in `ContentHighlightRanking`
* Work on `Commands`: `RechnungsDatenAbzugCommand` that will eventually be moved to
  a separate bundle together with everything else not strictly needed here like
  `tl_aufsteller` which does not make sense in steel-darts.

## Version 0.9.2

* Bugfix:  "Spielfrei" Begegnungen werden wieder angezeigt

## Version 0.9.0

* "Allow better personal data control": Möglichkeit, die Kontaktdaten der TC nur angemeldeten Mitgliedern auszugeben

## Version 0.8.0

* merge branch `begegnungserfassung_frontend`
  - neues Content Element "Begegnungsauswahl" (experimentell!)
  - Begegnungserfassung im Backend erweitert. Das Eingabeformular kann nun mehrmals aufgerufen und
    die Bearbeitung fortgesetzt werden.


## Version 0.7.1

* Bugfix-Release: Update der Vue-App für die Begegnungserfassung


## Version 0.7.0

* New: In der Auflistung der Begegnungen nur die anzeigen, deren zugeordnete Liga aktiv ist (`l_liga.aktiv=1`).
  Der Zugriff auf die anderen Begegnungen ist als Kindelemente der entsprechenden Liga weiterhin möglich.
* kleinere Verbesserungen (z.B. Label bei Spielerhistorie)
* "Datenerfassung Begegnung": es ist nun möglich, die Eingabemaske zur Erfassung einer Begegnung auch
   nach dem Speichern erneut zu öffnen und Daten zu ergänzen. Dazu wird das neue Icon "Stift mit Plus-Zeichen"
   (mehrere bearbeiten) verwendet. Diese Bearbeitung erfordert, daß (z.B.) Daten zur Aufstellung in der
   Begegnung abgespeichert werden. Da dies bei Begegnungen, die vor Verwendung der Versopn 0.7.0 erfasst
   wurden nicht gegeben ist, ist das Icon hier ausgegraut (deaktiviert). Um "alte" Begegnungen erneut
   bearbeiten zu können muss in den Systemeinstellungen bei "Erfassung Spielberichtsbögen (Begegnungen)"
   das Häkchen gesetzt werden. Dann wird neben dem neuen Icon auch das alte ("der Stift") angezeigt und
   "alte Begegnungen" können wie gewohnt nachbearbeitet werden.

   Die Begegnung kann mit dem neuen "Auge-Symbol" veröffentlicht werden. Erst dann werden ihre Daten
   im Frontend angezeigt.

   *Wichtig: damit bereits erfasste Begegnungen im Frontend angezeigt werden, müssen sie nachträglich
   veröffentlicht werden!*


## Version 0.6.3

* New: Spielplan 8 Einzel, 2 Doppel

## Version 0.6.2

* Fix: Delete Code that belongs to branch `ergebniseingabe-frontend`

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

  Für ein Anwendungsbeispiel siehe z.B. https://github.com/fiedsch/contao-ligaverwaltung-bundle/issues/8#issuecomment-459409916


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
