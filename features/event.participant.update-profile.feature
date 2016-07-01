@event

Feature: Update participant profile
  As a participant, I need to be able to update my profile

  Scenario: I can update the participant profile
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
    And I should see "YY"
    And I should see "Yeb YUPONT"

  Scenario: I can update the participant avatar
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    And I go to this page "/fr/account/sheet/1/participant/1"
    Then I follow "account.profile.updateAvatar"
    And I should be on this page "/fr/account/sheet/1/participant/1/avatar/cb66008e"
    And I should see "account.update.avatar.title"
    And I attach the file "dummy-image-test.jpg" to "avatar[cb66008e][file]"
    And I press "common.validate"
    Then I should be on this page "/fr/account/sheet/1/participant/1"
    And I should not see "YY"

  Scenario: I can update the participant company
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    And I go to this page "/fr/account/sheet/1/participant/1/company"
    And I should see "account.update.company.title"
    Then I fill in the following:
      | Nom (Société / Organisme) | Elao                  |
      | Site internet             | https://www.elao.com  |
      | Adresse                   | 10 rue Saint Marc     |
      | Code postal               | 75002                 |
      | Ville                     | Paris                 |
    And I select "category1" from "company_97ed778d_item_first"
    And I select "FR" from "company_e801edd4_country"
    And I press "common.validate"
    Then I should be on this page "/fr/account/sheet/1/participant/1"
    And I should see "Elao"
    And I should see "https://www.elao.com"
    And I should see "10 rue Saint Marc"
    And I should see "75002"
    And I should see "Paris"
    And I should see "FRANCE"
