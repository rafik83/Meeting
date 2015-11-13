Feature: Choose a package
  I need to be able to choose a package

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml    |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Type.yml     |
      | User.yml                                                  |
      | Sheet.yml                                                 |
      | OneSheetOneParticipant.yml                                |

  Scenario: I can choose the Silver package
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then the response status code should be 200
    And I follow "event.link.see_my_sheet"
    Then the response status code should be 200
    And I follow "event.sheet.package.step.next"
    Then the response status code should be 200
    And I should see "Forfait de participation"
    When I check the "Forfait Silver" radio
    And I press "form.update_sheet_package_step.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.package.update_step.success"

  Scenario: I can complete my package
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    And the response status code should be 200
    Then I follow "event.link.see_my_sheet"
    And the response status code should be 200
    Then I follow "event.sheet.package.step.next"
    And the response status code should be 200
    And I should see "Forfait de participation"
    When I check the "Forfait Silver" radio
    And I press "form.update_sheet_package_step.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.package.update_step.success"
    And I check "Ajouter des participants"
    And I select the quantity "2" for the checkbox "Ajouter des participants"
    And I press "form.update_sheet_package_step.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.package.update_step.success"
    And I check the "Je ne prends pas" radio
    And I check the "Formule 3" radio
    And I check "Option payant avec sélection de quantité"
    And I select the quantity "2" for the checkbox "Option payant avec sélection de quantité"
    And I press "form.update_sheet_package_step.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.package.update_step.success"
    And I check "traduction de votre fiche de présentation"
    And I check "Insertion de votre brochure dans les pochettes d’accueil"
    And I select the quantity "2" for the checkbox "traduction de votre fiche de présentation"
    And I check "Wifi sur place"
    And I press "form.update_sheet_package_step.children.submit.label"
    Then I should see "flash.package.final_step.success"
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/billing"
    And the response status code should be 200
