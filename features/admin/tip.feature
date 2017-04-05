@admin @tip

  Feature: Tip in admin
    I can manage tip on admin

  Scenario: I can see the tip menu on admin navbar
    Given the database is purged
    And the following fixtures files are loaded:
      | Admin.yml |
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml |
    And I am logged with "test@test.com" on admin
    When I go to this page "/admin/fr/event"
    Then I should see "admin.tip.link"

  Scenario: I can see the tip list on admin
    Given I am logged with "test@test.com" on admin
    And I go to this page "/admin/fr/tip/list"
    Then the response status code should be 200
    And the response should contain "admin.tip.create.link"
    And the response should contain "title"
    And the response should contain "Français"

  Scenario: I can create a new tip
    Given I am logged with "test@test.com" on admin
    And I go to this page "/admin/fr/tip/create"
    Then the response status code should be 200
    And I should see "form.tip_create.children.title.label"
    And I should see "form.tip_create.children.onCatalog.label"
    And I should see "form.tip_create.children.translations.prototype.children.title.label"
    And I should see "form.tip_create.children.translations.prototype.children.locale.label"
    And I should see "form.tip_create.children.translations.prototype.children.content.label"
    And I should see "form.tip_create.children.translations.label_delete"
    And I should see "form.tip_create.children.translations.prototype.label"
    And I should see "form.tip_create.children.translations.label_add"
    And I should see "form.tip_create.children.submit.label"
    When I fill in the following:
      | tip_create_title | title |
      | tip_create_translations_fr_title | fr |
      | tip_create_translations_fr_content | content |
      | tip_create_translations_en_title | en |
      | tip_create_translations_en_content | content |
    And I check "tip_create_onCatalog"
    And I press "tip_create_submit"
    Then the response status code should be 200
    And I should see "flash.admin.tip.create.success"

  Scenario: I can update an existent tip
    Given I am logged with "test@test.com" on admin
    And I go to this page "/admin/fr/tip/1/update"
    Then the response status code should be 200
    And I should see "form.tip_update.children.title.label"
    And I should see "form.tip_update.children.onCatalog.label"
    And I should see "form.tip_update.children.translations.prototype.children.title.label"
    And I should see "form.tip_update.children.translations.prototype.children.locale.label"
    And I should see "form.tip_update.children.translations.prototype.children.content.label"
    And I should see "form.tip_update.children.translations.label_delete"
    And I should see "form.tip_update.children.translations.prototype.label"
    And I should see "form.tip_update.children.translations.label"
    When I fill in the following:
      | tip_update_title | title |
      | tip_update_translations_fr_title | fr |
      | tip_update_translations_fr_content | content |
      | tip_update_translations_en_title | en |
      | tip_update_translations_en_content | content |
    And I check "tip_update_onCatalog"
    And I press "tip_update_submit"
    Then the response status code should be 200
    And I should see "flash.admin.tip.update.success"
