# Rankings (Methodik)

Zunächst die Beschreibung der hinterlegten "Standards". Wie diese überschrieben werden
können wird im Anschluss erklärt.

## Spielerrankings

Für die Ermittlung der Spielerrankings werden jedem Spieler pro gespieltem
Spiel Punkte vergeben (s.u.). Zunächst wird nach diesen Punkten sortiert.
Bei Punktgleichheit wird wie folgt unterschieden:

1. Nach Spielen (gewonnen − verloren).
2. Sind diese auch gleich, nach Legs (gewonnen − verloren).
3. Ist auch die Legdifferenz gleich, dann nach gewonnenen Legs.


### Ranking nach Punkten

Hier werden je nach Ergebnis verschiedene Punkte vergeben. Motivation:
ein deutlicher Sieg soll mehr Punkte ergeben, als ein knapper; eine
knappe Niederlage soll besser bewertet werden, als eine klare "zu Null Niederlage".

Beispiel bei "best of three":

| Ergebnis | Punkte |
|----------|--------|
| 2:0      | 3      |
| 2:1      | 2      |
| 1:2      | 1      |
| 0:2      | 0      |

### Ranking nach gewonnenen Spielen

Hier zählt nur das Ergebnis gewonnen oder verloren.

Beispiel bei "best of three":

| Ergebnis | Punkte |
|----------|--------|
| 2:0      | 1      |
| 2:1      | 1      |
| 1:2      | 0      |
| 0:2      | 0      |

## Mannschaftenrankings

Bei den Rankings von Mannschaften werden ebenfalls Punkte vergeben:

* gewonnen 3 Punkte
* unentschieden 1 Punkt
* verloren 0 Punkte

Dann Sortierung nach den Punkten und bei Gleichstand wird wie bei den Spielerrankings
verfahren.


## Überschreiben der Standards

Um die "mitgelieferten" Implementierungen (siehe `src/Helper/RankingHelper.php`) zu überschreiben
muss ein "`App` Bundle" angelegt werden, das einene eigenen `Helper` registriert. In diesem können
dann die gewünschten Regeln hinterlegt werden.

TODO: Update description to show the proper Contao 5 way of doing things

Dazu müssen die folgenden Dateien angelegt werden:

```php
<?php
# app/ContaoManagerPlugin.php

use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Fiedsch\Ligaverwaltung\FiedschLigaverwaltungBundle;

class ContaoManagerPlugin implements BundlePluginInterface
{

    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(App\App::class)
                ->setLoadAfter([FiedschLigaverwaltungBundle::class]),
        ];
    }

}
```

```php
<?php
# src/App/Helper/RankingHelper.php

namespace App\Helper;

use Fiedsch\Ligaverwaltung\Helper\RankingHelper as OriginalHelper;

class RankingHelper extends OriginalHelper
{
    /*
     * Nach Bedarf die Teile überschreiben, die in
     * Fiedsch\Ligaverwaltung\Helper\RankingHelper geändert werden sollen.
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
<?php
# src/App/App.php

namespace App;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class App extends Bundle
{

  /**
   * Den "Standardservice" für die Berechnungen bei den Rankings überschreiben
   */
  public function build(ContainerBuilder $container)
  {
    parent::build($container);
    $definition = new Definition(\App\Helper\RankingHelper::class);
    $definition->setPublic(true);
    $container->setDefinition('fiedsch_ligaverwaltung.helper.ranking', $definition);
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
	        "App\\": "src/App/"
	    }
    }
```


TODO: "Korrekt" machen; siehe https://symfony.com/doc/current/service_container/service_decoration.html
