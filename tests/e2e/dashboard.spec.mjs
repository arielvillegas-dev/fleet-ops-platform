import { expect, test } from '@playwright/test';

test('schedules a fleet trip from CSV import and updates distance via telemetry batch API', async ({ page }) => {
  const suffix = Date.now().toString().slice(-6);
  const busCode = `BUS-E2E-${suffix}`;
  const routeCode = `R-E2E-${suffix}`;

  await page.goto('/');
  await expect(page.locator('h1')).toContainText('Fleet Ops Platform');

  await page.fill('#vehicle-form input[name="code"]', busCode);
  await page.fill('#vehicle-form input[name="plate"]', `PL-${suffix}`);
  await page.fill('#vehicle-form input[name="capacity"]', '42');
  await page.click('#vehicle-form button[type="submit"]');
  await expect(page.locator('#flash-status')).toContainText('Vehicle created');

  await page.fill(
    '#csv-form textarea[name="csv_content"]',
    `code,name,origin_name,destination_name,planned_distance_km\n${routeCode},Express Line ${suffix},North Hub,South Hub,18.4`
  );
  await page.click('#csv-form button[type="submit"]');
  await expect(page.locator('#flash-status')).toContainText('Imported 1');

  await page.selectOption('#trip-form select[name="vehicle_id"]', { label: `${busCode} (PL-${suffix})` });
  await page.selectOption('#trip-form select[name="route_id"]', { label: `${routeCode} - Express Line ${suffix}` });
  await page.fill('#trip-form input[name="operator_name"]', `Operator ${suffix}`);
  await page.click('#trip-form button[type="submit"]');

  const tripRow = page.locator('#trips-tbody tr', { hasText: `Operator ${suffix}` });
  await expect(tripRow).toBeVisible();
  const tripId = (await tripRow.locator('td').first().textContent()).trim();

  const apiRes = await page.request.post(`/api/v1/trips/${tripId}/telemetry-batch`, {
    headers: { Authorization: 'Bearer demo-fleet-token' },
    data: {
      points: [
        {
          client_event_id: `e2e-${suffix}-1`,
          latitude: 13.6929,
          longitude: -89.2182,
          accuracy_meters: 8.0,
          speed_kmh: 34,
          heading: 90,
          recorded_at: '2026-11-20T11:00:00Z',
        },
        {
          client_event_id: `e2e-${suffix}-2`,
          latitude: 13.7029,
          longitude: -89.2182,
          accuracy_meters: 9.0,
          speed_kmh: 36,
          heading: 90,
          recorded_at: '2026-11-20T11:02:00Z',
        },
      ],
    },
  });
  expect(apiRes.ok()).toBeTruthy();

  await page.reload();
  await expect(tripRow).toContainText('in_progress');
  await expect(tripRow).toContainText('2');
});
