import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { LoginForm } from './LoginForm';

test('logs in and shows the user email on success', async () => {
  const qc = new QueryClient({
    defaultOptions: { queries: { retry: false }, mutations: { retry: false } },
  });
  render(
    <QueryClientProvider client={qc}>
      <LoginForm />
    </QueryClientProvider>,
  );

  await userEvent.type(screen.getByLabelText(/email/i), 'test@example.com');
  await userEvent.type(screen.getByLabelText(/password/i), 'secret-pass');
  await userEvent.click(screen.getByRole('button', { name: /sign in/i }));

  expect(await screen.findByText('test@example.com')).toBeInTheDocument();
});
