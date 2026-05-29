import { render, screen, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { HealthPage } from './HealthPage';

function renderPage() {
  const qc = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return render(
    <QueryClientProvider client={qc}>
      <HealthPage />
    </QueryClientProvider>,
  );
}

test('renders backend health status from /api/v1/health', async () => {
  renderPage();
  await waitFor(() => expect(screen.getByTestId('health-status')).toHaveTextContent('ok'));
  expect(screen.getByTestId('health-db')).toHaveTextContent('ok');
});
