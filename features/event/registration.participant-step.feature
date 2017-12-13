@event @participant @registration @mail
Feature: Register with participant step
  I need to be able to register and fill information during the registration

  Scenario: Register an user in 3 steps
    Given the database is purged
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
    And I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/participant/1/step/3"
    Then I should see "Organisme"
    And I should see "register.step 3/3"
    And I should see "Ceci est une description"
    And I fill in the following:
      | Nom (Société / Organisme)     | Elao              |
      | block[97ed778d][item][first]  | category1         |
      | Adresse                       | 10 rue Saint Marc |
      | Code postal                   | 75002             |
      | Ville                         | Paris             |
      | Pays                          | FR                |
      | block[57da9df7ced30][boolean] | 1                 |
      | Décrivez votre activité       |                   |
    When I press "register.finalize"
    Then I should see "This value should not be blank."
    And I fill in the following:
    | Décrivez votre activité | Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description|
    When I press "register.finalize"
    Then I should be on this page "/fr/participant/1/step/3"
    And I should see "This value is too long. It should have 300 characters or less."
    And I fill in the following:
      | Décrivez votre activité | Ceci est une description |
    When I press "register.finalize"
    Then the "event.preregistered" mail should be sent to "user_asddays_1@proximum.com" from "no-reply@asddays-2016.vimeet.proximum"
    Then the "event.preregistered" mail should be sent in bcc to "team-project@example.net" from "no-reply@asddays-2016.vimeet.proximum"
    And I should be on this page "/fr/sheet/1"
    And I should see "sheet.welcome.title"
    And I should see "sheet.welcome.sheetContent"
    And I should see "sheet.welcome.packageContent"
