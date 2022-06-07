require("dotenv").config();
import "chromedriver";
import webdriver, { until, By, WebElement } from "selenium-webdriver";

var searchBox: WebElement;

export const waitForPageLoad = async (driver: webdriver.ThenableWebDriver) => {
  searchBox = await driver.wait(until.elementLocated(By.css(".gLFyf.gsfi")), 5 * 1000);
};
export const enterSearchTerm = async (searchTerm: string) => {
  await searchBox.sendKeys(searchTerm);
};
