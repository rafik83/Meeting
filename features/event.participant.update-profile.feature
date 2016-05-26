Feature: Update participant profile
  As a participant, I need to be able to update my profile

  Scenario: I can go to the participant sheet of the user
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016Event.yml              |
    And I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    When I go to this page "/fr"
    And I follow "event.link.see_my_sheet"
    Then I should be on this page "/fr/sheet"
    And I should see "JD"
    And I should see "John DOE"
    Then I follow "JD"
    And I should be on this page "/fr/account/sheet/1/participant/1/profile"
    And I should see "account.update.profile.title"
    Then I fill in the following:
      | Prénom             | Seb         |
      | Nom                | Dupont      |
      | Téléphone portable | +0698765432 |
      | Téléphone fixe     | +0198765432 |
    And I press "common.validate"
    Then I should be on this page "/fr/sheet"
    And I should see "SD"
    And I should see "Seb DUPONT"
