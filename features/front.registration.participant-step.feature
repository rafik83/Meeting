Feature: Register with participant step
  I need to be able to register and fill information during the registration

  Scenario: Register an user in 3 steps
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/EventASDDays2016-1.yml |
      | @InfrastructureBundle/DataFixtures/ORM/EventASDDays2016-2.yml |
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I go to this page "/fr/participant/1/step/3"
    Then I should see "Organisme"
    And I should see "register.step 3/3"
    And I fill in the following:
      | Nom (Société / Organisme) | Elao                  |
      | Adresse                   | 10 rue Saint Marc     |
      | Code postal               | 75002                 |
      | Ville                     | Paris                 |
      | block_e801edd4            | FR                    |
    And I press "register.finalize"
    Then I should be on this page "/fr/sheet"

