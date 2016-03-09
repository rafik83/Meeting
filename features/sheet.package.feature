Feature: Choose a package
  I need to be able to choose a package

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml           |
      | app/Event.yml              |
      | app/Type.yml               |
      | User.yml                   |
      | Sheet.yml                  |
      | OneSheetOneParticipant.yml |
    Given I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"

  Scenario: I can choose the Silver package
    When I follow "event.link.see_my_sheet"
    Then the response status code should be 200
    And I follow "event.sheet.package.step.next"
    Then the response status code should be 200
    And I should see "Forfait de participation"
    When I check the "Forfait Silver" radio
    And I press "form.update_step.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.package.update_step.success"

  Scenario: I can complete my package
    When I follow "event.link.see_my_sheet"
    And the response status code should be 200
    Then I follow "event.sheet.package.step.next"
    And the response status code should be 200
    And I should see "Forfait de participation"
    When I check the "Forfait Silver" radio
    And I press "form.update_step.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.package.update_step.success"
    And I check "Ajouter des participants"
    And I select the quantity "2" for the checkbox "Ajouter des participants"
    And I press "form.update_step.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.package.update_step.success"
    And I should see "event.sheet.package.include"
    And I check "Logo dans les emails promotionnels de l'événement"
    And I press "form.update_step.children.submit.label"
    Then the response status code should be 200
    And I check "Bannière"
    And I select the quantity "1" for the checkbox "Bannière"
    And I press "form.update_step.children.submit.label"
    And I check "Option payant avec sélection de quantité"
    And I select the quantity "2" for the checkbox "Option payant avec sélection de quantité"
    And I press "form.update_step.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.package.update_step.success"
    And I check "traduction de votre fiche de présentation"
    And I check "Insertion de votre brochure dans les pochettes d’accueil"
    And I select the quantity "2" for the checkbox "traduction de votre fiche de présentation"
    And I check "Wifi sur place"
    And I press "form.update_step.children.submit.label"
    Then I should be on "/fr/sheet/1/cart"
    And the response status code should be 200
    And I should see "flash.package.final_step.success"
    And I should see "event.sheet.cart.totalWithoutTaxes"
    And I should see "30 280,00 €"
    And I follow "event.sheet.cart.label.billingStep"
    And I should be on "/fr/sheet/1/billing"
    And the response status code should be 200
