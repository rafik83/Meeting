Feature: add rule who see who
  I need to be able to add a rule for categories and types

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml |
      | app/Event.yml    |
      | app/Type.yml     |
      | app/Category.yml |
      | app/Rule.yml     |
      | Admin.yml        |
    Given I am logged with "test@test.com" on admin
    And I go to "/admin/fr/event"

  Scenario: add rule
    When I follow "Qui voit qui"
    Then the response status code should be 200
    And I should be on "/admin/fr/event/1/who-see-who"
    And I fill in the following:
      | who_see_who_seer    | category:2 |
      | who_see_who_seeable | type:1     |
    And I press "form.who_see_who.children.submit.label"
    Then the response status code should be 200
    And I should be on "/admin/fr/event/1/category/2/see/type/1/dont-see"
    And I check "dont_see_what_participant_563caf2f0ddbd"
    And I press "form.dont_see_what.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.event.who_see_what.success"
