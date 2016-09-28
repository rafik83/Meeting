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
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-UserEvent.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    When I go to this page "/admin/fr/event/1/users"
    Then I should see "user_asddays_1@proximum.com"
    And I should see "user_asddays_2@proximum.com"
    And I should see "user_asddays_3@proximum.com"

  Scenario: I can filter users by type
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    When I go to this page "/admin/fr/event/1/users"
    And I follow "Investisseur"
    Then I should see "user_asddays_1@proximum.com"
    And I should not see "user_asddays_2@proximum.com"
    And I should not see "user_asddays_3@proximum.com"
    And I follow "Fournisseur"
    Then I should not see "user_asddays_1@proximum.com"
    And I should see "user_asddays_2@proximum.com"
    And I should not see "user_asddays_3@proximum.com"

  Scenario: I can see details information from an user
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    When I go to this page "/admin/fr/event/1/users/39"
    Then I should see "gender.man"
    And I should see "Martin"
    And I should see "Dupont"
    And I should see "Consultant"
    And I should see "10 rue des lilas"
    And I should see "75002"
    And I should see "user_asddays_1@proximum.com"
    And I should see "0668973246"
    And I should see "www.monsite.fr"
    And I should see "Elao"
    And I should see "10 rue Saint Marc"
    And I should see "Paris"
    And I should see "FR"
    And I should see "www.elao.com"
