import { setupServer } from 'msw/node';
import { http, HttpResponse } from 'msw';

export const handlers = [
  http.get('*/api/v1/health', () =>
    HttpResponse.json({ status: 'ok', checks: { database: 'ok' } }),
  ),
  http.get('*/sanctum/csrf-cookie', () => new HttpResponse(null, { status: 204 })),
  http.post('*/api/v1/auth/login', async ({ request }) => {
    const body = (await request.json()) as { email: string; password: string };
    if (body.email === 'test@example.com' && body.password === 'secret-pass') {
      return HttpResponse.json({
        data: { id: 1, email: body.email, name: 'Test', display_name: null,
                time_zone: 'UTC', locale: 'en', email_verified: true, is_global_admin: false },
      });
    }
    return HttpResponse.json({ message: 'Invalid' }, { status: 422 });
  }),
  http.get('*/api/v1/auth/me', () =>
    HttpResponse.json(null, { status: 401 })
  ),
  http.post('*/api/v1/auth/register', async ({ request }) => {
    const body = (await request.json()) as { name: string; email: string };
    return HttpResponse.json({
      data: { id: 2, name: body.name, email: body.email, display_name: null,
              time_zone: 'UTC', locale: 'en', email_verified: false, is_global_admin: false },
    }, { status: 201 });
  }),
  http.post('*/api/v1/auth/logout', () => new HttpResponse(null, { status: 204 })),
  http.post('*/api/v1/auth/password/forgot', () => new HttpResponse(null, { status: 204 })),
  http.post('*/api/v1/auth/password/reset', () => new HttpResponse(null, { status: 204 })),
  http.patch('*/api/v1/auth/me', async ({ request }) => {
    const body = (await request.json()) as Record<string, unknown>;
    return HttpResponse.json({
      data: { id: 1, name: body.name ?? 'Test', email: 'test@example.com',
              display_name: body.display_name ?? null, time_zone: body.time_zone ?? 'UTC',
              locale: body.locale ?? 'en', email_verified: true, is_global_admin: false },
    });
  }),
  http.post('*/api/v1/auth/password', () => new HttpResponse(null, { status: 204 })),
  http.post('*/api/v1/auth/email/resend', () => new HttpResponse(null, { status: 204 })),
  // ─── Tournament handlers ────────────────────────────────────────────────────
  http.get('*/api/v1/tournaments', () =>
    HttpResponse.json({
      data: [
        {
          id: 1, name: 'World Cup 2026', slug: 'wc2026', sport: 'football',
          status: 'open', starts_at: null, ends_at: null, created_at: '2025-01-01T00:00:00Z',
        },
      ],
      meta: { current_page: 1, last_page: 1, total: 1 },
    })
  ),
  http.get('*/api/v1/tournaments/wc2026', () =>
    HttpResponse.json({
      data: {
        id: 1, name: 'World Cup 2026', slug: 'wc2026', sport: 'football',
        status: 'open', starts_at: null, ends_at: null, created_at: '2025-01-01T00:00:00Z',
      },
    })
  ),
  http.get('*/api/v1/tournaments/not-found', () => new HttpResponse(null, { status: 404 })),
  http.get('*/api/v1/tournaments/not-found/stages', () => new HttpResponse(null, { status: 404 })),
  http.get('*/api/v1/tournaments/not-found/fixtures', () => new HttpResponse(null, { status: 404 })),
  http.get('*/api/v1/tournaments/wc2026/stages', () =>
    HttpResponse.json({
      data: [
        { id: 1, name: 'Group Stage', ord: 0, type: 'group_stage', config: null, starts_at: null, locks_at: null },
      ],
    })
  ),
  // ─── Admin Tournament handlers ──────────────────────────────────────────────
  http.post('*/api/v1/admin/tournaments', async ({ request }) => {
    const body = (await request.json()) as Record<string, unknown>;
    return HttpResponse.json({
      data: {
        id: 10,
        name: body.name,
        slug: body.slug,
        sport: body.sport,
        status: 'draft',
        starts_at: null,
        ends_at: null,
        created_at: '2025-01-01T00:00:00Z',
      },
    }, { status: 201 });
  }),
  http.patch('*/api/v1/admin/tournaments/:slug', async ({ request }) => {
    const body = (await request.json()) as Record<string, unknown>;
    return HttpResponse.json({
      data: {
        id: 1,
        name: body.name ?? 'World Cup 2026',
        slug: 'wc2026',
        sport: body.sport ?? 'football',
        status: body.status ?? 'open',
        starts_at: null,
        ends_at: null,
        created_at: '2025-01-01T00:00:00Z',
      },
    });
  }),
  http.delete('*/api/v1/admin/tournaments/:slug', () =>
    new HttpResponse(null, { status: 204 })
  ),
  // ─── Fixture + Prediction handlers ─────────────────────────────────────────
  http.get('*/api/v1/tournaments/wc2026/fixtures', () =>
    HttpResponse.json({
      data: [
        {
          id: 101, round_id: 1,
          kickoff_at: new Date(Date.now() + 86400000).toISOString(),
          home_team_id: 1, away_team_id: 2,
          status: 'scheduled',
          home_score: null, away_score: null, winner_team_id: null,
        },
        {
          id: 102, round_id: 1,
          kickoff_at: new Date(Date.now() - 86400000).toISOString(),
          home_team_id: 3, away_team_id: 4,
          status: 'finished',
          home_score: 2, away_score: 1, winner_team_id: 3,
        },
      ],
    })
  ),
  http.get('*/api/v1/tournaments/wc2026/predictions/score', () =>
    HttpResponse.json({
      data: [
        {
          id: 1, fixture_id: 101, tournament_id: 1,
          home_score: 2, away_score: 0,
          predicted_winner_team_id: null,
          submitted_at: '2026-06-01T10:00:00Z',
        },
      ],
    })
  ),
  http.put('*/api/v1/tournaments/wc2026/fixtures/101/prediction', async ({ request }) => {
    const body = (await request.json()) as Record<string, unknown>;
    return HttpResponse.json({
      data: {
        id: 1, fixture_id: 101, tournament_id: 1,
        home_score: body.home_score as number,
        away_score: body.away_score as number,
        predicted_winner_team_id: (body.predicted_winner_team_id as number | null) ?? null,
        submitted_at: new Date().toISOString(),
      },
    });
  }),
  http.put('*/api/v1/tournaments/wc2026/fixtures/102/prediction', () =>
    new HttpResponse(null, { status: 423 })
  ),
];

export const server = setupServer(...handlers);
