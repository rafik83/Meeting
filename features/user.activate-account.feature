Feature: Activate Account
  I need to be able to change my password if I forgot it

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml    |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Type.yml     |
      | UserWithActivateAccountTokenAndSheet.yml                  |

  Scenario: I can activate my account
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/activate/azertyuiopqsdfghjklmwxcvbn"
    And the response status code should be 200
    Then I fill in the following:
      | form.activate_account_password.children.password.children.first.label  | tructruc |
      | form.activate_account_password.children.password.children.second.label | tructruc |
    And I press "form.activate_account_password.children.submit.label"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/update_participant/2"
    And the response status code should be 200
    And I should see "login.logged_as"
    And I should see "flash.activate_account.success"

  Scenario: I can buy a participant and activate the account
    Given I am logged with "test@test.com" and "p@ssw0rd" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/sheet/1"
    And the response status code should be 200
    Then I follow "event.sheet.package.step.next"
    And the response status code should be 200
    And I check the "Forfait Silver" radio
    Then I press "form.update_step.children.submit.label"
    And the response status code should be 200
    And I check "Ajouter des participants"
    And I select the quantity 2 for the checkbox "Ajouter des participants"
    Then I press "form.update_step.children.submit.label"
    And the response status code should be 200
    Then I go to this page "/fr/sheet/1"
    And the response status code should be 200
    And I follow "event.sheet.block.add_participant.title"
    Then I should be on "/fr/sheet/1/add_participant"
    And I fill in the following:
    | form.add_participant.children.email.label | test_activate@test.fr |
    | Nom                                       | Nouveau               |
    | Prénom                                    | Participant           |
    | Téléphone                                 | 0101010202            |
    | Fonction                                  | position4             |
    Then I press "form.add_participant.children.submit.label"
    And the "activate_account" mail should be sent to "test_activate@test.fr"
    And the "activate_account" mail should contain the link "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/activate/"
    Then I follow the "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/activate/" link in the "activate_account" mail
    And the response status code should be 200
    And I should see "greetings"
    And I should see "login.link"

