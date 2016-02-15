Feature: Update a sheet block
  I need to be able to choose a package

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml                |
      | app/Event.yml                   |
      | app/Type.yml                    |
      | User.yml                        |
      | Sheet.yml                       |
      | OneSheetSeveralParticipants.yml |

  Scenario: I can set my company informations
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/update_block/563cae566af03"
    And the response status code should be 200
    And I fill in the following:
      | Nom               | CompanySAS             |
      | Adresse           | 1 rue de Clery, Paris  |
      | Site web          | http://www.site.web    |
      | Descriptif        | Lorem ipsum            |
      | Pays              | FR                     |
      | Activité          | activity2              |
      | Taille            | size3                  |
      | Chiffre d'affaire | turnover2              |
      | Statut juridique  | status1                |
    And I press "form.update_block.children.submit.label"
    Then the response status code should be 200
    And I should see "Pays"
    And I should see "France"
