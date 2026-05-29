import { describe, it, expect } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import TournamentDetailPage from './tournaments.$slug';

function renderWithQuery(ui: React.ReactElement) {
  const qc = new QueryClient({
    defaultOptions: { queries: { retry: false }, mutations: { retry: false } },
  });
  return render(<QueryClientProvider client={qc}>{ui}</QueryClientProvider>);
}

describe('TournamentDetailPage', () => {
  it('renders loading initially', () => {
    renderWithQuery(<TournamentDetailPage slug="wc2026" />);
    expect(screen.getByText(/loading/i)).toBeInTheDocument();
  });

  it('renders tournament name after fetch', async () => {
    renderWithQuery(<TournamentDetailPage slug="wc2026" />);
    await waitFor(() =>
      expect(screen.getByText('World Cup 2026')).toBeInTheDocument()
    );
  });

  it('renders stages section', async () => {
    renderWithQuery(<TournamentDetailPage slug="wc2026" />);
    await waitFor(() =>
      expect(screen.getByText('Group Stage')).toBeInTheDocument()
    );
  });

  it('renders not found when API returns 404', async () => {
    renderWithQuery(<TournamentDetailPage slug="not-found" />);
    await waitFor(() =>
      expect(screen.getByText(/not found/i)).toBeInTheDocument()
    );
  });

  it('renders not found when no slug is provided', () => {
    renderWithQuery(<TournamentDetailPage />);
    expect(screen.getByText(/not found/i)).toBeInTheDocument();
  });

  it('renders fixtures section', async () => {
    renderWithQuery(<TournamentDetailPage slug="wc2026" />);
    await waitFor(() =>
      expect(screen.getByText(/fixtures/i)).toBeInTheDocument()
    );
  });

  it('shows finished fixture result', async () => {
    renderWithQuery(<TournamentDetailPage slug="wc2026" />);
    await waitFor(() =>
      expect(screen.getByText('2 – 1')).toBeInTheDocument()
    );
  });
});
