import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ChangePasswordForm } from './ChangePasswordForm';

const qc = () =>
  new QueryClient({ defaultOptions: { queries: { retry: false }, mutations: { retry: false } } });
const wrap = (children: React.ReactNode) => (
  <QueryClientProvider client={qc()}>{children}</QueryClientProvider>
);

test('shows success after password change', async () => {
  render(wrap(<ChangePasswordForm />));
  await userEvent.type(screen.getByLabelText(/current password/i), 'old-pass');
  await userEvent.type(screen.getByLabelText(/^new password$/i), 'new-secret-123');
  await userEvent.type(screen.getByLabelText(/confirm/i), 'new-secret-123');
  await userEvent.click(screen.getByRole('button', { name: /change password/i }));
  expect(await screen.findByText(/password changed/i)).toBeInTheDocument();
});
