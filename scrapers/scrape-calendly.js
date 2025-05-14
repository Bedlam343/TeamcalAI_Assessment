const { chromium } = require('playwright');
const fs = require('fs');

const scrapeCalendly = async () => {
  const calendlyUrl = process.argv[2];
  console.log(calendlyUrl);
};

scrapeCalendly();
