# Was ist das?

- Geschichte des Mutation-Testings
    - seit ...
    - In jeder Programmiersprache (Beispiele)
    - alles folgt aber dem gleichen Prinzip:
- Veränderung des Quell-Codes und Testen mit den geschrieben Tests (Erstellung von Mutanten).
  Bleiben die Tests "grün" (die Mutante überlebt), ist der Test nicht "gut genug".
- 4+0 = 4 <=> 4-0 = 4
- States für Mutanten
    - uncovered
    - survived
    - killed
    - timeout

# Wie/Warum setzen wir es ein?
- Crazy Zusätzlicher Kollege: Edge cases werden dadurch einfach sichtbar

- Neben verschiedenen anderen Test-Tools, ist Mutation testing ein weiteres Tool in unserem Entwickler-Baukasten, dass
  wir für die Qualitätssicherung in unseren Projekten benutzen.

- Code-Beispiel aus der Realität

# Was ist unsere Einschätzung?

- Code coverage ist gut, heißt aber nicht, dass die Test-Qualität gut ist -> Quality over Quantity
  -> Code coverage: Lines tested
  -> Mutation testing: Logical testing

- Kann nicht immer laufen, da sehr zeitintensiv (Jede Mutante erwirkt eine erneuten Durchlauf der gesamten Test-Suite.)
  -> es empfiehlt sich, Mutation-Testings per githook zu triggern
  -> und vielleicht in bestimmten Abständen (nightly) über alle Tests laufen lassen?
  -> oder Einschränkung des Mutanten-Sets (gibt es dafür ein gutes Beispiel)???
