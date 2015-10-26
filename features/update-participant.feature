Feature: Update self participant sheet
  I need to be able to update my participant informations

  Background: Re-init the database and load the fixtures
    Given the database is initialized
    And the fixtures "Participant.yml" are loaded

  Scenario: I can go to the participant sheet and update it
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then I follow "event.link.see_my_sheet"
    Then I follow "event.sheet.block.update_participant.title.edit"
    And I fill in the following:
      |Nom       |Jean        |
      |Prénom    |Dupond      |
      |Téléphone |0611111111  |
      |Fonction  |Developpeur |
    And I press "form.participant_update.children.submit.label"
    Then I should see "flash.sheet.update_participant.success"
