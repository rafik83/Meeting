Feature: Choose a package
  I need to be able to choose a package

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Type.yml  |
      | User.yml                                               |
      | Sheet.yml                                              |
      | Participant.yml                                        |

  Scenario: I can delete a participant as an owner
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then I follow "event.link.see_my_sheet"
    Then I follow "event.sheet.package.step.next"
    And I should see "Forfait de participation"
    When I check the "Forfait Silver" radio
    And I press "form.update_sheet_package_step.children.submit.label"
    Then I should see "flash.package.update_step.success"
