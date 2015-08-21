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
- id

Am Beispiel eines Achievements, welches beim drüberlaufen gefeuert wird und die Bernd weiterlaufen lässt:

```
  <path
     stopsWalk="false"
     type="achievement"
     trigger="walkon"
     id="first_step" ... />
```

Dies aktiviert das Achievement "first_step" (wie definiert, siehe `js/achievements.js`)

Ganz wichtig in dieser Ebene: Ein Objekt mit der ID="player_spawn"!

Konvention: spawn roter Kreis, walkon pink, mapchange grün, ... ; Ebene leicht transparent