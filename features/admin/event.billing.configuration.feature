@admin

Feature: Edit event billing configuration
  As an Admin, I need to be able to edit event billing configuration

  Scenario: go to edit billing configuration page
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    Then I am on this page "/admin/fr/event"
    And I go to this page "/admin/fr/event/1/billing/configuration"
    Then the response status code should be 200
    And I should see "event.billing.configuration.title"

  Scenario: I can update event billing configuration
    Given I am logged with "test@test.com" on admin
    And I go to this page "/admin/fr/event/1/billing/configuration"
    And I fill in the following:
      | form.event_billing_configuration.children.legalInfo.label                                        | Informations légales   |
      | form.event_billing_configuration.children.translations.prototype.children.bankInfo.label         | Infos banques          |
      | form.event_billing_configuration.children.translations.prototype.children.billingAddress.label   | 42 rue du nuage        |
      | form.event_billing_configuration.children.translations.prototype.children.paymentCondition.label | Conditions de paiement |
      | form.event_billing_configuration.children.translations.prototype.children.paymentFooter.label    | Pied de page           |
    And I attach the file "dummy-image-test.jpg" to "event_billing_configuration[invoiceLogo]"
    And I press "form.event_billing_configuration.children.submit.label"
    Then the response status code should be 200
    