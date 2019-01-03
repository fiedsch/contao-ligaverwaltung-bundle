# Konfiguration


## Erstellen von Ligen

1. Saison anlegen
2. Liga anlegen


## Begriffe

* Spielstärke: Die Spielstärke ist eine beliebige Zahl. Sie dient lediglich dazu,
  Ligen sortieren zu können. Ligen mit kleinerer Spielstärke werden weiter oben
  einsortiert (1. Liga, 2. Liga usw.). Verwendung findet die Spielstärke im 
  Content-Element `ContentMannschaftenuebersicht`. 


## Systemeinstellungen


###  Spielberechtigungen

In den Contao Systemeinstellungen kann im Bereich "Ligaverwaltung" festgelegt werden, wie einzelne
Spieler zeitgleich in verscheidenen Mannschaften spielen dürfen. Die dort getroffene Auswahl bestimmt
die Spieler, die beim Hinzufügen zu einer Mannschaft im Auswahlmenü angezeigt werden:
* "in einer Mannschft (je Saison)": ein Spieler darf in einer Saison (ligaübergreifend) nur in einer 
  Mannschaft spielen
* "in einer Mannschft (je Liga)" — weniger restriktiv: ein Spieler darf in einer Liga nur in einer 
  Mannschaft spielen. In einer anderen Liga darf er aber zeitgleich auch spielen!


### Mannschaftsseite

Hier kann eine Seite angegeben werden, auf der ein Modul vom Typ "Mannschaftsseiten Reader"
eingebunden ist. In diesem Fall werden Mannschaftsnamen auf diese Seite verlinkt.
 
### Spielberichtseite

Hier kann eine Seite angegeben werden, auf der ein Modul vom Typ "Spielbericht Reader"
eingebunden ist. In diesem Fall werden Spielergebnisse auf diese Seite verlinkt.

### Sortierung Ranking ("Punktevergabe")

Hier kann festgelegt werden, wie bei den Spielerrankings sortiert werden soll.

* nach Punkten: es werden je nach Ergebnis Punkte vergeben. Beipiel für "Best of three":
  2:0 ergibt 3 Punkte, 2:1 2 Punkte, 1:2 1 Punkt und 0:2 0 Punkte. Spieler die knapp 
  verlieren sollen mit den Punkten motiviert werden. 
  
* nach gewonnenen Spielen: hier zählt nur gewonnen oder verloren. Der Leg-Stand ist 
  belanglos.
  
Je nach gewählter Einstellung wird bei der Frontendausgabe die Spalte "Punkte" 
ausgegeben ("nach Punkten") oder nicht ("nach gewonnenen Spielen").

[Details zur Berechnung](ranking_aglogithms.md)


### Textbausteine

Die Begriffe "TC" (Teamcaptain) und "Co-TC" (Co-Teamcaptain oder zweiter TC) können
über Variablen konfiguriert werden. Beispiel für eine geänderte Definition:

```php
# app/Resources/contao/languages/de/default.php
$GLOBALS['TL_LANG']['MSC']['tc1'] = '1. TC';
$GLOBALS['TL_LANG']['MSC']['tc2'] = '2. TC';
```
Diese Textbausteine werden im Template `ce_spielerliste.html5` und im `SpielerModel`
(und damit letztlich in `ContentMannschaftenuebersicht`) verwendet.

