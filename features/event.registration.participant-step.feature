@event
@participant
@registration
Feature: Register with participant step
  I need to be able to register and fill information during the registration

  Scenario: Register an user in 3 steps
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml      |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Product.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml             |
    And I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I go to this page "/fr/participant/1/step/3"
    Then I should see "Organisme"
    And I should see "register.step 3/3"
    And I should see "Ceci est une description"
    And I fill in the following:
      | Nom (Société / Organisme)    | Elao              |
      | block[97ed778d][item][first] | category1         |
      | Adresse                      | 10 rue Saint Marc |
      | Code postal                  | 75002             |
      | Ville                        | Paris             |
      | block[e801edd4][country]     | FR                |
    When I press "register.finalize"
    Then the "event.preregistered" mail should be sent to "user_asddays_1@proximum.com"
    Then I should be on this page "/fr/sheet"
