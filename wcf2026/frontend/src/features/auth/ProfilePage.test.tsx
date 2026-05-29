import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { server } from '../../test/server';
import { http, HttpResponse } from 'msw';
import { ProfilePage } from './ProfilePage';

const qc = () =>
  new QueryClient({ defaultOptions: { queries: { retry: false }, mutations: { retry: false } } });
const wrap = (children: React.ReactNode) => (
  <QueryClientProvider client={qc()}>{children}</QueryClientProvider>
);

beforeEach(() => {
  server.use(
    http.get('*/api/v1/auth/me', () =>
      HttpResponse.json({
        data: { id: 1, name: 'Test User', email: 'test@example.com',
                display_name: null, time_zone: 'UTC', locale: 'en',
                email_verified: true, is_global_admin: false },
      })
    ),
  );
});

test('displays user profile', async () => {
  render(wrap(<ProfilePage />));
  expect(await screen.findByDisplayValue('Test User')).toBeInTheDocument();
});

test('updates profile on submit', async () => {
  render(wrap(<ProfilePage />));
  const nameInput = await screen.findByDisplayValue('Test User');
  await userEvent.clear(nameInput);
  await userEvent.type(nameInput, 'Updated Name');
  await userEvent.click(screen.getByRole('button', { name: /save/i }));
  expect(await screen.findByText(/saved/i)).toBeInTheDocument();
});
