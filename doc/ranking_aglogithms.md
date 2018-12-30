# Rankings (Methodik)

Zunächst die Beschreibung der hinterlegten "Standards". Wie diese überschrieben werden
können wird im Anschluss erklärt.

## Spielerrankings

Für die Ermittlung der Spielerrankings werden jedem Spieler pro gespieltem 
Spiel Punkte vergeben. Nach diesen Punkten wird sortiert. Bei Punktgleichheit
wird wie folgt unterschieden:

1. Nach Spielen (gewonnen − verloren). Sind diese auch gleich,
2. nach Legs (gewonnen − verloren).
3. Ist auch die Legdifferenz gleich, dann nach gewonnenen Legs.


### Ranking nach Punkten 

Hier werden je nach Ergebnis verschiedene Punkte vergeben. Motivation: 
ein deutlicher Sieg soll mehr Punkte ergeben, als ein knapper; eine
knappe Niederlage soll besser bewertet werden, als eine klare "zu Null Niederlage".

Beispiel bei "best of three":

Ergebnis | Punkte
--- | ---
2:0 | 3
2:1 | 2
1:2 | 1
0:2 | 0

  
### Ranking nach gewonnenen Spielen

Hier zählt nur das Ergebnis gewonnen oder verloren.

Beispiel bei "best of three":

Ergebnis | Punkte
--- | ---
2:0 | 1
2:1 | 1
1:2 | 0
0:2 | 0 


## Mannschaftenrankings
 
Bei den Rankings von Mannschaften werden ebenfalls Punkte vergeben:

* gewonnen 3 Punkte
* unentschieden 1 Punkt
* verloren 0 Punkte

Dann Sortierung nach den Punkten und bei Gleichstand wird wie bei den Spielerrankings
verfahren.


## Überschreiben der Standards

Um die "mitgelieferten" Implementierungen (siehe `src/Helper/RankingHelper.php`) zu überschreiben
muss ein `AppBundle` angelegt werden, das einene eigenen `Helper` registriert. In diesem können
dann die gewünschten Regeln hinterlegt werden.

Dazu müssen die folgenden Dateien angelegt werden:

```php
# app/ContaoManagerPlugin.php
<?php

use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Fiedsch\LigaverwaltungBundle\FiedschLigaverwaltungBundle;

class ContaoManagerPlugin implements BundlePluginInterface
{

    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(AppBundle\AppBundle::class)
                ->setLoadAfter([FiedschLigaverwaltungBundle::class]),
        ];
    }

}
``` 

```php
# src/AppBundle/Helper/RankingHelper.php
<?php

namespace AppBundle\Helper;

use Fiedsch\LigaverwaltungBundle\Helper\RankingHelper as OriginalHelper;

class RankingHelper extends OriginalHelper
{
    /*
     * Nach Bedarf die Teile überschreiben, die in
     * Fiedsch\LigaverwaltungBundle\Helper\RankingHelper geändert werden sollen.
     */

    const PUNKTE_GEWONNEN = 2;         // Bsp.: 3 zu 2 geändert
    // const PUNKTE_UNENTSCHIEDEN = 1; // soll gleich bleiben
    // const PUNKTE_VERLOREN = 0;      // soll gleich bleiben

    public function compareResults(array $a, array $b): int
    {
        /* ... */
    }

    public function getPunkte(string $score, int $ranking_model = 1): int
    {
        /* ... */
    }

}
```



```php
# src/AppBundle/AppBundle.php
<?php

namespace AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AppBundle extends Bundle
{

  /**
   * Den "Standardservice" für die Berechnungen bei den Rankings überschreiben
   */
  public function build(ContainerBuilder $container)
  {
    parent::build($container);
    $definition = new Definition(\AppBundle\Helper\RankingHelper::class);
    $definition->setPublic(true);
    $container->setDefinition('fiedsch_ligaverwaltung.rankinghelper', $definition);
  }

}
```

```json
# composer.json
    "autoload": {
        "classmap": [
	        "app/ContaoManagerPlugin.php"
	    ],
	    "psr-4": {
	        "AppBundle\\": "src/AppBundle/"
	    }
    } 
```
