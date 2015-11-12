Feature: Update a sheet block
  I need to be able to choose a package

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml        |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Nomenclature.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Type.yml         |
      | User.yml                                                      |
      | Sheet.yml                                                     |
      | Participant.yml                                               |

  Scenario: I can set my company informations
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/update_block/563cae566af03"
    And the response status code should be 200
    And I fill in the following:
      | Nom                                         | CompanySAS             |
      | Adresse                                     | 1 rue de Clery, Paris  |
      | Site web                                    | http://www.site.web    |
      | Descriptif                                  | Lorem ipsum            |
      | Pays                                        | FR                     |
      # Activité
      | update_sheet_block_data_563cb1036EB88_value | 2                      |
      # Taille
      | update_sheet_block_data_5641f581998c1_value | 27                     |
      # Chiffre d'affaire
      | update_sheet_block_data_5641f59737211_value | 28                     |
      # Type de structure
      | update_sheet_block_data_5641f59e537b9_value | 34                     |
    And I press "form.update_sheet_block.children.submit.label"
    Then the response status code should be 200
    And I should see "Pays"
    And I should see "France"
