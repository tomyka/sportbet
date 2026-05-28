import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ResetPasswordForm } from './ResetPasswordForm';

const qc = () =>
  new QueryClient({ defaultOptions: { queries: { retry: false }, mutations: { retry: false } } });
const wrap = (children: React.ReactNode) => (
  <QueryClientProvider client={qc()}>{children}</QueryClientProvider>
);

test('shows success after password reset', async () => {
  render(wrap(<ResetPasswordForm token="test-token" email="test@example.com" onSuccess={vi.fn()} />));
  await userEvent.type(screen.getByLabelText(/^new password$/i), 'new-secret-pass-123');
  await userEvent.type(screen.getByLabelText(/confirm/i), 'new-secret-pass-123');
  await userEvent.click(screen.getByRole('button', { name: /reset password/i }));
  expect(await screen.findByText(/password has been reset/i)).toBeInTheDocument();
});
