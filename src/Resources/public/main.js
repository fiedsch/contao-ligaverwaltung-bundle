/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Auswahl eines Spielers in der Aufstellung
 */
Vue.component('lineupplayerselect', {
    props: {
        available: Array, // jeweils ID und Name der verfügbaren Spieler
        lineup: Array, // IDs der Aufgestellten Spieler in der Reihenfolge der Aufstellung
        slotNumber: Number,
        suffix: String
    },
    data: function () {
            return {
                selected: 0
            };
    },
    template: '<div>\
          <span class="slot">{{ suffix }}{{ slotNumber }}</span>\
          <select v-model="selected">\
            <option \
            :class="{ isNotAvailable: !isAvailable(a.id) }"\
            :disabled="!isAvailable(a.id)"\
            v-for="a in available" :value="a.id">{{ a.name }}</option>\
          </select>\
          </div>',
    watch: {
        selected: function() {
            this.$emit("lineupplayerchanged", this.slotNumber, this.selected);
        }
    },
    methods: {
        isAvailable: function(id) {
            return id == 0 || !this.lineup.contains(id);
        }
    }
});

/**
 * Aufstellung der Spieler für eine Mannschaft
 */
Vue.component('teamlineup', {
    template: '\
      <div>\
        <h2>{{ name }}</h2>\
        <div v-for="i in slots">\
        <lineupplayerselect :suffix="suffix" :slotNumber="i" :available="available" :lineup="lineup" @lineupplayerchanged="lineupplayerchanged"></lineupplayerselect>\
    </div></div>',
    props: {
        name: String, // Name des Teams
        suffix: String,
        available: Array, // jeweils ID und Name der verfügbaren Spieler
        lineup: Array, // IDs der aufgestellten Spieler
        slots: Number // Anzahl Spieler, die benannt (aufgestellt) werden können
    },
    methods: {
        lineupplayerchanged: function(slotnumber, selected) {
            this.lineup[slotnumber-1] = selected;
            // make change "visible" (this is most likely not the proper way)
            this.lineup.push(this.lineup.pop());
        }
    }
});

/**
 * Aufstellung der beiden Teams
 */
 Vue.component('aufstellung', {
    props: {
            home: Object, // Daten zur Heimmanschaft
            away: Object, // Daten zur Auswärtsmannschaft
            slots: Number // Anzahl Spieler, die benannt (aufgestellt) werden können
    },
    template: '\
    <div>\
    <div style="width:45%;float:left">\
      <teamlineup :name="home.name" suffix="H" :available="home.available" :lineup="home.lineup" :slots="slots"></teamlineup>\
    </div>\
    <div style="width:45%;float:left">\
      <teamlineup :name="away.name" suffix="G" :available="away.available" :lineup="away.lineup" :slots="slots"></teamlineup>\
    </div>\
    </div>'
});

/**
 * Ergebnistabelle
 */
Vue.component('resultstable', {
    props: ['home', 'away', 'spielplan'],
    template: '\
    <table>\
      <tableheader :home="home" :away="away"></tableheader>\
      <tablebody :home="home" :away="away" :spielplan="spielplan"></tablebody>\
    </table>'
});

/**
 * Tabellenkopf ("Überschriften")
 */
Vue.component('tableheader', {
    props: ['home', 'away'],
    template: '\
    <thead>\
      <tr>\
        <th></th>\
        <th> {{ home.name }} </th>\
        <th> {{ away.name }} </th>\
        <th> {{ home.name }} </th>\
        <th> {{ away.name }} </th>\
        <th> Spiel </th>\
        <th> Gesamt </th>\
      </tr>\
    </thead>'
});

/**
 * Tabelle mit den einzelnen Spielen (zwei Spieler gegeneinander) als Zeilen
 */
Vue.component('tablebody', {
    props: ['home', 'away', 'spielplan'],
    template: '\
    <tbody>\
        <tr v-for="(spiel,index) in spielplan">\
            <td><span class="slot">{{ index+1 }}</span></td>\
            <td><spielerselect :team="home" :position="spiel.home" :index="index"></spielerselect></td>\
            <td><spielerselect :team="away" :position="spiel.away" :index="index"></spielerselect></td>\
            <td><spielerscore :team="home" :index="index"></spielerscore></td>\
            <td><spielerscore :team="away" :index="index"></spielerscore></td>\
            <td><spielergebnis :index="index"></spielergebnis></td>\
            <td><span v-if="spiel.scores.home != null && spiel.scores.away != null">\
            <spielstand :index="index"></spielstand>\
            </span></td>\
        </tr>\
        <tr><td colspan="3"></td>\
        <td colspan="2"><legsstand :index="spielplan.length"></legsstand></td>\
        <td colspan="2"><spielstand :index="spielplan.length"></spielstand></td>\
        </tr>\
    </tbody>'
});

