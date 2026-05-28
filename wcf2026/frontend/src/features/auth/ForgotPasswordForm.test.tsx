import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ForgotPasswordForm } from './ForgotPasswordForm';

const qc = () =>
  new QueryClient({ defaultOptions: { queries: { retry: false }, mutations: { retry: false } } });
const wrap = (children: React.ReactNode) => (
  <QueryClientProvider client={qc()}>{children}</QueryClientProvider>
);

test('shows confirmation after form submit', async () => {
  render(wrap(<ForgotPasswordForm />));
  await userEvent.type(screen.getByLabelText(/email/i), 'test@example.com');
  await userEvent.click(screen.getByRole('button', { name: /send reset/i }));
  expect(await screen.findByText(/check your email/i)).toBeInTheDocument();
});
