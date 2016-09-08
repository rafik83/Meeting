@admin @rule

Feature: add rule who see who
  I need to be able to add a rule for categories and types

  Scenario: add rule
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Category.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Rule.yml            |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event/1/who-see-who"
    When I fill in the following:
      | who_see_who_seer    | category:2 |
      | who_see_who_seeable | type:1     |
    And I press "form.who_see_who.children.submit.label"
    Then I should be on this page "/admin/fr/event/1/who-see-who/see-what/6"
    And I should see "Who.see_who_but_dont_see"
    And I should see "form.rule_see_what.children.seeWhat.label"
    Then I select "participant_position" from "form.rule_see_what.children.seeWhat.label"
    And I additionally select "participant_firstname" from "form.rule_see_what.children.seeWhat.label"
    And I press "form.rule_see_what.children.submit.label"
    When I should see "flash.admin.event.who_see_what.success"
