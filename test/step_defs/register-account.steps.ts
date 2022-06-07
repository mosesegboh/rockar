require("dotenv").config();
import { defineFeature, loadFeature } from "jest-cucumber";
import { waitForPageLoad, enterSearchTerm } from "../page_objects/homepage";
import "chromedriver";
import webdriver, { until, By, WebElement } from "selenium-webdriver";

var driver = new webdriver.Builder().forBrowser("chrome").build();
const dealersFeature = loadFeature("../features/register-account.feature", {
  loadRelativePath: true
});

defineFeature(dealersFeature, test => {
  beforeEach(async () => {
    await driver.get("http://www.google.com");
  });
  afterEach(() => {
    driver.quit();
  });

  test("User can register account", ({ given, when, then }) => {
    given("I have an option to register a new account", async () => {
	  await waitForPageLoad(driver);
	  await enterSearchTerm("Peppermint");
    });

    when("I register a new account in GCDM", async () => {});

    then("my Rockar user account should be created", async () => {});

    then("I should be logged into my Rockar account", async () => {});
  });
});

