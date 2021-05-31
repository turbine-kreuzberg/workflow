# Was ist das?
First of all: Mutation testing is nothing new. The concept is around for quite a while. 
Regarding to Wikipedia (https://en.wikipedia.org/wiki/Mutation_testing) it was proposed already in 1971 by Richard Lipton.  
For a long time it was just an academic approach because of its cost and time intensity. But as the performance of developer machines
increases from decade to decade, mutation testing had a bit of a revival lately (meaning the last 10 years).
Every big programming language has a mutation testing library 

Service-Box

| Programming language        | Mutation testing framework |
| --------------------------- | -------------------------- |
| PHP                         | https://github.com/infection/infection |
| python                      | https://github.com/boxed/mutmut      |
| JavaScript                  | https://github.com/stryker-mutator/stryker-js |
| golang                      | https://github.com/zimmski/go-mutesting |
| Java                        | https://github.com/hcoles/pitest |
| C#                          | https://github.com/stryker-mutator/stryker-net |
| Rust                        | https://github.com/llogiq/mutagen |

The basic principle looks like this:   
The source code of an application will be changed: a mutation is created. Once the mutation is there, 
the connected tests are executed. If one of them fails, the mutation is "killed". If not, the mutation "survives" 
which means that the existing tests seem to be not prepared

Der Quellcode einer Anwendung wird verändert (Mutation). Nach der Änderung wird geprüft, ob die automatischen Tests diese Änderungen registrieren indem sie fehlschlagen.
Laufen die Tests weiterhin erfolgreich durch (die Mutation überlebt), sind die geschriebenen Tests nicht ausreichend um potentielle Bugs zu entdecken. 

Wie läuft nun das Mutation-Testing ab:
Zu allererst wird die komplette Test-Suite durchlaufen. Nur wenn alle Tests erfolgreich sind, werden die nächsten Schritte ausgeführt.
Im nächsten Schritt werden dann die Mutanten auf den Code losgelassen. Sie verändern den Quellcode.
Nach jeder Veränderung des Quellcodes werden nun die Tests ausgeführt, die den veränderten Quellcode abdecken. Dabei wird protokolliert, welche Mutationen zu welchem Ergebnis geführt haben.
Somit können sich folgende Status ergeben:
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
Doch was passiert nun, wenn der Quellcode wie folgt verändert wird:   
  ```
  function add(a, b){ return a - b}
  ```
Der Test wird weiterhin erfolgreich durchlaufen, denn nicht nur `1 + 0 = 1`, sondern auch `1 - 0 = 1`. Ein potentieller Bug bleibt somit unerkannt. 
Um dem entgegen zu wirken, benötigt es einer minimalen Änderung:
  ```
  function testAddFunction(){ assert(add(1,1) === 2)}
  ```
Somit ist das Testergebnis für `1 + 1 = 2` weiterhin richtig, allerdings ist nun eine Mutation des Operanden nicht mehr möglich, da `1 - 1 = 2` einfach falsch ist. 

# Wie/Warum setzen wir es ein?
If you ever heart of the Netflix "Chaos monkey", you can imagine what mutation testing is helping in "production" environments. 
The mutation tester feels exactly like this, only producing chaos in your source code and helping to find weird edge cases
you haven't thought of while writing your tests.

Our experience shows that especially in long-running projects those edge cases will occur - no matter what. So having mutation
testing as an addition in our development toolbox, is helping us to ensure higher quality in our projects already at the beginning 
but even more in the long run. 

- it can help to remove unneeded code

To make this more tangible we have a real-life example: 
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


Imagine you have to adjust a tiny bit in your source code and according to your opinion and your code coverage
this change is well covered. Without mutation testing you're done but
# Was ist unsere Einschätzung?

- Code coverage ist gut, heißt aber nicht, dass die Test-Qualität gut ist -> Quality over Quantity
  -> Code coverage: Lines tested
  -> Mutation testing: Logical testing

- Kann nicht immer laufen, da sehr zeitintensiv (Jede Mutante erwirkt eine erneuten Durchlauf der gesamten Test-Suite.)
  -> es empfiehlt sich, Mutation-Testings per githook zu triggern
  -> und vielleicht in bestimmten Abständen (nightly) über alle Tests laufen lassen?
  -> oder Einschränkung des Mutanten-Sets (gibt es dafür ein gutes Beispiel)???
MSI als mögliche Kennziffer für Qualitätsstand in Pipeline integriert


Test-Stats: 144 tests
Code coverage: 98,37%
Mutation testing run took: 1 minute, 45 sec
