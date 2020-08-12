@managing_payment_method_dalenys
Feature: Adding a new payment method
    In order to pay for orders in different ways
    As an Administrator
    I want to add a new payment method to the registry

    Background:
        Given the store operates on a single channel in "United States"
        And adding a new channel in "France"
        And I am logged in as an administrator

    @ui
    Scenario: Adding a new Dalenys payment method with result successfully
        Given I want to create a new payment method with Dalenys gateway factory
        When I name it "Dalenys" in "English (United States)"
        And I specify its code as "DALENYS"
        And make it available in channel "France"
        And I configure it with test Dalenys credentials
        And I add it
        Then I should be notified that it has been successfully created
        And the payment method "Dalenys" should appear in the registry
        And the payment method "Dalenys" should be available in channel "France"
