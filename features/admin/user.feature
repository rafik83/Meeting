@admin
@user
Feature:
  I need to be able to see the users by events and their details information

  Scenario: I can see a list of users
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
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    When I go to this page "/admin/fr/event/1/users"
    Then I should see "user_asddays_1@proximum.com"
    And I should see "user_asddays_2@proximum.com"
    And I should see "user_asddays_3@proximum.com"

  Scenario: I can filter users by sheet type
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    And I go to this page "/admin/fr/event/1/users"
    And I should see "user_asddays_1@proximum.com"
    And I should see "user_asddays_2@proximum.com"
    And I should see "user_asddays_3@proximum.com"
    And I should see "user_asddays_4@proximum.com"
    When I follow "Investisseur"
    Then I should see "user_asddays_3@proximum.com"
    And I should not see "user_asddays_1@proximum.com"
    And I should not see "user_asddays_2@proximum.com"
    And I should not see "user_asddays_4@proximum.com"
    When I follow "Fournisseur"
    Then I should see "user_asddays_1@proximum.com"
    And I should see "user_asddays_2@proximum.com"
    And I should not see "user_asddays_3@proximum.com"
    And I should not see "user_asddays_4@proximum.com"
    When I follow "Donneur d'ordre"
    Then I should see "user_asddays_4@proximum.com"
    And I should not see "user_asddays_1@proximum.com"
    And I should not see "user_asddays_2@proximum.com"
    And I should not see "user_asddays_3@proximum.com"

  Scenario: I can see details information from an user
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    And I go to this page "/admin/fr/event/1/users"
    And I follow "Investisseur"
    And I should see "user_asddays_3@proximum.com"
    When I follow "admin.users.details"
    Then I should see "gender.woman"
    And I should see "Julie Martin"
    And I should see "Community Manager"
    And I should see "user_asddays_3@proximum.com"
