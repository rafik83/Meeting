@event
@participant
Feature: Update participant profile
  As a participant, I need to be able to update my profile

  Scenario: I can update the participant profile
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
    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    And I should see "JD"
    And I should see "John DOE"
    Then I follow "JD"
    And I should be on this page "/fr/account/sheet/1/participant/1"
    Then I follow "common.update"
    And I should be on this page "/fr/account/sheet/1/participant/1/profile"
    And I should see "account.update.profile.title"
    Then I fill in the following:
      | Prénom             | Yeb         |
      | Nom                | Yupont      |
      | Téléphone portable | +0698765432 |
      | Téléphone fixe     | +0198765432 |
    And I check the "gender.man" radio
    And I press "common.validate"
    Then I should be on this page "/fr/account/sheet/1/participant/1"
    And I should see "Yeb YUPONT"

  Scenario: I can update the participant avatar
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr/account/sheet/1/participant/1"
    Then I follow "account.profile.updateAvatar"
    And I should be on this page "/fr/account/sheet/1/participant/1/avatar/cb66008e"
    And I should see "account.update.avatar.title"
    And I attach the file "dummy-image-test.jpg" to "avatar[cb66008e][file]"
    And I press "common.validate"
    Then I should be on this page "/fr/account/sheet/1/participant/1"
    And I should not see "YY"

  Scenario: I can update the participant company
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr/account/sheet/1/company"
    Then I should see "account.update.company.title"
    And I fill in the following:
      | Nom (Société / Organisme)       | Elao                     |
      | Site internet                   | https://www.elao.com     |
      | Adresse                         | 10 rue Saint Marc        |
      | Code postal                     | 75002                    |
      | Ville                           | Paris                    |
      | company[57da9df7ced30][boolean] | 1                        |
      | Pays                            | FR                       |
      | company[97ed778d][item][first]  | category1                |
      | Décrivez votre activité         | Ceci est une description |
    And I press "common.validate"
    Then I should be on this page "/fr/sheet/1"
    And I go to this page "/fr/account/sheet/1/participant/1"
    And I should see "Elao"
    And I should see "https://www.elao.com"
    And I should see "10 rue Saint Marc"
    And I should see "75002"
    And I should see "Paris"
    And I should see "FRANCE"
