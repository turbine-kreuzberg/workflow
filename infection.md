# What is mutation testing
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

But enough about theory. Here's an example from a real-life project, which we use in our day-to-day work to make our
developer lives easier. It helps us for example to make our beloved time bookings a little smoother - especially when working in pair or mob.
For getting data out of Jira we have a class `TimeExtractor`.

In this `TimeExtractor` class the following method was implemented to return the worked time for the day (aggregated time
of all tasks). If there was only one task that was worked on, this method will return only the amount of time of this task.
The method looks like this:

```phpt
public function getTimeSpent(JiraIssueTransfer $issueData): int {
  return $issueData->aggregateTimeSpent ?? $issueData->timeSpent;
}
```
The corresponding tests look like this. They cover both described cases and we achieve full code coverage.
```
public function testGetTimeSpentReturnsTimespent(): void
{
    $jiraIssueTransfer = new JiraIssueTransfer();
    $jiraIssueTransfer->timeSpent = 23;

    self::assertEquals(23, (new TimeExtractor())->getTimeSpent($jiraIssueTransfer));
}

public function testGetTimeSpentReturnsAggregateTimeSpent(): void
{
    $jiraIssueTransfer = new JiraIssueTransfer();
    $jiraIssueTransfer->aggregateTimeSpent = 42;

    self::assertEquals(42, (new TimeExtractor())->getTimeSpent($jiraIssueTransfer));
}
```
Now the mutation framework goes to work and among others creates the current mutation. It just swaps the operands.
With this mutation we have a total change of business logic which wasn't tested before:
Now the `TimeExtractor` class would return the worked time for the task (which is always set) 
instead of the aggregated time of all tasks.
```phpt
public function getTimeSpent(JiraIssueTransfer $issueData): int {
  return $issueData->timeSpent ?? $issueData->aggregateTimeSpent;
}
```
The current implemented tests do not catch this change. To kill the mutant we have to adjust one of the existing tests
by adding just the second attribute `timeSpent` with a different value. As a result the swap won't 
work anymore and so the business logic cannot be changed without a failing test. 
```
public function testGetTimeSpentReturnsTimespent(): void
{
    $jiraIssueTransfer = new JiraIssueTransfer();
    $jiraIssueTransfer->timeSpent = 23;

    self::assertEquals(23, (new TimeExtractor())->getTimeSpent($jiraIssueTransfer));
}

public function testGetTimeSpentReturnsAggregateTimeSpent(): void
{
    $jiraIssueTransfer = new JiraIssueTransfer();
    $jiraIssueTransfer->aggregateTimeSpent = 42;
    $jiraIssueTransfer->timeSpent = 23;

    self::assertEquals(42, (new TimeExtractor())->getTimeSpent($jiraIssueTransfer));
}
```
#Conclusion
If you ever heart of the Netflix "Chaos monkey", you can imagine how mutation testing is helping in "production" environments.
The mutation tester acts exactly like this, producing chaos in your source code and helping to find weird edge cases
you haven't even thought of while writing your tests.

Our experience shows that especially in long-running projects those edge cases will occur - no matter what. So having mutation
testing as an addition in our development toolbox, is helping us to ensure higher quality in our projects already at the beginning
but even more in the long run.

Before we were using mutation testing we only had code coverage as a metric to see status of our tests and where we can improve.
But this only meant that we had a look on the quantity of tests (number of lines that are tested).
With mutation testing we now have a second metric which is not about the quantity but the quality of our tests. And additionally 
it improves not only the quality of our tests but also the quality of the code itself. For example we experienced 
while using mutation testing that it really can help to find (and as a consequence remove) unneeded code.

- Kann nicht immer laufen, da sehr zeitintensiv (Jede Mutante erwirkt eine erneuten Durchlauf der gesamten Test-Suite.)
  -> es empfiehlt sich, Mutation-Testings per githook zu triggern
  -> und vielleicht in bestimmten Abständen (nightly) über alle Tests laufen lassen?
  -> oder Einschränkung des Mutanten-Sets (gibt es dafür ein gutes Beispiel)???
MSI als mögliche Kennziffer für Qualitätsstand in Pipeline integriert

Test-Stats: 144 tests
Code coverage: 98,37%
Mutation testing run took: 1 minute, 45 sec
