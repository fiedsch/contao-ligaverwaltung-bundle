(function(e){function t(t){for(var n,r,l=t[0],o=t[1],u=t[2],p=0,h=[];p<l.length;p++)r=l[p],Object.prototype.hasOwnProperty.call(a,r)&&a[r]&&h.push(a[r][0]),a[r]=0;for(n in o)Object.prototype.hasOwnProperty.call(o,n)&&(e[n]=o[n]);c&&c(t);while(h.length)h.shift()();return i.push.apply(i,u||[]),s()}function s(){for(var e,t=0;t<i.length;t++){for(var s=i[t],n=!0,l=1;l<s.length;l++){var o=s[l];0!==a[o]&&(n=!1)}n&&(i.splice(t--,1),e=r(r.s=s[0]))}return e}var n={},a={app:0},i=[];function r(t){if(n[t])return n[t].exports;var s=n[t]={i:t,l:!1,exports:{}};return e[t].call(s.exports,s,s.exports,r),s.l=!0,s.exports}r.m=e,r.c=n,r.d=function(e,t,s){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:s})},r.r=function(e){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"===typeof e&&e&&e.__esModule)return e;var s=Object.create(null);if(r.r(s),Object.defineProperty(s,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)r.d(s,n,function(t){return e[t]}.bind(null,n));return s},r.n=function(e){var t=e&&e.__esModule?function(){return e["default"]}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="/";var l=window["webpackJsonp"]=window["webpackJsonp"]||[],o=l.push.bind(l);l.push=t,l=l.slice();for(var u=0;u<l.length;u++)t(l[u]);var c=o;i.push([0,"chunk-vendors"]),s()})({0:function(e,t,s){e.exports=s("56d7")},"009b":function(e,t,s){},"04f9":function(e,t,s){"use strict";var n=s("e2cd"),a=s.n(n);a.a},1:function(e,t){},"14c8":function(e,t,s){},"247d":function(e,t,s){"use strict";var n=s("8856"),a=s.n(n);a.a},"2b1a":function(e,t,s){},3152:function(e,t,s){"use strict";var n=s("4901"),a=s.n(n);a.a},3775:function(e,t,s){},"3ce2":function(e,t,s){},4245:function(e,t,s){},4431:function(e,t,s){"use strict";var n=s("14c8"),a=s.n(n);a.a},4901:function(e,t,s){},"4d9c":function(e,t,s){"use strict";var n=s("5d35"),a=s.n(n);a.a},"4f4b":function(e,t,s){"use strict";var n=s("6253"),a=s.n(n);a.a},"56d7":function(e,t,s){"use strict";s.r(t);s("cadf"),s("551c"),s("f751"),s("097d");var n=s("2b0e"),a=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{attrs:{id:"begegnungserfassung"}},[s("BegegnungsErfassung",{attrs:{data:e.data}})],1)},i=[],r=(s("6b54"),function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",[s("Aufstellung",{attrs:{showspielerpass:e.showspielerpass}}),s("div",{staticClass:"showspielerpasscheckbox"},[s("input",{directives:[{name:"model",rawName:"v-model",value:e.showspielerpass,expression:"showspielerpass"}],attrs:{type:"checkbox",id:"showspielerpass"},domProps:{checked:Array.isArray(e.showspielerpass)?e._i(e.showspielerpass,null)>-1:e.showspielerpass},on:{change:function(t){var s=e.showspielerpass,n=t.target,a=!!n.checked;if(Array.isArray(s)){var i=null,r=e._i(s,i);n.checked?r<0&&(e.showspielerpass=s.concat([i])):r>-1&&(e.showspielerpass=s.slice(0,r).concat(s.slice(r+1)))}else e.showspielerpass=a}}}),s("label",{attrs:{for:"showspielerpass"}},[e._v("Spielerpass-Nummern anzeigen")])]),s("form",{staticClass:"tl_form tl_edit_form",attrs:{method:"POST",id:"vue_begegnungserfassung",enctype:"application/x-www-form-urlencoded"}},[s("div",{staticClass:"tl_formbody_edit"},[s("input",{attrs:{type:"hidden",name:"REQUEST_TOKEN"},domProps:{value:e.requestToken}}),s("input",{attrs:{type:"hidden",name:"FORM_SUBMIT",value:"begegnungserfassung"}}),s("input",{attrs:{type:"hidden",name:"json_data"},domProps:{value:e.dataToSubmit}})])]),s("ResultsTable",{attrs:{showspielerpass:e.showspielerpass}}),s("HighlightsEntry",{attrs:{showspielerpass:e.showspielerpass}}),s("div",{staticClass:"tl_formbody_submit"},[s("div",{staticClass:"tl_submit_container"},[s("button",{staticClass:"btn btn-primary tl_submit",attrs:{type:"submit",name:"save",id:"save",accesskey:"s"},on:{click:function(t){return t.preventDefault(),e.saveFormData(t)}}},[e._v("Speichern")]),e._v(" "),s("button",{staticClass:"btn btn-primary tl_submit",attrs:{type:"submit",name:"saveandclose",id:"saveandclose",accesskey:"c"},on:{click:function(t){return t.preventDefault(),e.saveFormDataAndClose(t)}}},[e._v("Speichern und schließen")])])])],1)}),l=[],o=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{staticClass:"aufstellung"},[s("div",{staticClass:"team-lineup"},[s("TeamLineup",{attrs:{name:e.home.name,suffix:"H",available:e.home.available,lineup:e.home.lineup,slots:e.slots,showspielerpass:e.showspielerpass}})],1),s("div",{staticClass:"team-lineup"},[s("TeamLineup",{attrs:{name:e.away.name,suffix:"G",available:e.away.available,lineup:e.away.lineup,slots:e.slots,showspielerpass:e.showspielerpass}})],1)])},u=[],c=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{staticClass:"team-lineup"},[s("h2",[e._v(e._s(e.name))]),e._l(e.slots,(function(t){return s("div",{key:"lps_"+t},[s("LineupPlayerSelect",{attrs:{suffix:e.suffix,slotNumber:t,available:e.available,lineup:e.lineup,showspielerpass:e.showspielerpass},on:{lineupplayerchanged:e.lineupplayerchanged}})],1)}))],2)},p=[],h=(s("c5f6"),function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{staticClass:"linup-player"},[s("span",{staticClass:"slot"},[e._v(e._s(e.suffix)+e._s(e.slotNumber))]),s("select",{directives:[{name:"model",rawName:"v-model",value:e.selected,expression:"selected"}],staticClass:"tl_select unsetwidth",attrs:{name:e.selectname},on:{change:function(t){var s=Array.prototype.filter.call(t.target.options,(function(e){return e.selected})).map((function(e){var t="_value"in e?e._value:e.value;return t}));e.selected=t.target.multiple?s:s[0]}}},e._l(e.available,(function(t){return s("option",{key:"player_"+t.id,class:{isNotAvailable:!e.isAvailable(t.id)},attrs:{disabled:!e.isAvailable(t.id)},domProps:{value:t.id}},[e._v(" "+e._s(t.name)+" "),e.showspielerpass&&t.pass>0?s("span",[e._v("("+e._s(t.pass)+")")]):e._e()])})),0)])}),d=[],m=(s("6762"),s("2fdb"),{name:"LineupPlayerSelect",props:{available:{type:Array,required:!0},lineup:{type:Array,required:!0},slotNumber:{type:Number,required:!0},suffix:{type:String,required:!0},showspielerpass:{type:Boolean,default:!1}},data:function(){return{selected:this.slotNumber<=this.lineup.length?this.lineup[this.slotNumber-1]:0}},computed:{selectname:function(){return this.suffix+"_lineup_player_"+this.slotNumber}},watch:{selected:function(){this.$emit("lineupplayerchanged",this.slotNumber,this.selected)}},methods:{isAvailable:function(e){return 0===e||!this.lineup.includes(e)}}}),f=m,v=(s("f34d"),s("2877")),g=Object(v["a"])(f,h,d,!1,null,null,null),y=g.exports,b={name:"TeamLineup",components:{LineupPlayerSelect:y},props:{name:{type:String,required:!0},suffix:{type:String,required:!0},available:{type:Array,required:!0},lineup:{type:Array,required:!0},slots:{type:Number,required:!0},showspielerpass:{type:Boolean,default:!1}},methods:{lineupplayerchanged:function(e,t){this.$store.dispatch("lineupPlayerChanged",{suffix:this.suffix,slotnumber:e,selected:t})}}},_=b,w=(s("3152"),Object(v["a"])(_,c,p,!1,null,null,null)),S=w.exports,x={name:"Aufstellung",components:{TeamLineup:S},props:{showspielerpass:{type:Boolean,default:!1}},computed:{home:function(){return this.$store.state.home},away:function(){return this.$store.state.away},slots:function(){return this.$store.state.numSlots}}},$=x,k=(s("4d9c"),Object(v["a"])($,o,u,!1,null,null,null)),C=k.exports,T=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{staticClass:"results-table"},[s("table",[s("TableHeader"),s("TableBody",{attrs:{showspielerpass:e.showspielerpass}})],1)])},O=[],N=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("thead",[s("tr",[s("th"),s("th",[e._v(" "+e._s(e.home.name)+" ")]),s("th",[e._v(" "+e._s(e.away.name)+" ")]),s("th",[e._v(" "+e._s(e.home.name)+" ")]),s("th",[e._v(" "+e._s(e.away.name)+" ")]),s("th",[e._v(" Spiel ")]),s("th",[e._v(" Gesamt ")])])])},A=[],E={name:"TableHeader",computed:{home:function(){return this.$store.state.home},away:function(){return this.$store.state.away}}},P=E,q=(s("247d"),Object(v["a"])(P,N,A,!1,null,null,null)),D=q.exports,j=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("tbody",[e._l(e.spielplan,(function(t,n){return s("tr",{key:"row_"+n,class:{double:t.home.length>1,single:1==t.home.length}},[s("td",[s("span",{staticClass:"slot"},[e._v(e._s(n+1))])]),s("td",[s("SpielerSelect",{attrs:{team:e.home,position:t.home,index:n,showspielerpass:e.showspielerpass}})],1),s("td",[s("SpielerSelect",{attrs:{team:e.away,position:t.away,index:n,showspielerpass:e.showspielerpass}})],1),s("td",{staticClass:"narrow centered"},[s("SpielerScore",{attrs:{team:e.home,index:n}})],1),s("td",{staticClass:"narrow centered"},[s("SpielerScore",{attrs:{team:e.away,index:n}})],1),s("td",{staticClass:"narrow centered"},[s("SpielErgebnis",{staticClass:"spiel",attrs:{index:n}})],1),s("td",{staticClass:"narrow centered"},[null!=t.scores.home&&null!=t.scores.away?s("span",[s("SpielStand",{staticClass:"gesamt",attrs:{index:n,spielplan:e.spielplan}})],1):e._e()])])})),s("tr",[s("td",{attrs:{colspan:"5"}}),s("td",{staticClass:"centered",attrs:{colspan:"1"}},[s("LegStand",{staticClass:"gesamt",attrs:{index:e.spielplan.length,spielplan:e.spielplan}})],1),s("td",{staticClass:"centered",attrs:{colspan:"1"}},[s("SpielStand",{staticClass:"gesamt",attrs:{index:e.spielplan.length,spielplan:e.spielplan}})],1)])],2)},H=[],I=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("span",[s("select",{directives:[{name:"model",rawName:"v-model",value:e.selected,expression:"selected"}],staticClass:"tl_select unsetwidth",class:{double:e.isDouble,winner:e.isWinner,loser:e.isLoser},attrs:{title:"SpielerSelect",name:e.selectname,tabindex:"-1"},on:{change:function(t){var s=Array.prototype.filter.call(t.target.options,(function(e){return e.selected})).map((function(e){var t="_value"in e?e._value:e.value;return t}));e.selected=t.target.multiple?s:s[0]}}},e._l(e.team.lineup.length,(function(t){return s("option",{key:"pl1_"+(t-1),domProps:{value:t-1}},[e._v(e._s(e.spielername(t-1))+" ")])})),0),e.isDouble?s("select",{directives:[{name:"model",rawName:"v-model",value:e.selected2,expression:"selected2"}],staticClass:"tl_select unsetwidth",class:{double:e.isDouble,winner:e.isWinner,loser:e.isLoser},attrs:{title:"SpleierSelect2",name:e.selectname2,tabindex:"-1"},on:{change:function(t){var s=Array.prototype.filter.call(t.target.options,(function(e){return e.selected})).map((function(e){var t="_value"in e?e._value:e.value;return t}));e.selected2=t.target.multiple?s:s[0]}}},e._l(e.team.lineup.length,(function(t){return s("option",{key:"pl2_"+(t-1),domProps:{value:t-1}},[e._v(e._s(e.spielername(t-1))+" ")])})),0):e._e()])},B=[],L=(s("7f7f"),{name:"SpielerSelect",props:{team:{type:Object},position:{type:Array},index:{type:Number},showspielerpass:{type:Boolean,default:!1}},methods:{spielername:function(e){"undefined"===typeof e&&(e=0);var t=this.team.lineup[e];"undefined"===typeof t&&(t=0);var s=this.team.available.filter((function(e){return e.id===t}));if(0===s.length)return"Kein Name für Pos. "+e;var n="home"===this.team.key?"H":"G";return"("+n+(e+1)+") "+s[0].name+(this.showspielerpass&&s[0].pass>0?" ("+s[0].pass+")":"")}},computed:{spielplan:function(){return this.$store.state.spielplan},selectname:function(){return"spieler_"+this.team.key+"_"+this.index+(this.isDouble?"_1":"")},selectname2:function(){return"spieler_"+this.team.key+"_"+this.index+(this.isDouble?"_2":"")},selected:{get:function(){return this.team.played[this.index].ids[0]},set:function(e){this.$store.dispatch("resultsTablePlayerChanged",{key:this.team.key,index:this.index,position:0,value:e})}},selected2:{get:function(){return this.team.played[this.index].ids[1]},set:function(e){this.$store.dispatch("resultsTablePlayerChanged",{key:this.team.key,index:this.index,position:1,value:e})}},isWinner:function(){var e="home"===this.team.key?"away":"home",t=this.spielplan[this.index];return null!=t.scores[this.team.key]&&null!=t.scores[e]&&(""!==t.scores[this.team.key]&&""!==t.scores[e]&&t.scores[this.team.key]>t.scores[e])},isLoser:function(){var e="home"===this.team.key?"away":"home",t=this.spielplan[this.index];return null!=t.scores[this.team.key]&&null!=t.scores[e]&&(""!==t.scores[this.team.key]&&""!==t.scores[e]&&t.scores[this.team.key]<t.scores[e])},isDouble:function(){return this.position.length>1}}}),U=L,R=(s("cb4d"),Object(v["a"])(U,I,B,!1,null,null,null)),F=R.exports,M=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("input",{directives:[{name:"model",rawName:"v-model.number",value:e.score,expression:"score",modifiers:{number:!0}}],staticClass:"form-control spieler-score",attrs:{name:e.inputname,type:"number",min:"0",max:"3",autocomplete:"off",title:"Spieler Score"},domProps:{value:e.score},on:{input:function(t){t.target.composing||(e.score=e._n(t.target.value))},blur:function(t){return e.$forceUpdate()}}})},W=[],G={name:"SpielerScore",props:{team:{type:Object,required:!0},index:{type:Number,required:!0}},computed:{spielplan:function(){return this.$store.state.spielplan},inputname:{get:function(){return"score_"+this.team.key+"_"+this.index}},score:{get:function(){return this.spielplan[this.index].scores[this.team.key]},set:function(e){var t=null,s=this.spielplan[this.index];s.scores[this.team.key]=""===e?null:e,null!=s.scores["home"]&&null!=s.scores["away"]&&(s.scores["home"]===s.scores["away"]?t="1:1":s.scores["home"]<s.scores["away"]?t="0:1":s.scores["home"]>s.scores["away"]&&(t="1:0")),s.result=t,this.spielplan.splice(this.index,1,s)}}}},V=G,z=(s("6cc0"),Object(v["a"])(V,M,W,!1,null,null,null)),J=z.exports,K=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("span",[e._v(e._s(e.result))])},Q=[],X={name:"SpielErgebnis",props:{index:{type:Number,required:!0}},computed:{result:function(){return this.$store.state.spielplan[this.index].result}}},Y=X,Z=Object(v["a"])(Y,K,Q,!1,null,null,null),ee=Z.exports,te=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("span",{staticClass:"legsstand"},[e._v(e._s(e.legsstand[1])+":"+e._s(e.legsstand[2]))])},se=[],ne={name:"LegStand",props:{index:{type:Number,required:!0},spielplan:{type:Array,required:!0}},computed:{legsstand:function(){return this.spielplan.reduce((function(e,t,s){return s<=e[0]&&t.scores&&null!=t.scores.home&&null!=t.scores.away&&(e[1]+=t.scores.home,e[2]+=t.scores.away),e}),[this.index,0,0])}}},ae=ne,ie=Object(v["a"])(ae,te,se,!1,null,null,null),re=ie.exports,le=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("span",{staticClass:"spielstand"},[e._v(e._s(e.spielstand[1])+":"+e._s(e.spielstand[2]))])},oe=[],ue={name:"SpielStand",props:{index:{type:Number,required:!0},spielplan:{type:Array,required:!0}},computed:{spielstand:function(){return this.spielplan.reduce((function(e,t,s){return s<=e[0]&&null!==t.scores.home&&null!==t.scores.away&&(t.scores.home>t.scores.away?e[1]+=1:t.scores.home<t.scores.away&&(e[2]+=1)),e}),[this.index,0,0])}}},ce=ue,pe=Object(v["a"])(ce,le,oe,!1,null,null,null),he=pe.exports,de={name:"TableBody",components:{SpielerSelect:F,SpielerScore:J,SpielErgebnis:ee,LegStand:re,SpielStand:he},props:{showspielerpass:{type:Boolean,default:!1}},computed:{home:function(){return this.$store.state.home},away:function(){return this.$store.state.away},spielplan:function(){return this.$store.state.spielplan}},methods:{getResult:function(e,t){return e+t}}},me=de,fe=(s("4431"),Object(v["a"])(me,j,H,!1,null,null,null)),ve=fe.exports,ge={name:"ResultsTable",components:{TableHeader:D,TableBody:ve},props:{showspielerpass:{type:Boolean,default:!1}}},ye=ge,be=(s("04f9"),Object(v["a"])(ye,T,O,!1,null,null,null)),_e=be.exports,we=function(){var e=this,t=e.$createElement,s=e._self._c||t;return e.sortedPlayedAll.length>0?s("div",{staticClass:"highlights"},[s("h2",[e._v("Highlights")]),s("table",[e._m(0),s("tbody",e._l(e.sortedPlayedAll,(function(t,n){return s("tr",{key:"pr_"+n},[s("td",[e._v(" ("+e._s(t.abbrev)+") "+e._s(t.name)+" "),s("span",{directives:[{name:"show",rawName:"v-show",value:e.showspielerpass,expression:"showspielerpass"}]},[e._v("("+e._s(t.pass)+")")])]),s("td",[s("NumberInput",{attrs:{inputname:"one80_"+t.id,placeholder:"180er"}})],1),s("td",[s("NumberInput",{attrs:{inputname:"one71_"+t.id,placeholder:"171er"}})],1),s("td",[s("NumberListInput",{attrs:{inputname:"highfinish_"+t.id,placeholder:"Highfinishes"}})],1),s("td",[s("NumberListInput",{attrs:{inputname:"shortleg_"+t.id,placeholder:"Shortlegs"}})],1)])})),0)])]):e._e()},Se=[function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("thead",[s("tr",[s("th",[e._v("Spieler")]),s("th",[e._v("180er")]),s("th",[e._v("171er")]),s("th",[e._v("Highfinish")]),s("th",[e._v("Shortleg")])])])}],xe=(s("4917"),s("55dd"),function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("input",{staticClass:"form-control number-input",class:{valid:e.isValid,invalid:!e.isValid},attrs:{name:e.inputname,placeholder:e.placeholder,autocomplete:"off"},domProps:{value:e.value},on:{change:e.update}})}),$e=[],ke={name:"NumberInput",props:{inputname:{type:String,required:!0},placeholder:{type:String,default:""}},computed:{value:function(){return this.$store.state.highlights[this.inputname]},isValid:function(){return void 0===this.val||String(this.val).match(/^\d*$/)}},methods:{update:function(e){this.$store.dispatch("setHighlight",{key:e.target.name,value:e.target.value})}}},Ce=ke,Te=(s("e86a"),Object(v["a"])(Ce,xe,$e,!1,null,null,null)),Oe=Te.exports,Ne=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("input",{staticClass:"form-control numberlist-input",class:{valid:e.isValid,invalid:!e.isValid},attrs:{name:e.inputname,placeholder:e.placeholder,autocomplete:"off"},domProps:{value:e.value},on:{change:e.update}})},Ae=[],Ee={name:"NumberListInput",props:{inputname:{type:String,required:!0},placeholder:{type:String,default:""}},computed:{value:function(){return this.$store.state.highlights[this.inputname]},isValid:function(){return void 0===this.val||String(this.val).match(/^(\d+,{0,1})*$/)}},methods:{update:function(e){this.$store.dispatch("setHighlight",{key:e.target.name,value:e.target.value})}}},Pe=Ee,qe=(s("e58b"),Object(v["a"])(Pe,Ne,Ae,!1,null,null,null)),De=qe.exports,je={name:"HighlightsEntry",components:{NumberInput:Oe,NumberListInput:De},props:{showspielerpass:{type:Boolean,default:!1}},computed:{sortedPlayedAll:function(){var e=this.$store.getters.playedAll.slice(0);return e.sort((function(e,t){return e.abbrev.match(/^H/)&&t.abbrev.match(/^G/)?-1:e.abbrev.match(/^G/)&&t.abbrev.match(/^H/)?1:e.abbrev>t.abbrev?1:-1}))}}},He=je,Ie=(s("4f4b"),Object(v["a"])(He,we,Se,!1,null,null,null)),Be=Ie.exports,Le={name:"begegnungs-erfassung",props:{data:{type:Object,required:!0}},components:{Aufstellung:C,ResultsTable:_e,HighlightsEntry:Be},data:function(){return{showspielerpass:!1}},computed:{requestToken:function(){return this.$store.state.requestToken},begegnungId:function(){return this.$store.state.begegnungId},dataToSubmit:function(){return JSON.stringify({spielplan:this.$store.state.spielplan,spielplan_code:this.$store.state.spielplan_code,home:this.$store.state.home,away:this.$store.state.away,highlights:this.$store.state.highlights,begegnungId:this.$store.state.begegnungId,REQUEST_TOKEN:this.$store.state.requestToken,FORM_SUBMIT:"begegnungserfassung"})}},methods:{saveFormData:function(){var e=new FormData(document.querySelector("#vue_begegnungserfassung"));this.$store.dispatch("saveData",e)},saveFormDataAndClose:function(){var e=new FormData(document.querySelector("#vue_begegnungserfassung"));this.$store.dispatch("saveDataAndClose",e)},setData:function(e){this.$store.dispatch("setNumSlots",e.numSlots),this.$store.dispatch("setSpielplan",e.spielplan),this.$store.dispatch("setHome",e.home),this.$store.dispatch("setAway",e.away),this.$store.dispatch("setRequestToken",void 0!==e.requestToken?e.requestToken:""),this.$store.dispatch("setBegegnungId",void 0!==e.begegnungId?e.begegnungId:0),this.$store.dispatch("initializeData"),this.$store.dispatch("setSpielplanCode",e.spielplan_code),this.$store.dispatch("setHighlightsData",void 0!==e.highlights?e.highlights:{}),this.$store.dispatch("setWebserviceUrl",void 0!==e.webserviceUrl?e.webserviceUrl:"")}},mounted:function(){this.setData(this.data)}},Ue=Le,Re=(s("6cb4"),Object(v["a"])(Ue,r,l,!1,null,null,null)),Fe=Re.exports,Me={name:"app",components:{BegegnungsErfassung:Fe},data:function(e){function t(){return e.apply(this,arguments)}return t.toString=function(){return e.toString()},t}((function(){return{data:data}}))},We=Me,Ge=Object(v["a"])(We,a,i,!1,null,null,null),Ve=Ge.exports,ze=s("2f62"),Je={home:{key:"home",name:"",available:[],lineup:[],played:[]},away:{key:"away",name:"",available:[],lineup:[],played:[]},highlights:{},spielplan:[],spielplan_code:"",numSlots:0,requestToken:"",begegnungId:"",webserviceUrl:""},Ke=(s("ac4d"),s("8a81"),s("ac6a"),s("28dd"));n["a"].use(Ke["a"]);var Qe={setData:function(e,t){e.commit("setData",t)},setHome:function(e,t){e.commit("setHome",t)},setAway:function(e,t){e.commit("setAway",t)},setSpielplan:function(e,t){e.commit("setSpielplan",t)},setSpielplanCode:function(e,t){e.commit("setSpielplanCode",t)},setNumSlots:function(e,t){e.commit("setNumSlots",t)},setRequestToken:function(e,t){e.commit("setRequestToken",t)},setBegegnungId:function(e,t){e.commit("setBegegnungId",t)},initializeData:function(e){e.commit("initializeData")},lineupPlayerChanged:function(e,t){e.commit("lineupPlayerChanged",t)},resultsTablePlayerChanged:function(e,t){e.commit("resultsTablePlayerChanged",t)},setHighlight:function(e,t){e.commit("setHighlight",t)},setHighlightsData:function(e,t){e.commit("setHighlightsData",t)},setWebserviceUrl:function(e,t){e.commit("setWebserviceUrl",t)},saveData:function(e,t){var s=e.state.webserviceUrl+e.state.begegnungId,a=!0,i=!1,r=void 0;try{for(var l,o=t.entries()[Symbol.iterator]();!(a=(l=o.next()).done);a=!0){var u=l.value;console.log("%s %o",u[0],u[1])}}catch(c){i=!0,r=c}finally{try{a||null==o.return||o.return()}finally{if(i)throw r}}console.log("%o",t.entries()),n["a"].http.post(s,t).then((function(e){alert(e.data)})).catch((function(e){alert(e.url+" : "+e.statusText)}))},saveDataAndClose:function(e,t){alert("save and close ist noch TODO"),e.dispatch("saveData",t)}};function Xe(e){return Array.apply(null,new Array(e)).map((function(){return 0}))}var Ye={setHome:function(e,t){e.home=t},setAway:function(e,t){e.away=t},setSpielplan:function(e,t){e.spielplan=t},setSpielplanCode:function(e,t){e.spielplan_code=t},setNumSlots:function(e,t){e.numSlots=t},setRequestToken:function(e,t){e.requestToken=t},setBegegnungId:function(e,t){e.begegnungId=t},initializeData:function(e){0===e.home.lineup.length&&(e.home.lineup=Xe(e.numSlots)),0===e.away.lineup.length&&(e.away.lineup=Xe(e.numSlots)),e.spielplan.forEach((function(e){"undefined"===typeof e.scores&&(e.scores={home:null,away:null}),"undefined"===typeof e.result&&(e.result=null)})),0===e.home.played.length&&e.spielplan.forEach((function(t,s){e.home.played.push({ids:t.home,slot:s+1})})),0===e.away.played.length&&e.spielplan.forEach((function(t,s){e.away.played.push({ids:t.away,slot:s+1})}))},lineupPlayerChanged:function(e,t){"H"===t.suffix?n["a"].set(e.home.lineup,t.slotnumber-1,t.selected):n["a"].set(e.away.lineup,t.slotnumber-1,t.selected)},resultsTablePlayerChanged:function(e,t){console.log("resultsTablePlayerChanged: "+JSON.stringify(t)),"home"===t.key?n["a"].set(e.home.played[t.index].ids,t.position,t.value):n["a"].set(e.away.played[t.index].ids,t.position,t.value)},setHighlight:function(e,t){n["a"].set(e.highlights,t.key,t.value)},setHighlightsData:function(e,t){n["a"].set(e,"highlights",t)},setWebserviceUrl:function(e,t){t.match(/\/$/)||(t+="/"),n["a"].set(e,"webserviceUrl",t)}},Ze=(s("5df3"),s("4f7f"),s("75fc")),et=function(e,t){var s=e.home.lineup.indexOf(t);return s>-1?"H"+(s+1):(s=e.away.lineup.indexOf(t),s>-1?"G"+(s+1):t)},tt=function(e,t,s){var n=e.home.available.filter((function(e){return e.id===t}));return n.length>0?n[0][s]:(n=e.away.available.filter((function(e){return e.id===t})),n.length>0?n[0][s]:t)},st={playedAll:function(e){var t=[];return e.spielplan.forEach((function(s){t.push(e.home.lineup[s.home[0]]),t.push(e.away.lineup[s.away[0]]),s.home.length>1&&(t.push(e.home.lineup[s.home[1]]),t.push(e.away.lineup[s.away[1]]))})),Object(Ze["a"])(new Set(t.filter((function(e){return e>0})))).map((function(t){return{id:t,name:""+tt(e,t,"name"),abbrev:""+et(e,t),pass:tt(e,t,"pass")}}))}};n["a"].use(ze["a"]);var nt=new ze["a"].Store({state:Je,actions:Qe,mutations:Ye,getters:st});n["a"].config.productionTip=!1,new n["a"]({store:nt,render:function(e){return e(Ve)}}).$mount("#app")},"5d35":function(e,t,s){},6253:function(e,t,s){},"6cb4":function(e,t,s){"use strict";var n=s("d60a"),a=s.n(n);a.a},"6cc0":function(e,t,s){"use strict";var n=s("3ce2"),a=s.n(n);a.a},8856:function(e,t,s){},cb4d:function(e,t,s){"use strict";var n=s("4245"),a=s.n(n);a.a},d60a:function(e,t,s){},e2cd:function(e,t,s){},e58b:function(e,t,s){"use strict";var n=s("009b"),a=s.n(n);a.a},e86a:function(e,t,s){"use strict";var n=s("3775"),a=s.n(n);a.a},f34d:function(e,t,s){"use strict";var n=s("2b1a"),a=s.n(n);a.a}});