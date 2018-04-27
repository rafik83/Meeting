@event
@package
@order
@product
Feature: Edit my package
  I need to be able to edit my package after a first order

  Scenario: I can edit my package
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml      |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml          |
      | Package/User.yml                                                         |
      | Package/Product.yml                                                      |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml             |
      | Package/Order.yml                                                        |
    And I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I am on this page "/fr"
    And I go to this page "/fr/sheet"
    And I follow "navigation.links.package.order_summary_total"
    Then I should be on this page "/fr/sheet/1/order/summary"
    And I should see "package.summary.title"
    When I follow "package.summary.edit"
    Then I should be on this page "/fr/sheet/1/package/step/1"
    And I should see "Participants & plannings"
    And I should see "Packs de rendez-vous"
    And I should see "package.participant.add"
    And I follow "package.participant.add"
    And I should see "sheet.participant.sendInvite"

  Scenario: I can remove two participant from my package (one included and one payed)
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I am on this page "/fr"
    And I go to this page "/fr/sheet/1/package/step/1"
    Then I should see "package.product.unitPrice"
    When I follow "package.participant.delete"
    Then I should be on this page "/fr/sheet/1/package/step/1/participant/remove"
    And I check "remove_participant_participants_5"
    And I check "remove_participant_participants_4"
    When I press "sheet.participant.remove"
    Then I should be on this page "/fr/sheet/1/package/step/1"
    And I should not see "package.participant.delete"

  Scenario: I can remove one planning
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I am on this page "/fr"
    Then I go to this page "/fr/sheet/1/package/step/1"
    And I should not see "package.participant.delete"
    And the "participant_and_planning[planningQuantity][quantity]" field should contain "1"
    # participant product for the participant id=1
    When I fill in "participant_and_planning[1]" with "3"
    # planning
    And I fill in "participant_and_planning[planningQuantity][quantity]" with "0"
    And I press "package.participant_planning.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"

  Scenario: I can edit my package option. Remove 2 options chaise and add 1 option A
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I am on this page "/fr"
    Then I go to this page "/fr/sheet/1/package/step/2"
    And the "options[5][quantity]" field should contain "1"
    And the "options[6][quantity]" field should contain "2"
    And the "options[7][quantity]" field should contain "1"
    And the "options[11][quantity]" field should contain "4"
    When I fill in "options[6][quantity]" with "0"
    And I fill in "options[7][quantity]" with "2"
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/package/summary"

  Scenario: I can see my updated package summary
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I am on this page "/fr"
    When I go to this page "/fr/sheet/1/package/summary"
    Then I should see "package.summary.title"
    # planning supplementaire
    And the "tr[data-product-id='4']" element should contain "-1"
    # option chaise
    And the "tr[data-product-id='6']" element should contain "-2"
    # option A
    And the "tr[data-product-id='7']" element should contain "1"

  Scenario: I can't remove a product that is not deletable or buyable
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I am on this page "/fr"
    Then I go to this page "/fr/sheet/1/package/step/2"
    And I should see "package.product.unavailable"
    When I fill in "options[11][quantity]" with "2"
    Then I press "package.product.validate"
    And I should be on this page "/fr/sheet/1/package/step/2"
    And I should see "package.product.productNotDeletable"

  Scenario: I can't use a promotion code for a negative product quantity
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I am on this page "/fr"
    Then I go to this page "/fr/sheet/1/package/summary"
    And I fill in "package_summary_promotion_code_promotionCode" with "ASDDAYS10"
    And I press "package.summary.promotion.button.label"
    Then I should be on this page "/fr/sheet/1/package/summary#summary-promo-code-row"
    And I should see "flash.package.promotionCode.error.negativeRow"

  Scenario: I can pay my updated package
    Given I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I am on this page "/fr"
    Then I go to this page "/fr/sheet/1/package/summary"
    And I check "form.package_summary_terms_of_sale.children.termsOfSale.label"
    When I press "package.summary.pay"
    Then I should be on this page "/fr/sheet/1/orders"
