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
      return HttpResponse.json({ data: { id: 1, email: body.email, name: 'Test' } });
    }
    return HttpResponse.json({ message: 'Invalid' }, { status: 422 });
  }),
];

export const server = setupServer(...handlers);
