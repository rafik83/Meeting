@admin @tip

  Feature: Tip in admin
    I can manage tip on admin

  Scenario: I can see the tip menu on admin navbar
    Given the database is purged
    And the tip "Awesome tip" is created for the event "Best of Web"
    And I am logged as admin
    When I go to this page "/fr/event"
    Then I should see "admin.tip.link"

  Scenario: I can not see the tip on global but on the tip list of the event
    Given I am logged as admin
    When I go to this page "/fr/tip/list"
    Then I should see "admin.tip.list.title"
    And I should not see "Awesome tip"
    Then I go to this page "/fr/event/1/tip/list"
    And I should see "Awesome tip"

  Scenario: I can create a new tip
    Given I am logged as admin
    And I go to this page "/fr/tip/create"
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
    Given I am logged as admin
    And I go to this page "/fr/tip/2/update"
    And I should see "form.tip_update.children.title.label"
    And I should see "form.tip_update.children.onCatalog.label"
    And I should see "form.tip_update.children.translations.prototype.children.title.label"
    And I should see "form.tip_update.children.translations.prototype.children.locale.label"
    And I should see "form.tip_update.children.translations.prototype.children.content.label"
    And I should see "form.tip_update.children.translations.label_delete"
    And I should see "form.tip_update.children.translations.prototype.label"
    And I should see "form.tip_update.children.translations.label"
    When I fill in the following:
      | tip_update_title                   | tip_updated_title |
      | tip_update_translations_fr_title   | fr |
      | tip_update_translations_fr_content | tip_updated_content |
    And I check "tip_update_onCatalog"
    And I press "tip_update_submit"
    Then the response status code should be 200
    And I should see "flash.admin.tip.update.success"
    And I should see "tip_updated_title"
