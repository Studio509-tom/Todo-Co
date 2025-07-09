<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Given I am using Behat
     */
    public function iAmUsingBehat()
    {
        // Just a placeholder
    }

    /**
     * @Then I should see :text
     */
    public function iShouldSee($text)
    {
        if ($text !== "Hello Behat!") {
            throw new Exception("Text not found: $text");
        }
    }
}
