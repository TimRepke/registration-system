Wie baut man eine Karte richtig?
=================================


Zum Bauen der Karte wird empfohlen Inkscrape zu nutzen.

## Definitionslayer
Es gibt drei Ebenen, die vorhanden sein müssen. Hierrauf sollten sich keine graphischen Objekte befinden. 
Sie sind ausschließlich der Definition für das Program gedacht.

- WALK
- NOWALK
- EVENT

### WALK

Alle Objekte auf dieser Ebene sind begehbar. Anzulegen mit dem Bezier Kurven tool (aber nur gerade Linien ziehen!).

Konvention: Objekte mit weißem fill, Ebene leicht transparent stellen.

### NOWALK

Same.

Konvention: Objekte mit rotem fill, Ebene leicht transparent stellen.

### EVENT

Hier wird es interessant! Eigentlich kann man die Objekte wie zuvor anlegen. Nachträglich müssen sie noch mit Attributen versehen werden.
Empfohlen hierfür der XML Editor (Edit > XML Editor)

Folgende Attribute sollten gesetzt werden:

- trigger (= walkon, hover, click)
- type (= achievement, mapchange, ...)
- stopsWalk (= true, false)
- destination (optional, target map für mapchange)
- target (optional, spawn point für mapchange)
- id
- action (optional, action to call as defined in Events.actions)
- directAction (optional, set true to fire action before walkTo point is reached)
- walkTo (optional, before calling action, walk to point with this ID)
- condition (optional, expression with vars as defined in Environment.progress, parenthesis allowed, i.e.: `(!someVar||otherVar)&&moreVar`)

Am Beispiel eines Achievements, welches beim drüberlaufen gefeuert wird und die Bernd weiterlaufen lässt:

```
  <path
     stopsWalk="false"
     type="achievement"
     trigger="walkon"
     id="first_step" ... />
```

Dies aktiviert das Achievement "first_step" (wie definiert, siehe `js/achievements.js`)

Ganz wichtig in der Start-Ebene: Ein Objekt mit der ID="player_spawn", ansonsten target nutzen.

Konvention: spawn roter Kreis, walkon pink, mapchange grün, ... ; Ebene leicht transparent

## Special elements

Man kann elementen das Attribut `special_elem` geben. Zum Beispiel mit dem Wert `speech_bubble`. Diese Elemente werden
direkt versteckt und beispielsweise in einer action sichtbar gemacht.