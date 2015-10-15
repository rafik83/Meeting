Feature: Hello
  I need to be able to see a greeting with my name

  Scenario: Greeting Maxime
    When I go to "/hello/maxime"
    Then I should see "Bonjour Maxime"
