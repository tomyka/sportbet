import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { EmailVerificationPage } from './EmailVerificationPage';

const qc = () =>
  new QueryClient({ defaultOptions: { queries: { retry: false }, mutations: { retry: false } } });
const wrap = (children: React.ReactNode) => (
  <QueryClientProvider client={qc()}>{children}</QueryClientProvider>
);

test('shows resend button and sends on click', async () => {
  render(wrap(<EmailVerificationPage />));
  expect(screen.getByText(/verify your email/i)).toBeInTheDocument();
  await userEvent.click(screen.getByRole('button', { name: /resend/i }));
  expect(await screen.findByText(/email sent/i)).toBeInTheDocument();
});
