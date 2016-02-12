Feature: Pro-forma
  I need to be able get a pro-forma

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | Template.yml                                         |
      | app/Event.yml                                        |
      | app/Nomenclature.yml                                 |
      | app/Type.yml                                         |
      | User.yml                                             |
      | OneSheetOneParticipantWithBillingDataForProForma.yml |
    Given I am logged with "test-3@test.com" and "p@ssw0rd" on event "http://rdv-carnot-2016.vimeet.proximum.dev"

  Scenario: I can see a valid pro-forma
    When I go to this page "/fr/sheet/1/orders"
    Then I should see "event.sheet.listOrders.proForma"
    Then I follow "event.sheet.listOrders.proForma"
    And I should be on this page "/fr/sheet/1/pro_forma/1"
