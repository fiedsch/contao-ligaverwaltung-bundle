# API changes

## Version 0.* to 1.0

Due to refactored directory structure (and hence PHP namespace changes) templates now have to use (e.g.)
`Fiedsch\LigaverwaltungBundle\Model\HighlightModel` as opposed to `\HighlightModel` which was used in
versions `< 1.0` where the models lived in the `Contao` namespace. Affected templates are
`ce_highlightranking.html5` and `ce_spielbericht.html5`.


