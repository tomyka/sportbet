import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { LoginForm } from './LoginForm';

test('submits login form without error', async () => {
  const qc = new QueryClient({
    defaultOptions: { queries: { retry: false }, mutations: { retry: false } },
  });
  const onSuccess = vi.fn();
  render(
    <QueryClientProvider client={qc}>
      <LoginForm onSuccess={onSuccess} />
    </QueryClientProvider>,
  );

  await userEvent.type(screen.getByLabelText(/email/i), 'test@example.com');
  await userEvent.type(screen.getByLabelText(/password/i), 'secret-pass');
  await userEvent.click(screen.getByRole('button', { name: /sign in/i }));

  // Wait for mutation to complete
  await screen.findByRole('button', { name: /sign in/i });
  expect(onSuccess).toHaveBeenCalledOnce();
  expect(screen.queryByRole('alert')).not.toBeInTheDocument();
});