/**
 * Spielerauswahl in der Spiele-/Ergebnisliste
 */
Vue.component('spielerselect', {
    props: {
            team: Object, // Alle Daten zum Team
            position: Array, // Index (oder Indices bei Doppeln) der Spieler-ID im Array lineup
            index: Number // (nullbasierter) Zeilenindex (Nummer des Spiels -1)
    },
    template: '<span>\
    <select \
      v-model="selected"  v-bind:class="{ double: isDouble, winner: isWinner, loser: isLoser }"\
      :name="selectname" tabindex="-1">\
        <option \
            v-for="lineupindex in team.lineup.length" \
            :value="lineupindex-1">{{ spielername(lineupindex-1) }}</option>\
    </select><select\
      v-if="isDouble"\
      v-model="selected2" v-bind:class="{ double: isDouble, winner: isWinner, loser: isLoser }"\
      :name="selectname2" tabindex="-1">\
        <option v-for="lineupindex in team.lineup.length" :value="lineupindex-1">{{ spielername(lineupindex-1) }}</option>\
    </select>\
    </span>',
    methods: {
        spielername: function (index) {
            if (undefined == index) { index = 0; }
            var spielerid = this.team.lineup[index];
            if (undefined == spielerid) { spielerid = 0; }
            var player = this.team.available.filter(function (v) {
                return v.id === spielerid;
            });
            if (player.length==0) { return "Kein Name für Pos. "+index; }
            var suffix = this.team.key == 'home' ? "H" : "G";
            return "("+suffix+(index+1)+") " + player[0].name;
        }
    },
    computed: {
        selectname: function () {
            return 'spieler_' + this.team.key + '_' + this.index + (this.isDouble ? '_1' : '');
        },
        selectname2: function () {
            return 'spieler_' + this.team.key + '_' + this.index + (this.isDouble ? '_2' : '');
        },
        selected: {
            get: function () {
                return this.team.played[this.index].ids[0];
            },
            set: function (value) {
                this.team.played[this.index].ids[0] = value;
                // force update:
                this.team.played.push(this.team.played.pop());
            }
        },
        selected2: {
            get: function () {
                return this.team.played[this.index].ids[1];
            },
            set: function (value) {
                this.team.played[this.index].ids[1] = value;
                // force update:
                this.team.played.push(this.team.played.pop());
            }
        },
        isWinner: function () {
            var other = this.team.key == 'home' ? 'away' : 'home';
            var spiel = this.$root.$data.spielplan[this.index];
            if (spiel.scores[this.team.key] == null || spiel.scores[other] == null) {
                return false;
            }
            if (spiel.scores[this.team.key] === '' || spiel.scores[other] === '') {
                return false;
            }
            return spiel.scores[this.team.key] > spiel.scores[other];
        },
        isLoser: function () {
            var other = this.team.key == 'home' ? 'away' : 'home';
            var spiel = this.$root.$data.spielplan[this.index];
            if (spiel.scores[this.team.key] == null || spiel.scores[other] == null) {
                return false;
            }
            if (spiel.scores[this.team.key] === '' || spiel.scores[other] === '') {
                return false;
            }
            return spiel.scores[this.team.key] < spiel.scores[other];
        },
        isDouble: function () {
            return this.position.length > 1;
        }
    }
});

/**
 * (Anzeige des) Score eines Spielers
 */
