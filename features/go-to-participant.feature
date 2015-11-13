Feature: Test to go to the participant sheet
  I need to be able to register to an event and login to my account

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml     |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml        |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Nomenclature.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Type.yml         |
      | User.yml                                                      |
      | Sheet.yml                                                     |
      | Participant.yml                                               |

  Scenario: I can go to the participant sheet of the user
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And the response status code should be 200
    And I follow "event.link.see_my_sheet"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1"
    And the response status code should be 200
    And I should see "Exposant"
    And I should see "Dutest"
