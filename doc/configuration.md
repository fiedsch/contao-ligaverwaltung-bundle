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


###  Spielberechtingungen

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
