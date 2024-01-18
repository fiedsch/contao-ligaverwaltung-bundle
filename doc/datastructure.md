# Datenstrukturen

```
tl_spielort (Marker; wird einer Mannschaft zugeordnet)

tl_aufsteller (Marker; wird einem Spielort zugeordnet)

tl_saison (Marker; wird einer Liga zugeordnet) Hat einen `alias` der die Saison identifiziert und ein Label,
das bei der Ausgabe im Frontend verwednet wird. Damit ist es möglich, verschiedene Saisons mit dem gleichen Label
anzulegen. Damit ist es möglich, einen Spieler im gleichen Jahr (Label der Saison) verschiedenen Mannschaften
zuzuordnen, sofern diese in unterschiedlichen Saisons (alias) aktiv sind.

Use Case: es soll 4er und 6er Teams geben. Ein Spieler soll in einem 4er und in einem 6er Team spielen können.
Damit benötigen wir zwei Saisons: z.B. (`2024_4er`, "2024") und (`2024_6er`, "2024") denen die Mannschaften der
4er bzw. 6er Teams zugeordnet werden. Dadurch, daß die Mannschaften in zwei (technisch) verschiedenen Saisons
aktiv sind, gibt es keine Beschränkung bei der Speielerzuordnung. Innerhalb einer Saison gilt weiterhin: ein Spieler
kann in einer Saison nicht in zwei verschiedenen Mannschaften aktv sein.


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
