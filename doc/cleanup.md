# Datenbereinigungen

Nicht alle Daten der Ligaverwaltung sind in Contaos DCA-Konzept von table und `ctable` (bzw. `ptable`) organisiert.

Dadurch können "Datenleichen" entstehen. Dies beinflusst zwar nicht das Funktionieren der Ligaverwaltung, führt im
Laufe der Zeit aber zu unnötigem Datenmüll.

## Beispiele

* `tl_aufsteller`: Aufsteller werden in einer eigenen Tabelle verwaltet. Verwendet werden Aufsteller bei Spielorten
  (`tl_spielort`), die wiederum bei Mannschaften (`tl_mannschaft`) verwendet werden. Mannschaften werden schließlich
  in Begegnungen (`tl_begegnung`) als Heim- oder Auswärts-Team verwendet. Erst `tl_begegnung` ist in der Contao-üblichen
  Hierarchie organisiert (`tl_verband` -> `tl_liga` -> `tl_begegnung` -> `tl_spiel`; siehe [`datastructure.md`](./datastructure.md))

## Datenbereinigungen

Folgende Datenbereinigungen können nicht einfach im Backend druchgeführt werden (entweder, weil die Suche nach den Records
zu aufwändig ist, oder, weil es zu viele sein könnten um es manuell einzeln zu machen).

### Verwaiste Begegnungen

Finden von verwaisten Begegnungen:

Zugehörige Mannschaften existieren nicht mehr
```sql
SELECT id FROM tl_begegnung b
  LEFT JOIN tl_mannschaft mh ON (b.home=mh.id)
  LEFT JOIN tl_mannschaft ma ON (b.away=ma.id)
  WHERE
      b.away > 0 -- kein "Spielfrei"
      AND (mh.id IS NULL OR ma.id IS NULL)
  ;
```
Bei obigen Query u.U. noch berücksichtigen, daß die Begegnung nicht aus einer aktuell aktiven Saison (Ausgabe im
Frontend) stammt (`tl_begegnung.pid = tl_liga.id` und `tl_liga.aktiv = 1`). Your Mileage may vary, da die Liga u.U.
nicht mehr im Fontend ausgegeben wird und dennoch immer noch auf `aktiv` steht.

Vor einem `DELETE FROM tl_begegnung WHERE /* s.o. */` müssen auch

- die zugehörigen `tl_spiel` Records gelöscht werden: Relation `tl_spiel.pid = tl_begegnung.id`
```sql
DELETE FROM tl_spiel WHERE pid IN (/*obiger Query mit SELECT id FROM ...*/)
```
- die zugehörigen `tl_highlight` Records gelöscht werden: Relation `tl_highlight.begegnung_id = tl_begegnung.id`
```sql
DELETE FROM tl_highlight WHERE begegnung_id IN (/*obiger Query mit SELECT id FROM ...*/)
```
- die zugehörigen `tl_spieler` Records gelöscht werden: Relation `tl_spieler.pid = tl_mannschaft.id`
  und `tl_mannschaft.id` ist entweder `tl_begegnung.home` oder `tl_begegnung.away`

Danach: Löschen von nun verwaisten Mannschaften: Eine Begegnung könnte gelöscht werden, weil `home`oder `away` nicht
mehr existieren. Durch das Löschen der Begegnung ist nun u.U. `away` oder `home` eineMannschaft, die nirgends verwendet
wird ...
