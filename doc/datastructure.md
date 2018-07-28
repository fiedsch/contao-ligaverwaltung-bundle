# Datenstrukturen
  
```
tl_spielort (Marker; wird einer Mannschaft zugeordnet)

tl_aufsteller (Marker; wird einem Spielort zugeordnet)
  
tl_saison (Marker; wird einer Liga zugeordnet)
  
tl_verband
   |
   + tl_liga
        |
        + tl_begegnung (Mannschaft gegen Mannschaft)
            |
            + tl_spiel (Spieler gegen Spieler)
          
tl_mannschaft (hat als Attribut (u.A.) eine Liga, ist aber im Sinne der Contao DCA keine 
Kindtabelle von tl_liga!)
   |
   + tl_spieler (Mapping-Tabelle, die einen Spieler in einer Mannschaft -- und damit Liga
     und damit Saison -- auf ein Contao-Member abbildet).

tl_highlight (dient der Erfassung von Highlights wie High-Finishes oder Shortlegs)

tl_spielort Verwaltung von Spielorten (Attribut einer Mannschart)

```