Vue.component('spielerscore', {
    props: ['team', 'index'],
    template: '<input class="form-control" :name="inputname" v-model.number="score" type="number" min="0" max="3" autocomplete="off">',
    data: function () {
        return {spielplan: data.spielplan};
    },
    computed: {
        inputname: {
            get: function () {
                return 'score_' + this.team.key + '_' + this.index;
            }
        },
        score: {
            get: function () {
                return this.spielplan[this.index].scores[this.team.key];
            },
            set: function (newValue) {
                var current = this.spielplan[this.index];
                current.scores[this.team.key] = newValue === '' ? null : newValue;
                // Wenn beide Ergebnisse vorliegen gibt es das Gesamtergebnis (Seiteneffekt: result setzen)
                var result = null;
                if (current.scores['home'] != null && current.scores['away'] != null) {
                    if (current.scores['home'] === current.scores['away']) {
                        result = '1:1';
                    } else if (current.scores['home'] < current.scores['away']) {
                        result = '0:1';
                    } else if (current.scores['home'] > current.scores['away']) {
                        result = '1:0';
                    }
                }
                current.result = result;
                // .splice to trigger Vue's view updates,
                // see https://vuejs.org/v2/guide/list.html#Caveats
                this.spielplan.splice(this.index, 1, current);
            }
        }
    }
});

/**
 * (Anzeige des) Ergebnis eines Spiels (zwei Spieler gegeneinander oder ein Doppel)
 */
Vue.component('spielergebnis', {
    props: ['index'],
    data: function () {
        return {spielplan: data.spielplan};
    },
    template: '<span>{{ this.spielplan[this.index].result }}</span>'
});

/**
 * (Anzeige des) Gesamtstands Legs
 */
Vue.component('legsstand', {
    props: ['index'],
    template: '<span class="legsstand">{{ legsstand[1] }}:{{ legsstand[2] }}</span>',
    data: function () {
        return {spielplan: data.spielplan};
    },
    computed: {
        legsstand: {
            get: function () {
                return this.spielplan.reduce(function (acc, currentValue, currentIndex) {
                    if (currentIndex <= acc[0]) {
                        if (currentValue.scores && currentValue.scores.home != null && currentValue.scores.away != null) {
                            acc[1] += currentValue.scores.home;
                            acc[2] += currentValue.scores.away;
                        }
                    }
                    return acc;
                }, [this.index, 0, 0]);
            }
        }
    }
});

/**
 * (Anzeige des) Gesamtstands Punkte (Spielstand)
 */
Vue.component('spielstand', {
    props: ['index'],
    template: '<span class="spielstand">{{ spielstand[1] }}:{{ spielstand[2] }}</span>',
    data: function () {
        return {spielplan: data.spielplan};
    },
    computed: {
        spielstand: {
            get: function () {
                return this.spielplan.reduce(function (acc, currentValue, currentIndex) {
                    if (currentIndex <= acc[0]) {
                        if (currentValue.scores.home !== null && currentValue.scores.away !== null) {
                            if (currentValue.scores.home > currentValue.scores.away) {
                                acc[1] += 1;
                            } else if (currentValue.scores.home < currentValue.scores.away) {
                                acc[2] += 1;
                            }
                        }
                    }
                    return acc;
                }, [this.index, 0, 0]);
            }
        }
    }
});

/**
 * Die Vue.js App
 */
var app = new Vue({
    el: '#app',
    data: data,
    created: function () {
        if (this.home.lineup.length == 0) {
            this.home.lineup = this.make_lineuparray(this.num_players);
        }
        if (this.away.lineup.length == 0) {
            this.away.lineup = this.make_lineuparray(this.num_players);
        }
        this.spielplan.forEach(function (entry) {
            if (typeof entry.scores == "undefined") {
                //console.log("setze scores");
                entry.scores = {home: null, away: null};
            }
            if (typeof entry.result == "undefined") {
                entry.result = null;
            }
        });
        if (this.home.played.length == 0) {
            this.spielplan.forEach(function (entry, i) {
                this.home.played.push({ids: entry.home, slot: i + 1});
                this.away.played.push({ids: entry.away, slot: i + 1});
            }, this);
        }
        //console.log(JSON.stringify(this.$data));
    },
    computed: {
        // für DEBUG {{ showdata }}
        showData: function () {
            return JSON.stringify(this.$data, null, 9);
        }
    },
    methods: {
        make_lineuparray: function (n) {
            var arr = Array.apply(null, new Array(n));
            return arr.map(function (x, i) {
                return 0
            });
        }
    }
});


/**
 * Für das Contao Backend: auf dieser Seite den "Effekt" von stylect.js
 * rückgängig machen, da sonst Vue nicht funktioniert
 */

window.addEvent('domready', function () {
    $$('.styled_select').each(function (el) {
        el.remove()
    });
    //$$('select').each(function(el) { el.setStyle('opacity', 1)} );
    // we don't use inline style, so remove it completely
    $$('select').each(function (el) {
        el.removeProperty('style')
    });
});
