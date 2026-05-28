import { describe, it, expect } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import TournamentsPage from './tournaments';

function renderWithQuery(ui: React.ReactElement) {
  const qc = new QueryClient({
    defaultOptions: { queries: { retry: false }, mutations: { retry: false } },
  });
  return render(<QueryClientProvider client={qc}>{ui}</QueryClientProvider>);
}

describe('TournamentsPage', () => {
  it('renders loading state initially', () => {
    renderWithQuery(<TournamentsPage />);
    expect(screen.getByText(/loading/i)).toBeInTheDocument();
  });

  it('renders tournament list after fetch', async () => {
    renderWithQuery(<TournamentsPage />);
    await waitFor(() =>
      expect(screen.getByText('World Cup 2026')).toBeInTheDocument()
    );
  });

  it('shows sport badge', async () => {
    renderWithQuery(<TournamentsPage />);
    await waitFor(() =>
      expect(screen.getByText(/football/i)).toBeInTheDocument()
    );
  });
});
