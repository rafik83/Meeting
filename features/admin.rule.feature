Feature: add rule who see who
  I need to be able to add a rule for categories and types

  Scenario: add rule
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/EventRdvCarnot2016.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/TypeRdvCarnot2016.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/Category.yml                      |
      | @InfrastructureBundle/DataFixtures/ORM/Rule.yml                          |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event/1/who-see-who"
    When I fill in the following:
      | who_see_who_seer    | category:2 |
      | who_see_who_seeable | type:1     |
    And I press "form.who_see_who.children.submit.label"
    Then I should be on this page "/admin/fr/event/1/category/2/see/type/1/dont-see"
    When I check "dont_see_what_participant_563caf2f0ddbd"
    And I press "form.dont_see_what.children.submit.label"
    Then I should be on this page "/admin/fr/event/1/who-see-who"
    And I should see "flash.admin.event.who_see_what.success"
