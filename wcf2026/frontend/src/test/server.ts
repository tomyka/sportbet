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
];

export const server = setupServer(...handlers);
