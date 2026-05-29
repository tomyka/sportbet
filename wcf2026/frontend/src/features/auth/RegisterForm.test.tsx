import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { RegisterForm } from './RegisterForm';

function wrapper() {
  const qc = new QueryClient({ defaultOptions: { queries: { retry: false }, mutations: { retry: false } } });
  return ({ children }: { children: React.ReactNode }) => (
    <QueryClientProvider client={qc}>{children}</QueryClientProvider>
  );
}

test('shows success message after registration', async () => {
  render(<RegisterForm onSuccess={vi.fn()} />, { wrapper: wrapper() });

  await userEvent.type(screen.getByLabelText(/name/i), 'Alice');
  await userEvent.type(screen.getByLabelText(/email/i), 'alice@example.com');
  await userEvent.type(screen.getByLabelText(/^password$/i), 'secret-pass-123');
  await userEvent.type(screen.getByLabelText(/confirm password/i), 'secret-pass-123');
  await userEvent.click(screen.getByRole('button', { name: /create account/i }));

  expect(await screen.findByText(/check your email/i)).toBeInTheDocument();
});

test('shows error when registration fails', async () => {
  const { server } = await import('../../test/server');
  const { http, HttpResponse } = await import('msw');
  server.use(
    http.post('*/api/v1/auth/register', () =>
      HttpResponse.json({ errors: { email: ['Already taken.'] } }, { status: 422 })
    ),
  );

  render(<RegisterForm onSuccess={vi.fn()} />, { wrapper: wrapper() });

  await userEvent.type(screen.getByLabelText(/name/i), 'Bob');
  await userEvent.type(screen.getByLabelText(/email/i), 'existing@example.com');
  await userEvent.type(screen.getByLabelText(/^password$/i), 'secret-pass-123');
  await userEvent.type(screen.getByLabelText(/confirm password/i), 'secret-pass-123');
  await userEvent.click(screen.getByRole('button', { name: /create account/i }));

  expect(await screen.findByRole('alert')).toBeInTheDocument();
});
