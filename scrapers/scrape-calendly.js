const { chromium } = require('playwright');
const fs = require('fs');

const MONTH_INDEX = {
  January: 0,
  February: 1,
  March: 2,
  April: 3,
  May: 4,
  June: 5,
  July: 6,
  August: 7,
  September: 8,
  October: 9,
  November: 10,
  December: 11,
};

const scrapeCalendly = async () => {
  const calendlyUrl = process.argv[2];

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  const startDate = getNextMonday();
  const startDateStr = getFormattedDate(startDate);

  const endDate = new Date();
  endDate.setDate(endDate.getDate() + 28);
  const endDateStr = getFormattedDate(endDate);

  try {
    await page.goto(calendlyUrl, { waitUntil: 'networkidle' });

    const slots = {};

    while (true) {
      await page.waitForSelector('[data-testid="calendar-header"]');

      const tableHeader = page.getByTestId('calendar-header');
      const headerTitle = await tableHeader
        .locator('div[data-testid="title"]')
        .textContent();

      const [calendarMonth, year] = headerTitle.split(' ');

      if (slots[year]) {
        slots[year][calendarMonth] = [];
      } else {
        slots[year] = {};
        slots[year][calendarMonth] = [];
      }

      const tbody = page.getByTestId('calendar-table');
      const rows = tbody.locator('tr');
      const rowCount = await rows.count();

      for (let i = 0; i < rowCount; ++i) {
        const row = rows.nth(i);
        const tds = row.locator('td');
        const tdCount = await tds.count();

        for (let j = 0; j < tdCount; ++j) {
          const td = tds.nth(j);
          const day = await td.textContent();

          const btn = td.locator('button');
          const ariaLabel = await btn.getAttribute('aria-label');
          const [, divMonth] = ariaLabel.split(' - ')[0].split(' ');

          const currentDate = new Date(
            Number(year),
            MONTH_INDEX[calendarMonth],
            Number(day),
          );
          const currentDateStr = getFormattedDate(currentDate);

          const isDisabled = await btn.isDisabled();

          if (divMonth !== calendarMonth) {
            continue;
          }

          if (currentDateStr < startDateStr) {
            continue;
          }

          if (!isDisabled) {
            await btn.click();
            const spotList = page.locator('div[data-component="spot-list"]');
            const spotDivs = spotList.locator('div[role="listitem"]');
            const numSpots = await spotDivs.count();

            const timeSlots = [];

            for (let k = 0; k < numSpots; ++k) {
              const spot = spotDivs.nth(k);
              const time = await spot.textContent();
              timeSlots.push(time);
            }

            slots[year][calendarMonth].push({ monthDay: day, timeSlots });
          } else {
            slots[year][calendarMonth].push({ monthDay: day });
          }

          if (currentDateStr === endDateStr) {
            console.log(JSON.stringify(slots));
            await browser.close();
            return;
          }
        }
      }

      // go to next month
      const nextBtn = tableHeader.locator('button').nth(1);
      await nextBtn.click();

      const newUrl = page.url();
      // Now force navigation to that updated URL
      await page.goto(newUrl, { waitUntil: 'networkidle' });
    }
  } catch (err) {
    console.error(err);
  } finally {
    await browser.close();
  }
};

const getNextMonday = () => {
  const today = new Date();

  // Find next Monday
  const dayOfWeek = today.getDay(); // 0 (Sun) to 6 (Sat)
  const daysUntilNextMonday = (8 - dayOfWeek) % 7;
  const nextMonday = new Date(today);
  nextMonday.setDate(today.getDate() + daysUntilNextMonday);
  return nextMonday;
};

const getFormattedDate = (date) => {
  let month = date.getMonth().toString();
  if (month.length === 1) {
    month = '0' + month;
  }

  let monthDate = date.getDate().toString();
  if (monthDate.length === 1) {
    monthDate = '0' + monthDate;
  }
  return `${date.getFullYear()}-${month}-${monthDate}`;
};

scrapeCalendly();
