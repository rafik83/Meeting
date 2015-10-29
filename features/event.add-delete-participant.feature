Feature: Add and delete participant
  I need to be able to add and delete participant on a sheet of an event

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
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And I follow "event.link.see_my_sheet"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1"
    And I should see "Exposant"
    And I should see "form.delete_participant.children.submit.label"
    Then I press "form.delete_participant.children.submit.label"
    And I should see "flash.sheet.delete_participant.success"

  Scenario: I can not delete a participant as a guest
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "form.login.children.username.label" with "test-2@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And I follow "event.link.see_my_sheet"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1"
    And I should see "Exposant"
    And I should not see "form.delete_participant.children.submit.label"

  Scenario: I can add a participant
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "form.login.children.username.label" with "test-2@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And I follow "event.link.see_my_sheet"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1"
    And I should see "Exposant"
    And I should see "event.sheet.block.add_participant.title"
    Then I follow "event.sheet.block.add_participant.title"
    And I fill in the following:
    |form.add_participant.children.email.label |test-3@test.fr |
    |Nom                                       |Le Tester      |
    |Prénom                                    |Test           |
    |Téléphone                                 |0101010101     |
    |Fonction                                  |Operator       |
    Then I press "form.add_participant.children.submit.label"
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1"
    And I should see "Le Tester"
    And I should see "flash.sheet.add_participant.success"

  Scenario: I can not add a participant without all the information
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "form.login.children.username.label" with "test-2@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And I follow "event.link.see_my_sheet"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1"
    And I should see "Exposant"
    And I should see "event.sheet.block.add_participant.title"
    Then I follow "event.sheet.block.add_participant.title"
    And I fill in the following:
      |form.add_participant.children.email.label |test-4@test.fr |
      |Nom                                       |Blablabla      |
      |Prénom                                    |               |
      |Téléphone                                 |0202020202     |
      |Fonction                                  |Decorator      |
    Then I press "form.add_participant.children.submit.label"
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/add_participant"
    And I should see "validators.field.required"

