# Was ist das?

- Geschichte des Mutation-Testings
    - seit ...
    - In jeder Programmiersprache (Beispiele)
    - alles folgt aber dem gleichen Prinzip:

Das grundsätzliche Prinzip sieht wie folgt aus:
Der Quellcode einer Anwendung wird verändert (Mutation). Nach der Änderung wird geprüft, ob die automatischen Tests diese Änderungen registrieren indem sie fehlschlagen.
Laufen die Tests weiterhin erfolgreich durch (die Mutation überlebt), sind die geschriebenen Tests nicht ausreichend um potentielle Bugs zu entdecken. 

Wie läuft nun das Mutation-Testing ab:
Zu allererst wird die komplette Test-Suite durchlaufen. Nur wenn alle Tests erfolgreich sind, werden die nächsten Schritte ausgeführt.
Im nächsten Schritt werden dann die Mutanten auf den Code losgelassen. Sie verändern den Quellcode.
Nach jeder Veränderung des Quellcodes werden nun die Tests ausgeführt, die den veränderten Quellcode abdecken. Dabei wird protokolliert, welche Mutationen zu welchem Ergebnis geführt haben.
Somit können sich folgende Status ergeben
- "killed": Die Mutation wurde durch einen Test erkannt.
- "survived": Die Mutation wurde nicht erkannt. Es besteht die Möglichkeit, dass sich ein Bug unbemerkt von Tests in den Quellcode einschleicht.
- "timeout": Die Veränderungen des Quellcodes haben dafür gesorgt, dass das Ausführen des Codes eine (vor-definierte) Zeitbegrenzung überschritten hat.
- "uncovered": Es gibt keine Test für den Quellcode.

Ein Lehrbuch-Beispiel zum einfacheren Verständnis wie Mutation-Testing funktioniert ist eine klassische add-Methode:  
  ```
  function add(a, b){ return a + b}
  ```
Ein erfolgreicher Test mit 100% Code coverage kann folgendermaßen aussehen:
  ```
  function testAddFunction(){ assert(add(1,0) === 1)}
  ```
Doch was passiert nun, wenn der Quell-Code wie folgt verändert wird:   
  ```
  function add(a, b){ return a - b}
  ```
Der Test wird weiterhin erfolgreich durchlaufen, denn nicht nur `1 + 0 = 1`, sondern auch `1 - 0 = 1`. Ein potentieller Bug bleibt somit unerkannt. 
Um dem entgegen zu wirken, benötigt es einer minimalen Änderung:
  ```
  function testAddFunction(){ assert(add(1,1) === 2)}
  ```
Somit ist das Testergebnis für `1 + 1 = 2` weiterhin richtig, allerdings ist nun eine Mutation des Operanden nicht mehr möglich, da der  `1 - 1 = 2` einfach falsch ist. 

# Wie/Warum setzen wir es ein?
- Crazy Zusätzlicher Kollege: Edge cases werden dadurch einfach sichtbar

- Neben verschiedenen anderen Test-Tools, ist Mutation testing ein weiteres Tool in unserem Entwickler-Baukasten, dass
  wir für die Qualitätssicherung in unseren Projekten benutzen.

- Code-Beispiel aus der Realität
- Ausgangs-Methode:
```phpt
if ($argumentTicketNumber !== null && is_string($argumentTicketNumber)) {
    return $argumentTicketNumber;
}
```
Mutation: 
```phpt
if ($argumentTicketNumber !== null || is_string($argumentTicketNumber)) {
    return $argumentTicketNumber;
}
```
Ausgangs-Methode
```phpt
['Time spent' => $issueData->aggregateTimeSpent ?? $issueData->timeSpent]
```
Mutation (Coalesce)
```phpt
['Time spent' => $issueData->timeSpent ?? $issueData->aggregateTimeSpent ]
```

Original code

```phpt
public function getProjectUrl(string $gitlabUrl, string $projectIdentifier): string
{
    return rtrim($gitlabUrl, '/') . '/projects/' . $projectIdentifier . '/';
}
```

Original test
```phpt
public function testGetProjectUrl(): void
{
    $configurationMock = $this->createMock(Configuration::class);
    $gitlabHttpClientMock = $this->createMock(GitlabHttpClient::class);

    $gitlabClient = new GitlabClient(
        $gitlabHttpClientMock,
        $configurationMock
    );

    self::assertEquals('gitlaburl/projects/projectId/', $gitlabClient->getProjectUrl('gitlaburl', 'projectId'));
}
```
Rtrim mutation
```phpt
public function getProjectUrl(string $gitlabUrl, string $projectIdentifier): string
{
    return $gitlabUrl . '/projects/' . $projectIdentifier . '/';
}
```

Angepasster test (mit "/")
```phpt
public function testGetProjectUrl(): void
{
    $configurationMock = $this->createMock(Configuration::class);
    $gitlabHttpClientMock = $this->createMock(GitlabHttpClient::class);

    $gitlabClient = new GitlabClient(
        $gitlabHttpClientMock,
        $configurationMock
    );

    self::assertEquals('gitlaburl/projects/projectId/', $gitlabClient->getProjectUrl('gitlaburl/', 'projectId'));
}
```
# Was ist unsere Einschätzung?

- Code coverage ist gut, heißt aber nicht, dass die Test-Qualität gut ist -> Quality over Quantity
  -> Code coverage: Lines tested
  -> Mutation testing: Logical testing

- Kann nicht immer laufen, da sehr zeitintensiv (Jede Mutante erwirkt eine erneuten Durchlauf der gesamten Test-Suite.)
  -> es empfiehlt sich, Mutation-Testings per githook zu triggern
  -> und vielleicht in bestimmten Abständen (nightly) über alle Tests laufen lassen?
  -> oder Einschränkung des Mutanten-Sets (gibt es dafür ein gutes Beispiel)???
MSI als mögliche Kennziffer für Qualitätsstand in Pipeline integriert
