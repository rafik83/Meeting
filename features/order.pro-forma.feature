Feature: Pro-forma
  I need to be able get a pro-forma

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml     |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml        |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Nomenclature.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Type.yml         |
      | User.yml                                                      |
      | OneSheetOneParticipantWithBillingDataForProForma.yml          |

  Scenario: I can see a valid pro-forma
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I fill in "form.login.children.username.label" with "test-3@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    And the response status code should be 200
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/orders"
    And the response status code should be 200
    And I should see "event.sheet.listOrders.proForma"
    Then I follow "event.sheet.listOrders.proForma"
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/pro_forma/1"
    And the response status code should be 200
